<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Http\Requests\Exercise\StoreExerciseRequest;
use App\Http\Requests\Exercise\UpdateExerciseRequest;
use App\Services\ExerciseService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ExerciseController extends Controller
{
    protected ExerciseService $exerciseService;

    public function __construct(ExerciseService $exerciseService)
    {
        $this->exerciseService = $exerciseService;
    }

    /**
     * Display a listing of the exercises.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'category', 'difficulty', 'is_active']);
        $exercises = $this->exerciseService->listExercises($filters);

        return Inertia::render('Exercises/Index', [
            'exercises' => $exercises,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'category' => $filters['category'] ?? '',
                'difficulty' => $filters['difficulty'] ?? '',
                'is_active' => $filters['is_active'] ?? '',
            ],
            'categories' => ExerciseService::CATEGORIES,
            'difficulties' => ExerciseService::DIFFICULTIES,
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ]
        ]);
    }

    /**
     * Show the form for creating a new exercise.
     */
    public function create(): Response
    {
        return Inertia::render('Exercises/Create', [
            'categories' => ExerciseService::CATEGORIES,
            'difficulties' => ExerciseService::DIFFICULTIES,
        ]);
    }

    /**
     * Store a newly created exercise in storage.
     */
    public function store(StoreExerciseRequest $request): RedirectResponse
    {
        $exercise = $this->exerciseService->createExercise($request->validated());

        return redirect()->route('exercises.show', $exercise->id)
            ->with('success', 'Đã tạo bài tập.');
    }

    /**
     * Display the specified exercise.
     */
    public function show(Exercise $exercise): Response
    {
        $detailedExercise = $this->exerciseService->getExerciseDetail($exercise);

        return Inertia::render('Exercises/Show', [
            'exercise' => $detailedExercise,
            'categories' => ExerciseService::CATEGORIES,
            'difficulties' => ExerciseService::DIFFICULTIES,
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified exercise.
     */
    public function edit(Exercise $exercise): Response
    {
        return Inertia::render('Exercises/Edit', [
            'exercise' => $exercise,
            'categories' => ExerciseService::CATEGORIES,
            'difficulties' => ExerciseService::DIFFICULTIES,
        ]);
    }

    /**
     * Update the specified exercise in storage.
     */
    public function update(UpdateExerciseRequest $request, Exercise $exercise): RedirectResponse
    {
        $this->exerciseService->updateExercise($exercise, $request->validated());

        return redirect()->route('exercises.show', $exercise->id)
            ->with('success', 'Đã cập nhật bài tập.');
    }

    /**
     * Remove the specified exercise from storage.
     */
    public function destroy(Exercise $exercise): RedirectResponse
    {
        $this->exerciseService->deleteExercise($exercise);

        return redirect()->route('exercises.index')
            ->with('success', 'Đã xóa bài tập.');
    }
}
