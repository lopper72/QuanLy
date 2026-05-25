<?php

namespace App\Http\Controllers;

use App\Http\Requests\Assessment\StoreAssessmentRequest;
use App\Http\Requests\Assessment\UpdateAssessmentRequest;
use App\Models\Assessment;
use App\Models\Child;
use App\Services\AssessmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssessmentController extends Controller
{
    protected AssessmentService $assessmentService;

    public function __construct(AssessmentService $assessmentService)
    {
        $this->assessmentService = $assessmentService;
    }

    public function index(Request $request): Response
    {
        $filters = $this->cleanFilters($request->only(['child_id', 'start_date', 'end_date', 'search']));

        $assessments = $this->assessmentService->listAssessments($filters);
        $summary = $this->assessmentService->getAssessmentSummary($filters);
        $children = Child::orderBy('full_name')->get(['id', 'full_name', 'status']);

        return Inertia::render('Assessment/Index', [
            'assessments' => $assessments,
            'summary' => $summary,
            'children' => $children,
            'filters' => $filters,
            'skillTypes' => $this->getSkillTypes(),
            'levels' => $this->getLevels(),
        ]);
    }

    public function progress(Request $request): Response
    {
        $filters = $this->cleanFilters($request->only(['child_id', 'skill_name']));

        $progressData = $this->assessmentService->getSkillProgress($filters);
        $latestSkillLevels = $this->assessmentService->getLatestSkillLevels(
            $filters['child_id'] ?? null
        );
        $children = Child::orderBy('full_name')->get(['id', 'full_name', 'status']);

        return Inertia::render('Assessment/Progress', [
            'progressData' => $progressData,
            'latestSkillLevels' => $latestSkillLevels,
            'children' => $children,
            'filters' => $filters,
            'skillTypes' => $this->getSkillTypes(),
            'levels' => $this->getLevels(),
        ]);
    }

    public function create(): Response
    {
        $children = Child::active()->orderBy('full_name')->get(['id', 'full_name', 'status']);

        // Default empty items with the 10 core skill names pre-filled
        $defaultItems = collect($this->getSkillTypes())->map(function ($label, $key) {
            return [
                'skill_name' => $key,
                'score' => null,
                'level' => null,
                'note' => null,
            ];
        })->values()->all();

        return Inertia::render('Assessment/Create', [
            'children' => $children,
            'defaultItems' => $defaultItems,
            'skillTypes' => $this->getSkillTypes(),
            'levels' => $this->getLevels(),
        ]);
    }

    public function store(StoreAssessmentRequest $request)
    {
        $this->assessmentService->createAssessment($request->validated());

        return redirect()->route('assessment.index')
            ->with('success', 'Đã ghi nhận đánh giá.');
    }

    public function show(Assessment $assessment): Response
    {
        $detail = $this->assessmentService->getAssessmentDetail($assessment);

        return Inertia::render('Assessment/Show', [
            'assessment' => $detail,
            'skillTypes' => $this->getSkillTypes(),
            'levels' => $this->getLevels(),
        ]);
    }

    public function edit(Assessment $assessment): Response
    {
        $detail = $this->assessmentService->getAssessmentDetail($assessment);
        $children = Child::active()->orderBy('full_name')->get(['id', 'full_name', 'status']);

        return Inertia::render('Assessment/Edit', [
            'assessment' => $detail,
            'children' => $children,
            'skillTypes' => $this->getSkillTypes(),
            'levels' => $this->getLevels(),
        ]);
    }

    public function update(UpdateAssessmentRequest $request, Assessment $assessment)
    {
        $this->assessmentService->updateAssessment($assessment, $request->validated());

        return redirect()->route('assessment.show', $assessment->id)
            ->with('success', 'Đã cập nhật đánh giá.');
    }

    public function destroy(Assessment $assessment)
    {
        $this->assessmentService->deleteAssessment($assessment);

        return redirect()->route('assessment.index')
            ->with('success', 'Đã xóa đánh giá.');
    }

    protected function getSkillTypes(): array
    {
        return [
            'gross_motor' => 'Vận động thô',
            'fine_motor' => 'Vận động tinh',
            'receptive_language' => 'Ngôn ngữ tiếp nhận',
            'expressive_language' => 'Ngôn ngữ biểu đạt',
            'social_interaction' => 'Tương tác xã hội',
            'self_care' => 'Tự chăm sóc',
            'sensory_processing' => 'Xử lý giác quan',
            'attention' => 'Chú ý',
            'imitation' => 'Bắt chước',
            'play_skill' => 'Kỹ năng chơi',
        ];
    }

    protected function getLevels(): array
    {
        return [
            'emerging' => 'Đang hình thành',
            'developing' => 'Đang phát triển',
            'achieved' => 'Đã đạt được',
            'regression' => 'Thoái lui',
        ];
    }
    protected function cleanFilters(array $filters): array
    {
        return collect($filters)
            ->reject(fn ($value) => $value === null || $value === '')
            ->all();
    }
}
