<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\ExerciseCombo;
use App\Models\ExerciseStep;
use App\Models\WeeklyTrainingPlan;
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

    public const CATEGORY_DESCRIPTIONS = [
        'gross_motor' => 'Giúp cải thiện thăng bằng, phối hợp cơ thể và kiểm soát vận động.',
        'fine_motor' => 'Rèn sự khéo léo của bàn tay, phối hợp tay mắt và chuẩn bị cho kỹ năng học tập.',
        'sensory' => 'Hỗ trợ bé điều chỉnh cảm giác, giảm né tránh hoặc tìm kiếm kích thích quá mức.',
        'communication' => 'Tăng chú ý chung, giao tiếp mắt, hiểu lời nói và chủ động tương tác.',
        'cognitive' => 'Phát triển khả năng quan sát, ghi nhớ, phân loại và làm theo chỉ dẫn.',
        'social' => 'Giúp bé biết chờ lượt, chơi cùng người khác và phản hồi phù hợp trong tình huống hằng ngày.',
        'self_care' => 'Tập các kỹ năng tự lập như dọn dẹp, mặc đồ, vệ sinh và chuyển hoạt động.',
    ];

    public const CATEGORY_BENEFITS = [
        'gross_motor' => ['Thăng bằng tốt hơn', 'Phối hợp tay chân', 'Tăng sức bền vận động'],
        'fine_motor' => ['Cầm nắm chính xác', 'Phối hợp tay mắt', 'Tập trung lâu hơn'],
        'sensory' => ['Điều chỉnh cảm giác', 'Giảm né tránh', 'Bình tĩnh hơn'],
        'communication' => ['Tăng giao tiếp mắt', 'Hiểu chỉ dẫn', 'Chủ động gọi người lớn'],
        'cognitive' => ['Nhận biết tốt hơn', 'Giải quyết vấn đề', 'Làm theo chuỗi bước'],
        'social' => ['Chờ đến lượt', 'Chơi tương tác', 'Giảm phản ứng bốc đồng'],
        'self_care' => ['Tự lập hơn', 'Theo nề nếp', 'Chuyển hoạt động dễ hơn'],
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
                  ->orWhere('instructions', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('expected_benefits', 'like', "%{$search}%")
                  ->orWhere('weekly_expectation', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['difficulty'])) {
            $query->where('difficulty', $filters['difficulty']);
        }

        if (!empty($filters['target_skill'])) {
            $query->where('target_skill', $filters['target_skill']);
        }

        if (!empty($filters['age'])) {
            $age = (int) $filters['age'];
            $query->where(function ($q) use ($age) {
                $q->whereNull('recommended_age')
                    ->orWhere('recommended_age', 'like', "%{$age}%")
                    ->orWhere('recommended_age', 'like', '%3-6%')
                    ->orWhere('recommended_age', 'like', '%4-7%')
                    ->orWhere('recommended_age', 'like', '%5-8%');
            });
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $isActive = filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isActive !== null) {
                $query->where('is_active', $isActive);
            }
        }

        return $query->orderBy('title')->get();
    }

    public function groupedExercises(Collection $exercises): array
    {
        $grouped = $exercises->groupBy('category');

        return collect(self::CATEGORIES)
            ->map(fn (string $label, string $key) => [
                'key' => $key,
                'label' => $label,
                'description' => self::CATEGORY_DESCRIPTIONS[$key] ?? '',
                'benefits' => self::CATEGORY_BENEFITS[$key] ?? [],
                'exercises' => $grouped->get($key, collect())->values(),
                'count' => $grouped->get($key, collect())->count(),
            ])
            ->values()
            ->all();
    }

    public function listCombos(): Collection
    {
        return ExerciseCombo::with('exercises')
            ->orderBy('title')
            ->get();
    }

    public function listWeeklyPlans(): Collection
    {
        return WeeklyTrainingPlan::with(['items.exercise', 'items.combo'])
            ->orderBy('title')
            ->get();
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
        return $exercise->load(['steps', 'combos.exercises']);
    }

    public function relatedExercises(Exercise $exercise): Collection
    {
        return Exercise::query()
            ->where('id', '!=', $exercise->id)
            ->where('category', $exercise->category)
            ->active()
            ->orderBy('title')
            ->limit(4)
            ->get();
    }

    public function suggestedWeeklyPlans(Exercise $exercise): Collection
    {
        return WeeklyTrainingPlan::query()
            ->where('target_condition', $exercise->target_skill)
            ->orWhereHas('items', fn ($query) => $query->where('exercise_id', $exercise->id))
            ->with(['items.exercise', 'items.combo'])
            ->limit(3)
            ->get();
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
