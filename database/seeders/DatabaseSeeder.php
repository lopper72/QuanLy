<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentItem;
use App\Models\BehaviorLog;
use App\Models\Child;
use App\Models\Exercise;
use App\Models\Report;
use App\Models\TrainingSession;
use App\Models\TrainingSessionItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Quản trị viên hệ thống',
                'password' => Hash::make('password'),
            ]
        );

        $childNames = [
            ['full_name' => 'Nguyễn Minh Anh', 'nickname' => 'Minh Anh', 'gender' => 'female', 'diagnosis_level' => 'mild'],
            ['full_name' => 'Trần Gia Bảo', 'nickname' => 'Gia Bảo', 'gender' => 'male', 'diagnosis_level' => 'moderate'],
            ['full_name' => 'Lê Khánh An', 'nickname' => 'Khánh An', 'gender' => 'female', 'diagnosis_level' => 'mild'],
            ['full_name' => 'Phạm Tuấn Kiệt', 'nickname' => 'Tuấn Kiệt', 'gender' => 'male', 'diagnosis_level' => 'severe'],
        ];

        $children = collect();
        foreach ($childNames as $childData) {
            $children->push(Child::factory()->create(array_merge($childData, [
                'notes' => 'Bé hợp tác tốt hơn khi có phần thưởng nhỏ. Cần nhắc lại chỉ dẫn 2-3 lần. Bé dễ mất tập trung sau khoảng 10 phút.',
                'status' => 'active',
            ])));
        }

        $this->call(InterventionProgramSeeder::class);
        $exercises = Exercise::active()->get();

        $sessions = collect();
        foreach (range(1, 14) as $index) {
            $child = $children->random();
            $session = TrainingSession::factory()
                ->for($child)
                ->create([
                    'session_date' => Carbon::today()->subDays(14 - $index)->format('Y-m-d'),
                ]);

            $exerciseIds = $exercises->random(rand(3, 5))->pluck('id');
            foreach ($exerciseIds as $sortOrder => $exerciseId) {
                TrainingSessionItem::factory()->create([
                    'training_session_id' => $session->id,
                    'exercise_id' => $exerciseId,
                    'sort_order' => $sortOrder + 1,
                ]);
            }

            $sessions->push($session);
        }

        $assessments = collect();
        foreach ($children as $child) {
            foreach (range(1, 4) as $index) {
                $assessment = Assessment::factory()
                    ->for($child)
                    ->create([
                        'assessment_date' => Carbon::today()->subWeeks(4 - $index)->format('Y-m-d'),
                    ]);

                AssessmentItem::factory()
                    ->count(rand(3, 6))
                    ->create(['assessment_id' => $assessment->id]);

                $assessments->push($assessment);
            }
        }

        foreach ($children as $child) {
            BehaviorLog::factory()->count(6)->create([
                'child_id' => $child->id,
            ]);

            foreach (range(1, 4) as $index) {
                Report::factory()->create([
                    'child_id' => $child->id,
                    'report_date' => Carbon::today()->subWeeks(4 - $index)->format('Y-m-d'),
                    'report_type' => 'weekly_summary',
                ]);
            }
        }
    }
}
