<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\TrainingSession;
use App\Models\TrainingSessionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingSessionItemFactory extends Factory
{
    protected $model = TrainingSessionItem::class;

    public function definition(): array
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
            'sort_order' => $this->optionalNumber(1, 8, 50),
            'duration_minutes' => $this->optionalNumber(5, 30, 70),
            'completion_status' => $this->optionalValue(['not_started', 'completed', 'partially_completed', 'skipped'], 50),
            'therapist_note' => $this->optionalValue($notes, 50),
        ];
    }

    private function randomValue(array $values): mixed
    {
        return $values[array_rand($values)];
    }

    private function optionalValue(array $values, int $percent = 50): mixed
    {
        return random_int(1, 100) <= $percent ? $this->randomValue($values) : null;
    }

    private function optionalNumber(int $min, int $max, int $percent = 50): ?int
    {
        return random_int(1, 100) <= $percent ? random_int($min, $max) : null;
    }
}
