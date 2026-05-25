<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingParticipantConflictTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_rejects_participant_with_overlapping_booking(): void
    {
        $owner = User::factory()->create(['is_active' => true]);
        $guest = User::factory()->create(['is_active' => true]);
        $court = Court::factory()->create(['is_active' => true]);
        $court->club->update([
            'registration_status' => 'approved',
            'subscription_status' => 'active',
        ]);

        $start = Carbon::parse('2026-09-01 10:00:00');
        $end = Carbon::parse('2026-09-01 11:00:00');

        $existing = Booking::factory()->create([
            'court_id' => $court->id,
            'owner_user_id' => $guest->id,
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'confirmed',
        ]);
        $existing->participants()->attach($guest->id, [
            'amount_due' => 0,
            'payment_status' => 'paid',
        ]);

        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/bookings', [
            'court_id' => Court::factory()->create([
                'is_active' => true,
                'club_id' => $court->club_id,
            ])->id,
            'start_time' => $start->copy()->addMinutes(15)->toDateTimeString(),
            'end_time' => $end->copy()->addMinutes(15)->toDateTimeString(),
            'match_type' => 'private',
            'participant_ids' => [$guest->id],
        ])
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'One or more participants have a conflicting booking or training session.',
            ]);
    }
}
