<?php

namespace Tests\Feature;

use App\Models\AcademySession;
use App\Models\Club;
use App\Models\User;
use App\Notifications\AcademySessionCancelledNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AcademySessionUpdateCancelTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_update_and_cancel_session(): void
    {
        Notification::fake();

        $manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $player = User::factory()->create(['is_active' => true]);
        $club = Club::factory()->create(['registration_status' => 'approved', 'subscription_status' => 'active']);
        $club->users()->attach($manager->id, ['role' => 'owner']);

        $session = AcademySession::factory()->create([
            'club_id' => $club->id,
            'created_by_user_id' => $manager->id,
            'status' => 'scheduled',
            'start_time' => Carbon::now()->addDays(3),
            'end_time' => Carbon::now()->addDays(3)->addHour(),
            'title' => 'Old Title',
        ]);
        $session->players()->attach($player->id, ['status' => 'registered']);

        Sanctum::actingAs($manager);

        $this->patchJson("/api/v1/academy-sessions/{$session->id}", [
            'title' => 'Updated Title',
        ])->assertOk()->assertJsonPath('data.title', 'Updated Title');

        $this->postJson("/api/v1/academy-sessions/{$session->id}/cancel", [
            'reason' => 'Weather',
        ])->assertOk()->assertJsonPath('session.status', 'cancelled');

        Notification::assertSentTo($player, AcademySessionCancelledNotification::class);
    }
}
