<?php

namespace Tests\Feature;

use App\Models\AcademySession;
use App\Models\Club;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademySessionPublicIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_index_excludes_sessions_from_unapproved_clubs(): void
    {
        $creator = User::factory()->create();

        $pendingClub = Club::factory()->create([
            'registration_status' => 'pending',
            'subscription_status' => 'inactive',
        ]);

        $approvedClub = Club::factory()->create([
            'registration_status' => 'approved',
            'subscription_status' => 'active',
        ]);

        AcademySession::factory()->create([
            'club_id' => $pendingClub->id,
            'created_by_user_id' => $creator->id,
            'status' => 'scheduled',
            'start_time' => Carbon::now()->addDays(2),
            'end_time' => Carbon::now()->addDays(2)->addHour(),
            'title' => 'Hidden Session',
        ]);

        AcademySession::factory()->create([
            'club_id' => $approvedClub->id,
            'created_by_user_id' => $creator->id,
            'status' => 'scheduled',
            'start_time' => Carbon::now()->addDays(2),
            'end_time' => Carbon::now()->addDays(2)->addHour(),
            'title' => 'Visible Session',
        ]);

        $response = $this->getJson('/api/academy-sessions');

        $response->assertOk();

        $titles = collect($response->json('data'))->pluck('title')->all();

        $this->assertContains('Visible Session', $titles);
        $this->assertNotContains('Hidden Session', $titles);
    }
}
