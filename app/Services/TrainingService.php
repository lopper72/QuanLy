<?php

namespace App\Services;

use App\Models\TrainingSession;
use App\Models\TrainingSessionItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TrainingService
{
    /**
     * List all training sessions with filters.
     */
    public function listSessions(array $filters = [])
    {
        $query = TrainingSession::with(['child', 'items.exercise']);

        if (!empty($filters['child_id'])) {
            $query->where('child_id', $filters['child_id']);
        }

        if (!empty($filters['session_date'])) {
            $query->whereDate('session_date', $filters['session_date']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('session_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('session_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('session_date', 'desc')
            ->orderBy('scheduled_time', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create a training session with optional items.
     */
    public function createSession(array $data): TrainingSession
    {
        return DB::transaction(function () use ($data) {
            $totalMinutes = collect($data['items'] ?? [])->sum('duration_minutes');

            $session = TrainingSession::create([
                'child_id' => $data['child_id'],
                'session_date' => $data['session_date'],
                'scheduled_time' => $data['scheduled_time'] ?? null,
                'status' => $data['status'] ?? 'planned',
                'total_minutes' => $data['duration_minutes'] ?? ($totalMinutes > 0 ? $totalMinutes : null),
                'notes' => $data['notes'] ?? null,
            ]);

            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $index => $itemData) {
                    $session->items()->create([
                        'exercise_id' => $itemData['exercise_id'],
                        'sort_order' => $itemData['sort_order'] ?? ($index + 1),
                        'duration_minutes' => $itemData['duration_minutes'] ?? null,
                        'completion_status' => $itemData['completion_status'] ?? 'not_started',
                        'therapist_note' => $itemData['therapist_note'] ?? null,
                    ]);
                }
            }

            return $session->load(['child', 'items.exercise']);
        });
    }

    /**
     * Get detailed training session view.
     */
    public function getSessionDetail(TrainingSession $session): TrainingSession
    {
        return $session->load(['child', 'items.exercise']);
    }

    /**
     * Update training session and its items.
     */
    public function updateSession(TrainingSession $session, array $data): TrainingSession
    {
        return DB::transaction(function () use ($session, $data) {
            $totalMinutes = collect($data['items'] ?? [])->sum('duration_minutes');

            $session->update([
                'child_id' => $data['child_id'],
                'session_date' => $data['session_date'],
                'scheduled_time' => $data['scheduled_time'] ?? $session->scheduled_time,
                'status' => $data['status'],
                'total_minutes' => $data['duration_minutes'] ?? ($totalMinutes > 0 ? $totalMinutes : null),
                'notes' => $data['notes'] ?? null,
            ]);

            if (isset($data['items']) && is_array($data['items'])) {
                $keepIds = [];
                
                foreach ($data['items'] as $index => $itemData) {
                    $itemPayload = [
                        'exercise_id' => $itemData['exercise_id'],
                        'sort_order' => $itemData['sort_order'] ?? ($index + 1),
                        'duration_minutes' => $itemData['duration_minutes'] ?? null,
                        'completion_status' => $itemData['completion_status'] ?? 'not_started',
                        'therapist_note' => $itemData['therapist_note'] ?? null,
                    ];

                    if (!empty($itemData['id'])) {
                        $item = $session->items()->find($itemData['id']);
                        if ($item) {
                            $item->update($itemPayload);
                            $keepIds[] = $item->id;
                            continue;
                        }
                    }

                    // Create new
                    $newItem = $session->items()->create($itemPayload);
                    $keepIds[] = $newItem->id;
                }

                // Delete items that were not updated or created
                $session->items()->whereNotIn('id', $keepIds)->delete();
            }

            return $session->load(['child', 'items.exercise']);
        });
    }

    /**
     * Soft delete training session.
     */
    public function deleteSession(TrainingSession $session): bool
    {
        $session->items()->delete();
        return $session->delete();
    }

    /**
     * Update session status.
     */
    public function updateSessionStatus(TrainingSession $session, string $status): TrainingSession
    {
        $payload = ['status' => $status];
        if ($session->status === 'missed' && in_array($status, ['completed', 'skipped'], true)) {
            $payload['notes'] = trim(($session->notes ? $session->notes.PHP_EOL : '').'Cập nhật sau ngày tập');
        }

        $session->update($payload);
        return $session->load(['child', 'items.exercise']);
    }

    /**
     * Update dynamic status for an individual training session item.
     */
    public function updateItemStatus(TrainingSessionItem $item, string $status): TrainingSessionItem
    {
        $payload = ['completion_status' => $status];
        $session = $item->trainingSession;
        if ($session?->status === 'missed' && in_array($status, ['completed', 'partially_completed', 'skipped'], true)) {
            $payload['therapist_note'] = trim(($item->therapist_note ? $item->therapist_note.PHP_EOL : '').'Cập nhật sau ngày tập');
        }

        $item->update($payload);
        if ($session) {
            $this->checkAndAutoUpdateSessionStatus($session);
        }

        return $item->load('exercise');
    }

    public function recalculateSessionStatus(TrainingSession $session): TrainingSession
    {
        $this->checkAndAutoUpdateSessionStatus($session);

        return $session->refresh()->load(['child', 'items.exercise']);
    }

    /**
     * List all training sessions scheduled for today.
     */
    public function listTodaySessions()
    {
        return TrainingSession::with(['child', 'items.exercise'])
            ->whereHas('child', fn ($query) => $query->active())
            ->whereDate('session_date', today())
            ->orderBy('scheduled_time', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Quick complete a training session item.
     */
    public function quickCompleteItem(TrainingSessionItem $item): TrainingSessionItem
    {
        $item->update(['completion_status' => 'completed']);
        $this->checkAndAutoUpdateSessionStatus($item->trainingSession);
        return $item->load('exercise');
    }

    /**
     * Quick skip a training session item.
     */
    public function quickSkipItem(TrainingSessionItem $item): TrainingSessionItem
    {
        $item->update(['completion_status' => 'skipped']);
        $this->checkAndAutoUpdateSessionStatus($item->trainingSession);
        return $item->load('exercise');
    }

    /**
     * Mark past unfinished sessions and items as missed. Idempotent.
     */
    public function closeMissedSessions(?Carbon $beforeDate = null): int
    {
        $beforeDate ??= today();
        $count = 0;

        TrainingSession::with('items')
            ->whereDate('session_date', '<', $beforeDate->toDateString())
            ->whereIn('status', ['pending', 'planned', 'in_progress'])
            ->chunkById(100, function ($sessions) use (&$count) {
                foreach ($sessions as $session) {
                    DB::transaction(function () use ($session, &$count) {
                        $session->update([
                            'status' => 'missed',
                            'closed_at' => $session->closed_at ?: now(),
                            'auto_closed_reason' => $session->auto_closed_reason ?: 'Quá ngày chưa hoàn thành',
                        ]);

                        $session->items()
                            ->whereIn('completion_status', ['pending', 'not_started', 'planned', 'in_progress', 'partially_completed'])
                            ->update(['completion_status' => 'missed']);

                        $itemIds = $session->items()->pluck('id');
                        if ($itemIds->isNotEmpty()) {
                            \App\Models\ChecklistItem::whereIn('training_session_item_id', $itemIds)
                                ->whereIn('status', ['pending', 'not_started', 'in_progress'])
                                ->update(['status' => 'missed']);
                        }

                        $count++;
                    });
                }
            });

        return $count;
    }

    /**
     * Quick update session notes (parent/therapist note).
     */
    public function updateSessionQuickNote(TrainingSession $session, ?string $notes): TrainingSession
    {
        $session->update(['notes' => $notes]);
        return $session->load(['child', 'items.exercise']);
    }

    /**
     * Group sessions by child for the timeline view.
     * Child groups ordered by latest created_at DESC (newest scheduled first).
     * Sessions within each group ordered by created_at DESC.
     */
    public function groupSessionsByChild($sessions): array
    {
        $grouped = [];

        foreach ($sessions as $session) {
            // Use child_id or 'unknown' if child_id is null or child relation is missing
            $childId = $session->child_id ?? 'unknown';
            if ($session->child_id && !$session->child) {
                $childId = 'unknown';
            }

            if (!isset($grouped[$childId])) {
                $grouped[$childId] = [
                    'child' => $childId === 'unknown' ? null : $session->child,
                    'is_unknown' => $childId === 'unknown',
                    'group_key' => $childId,
                    'sessions' => [],
                    'latest_date' => $session->session_date->toDateString(),
                    'total_count' => 0,
                    'latest_created_at' => $session->created_at,
                ];
            }

            $grouped[$childId]['sessions'][] = $session;
            $grouped[$childId]['total_count']++;

            if ($session->session_date->toDateString() > $grouped[$childId]['latest_date']) {
                $grouped[$childId]['latest_date'] = $session->session_date->toDateString();
            }

            // Track the most recent created_at for this child group ordering
            if ($session->created_at > $grouped[$childId]['latest_created_at']) {
                $grouped[$childId]['latest_created_at'] = $session->created_at;
            }
        }

        // Sort sessions within each group by created_at DESC (newest scheduled first)
        foreach ($grouped as &$group) {
            usort($group['sessions'], function ($a, $b) {
                if ($a->created_at->timestamp !== $b->created_at->timestamp) {
                    return $b->created_at->timestamp - $a->created_at->timestamp;
                }
                return $b->session_date->timestamp - $a->session_date->timestamp;
            });
        }

        // Find next upcoming session for each child
        $today = today()->toDateString();
        foreach ($grouped as &$group) {
            $nextSession = null;
            foreach ($group['sessions'] as $session) {
                $sessionDate = $session->session_date->toDateString();
                if ($sessionDate >= $today) {
                    if (!$nextSession || $sessionDate < $nextSession->session_date->toDateString() ||
                        ($sessionDate === $nextSession->session_date->toDateString() && ($session->scheduled_time ?? '00:00') < ($nextSession->scheduled_time ?? '00:00'))) {
                        $nextSession = $session;
                    }
                }
            }
            $group['next_session'] = $nextSession;
        }

        // Sort child groups by latest created_at DESC (children with newest training first)
        usort($grouped, function ($a, $b) {
            $cmp = $b['latest_created_at']->timestamp - $a['latest_created_at']->timestamp;
            if ($cmp !== 0) return $cmp;
            // Fallback: most recent session date
            return strcmp($b['latest_date'], $a['latest_date']);
        });

        return array_values($grouped);
    }

    /**
     * Delete all training sessions in an unknown group.
     */
    public function deleteUnknownGroup(string $groupKey): int
    {
        if ($groupKey !== 'unknown') {
            return 0;
        }

        return DB::transaction(function () {
            // Find sessions where child_id is null OR child does not exist
            $sessions = TrainingSession::whereNull('child_id')
                ->orWhereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('children')
                        ->whereColumn('children.id', 'training_sessions.child_id')
                        ->whereNull('children.deleted_at');
                })
                ->get();

            $count = 0;
            foreach ($sessions as $session) {
                $this->deleteSession($session);
                $count++;
            }

            return $count;
        });
    }

    /**
     * Auto-updates the session status based on item statuses.
     */
    protected function checkAndAutoUpdateSessionStatus(TrainingSession $session): void
    {
        $items = $session->items()->get();
        if ($items->isEmpty()) {
            return;
        }

        $allProcessed = $items->every(function ($sessionItem) {
            return in_array($sessionItem->completion_status, ['completed', 'skipped']);
        });

        if ($allProcessed) {
            $session->update(['status' => 'completed']);
        } else {
            // If any is completed or skipped or in_progress, it's in_progress
            $anyProcessed = $items->contains(function ($sessionItem) {
                return in_array($sessionItem->completion_status, ['completed', 'skipped', 'partially_completed']);
            });

            if ($anyProcessed) {
                $session->update(['status' => 'in_progress']);
            }
        }
    }
}
