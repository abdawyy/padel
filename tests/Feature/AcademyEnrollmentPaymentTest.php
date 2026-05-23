<?php

namespace Tests\Feature;

use App\Models\AcademySession;
use App\Models\Club;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AcademyEnrollmentPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_assigning_player_to_paid_session_requires_payment(): void
    {
        config([
            'services.paymob.api_key' => 'test-key',
            'services.paymob.integration_id' => 12345,
            'services.paymob.iframe_id' => 'iframe-1',
            'services.paymob.base_url' => 'https://accept.paymob.com/api',
        ]);

        Http::fake([
            'https://accept.paymob.com/api/auth/tokens' => Http::response(['token' => 'auth-token']),
            'https://accept.paymob.com/api/ecommerce/orders' => Http::response(['id' => 9001]),
            'https://accept.paymob.com/api/acceptance/payment_keys' => Http::response(['token' => 'payment-key']),
        ]);

        $club = Club::factory()->create(['registration_status' => 'approved', 'subscription_status' => 'active']);
        $manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $player = User::factory()->create(['role' => 'player', 'is_active' => true]);
        $club->users()->attach($manager->id, ['role' => 'owner']);

        $session = AcademySession::factory()->create([
            'club_id' => $club->id,
            'created_by_user_id' => $manager->id,
            'price_per_player' => 40,
            'status' => 'scheduled',
            'start_time' => Carbon::now()->addDays(2),
            'end_time' => Carbon::now()->addDays(2)->addHour(),
        ]);

        Sanctum::actingAs($manager);

        $this->postJson("/api/academy-sessions/{$session->id}/enroll", [
            'player_id' => $player->id,
        ])
            ->assertStatus(402)
            ->assertJsonPath('player_id', $player->id);

        $this->assertDatabaseMissing('academy_session_user', [
            'academy_session_id' => $session->id,
            'user_id' => $player->id,
        ]);
    }
}
