<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademySession;
use App\Models\Booking;
use App\Models\ClubSaasSubscription;
use App\Models\User;
use App\Notifications\BookingConfirmedNotification;
use App\Models\PaymentTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function transactionProcessed(Request $request): JsonResponse
    {
        $hmacSecret = (string) config('services.paymob.hmac_secret', '');
        if ($hmacSecret === '') {
            Log::error('Paymob webhook rejected: PAYMOB_HMAC_SECRET is not configured.');

            return response()->json([
                'message' => 'Paymob webhook is not configured. Set PAYMOB_HMAC_SECRET in your environment.',
            ], 503);
        }

        $hmac = (string) ($request->query('hmac') ?? $request->input('hmac', ''));
        $transaction = $request->input('obj', []);

        if (! is_array($transaction) || ! $this->isValidHmac($transaction, $hmac, $hmacSecret)) {
            abort(403, 'Invalid Paymob webhook signature.');
        }

        $paymobTransactionId = (string) data_get($transaction, 'id', '');
        if ($paymobTransactionId === '') {
            return response()->json(['message' => 'Missing Paymob transaction id.'], 422);
        }

        $isSuccess = (bool) data_get($transaction, 'success', false);
        $transactionStatus = $isSuccess ? 'success' : 'failed';

        $merchantOrderId = (string) data_get($transaction, 'order.merchant_order_id', '');
        $order = $this->parseMerchantOrderId($merchantOrderId);

        if ($order === null) {
            return response()->json(['message' => 'Invalid merchant_order_id payload.'], 422);
        }

        $isRefunded = (bool) data_get($transaction, 'is_refunded', false);
        $isVoided = (bool) data_get($transaction, 'is_voided', false);

        if ($isRefunded || $isVoided) {
            return $this->processRefundOrVoid($order, $transaction, $paymobTransactionId);
        }

        $existingTransaction = PaymentTransaction::query()
            ->where('paymob_transaction_id', $paymobTransactionId)
            ->first();

        if ($existingTransaction && $existingTransaction->status === 'success') {
            return response()->json(['status' => 'already_processed']);
        }

        $amountCents = (int) data_get($transaction, 'amount_cents', 0);
        $amount = round($amountCents / 100, 2);

        if ($isSuccess) {
            $expectedAmount = $this->expectedAmountForOrder($order);
            if ($expectedAmount !== null && abs($amount - $expectedAmount) > 0.01) {
                Log::warning('Paymob webhook amount mismatch.', [
                    'merchant_order_id' => $merchantOrderId,
                    'expected' => $expectedAmount,
                    'received' => $amount,
                ]);

                return response()->json(['message' => 'Payment amount does not match the expected charge.'], 422);
            }
        }

        $paymentAttributes = [
            'booking_id' => $order['type'] === 'booking' ? $order['reference_id'] : null,
            'academy_session_id' => $order['type'] === 'session' ? $order['reference_id'] : null,
            'club_saas_subscription_id' => $order['type'] === 'saas' ? $order['reference_id'] : null,
            'user_id' => $order['user_id'],
            'paymob_transaction_id' => $paymobTransactionId,
            'amount' => $amount,
            'status' => $transactionStatus,
            'provider_payload' => $transaction,
        ];

        if (! $existingTransaction) {
            PaymentTransaction::query()->create($paymentAttributes);
        } else {
            $existingTransaction->update($paymentAttributes);
        }

        if (! $isSuccess) {
            return response()->json(['status' => 'ignored']);
        }

        if ($order['type'] === 'saas') {
            return $this->activateSaasSubscription($order['reference_id'], $paymobTransactionId);
        }

        if ($order['type'] === 'session') {
            return $this->enrollPlayerInSession($order['reference_id'], $order['user_id']);
        }

        return $this->processBookingPayment($order['reference_id'], $order['user_id']);
    }

    private function activateSaasSubscription(int $subscriptionId, string $paymobTransactionId): JsonResponse
    {
        DB::transaction(function () use ($subscriptionId, $paymobTransactionId) {
            $subscription = ClubSaasSubscription::query()->lockForUpdate()->find($subscriptionId);
            if (! $subscription || $subscription->status !== 'pending') {
                return;
            }

            ClubSaasSubscription::query()
                ->where('club_id', $subscription->club_id)
                ->where('id', '!=', $subscription->id)
                ->whereIn('status', ['active', 'past_due'])
                ->update(['status' => 'cancelled']);

            $subscription->applyBillingPeriodFrom();
            $subscription->status = 'active';
            $subscription->payment_reference = $paymobTransactionId;
            $subscription->save();

            $subscription->syncClubStatus();
        });

        return response()->json(['status' => 'saas_activated']);
    }

    private function enrollPlayerInSession(int $sessionId, int $userId): JsonResponse
    {
        $enrolled = DB::transaction(function () use ($sessionId, $userId) {
            $session = AcademySession::query()->lockForUpdate()->find($sessionId);
            if (! $session) {
                return false;
            }

            if ($session->players()->where('users.id', $userId)->exists()) {
                return true;
            }

            $playerCount = $session->players()->count();
            if ($playerCount >= (int) $session->max_players) {
                Log::warning('Session enrollment rejected: capacity full after payment.', [
                    'academy_session_id' => $sessionId,
                    'user_id' => $userId,
                ]);

                return false;
            }

            $session->players()->attach($userId, [
                'status' => 'registered',
                'notes'  => 'Enrolled via payment.',
            ]);

            return true;
        });

        return response()->json([
            'status' => $enrolled ? 'session_enrolled' : 'session_full',
        ]);
    }

    private function processBookingPayment(int $bookingId, int $userId): JsonResponse
    {
        DB::transaction(function () use ($bookingId, $userId) {
            DB::table('booking_participants')
                ->where('booking_id', $bookingId)
                ->where('user_id', $userId)
                ->where('payment_status', 'pending')
                ->update([
                    'payment_status' => 'paid',
                    'updated_at' => now(),
                ]);

            $hasUnpaidParticipants = DB::table('booking_participants')
                ->where('booking_id', $bookingId)
                ->where('payment_status', '!=', 'paid')
                ->exists();

            if (! $hasUnpaidParticipants) {
                Booking::query()
                    ->where('id', $bookingId)
                    ->update([
                        'status' => 'confirmed',
                        'updated_at' => now(),
                    ]);

                $booking = Booking::with('court.club')->find($bookingId);
                if ($booking) {
                    $participantIds = DB::table('booking_participants')
                        ->where('booking_id', $bookingId)
                        ->pluck('user_id');
                    User::whereIn('id', $participantIds)->get()
                        ->each(fn (User $u) => $u->notify(new BookingConfirmedNotification($booking)));
                }
            }
        });

        return response()->json(['status' => 'processed']);
    }

    /**
     * @param  array{type: 'booking'|'session'|'saas', reference_id: int, user_id: int}  $order
     * @param  array<string, mixed>  $transaction
     */
    private function processRefundOrVoid(array $order, array $transaction, string $paymobTransactionId): JsonResponse
    {
        $amountCents = (int) data_get($transaction, 'amount_cents', 0);
        $amount = round($amountCents / 100, 2);

        PaymentTransaction::query()->updateOrCreate(
            ['paymob_transaction_id' => $paymobTransactionId],
            [
                'booking_id' => $order['type'] === 'booking' ? $order['reference_id'] : null,
                'academy_session_id' => $order['type'] === 'session' ? $order['reference_id'] : null,
                'club_saas_subscription_id' => $order['type'] === 'saas' ? $order['reference_id'] : null,
                'user_id' => $order['user_id'],
                'amount' => $amount,
                'status' => 'refunded',
                'provider_payload' => $transaction,
            ],
        );

        return match ($order['type']) {
            'booking' => $this->reverseBookingPayment($order['reference_id'], $order['user_id']),
            'session' => $this->reverseSessionEnrollment($order['reference_id'], $order['user_id']),
            'saas' => $this->reverseSaasSubscription($order['reference_id']),
            default => response()->json(['status' => 'ignored']),
        };
    }

    private function reverseBookingPayment(int $bookingId, int $userId): JsonResponse
    {
        DB::transaction(function () use ($bookingId, $userId) {
            DB::table('booking_participants')
                ->where('booking_id', $bookingId)
                ->where('user_id', $userId)
                ->where('payment_status', 'paid')
                ->update([
                    'payment_status' => 'refunded',
                    'updated_at' => now(),
                ]);

            $hasUnpaidParticipants = DB::table('booking_participants')
                ->where('booking_id', $bookingId)
                ->where('payment_status', '!=', 'paid')
                ->exists();

            if ($hasUnpaidParticipants) {
                Booking::query()
                    ->where('id', $bookingId)
                    ->where('status', 'confirmed')
                    ->update([
                        'status' => 'pending',
                        'updated_at' => now(),
                    ]);
            }
        });

        return response()->json(['status' => 'booking_refunded']);
    }

    private function reverseSessionEnrollment(int $sessionId, int $userId): JsonResponse
    {
        DB::transaction(function () use ($sessionId, $userId) {
            $session = AcademySession::query()->lockForUpdate()->find($sessionId);
            if (! $session) {
                return;
            }

            $session->players()->detach($userId);
        });

        return response()->json(['status' => 'session_refunded']);
    }

    private function reverseSaasSubscription(int $subscriptionId): JsonResponse
    {
        DB::transaction(function () use ($subscriptionId) {
            $subscription = ClubSaasSubscription::query()->lockForUpdate()->find($subscriptionId);
            if (! $subscription || $subscription->status !== 'active') {
                return;
            }

            $subscription->update(['status' => 'cancelled']);
            $subscription->syncClubStatus();
        });

        return response()->json(['status' => 'saas_refunded']);
    }

    /**
     * @param  array{type: string, reference_id: int, user_id: int}  $order
     */
    private function expectedAmountForOrder(array $order): ?float
    {
        return match ($order['type']) {
            'booking' => $this->expectedBookingParticipantAmount($order['reference_id'], $order['user_id']),
            'session' => $this->expectedSessionEnrollmentAmount($order['reference_id']),
            'saas' => $this->expectedSaasSubscriptionAmount($order['reference_id']),
            default => null,
        };
    }

    private function expectedBookingParticipantAmount(int $bookingId, int $userId): ?float
    {
        $participant = DB::table('booking_participants')
            ->where('booking_id', $bookingId)
            ->where('user_id', $userId)
            ->first();

        return $participant ? (float) $participant->amount_due : null;
    }

    private function expectedSessionEnrollmentAmount(int $sessionId): ?float
    {
        $session = AcademySession::query()->find($sessionId);

        return $session ? (float) $session->price_per_player : null;
    }

    private function expectedSaasSubscriptionAmount(int $subscriptionId): ?float
    {
        $subscription = ClubSaasSubscription::query()->find($subscriptionId);

        return $subscription ? (float) $subscription->amount_paid : null;
    }

    private function isValidHmac(array $transaction, string $receivedHmac, string $hmacSecret): bool
    {
        if ($receivedHmac === '') {
            return false;
        }

        $orderedValues = [
            data_get($transaction, 'amount_cents'),
            data_get($transaction, 'created_at'),
            data_get($transaction, 'currency'),
            data_get($transaction, 'error_occured'),
            data_get($transaction, 'has_parent_transaction'),
            data_get($transaction, 'id'),
            data_get($transaction, 'integration_id'),
            data_get($transaction, 'is_3d_secure'),
            data_get($transaction, 'is_auth'),
            data_get($transaction, 'is_capture'),
            data_get($transaction, 'is_refunded'),
            data_get($transaction, 'is_standalone_payment'),
            data_get($transaction, 'is_voided'),
            data_get($transaction, 'order.id'),
            data_get($transaction, 'owner'),
            data_get($transaction, 'pending'),
            data_get($transaction, 'source_data.pan'),
            data_get($transaction, 'source_data.sub_type'),
            data_get($transaction, 'source_data.type'),
            data_get($transaction, 'success'),
        ];

        $payload = collect($orderedValues)
            ->map(fn ($value) => $this->normalizeHmacValue($value))
            ->implode('');

        $computedHmac = hash_hmac('sha512', $payload, $hmacSecret);

        return hash_equals(strtolower($computedHmac), strtolower($receivedHmac));
    }

    private function normalizeHmacValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return '';
        }

        return (string) $value;
    }

    /**
     * @return array{type: 'booking'|'session'|'saas', reference_id: int, user_id: int}|null
     */
    private function parseMerchantOrderId(string $merchantOrderId): ?array
    {
        if (preg_match('/^session_(\d+)_user_(\d+)$/', $merchantOrderId, $matches)) {
            return [
                'type' => 'session',
                'reference_id' => (int) $matches[1],
                'user_id' => (int) $matches[2],
            ];
        }

        if (preg_match('/^booking_(\d+)_user_(\d+)$/', $merchantOrderId, $matches)) {
            return [
                'type' => 'booking',
                'reference_id' => (int) $matches[1],
                'user_id' => (int) $matches[2],
            ];
        }

        if (preg_match('/^saas_(\d+)_user_(\d+)$/', $merchantOrderId, $matches)) {
            return [
                'type' => 'saas',
                'reference_id' => (int) $matches[1],
                'user_id' => (int) $matches[2],
            ];
        }

        return null;
    }
}
