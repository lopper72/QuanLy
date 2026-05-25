<?php

namespace Database\Factories;

use App\Models\BehaviorLog;
use App\Models\Child;
use Illuminate\Database\Eloquent\Factories\Factory;

class BehaviorLogFactory extends Factory
{
    protected $model = BehaviorLog::class;

    public function definition()
    {
        $triggers = [
            'Bé phải chuyển sang hoạt động mới khi chưa sẵn sàng.',
            'Môi trường có nhiều tiếng ồn và ánh sáng mạnh.',
            'Bé được yêu cầu dừng món đồ chơi yêu thích.',
            'Bé chờ lâu trước khi đến lượt.',
            'Lịch sinh hoạt trong ngày thay đổi đột ngột.',
        ];

        $responses = [
            'Người lớn giảm kích thích xung quanh và nhắc lại chỉ dẫn ngắn.',
            'Cho bé nghỉ 2 phút rồi quay lại hoạt động bằng gợi ý tay.',
            'Dùng bảng hình ảnh để báo trước bước tiếp theo.',
            'Khen ngay khi bé bình tĩnh và làm theo hướng dẫn.',
            'Chuyển sang hoạt động dễ hơn để bé lấy lại sự hợp tác.',
        ];

        $notes = [
            'Bé hợp tác tốt hơn khi có phần thưởng nhỏ.',
            'Cần nhắc lại chỉ dẫn 2-3 lần.',
            'Bé dễ mất tập trung sau khoảng 10 phút.',
            'Nên tập trong môi trường ít tiếng ồn.',
            'Bé phản ứng tốt với lời khen ngay sau khi hoàn thành.',
        ];

        return [
            'child_id' => Child::factory(),
            'behavior_type' => $this->faker->randomElement([
                'aggression',
                'withdrawal',
                'hyperactivity',
                'noncompliance',
                'sensory_seeking',
            ]),
            'severity' => $this->faker->randomElement(['low', 'medium', 'high']),
            'trigger' => $this->faker->optional()->randomElement($triggers),
            'response' => $this->faker->optional()->randomElement($responses),
            'note' => $this->faker->optional()->randomElement($notes),
            'recorded_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
