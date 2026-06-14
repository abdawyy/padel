<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingSkillFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_open_match_skill_range_filters_discovery(): void
    {
        $court = Court::factory()->create();
        $owner = User::factory()->create();

        Booking::factory()->create([
            'court_id' => $court->id,
            'owner_user_id' => $owner->id,
            'match_type' => 'open_match',
            'status' => 'pending',
            'start_time' => Carbon::now()->addDay(),
            'end_time' => Carbon::now()->addDay()->addHour(),
            'max_players' => 4,
            'skill_min' => 3,
            'skill_max' => 5,
        ]);

        $eligible = $this->getJson('/api/v1/matches/open?skill_level=4')->assertOk();
        $ineligible = $this->getJson('/api/v1/matches/open?skill_level=7')->assertOk();

        $this->assertCount(1, $eligible->json('data'));
        $this->assertCount(0, $ineligible->json('data'));
    }

    public function test_store_sets_skill_min_and_max_from_numeric_skill_level(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $court = Court::factory()->create(['is_active' => true]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/bookings', [
            'court_id' => $court->id,
            'start_time' => Carbon::now()->addDays(2)->setTime(10, 0)->toDateTimeString(),
            'end_time' => Carbon::now()->addDays(2)->setTime(11, 0)->toDateTimeString(),
            'match_type' => 'open_match',
            'skill_level' => 4,
            'skill_min' => 3,
            'skill_max' => 5,
        ])->assertCreated();

        $this->assertDatabaseHas('bookings', [
            'court_id' => $court->id,
            'skill_min' => 3,
            'skill_max' => 5,
        ]);
    }
}
