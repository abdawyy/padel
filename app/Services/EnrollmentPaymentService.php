<?php

namespace App\Services;

use App\Models\AcademySession;
use App\Models\User;
use RuntimeException;

class EnrollmentPaymentService
{
    public function __construct(
        private readonly PaymobService $paymob,
        private readonly PaymentIdempotency $idempotency,
    ) {}

    public function createEnrollmentPayment(AcademySession $session, User $payer, float $amountDue, ?string $idempotencyKey = null): array
    {
        if ($amountDue <= 0) {
            throw new RuntimeException('free_session');
        }

        if (! in_array($session->status, ['scheduled', 'active'], true)) {
            throw new RuntimeException('session_closed');
        }

        return $this->idempotency->resolve($payer, $idempotencyKey, fn () => $this->paymob->createPaymentSessionForEnrollment(
            $session,
            $payer,
            $amountDue,
        ));
    }
}
