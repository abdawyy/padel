<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentCancelledBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_pay_rejects_cancelled_booking(): void
    {
        config([
            'services.paymob.api_key' => 'test-key',
            'services.paymob.integration_id' => 12345,
            'services.paymob.iframe_id' => 'iframe-1',
            'services.paymob.base_url' => 'https://accept.paymob.com/api',
        ]);

        Http::fake();

        $user = User::factory()->create(['is_active' => true]);
        $booking = Booking::factory()->create([
            'owner_user_id' => $user->id,
            'status' => 'cancelled',
        ]);

        DB::table('booking_participants')->insert([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'amount_due' => 50.00,
            'payment_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/bookings/{$booking->id}/pay")
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Payments are not accepted for cancelled bookings.',
            ]);
    }
}
