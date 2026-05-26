<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\MealPlanItem;
use App\Models\MealPlanTemplate;
use App\Models\SchedulerRun;
use App\Models\TelegramMessage;
use App\Models\TelegramSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramSchedulerDiagnosticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Carbon::setTestNow(Carbon::parse('2026-05-26 14:00:00'));
    }

    public function test_telegram_scheduler_demo_page_loads(): void
    {
        $this->fakeTelegram();
        $this->createDinnerPlan();

        $this->actingAs(User::factory()->create())
            ->get('/telegram')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Telegram/Index')
                ->has('schedulerDiagnostics')
                ->has('schedulerDiagnostics.cron_hint')
            );
    }

    public function test_telegram_test_center_uses_dinner_suggestion_label(): void
    {
        $source = file_get_contents(resource_path('js/Pages/Telegram/Index.vue'));

        $this->assertStringContainsString('Test gợi ý bữa tối lúc 14:00', $source);
        $this->assertStringContainsString('Gửi gợi ý bữa tối ngay', $source);
        $this->assertStringNotContainsString('Test nhắc lịch ăn trước 30 phút', $source);
    }

    public function test_demo_create_today_data_works(): void
    {
        $this->fakeTelegram();
        Child::factory()->create(['status' => Child::STATUS_ACTIVE]);

        $this->actingAs(User::factory()->create())
            ->post('/telegram/demo/create-today-data')
            ->assertRedirect();

        $this->assertSame(1, \App\Models\TrainingSession::whereDate('session_date', today())->count());
        $this->assertDatabaseHas('supplement_schedules', ['name' => 'DHA demo']);
        $this->assertDatabaseHas('meal_plan_templates', ['title' => 'Demo lịch ăn Telegram']);
        $this->assertDatabaseHas('telegram_reminder_logs', ['status' => 'pending']);
    }

    public function test_send_dinner_now_route_requires_auth(): void
    {
        $this->post('/telegram/test/send-dinner-now')->assertRedirect('/login');
    }

    public function test_send_dinner_now_creates_outbound_log(): void
    {
        $this->fakeTelegram();
        $child = $this->createDinnerPlan();

        $this->actingAs(User::factory()->create())
            ->post('/telegram/test/send-dinner-now', ['child_id' => $child->id])
            ->assertRedirect();

        $this->assertDatabaseHas('telegram_messages', [
            'direction' => TelegramMessage::DIRECTION_OUTBOUND,
            'message_type' => 'meal_suggestion',
            'related_child_id' => $child->id,
        ]);
    }

    public function test_simulate_an_command_returns_message(): void
    {
        $this->fakeTelegram();
        $child = $this->createDinnerPlan();

        $this->actingAs(User::factory()->create())
            ->post('/telegram/test/simulate-an-command', ['child_id' => $child->id])
            ->assertRedirect();

        $this->assertStringContainsString(
            'Lịch ăn uống hôm nay',
            TelegramMessage::where('direction', TelegramMessage::DIRECTION_OUTBOUND)->latest('id')->first()->message_text
        );
    }

    public function test_simulate_doimon_command_returns_alternative(): void
    {
        $this->fakeTelegram();
        $child = $this->createDinnerPlan();

        $this->actingAs(User::factory()->create())
            ->post('/telegram/test/simulate-doimon-command', ['child_id' => $child->id])
            ->assertRedirect();

        $this->assertStringContainsString(
            'Gợi ý món thay thế',
            TelegramMessage::where('direction', TelegramMessage::DIRECTION_OUTBOUND)->latest('id')->first()->message_text
        );
    }

    public function test_scheduler_run_is_logged(): void
    {
        $this->fakeTelegram();
        $this->createDinnerPlan();

        $this->artisan('telegram:send-dinner-suggestions')->assertExitCode(0);

        $this->assertDatabaseHas('scheduler_runs', [
            'command' => 'telegram:send-dinner-suggestions',
            'status' => SchedulerRun::STATUS_SUCCESS,
        ]);
    }

    public function test_duplicate_dinner_suggestion_is_skipped_safely(): void
    {
        $this->fakeTelegram();
        $child = $this->createDinnerPlan();

        $this->artisan('telegram:send-dinner-suggestions')->assertExitCode(0);
        $this->artisan('telegram:send-dinner-suggestions')->assertExitCode(0);

        $this->assertSame(1, TelegramMessage::where('message_type', 'meal_suggestion')->where('related_child_id', $child->id)->count());
    }

    private function fakeTelegram(): void
    {
        TelegramSetting::create([
            'bot_token' => '123456:SECRET',
            'default_chat_id' => '6005717884',
            'enabled' => true,
        ]);

        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]], 200),
        ]);
    }

    private function createDinnerPlan(): Child
    {
        $child = Child::factory()->create([
            'full_name' => 'Nguyễn Minh Anh',
            'status' => Child::STATUS_ACTIVE,
        ]);

        $template = MealPlanTemplate::create([
            'title' => 'Tuần hỗ trợ tiêu hóa',
            'goal' => 'constipation_support',
            'description' => 'Thực đơn mẫu.',
            'week_number' => 1,
            'is_active' => true,
        ]);

        MealPlanItem::create([
            'meal_plan_template_id' => $template->id,
            'day_of_week' => today()->dayOfWeekIso,
            'meal_time' => 'dinner',
            'scheduled_time' => '18:00',
            'title' => 'Bữa tối mềm',
            'foods_json' => ['Cơm mềm', 'Canh bí đỏ', 'Cá hấp', 'Thanh long chín'],
            'constipation_support_note' => 'Có thể hỗ trợ tiêu hóa nếu phù hợp với bé.',
            'parent_tip' => 'Cho bé thử từng lượng nhỏ.',
        ]);

        return $child;
    }
}
