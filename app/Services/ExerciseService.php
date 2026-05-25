<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\ExerciseStep;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExerciseService
{
    public const CATEGORIES = [
        'gross_motor' => 'Vận động thô',
        'fine_motor' => 'Vận động tinh',
        'sensory' => 'Giác quan',
        'communication' => 'Giao tiếp',
        'cognitive' => 'Nhận thức',
        'social' => 'Xã hội',
        'self_care' => 'Tự chăm sóc',
    ];

    public const DIFFICULTIES = [
        'easy' => 'Dễ',
        'medium' => 'Trung bình',
        'hard' => 'Khó',
    ];

    /**
     * List exercises with optional filters.
     */
    public function listExercises(array $filters = []): Collection
    {
        $query = Exercise::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('instructions', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['difficulty'])) {
            $query->where('difficulty', $filters['difficulty']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $isActive = filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isActive !== null) {
                $query->where('is_active', $isActive);
            }
        }

        return $query->orderBy('title')->get();
    }

    /**
     * Create a new exercise.
     */
    public function createExercise(array $data): Exercise
    {
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        if (!isset($data['is_active'])) {
            $data['is_active'] = true;
        }

        // Handle media uploads
        if (isset($data['thumbnail'])) {
            $data['thumbnail_path'] = $data['thumbnail']->store('exercises/thumbnails', 'public');
        }

        if (isset($data['video'])) {
            $data['video_path'] = $data['video']->store('exercises/videos', 'public');
        }

        $exercise = Exercise::create($data);

        // Handle steps
        if (isset($data['steps']) && is_array($data['steps'])) {
            $this->syncSteps($exercise, $data['steps']);
        }

        return $exercise;
    }

    /**
     * Get detailed exercise information.
     */
    public function getExerciseDetail(Exercise $exercise): Exercise
    {
        return $exercise->load('steps');
    }

    /**
     * Update an existing exercise.
     */
    public function updateExercise(Exercise $exercise, array $data): Exercise
    {
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title'], $exercise->id);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        // Handle media uploads
        if (isset($data['thumbnail'])) {
            if ($exercise->thumbnail_path) {
                Storage::disk('public')->delete($exercise->thumbnail_path);
            }
            $data['thumbnail_path'] = $data['thumbnail']->store('exercises/thumbnails', 'public');
        }

        if (isset($data['video'])) {
            if ($exercise->video_path) {
                Storage::disk('public')->delete($exercise->video_path);
            }
            $data['video_path'] = $data['video']->store('exercises/videos', 'public');
        }

        $exercise->update($data);

        // Handle steps
        if (isset($data['steps']) && is_array($data['steps'])) {
            $this->syncSteps($exercise, $data['steps']);
        }

        return $exercise;
    }

    /**
     * Sync exercise steps.
     */
    protected function syncSteps(Exercise $exercise, array $stepsData): void
    {
        // Simple approach: delete all and recreate
        // In a real app, we might want to update existing ones to preserve IDs
        
        // First, delete old images if any
        foreach ($exercise->steps as $step) {
            if ($step->image_path) {
                Storage::disk('public')->delete($step->image_path);
            }
        }
        $exercise->steps()->delete();

        foreach ($stepsData as $index => $stepData) {
            $imagePath = null;
            if (isset($stepData['image'])) {
                $imagePath = $stepData['image']->store('exercises/steps', 'public');
            } elseif (isset($stepData['image_path'])) {
                $imagePath = $stepData['image_path'];
            }

            $exercise->steps()->create([
                'title' => $stepData['title'],
                'instruction' => $stepData['instruction'] ?? null,
                'image_path' => $imagePath,
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * Delete an existing exercise.
     */
    public function deleteExercise(Exercise $exercise): bool
    {
        return $exercise->delete();
    }

    /**
     * Generate a unique slug for the exercise.
     */
    protected function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    /**
     * Check if the slug exists in the database.
     */
    protected function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Exercise::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }
}
