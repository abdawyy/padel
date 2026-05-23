<?php

namespace Tests\Feature;

use App\Models\AcademySession;
use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingOverlapTest extends TestCase
{
    use RefreshDatabase;

    public function test_overlapping_booking_is_rejected(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $court = Court::factory()->create(['is_active' => true]);

        $start = Carbon::parse('2026-06-01 10:00:00');
        $end = Carbon::parse('2026-06-01 11:00:00');

        Booking::factory()->create([
            'court_id' => $court->id,
            'owner_user_id' => $user->id,
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'confirmed',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/bookings', [
            'court_id' => $court->id,
            'start_time' => $start->copy()->addMinutes(30)->toDateTimeString(),
            'end_time' => $end->copy()->addMinutes(30)->toDateTimeString(),
            'match_type' => 'private',
        ])
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'This court is not available for the selected time range.',
            ]);
    }

    public function test_booking_conflicts_with_academy_session(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $court = Court::factory()->create(['is_active' => true]);

        $start = Carbon::parse('2026-06-02 14:00:00');
        $end = Carbon::parse('2026-06-02 15:00:00');

        AcademySession::factory()->create([
            'club_id' => $court->club_id,
            'court_id' => $court->id,
            'created_by_user_id' => $user->id,
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'scheduled',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/bookings', [
            'court_id' => $court->id,
            'start_time' => $start->toDateTimeString(),
            'end_time' => $end->toDateTimeString(),
            'match_type' => 'private',
        ])
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'This court is not available for the selected time range.',
            ]);
    }

    public function test_non_overlapping_bookings_can_be_created(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $court = Court::factory()->create(['is_active' => true]);

        Sanctum::actingAs($user);

        $this->postJson('/api/bookings', [
            'court_id' => $court->id,
            'start_time' => '2026-06-03 10:00:00',
            'end_time' => '2026-06-03 11:00:00',
            'match_type' => 'private',
        ])->assertCreated();

        $this->postJson('/api/bookings', [
            'court_id' => $court->id,
            'start_time' => '2026-06-03 11:00:00',
            'end_time' => '2026-06-03 12:00:00',
            'match_type' => 'private',
        ])->assertCreated();

        $this->assertDatabaseCount('bookings', 2);
    }
}
