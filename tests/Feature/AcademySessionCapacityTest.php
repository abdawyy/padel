<?php

namespace Tests\Feature;

use App\Models\AcademySession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AcademySessionCapacityTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_enroll_when_session_is_full(): void
    {
        $admin = User::factory()->create(['is_active' => true, 'role' => 'admin']);
        $player = User::factory()->create(['is_active' => true]);
        $existing = User::factory()->create(['is_active' => true]);

        $session = AcademySession::factory()->create([
            'created_by_user_id' => $admin->id,
            'max_players' => 1,
            'price_per_player' => 0,
            'status' => 'scheduled',
            'start_time' => Carbon::now()->addDays(3),
            'end_time' => Carbon::now()->addDays(3)->addHour(),
        ]);

        $session->club->users()->attach($admin->id, ['role' => 'owner']);
        $session->players()->attach($existing->id, ['status' => 'registered']);

        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/academy-sessions/{$session->id}/enroll", [
            'player_id' => $player->id,
        ])
            ->assertUnprocessable()
            ->assertJson(['message' => 'This session is already full.']);
    }
}
