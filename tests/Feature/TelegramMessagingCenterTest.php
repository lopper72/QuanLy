<?php

namespace Tests\Feature;

use App\Models\TelegramMessage;
use App\Models\TelegramSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TelegramMessagingCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config([
            'services.telegram.bot_token' => null,
            'services.telegram.webhook_secret' => 'test-secret',
        ]);
    }

    public function test_webhook_stores_inbound_message(): void
    {
        $this->postJson('/telegram/webhook', [
            'message' => [
                'date' => now()->timestamp,
                'text' => 'Phụ huynh phản hồi',
                'chat' => [
                    'id' => 123456,
                    'username' => 'parent_chat',
                    'first_name' => 'Phụ huynh',
                ],
                'from' => [
                    'id' => 987654,
                    'username' => 'parent_user',
                    'first_name' => 'Phụ huynh',
                ],
            ],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
        ])->assertOk();

        $this->assertDatabaseHas('telegram_messages', [
            'direction' => TelegramMessage::DIRECTION_INBOUND,
            'telegram_chat_id' => '123456',
            'telegram_user_id' => '987654',
            'telegram_username' => 'parent_user',
            'message_text' => 'Phụ huynh phản hồi',
            'delivery_status' => TelegramMessage::STATUS_RECEIVED,
        ]);

        $this->assertDatabaseHas('telegram_contacts', [
            'telegram_chat_id' => '123456',
            'telegram_user_id' => '987654',
            'telegram_username' => 'parent_user',
        ]);
    }

    public function test_test_send_creates_outbound_log(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);
        TelegramSetting::query()->create([
            'bot_token' => '123456:SECRET',
            'enabled' => true,
        ]);

        $this->actingAs(User::factory()->create())
            ->post('/telegram/test-send', [
                'chat_id' => '123456',
                'message_text' => 'Tin nhắn thử nghiệm',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('telegram_messages', [
            'direction' => TelegramMessage::DIRECTION_OUTBOUND,
            'telegram_chat_id' => '123456',
            'message_text' => 'Tin nhắn thử nghiệm',
            'delivery_status' => TelegramMessage::STATUS_SENT,
        ]);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/sendMessage'));
    }

    public function test_telegram_settings_save_works(): void
    {
        $this->actingAs(User::factory()->create())
            ->patch('/telegram/settings', [
                'bot_token' => '123456:SECRET',
                'bot_username' => 'care_bot',
                'webhook_secret' => 'secret',
                'default_chat_id' => '123456',
                'enabled' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('telegram_settings', [
            'bot_username' => 'care_bot',
            'webhook_secret' => 'secret',
            'default_chat_id' => '123456',
            'enabled' => true,
        ]);
    }

    public function test_unauthorized_user_blocked(): void
    {
        $this->get('/telegram')->assertRedirect('/login');
    }

    public function test_messages_page_loads(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/telegram')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Telegram/Index')
                ->has('contacts')
                ->has('messages')
                ->has('settings')
            );
    }

    public function test_token_not_exposed_in_response(): void
    {
        TelegramSetting::query()->create([
            'bot_token' => '123456:SECRET',
            'bot_username' => 'care_bot',
            'enabled' => true,
        ]);

        $this->actingAs(User::factory()->create())
            ->get('/telegram/settings')
            ->assertOk()
            ->assertDontSee('123456:SECRET')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Telegram/Settings')
                ->where('settings.bot_token_masked', '••••••••CRET')
            );
    }
}
