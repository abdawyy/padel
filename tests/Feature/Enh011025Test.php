<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Club;
use App\Support\ClubSubscriptionStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class Enh011025Test extends TestCase
{
    use RefreshDatabase;

    public function test_api_routes_use_v1_prefix(): void
    {
        $login = collect(Route::getRoutes())->first(
            fn ($route) => $route->uri() === 'api/v1/login' && in_array('POST', $route->methods(), true)
        );

        $this->assertNotNull($login);
    }

    public function test_club_subscription_status_normalizes_cancelled(): void
    {
        $this->assertSame(
            ClubSubscriptionStatus::INACTIVE,
            ClubSubscriptionStatus::normalize('cancelled')
        );
    }

    public function test_brand_config_has_shared_assets(): void
    {
        $this->assertStringContainsString('brand', config('brand.logo'));
        $this->assertNotEmpty(config('brand.primary_hex'));
    }

    public function test_audit_logs_table_is_writable(): void
    {
        AuditLog::query()->create([
            'event_type' => 'test.event',
            'subject_type' => Club::class,
            'subject_id' => 1,
            'payload' => ['ok' => true],
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'test.event',
        ]);
    }

    public function test_player_locale_route_switches_session(): void
    {
        $this->post(route('player.locale'), ['locale' => 'ar'])
            ->assertRedirect();

        $this->assertSame('ar', session('player_locale'));
    }
}
