<?php

namespace App\Services;

use App\Models\BehaviorLog;
use App\Models\Child;
use Illuminate\Support\Carbon;

class BehaviorService
{
    public const BEHAVIOR_TYPES = [
        'tantrum' => 'Ăn vạ',
        'avoidance' => 'Né tránh',
        'sensory_seeking' => 'Tìm kiếm cảm giác',
        'aggression' => 'Hành vi gây hấn',
        'self_stimulation' => 'Tự kích thích',
        'difficulty_transitioning' => 'Khó chuyển hoạt động',
        'poor_sleep' => 'Ngủ kém',
        'picky_eating' => 'Kén ăn',
        'other' => 'Khác',
    ];

    public const SEVERITIES = [
        'low' => 'Nhẹ',
        'medium' => 'Trung bình',
        'high' => 'Cao',
    ];

    /**
     * List all behavior logs with optional filtering.
     */
    public function listBehaviorLogs(array $filters = [])
    {
        $query = BehaviorLog::with('child')->orderBy('recorded_at', 'desc');
        $childStatus = $filters['child_status'] ?? Child::STATUS_ACTIVE;

        if ($childStatus === Child::STATUS_ACTIVE) {
            $query->whereHas('child', fn ($childQuery) => $childQuery->active());
        } elseif ($childStatus === Child::STATUS_PAUSED) {
            $query->whereHas('child', fn ($childQuery) => $childQuery->paused());
        } elseif ($childStatus === Child::STATUS_VOIDED) {
            $query->whereHas('child', fn ($childQuery) => $childQuery->voided());
        } elseif ($childStatus !== 'all') {
            $query->whereHas('child', fn ($childQuery) => $childQuery->active());
        }

        if (!empty($filters['child_id'])) {
            $query->where('child_id', $filters['child_id']);
        }

        if (!empty($filters['behavior_type'])) {
            $query->where('behavior_type', $filters['behavior_type']);
        }

        if (!empty($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        $dateFrom = $filters['date_from'] ?? $filters['start_date'] ?? null;
        $dateTo = $filters['date_to'] ?? $filters['end_date'] ?? null;

        if (!empty($dateFrom)) {
            $query->whereDate('recorded_at', '>=', Carbon::parse($dateFrom));
        }

        if (!empty($dateTo)) {
            $query->whereDate('recorded_at', '<=', Carbon::parse($dateTo));
        }

        return $query->get();
    }

    public function groupLogsByChild($logs): array
    {
        return $logs
            ->groupBy('child_id')
            ->map(function ($childLogs) {
                $child = $childLogs->first()->child;

                return [
                    'child' => $child ? [
                        'id' => $child->id,
                        'full_name' => $child->full_name,
                        'status' => $child->status,
                    ] : null,
                    'logs' => $childLogs->values(),
                ];
            })
            ->values()
            ->toArray();
    }

    public function getFilterChildren(?string $childStatus = null)
    {
        $query = Child::query();
        $status = $childStatus ?? Child::STATUS_ACTIVE;

        if ($status === Child::STATUS_ACTIVE) {
            $query->active();
        } elseif ($status === Child::STATUS_PAUSED) {
            $query->paused();
        } elseif ($status === Child::STATUS_VOIDED) {
            $query->voided();
        }

        return $query->orderBy('full_name')->get(['id', 'full_name', 'status']);
    }

    public function getActiveChildren()
    {
        return Child::active()->orderBy('full_name')->get(['id', 'full_name', 'status']);
    }

    /**
     * Create a new behavior log entry.
     */
    public function createBehaviorLog(array $data): BehaviorLog
    {
        return BehaviorLog::create($data);
    }

    /**
     * Get detail of a specific behavior log.
     */
    public function getBehaviorLogDetail(BehaviorLog $behaviorLog): BehaviorLog
    {
        return $behaviorLog->load('child');
    }

    /**
     * Update an existing behavior log.
     */
    public function updateBehaviorLog(BehaviorLog $behaviorLog, array $data): bool
    {
        return $behaviorLog->update($data);
    }

    /**
     * Delete a behavior log.
     */
    public function deleteBehaviorLog(BehaviorLog $behaviorLog): ?bool
    {
        return $behaviorLog->delete();
    }

    /**
     * Retrieve behavior metrics and analytics based on filters.
     */
    public function getBehaviorSummary(array $filters = []): array
    {
        $logs = $this->listBehaviorLogs($filters);

        $totalCount = $logs->count();
        
        $lowCount = $logs->where('severity', 'low')->count();
        $mediumCount = $logs->where('severity', 'medium')->count();
        $highCount = $logs->where('severity', 'high')->count();

        // Calculate most frequent behavior type
        $mostFrequentType = 'N/A';
        if ($totalCount > 0) {
            $groupedTypes = $logs->groupBy('behavior_type')
                ->map(fn($group) => $group->count())
                ->sortDesc();
            $mostFrequentType = $groupedTypes->keys()->first() ?? 'N/A';
        }

        return [
            'total_incidents' => $totalCount,
            'low_count' => $lowCount,
            'medium_count' => $mediumCount,
            'high_count' => $highCount,
            'most_frequent_type' => $mostFrequentType,
        ];
    }
}
