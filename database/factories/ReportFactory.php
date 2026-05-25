<?php

namespace Database\Factories;

use App\Models\Child;
use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        $summaries = [
            'Tuần này bé hợp tác tốt hơn trong các hoạt động vận động và cần tiếp tục luyện giao tiếp mắt khi gọi tên.',
            'Bé hoàn thành nhiều bài tập hơn khi lịch tập ngắn và có phần thưởng nhỏ sau mỗi hoạt động.',
            'Phụ huynh ghi nhận bé ngủ tốt hơn tuần này, hành vi mất tập trung giảm khi môi trường ít tiếng ồn.',
            'Bé cần hỗ trợ bằng gợi ý tay ở bước đầu, sau đó có thể tự thực hiện một phần hoạt động.',
            'Nên duy trì các bài tập vận động thô, phân loại màu sắc và tự chăm sóc trong tuần tiếp theo.',
        ];

        return [
            'child_id' => Child::factory(),
            'report_type' => $this->randomValue(['weekly_summary', 'progress_update', 'behavior_overview']),
            'report_date' => now()->subDays(random_int(0, 30))->format('Y-m-d'),
            'summary' => $this->optionalValue($summaries, 50),
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
}
