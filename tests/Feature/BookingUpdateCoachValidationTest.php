<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Club;
use App\Models\Court;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingUpdateCoachValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_rejects_coach_not_in_club(): void
    {
        $club = Club::factory()->create();
        $court = Court::factory()->create(['club_id' => $club->id]);
        $owner = User::factory()->create(['is_active' => true]);
        $outsiderCoach = User::factory()->create(['role' => 'coach', 'is_active' => true]);

        $booking = Booking::factory()->create([
            'court_id' => $court->id,
            'owner_user_id' => $owner->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($owner);

        $this->patchJson("/api/v1/bookings/{$booking->id}", [
            'coach_user_id' => $outsiderCoach->id,
        ])
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'The selected coach must belong to the same club as the court.',
            ]);
    }
}
