<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\PaymobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function pay(Request $request, Booking $booking, PaymobService $paymobService): JsonResponse
    {
        $user = $request->user();

        $participant = $booking->participants()
            ->where('users.id', $user->id)
            ->first();

        if (! $participant) {
            return response()->json(['message' => 'You are not a participant in this booking.'], 403);
        }

        if ($participant->pivot->payment_status === 'paid') {
            return response()->json(['message' => 'Your payment for this booking is already completed.'], 409);
        }

        $paymentSession = $paymobService->createPaymentSessionForParticipant(
            $booking,
            $user,
            (float) $participant->pivot->amount_due,
        );

        return response()->json($paymentSession);
    }
}
