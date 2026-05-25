<?php

namespace Database\Factories;

use App\Models\Exercise;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ExerciseFactory extends Factory
{
    protected $model = Exercise::class;

    protected static int $sequence = 1;

    public function definition(): array
    {
        $vietnameseExercises = [
            ['title' => 'Bật nhảy trên thảm', 'category' => 'gross_motor', 'difficulty' => 'easy', 'instructions' => 'Cho bé đứng trên thảm mềm, hướng dẫn bé nhún gối rồi bật nhẹ lên. Làm mẫu trước, sau đó khuyến khích bé làm theo.'],
            ['title' => 'Đi thăng bằng trên vạch', 'category' => 'gross_motor', 'difficulty' => 'medium', 'instructions' => 'Kẻ một đường thẳng trên sàn và hỗ trợ bé đi chậm từng bước. Giữ tay bé khi cần thiết, giảm dần hỗ trợ khi bé tự tin hơn.'],
            ['title' => 'Lăn bóng qua lại', 'category' => 'gross_motor', 'difficulty' => 'easy', 'instructions' => 'Ngồi đối diện bé, lăn bóng qua lại và khuyến khích bé nhìn theo bóng.'],
            ['title' => 'Bắt chước vỗ tay', 'category' => 'fine_motor', 'difficulty' => 'easy', 'instructions' => 'Làm mẫu động tác vỗ tay, sau đó yêu cầu bé bắt chước theo nhịp ngắn.'],
            ['title' => 'Nhìn mắt khi gọi tên', 'category' => 'communication', 'difficulty' => 'easy', 'instructions' => 'Gọi tên bé nhẹ nhàng và khen khi bé nhìn vào mắt trong 1-2 giây.'],
            ['title' => 'Phân loại màu sắc', 'category' => 'cognitive', 'difficulty' => 'medium', 'instructions' => 'Chuẩn bị các vật có màu khác nhau và hướng dẫn bé phân loại theo màu.'],
            ['title' => 'Xếp vòng theo kích thước', 'category' => 'cognitive', 'difficulty' => 'medium', 'instructions' => 'Hướng dẫn bé xếp vòng từ lớn đến nhỏ trên trục.'],
            ['title' => 'Tập đánh răng theo từng bước', 'category' => 'self_care', 'difficulty' => 'hard', 'instructions' => 'Chia hoạt động đánh răng thành từng bước nhỏ và hướng dẫn bé làm theo.'],
            ['title' => 'Chơi cảm giác với hạt gạo', 'category' => 'sensory', 'difficulty' => 'easy', 'instructions' => 'Cho bé chạm, bốc, đổ hạt gạo trong khay dưới sự giám sát.'],
            ['title' => 'Chờ đến lượt khi chơi', 'category' => 'social', 'difficulty' => 'hard', 'instructions' => 'Chơi trò chơi lượt với bé và khen khi bé chờ đến lượt.'],
        ];

        $selected = $this->randomValue($vietnameseExercises);
        $title = $selected['title'];
        $sequence = self::$sequence++;

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . $sequence . '-' . random_int(1000, 9999),
            'category' => $selected['category'],
            'difficulty' => $selected['difficulty'],
            'instructions' => $selected['instructions'],
            'estimated_minutes' => random_int(5, 30),
            'is_active' => true,
        ];
    }

    private function randomValue(array $values): mixed
    {
        return $values[array_rand($values)];
    }
}
