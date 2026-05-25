<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MatchmakingJoinTest extends TestCase
{
    use RefreshDatabase;

    public function test_paymob_failure_removes_pending_participant(): void
    {
        config([
            'services.paymob.api_key' => 'test-key',
            'services.paymob.integration_id' => 12345,
            'services.paymob.iframe_id' => 'iframe-1',
            'services.paymob.base_url' => 'https://accept.paymob.com/api',
        ]);

        Http::fake([
            'https://accept.paymob.com/api/auth/tokens' => Http::response(['token' => 'auth-token']),
            'https://accept.paymob.com/api/ecommerce/orders' => Http::response(['id' => 9001], 500),
        ]);

        $user = User::factory()->create(['is_active' => true]);
        $court = Court::factory()->create();
        $booking = Booking::factory()->create([
            'court_id' => $court->id,
            'match_type' => 'open_match',
            'status' => 'pending',
            'start_time' => Carbon::now()->addDay(),
            'end_time' => Carbon::now()->addDay()->addHour(),
            'max_players' => 4,
            'total_price' => 200,
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/bookings/{$booking->id}/join")->assertServerError();

        $this->assertDatabaseMissing('booking_participants', [
            'booking_id' => $booking->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_successful_join_creates_pending_participant(): void
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

        $user = User::factory()->create(['is_active' => true]);
        $court = Court::factory()->create();
        $booking = Booking::factory()->create([
            'court_id' => $court->id,
            'match_type' => 'open_match',
            'status' => 'pending',
            'start_time' => Carbon::now()->addDay(),
            'end_time' => Carbon::now()->addDay()->addHour(),
            'max_players' => 4,
            'total_price' => 200,
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/bookings/{$booking->id}/join")->assertOk();

        $this->assertDatabaseHas('booking_participants', [
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'payment_status' => 'pending',
        ]);
    }
}
