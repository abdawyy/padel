<?php

namespace Tests\Feature;

use App\Models\Court;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingPaymentSplitFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_participant_amounts_sum_to_total_price(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $court = Court::factory()->create([
            'is_active' => true,
            'price_per_hour' => 100,
            'club_id' => \App\Models\Club::factory()->create([
                'registration_status' => 'approved',
                'subscription_status' => 'active',
            ]),
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/bookings', [
            'court_id' => $court->id,
            'start_time' => Carbon::now()->addDays(3)->setTime(10, 0)->toDateTimeString(),
            'end_time' => Carbon::now()->addDays(3)->setTime(11, 0)->toDateTimeString(),
            'match_type' => 'open_match',
            'max_players' => 4,
        ])->assertCreated();

        $amounts = DB::table('booking_participants')->pluck('amount_due')->map(fn ($v) => (float) $v);
        $booking = DB::table('bookings')->latest('id')->first();

        $this->assertEqualsWithDelta((float) $booking->total_price, $amounts->sum(), 0.01);
    }
}
