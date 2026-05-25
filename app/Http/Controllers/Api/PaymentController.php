<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function pay(Request $request, Booking $booking, BookingPaymentService $bookingPayment): JsonResponse
    {
        $this->authorize('pay', $booking);

        try {
            $paymentSession = $bookingPayment->createParticipantPayment(
                $booking,
                $request->user(),
                $request->header('X-Idempotency-Key'),
            );
        } catch (\RuntimeException $exception) {
            return match ($exception->getMessage()) {
                'cancelled_booking' => response()->json(['message' => 'Payments are not accepted for cancelled bookings.'], 422),
                'not_participant' => response()->json(['message' => 'You are not a participant in this booking.'], 403),
                'already_paid' => response()->json(['message' => 'Your payment for this booking is already completed.'], 409),
                default => throw $exception,
            };
        }

        return response()->json($paymentSession);
    }
}
