<?php

namespace Database\Factories;

use App\Models\Child;
use App\Models\TrainingSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingSessionFactory extends Factory
{
    protected $model = TrainingSession::class;

    public function definition(): array
    {
        $notes = [
            'Bé hợp tác tốt hơn khi có phần thưởng nhỏ.',
            'Cần nhắc lại chỉ dẫn 2-3 lần.',
            'Bé dễ mất tập trung sau khoảng 10 phút.',
            'Nên tập trong môi trường ít tiếng ồn.',
            'Phụ huynh ghi nhận bé ngủ tốt hơn tuần này.',
        ];

        return [
            'child_id' => Child::factory(),
            'session_date' => now()->subDays(random_int(0, 14))->format('Y-m-d'),
            'scheduled_time' => $this->optionalValue(['07:00', '08:00', '09:00', '14:00', '15:30', '17:00', '20:00'], 80),
            'status' => $this->randomValue(['planned', 'in_progress', 'completed', 'skipped']),
            'total_minutes' => $this->optionalNumber(15, 90, 60),
            'notes' => $this->optionalValue($notes, 60),
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
