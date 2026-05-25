<?php

namespace App\Services;

use App\Models\Child;
use App\Models\Exercise;
use App\Models\MealLog;
use App\Models\MealPlanItem;
use App\Models\SupplementLog;
use App\Models\SupplementSchedule;
use App\Models\TrainingSession;
use App\Models\BehaviorLog;
use App\Models\Assessment;
use Illuminate\Support\Carbon;

class DashboardService
{
    public function __construct(protected TrainingService $trainingService)
    {
    }

    /**
     * Get high-level overview stats.
     */
    public function getOverviewStats(): array
    {
        $todayStr = Carbon::today()->toDateString();

        return [
            'total_children' => Child::active()->count(),
            'active_children_count' => Child::active()->count(),
            'paused_children_count' => Child::paused()->count(),
            'voided_children_count' => Child::voided()->count(),
            'active_exercises' => Exercise::active()->count(),
            'today_sessions_count' => TrainingSession::whereHas('child', fn ($query) => $query->active())
                ->whereDate('session_date', $todayStr)
                ->count(),
            'today_completed_count' => TrainingSession::whereDate('session_date', $todayStr)
                ->whereHas('child', fn ($query) => $query->active())
                ->where('status', 'completed')
                ->count(),
        ];
    }

    /**
     * Get a summary of today's training sessions.
     */
    public function getTodayTrainingSummary(): array
    {
        $todayStr = Carbon::today()->toDateString();

        $sessions = TrainingSession::with(['child', 'items.exercise'])
            ->whereHas('child', fn ($query) => $query->active())
            ->whereDate('session_date', $todayStr)
            ->orderBy('scheduled_time', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return $sessions
            ->groupBy('child_id')
            ->map(function ($childSessions) {
                $first = $childSessions->first();

                return [
                    'child_id' => $first->child_id,
                    'child_name' => $first->child->full_name ?? 'Chưa xác định',
                    'sessions' => $childSessions->map(fn ($session) => [
                        'id' => $session->id,
                        'status' => $session->status,
                        'scheduled_time' => $session->scheduled_time,
                        'total_minutes' => $session->total_minutes,
                        'items_count' => $session->items->count(),
                        'exercise_thumbnails' => $this->sessionExerciseThumbnails($session),
                        'items' => $session->items
                            ->sortBy('sort_order')
                            ->map(fn ($item) => $this->serializeTodayItem($session, $item))
                            ->values()
                            ->all(),
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();

        return TrainingSession::with(['child', 'items.exercise'])
            ->whereHas('child', fn ($query) => $query->active())
            ->whereDate('session_date', $todayStr)
            ->orderBy('scheduled_time', 'asc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'child_name' => $session->child->full_name ?? 'Chưa xác định',
                    'child_id' => $session->child_id,
                    'status' => $session->status,
                    'scheduled_time' => $session->scheduled_time,
                    'total_minutes' => $session->total_minutes,
                    'items_count' => $session->items()->count(),
                    'exercise_thumbnails' => $this->sessionExerciseThumbnails($session),
                ];
            })
            ->toArray();
    }

    /**
     * Get weekly training session completion stats over the last 7 days.
     */
    public function getWeeklyTrainingCompletion(): array
    {
        $startDate = Carbon::today()->subDays(6)->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        $sessions = TrainingSession::whereHas('child', fn ($query) => $query->active())
            ->whereBetween('session_date', [$startDate, $endDate])
            ->get();

        $total = $sessions->count();
        $completed = $sessions->where('status', 'completed')->count();
        $rate = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;

        // Daily breakdown
        $daily = [];
        for ($i = 6; $i >= 0; $i--) {
            $currentDay = Carbon::today()->subDays($i);
            $dateStr = $currentDay->toDateString();
            
            $daySessions = $sessions->filter(function ($s) use ($dateStr) {
                return $s->session_date instanceof Carbon
                    ? $s->session_date->toDateString() === $dateStr
                    : Carbon::parse($s->session_date)->toDateString() === $dateStr;
            });

            $dayTotal = $daySessions->count();
            $dayCompleted = $daySessions->where('status', 'completed')->count();

            $daily[] = [
                'date' => $dateStr,
                'day_name' => $this->weekdayLabel($currentDay->format('D')),
                'total' => $dayTotal,
                'completed' => $dayCompleted,
                'rate' => $dayTotal > 0 ? round(($dayCompleted / $dayTotal) * 100, 1) : 0.0,
            ];
        }

        return [
            'completion_rate' => $rate,
            'total_sessions' => $total,
            'completed_sessions' => $completed,
            'daily_breakdown' => $daily,
        ];
    }

    /**
     * Get recent training sessions.
     */
    public function getRecentTrainingSessions(int $limit = 5): array
    {
        return TrainingSession::with(['child', 'items.exercise'])
            ->whereHas('child', fn ($query) => $query->active())
            ->orderBy('session_date', 'desc')
            ->orderBy('scheduled_time', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'child_name' => $session->child->full_name ?? 'Chưa xác định',
                    'child_id' => $session->child_id,
                    'session_date' => $session->session_date instanceof Carbon 
                        ? $session->session_date->toDateString() 
                        : Carbon::parse($session->session_date)->toDateString(),
                    'scheduled_time' => $session->scheduled_time,
                    'status' => $session->status,
                    'total_minutes' => $session->total_minutes,
                    'exercise_thumbnails' => $this->sessionExerciseThumbnails($session),
                ];
            })
            ->toArray();
    }

    /**
     * Get recent behavior logs.
     */
    public function getRecentBehaviorLogs(int $limit = 5): array
    {
        return BehaviorLog::with('child')
            ->whereHas('child', fn ($query) => $query->active())
            ->orderBy('recorded_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'child_name' => $log->child->full_name ?? 'Chưa xác định',
                    'child_id' => $log->child_id,
                    'behavior_type' => $log->behavior_type,
                    'severity' => $log->severity,
                    'recorded_at' => $log->recorded_at instanceof Carbon
                        ? $log->recorded_at->toDateTimeString()
                        : Carbon::parse($log->recorded_at)->toDateTimeString(),
                    'note' => $log->note,
                ];
            })
            ->toArray();
    }

