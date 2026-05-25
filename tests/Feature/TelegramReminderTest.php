<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\MealPlanItem;
use App\Models\MealPlanTemplate;
use App\Models\SupplementSchedule;
use App\Models\TelegramReminderLog;
use App\Models\TelegramSetting;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\TelegramReminderService;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TelegramReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Carbon::setTestNow(Carbon::parse('2026-05-25 08:00:00'));
        config(['services.telegram.webhook_secret' => null]);
    }

    public function test_meal_plan_item_can_store_scheduled_time(): void
    {
        $template = MealPlanTemplate::create([
            'title' => 'Tuần hỗ trợ tiêu hóa',
            'goal' => 'constipation_support',
            'description' => 'Thực đơn mẫu.',
            'is_active' => true,
        ]);

        $item = MealPlanItem::create([
            'meal_plan_template_id' => $template->id,
            'day_of_week' => today()->dayOfWeekIso,
            'meal_time' => 'breakfast',
            'scheduled_time' => '07:00',
            'title' => 'Bữa sáng mềm',
            'foods_json' => ['Cháo yến mạch'],
        ]);

        $this->assertSame('07:00', substr($item->refresh()->scheduled_time, 0, 5));
    }

    public function test_training_reminder_due_30_minutes_before_is_created_and_sent(): void
    {
        $this->fakeTelegram();
        $child = Child::factory()->create(['status' => 'active']);
        $session = TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => today()->toDateString(),
            'scheduled_time' => now()->addMinutes(30)->format('H:i'),
            'status' => 'planned',
        ]);

        $result = app(TelegramReminderService::class)->sendDueReminders();

        $this->assertSame(1, $result['sent']);
        $this->assertDatabaseHas('telegram_reminder_logs', [
            'reminder_type' => 'training',
            'related_id' => $session->id,
            'status' => 'sent',
        ]);
    }

    public function test_meal_reminder_due_30_minutes_before_is_created_and_sent(): void
    {
        $this->fakeTelegram();
        Child::factory()->create(['status' => 'active']);
        $item = $this->mealItem(now()->addMinutes(30)->format('H:i'));

        $result = app(TelegramReminderService::class)->sendDueReminders();

        $this->assertSame(1, $result['sent']);
        $this->assertDatabaseHas('telegram_reminder_logs', [
            'reminder_type' => 'meal',
            'related_id' => $item->id,
            'status' => 'sent',
        ]);
    }

    public function test_supplement_reminder_due_30_minutes_before_is_created_and_sent(): void
    {
        $this->fakeTelegram();
        $child = Child::factory()->create(['status' => 'active']);
        $schedule = SupplementSchedule::create([
            'child_id' => $child->id,
            'name' => 'DHA',
            'timing_type' => 'fixed_time',
            'scheduled_time' => now()->addMinutes(30)->format('H:i'),
            'frequency' => 'hằng ngày',
            'status' => 'active',
        ]);

        $result = app(TelegramReminderService::class)->sendDueReminders();

        $this->assertSame(1, $result['sent']);
        $this->assertDatabaseHas('telegram_reminder_logs', [
            'reminder_type' => 'supplement',
            'related_id' => $schedule->id,
            'status' => 'sent',
        ]);
    }

    public function test_duplicate_reminder_is_not_sent_twice(): void
    {
        $this->fakeTelegram();
        $child = Child::factory()->create(['status' => 'active']);
        TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => today()->toDateString(),
            'scheduled_time' => now()->addMinutes(30)->format('H:i'),
            'status' => 'planned',
        ]);

        app(TelegramReminderService::class)->sendDueReminders();
        $result = app(TelegramReminderService::class)->sendDueReminders();

        $this->assertSame(0, $result['sent']);
        $this->assertSame(1, TelegramReminderLog::where('status', 'sent')->count());
    }

    public function test_reminder_skipped_if_child_is_voided(): void
    {
        $this->fakeTelegram();
        $child = Child::factory()->create(['status' => 'voided']);
        TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => today()->toDateString(),
            'scheduled_time' => now()->addMinutes(30)->format('H:i'),
            'status' => 'planned',
        ]);

        $result = app(TelegramReminderService::class)->sendDueReminders();

        $this->assertSame(0, $result['sent']);
        $this->assertDatabaseCount('telegram_reminder_logs', 0);
    }

    public function test_reminder_skipped_if_no_telegram_chat_id(): void
    {
        TelegramSetting::create(['bot_token' => 'test-token', 'enabled' => true]);
        $child = Child::factory()->create(['status' => 'active']);
        TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => today()->toDateString(),
            'scheduled_time' => now()->addMinutes(30)->format('H:i'),
            'status' => 'planned',
        ]);

        $result = app(TelegramReminderService::class)->sendDueReminders();

        $this->assertSame(0, $result['sent']);
        $this->assertDatabaseCount('telegram_reminder_logs', 0);
    }

    public function test_supplement_callback_taken_creates_supplement_log(): void
    {
        TelegramSetting::create(['webhook_secret' => null, 'enabled' => true]);
        $child = Child::factory()->create(['status' => 'active']);
        $schedule = SupplementSchedule::create([
            'child_id' => $child->id,
            'name' => 'DHA',
            'timing_type' => 'fixed_time',
            'scheduled_time' => '21:00',
            'frequency' => 'hằng ngày',
            'status' => 'active',
        ]);

        $this->postJson('/telegram/webhook', [
            'callback_query' => [
                'id' => 'callback-1',
                'from' => ['id' => 123, 'first_name' => 'Phụ huynh'],
                'message' => ['date' => now()->timestamp, 'chat' => ['id' => '6005717884']],
                'data' => "supplement_schedule:{$schedule->id}:taken",
            ],
        ])->assertOk();

        $this->assertDatabaseHas('supplement_logs', [
            'supplement_schedule_id' => $schedule->id,
            'child_id' => $child->id,
            'status' => 'taken',
        ]);
        $this->assertDatabaseHas('telegram_messages', [
            'message_type' => 'supplement_callback',
            'callback_data' => "supplement_schedule:{$schedule->id}:taken",
        ]);
    }

    public function test_telegram_test_page_loads(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/telegram')->assertInertia(fn (Assert $page) => $page
            ->component('Telegram/Index')
            ->has('reminderTest')
        );
    }

    public function test_test_reminder_endpoints_require_auth(): void
    {
        $this->post('/telegram/test/reminder/training')->assertRedirect('/login');
    }

    public function test_webhook_training_callback_still_works(): void
    {
        TelegramSetting::create(['webhook_secret' => null, 'enabled' => true]);
        $child = Child::factory()->create(['status' => 'active']);
        $session = TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => today()->toDateString(),
            'scheduled_time' => '20:00',
            'status' => 'planned',
        ]);

        $this->postJson('/telegram/webhook', [
            'callback_query' => [
                'id' => 'callback-2',
                'from' => ['id' => 123, 'first_name' => 'Phụ huynh'],
                'message' => ['date' => now()->timestamp, 'chat' => ['id' => '6005717884']],
                'data' => "training_session:{$session->id}:completed",
            ],
        ])->assertOk();

        $this->assertDatabaseHas('training_sessions', [
            'id' => $session->id,
            'status' => 'completed',
        ]);
    }

    private function fakeTelegram(): void
    {
        TelegramSetting::create([
            'bot_token' => 'test-token',
            'default_chat_id' => '6005717884',
            'enabled' => true,
        ]);

        Http::fake([
            'https://api.telegram.org/*' => Http::response([
                'ok' => true,
                'result' => ['message_id' => 1],
            ]),
        ]);
    }

    private function mealItem(string $time): MealPlanItem
    {
        $template = MealPlanTemplate::create([
            'title' => 'Tuần hỗ trợ tiêu hóa',
            'goal' => 'constipation_support',
            'description' => 'Thực đơn mẫu.',
            'is_active' => true,
        ]);

        return MealPlanItem::create([
            'meal_plan_template_id' => $template->id,
            'day_of_week' => today()->dayOfWeekIso,
            'meal_time' => 'breakfast',
            'scheduled_time' => $time,
            'title' => 'Bữa sáng mềm',
            'foods_json' => ['Cháo yến mạch'],
        ]);
    }
}
