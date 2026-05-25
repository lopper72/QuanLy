<?php

namespace Database\Factories;

use App\Models\Child;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChildFactory extends Factory
{
    protected $model = Child::class;

    public function definition(): array
    {
        $vietnameseNames = [
            'Nguyễn Minh Anh',
            'Trần Gia Bảo',
            'Lê Khánh An',
            'Phạm Tuấn Kiệt',
            'Phan Hoàng Bách',
            'Vũ Hải Đăng',
            'Đỗ Thùy Dương',
            'Ngô Bảo Châu',
        ];

        $birthDate = now()
            ->subYears(random_int(4, 12))
            ->subDays(random_int(0, 364))
            ->format('Y-m-d');

        return [
            'full_name' => $vietnameseNames[array_rand($vietnameseNames)] . ' ' . random_int(10, 99),
            'nickname' => 'Bé',
            'date_of_birth' => $birthDate,
            'gender' => $this->randomValue(['male', 'female', 'non_binary', null]),
            'diagnosis_level' => $this->randomValue(['mild', 'moderate', 'severe', null]),
            'notes' => 'Bé hợp tác tốt hơn khi có phần thưởng nhỏ. Cần nhắc lại chỉ dẫn 2-3 lần. Bé dễ mất tập trung sau khoảng 10 phút. Nên tập trong môi trường ít tiếng ồn. Phụ huynh ghi nhận bé ngủ tốt hơn tuần này.',
            'status' => 'active',
            'paused_at' => null,
            'voided_at' => null,
            'status_note' => null,
        ];
    }

    private function randomValue(array $values): mixed
    {
        return $values[array_rand($values)];
    }
}
