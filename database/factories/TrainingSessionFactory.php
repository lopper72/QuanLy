<?php

namespace Database\Factories;

use App\Models\TrainingSession;
use App\Models\Child;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingSessionFactory extends Factory
{
    protected $model = TrainingSession::class;

    public function definition()
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
            'session_date' => $this->faker->dateTimeBetween('-14 days', 'now')->format('Y-m-d'),
            'scheduled_time' => $this->faker->optional(0.8)->randomElement(['07:00', '08:00', '09:00', '14:00', '15:30', '17:00', '20:00']),
            'status' => $this->faker->randomElement(['planned', 'in_progress', 'completed', 'skipped']),
            'total_minutes' => $this->faker->optional(0.6)->numberBetween(15, 90),
            'notes' => $this->faker->optional()->randomElement($notes),
        ];
    }
}