    /**
     * Get latest assessments.
     */
    public function getLatestAssessments(int $limit = 5): array
    {
        return Assessment::with('child')
            ->whereHas('child', fn ($query) => $query->active())
            ->orderBy('assessment_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($assessment) {
                return [
                    'id' => $assessment->id,
                    'child_name' => $assessment->child->full_name ?? 'Chưa xác định',
                    'child_id' => $assessment->child_id,
                    'assessment_date' => $assessment->assessment_date instanceof Carbon
                        ? $assessment->assessment_date->toDateString()
                        : Carbon::parse($assessment->assessment_date)->toDateString(),
                    'overall_score' => $assessment->overall_score,
                    'notes' => $assessment->notes,
                ];
            })
            ->toArray();
    }

    /**
     * Get progress summary for active children.
     */
    public function getChildrenProgressSummary(int $limit = 6): array
    {
        return Child::active()
        ->withCount([
            'trainingSessions',
            'trainingSessions as completed_sessions_count' => function ($query) {
                $query->where('status', 'completed');
            },
            'behaviorLogs',
        ])
        ->with(['assessments' => function ($query) {
            $query->orderBy('assessment_date', 'desc')->orderBy('id', 'desc');
        }])
        ->orderBy('updated_at', 'desc')
        ->limit($limit)
        ->get()
        ->map(function ($child) {
            $latestAssessment = $child->assessments->first();
            return [
                'id' => $child->id,
                'full_name' => $child->full_name,
                'diagnosis_level' => $child->diagnosis_level,
                'total_sessions' => $child->training_sessions_count,
                'completed_sessions' => $child->completed_sessions_count,
                'behavior_logs_count' => $child->behavior_logs_count,
                'latest_assessment_score' => $latestAssessment ? $latestAssessment->overall_score : null,
                'latest_assessment_date' => $latestAssessment ? ($latestAssessment->assessment_date instanceof Carbon 
                    ? $latestAssessment->assessment_date->toDateString() 
                    : Carbon::parse($latestAssessment->assessment_date)->toDateString()) : null,
            ];
        })
        ->toArray();
    }

    /**
     * Consolidate all dashboard data arrays.
     */
    public function getDashboardData(): array
    {
        $this->trainingService->closeMissedSessions();

        return [
            'overview_stats' => $this->getOverviewStats(),
            'today_training_summary' => $this->getTodayTrainingSummary(),
            'weekly_training_completion' => $this->getWeeklyTrainingCompletion(),
            'recent_training_sessions' => $this->getRecentTrainingSessions(),
            'recent_behavior_logs' => $this->getRecentBehaviorLogs(),
            'latest_assessments' => $this->getLatestAssessments(),
            'children_progress_summary' => $this->getChildrenProgressSummary(),
            'today_supplement_reminders' => $this->getTodaySupplementReminders(),
            'today_meal_reminders' => $this->getTodayMealReminders(),
            'latest_stool_note' => $this->getLatestStoolNote(),
        ];
    }

    public function getTodaySupplementReminders(): array
    {
        $today = today()->toDateString();

        return SupplementSchedule::with(['child', 'logs' => fn ($query) => $query->whereDate('scheduled_for', $today)])
            ->whereHas('child', fn ($query) => $query->active())
            ->active()
            ->orderBy('scheduled_time')
            ->limit(6)
            ->get()
            ->map(fn (SupplementSchedule $schedule) => [
                'id' => $schedule->id,
                'child_name' => $schedule->child?->full_name,
                'name' => $schedule->name,
                'display_time' => $this->supplementDisplayTime($schedule),
                'status' => $schedule->logs->first()?->status ?? 'pending',
            ])
            ->toArray();
    }

    public function getTodayMealReminders(): array
    {
        $todayDay = (int) today()->dayOfWeekIso;

        return MealPlanItem::with('template')
            ->whereHas('template', fn ($query) => $query->active())
            ->where('day_of_week', $todayDay)
            ->orderBy('meal_time')
            ->limit(6)
            ->get()
            ->map(fn (MealPlanItem $item) => [
                'id' => $item->id,
                'template_title' => $item->template?->title,
                'meal_time' => $this->mealTimeLabel($item->meal_time),
                'title' => $item->title,
            ])
            ->toArray();
    }

    public function getLatestStoolNote(): ?array
    {
        $log = MealLog::with('child')
            ->whereNotNull('stool_note')
            ->orderBy('meal_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if (!$log) {
            return null;
        }

        return [
            'child_name' => $log->child?->full_name,
            'meal_date' => $log->meal_date?->toDateString(),
            'stool_note' => $log->stool_note,
            'water_note' => $log->water_note,
        ];
    }

    protected function weekdayLabel(string $weekday): string
    {
        return [
            'Mon' => 'Thứ 2',
            'Tue' => 'Thứ 3',
            'Wed' => 'Thứ 4',
            'Thu' => 'Thứ 5',
            'Fri' => 'Thứ 6',
            'Sat' => 'Thứ 7',
            'Sun' => 'Chủ nhật',
        ][$weekday] ?? $weekday;
    }

    protected function supplementDisplayTime(SupplementSchedule $schedule): string
    {
        if ($schedule->timing_type === 'fixed_time' && $schedule->scheduled_time) {
            return substr($schedule->scheduled_time, 0, 5);
        }

        return [
            'before_meal' => 'Trước bữa ăn',
            'after_meal' => 'Sau bữa ăn',
            'before_breakfast' => 'Trước bữa sáng',
            'before_lunch' => 'Trước bữa trưa',
            'before_dinner' => 'Trước bữa tối',
            'after_breakfast' => 'Sau bữa sáng',
            'after_lunch' => 'Sau bữa trưa',
            'after_dinner' => 'Sau bữa tối',
            'bedtime' => 'Trước khi ngủ',
        ][$schedule->meal_relation ?: $schedule->timing_type] ?? 'Theo lịch';
    }

    protected function mealTimeLabel(string $mealTime): string
    {
        return [
            'breakfast' => 'Bữa sáng',
            'snack' => 'Bữa phụ',
            'lunch' => 'Bữa trưa',
            'dinner' => 'Bữa tối',
            'water' => 'Nhắc uống nước',
            'toilet' => 'Thói quen đi vệ sinh',
        ][$mealTime] ?? $mealTime;
    }

    protected function sessionExerciseThumbnails(TrainingSession $session): array
    {
        return $session->items
            ->map(fn ($item) => $item->exercise)
            ->filter()
            ->take(3)
            ->map(fn ($exercise) => [
                'id' => $exercise->id,
                'title' => $exercise->title,
                'thumbnail_path' => $exercise->thumbnail_path,
            ])
            ->values()
            ->toArray();
    }

    protected function serializeTodayItem($session, $item): array
    {
        $exercise = $item->exercise;

        return [
            'session_id' => $session->id,
            'item_id' => $item->id,
            'scheduled_time' => $session->scheduled_time,
            'duration_minutes' => $item->duration_minutes ?: $session->total_minutes,
            'status' => $item->completion_status ?: $session->status,
            'exercise' => $exercise ? [
                'id' => $exercise->id,
                'title' => $exercise->title,
                'thumbnail_path' => $exercise->thumbnail_path,
            ] : null,
        ];
    }
}
