<?php

namespace Database\Factories;

use App\Models\AssessmentItem;
use App\Models\Assessment;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentItemFactory extends Factory
{
    protected $model = AssessmentItem::class;

    public function definition()
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
            'skill_name' => $this->faker->randomElement([
                'communication',
                'balance',
                'fine_motor',
                'problem_solving',
                'self_regulation',
                'social_interaction',
            ]),
            'score' => $this->faker->optional(0.8)->numberBetween(1, 5),
            'level' => $this->faker->optional()->randomElement(['emerging', 'developing', 'achieved', 'regression']),
            'note' => $this->faker->optional()->randomElement($notes),
        ];
    }
}
