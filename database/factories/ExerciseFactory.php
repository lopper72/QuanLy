<?php

namespace Database\Factories;

use App\Models\Exercise;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ExerciseFactory extends Factory
{
    protected $model = Exercise::class;

    public function definition()
    {
        $vietnameseExercises = [
            ['title' => 'Bật nhảy trên thảm', 'category' => 'gross_motor', 'difficulty' => 'easy', 'instructions' => 'Cho bé đứng trên thảm mềm, hướng dẫn bé nhún gối rồi bật nhẹ lên. Làm mẫu trước, sau đó khuyến khích bé làm theo. Khen ngợi khi bé thực hiện được.'],
            ['title' => 'Đi thăng bằng trên vạch', 'category' => 'gross_motor', 'difficulty' => 'medium', 'instructions' => 'Kẻ một đường thẳng trên sàn và hỗ trợ bé đi chậm từng bước. Giữ tay bé khi cần thiết, giảm dần hỗ trợ khi bé tự tin hơn.'],
            ['title' => 'Lăn bóng qua lại', 'category' => 'gross_motor', 'difficulty' => 'easy', 'instructions' => 'Ngồi đối diện bé, lăn bóng qua lại và khuyến khích bé nhìn theo bóng. Dùng bóng có màu sắc nổi bật để thu hút sự chú ý của bé.'],
            ['title' => 'Bắt chước vỗ tay', 'category' => 'fine_motor', 'difficulty' => 'easy', 'instructions' => 'Làm mẫu động tác vỗ tay, sau đó yêu cầu bé bắt chước. Hát một bài hát ngắn và vỗ tay theo nhịp để tạo hứng thú.'],
            ['title' => 'Nhìn mắt khi gọi tên', 'category' => 'communication', 'difficulty' => 'easy', 'instructions' => 'Gọi tên bé nhẹ nhàng và khen khi bé nhìn vào mắt trong 1-2 giây. Tăng dần thời gian yêu cầu bé duy trì giao tiếp bằng mắt.'],
            ['title' => 'Phân loại màu sắc', 'category' => 'cognitive', 'difficulty' => 'medium', 'instructions' => 'Chuẩn bị các vật có màu khác nhau và hướng dẫn bé phân loại theo màu. Bắt đầu với 2 màu cơ bản, tăng dần số lượng màu khi bé tiến bộ.'],
            ['title' => 'Xếp vòng theo kích thước', 'category' => 'cognitive', 'difficulty' => 'medium', 'instructions' => 'Hướng dẫn bé xếp vòng từ lớn đến nhỏ trên trục. Làm mẫu chậm rãi và chỉ cho bé thấy sự khác biệt về kích thước.'],
            ['title' => 'Tập đánh răng theo từng bước', 'category' => 'self_care', 'difficulty' => 'hard', 'instructions' => 'Chia hoạt động đánh răng thành từng bước nhỏ: lấy kem, mở vòi nước, chải răng, súc miệng. Làm mẫu trước từng bước và hướng dẫn bé làm theo.'],
            ['title' => 'Chơi cảm giác với hạt gạo', 'category' => 'sensory', 'difficulty' => 'easy', 'instructions' => 'Cho bé chạm, bốc, đổ hạt gạo trong khay dưới sự giám sát. Có thể giấu đồ chơi nhỏ trong hạt gạo để bé tìm.'],
            ['title' => 'Làm theo chỉ dẫn một bước', 'category' => 'communication', 'difficulty' => 'easy', 'instructions' => 'Đưa ra chỉ dẫn ngắn như "đưa bóng", "ngồi xuống", "vỗ tay". Khen ngợi ngay khi bé làm đúng. Dùng hình ảnh hỗ trợ nếu cần.'],
            ['title' => 'Ném bóng vào rổ', 'category' => 'gross_motor', 'difficulty' => 'medium', 'instructions' => 'Đặt rổ cách bé 1-2 mét. Hướng dẫn bé cầm bóng bằng hai tay và ném vào rổ. Giảm khoảng cách nếu bé gặp khó khăn.'],
            ['title' => 'Bò qua đường hầm', 'category' => 'gross_motor', 'difficulty' => 'medium', 'instructions' => 'Tạo đường hầm bằng bàn ghế hoặc đồ chơi. Khuyến khích bé bò qua, đặt đồ chơi ở đầu kia để tạo động lực.'],
            ['title' => 'Chuyền bóng hai tay', 'category' => 'gross_motor', 'difficulty' => 'medium', 'instructions' => 'Ngồi đối diện bé, hướng dẫn bé dùng hai tay đẩy bóng về phía bạn. Tăng khoảng cách dần dần khi bé cải thiện.'],
            ['title' => 'Nhảy qua vật cản thấp', 'category' => 'gross_motor', 'difficulty' => 'hard', 'instructions' => 'Đặt vật cản thấp 5-10cm. Nắm tay bé và nhảy cùng bé qua vật cản. Tăng dần độ cao khi bé tự tin hơn.'],
            ['title' => 'Gắp hạt bằng kẹp', 'category' => 'fine_motor', 'difficulty' => 'hard', 'instructions' => 'Cho bé dùng kẹp hoặc nhíp để gắp các hạt nhỏ từ bát này sang bát khác. Giám sát chặt chẽ để đảm bảo an toàn.'],
            ['title' => 'Xâu hạt lớn', 'category' => 'fine_motor', 'difficulty' => 'medium', 'instructions' => 'Chuẩn bị hạt lớn có lỗ và dây xâu. Hướng dẫn bé cầm dây và xâu từng hạt một. Khen ngợi khi bé xâu được một hạt.'],
            ['title' => 'Ghép hình đơn giản', 'category' => 'cognitive', 'difficulty' => 'easy', 'instructions' => 'Đưa cho bé bảng ghép hình với 2-4 miếng ghép lớn. Hướng dẫn bé xoay miếng ghép cho đúng vị trí.'],
            ['title' => 'Chờ đến lượt khi chơi', 'category' => 'social', 'difficulty' => 'hard', 'instructions' => 'Chơi trò chơi lượt với bé. Dùng đồng hồ cát để giúp bé hình dung thời gian chờ. Khen bé khi bé chờ đến lượt.'],
            ['title' => 'Gọi tên đồ vật quen thuộc', 'category' => 'communication', 'difficulty' => 'medium', 'instructions' => 'Chỉ vào đồ vật quen thuộc và hỏi bé "Đây là gì?". Gợi ý âm đầu nếu bé chưa trả lời được. Mở rộng dần vốn từ.'],
            ['title' => 'Cất đồ chơi sau khi chơi', 'category' => 'self_care', 'difficulty' => 'medium', 'instructions' => 'Hướng dẫn bé cất đồ chơi vào thùng sau khi chơi xong. Hát bài hát dọn dẹp để tạo thói quen. Khen ngợi khi bé giúp dọn dẹp.'],
        ];

        $selected = $this->faker->randomElement($vietnameseExercises);
        $title = $selected['title'];

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'category' => $selected['category'],
            'difficulty' => $selected['difficulty'],
            'instructions' => $selected['instructions'],
            'estimated_minutes' => $this->faker->numberBetween(5, 30),
            'is_active' => true,
        ];
    }
}