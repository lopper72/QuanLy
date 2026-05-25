<?php

namespace Database\Factories;

use App\Models\TrainingSessionItem;
use App\Models\TrainingSession;
use App\Models\Exercise;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingSessionItemFactory extends Factory
{
    protected $model = TrainingSessionItem::class;

    public function definition()
    {
        $notes = [
            'Bé cần hỗ trợ bằng gợi ý tay trong bước đầu.',
            'Bé phản ứng tốt với lời khen ngay sau khi hoàn thành.',
            'Cần nhắc lại chỉ dẫn 2-3 lần.',
            'Bé dễ mất tập trung sau khoảng 10 phút.',
            'Nên giảm tiếng ồn khi thực hiện hoạt động này.',
        ];

        return [
            'training_session_id' => TrainingSession::factory(),
            'exercise_id' => Exercise::factory(),
            'sort_order' => $this->faker->optional()->numberBetween(1, 8),
            'duration_minutes' => $this->faker->optional(0.7)->numberBetween(5, 30),
            'completion_status' => $this->faker->optional()->randomElement(['not_started', 'completed', 'partially_completed', 'skipped']),
            'therapist_note' => $this->faker->optional()->randomElement($notes),
        ];
    }
}
