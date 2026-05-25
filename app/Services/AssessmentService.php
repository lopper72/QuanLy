<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentItem;
use Illuminate\Support\Facades\DB;

class AssessmentService
{
    public function listAssessments(array $filters = [])
    {
        $query = Assessment::with('child')->orderBy('assessment_date', 'desc');

        if (!empty($filters['child_id'])) {
            $query->where('child_id', $filters['child_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->where('assessment_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('assessment_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('child', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%");
            });
        }

        return $query->paginate(10)->withQueryString();
    }

    public function createAssessment(array $data): Assessment
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];

            // Calculate overall score if not explicitly set
            if (!isset($data['overall_score']) || is_null($data['overall_score'])) {
                $scores = collect($items)->pluck('score')->filter(fn($s) => !is_null($s));
                $data['overall_score'] = $scores->isNotEmpty() ? (int) round($scores->average()) : null;
            }

            $assessment = Assessment::create([
                'child_id' => $data['child_id'],
                'assessment_date' => $data['assessment_date'],
                'overall_score' => $data['overall_score'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $assessment->items()->create([
                    'skill_name' => $item['skill_name'],
                    'score' => $item['score'] ?? null,
                    'level' => $item['level'] ?? null,
                    'note' => $item['note'] ?? null,
                ]);
            }

            return $assessment;
        });
    }

    public function getAssessmentDetail(Assessment $assessment): Assessment
    {
        return $assessment->load(['child', 'items']);
    }

    public function updateAssessment(Assessment $assessment, array $data): Assessment
    {
        return DB::transaction(function () use ($assessment, $data) {
            $items = $data['items'] ?? [];

            // Calculate overall score if not explicitly set
            if (!isset($data['overall_score']) || is_null($data['overall_score'])) {
                $scores = collect($items)->pluck('score')->filter(fn($s) => !is_null($s));
                $data['overall_score'] = $scores->isNotEmpty() ? (int) round($scores->average()) : null;
            }

            $assessment->update([
                'child_id' => $data['child_id'],
                'assessment_date' => $data['assessment_date'],
                'overall_score' => $data['overall_score'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Sync items (delete and recreate is simplest and robust)
            $assessment->items()->delete();

            foreach ($items as $item) {
                $assessment->items()->create([
                    'skill_name' => $item['skill_name'],
                    'score' => $item['score'] ?? null,
                    'level' => $item['level'] ?? null,
                    'note' => $item['note'] ?? null,
                ]);
            }

            return $assessment;
        });
    }

    public function deleteAssessment(Assessment $assessment): bool
    {
        return $assessment->delete();
    }

    public function getAssessmentSummary(array $filters = []): array
    {
        $query = Assessment::query();

        if (!empty($filters['child_id'])) {
            $query->where('child_id', $filters['child_id']);
        }

        $totalAssessments = $query->count();
        $avgScore = $query->avg('overall_score');

        // Let's get counts of items by level
        $itemQuery = AssessmentItem::query();
        if (!empty($filters['child_id'])) {
            $itemQuery->whereHas('assessment', function ($q) use ($filters) {
                $q->where('child_id', $filters['child_id']);
            });
        }

        $levelCounts = $itemQuery->select('level', DB::raw('count(*) as count'))
            ->whereNotNull('level')
            ->groupBy('level')
            ->pluck('count', 'level')
            ->toArray();

        return [
            'total_assessments' => $totalAssessments,
            'average_score' => $avgScore ? (int) round($avgScore) : 0,
            'level_counts' => [
                'emerging' => $levelCounts['emerging'] ?? 0,
                'developing' => $levelCounts['developing'] ?? 0,
                'achieved' => $levelCounts['achieved'] ?? 0,
                'regression' => $levelCounts['regression'] ?? 0,
            ],
        ];
    }

    public function getSkillProgress(array $filters = [])
    {
        $query = AssessmentItem::with('assessment.child')
            ->join('assessments', 'assessment_items.assessment_id', '=', 'assessments.id')
            ->select('assessment_items.*', 'assessments.assessment_date')
            ->orderBy('assessments.assessment_date', 'asc');

        if (!empty($filters['child_id'])) {
            $query->where('assessments.child_id', $filters['child_id']);
        }

        if (!empty($filters['skill_name'])) {
            $query->where('assessment_items.skill_name', $filters['skill_name']);
        }

        return $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'skill_name' => $item->skill_name,
                'score' => $item->score,
                'level' => $item->level,
                'date' => $item->assessment_date,
                'note' => $item->note,
            ];
        });
    }

    public function getLatestSkillLevels(?int $childId = null)
    {
        $skills = [
            'gross_motor', 'fine_motor', 'receptive_language', 'expressive_language',
            'social_interaction', 'self_care', 'sensory_processing', 'attention',
            'imitation', 'play_skill'
        ];

        $latestLevels = [];

        foreach ($skills as $skill) {
            $query = AssessmentItem::where('skill_name', $skill)
                ->join('assessments', 'assessment_items.assessment_id', '=', 'assessments.id')
                ->orderBy('assessments.assessment_date', 'desc');

            if ($childId) {
                $query->where('assessments.child_id', $childId);
            }

            $latest = $query->first();

            if ($latest) {
                $trend = $this->getSkillTrend($skill, $childId);
                $latestLevels[] = [
                    'skill_name' => $skill,
                    'level' => $latest->level,
                    'score' => $latest->score,
                    'date' => $latest->assessment_date,
                    'trend' => $trend,
                ];
            } else {
                $latestLevels[] = [
                    'skill_name' => $skill,
                    'level' => null,
                    'score' => null,
                    'date' => null,
                    'trend' => 'stable',
                ];
            }
        }

        return $latestLevels;
    }

    public function getSkillTrend(string $skillName, ?int $childId = null): string
    {
        $query = AssessmentItem::where('skill_name', $skillName)
            ->join('assessments', 'assessment_items.assessment_id', '=', 'assessments.id')
            ->orderBy('assessments.assessment_date', 'desc')
            ->limit(2);

        if ($childId) {
            $query->where('assessments.child_id', $childId);
        }

        $items = $query->get();

        if ($items->count() < 2) {
            return 'stable';
        }

        $latest = $items[0]->score;
        $previous = $items[1]->score;

        if ($latest > $previous) {
            return 'improving';
        } elseif ($latest < $previous) {
            return 'regressing';
        }

        return 'stable';
    }
}
