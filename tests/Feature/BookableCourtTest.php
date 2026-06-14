<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Court;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookableCourtTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_book_court_on_pending_club(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $club = Club::factory()->create([
            'registration_status' => 'pending',
            'subscription_status' => 'inactive',
        ]);
        $court = Court::factory()->create([
            'club_id' => $club->id,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/bookings', [
            'court_id' => $court->id,
            'start_time' => Carbon::now()->addDays(2)->setTime(10, 0)->toDateTimeString(),
            'end_time' => Carbon::now()->addDays(2)->setTime(11, 0)->toDateTimeString(),
            'match_type' => 'private',
        ])
            ->assertUnprocessable()
            ->assertJsonFragment([
                'message' => 'Bookings are not available until the club registration is approved.',
            ]);
    }

    public function test_cannot_book_inactive_court(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $court = Court::factory()->create([
            'is_active' => false,
            'club_id' => Club::factory()->create([
                'registration_status' => 'approved',
                'subscription_status' => 'active',
            ]),
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/bookings', [
            'court_id' => $court->id,
            'start_time' => Carbon::now()->addDays(2)->setTime(10, 0)->toDateTimeString(),
            'end_time' => Carbon::now()->addDays(2)->setTime(11, 0)->toDateTimeString(),
            'match_type' => 'private',
        ])
            ->assertUnprocessable()
            ->assertJsonFragment([
                'message' => 'This court is not available for booking.',
            ]);
    }
}
