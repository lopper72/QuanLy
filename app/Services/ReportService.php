<?php

namespace App\Services;

use App\Models\Report;
use App\Models\TrainingSession;
use App\Models\BehaviorLog;
use App\Models\Assessment;
use Illuminate\Support\Carbon;

class ReportService
{
    /**
     * List all reports with filters.
     */
    public function listReports(array $filters = [])
    {
        $query = Report::with('child')->orderBy('report_date', 'desc')->orderBy('id', 'desc');

        if (!empty($filters['child_id'])) {
            $query->where('child_id', $filters['child_id']);
        } else {
            $query->whereHas('child', fn ($q) => $q->notVoided());
        }

        if (!empty($filters['report_type'])) {
            $query->where('report_type', $filters['report_type']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('report_date', '>=', Carbon::parse($filters['start_date']));
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('report_date', '<=', Carbon::parse($filters['end_date']));
        }

        return $query->paginate(10)->withQueryString();
    }

    /**
     * Create a new report.
     */
    public function createReport(array $data): Report
    {
        return Report::create([
            'child_id' => $data['child_id'],
            'report_type' => $data['report_type'],
            'report_date' => $data['report_date'],
            'summary' => $data['summary'] ?? null,
        ]);
    }

    /**
     * Get report detail.
     */
    public function getReportDetail(Report $report): Report
    {
        return $report->load('child');
    }

    /**
     * Update an existing report.
     */
    public function updateReport(Report $report, array $data): bool
    {
        return $report->update([
            'child_id' => $data['child_id'],
            'report_type' => $data['report_type'],
            'report_date' => $data['report_date'],
            'summary' => $data['summary'] ?? null,
        ]);
    }

    /**
     * Soft delete a report.
     */
    public function deleteReport(Report $report): ?bool
    {
        return $report->delete();
    }

    /**
     * Build summary data from training, assessment, and behavior for the report.
     */
    public function buildReportSummary(Report $report): array
    {
        $endDate = Carbon::parse($report->report_date)->endOfDay();
        
        $startDate = match ($report->report_type) {
            'daily' => Carbon::parse($report->report_date)->startOfDay(),
            'weekly' => Carbon::parse($report->report_date)->subDays(6)->startOfDay(),
            'monthly' => Carbon::parse($report->report_date)->subDays(29)->startOfDay(),
            default => Carbon::parse($report->report_date)->subDays(29)->startOfDay(),
        };

        // Training stats
        $trainingQuery = TrainingSession::where('child_id', $report->child_id)
            ->whereBetween('session_date', [$startDate->toDateString(), $endDate->toDateString()]);
        
        $trainingSessionsCount = (int) $trainingQuery->count();
        $completedSessionsCount = (int) (clone $trainingQuery)->where('status', 'completed')->count();
        $completionRate = $trainingSessionsCount > 0 ? (float) round(($completedSessionsCount / $trainingSessionsCount) * 100, 1) : 0.0;
        $totalTrainingMinutes = (int) $trainingQuery->sum('total_minutes');

        // Behavior stats
        $behaviorQuery = BehaviorLog::where('child_id', $report->child_id)
            ->whereBetween('recorded_at', [$startDate, $endDate]);

        $recentBehaviorCount = (int) $behaviorQuery->count();
        $highSeverityBehaviorCount = (int) (clone $behaviorQuery)->where('severity', 'high')->count();

        // Assessment stats
        $latestAssessment = Assessment::with('items')
            ->where('child_id', $report->child_id)
            ->where('assessment_date', '<=', $endDate->toDateString())
            ->orderBy('assessment_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $latestAssessmentScore = $latestAssessment ? (int) $latestAssessment->overall_score : 0;
        $assessmentSkillSummary = $latestAssessment && $latestAssessment->items
            ? $latestAssessment->items->map(fn($item) => [
                'skill_name' => $item->skill_name,
                'score' => $item->score,
                'level' => $item->level,
            ])->toArray()
            : [];

        return [
            'training_sessions_count' => $trainingSessionsCount,
            'completed_sessions_count' => $completedSessionsCount,
            'completion_rate' => $completionRate,
            'total_training_minutes' => $totalTrainingMinutes,
            'recent_behavior_count' => $recentBehaviorCount,
            'high_severity_behavior_count' => $highSeverityBehaviorCount,
            'latest_assessment_score' => $latestAssessmentScore,
            'assessment_skill_summary' => $assessmentSkillSummary,
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ]
        ];
    }
}