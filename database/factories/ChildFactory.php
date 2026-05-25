<?php

namespace Database\Factories;

use App\Models\Child;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChildFactory extends Factory
{
    protected $model = Child::class;

    public function definition(): array
    {
        $faker = FakerFactory::create('vi_VN');

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

        return [
            'full_name' => $faker->randomElement($vietnameseNames) . ' ' . $faker->unique()->numberBetween(10, 99),
            'nickname' => 'Bé',
            'date_of_birth' => $faker->dateTimeBetween('-12 years', '-4 years')->format('Y-m-d'),
            'gender' => $faker->randomElement(['male', 'female', 'non_binary', null]),
            'diagnosis_level' => $faker->randomElement(['mild', 'moderate', 'severe', null]),
            'notes' => 'Bé hợp tác tốt hơn khi có phần thưởng nhỏ. Cần nhắc lại chỉ dẫn 2-3 lần. Bé dễ mất tập trung sau khoảng 10 phút. Nên tập trong môi trường ít tiếng ồn. Phụ huynh ghi nhận bé ngủ tốt hơn tuần này.',
            'status' => 'active',
            'paused_at' => null,
            'voided_at' => null,
            'status_note' => null,
        ];
    }
}
