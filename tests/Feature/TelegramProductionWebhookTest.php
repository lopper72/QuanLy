<?php

namespace Tests\Feature;

use App\Models\TelegramSetting;
use App\Models\TrainingSession;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramProductionWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config([
            'services.telegram.bot_token' => '123456:SECRET',
            'services.telegram.webhook_secret' => 'prod-secret',
            'services.telegram.webhook_url' => 'https://hongbiennhanh.online/telegram/webhook',
        ]);
    }

    public function test_webhook_route_accessible_without_auth(): void
    {
        $this->postJson('/telegram/webhook', [
            'message' => [
                'chat' => ['id' => 123456],
                'text' => 'Xin chào',
            ],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'prod-secret',
        ])->assertOk();
    }

    public function test_invalid_secret_rejected(): void
    {
        $this->postJson('/telegram/webhook', [
            'message' => [
                'chat' => ['id' => 123456],
                'text' => 'Xin chào',
            ],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'wrong-secret',
        ])->assertForbidden();
    }

    public function test_valid_callback_accepted(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true], 200)]);
        $session = TrainingSession::factory()->create(['status' => 'planned']);

        $this->postJson('/telegram/webhook', [
            'callback_query' => [
                'id' => 'cb-prod',
                'data' => "training_session:{$session->id}:completed",
                'from' => ['id' => 987654],
                'message' => [
                    'date' => now()->timestamp,
                    'chat' => ['id' => 123456],
                ],
            ],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'prod-secret',
        ])->assertOk();

        $this->assertDatabaseHas('training_sessions', [
            'id' => $session->id,
            'status' => 'completed',
        ]);
    }

    public function test_set_webhook_service_works(): void
    {
        Http::fake([
            'api.telegram.org/*/setWebhook' => Http::response(['ok' => true, 'result' => true], 200),
        ]);

        $response = app(TelegramService::class)->setWebhook();

        $this->assertTrue($response->successful());
        Http::assertSent(fn ($request) => str_contains($request->url(), '/setWebhook')
            && $request['url'] === 'https://hongbiennhanh.online/telegram/webhook'
            && $request['secret_token'] === 'prod-secret');
    }

    public function test_webhook_info_service_works(): void
    {
        Http::fake([
            'api.telegram.org/*/getWebhookInfo' => Http::response([
                'ok' => true,
                'result' => [
                    'url' => 'https://hongbiennhanh.online/telegram/webhook',
                    'pending_update_count' => 0,
                ],
            ], 200),
        ]);

        $response = app(TelegramService::class)->getWebhookInfo();

        $this->assertTrue($response->successful());
        $this->assertSame('https://hongbiennhanh.online/telegram/webhook', $response->json('result.url'));
    }

    public function test_answer_callback_query_called(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true], 200)]);

        app(TelegramService::class)->answerCallbackQuery('callback-id', 'Đã ghi nhận: Hoàn thành');

        Http::assertSent(fn ($request) => str_contains($request->url(), '/answerCallbackQuery')
            && $request['callback_query_id'] === 'callback-id'
            && $request['show_alert'] === false);
    }

    public function test_production_config_loads_correctly(): void
    {
        $this->assertSame('123456:SECRET', config('services.telegram.bot_token'));
        $this->assertSame('prod-secret', config('services.telegram.webhook_secret'));
        $this->assertSame('https://hongbiennhanh.online/telegram/webhook', config('services.telegram.webhook_url'));
    }

    public function test_database_setting_can_override_webhook_url(): void
    {
        TelegramSetting::query()->create([
            'bot_token' => '123456:SECRET',
            'webhook_secret' => 'prod-secret',
            'webhook_url' => 'https://hongbiennhanh.online/telegram/webhook',
            'enabled' => true,
        ]);

        $this->assertSame('https://hongbiennhanh.online/telegram/webhook', app(TelegramService::class)->webhookUrl());
    }
}
