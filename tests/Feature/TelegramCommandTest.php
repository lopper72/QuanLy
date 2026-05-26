<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Exercise;
use App\Models\MealLog;
use App\Models\MealPlanItem;
use App\Models\MealPlanTemplate;
use App\Models\SupplementLog;
use App\Models\SupplementSchedule;
use App\Models\TelegramMessage;
use App\Models\TelegramSetting;
use App\Models\TrainingSession;
use App\Models\TrainingSessionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['services.telegram.webhook_secret' => null]);
        $this->fakeTelegram();
    }

    public function test_menu_command_returns_parent_command_list(): void
    {
        $this->postCommand('/menu')->assertOk();

        $message = TelegramMessage::where('direction', TelegramMessage::DIRECTION_OUTBOUND)->latest('id')->first();
        $this->assertStringContainsString('Menu hỗ trợ phụ huynh', $message->message_text);
        $this->assertStringContainsString('/an', $message->message_text);
        $this->assertStringContainsString('/doimon', $message->message_text);
        $this->assertStringContainsString('/ditoilet', $message->message_text);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/sendMessage')
            && data_get($request->data(), 'reply_markup.inline_keyboard.0.0.callback_data') === 'telegram_menu:today_meal'
        );
    }

    public function test_full_command_returns_training_meal_and_supplement_schedule(): void
    {
        $child = $this->createChildSchedule();

        $this->postCommand('/full')->assertOk();

        $message = TelegramMessage::where('direction', TelegramMessage::DIRECTION_OUTBOUND)->latest('id')->first();
        $this->assertStringContainsString("Lịch hôm nay của bé {$child->full_name}", $message->message_text);
        $this->assertStringContainsString('Bật nhảy trên thảm', $message->message_text);
        $this->assertStringContainsString('Cháo yến mạch', $message->message_text);
        $this->assertStringContainsString('DHA', $message->message_text);
    }

    public function test_supplement_command_returns_keyboard_buttons(): void
    {
        $this->createChildSchedule();

        $this->postCommand('/thuoc')->assertOk();

        Http::assertSent(fn ($request) => str_contains($request->url(), '/sendMessage')
            && $request['chat_id'] === '6005717884'
            && str_contains($request['text'], 'Lịch bổ sung hôm nay')
            && data_get($request->data(), 'reply_markup.inline_keyboard.0.0.callback_data') !== null
        );
    }

    public function test_meal_command_returns_scheduled_time(): void
    {
        $this->createChildSchedule();

        $this->postCommand('/an')->assertOk();

        $message = TelegramMessage::where('direction', TelegramMessage::DIRECTION_OUTBOUND)->latest('id')->first();
        $this->assertStringContainsString('14:00', $message->message_text);
        $this->assertStringContainsString('07:00', $message->message_text);
        $this->assertStringContainsString('Cháo yến mạch', $message->message_text);
    }

    public function test_doimon_command_returns_alternative_dinner(): void
    {
        $this->createChildSchedule();

        $this->postCommand('/doimon')->assertOk();

        $message = TelegramMessage::where('direction', TelegramMessage::DIRECTION_OUTBOUND)->latest('id')->first();
        $this->assertStringContainsString('Gợi ý món thay thế cho bữa tối hôm nay', $message->message_text);
    }

    public function test_tap_command_returns_today_training(): void
    {
        $this->createChildSchedule();

        $this->postCommand('/tap')->assertOk();

        Http::assertSent(fn ($request) => str_contains($request->url(), '/sendMessage')
            && str_contains($request['text'], 'Lịch tập hôm nay')
            && str_contains($request['text'], 'Bật nhảy trên thảm')
        );
    }

    public function test_progress_command_returns_today_summary(): void
    {
        $this->createChildSchedule([
            'training_status' => 'completed',
            'supplement_status' => 'taken',
            'meal_status' => 'done',
        ]);

        $this->postCommand('/tiendo')->assertOk();

        $message = TelegramMessage::where('direction', TelegramMessage::DIRECTION_OUTBOUND)->latest('id')->first();
        $this->assertStringContainsString('Tóm tắt gần đây', $message->message_text);
        $this->assertStringContainsString('Bài tập: 1/1 đã hoàn thành hôm nay', $message->message_text);
        $this->assertStringContainsString('Bổ sung: 1/1 đã uống', $message->message_text);
    }

    public function test_commands_exclude_voided_children(): void
    {
        $activeChild = $this->createChildSchedule(['child_name' => 'Bé Đang Can Thiệp']);
        $voidedChild = $this->createChildSchedule([
            'child_name' => 'Bé Ngừng Can Thiệp',
            'child_status' => Child::STATUS_VOIDED,
        ]);

        $this->postCommand('/full')->assertOk();

        $messages = TelegramMessage::where('direction', TelegramMessage::DIRECTION_OUTBOUND)
            ->pluck('message_text')
            ->implode("\n");

        $this->assertStringContainsString($activeChild->full_name, $messages);
        $this->assertStringNotContainsString($voidedChild->full_name, $messages);
    }

    public function test_toilet_callback_creates_daily_tracking_log_without_duplicates(): void
    {
        $child = $this->createChildSchedule();

        $this->postCallback('toilet:hard')->assertOk();
        $this->postCallback('toilet:soft')->assertOk();

        $this->assertSame(1, MealLog::query()
            ->where('child_id', $child->id)
            ->whereDate('meal_date', today())
            ->whereNull('meal_plan_item_id')
            ->count());

        $this->assertDatabaseHas('meal_logs', [
            'child_id' => $child->id,
            'meal_plan_item_id' => null,
            'stool_note' => 'Phân mềm, không đau.',
        ]);
    }

    public function test_water_callback_creates_daily_tracking_log(): void
    {
        $child = $this->createChildSchedule();

        $this->postCallback('water:little')->assertOk();

        $this->assertDatabaseHas('meal_logs', [
            'child_id' => $child->id,
            'meal_plan_item_id' => null,
            'water_note' => 'Hôm nay bé uống nước ít.',
        ]);
    }

    public function test_menu_buttons_trigger_matching_command_services(): void
    {
        $this->createChildSchedule();

        $this->postCallback('telegram_menu:today_meal')->assertOk();

        $this->assertDatabaseHas('telegram_messages', [
            'direction' => TelegramMessage::DIRECTION_INBOUND,
            'message_type' => 'menu_callback',
            'callback_data' => 'telegram_menu:today_meal',
        ]);

        $message = TelegramMessage::where('direction', TelegramMessage::DIRECTION_OUTBOUND)->latest('id')->first();
        $this->assertStringContainsString('Lịch ăn uống hôm nay', $message->message_text);
    }

    public function test_id_and_hotro_commands_work(): void
    {
        $this->postCommand('/id')->assertOk();
        $this->postCommand('/hotro Bé cần hỗ trợ khi tập')->assertOk();

        $this->assertDatabaseHas('telegram_messages', [
            'telegram_chat_id' => '6005717884',
            'message_type' => 'support_request',
            'message_text' => 'Bé cần hỗ trợ khi tập',
        ]);

        $messages = TelegramMessage::where('direction', TelegramMessage::DIRECTION_OUTBOUND)
            ->pluck('message_text')
            ->implode("\n");

        $this->assertStringContainsString('Mã hội thoại Telegram của bạn là: 6005717884', $messages);
        $this->assertStringContainsString('Đã ghi nhận yêu cầu hỗ trợ', $messages);
    }

    public function test_set_commands_artisan_command_calls_telegram_api(): void
    {
        $this->artisan('telegram:set-commands')->assertExitCode(0);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/setMyCommands')
            && collect($request['commands'])->contains(fn ($command) => $command['command'] === 'menu')
            && collect($request['commands'])->contains(fn ($command) => $command['command'] === 'ditoilet')
        );
    }

    public function test_quick_command_test_route_requires_auth(): void
    {
        $this->post('/telegram/test/quick-command', ['command' => '/menu'])
            ->assertRedirect('/login');
    }

    public function test_quick_command_test_route_sends_command_when_authenticated(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/telegram/test/quick-command', ['command' => '/menu'])
            ->assertRedirect();

        $this->assertDatabaseHas('telegram_messages', [
            'direction' => TelegramMessage::DIRECTION_INBOUND,
            'message_text' => '/menu',
        ]);
    }

    private function postCommand(string $command)
    {
        return $this->postJson('/telegram/webhook', [
            'message' => [
                'date' => now()->timestamp,
                'text' => $command,
                'chat' => ['id' => 6005717884],
                'from' => ['id' => 6005717884, 'first_name' => 'Phụ huynh'],
            ],
        ]);
    }

    private function postCallback(string $data)
    {
        return $this->postJson('/telegram/webhook', [
            'callback_query' => [
                'id' => 'callback-test',
                'data' => $data,
                'from' => ['id' => 6005717884, 'first_name' => 'Phụ huynh'],
                'message' => [
                    'date' => now()->timestamp,
                    'chat' => ['id' => 6005717884],
                ],
            ],
        ]);
    }

    private function fakeTelegram(): void
    {
        TelegramSetting::create([
            'bot_token' => '123456:SECRET',
            'default_chat_id' => '6005717884',
            'enabled' => true,
        ]);

        Http::fake([
            'https://api.telegram.org/*' => Http::response([
                'ok' => true,
                'result' => ['message_id' => 1],
            ], 200),
        ]);
    }

    private function createChildSchedule(array $overrides = []): Child
    {
        $child = Child::factory()->create([
            'full_name' => $overrides['child_name'] ?? 'Nguyễn Minh Anh',
            'status' => $overrides['child_status'] ?? Child::STATUS_ACTIVE,
        ]);

        $session = TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => today()->toDateString(),
            'scheduled_time' => '08:00',
            'status' => 'planned',
        ]);
        $exercise = Exercise::factory()->create(['title' => 'Bật nhảy trên thảm']);
        TrainingSessionItem::factory()->create([
            'training_session_id' => $session->id,
            'exercise_id' => $exercise->id,
            'duration_minutes' => 10,
            'completion_status' => $overrides['training_status'] ?? 'not_started',
        ]);

        $supplement = SupplementSchedule::create([
            'child_id' => $child->id,
            'name' => 'DHA',
            'timing_type' => 'fixed_time',
            'scheduled_time' => '21:00',
            'frequency' => 'hằng ngày',
            'status' => 'active',
        ]);

        if (isset($overrides['supplement_status'])) {
            SupplementLog::create([
                'supplement_schedule_id' => $supplement->id,
                'child_id' => $child->id,
                'scheduled_for' => today()->toDateString(),
                'status' => $overrides['supplement_status'],
                'taken_at' => $overrides['supplement_status'] === 'taken' ? now() : null,
            ]);
        }

        $template = MealPlanTemplate::create([
            'title' => 'Tuần hỗ trợ tiêu hóa',
            'goal' => 'constipation_support',
            'description' => 'Thực đơn mẫu.',
            'week_number' => 1,
            'is_active' => true,
        ]);
        $mealItem = MealPlanItem::create([
            'meal_plan_template_id' => $template->id,
            'day_of_week' => today()->dayOfWeekIso,
            'meal_time' => 'breakfast',
            'scheduled_time' => '07:00',
            'title' => 'Bữa sáng mềm',
            'foods_json' => ['Cháo yến mạch'],
            'constipation_support_note' => 'Hỗ trợ tăng chất xơ nhẹ nhàng.',
        ]);

        if (isset($overrides['meal_status'])) {
            MealLog::create([
                'child_id' => $child->id,
                'meal_plan_item_id' => $mealItem->id,
                'meal_date' => today()->toDateString(),
                'scheduled_for' => today()->setTimeFromTimeString('07:00'),
                'status' => $overrides['meal_status'],
            ]);
        }

        return $child;
    }
}
