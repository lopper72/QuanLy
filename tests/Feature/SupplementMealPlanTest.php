<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\MealLog;
use App\Models\MealPlanTemplate;
use App\Models\SupplementLog;
use App\Models\SupplementSchedule;
use App\Models\User;
use Database\Seeders\SupportiveMealPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SupplementMealPlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->actingAs(User::factory()->create());
    }

    public function test_supplement_schedule_can_be_created(): void
    {
        $child = Child::factory()->create(['status' => 'active']);

        $response = $this->post('/supplements', [
            'child_id' => $child->id,
            'name' => 'DHA',
            'type' => 'Bổ sung',
            'dosage_note' => 'Theo nhãn sản phẩm.',
            'timing_type' => 'fixed_time',
            'scheduled_time' => '21:00',
            'frequency' => 'hằng ngày',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('supplements.index'));
        $this->assertDatabaseHas('supplement_schedules', [
            'child_id' => $child->id,
            'name' => 'DHA',
            'scheduled_time' => '21:00',
        ]);
    }

    public function test_supplement_can_be_marked_taken(): void
    {
        $child = Child::factory()->create(['status' => 'active']);
        $schedule = SupplementSchedule::create([
            'child_id' => $child->id,
            'name' => 'DHA',
            'timing_type' => 'fixed_time',
            'scheduled_time' => '21:00',
            'frequency' => 'hằng ngày',
            'status' => 'active',
        ]);

        $this->patch(route('supplements.taken', $schedule))->assertRedirect();

        $this->assertDatabaseHas('supplement_logs', [
            'supplement_schedule_id' => $schedule->id,
            'child_id' => $child->id,
            'status' => 'taken',
        ]);
        $this->assertNotNull(SupplementLog::first()->taken_at);
    }

    public function test_supplement_can_be_skipped(): void
    {
        $child = Child::factory()->create(['status' => 'active']);
        $schedule = SupplementSchedule::create([
            'child_id' => $child->id,
            'name' => 'Vương Não Khang',
            'timing_type' => 'before_meal',
            'meal_relation' => 'before_meal',
            'frequency' => 'hằng ngày',
            'status' => 'active',
        ]);

        $this->patch(route('supplements.skip', $schedule))->assertRedirect();

        $this->assertDatabaseHas('supplement_logs', [
            'supplement_schedule_id' => $schedule->id,
            'child_id' => $child->id,
            'status' => 'skipped',
        ]);
    }

    public function test_meal_plan_templates_load(): void
    {
        $this->seed(SupportiveMealPlanSeeder::class);

        $response = $this->get('/meal-plans');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('MealPlans/Index')
            ->has('templates', 4)
            ->where('templates.0.goal', 'constipation_support')
        );
    }

    public function test_meal_plan_can_be_applied_to_child(): void
    {
        $this->seed(SupportiveMealPlanSeeder::class);
        $child = Child::factory()->create(['status' => 'active']);
        $template = MealPlanTemplate::first();

        $this->post(route('mealPlans.apply'), [
            'child_id' => $child->id,
            'meal_plan_template_id' => $template->id,
        ])->assertRedirect();

        $this->assertGreaterThan(0, MealLog::where('child_id', $child->id)->count());
    }

    public function test_meal_log_can_be_created(): void
    {
        $child = Child::factory()->create(['status' => 'active']);

        $this->post(route('mealPlans.logs.store'), [
            'child_id' => $child->id,
            'status' => 'done',
            'stool_note' => 'Phân mềm, không đau.',
            'water_note' => 'Uống nước chia nhỏ trong ngày.',
        ])->assertRedirect();

        $this->assertDatabaseHas('meal_logs', [
            'child_id' => $child->id,
            'status' => 'done',
            'stool_note' => 'Phân mềm, không đau.',
        ]);
    }

    public function test_dashboard_shows_today_supplement_and_meal_reminders(): void
    {
        $this->seed(SupportiveMealPlanSeeder::class);
        $child = Child::factory()->create(['status' => 'active']);
        SupplementSchedule::create([
            'child_id' => $child->id,
            'name' => 'DHA',
            'timing_type' => 'fixed_time',
            'scheduled_time' => '21:00',
            'frequency' => 'hằng ngày',
            'status' => 'active',
        ]);
        MealLog::create([
            'child_id' => $child->id,
            'meal_date' => today()->toDateString(),
            'status' => 'done',
            'stool_note' => 'Phân mềm.',
        ]);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->has('today_supplement_reminders', 1)
            ->where('today_supplement_reminders.0.name', 'DHA')
            ->has('today_meal_reminders')
            ->where('latest_stool_note.stool_note', 'Phân mềm.')
        );
    }
}
