<?php

namespace App\Services;

use App\Models\ChecklistItem;
use App\Models\ChecklistProgress;
use App\Models\Child;
use App\Models\DailyChecklist;
use App\Models\DailyMood;
use App\Models\ParentNote;
use App\Models\ProgressLog;
use App\Models\Reminder;
use App\Models\StreakTracking;
use App\Models\TrainingSession;
use App\Models\TrainingSessionItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DailyChecklistService
{
    public function getTodayData(?string $contextMode = null): array
    {
        $date = today();
        $children = Child::active()->orderBy('full_name')->get();
        $checklists = $children->map(fn (Child $child) => $this->syncChecklistForChild($child, $date, $contextMode));

        $items = $this->todayItems($date);
        $progress = $this->calculateProgress($items);

        return [
            'date' => $date->toDateString(),
            'greeting' => 'Xin chào bố/mẹ',
            'summary' => [
                'total_children' => $children->count(),
                'total_items' => $progress['total_items'],
                'completed_items' => $progress['completed_items'],
                'remaining_items' => $progress['remaining_items'],
                'completion_percent' => $progress['completion_percent'],
            ],
            'children' => $children,
            'checklists' => $checklists->map(fn (DailyChecklist $checklist) => $this->serializeChecklist($checklist))->values(),
            'timeline' => $this->serializeTimeline($items),
            'reminders' => $this->upcomingReminders($date),
            'streak' => $this->streakSummary($children),
            'moods' => $this->todayMoods($date),
            'progressLogs' => $this->recentProgressLogs(),
            'suggestions' => $this->suggestions($items),
            'contextMode' => $contextMode ?: 'home',
        ];
    }

    public function updateItem(ChecklistItem $item, array $data): ChecklistItem
    {
        return DB::transaction(function () use ($item, $data) {
            $status = $data['status'] ?? $item->status;
            $payload = [
                'status' => $status,
                'performance_result' => $data['performance_result'] ?? $item->performance_result,
                'parent_note' => array_key_exists('parent_note', $data) ? $data['parent_note'] : $item->parent_note,
                'completed_at' => $status === ChecklistItem::STATUS_COMPLETED ? now() : null,
            ];

            $item->update($payload);
            $this->syncTrainingItemStatus($item, $status);
            $this->storeParentNote($item);
            $this->refreshProgress($item->dailyChecklist);
            $this->refreshStreak($item->dailyChecklist);

            return $item->load(['dailyChecklist.child', 'trainingSessionItem.exercise']);
        });
    }

    public function quickComplete(ChecklistItem $item): ChecklistItem
    {
        return $this->updateItem($item, [
            'status' => ChecklistItem::STATUS_COMPLETED,
            'performance_result' => 'good',
        ]);
    }

    public function updateMood(Child $child, string $mood): DailyMood
    {
        return DailyMood::updateOrCreate(
            ['child_id' => $child->id, 'mood_date' => today()->toDateString()],
            ['mood' => $mood]
        );
    }

    public function addProgressLog(Child $child, string $title): ProgressLog
    {
        return ProgressLog::create([
            'child_id' => $child->id,
            'title' => $title,
            'logged_at' => now(),
        ]);
    }

    public function carryOver(ChecklistItem $item): ?ChecklistItem
    {
        if ($item->status === ChecklistItem::STATUS_COMPLETED || $item->carried_over_at) {
            return null;
        }

        return DB::transaction(function () use ($item) {
            $item->load(['dailyChecklist.child', 'trainingSessionItem.trainingSession', 'trainingSessionItem.exercise']);
            $sourceTrainingItem = $item->trainingSessionItem;

            if (!$sourceTrainingItem || !$item->dailyChecklist?->child?->isActive()) {
                return null;
            }

            $tomorrow = today()->addDay();
            $session = TrainingSession::firstOrCreate(
                [
                    'child_id' => $item->dailyChecklist->child_id,
                    'session_date' => $tomorrow->toDateString(),
                    'scheduled_time' => $sourceTrainingItem->trainingSession?->scheduled_time,
                ],
                [
                    'status' => 'pending',
                    'total_minutes' => $sourceTrainingItem->duration_minutes,
                    'notes' => 'Bài tập được chuyển từ checklist hôm trước.',
                ]
            );

            $trainingItem = TrainingSessionItem::firstOrCreate(
                [
                    'training_session_id' => $session->id,
                    'exercise_id' => $sourceTrainingItem->exercise_id,
                ],
                [
                    'sort_order' => $sourceTrainingItem->sort_order,
                    'duration_minutes' => $sourceTrainingItem->duration_minutes,
                    'completion_status' => 'pending',
                    'therapist_note' => $sourceTrainingItem->therapist_note,
                ]
            );

            $targetChecklist = $this->syncChecklistForChild($item->dailyChecklist->child, $tomorrow);
            $newItem = ChecklistItem::updateOrCreate(
                ['training_session_item_id' => $trainingItem->id],
                [
                    'daily_checklist_id' => $targetChecklist->id,
                    'carried_over_from_id' => $item->id,
                    'status' => ChecklistItem::STATUS_PENDING,
                ]
            );

            $item->update(['carried_over_at' => now()]);
            $this->refreshProgress($targetChecklist);

            return $newItem;
        });
    }

    protected function syncChecklistForChild(Child $child, Carbon $date, ?string $contextMode = null): DailyChecklist
    {
        $checklist = DailyChecklist::firstOrCreate(
            ['child_id' => $child->id, 'checklist_date' => $date->toDateString()],
            ['context_mode' => $contextMode ?: 'home']
        );

        if ($contextMode && $checklist->context_mode !== $contextMode) {
            $checklist->update(['context_mode' => $contextMode]);
        }

        $sessions = TrainingSession::with(['items.exercise'])
            ->where('child_id', $child->id)
            ->whereDate('session_date', $date)
            ->orderBy('scheduled_time')
            ->get();

        foreach ($sessions as $session) {
            foreach ($session->items as $trainingItem) {
                ChecklistItem::firstOrCreate(
                    ['training_session_item_id' => $trainingItem->id],
                    [
                        'daily_checklist_id' => $checklist->id,
                        'status' => $this->mapTrainingStatus($trainingItem->completion_status),
                    ]
                );
            }
        }

        return $this->refreshProgress($checklist);
    }

    protected function refreshProgress(DailyChecklist $checklist): DailyChecklist
    {
        $items = $checklist->items()->get();
        $progress = $this->calculateProgress($items);

        ChecklistProgress::updateOrCreate(
            ['daily_checklist_id' => $checklist->id],
            $progress
        );

        return $checklist->load(['child', 'progress', 'items.trainingSessionItem.exercise', 'items.trainingSessionItem.trainingSession']);
    }

    protected function calculateProgress(Collection $items): array
    {
        $total = $items->count();
        $completed = $items->where('status', ChecklistItem::STATUS_COMPLETED)->count();
        $remaining = max($total - $completed, 0);

        return [
            'total_items' => $total,
            'completed_items' => $completed,
            'remaining_items' => $remaining,
            'completion_percent' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
        ];
    }

    protected function todayItems(Carbon $date): Collection
    {
        return ChecklistItem::with([
            'dailyChecklist.child',
            'trainingSessionItem.exercise',
            'trainingSessionItem.trainingSession',
        ])
            ->whereHas('dailyChecklist', fn ($query) => $query->whereDate('checklist_date', $date))
            ->get()
            ->sortBy(fn (ChecklistItem $item) => sprintf(
                '%s-%03d',
                $item->trainingSessionItem?->trainingSession?->scheduled_time ?: '23:59',
                $item->trainingSessionItem?->sort_order ?: 999
            ))
            ->values();
    }

    protected function serializeChecklist(DailyChecklist $checklist): array
    {
        $checklist->loadMissing(['child', 'progress', 'items.trainingSessionItem.exercise', 'items.trainingSessionItem.trainingSession']);

        return [
            'id' => $checklist->id,
            'child' => $checklist->child,
            'context_mode' => $checklist->context_mode,
            'progress' => $checklist->progress,
            'items' => $this->serializeTimeline($checklist->items)->values(),
        ];
    }

    protected function serializeTimeline(Collection $items): Collection
    {
        return $items->map(function (ChecklistItem $item) {
            $trainingItem = $item->trainingSessionItem;
            $exercise = $trainingItem?->exercise;
            $session = $trainingItem?->trainingSession;

            return [
                'id' => $item->id,
                'status' => $item->status,
                'performance_result' => $item->performance_result,
                'parent_note' => $item->parent_note,
                'completed_at' => $item->completed_at,
                'carried_over_at' => $item->carried_over_at,
                'time' => $session?->scheduled_time,
                'duration_minutes' => $trainingItem?->duration_minutes,
                'therapist_note' => $trainingItem?->therapist_note,
                'exercise' => $exercise,
                'child' => $item->dailyChecklist?->child,
                'has_video' => filled($exercise?->video_path) || filled($exercise?->video_url),
                'short_instruction' => $exercise?->instructions ? Str::limit($exercise->instructions, 120) : null,
            ];
        })->values();
    }

    protected function upcomingReminders(Carbon $date): array
    {
        $now = now();
        $items = $this->todayItems($date)
            ->filter(fn (ChecklistItem $item) => !in_array($item->status, [ChecklistItem::STATUS_COMPLETED, ChecklistItem::STATUS_MISSED], true))
            ->filter(fn (ChecklistItem $item) => $item->trainingSessionItem?->trainingSession?->scheduled_time)
            ->filter(function (ChecklistItem $item) use ($date, $now) {
                $time = Carbon::parse($date->toDateString().' '.$item->trainingSessionItem->trainingSession->scheduled_time);
                return $time->greaterThanOrEqualTo($now) && $time->diffInMinutes($now) <= 60;
            })
            ->take(3);

        return $items->map(function (ChecklistItem $item) use ($date) {
            $time = Carbon::parse($date->toDateString().' '.$item->trainingSessionItem->trainingSession->scheduled_time);
            Reminder::firstOrCreate(
                [
                    'child_id' => $item->dailyChecklist->child_id,
                    'checklist_item_id' => $item->id,
                    'remind_at' => $time->copy()->subMinutes(15),
                ],
                ['channel' => 'in_app', 'status' => 'pending']
            );

            return [
                'id' => $item->id,
                'minutes_until' => max(now()->diffInMinutes($time, false), 0),
                'exercise_title' => $item->trainingSessionItem?->exercise?->title,
                'time' => $item->trainingSessionItem?->trainingSession?->scheduled_time,
            ];
        })->values()->all();
    }

    protected function todayMoods(Carbon $date): array
    {
        return DailyMood::whereDate('mood_date', $date)
            ->get()
            ->keyBy('child_id')
            ->toArray();
    }

    protected function recentProgressLogs(): array
    {
        return ProgressLog::with('child')
            ->latest('logged_at')
            ->limit(8)
            ->get()
            ->map(fn (ProgressLog $log) => [
                'id' => $log->id,
                'child_id' => $log->child_id,
                'title' => $log->title,
                'logged_at' => $log->logged_at,
            ])
            ->all();
    }

    protected function suggestions(Collection $items): array
    {
        $suggestions = [];
        foreach ($items as $item) {
            $exercise = $item->trainingSessionItem?->exercise;
            if (!$exercise) {
                continue;
            }

            $failures = ChecklistItem::where('status', ChecklistItem::STATUS_REFUSED)
                ->whereHas('trainingSessionItem', fn ($query) => $query->where('exercise_id', $exercise->id))
                ->whereHas('dailyChecklist', fn ($query) => $query->where('checklist_date', '>=', today()->subDays(3)->toDateString()))
                ->count();

            if ($failures >= 3) {
                $suggestions[] = [
                    'item_id' => $item->id,
                    'text' => "Bé từ chối bài {$exercise->title} 3 ngày liên tiếp. Nên giảm thời lượng hoặc đổi sang bài dễ hơn.",
                ];
            }
        }

        return array_values(array_unique($suggestions, SORT_REGULAR));
    }

    protected function streakSummary(Collection $children): array
    {
        return $children->map(function (Child $child) {
            $streak = StreakTracking::firstOrCreate(['child_id' => $child->id]);

            return [
                'child_id' => $child->id,
                'child_name' => $child->full_name,
                'current_streak' => $streak->current_streak,
                'best_streak' => $streak->best_streak,
                'last_completed_date' => $streak->last_completed_date,
                'reward_text' => $streak->current_streak >= 7
                    ? 'Hoàn thành đủ checklist 7 ngày'
                    : "{$streak->current_streak} ngày liên tiếp hoàn thành",
            ];
        })->values()->all();
    }

    protected function refreshStreak(DailyChecklist $checklist): void
    {
        $checklist->loadMissing('items');
        if ($checklist->items->isEmpty()) {
            return;
        }

        $allCompleted = $checklist->items->every(fn (ChecklistItem $item) => $item->status === ChecklistItem::STATUS_COMPLETED);
        if (!$allCompleted) {
            return;
        }

        $streak = StreakTracking::firstOrCreate(['child_id' => $checklist->child_id]);
        $date = $checklist->checklist_date;

        if ($streak->last_completed_date?->toDateString() === $date->toDateString()) {
            return;
        }

        $yesterday = $date->copy()->subDay()->toDateString();
        $current = $streak->last_completed_date?->toDateString() === $yesterday
            ? $streak->current_streak + 1
            : 1;

        $streak->update([
            'current_streak' => $current,
            'best_streak' => max($streak->best_streak, $current),
            'last_completed_date' => $date,
        ]);
    }

    protected function syncTrainingItemStatus(ChecklistItem $item, string $status): void
    {
        if (!$item->trainingSessionItem) {
            return;
        }

        $item->trainingSessionItem->update([
            'completion_status' => match ($status) {
                ChecklistItem::STATUS_COMPLETED => 'completed',
                ChecklistItem::STATUS_IN_PROGRESS => 'partially_completed',
                ChecklistItem::STATUS_REFUSED => 'refused',
                ChecklistItem::STATUS_MISSED => 'missed',
                default => 'pending',
            },
        ]);
    }

    protected function storeParentNote(ChecklistItem $item): void
    {
        if (!filled($item->parent_note) || !$item->dailyChecklist) {
            return;
        }

        ParentNote::updateOrCreate(
            ['checklist_item_id' => $item->id],
            [
                'child_id' => $item->dailyChecklist->child_id,
                'note' => $item->parent_note,
                'noted_at' => now(),
            ]
        );
    }

    protected function mapTrainingStatus(?string $status): string
    {
        return match ($status) {
            'completed' => ChecklistItem::STATUS_COMPLETED,
            'partially_completed', 'in_progress' => ChecklistItem::STATUS_IN_PROGRESS,
            'refused', 'skipped', 'failed' => ChecklistItem::STATUS_REFUSED,
            'missed' => ChecklistItem::STATUS_MISSED,
            default => ChecklistItem::STATUS_PENDING,
        };
    }
}
