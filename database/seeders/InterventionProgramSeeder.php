<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\ExerciseCombo;
use App\Models\TrainingSessionItem;
use App\Models\WeeklyTrainingPlan;
use App\Services\ExerciseService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InterventionProgramSeeder extends Seeder
{
    private const CATEGORY_GUIDANCE = [
        'gross_motor' => [
            'target_skill' => 'gross_motor',
            'benefits' => 'Cải thiện thăng bằng, phối hợp tay chân, sức bền và khả năng làm theo vận động mẫu.',
            'tools' => 'Thảm mềm, bóng nhẹ, băng dính giấy, gối hoặc vật cản thấp.',
            'safety' => 'Tập trên mặt sàn phẳng, có người lớn đứng gần và dừng lại nếu bé mệt hoặc mất thăng bằng.',
            'tips' => 'Làm mẫu chậm, đếm nhịp rõ ràng và khen ngay khi bé thử thực hiện.',
            'weekly' => 'Sau khoảng 1 tuần tập đều, bé có thể giữ thăng bằng tốt hơn, phản xạ nhanh hơn và tự tin vận động hơn.',
        ],
        'fine_motor' => [
            'target_skill' => 'fine_motor',
            'benefits' => 'Tăng phối hợp tay mắt, sức mạnh ngón tay, độ chính xác khi cầm nắm và sự kiên trì.',
            'tools' => 'Kẹp gắp, hạt lớn, đất nặn, giấy, bút sáp, hộp nhỏ.',
            'safety' => 'Không dùng vật quá nhỏ với bé còn hay cho đồ vào miệng. Luôn giám sát khi dùng kéo hoặc kẹp.',
            'tips' => 'Bắt đầu bằng đồ vật lớn, giảm dần hỗ trợ tay khi bé đã hiểu nhiệm vụ.',
            'weekly' => 'Sau khoảng 1 tuần tập đều, bé có thể cầm nắm chắc hơn, phối hợp tay mắt tốt hơn và tập trung vào việc bàn tay lâu hơn.',
        ],
        'communication' => [
            'target_skill' => 'communication',
            'benefits' => 'Tăng chú ý chung, giao tiếp mắt, hiểu chỉ dẫn và chủ động dùng âm thanh, cử chỉ hoặc lời nói.',
            'tools' => 'Đồ chơi bé thích, tranh ảnh quen thuộc, bóng, sách tranh.',
            'safety' => 'Không ép bé nhìn quá lâu. Ưu tiên tương tác vui vẻ, ngắn và lặp lại nhiều lần.',
            'tips' => 'Chờ bé phản hồi 3-5 giây trước khi nhắc lại. Khen mọi cố gắng giao tiếp.',
            'weekly' => 'Sau khoảng 1 tuần tập đều, bé có thể nhìn khi gọi tên nhiều hơn, hiểu chỉ dẫn ngắn tốt hơn và chủ động tương tác hơn.',
        ],
        'cognitive' => [
            'target_skill' => 'cognitive',
            'benefits' => 'Phát triển khả năng quan sát, phân loại, ghi nhớ, giải quyết vấn đề và làm theo chuỗi bước.',
            'tools' => 'Khối màu, vòng xếp, tranh ghép, thẻ hình, đồ vật quen thuộc.',
            'safety' => 'Chọn vật liệu vừa tay, không sắc cạnh và giảm số lượng đồ nếu bé dễ quá tải.',
            'tips' => 'Dùng câu ngắn, một yêu cầu mỗi lần và tăng độ khó khi bé làm đúng ổn định.',
            'weekly' => 'Sau khoảng 1 tuần tập đều, bé có thể phân loại tốt hơn, chú ý lâu hơn và làm theo chỉ dẫn rõ hơn.',
        ],
        'sensory' => [
            'target_skill' => 'sensory_processing',
            'benefits' => 'Giúp bé điều chỉnh cảm giác, giảm né tránh, tăng khả năng bình tĩnh và chuẩn bị cơ thể trước hoạt động học.',
            'tools' => 'Khay gạo, khăn mềm, bóng gai mềm, nước ấm, hộp cảm giác.',
            'safety' => 'Theo dõi phản ứng của bé. Dừng khi bé khó chịu rõ, hoảng sợ hoặc có dấu hiệu quá tải.',
            'tips' => 'Cho bé quyền từ chối, bắt đầu rất ngắn và mô tả cảm giác bằng lời đơn giản.',
            'weekly' => 'Sau khoảng 1 tuần tập đều, bé có thể chấp nhận cảm giác mới tốt hơn, giảm né tránh và bình tĩnh nhanh hơn.',
        ],
        'social' => [
            'target_skill' => 'social_interaction',
            'benefits' => 'Tăng khả năng chờ lượt, chơi chung, bắt chước bạn/người lớn và phản hồi phù hợp.',
            'tools' => 'Bóng, trò chơi lượt, đồng hồ cát, tranh cảm xúc, đồ chơi đóng vai.',
            'safety' => 'Giữ hoạt động ngắn, tránh tình huống cạnh tranh quá mức khiến bé căng thẳng.',
            'tips' => 'Dùng luật chơi đơn giản, báo trước lượt của bé và lượt của người khác.',
            'weekly' => 'Sau khoảng 1 tuần tập đều, bé có thể chờ đến lượt tốt hơn, giảm bốc đồng và tham gia chơi chung lâu hơn.',
        ],
        'self_care' => [
            'target_skill' => 'self_care',
            'benefits' => 'Tăng tự lập trong sinh hoạt, hiểu trình tự việc hằng ngày và chuyển hoạt động dễ hơn.',
            'tools' => 'Bàn chải, khăn, áo có cúc lớn, hộp đồ chơi, tranh lịch trình.',
            'safety' => 'Chọn nhiệm vụ phù hợp tuổi, tránh vật sắc nóng và hỗ trợ ngay khi bé bối rối.',
            'tips' => 'Chia nhỏ từng bước, dùng cùng một lời nhắc mỗi ngày để tạo thói quen.',
            'weekly' => 'Sau khoảng 1 tuần tập đều, bé có thể tự làm một phần công việc, hợp tác tốt hơn và ít cần nhắc lại hơn.',
        ],
    ];

    private const EXERCISES = [
        'gross_motor' => ['Bật nhảy trên thảm', 'Đi thăng bằng trên vạch', 'Lăn bóng qua lại', 'Ném bóng vào rổ', 'Bò qua đường hầm', 'Chuyền bóng hai tay', 'Nhảy qua vật cản thấp', 'Đi bước gấu trong phòng'],
        'fine_motor' => ['Gắp pom-pom bằng kẹp', 'Xâu hạt lớn', 'Bóc dán hình tròn', 'Nặn đất thành viên nhỏ', 'Kẹp quần áo lên dây', 'Xé giấy theo đường thẳng', 'Vặn mở nắp hộp', 'Tô màu trong khung lớn'],
        'communication' => ['Nhìn mắt khi gọi tên', 'Làm theo chỉ dẫn một bước', 'Gọi tên đồ vật quen thuộc', 'Chọn tranh theo yêu cầu', 'Đưa đồ khi được hỏi', 'Bắt chước âm thanh con vật', 'Nói hoặc chỉ để xin thêm', 'Chào tạm biệt bằng lời hoặc cử chỉ'],
        'cognitive' => ['Phân loại màu sắc', 'Xếp vòng theo kích thước', 'Ghép hình đơn giản', 'Tìm đồ vật bị giấu', 'So cặp hình giống nhau', 'Sắp xếp hai bước liên tiếp', 'Nhận biết to nhỏ', 'Chọn đồ theo chức năng'],
        'sensory' => ['Chơi cảm giác với hạt gạo', 'Lăn bóng gai mềm trên tay', 'Ép sâu bằng gối ôm', 'Chuyển nước bằng ca nhỏ', 'Chạm khăn mềm và khăn nhám', 'Tìm đồ chơi trong hộp cảm giác', 'Đi chân trần trên thảm khác nhau', 'Thổi bong bóng xà phòng'],
        'social' => ['Chờ đến lượt khi chơi', 'Đưa bóng cho bạn', 'Bắt chước nét mặt vui buồn', 'Chơi ú òa luân phiên', 'Cùng dọn đồ chơi với người lớn', 'Chọn trò chơi chung', 'Nói cảm ơn khi nhận đồ', 'Chia sẻ một món đồ chơi'],
        'self_care' => ['Tập đánh răng theo từng bước', 'Cất đồ chơi sau khi chơi', 'Rửa tay theo tranh hướng dẫn', 'Mặc áo khoác có trợ giúp', 'Tự xúc bằng thìa', 'Lau miệng sau khi ăn', 'Bỏ rác đúng thùng', 'Chuẩn bị ba lô đi học'],
    ];

    public function run(): void
    {
        $this->removeDuplicateExercises();
        $exercisesBySlug = $this->seedExercises();
        $this->removeDuplicateExercises();
        $combosBySlug = $this->seedCombos($exercisesBySlug);
        $this->seedWeeklyPlans($exercisesBySlug, $combosBySlug);
    }

    private function removeDuplicateExercises(): void
    {
        Exercise::query()
            ->select('title')
            ->groupBy('title')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('title')
            ->each(function (string $title): void {
                $duplicates = Exercise::where('title', $title)
                    ->orderByRaw('description IS NULL')
                    ->orderBy('id')
                    ->get();

                $keeper = $duplicates->first();
                if (!$keeper) {
                    return;
                }

                $duplicates->skip(1)->each(function (Exercise $duplicate) use ($keeper): void {
                    TrainingSessionItem::where('exercise_id', $duplicate->id)
                        ->update(['exercise_id' => $keeper->id]);

                    $duplicate->delete();
                });
            });
    }

    private function seedExercises(): array
    {
        $result = [];

        foreach (self::EXERCISES as $category => $titles) {
            $guidance = self::CATEGORY_GUIDANCE[$category];

            foreach ($titles as $index => $title) {
                $slug = Str::slug($title);
                $minutes = 8 + (($index % 4) * 4);
                $difficulty = $index < 3 ? 'easy' : ($index < 6 ? 'medium' : 'hard');

                $exercise = Exercise::withTrashed()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'title' => $title,
                        'category' => $category,
                        'difficulty' => $difficulty,
                        'instructions' => $this->instructionsFor($title),
                        'description' => $this->descriptionFor($title, $category),
                        'target_skill' => $guidance['target_skill'],
                        'recommended_age' => $index % 2 === 0 ? '3-6 tuổi' : '4-8 tuổi',
                        'required_tools' => $guidance['tools'],
                        'expected_benefits' => $guidance['benefits'],
                        'safety_notes' => $guidance['safety'],
                        'parent_tips' => $guidance['tips'],
                        'weekly_expectation' => $guidance['weekly'],
                        'estimated_minutes' => $minutes,
                        'is_active' => true,
                        'deleted_at' => null,
                    ]
                );

                $exercise->steps()->delete();
                foreach ($this->stepsFor($title) as $stepIndex => $step) {
                    $exercise->steps()->create([
                        'title' => $step['title'],
                        'instruction' => $step['instruction'],
                        'sort_order' => $stepIndex + 1,
                    ]);
                }

                $result[$slug] = $exercise;
            }
        }

        return $result;
    }

    private function seedCombos(array $exercises): array
    {
        $definitions = [
            ['Combo tăng tập trung', 'attention', ['nhin-mat-khi-goi-ten', 'phan-loai-mau-sac', 'xep-vong-theo-kich-thuoc'], 'Mỗi ngày 1 lần, 15-20 phút'],
            ['Combo tăng giao tiếp mắt', 'communication', ['nhin-mat-khi-goi-ten', 'dua-do-khi-duoc-hoi', 'chao-tam-biet-bang-loi-hoac-cu-chi'], '5 buổi mỗi tuần'],
            ['Combo giảm tăng động', 'self_regulation', ['bat-nhay-tren-tham', 'ep-sau-bang-goi-om', 'cho-den-luot-khi-choi'], 'Buổi sáng hoặc trước giờ học'],
            ['Combo vận động buổi sáng', 'gross_motor', ['bat-nhay-tren-tham', 'di-thang-bang-tren-vach', 'chuyen-bong-hai-tay'], 'Mỗi sáng 10-15 phút'],
            ['Combo chuẩn bị đi học', 'self_care', ['rua-tay-theo-tranh-huong-dan', 'mac-ao-khoac-co-tro-giup', 'chuan-bi-ba-lo-di-hoc'], 'Trước giờ đi học'],
            ['Combo giác quan nhẹ nhàng', 'sensory_processing', ['lan-bong-gai-mem-tren-tay', 'cham-khan-mem-va-khan-nham', 'thoi-bong-bong-xa-phong'], 'Khi bé căng thẳng hoặc trước giờ ngủ'],
            ['Combo tăng phối hợp tay chân', 'gross_motor', ['bo-qua-duong-ham', 'nem-bong-vao-ro', 'nhay-qua-vat-can-thap'], '3-4 buổi mỗi tuần'],
            ['Combo kỹ năng tự lập', 'self_care', ['cat-do-choi-sau-khi-choi', 'tu-xuc-bang-thia', 'bo-rac-dung-thung'], 'Lồng ghép trong sinh hoạt hằng ngày'],
        ];

        $result = [];

        foreach ($definitions as [$title, $targetSkill, $exerciseSlugs, $frequency]) {
            $combo = ExerciseCombo::updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'title' => $title,
                    'description' => 'Chuỗi bài tập ngắn giúp phụ huynh tập cùng bé theo một mục tiêu rõ ràng.',
                    'target_skill' => $targetSkill,
                    'estimated_minutes' => collect($exerciseSlugs)->sum(fn ($slug) => isset($exercises[$slug]) ? $exercises[$slug]->estimated_minutes : 0),
                    'difficulty' => 'medium',
                    'recommended_frequency' => $frequency,
                    'parent_instructions' => 'Tập theo đúng thứ tự, nghỉ ngắn giữa các bài và ghi nhận bài nào bé hợp tác tốt nhất.',
                ]
            );

            $combo->exercises()->sync(
                collect($exerciseSlugs)
                    ->filter(fn ($slug) => isset($exercises[$slug]))
                    ->mapWithKeys(fn ($slug, $index) => [$exercises[$slug]->id => ['sort_order' => $index + 1]])
                    ->all()
            );

            $result[$combo->slug] = $combo;
        }

        return $result;
    }

    private function seedWeeklyPlans(array $exercises, array $combos): void
    {
        $plans = [
            ['Bé tăng động nhẹ', 'self_regulation', '3-7 tuổi', 'Tăng vận động có kiểm soát và giúp bé bình tĩnh hơn.', ['combo-giam-tang-dong', 'combo-van-dong-buoi-sang', 'combo-giac-quan-nhe-nhang']],
            ['Bé chậm nói', 'communication', '3-6 tuổi', 'Tăng chú ý chung, bắt chước âm và chủ động giao tiếp.', ['combo-tang-giao-tiep-mat', 'nhin-mat-khi-goi-ten', 'goi-ten-do-vat-quen-thuoc']],
            ['Bé khó tập trung', 'attention', '4-8 tuổi', 'Rèn khả năng ngồi cùng nhiệm vụ, nghe chỉ dẫn và hoàn thành bước ngắn.', ['combo-tang-tap-trung', 'phan-loai-mau-sac', 'ghep-hinh-don-gian']],
            ['Bé né tránh giao tiếp', 'social_interaction', '3-7 tuổi', 'Tập tương tác nhẹ nhàng, chờ lượt và phản hồi với người lớn.', ['combo-tang-giao-tiep-mat', 'cho-den-luot-khi-choi', 'dua-bong-cho-ban']],
            ['Bé yếu vận động thô', 'gross_motor', '3-8 tuổi', 'Cải thiện thăng bằng, sức mạnh thân người và phối hợp vận động.', ['combo-tang-phoi-hop-tay-chan', 'di-thang-bang-tren-vach', 'bo-qua-duong-ham']],
            ['Bé khó chuyển hoạt động', 'self_care', '3-7 tuổi', 'Giúp bé hiểu trình tự, kết thúc hoạt động cũ và bắt đầu hoạt động mới.', ['combo-ky-nang-tu-lap', 'cat-do-choi-sau-khi-choi', 'chuan-bi-ba-lo-di-hoc']],
            ['Bé giác quan nhạy cảm', 'sensory_processing', '3-8 tuổi', 'Tăng chấp nhận cảm giác mới và giảm phản ứng quá mức.', ['combo-giac-quan-nhe-nhang', 'cham-khan-mem-va-khan-nham', 'di-chan-tran-tren-tham-khac-nhau']],
        ];

        foreach ($plans as [$title, $targetCondition, $age, $description, $assignments]) {
            $plan = WeeklyTrainingPlan::updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'title' => $title,
                    'description' => $description,
                    'target_condition' => $targetCondition,
                    'recommended_age' => $age,
                ]
            );

            $plan->items()->delete();
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            foreach ($days as $index => $day) {
                $assignment = $assignments[$index % count($assignments)];
                $isCombo = isset($combos[$assignment]);
                $plan->items()->create([
                    'combo_id' => $isCombo ? $combos[$assignment]->id : null,
                    'exercise_id' => !$isCombo && isset($exercises[$assignment]) ? $exercises[$assignment]->id : null,
                    'day_of_week' => $day,
                    'session_time' => $index % 2 === 0 ? 'morning' : 'evening',
                    'estimated_minutes' => $isCombo ? $combos[$assignment]->estimated_minutes : ($exercises[$assignment]->estimated_minutes ?? 15),
                    'notes' => 'Mục tiêu: tập ngắn, đều và ghi nhận mức hợp tác của bé sau buổi tập.',
                ]);
            }
        }
    }

    private function descriptionFor(string $title, string $category): string
    {
        return "{$title} là bài tập ngắn thuộc nhóm " . ExerciseService::CATEGORIES[$category] . ', giúp phụ huynh tập cùng bé tại nhà bằng hoạt động quen thuộc.';
    }

    private function instructionsFor(string $title): string
    {
        return "Chuẩn bị không gian yên tĩnh, làm mẫu bài {$title}, sau đó mời bé làm theo trong thời lượng ngắn. Nếu bé chưa hợp tác, giảm yêu cầu và hỗ trợ bằng cử chỉ hoặc lời nhắc đơn giản.";
    }

    private function stepsFor(string $title): array
    {
        return [
            ['title' => 'Chuẩn bị', 'instruction' => "Chọn dụng cụ phù hợp và nói trước với bé rằng mình sẽ tập {$title}."],
            ['title' => 'Làm mẫu', 'instruction' => 'Người lớn làm mẫu chậm một lần, dùng câu ngắn và nét mặt vui vẻ.'],
            ['title' => 'Bé thực hiện', 'instruction' => 'Cho bé thử, hỗ trợ vừa đủ và khen ngay khi bé có cố gắng.'],
            ['title' => 'Kết thúc', 'instruction' => 'Dừng khi hết thời gian, nhận xét ngắn điều bé làm tốt và chuyển sang hoạt động thư giãn.'],
        ];
    }
}
