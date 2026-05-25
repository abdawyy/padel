<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Services\BookingParticipantCapacity;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingParticipantCapacityTest extends TestCase
{
    use RefreshDatabase;

    public function test_stale_pending_participant_does_not_count_toward_capacity(): void
    {
        $booking = Booking::factory()->create(['max_players' => 2]);

        DB::table('booking_participants')->insert([
            'booking_id' => $booking->id,
            'user_id' => User::factory()->create()->id,
            'amount_due' => 50,
            'payment_status' => 'paid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('booking_participants')->insert([
            'booking_id' => $booking->id,
            'user_id' => User::factory()->create()->id,
            'amount_due' => 50,
            'payment_status' => 'pending',
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);

        $this->assertSame(1, BookingParticipantCapacity::countForBooking($booking->id));
    }

    public function test_open_matches_list_ignores_stale_pending_for_capacity(): void
    {
        $owner = User::factory()->create();
        $court = Court::factory()->create();

        $booking = Booking::factory()->create([
            'court_id' => $court->id,
            'owner_user_id' => $owner->id,
            'match_type' => 'open_match',
            'status' => 'pending',
            'start_time' => Carbon::now()->addDay(),
            'end_time' => Carbon::now()->addDay()->addHour(),
            'max_players' => 2,
        ]);

        DB::table('booking_participants')->insert([
            'booking_id' => $booking->id,
            'user_id' => $owner->id,
            'amount_due' => 50,
            'payment_status' => 'paid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('booking_participants')->insert([
            'booking_id' => $booking->id,
            'user_id' => User::factory()->create()->id,
            'amount_due' => 50,
            'payment_status' => 'pending',
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);

        $this->getJson('/api/v1/matches/open')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_join_succeeds_when_only_stale_pending_blocks_would_apply(): void
    {
        config([
            'services.paymob.api_key' => 'test-key',
            'services.paymob.integration_id' => 12345,
            'services.paymob.iframe_id' => 'iframe-1',
            'services.paymob.base_url' => 'https://accept.paymob.com/api',
        ]);

        Http::fake([
            'https://accept.paymob.com/api/auth/tokens' => Http::response(['token' => 'auth-token']),
            'https://accept.paymob.com/api/ecommerce/orders' => Http::response(['id' => 9001]),
            'https://accept.paymob.com/api/acceptance/payment_keys' => Http::response(['token' => 'payment-key']),
        ]);

        $owner = User::factory()->create(['is_active' => true]);
        $joiner = User::factory()->create(['is_active' => true]);
        $court = Court::factory()->create(['is_active' => true]);

        $booking = Booking::factory()->create([
            'court_id' => $court->id,
            'owner_user_id' => $owner->id,
            'match_type' => 'open_match',
            'status' => 'pending',
            'start_time' => Carbon::now()->addDay(),
            'end_time' => Carbon::now()->addDay()->addHour(),
            'max_players' => 2,
            'total_price' => 100,
        ]);

        DB::table('booking_participants')->insert([
            'booking_id' => $booking->id,
            'user_id' => $owner->id,
            'amount_due' => 100,
            'payment_status' => 'paid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('booking_participants')->insert([
            'booking_id' => $booking->id,
            'user_id' => User::factory()->create()->id,
            'amount_due' => 50,
            'payment_status' => 'pending',
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);

        Sanctum::actingAs($joiner);

        $this->postJson("/api/v1/bookings/{$booking->id}/join")->assertOk();

        $this->assertDatabaseHas('booking_participants', [
            'booking_id' => $booking->id,
            'user_id' => $joiner->id,
            'payment_status' => 'pending',
        ]);
    }

    public function test_expire_command_removes_stale_pending_rows(): void
    {
        $booking = Booking::factory()->create(['max_players' => 4]);

        DB::table('booking_participants')->insert([
            'booking_id' => $booking->id,
            'user_id' => User::factory()->create()->id,
            'amount_due' => 25,
            'payment_status' => 'pending',
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);

        Artisan::call('bookings:expire-pending-participants');

        $this->assertDatabaseCount('booking_participants', 0);
    }
}
