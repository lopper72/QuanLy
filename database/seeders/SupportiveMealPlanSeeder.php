<?php

namespace Database\Seeders;

use App\Models\MealPlanTemplate;
use Illuminate\Database\Seeder;

class SupportiveMealPlanSeeder extends Seeder
{
    public function run(): void
    {
        $weeks = [
            [
                'week_number' => 1,
                'title' => 'Tuần 1: Làm mềm phân, tăng nước và trái cây dễ ăn',
                'description' => 'Bắt đầu nhẹ nhàng với món mềm, dễ ăn, tăng nước chia nhỏ trong ngày.',
                'meals' => [
                    ['breakfast', '07:00', 'Cháo yến mạch và chuối chín', ['Cháo yến mạch', 'Chuối chín']],
                    ['snack', '10:00', 'Sữa chua không đường và thanh long', ['Sữa chua không đường', 'Thanh long']],
                    ['lunch', '11:30', 'Cơm mềm, rau mồng tơi và cá', ['Cơm mềm', 'Rau mồng tơi', 'Cá']],
                    ['dinner', '18:00', 'Canh bí đỏ hoặc rau xanh', ['Canh bí đỏ', 'Rau xanh mềm']],
                    ['water', '20:30', 'Nước ấm chia nhỏ trong ngày', ['Nước ấm']],
                ],
            ],
            [
                'week_number' => 2,
                'title' => 'Tuần 2: Tăng chất xơ nhẹ nhàng',
                'description' => 'Tăng chất xơ từ khoai, rau củ mềm, đậu lượng nhỏ và trái cây mềm.',
                'meals' => [
                    ['breakfast', '07:00', 'Khoai lang hấp và sữa ấm', ['Khoai lang hấp', 'Sữa ấm nếu bé phù hợp']],
                    ['snack', '10:00', 'Đu đủ chín sau bữa ăn', ['Đu đủ chín']],
                    ['lunch', '11:30', 'Cháo đậu xanh lượng nhỏ', ['Cháo', 'Đậu xanh lượng nhỏ']],
                    ['dinner', '18:00', 'Rau củ mềm và cơm mềm', ['Rau củ mềm', 'Cơm mềm']],
                    ['water', '20:30', 'Nhắc uống nước sau vận động', ['Nước lọc']],
                ],
            ],
            [
                'week_number' => 3,
                'title' => 'Tuần 3: Tạo nhịp ăn uống và đi vệ sinh',
                'description' => 'Duy trì giờ ăn đều, thêm nhịp ngồi toilet ngắn sau bữa tối.',
                'meals' => [
                    ['breakfast', '07:00', 'Bữa sáng có yến mạch hoặc khoai', ['Yến mạch', 'Khoai lang']],
                    ['lunch', '11:30', 'Bữa trưa có rau mềm', ['Rau xanh mềm', 'Cơm mềm']],
                    ['snack', '15:00', 'Trái cây mềm sau bữa ăn', ['Chuối chín', 'Đu đủ chín']],
                    ['dinner', '18:00', 'Bữa tối nhẹ dễ tiêu', ['Canh rau', 'Cá hoặc thịt mềm']],
                    ['toilet', '19:00', 'Ngồi toilet 5-10 phút sau ăn tối', ['Thói quen sau bữa tối']],
                ],
            ],
            [
                'week_number' => 4,
                'title' => 'Tuần 4: Duy trì và theo dõi phản ứng',
                'description' => 'Luân phiên món phù hợp và ghi nhận món có thể làm bé đầy bụng.',
                'meals' => [
                    ['breakfast', '07:00', 'Luân phiên yến mạch và khoai lang', ['Yến mạch', 'Khoai lang']],
                    ['lunch', '11:30', 'Rau xanh, bí đỏ và món đạm mềm', ['Rau xanh', 'Bí đỏ', 'Cá hoặc thịt mềm']],
                    ['snack', '15:00', 'Theo dõi trái cây bé dung nạp tốt', ['Thanh long', 'Đu đủ', 'Chuối chín']],
                    ['dinner', '18:00', 'Bữa tối nhẹ, ít món gây đầy bụng', ['Canh rau', 'Cơm mềm']],
                    ['toilet', '19:00', 'Ghi nhận phân cứng hoặc mềm, đau hoặc không đau', ['Theo dõi đi tiêu']],
                ],
            ],
        ];

        foreach ($weeks as $week) {
            $template = MealPlanTemplate::updateOrCreate(
                ['week_number' => $week['week_number'], 'goal' => 'constipation_support'],
                [
                    'title' => $week['title'],
                    'description' => $week['description'],
                    'is_active' => true,
                ]
            );

            $template->items()->delete();

            foreach (range(1, 7) as $day) {
                foreach ($week['meals'] as $index => [$mealTime, $scheduledTime, $title, $foods]) {
                    $template->items()->create([
                        'day_of_week' => $day,
                        'meal_time' => $mealTime,
                        'scheduled_time' => $scheduledTime,
                        'title' => $title,
                        'foods_json' => $foods,
                        'constipation_support_note' => 'Có thể hỗ trợ cải thiện thói quen đi tiêu nếu duy trì đều và phù hợp với bé.',
                        'parent_tip' => $index === 0
                            ? 'Tăng chất xơ từ từ, quan sát đầy bụng và mức hợp tác của bé.'
                            : 'Khuyến khích uống nước và vận động nhẹ trong ngày.',
                    ]);
                }
            }
        }
    }
}
