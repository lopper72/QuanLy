<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\Child;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentFactory extends Factory
{
    protected $model = Assessment::class;

    public function definition()
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
            'assessment_date' => $this->faker->dateTimeBetween('-90 days', 'now')->format('Y-m-d'),
            'overall_score' => $this->faker->optional(0.7)->numberBetween(40, 100),
            'notes' => $this->faker->optional()->randomElement($notes),
        ];
    }
}
