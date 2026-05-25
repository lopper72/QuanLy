<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\AssessmentItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentItemFactory extends Factory
{
    protected $model = AssessmentItem::class;

    public function definition(): array
    {
        $notes = [
            'Bé thực hiện tốt hơn khi có hình ảnh minh họa.',
            'Bé cần hỗ trợ bằng gợi ý tay trong bước đầu.',
            'Cần nhắc lại chỉ dẫn 2-3 lần.',
            'Bé dễ mất tập trung sau khoảng 10 phút.',
            'Bé phản ứng tốt với lời khen ngay sau khi hoàn thành.',
        ];

        return [
            'assessment_id' => Assessment::factory(),
            'skill_name' => $this->randomValue([
                'communication',
                'balance',
                'fine_motor',
                'problem_solving',
                'self_regulation',
                'social_interaction',
            ]),
            'score' => $this->optionalNumber(1, 5, 80),
            'level' => $this->optionalValue(['emerging', 'developing', 'achieved', 'regression'], 50),
            'note' => $this->optionalValue($notes, 50),
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
