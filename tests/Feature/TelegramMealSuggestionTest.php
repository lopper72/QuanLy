<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\MealPlanItem;
use App\Models\MealPlanTemplate;
use App\Models\TelegramMealSuggestionLog;
use App\Models\TelegramMessage;
use App\Models\TelegramSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramMealSuggestionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Carbon::setTestNow(Carbon::parse('2026-05-26 14:00:00'));
        config(['services.telegram.webhook_secret' => null]);
    }

    public function test_daily_14_command_sends_suggestion_to_active_child(): void
    {
        $this->fakeTelegram();
        $child = $this->createDinnerPlan();

        $this->artisan('telegram:send-dinner-suggestions')->assertExitCode(0);

        $this->assertDatabaseHas('telegram_meal_suggestion_logs', [
            'child_id' => $child->id,
            'telegram_chat_id' => '6005717884',
            'status' => 'sent',
        ]);
        $this->assertDatabaseHas('telegram_messages', [
            'direction' => TelegramMessage::DIRECTION_OUTBOUND,
            'message_type' => 'meal_suggestion',
            'related_child_id' => $child->id,
        ]);
    }

    public function test_no_duplicate_dinner_suggestion_same_day(): void
    {
        $this->fakeTelegram();
        $child = $this->createDinnerPlan();

        $this->artisan('telegram:send-dinner-suggestions')->assertExitCode(0);
        $this->artisan('telegram:send-dinner-suggestions')->assertExitCode(0);

        $this->assertSame(1, TelegramMealSuggestionLog::where('child_id', $child->id)->count());
        $this->assertSame(1, TelegramMessage::where('message_type', 'meal_suggestion')->count());
    }

    public function test_dinner_suggestion_message_uses_14h_preparation_wording(): void
    {
        $this->fakeTelegram();
        $this->createDinnerPlan();

        $this->artisan('telegram:send-dinner-suggestions')->assertExitCode(0);

        $message = TelegramMessage::where('message_type', 'meal_suggestion')->latest('id')->first();
        $this->assertStringNotContainsString('30', $message->message_text);
        $this->assertStringContainsString('14:00', $message->message_text);
        $this->assertStringContainsString('18:00', $message->message_text);
        $this->assertStringContainsString('/doimon', $message->message_text);
    }

    public function test_voided_child_is_skipped(): void
    {
        $this->fakeTelegram();
        $this->createDinnerPlan(['status' => Child::STATUS_VOIDED]);

        $this->artisan('telegram:send-dinner-suggestions')->assertExitCode(0);

        $this->assertDatabaseCount('telegram_meal_suggestion_logs', 0);
        $this->assertSame(0, TelegramMessage::where('message_type', 'meal_suggestion')->count());
    }

    public function test_child_without_chat_id_is_skipped(): void
    {
        TelegramSetting::create(['bot_token' => '123456:SECRET', 'enabled' => true]);
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true], 200)]);
        $this->createDinnerPlan();

        $this->artisan('telegram:send-dinner-suggestions')->assertExitCode(0);

        $this->assertDatabaseCount('telegram_meal_suggestion_logs', 0);
    }

    public function test_doimon_command_returns_alternative_menu(): void
    {
        $this->fakeTelegram();
        $this->createDinnerPlan();

        $this->postCommand('/doimon')->assertOk();

        $message = TelegramMessage::where('direction', TelegramMessage::DIRECTION_OUTBOUND)->latest('id')->first();
        $this->assertStringContainsString('1.', $message->message_text);
        $this->assertStringContainsString('2.', $message->message_text);
    }

    public function test_an_command_returns_today_meal_schedule(): void
    {
        $this->fakeTelegram();
        $this->createDinnerPlan();

        $this->postCommand('/an')->assertOk();

        $message = TelegramMessage::where('direction', TelegramMessage::DIRECTION_OUTBOUND)->latest('id')->first();
        $this->assertStringContainsString('14:00', $message->message_text);
        $this->assertStringContainsString('18:00', $message->message_text);
    }

    public function test_meal_suggestion_change_callback_sends_alternative(): void
    {
        $this->fakeTelegram();
        $child = $this->createDinnerPlan();

        $this->postMealCallback($child, 'change')->assertOk();

        $this->assertDatabaseHas('telegram_messages', [
            'message_type' => 'meal_suggestion_callback',
            'callback_data' => "meal_suggestion:{$child->id}:2026-05-26:change",
        ]);
        $this->assertStringContainsString('1.', TelegramMessage::where('direction', 'outbound')->latest('id')->first()->message_text);
    }

    public function test_meal_suggestion_view_callback_sends_today_schedule(): void
    {
        $this->fakeTelegram();
        $child = $this->createDinnerPlan();

        $this->postMealCallback($child, 'view')->assertOk();

        $this->assertStringContainsString('14:00', TelegramMessage::where('direction', 'outbound')->latest('id')->first()->message_text);
    }

    public function test_meal_suggestion_prepared_callback_logs_prepared_status(): void
    {
        $this->fakeTelegram();
        $child = $this->createDinnerPlan();

        $this->postMealCallback($child, 'prepared')->assertOk();

        $this->assertDatabaseHas('telegram_meal_suggestion_logs', [
            'child_id' => $child->id,
            'telegram_chat_id' => '6005717884',
            'status' => 'prepared',
        ]);
        $this->assertStringContainsString('chuan bi', str(TelegramMessage::where('direction', 'outbound')->latest('id')->first()->message_text)->ascii()->lower()->toString());
    }

    public function test_telegram_timeline_shows_inbound_command_and_outbound_reply(): void
    {
        $this->fakeTelegram();
        $this->createDinnerPlan();

        $this->postCommand('/doimon')->assertOk();

        $this->assertDatabaseHas('telegram_messages', [
            'direction' => TelegramMessage::DIRECTION_INBOUND,
            'telegram_chat_id' => '6005717884',
            'message_text' => '/doimon',
        ]);
        $this->assertDatabaseHas('telegram_messages', [
            'direction' => TelegramMessage::DIRECTION_OUTBOUND,
            'telegram_chat_id' => '6005717884',
        ]);
    }

    public function test_telegram_test_endpoints_require_auth(): void
    {
        $this->post('/telegram/test/meal-suggestion/dinner')->assertRedirect('/login');
    }

    public function test_telegram_test_page_loads_meal_suggestion_tools(): void
    {
        $this->fakeTelegram();
        $this->createDinnerPlan();

        $this->actingAs(User::factory()->create())
            ->get('/telegram')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Telegram/Index')
                ->has('mealSuggestionTest.children')
                ->has('mealSuggestionTest.preview')
            );
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

    private function createDinnerPlan(array $childAttributes = []): Child
    {
        $child = Child::factory()->create(array_merge([
            'full_name' => 'NguyÃƒÂ¡Ã‚Â»Ã¢â‚¬Â¦n Minh Anh',
            'status' => Child::STATUS_ACTIVE,
        ], $childAttributes));

        $template = MealPlanTemplate::create([
            'title' => 'TuÃƒÂ¡Ã‚ÂºÃ‚Â§n hÃƒÂ¡Ã‚Â»Ã¢â‚¬â€ trÃƒÂ¡Ã‚Â»Ã‚Â£ tiÃƒÆ’Ã‚Âªu hÃƒÆ’Ã‚Â³a',
            'goal' => 'constipation_support',
            'description' => 'ThÃƒÂ¡Ã‚Â»Ã‚Â±c Ãƒâ€žÃ¢â‚¬ËœÃƒâ€ Ã‚Â¡n mÃƒÂ¡Ã‚ÂºÃ‚Â«u.',
            'week_number' => 1,
            'is_active' => true,
        ]);

        MealPlanItem::create([
            'meal_plan_template_id' => $template->id,
            'day_of_week' => today()->dayOfWeekIso,
            'meal_time' => 'dinner',
            'scheduled_time' => '18:00',
            'title' => 'BÃƒÂ¡Ã‚Â»Ã‚Â¯a tÃƒÂ¡Ã‚Â»Ã¢â‚¬Ëœi mÃƒÂ¡Ã‚Â»Ã‚Âm',
            'foods_json' => ['CÃƒâ€ Ã‚Â¡m mÃƒÂ¡Ã‚Â»Ã‚Âm', 'Canh bÃƒÆ’Ã‚Â­ Ãƒâ€žÃ¢â‚¬ËœÃƒÂ¡Ã‚Â»Ã‚Â', 'CÃƒÆ’Ã‚Â¡ hÃƒÂ¡Ã‚ÂºÃ‚Â¥p', 'Thanh long chÃƒÆ’Ã‚Â­n'],
            'constipation_support_note' => 'CÃƒÆ’Ã‚Â³ thÃƒÂ¡Ã‚Â»Ã†â€™ hÃƒÂ¡Ã‚Â»Ã¢â‚¬â€ trÃƒÂ¡Ã‚Â»Ã‚Â£ tiÃƒÆ’Ã‚Âªu hÃƒÆ’Ã‚Â³a nÃƒÂ¡Ã‚ÂºÃ‚Â¿u phÃƒÆ’Ã‚Â¹ hÃƒÂ¡Ã‚Â»Ã‚Â£p vÃƒÂ¡Ã‚Â»Ã¢â‚¬Âºi bÃƒÆ’Ã‚Â©.',
            'parent_tip' => 'Cho bÃƒÆ’Ã‚Â© thÃƒÂ¡Ã‚Â»Ã‚Â­ tÃƒÂ¡Ã‚Â»Ã‚Â«ng lÃƒâ€ Ã‚Â°ÃƒÂ¡Ã‚Â»Ã‚Â£ng nhÃƒÂ¡Ã‚Â»Ã‚Â.',
        ]);

        return $child;
    }

    private function postCommand(string $command)
    {
        return $this->postJson('/telegram/webhook', [
            'message' => [
                'date' => now()->timestamp,
                'text' => $command,
                'chat' => ['id' => 6005717884],
                'from' => ['id' => 6005717884, 'first_name' => 'PhÃƒÂ¡Ã‚Â»Ã‚Â¥ huynh'],
            ],
        ]);
    }

    private function postMealCallback(Child $child, string $action)
    {
        return $this->postJson('/telegram/webhook', [
            'callback_query' => [
                'id' => 'meal-callback',
                'from' => ['id' => 6005717884, 'first_name' => 'PhÃƒÂ¡Ã‚Â»Ã‚Â¥ huynh'],
                'message' => ['date' => now()->timestamp, 'chat' => ['id' => '6005717884']],
                'data' => "meal_suggestion:{$child->id}:".today()->toDateString().":{$action}",
            ],
        ]);
    }
}
