<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Notifications\BookingCancelledNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingCancellationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_cancel_booking_and_record_refund(): void
    {
        Notification::fake();

        config([
            'services.paymob.api_key' => 'test-key',
            'services.paymob.base_url' => 'https://accept.paymob.com/api',
            'booking.cancellation.full_refund_hours_before' => 24,
        ]);

        Http::fake([
            'https://accept.paymob.com/api/auth/tokens' => Http::response(['token' => 'auth-token']),
            'https://accept.paymob.com/api/acceptance/void_refund/refund' => Http::response(['success' => true]),
        ]);

        $owner = User::factory()->create(['is_active' => true]);
        $court = Court::factory()->create(['is_active' => true]);
        $court->club->update([
            'registration_status' => 'approved',
            'subscription_status' => 'active',
        ]);

        $booking = Booking::factory()->create([
            'court_id' => $court->id,
            'owner_user_id' => $owner->id,
            'status' => 'confirmed',
            'start_time' => Carbon::now()->addDays(3),
            'end_time' => Carbon::now()->addDays(3)->addHour(),
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

        PaymentTransaction::query()->create([
            'booking_id' => $booking->id,
            'user_id' => $owner->id,
            'paymob_transaction_id' => 'txn_cancel_001',
            'amount' => 100,
            'status' => 'success',
            'provider_payload' => [],
        ]);

        Sanctum::actingAs($owner);

        $this->postJson("/api/bookings/{$booking->id}/cancel", ['reason' => 'Schedule conflict'])
            ->assertOk()
            ->assertJsonPath('booking.status', 'cancelled');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('payment_transactions', [
            'booking_id' => $booking->id,
            'user_id' => $owner->id,
            'status' => 'refunded',
        ]);

        Notification::assertSentTo($owner, BookingCancelledNotification::class);
    }

    public function test_participant_can_leave_open_match(): void
    {
        $owner = User::factory()->create(['is_active' => true]);
        $guest = User::factory()->create(['is_active' => true]);
        $court = Court::factory()->create(['is_active' => true]);

        $booking = Booking::factory()->create([
            'court_id' => $court->id,
            'owner_user_id' => $owner->id,
            'status' => 'confirmed',
            'match_type' => 'open_match',
            'start_time' => Carbon::now()->addDays(2),
            'end_time' => Carbon::now()->addDays(2)->addHour(),
        ]);

        $booking->participants()->attach($owner->id, ['amount_due' => 50, 'payment_status' => 'paid']);
        $booking->participants()->attach($guest->id, ['amount_due' => 50, 'payment_status' => 'paid']);

        Sanctum::actingAs($guest);

        $this->postJson("/api/bookings/{$booking->id}/leave")
            ->assertOk();

        $this->assertDatabaseMissing('booking_participants', [
            'booking_id' => $booking->id,
            'user_id' => $guest->id,
        ]);
    }
}
