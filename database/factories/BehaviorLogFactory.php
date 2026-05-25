<?php

namespace Database\Factories;

use App\Models\BehaviorLog;
use App\Models\Child;
use Illuminate\Database\Eloquent\Factories\Factory;

class BehaviorLogFactory extends Factory
{
    protected $model = BehaviorLog::class;

    public function definition(): array
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
            'behavior_type' => $this->randomValue([
                'aggression',
                'withdrawal',
                'hyperactivity',
                'noncompliance',
                'sensory_seeking',
            ]),
            'severity' => $this->randomValue(['low', 'medium', 'high']),
            'trigger' => $this->optionalValue($triggers, 50),
            'response' => $this->optionalValue($responses, 50),
            'note' => $this->optionalValue($notes, 50),
            'recorded_at' => now()->subDays(random_int(0, 30))->subMinutes(random_int(0, 1440)),
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
