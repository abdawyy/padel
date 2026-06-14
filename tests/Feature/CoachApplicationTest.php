<?php

namespace Tests\Feature;

use App\Models\AcademySession;
use App\Models\Club;
use App\Models\CoachApplication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CoachApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_coach_must_belong_to_club_to_apply(): void
    {
        $coach = User::factory()->create(['role' => 'coach', 'is_active' => true]);
        $club = Club::factory()->create(['registration_status' => 'approved', 'subscription_status' => 'active']);
        $session = AcademySession::factory()->create([
            'club_id' => $club->id,
            'coach_user_id' => null,
            'status' => 'scheduled',
            'start_time' => Carbon::now()->addDays(2),
        ]);

        Sanctum::actingAs($coach);

        $this->postJson("/api/v1/academy-sessions/{$session->id}/coach-apply")
            ->assertForbidden();
    }

    public function test_accept_assigns_coach_and_declines_other_pending_applications(): void
    {
        $club = Club::factory()->create(['registration_status' => 'approved', 'subscription_status' => 'active']);
        $manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $coachA = User::factory()->create(['role' => 'coach', 'is_active' => true]);
        $coachB = User::factory()->create(['role' => 'coach', 'is_active' => true]);

        $club->users()->attach($manager->id, ['role' => 'owner']);
        $club->users()->attach($coachA->id, ['role' => 'staff']);
        $club->users()->attach($coachB->id, ['role' => 'staff']);

        $session = AcademySession::factory()->create([
            'club_id' => $club->id,
            'created_by_user_id' => $manager->id,
            'coach_user_id' => null,
            'status' => 'scheduled',
            'start_time' => Carbon::now()->addDays(3),
        ]);

        $appA = CoachApplication::query()->create([
            'academy_session_id' => $session->id,
            'coach_user_id' => $coachA->id,
            'status' => 'pending',
        ]);

        $appB = CoachApplication::query()->create([
            'academy_session_id' => $session->id,
            'coach_user_id' => $coachB->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($manager);

        $this->patchJson("/api/v1/coach-applications/{$appA->id}", [
            'status' => 'accepted',
        ])->assertOk();

        $session->refresh();
        $appA->refresh();
        $appB->refresh();

        $this->assertSame($coachA->id, $session->coach_user_id);
        $this->assertSame('accepted', $appA->status);
        $this->assertSame('declined', $appB->status);
    }

    public function test_cannot_accept_when_session_already_has_coach(): void
    {
        $club = Club::factory()->create(['registration_status' => 'approved', 'subscription_status' => 'active']);
        $manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $assignedCoach = User::factory()->create(['role' => 'coach', 'is_active' => true]);
        $applicant = User::factory()->create(['role' => 'coach', 'is_active' => true]);

        $club->users()->attach($manager->id, ['role' => 'owner']);
        $club->users()->attach($assignedCoach->id, ['role' => 'staff']);
        $club->users()->attach($applicant->id, ['role' => 'staff']);

        $session = AcademySession::factory()->create([
            'club_id' => $club->id,
            'created_by_user_id' => $manager->id,
            'coach_user_id' => $assignedCoach->id,
            'status' => 'scheduled',
        ]);

        $application = CoachApplication::query()->create([
            'academy_session_id' => $session->id,
            'coach_user_id' => $applicant->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($manager);

        $this->patchJson("/api/v1/coach-applications/{$application->id}", [
            'status' => 'accepted',
        ])->assertConflict();
    }
}
