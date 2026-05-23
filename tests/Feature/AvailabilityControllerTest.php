<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Club;
use App\Models\Court;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AvailabilityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthorized_user_cannot_view_club_availability(): void
    {
        $club = Club::factory()->create();
        $user = User::factory()->create(['is_active' => true]);

        Sanctum::actingAs($user);

        $this->getJson("/api/clubs/{$club->id}/availability")
            ->assertForbidden();
    }

    public function test_availability_includes_booking_spanning_from_previous_day(): void
    {
        $manager = User::factory()->create(['is_active' => true, 'role' => 'manager']);
        $club = Club::factory()->create([
            'registration_status' => 'approved',
            'subscription_status' => 'active',
        ]);
        $club->users()->attach($manager->id, ['role' => 'owner']);

        $court = Court::factory()->create(['club_id' => $club->id, 'is_active' => true]);

        $targetDate = Carbon::parse('2026-08-10');

        Booking::factory()->create([
            'court_id' => $court->id,
            'status' => 'confirmed',
            'start_time' => $targetDate->copy()->subDay()->setTime(23, 0),
            'end_time' => $targetDate->copy()->setTime(1, 0),
        ]);

        Sanctum::actingAs($manager);

        $response = $this->getJson("/api/clubs/{$club->id}/availability?date={$targetDate->toDateString()}");

        $response->assertOk();

        $courtPayload = collect($response->json('data'))->firstWhere('id', $court->id);

        $this->assertNotEmpty($courtPayload['bookings'] ?? []);
    }
}
