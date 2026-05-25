<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Club;
use App\Models\Court;
use App\Models\User;
use App\Services\BookingPriceCalculator;
use App\Services\PaymentIdempotency;
use App\Support\AdminClubContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class EnhancementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_price_calculator_uses_court_hourly_and_coach_fee(): void
    {
        $club = Club::factory()->create([
            'registration_status' => 'approved',
            'subscription_status' => 'active',
            'settings' => ['coach_fee_per_hour' => 100],
        ]);

        $court = Court::factory()->create([
            'club_id' => $club->id,
            'price_per_hour' => 200,
        ]);

        $start = Carbon::parse('2026-06-01 10:00:00');
        $end = Carbon::parse('2026-06-01 12:00:00');

        $pricing = app(BookingPriceCalculator::class)->calculate($court, $start, $end, 1);

        $this->assertSame(400.0, $pricing['total_price']);
        $this->assertSame(200.0, $pricing['coach_fee']);
    }

    public function test_store_booking_rejects_client_supplied_total_price(): void
    {
        $club = Club::factory()->create([
            'registration_status' => 'approved',
            'subscription_status' => 'active',
        ]);

        $court = Court::factory()->create(['club_id' => $club->id, 'price_per_hour' => 100]);
        $user = User::factory()->create(['role' => 'player']);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/bookings', [
                'court_id' => $court->id,
                'start_time' => now()->addDay()->setTime(10, 0)->toIso8601String(),
                'end_time' => now()->addDay()->setTime(11, 0)->toIso8601String(),
                'match_type' => 'private',
                'total_price' => 1,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['total_price']);
    }

    public function test_payment_idempotency_returns_same_payload(): void
    {
        $user = User::factory()->create();
        $calls = 0;

        $first = app(PaymentIdempotency::class)->resolve($user, 'test-key', function () use (&$calls) {
            $calls++;

            return ['payment_key' => 'abc'];
        });

        $second = app(PaymentIdempotency::class)->resolve($user, 'test-key', function () use (&$calls) {
            $calls++;

            return ['payment_key' => 'should-not-run'];
        });

        $this->assertSame($first, $second);
        $this->assertSame(1, $calls);
        Cache::flush();
    }

    public function test_admin_club_context_filters_courts_in_filament_query_helper(): void
    {
        $clubA = Club::factory()->create(['registration_status' => 'approved', 'subscription_status' => 'active']);
        $clubB = Club::factory()->create(['registration_status' => 'approved', 'subscription_status' => 'active']);

        Court::factory()->create(['club_id' => $clubA->id]);
        Court::factory()->create(['club_id' => $clubB->id]);

        AdminClubContext::set($clubA->id);

        $ids = Court::query()->pluck('id')->all();

        $this->assertCount(1, $ids);
        $this->assertSame($clubA->id, Court::query()->value('club_id'));

        AdminClubContext::set(null);
    }

    public function test_booking_pay_requires_participant_policy(): void
    {
        $owner = User::factory()->create(['role' => 'player']);
        $other = User::factory()->create(['role' => 'player']);
        $club = Club::factory()->create([
            'registration_status' => 'approved',
            'subscription_status' => 'active',
        ]);
        $court = Court::factory()->create(['club_id' => $club->id]);

        $booking = Booking::factory()->create([
            'court_id' => $court->id,
            'owner_user_id' => $owner->id,
            'status' => 'pending',
        ]);

        $booking->participants()->attach($owner->id, ['amount_due' => 50, 'payment_status' => 'pending']);

        $this->actingAs($other, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/pay")
            ->assertForbidden();
    }
}
