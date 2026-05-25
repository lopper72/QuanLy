<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAndAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /**
     * Test that guests are redirected to the login page for core modules.
     *
     * @dataProvider coreRoutesProvider
     */
    public function test_guests_are_redirected_to_login(string $route, array $params = []): void
    {
        $response = $this->get(route($route, $params));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /**
     * Test that authenticated users can access the core modules.
     *
     * @dataProvider coreRoutesProvider
     */
    public function test_authenticated_users_can_access_core_modules(string $route, array $params = []): void
    {
        // For the child parameter we need a child instance, but since we are testing routing
        // and index/create/settings don't require parameters, we provide basic parameters if needed.
        // Index and create are safe for general 200 checks.
        if (str_contains($route, 'show') || str_contains($route, 'edit')) {
            $this->markTestSkipped('Skip show/edit details for general auth tests.');
        }

        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get(route($route, $params));

        $response->assertStatus(200);
    }

    /**
     * Provider for core routes.
     */
    public static function coreRoutesProvider(): array
    {
        return [
            'dashboard' => ['dashboard'],
            'children index' => ['children.index'],
            'children create' => ['children.create'],
            'training index' => ['training.index'],
            'training today' => ['training.today'],
            'exercises index' => ['exercises.index'],
            'exercises create' => ['exercises.create'],
            'assessment index' => ['assessment.index'],
            'assessment progress' => ['assessment.progress'],
            'behavior index' => ['behavior.index'],
            'behavior quick' => ['behavior.quick'],
            'reports index' => ['reports.index'],
            'reports create' => ['reports.create'],
            'reports weekly' => ['reports.weekly.index'],
            'settings' => ['settings.index'],
        ];
    }
}