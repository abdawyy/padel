<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Notifications\BookingConfirmedNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_confirm_booking_with_unpaid_participants(): void
    {
        $owner = User::factory()->create(['is_active' => true]);
        $court = Court::factory()->create();
        $booking = Booking::factory()->create([
            'court_id' => $court->id,
            'owner_user_id' => $owner->id,
            'status' => 'pending',
        ]);

        DB::table('booking_participants')->insert([
            'booking_id' => $booking->id,
            'user_id' => $owner->id,
            'amount_due' => 50,
            'payment_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($owner);

        $this->patchJson("/api/bookings/{$booking->id}", [
            'status' => 'confirmed',
        ])
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Cannot confirm booking until all participants have completed payment.',
            ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'pending',
        ]);
    }

    public function test_can_confirm_booking_when_all_participants_paid(): void
    {
        $owner = User::factory()->create(['is_active' => true]);
        $court = Court::factory()->create();
        $booking = Booking::factory()->create([
            'court_id' => $court->id,
            'owner_user_id' => $owner->id,
            'status' => 'pending',
        ]);

        DB::table('booking_participants')->insert([
            'booking_id' => $booking->id,
            'user_id' => $owner->id,
            'amount_due' => 50,
            'payment_status' => 'paid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($owner);

        $this->patchJson("/api/bookings/{$booking->id}", [
            'status' => 'confirmed',
        ])->assertOk();

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_booking_confirmed_notification_uses_start_time_date(): void
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->create([
            'start_time' => Carbon::parse('2026-07-15 18:30:00'),
            'end_time' => Carbon::parse('2026-07-15 19:30:00'),
        ]);

        $mail = (new BookingConfirmedNotification($booking))->toMail($user);

        $this->assertStringContainsString('2026-07-15', $mail->subject);
        $this->assertStringContainsString('2026-07-15', implode("\n", $mail->introLines));
        $this->assertSame(url('/player/my-matches'), $mail->actionUrl);
    }
}
