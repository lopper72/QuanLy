<?php

namespace Tests\Feature;

use App\Jobs\SendTelegramMessageJob;
use App\Models\ChecklistItem;
use App\Models\Child;
use App\Models\Exercise;
use App\Models\TrainingSession;
use App\Models\TrainingSessionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TelegramIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config([
            'services.telegram.webhook_secret' => 'test-secret',
            'services.telegram.bot_username' => 'YOUR_BOT',
        ]);
    }

    public function test_parent_can_generate_telegram_link(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/settings/telegram/link')
            ->assertRedirect();

        $user->refresh();

        $this->assertNotNull($user->telegram_link_token);

        $this->actingAs($user)
            ->get('/settings')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Settings/Index')
                ->where('telegram.connected', false)
                ->where('telegram.link_url', "https://t.me/YOUR_BOT?start=parent_{$user->telegram_link_token}")
            );
    }

    public function test_start_webhook_links_parent_telegram_chat(): void
    {
        Queue::fake();
        $user = User::factory()->create(['telegram_link_token' => 'abc-token']);

        $this->postJson('/webhooks/telegram', [
            'message' => [
                'chat' => ['id' => 123456],
                'text' => '/start parent_abc-token',
            ],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
        ])->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'telegram_chat_id' => '123456',
            'telegram_notifications_enabled' => true,
        ]);

        Queue::assertPushed(SendTelegramMessageJob::class);
    }

    public function test_invalid_webhook_secret_is_rejected(): void
    {
        $this->postJson('/webhooks/telegram', [
            'message' => [
                'chat' => ['id' => 123456],
                'text' => '/start parent_abc-token',
            ],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'wrong-secret',
        ])->assertForbidden();
    }

    public function test_callback_button_updates_checklist_item_status(): void
    {
        Queue::fake();
        $item = $this->createChecklistItem();

        $this->postJson('/webhooks/telegram', [
            'callback_query' => [
                'id' => 'cb123',
                'data' => "checklist_done:{$item->id}",
                'message' => [
                    'chat' => ['id' => 123456],
                ],
            ],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
        ])->assertOk();

        $this->assertDatabaseHas('checklist_items', [
            'id' => $item->id,
            'status' => ChecklistItem::STATUS_COMPLETED,
            'performance_result' => 'good',
        ]);

        Queue::assertPushed(SendTelegramMessageJob::class, function ($job) {
            return str_contains($job->text, 'Đã tập xong');
        });
    }

    public function test_refuse_callback_button_updates_checklist_item_status(): void
    {
        Queue::fake();
        $item = $this->createChecklistItem();

        $this->postJson('/webhooks/telegram', [
            'callback_query' => [
                'id' => 'cb124',
                'data' => "checklist_refuse:{$item->id}",
                'message' => [
                    'chat' => ['id' => 123456],
                ],
            ],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
        ])->assertOk();

        $this->assertDatabaseHas('checklist_items', [
            'id' => $item->id,
            'status' => ChecklistItem::STATUS_REFUSED,
            'performance_result' => 'not_cooperative',
        ]);

        Queue::assertPushed(SendTelegramMessageJob::class, function ($job) {
            return str_contains($job->text, 'Bé từ chối');
        });
    }

    public function test_fallback_command_done_updates_checklist_item(): void
    {
        Queue::fake();
        $item = $this->createChecklistItem();

        $this->postJson('/webhooks/telegram', [
            'message' => [
                'chat' => ['id' => 123456],
                'text' => "/done {$item->id}",
            ],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
        ])->assertOk();

        $this->assertDatabaseHas('checklist_items', [
            'id' => $item->id,
            'status' => ChecklistItem::STATUS_COMPLETED,
        ]);
    }

    public function test_fallback_command_refuse_updates_checklist_item(): void
    {
        Queue::fake();
        $item = $this->createChecklistItem();

        $this->postJson('/webhooks/telegram', [
            'message' => [
                'chat' => ['id' => 123456],
                'text' => "/refuse {$item->id}",
            ],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
        ])->assertOk();

        $this->assertDatabaseHas('checklist_items', [
            'id' => $item->id,
            'status' => ChecklistItem::STATUS_REFUSED,
        ]);
    }

    public function test_reminder_command_queues_telegram_job(): void
    {
        Queue::fake();
        User::factory()->create([
            'telegram_chat_id' => '123456',
            'telegram_notifications_enabled' => true,
        ]);
        $this->createChecklistItem(now()->addMinutes(10)->format('H:i'));

        $this->artisan('telegram:send-reminders')
            ->assertExitCode(0);

        Queue::assertPushed(SendTelegramMessageJob::class);
    }

    public function test_telegram_today_reminder_only_uses_today_sessions(): void
    {
        Queue::fake();
        User::factory()->create([
            'telegram_chat_id' => '123456',
            'telegram_notifications_enabled' => true,
        ]);

        $todayItem = $this->createChecklistItem(now()->addMinutes(10)->format('H:i'));

        $child = Child::factory()->create(['status' => 'active']);
        $exercise = Exercise::factory()->create(['title' => 'Bài tập hôm qua']);
        $pastSession = TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => today()->subDay()->toDateString(),
            'scheduled_time' => now()->addMinutes(10)->format('H:i'),
            'status' => 'pending',
        ]);
        TrainingSessionItem::factory()->create([
            'training_session_id' => $pastSession->id,
            'exercise_id' => $exercise->id,
            'completion_status' => 'pending',
        ]);

        $this->artisan('telegram:send-reminders')->assertExitCode(0);

        Queue::assertPushed(SendTelegramMessageJob::class, function ($job) use ($todayItem) {
            return str_contains($job->text, $todayItem->trainingSessionItem->exercise->title);
        });
        Queue::assertNotPushed(SendTelegramMessageJob::class, function ($job) {
            return str_contains($job->text, 'Bài tập hôm qua');
        });
    }

    protected function createChecklistItem(string $scheduledTime = '15:30'): ChecklistItem
    {
        $child = Child::factory()->create(['status' => 'active']);
        $exercise = Exercise::factory()->create(['title' => 'Bật nhảy trên thảm']);
        $session = TrainingSession::factory()->create([
            'child_id' => $child->id,
            'session_date' => today()->toDateString(),
            'scheduled_time' => $scheduledTime,
            'status' => 'planned',
        ]);
        $trainingItem = TrainingSessionItem::factory()->create([
            'training_session_id' => $session->id,
            'exercise_id' => $exercise->id,
            'duration_minutes' => 15,
            'completion_status' => 'not_started',
        ]);

        $this->actingAs(User::factory()->create())->get('/today');

        return ChecklistItem::where('training_session_item_id', $trainingItem->id)->firstOrFail();
    }
}
