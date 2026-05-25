<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\Child;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentFactory extends Factory
{
    protected $model = Assessment::class;

    public function definition(): array
    {
        $notes = [
            'Bé duy trì chú ý tốt hơn khi hoạt động được chia thành bước ngắn.',
            'Bé cần hỗ trợ bằng gợi ý tay trong bước đầu.',
            'Cần nhắc lại chỉ dẫn 2-3 lần trước khi bé phản hồi.',
            'Bé phản ứng tốt với lời khen ngay sau khi hoàn thành.',
            'Nên tiếp tục luyện tập trong môi trường ít tiếng ồn.',
        ];

        return [
            'child_id' => Child::factory(),
            'assessment_date' => now()->subDays(random_int(0, 90))->format('Y-m-d'),
            'overall_score' => $this->optionalNumber(40, 100, 70),
            'notes' => $this->optionalValue($notes, 50),
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
