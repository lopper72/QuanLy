<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class TelegramAutoLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_telegram_signed_url_authenticates_user()
    {
        $user = User::factory()->create();

        $url = URL::temporarySignedRoute(
            'telegram.login',
            now()->addHours(12),
            ['user' => $user->id]
        );

        $response = $this->get($url);

        $response->assertRedirect(route('today'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_expired_telegram_signed_url_fails()
    {
        $user = User::factory()->create();

        $url = URL::temporarySignedRoute(
            'telegram.login',
            now()->subHours(1),
            ['user' => $user->id]
        );

        $response = $this->get($url);

        $response->assertStatus(403);
        $this->assertGuest();
    }

    public function test_invalid_telegram_signed_url_fails()
    {
        $user = User::factory()->create();

        $url = route('telegram.login', ['user' => $user->id]) . '?signature=invalid';

        $response = $this->get($url);

        $response->assertStatus(403);
        $this->assertGuest();
    }
}