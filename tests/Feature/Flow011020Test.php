<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Court;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Flow011020Test extends TestCase
{
    use RefreshDatabase;

    public function test_player_availability_requires_approved_club(): void
    {
        $club = Club::factory()->create([
            'registration_status' => 'pending',
            'subscription_status' => 'active',
        ]);

        $user = User::factory()->create(['role' => 'player']);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/clubs/{$club->id}/player-availability?date=".now()->toDateString())
            ->assertStatus(422);
    }

    public function test_player_availability_returns_courts_for_approved_club(): void
    {
        $club = Club::factory()->create([
            'registration_status' => 'approved',
            'subscription_status' => 'active',
        ]);

        Court::factory()->create(['club_id' => $club->id, 'is_active' => true]);

        $user = User::factory()->create(['role' => 'player']);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/clubs/{$club->id}/player-availability?date=".now()->toDateString())
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_login_is_throttled(): void
    {
        for ($i = 0; $i < 11; $i++) {
            $response = $this->postJson('/api/v1/login', [
                'email' => 'nobody@example.com',
                'password' => 'wrong',
            ]);
        }

        $response->assertStatus(429);
    }

    public function test_paymob_webhook_allowlist_blocks_unknown_ip(): void
    {
        config(['services.paymob.webhook_allowed_ips' => ['203.0.113.1']]);

        $this->postJson('/api/v1/webhooks/paymob/transaction-processed', [])
            ->assertForbidden();
    }
}
