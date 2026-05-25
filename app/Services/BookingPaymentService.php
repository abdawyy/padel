<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use RuntimeException;

class BookingPaymentService
{
    public function __construct(
        private readonly PaymobService $paymob,
        private readonly PaymentIdempotency $idempotency,
    ) {}

    public function createParticipantPayment(Booking $booking, User $user, ?string $idempotencyKey = null): array
    {
        if ($booking->status === 'cancelled') {
            throw new RuntimeException('cancelled_booking');
        }

        $participant = $booking->participants()
            ->where('users.id', $user->id)
            ->first();

        if (! $participant) {
            throw new RuntimeException('not_participant');
        }

        if ($participant->pivot->payment_status === 'paid') {
            throw new RuntimeException('already_paid');
        }

        return $this->idempotency->resolve($user, $idempotencyKey, fn () => $this->paymob->createPaymentSessionForParticipant(
            $booking,
            $user,
            (float) $participant->pivot->amount_due,
        ));
    }
}
