<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Exercise;
use App\Models\TelegramMessage;
use App\Models\TelegramSetting;
use App\Models\TrainingSession;
use App\Models\TrainingSessionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramTrainingNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['services.telegram.webhook_secret' => 'test-secret']);
    }

    public function test_can_send_today_training_message(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true], 200)]);
        $child = $this->createTodayTraining();
        $this->configureTelegram();

        $this->actingAs(User::factory()->create())
            ->post('/telegram/training/send-today', ['child_id' => $child->id])
            ->assertRedirect();

        Http::assertSent(fn ($request) => str_contains($request['text'], "Lịch tập hôm nay của bé {$child->full_name}")
            && str_contains(json_encode($request['reply_markup'], JSON_UNESCAPED_UNICODE), 'training_session:'));
    }

    public function test_outbound_telegram_message_is_logged(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true], 200)]);
        $child = $this->createTodayTraining();
        $this->configureTelegram();

        $this->actingAs(User::factory()->create())
            ->post('/telegram/training/send-today', ['child_id' => $child->id])
            ->assertRedirect();

        $this->assertDatabaseHas('telegram_messages', [
            'direction' => TelegramMessage::DIRECTION_OUTBOUND,
            'message_type' => 'training_schedule',
            'related_child_id' => $child->id,
            'delivery_status' => TelegramMessage::STATUS_SENT,
        ]);
    }

    public function test_callback_completed_updates_training_session_to_completed(): void
    {
        $this->configureTelegram();
        $session = $this->createTodayTraining()->trainingSessions()->first();

        $this->postTrainingCallback($session, 'completed')->assertOk();

        $this->assertDatabaseHas('training_sessions', [
            'id' => $session->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('telegram_messages', [
            'message_type' => 'training_callback',
            'callback_data' => "training_session:{$session->id}:completed",
            'action_status' => 'completed',
        ]);
        Http::assertSent(fn ($request) => str_contains($request->url(), '/answerCallbackQuery')
            && $request['callback_query_id'] === 'cb123'
            && $request['text'] === 'Đã ghi nhận: Hoàn thành'
            && $request['show_alert'] === false);
    }

    public function test_callback_not_completed_keeps_session_in_progress(): void
    {
        $this->configureTelegram();
        $session = $this->createTodayTraining()->trainingSessions()->first();

        $this->postTrainingCallback($session, 'not_completed')->assertOk();

        $this->assertDatabaseHas('training_sessions', [
            'id' => $session->id,
            'status' => 'in_progress',
        ]);
        $this->assertDatabaseHas('telegram_messages', [
            'message_type' => 'training_callback',
            'callback_data' => "training_session:{$session->id}:not_completed",
            'action_status' => 'not_completed',
        ]);
    }

    public function test_callback_skipped_updates_session_to_skipped(): void
    {
        $this->configureTelegram();
        $session = $this->createTodayTraining()->trainingSessions()->first();

        $this->postTrainingCallback($session, 'skipped')->assertOk();

        $this->assertDatabaseHas('training_sessions', [
            'id' => $session->id,
            'status' => 'skipped',
        ]);
    }

    public function test_callback_need_help_does_not_mark_completed(): void
    {
        $this->configureTelegram();
        $session = $this->createTodayTraining()->trainingSessions()->first();

        $this->postTrainingCallback($session, 'need_help')->assertOk();

        $this->assertDatabaseHas('training_sessions', [
            'id' => $session->id,
            'status' => 'need_help',
        ]);
        $this->assertDatabaseMissing('training_sessions', [
            'id' => $session->id,
            'status' => 'completed',
        ]);
    }

    public function test_invalid_callback_is_rejected_safely(): void
    {
        $session = $this->createTodayTraining()->trainingSessions()->first();

        $this->postJson('/telegram/webhook', [
            'callback_query' => [
                'id' => 'cb-bad',
                'data' => "training_session:{$session->id}:unknown",
                'message' => ['chat' => ['id' => 123456], 'date' => now()->timestamp],
            ],
        ], ['X-Telegram-Bot-Api-Secret-Token' => 'test-secret'])->assertOk();

        $this->assertDatabaseHas('training_sessions', [
            'id' => $session->id,
            'status' => 'planned',
        ]);
    }

    public function test_voided_child_cannot_receive_new_training_message(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true], 200)]);
        $child = $this->createTodayTraining(['status' => Child::STATUS_VOIDED]);
        $this->configureTelegram();

        $this->actingAs(User::factory()->create())
            ->post('/telegram/training/send-today', ['child_id' => $child->id])
            ->assertRedirect();

        $this->assertDatabaseMissing('telegram_messages', [
            'message_type' => 'training_schedule',
            'related_child_id' => $child->id,
        ]);
    }

    public function test_telegram_test_panel_page_loads(): void
    {
        $this->createTodayTraining();

        $this->actingAs(User::factory()->create())
            ->get('/telegram')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Telegram/Index')
                ->has('trainingTest.children')
                ->has('trainingTest.sessions')
            );
    }

    private function configureTelegram(): void
    {
        TelegramSetting::query()->create([
            'bot_token' => '123456:SECRET',
            'default_chat_id' => '123456',
            'enabled' => true,
            'webhook_secret' => 'test-secret',
        ]);
    }

    private function createTodayTraining(array $childAttributes = []): Child
    {
        $child = Child::factory()->create(array_merge([
            'full_name' => 'Nguyễn Minh Anh',
            'status' => Child::STATUS_ACTIVE,
        ], $childAttributes));

        $session = TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => today()->toDateString(),
            'status' => 'planned',
            'scheduled_time' => '08:00',
        ]);

        $exercise = Exercise::factory()->create(['title' => 'Bật nhảy trên thảm']);
        TrainingSessionItem::factory()->create([
            'training_session_id' => $session->id,
            'exercise_id' => $exercise->id,
            'duration_minutes' => 10,
            'completion_status' => 'not_started',
        ]);

        return $child;
    }

    private function postTrainingCallback(TrainingSession $session, string $action)
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true], 200)]);

        return $this->postJson('/telegram/webhook', [
            'callback_query' => [
                'id' => 'cb123',
                'data' => "training_session:{$session->id}:{$action}",
                'from' => [
                    'id' => 987654,
                    'username' => 'parent_user',
                    'first_name' => 'Phụ huynh',
                ],
                'message' => [
                    'date' => now()->timestamp,
                    'chat' => ['id' => 123456],
                ],
            ],
        ], ['X-Telegram-Bot-Api-Secret-Token' => 'test-secret']);
    }
}
