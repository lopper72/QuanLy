<?php

namespace App\Services;

use App\Models\BehaviorLog;
use App\Models\Child;
use App\Models\TrainingSession;
use App\Models\AssessmentItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WeeklyReportService
{
    /**
     * Get data for the weekly report.
     */
    public function getWeeklyReportData(int $childId, string $startDate, string $endDate): array
    {
        $child = Child::findOrFail($childId);
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return [
            'child' => $child,
            'date_range' => [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ],
            'training' => $this->getTrainingSummary($childId, $start, $end),
            'behavior' => $this->getBehaviorSummary($childId, $start, $end),
            'assessment' => $this->getAssessmentSummary($childId),
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get training summary for the period.
     */
    protected function getTrainingSummary(int $childId, Carbon $start, Carbon $end): array
    {
        $sessions = TrainingSession::where('child_id', $childId)
            ->whereBetween('session_date', [$start, $end])
            ->get();

        $totalSessions = $sessions->count();
        $completedSessions = $sessions->where('status', 'completed')->count();
        $totalMinutes = $sessions->sum('duration_minutes');

        return [
            'total_sessions' => $totalSessions,
            'completed_sessions' => $completedSessions,
            'completion_rate' => $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100) : 0,
            'total_minutes' => $totalMinutes,
            'sessions' => $sessions->map(fn($s) => [
                'date' => $s->session_date,
                'status' => $s->status,
                'duration' => $s->duration_minutes,
            ]),
        ];
    }

    /**
     * Get behavior summary for the period.
     */
    protected function getBehaviorSummary(int $childId, Carbon $start, Carbon $end): array
    {
        $logs = BehaviorLog::where('child_id', $childId)
            ->whereBetween('log_date', [$start, $end])
            ->get();

        $severityCounts = $logs->groupBy('severity')->map->count();
        $typeCounts = $logs->groupBy('behavior_type')->map->count()->sortDesc();

        return [
            'total_incidents' => $logs->count(),
            'severity_counts' => [
                'low' => $severityCounts->get('low', 0),
                'medium' => $severityCounts->get('medium', 0),
                'high' => $severityCounts->get('high', 0),
            ],
            'top_behaviors' => $typeCounts->take(3)->toArray(),
            'logs' => $logs->map(fn($l) => [
                'date' => $l->log_date,
                'type' => $this->behaviorTypeLabel($l->behavior_type),
                'severity' => $l->severity,
            ]),
        ];
    }

    /**
     * Get latest assessment summary.
     */
    protected function getAssessmentSummary(int $childId): array
    {
        // Get latest score for each skill
        $latestItems = AssessmentItem::whereHas('assessment', function($query) use ($childId) {
                $query->where('child_id', $childId);
            })
            ->select('skill_name', 'score', 'level', 'assessment_id')
            ->whereIn('id', function($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('assessment_items')
                    ->groupBy('skill_name');
            })
            ->get();

        return [
            'skills' => $latestItems->map(fn($item) => [
                'name' => $this->skillLabel($item->skill_name),
                'score' => $item->score,
                'level' => $item->level,
            ]),
        ];
    }

    protected function behaviorTypeLabel(?string $type): string
    {
        return [
            'tantrum' => 'Ăn vạ',
            'avoidance' => 'Né tránh',
            'sensory_seeking' => 'Tìm kiếm cảm giác',
            'aggression' => 'Hành vi gây hấn',
            'self_stimulation' => 'Tự kích thích',
            'difficulty_transitioning' => 'Khó chuyển hoạt động',
            'poor_sleep' => 'Ngủ kém',
            'picky_eating' => 'Kén ăn',
            'withdrawal' => 'Thu mình',
            'hyperactivity' => 'Tăng động',
            'noncompliance' => 'Không tuân thủ',
            'other' => 'Khác',
        ][$type] ?? (string) $type;
    }

    protected function skillLabel(?string $skill): string
    {
        return [
            'gross_motor' => 'Vận động thô',
            'fine_motor' => 'Vận động tinh',
            'communication' => 'Giao tiếp',
            'balance' => 'Thăng bằng',
            'problem_solving' => 'Giải quyết vấn đề',
            'self_regulation' => 'Tự điều chỉnh',
            'social_interaction' => 'Tương tác xã hội',
            'receptive_language' => 'Ngôn ngữ tiếp nhận',
            'expressive_language' => 'Ngôn ngữ biểu đạt',
            'self_care' => 'Tự chăm sóc',
            'sensory_processing' => 'Xử lý giác quan',
            'attention' => 'Chú ý',
            'imitation' => 'Bắt chước',
            'play_skill' => 'Kỹ năng chơi',
        ][$skill] ?? (string) $skill;
    }
}
