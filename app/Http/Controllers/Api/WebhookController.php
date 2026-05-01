<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademySession;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingConfirmedNotification;
use App\Models\PaymentTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    public function transactionProcessed(Request $request): JsonResponse
    {
        $hmac = (string) ($request->query('hmac') ?? $request->input('hmac', ''));
        $transaction = $request->input('obj', []);

        if (! is_array($transaction) || ! $this->isValidHmac($transaction, $hmac)) {
            abort(403, 'Invalid Paymob webhook signature.');
        }

        $paymobTransactionId = (string) data_get($transaction, 'id', '');
        if ($paymobTransactionId === '') {
            return response()->json(['message' => 'Missing Paymob transaction id.'], 422);
        }

        $isSuccess = (bool) data_get($transaction, 'success', false);
        $transactionStatus = $isSuccess ? 'success' : 'failed';

        $merchantOrderId = (string) data_get($transaction, 'order.merchant_order_id', '');
        [$bookingId, $userId] = $this->extractBookingAndUserIds($merchantOrderId);

        if (! $bookingId || ! $userId) {
            return response()->json(['message' => 'Invalid merchant_order_id payload.'], 422);
        }

        $isSessionPayment = str_starts_with($merchantOrderId, 'session_');

        $existingTransaction = PaymentTransaction::query()
            ->where('paymob_transaction_id', $paymobTransactionId)
            ->first();

        if ($existingTransaction && $existingTransaction->status === 'success') {
            return response()->json(['status' => 'already_processed']);
        }

        $amountCents = (int) data_get($transaction, 'amount_cents', 0);
        $amount = round($amountCents / 100, 2);

        if (! $existingTransaction) {
            PaymentTransaction::query()->create([
                'booking_id' => $bookingId,
                'user_id' => $userId,
                'paymob_transaction_id' => $paymobTransactionId,
                'amount' => $amount,
                'status' => $transactionStatus,
                'provider_payload' => $transaction,
            ]);
        } else {
            $existingTransaction->update([
                'booking_id' => $bookingId,
                'user_id' => $userId,
                'amount' => $amount,
                'status' => $transactionStatus,
                'provider_payload' => $transaction,
            ]);
        }

        if (! $isSuccess) {
            return response()->json(['status' => 'ignored']);
        }

        if ($isSessionPayment) {
            // Enroll the player in the academy session upon successful payment
            DB::transaction(function () use ($bookingId, $userId) {
                $session = AcademySession::find($bookingId);
                if (! $session) {
                    return;
                }

                $alreadyEnrolled = $session->players()->where('users.id', $userId)->exists();
                if (! $alreadyEnrolled) {
                    $session->players()->attach($userId, [
                        'status' => 'registered',
                        'notes'  => 'Enrolled via payment.',
                    ]);
                }
            });

            return response()->json(['status' => 'session_enrolled']);
        }

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

    private function isValidHmac(array $transaction, string $receivedHmac): bool
    {
        if ($receivedHmac === '') {
            return false;
        }

        $hmacSecret = (string) config('services.paymob.hmac_secret', '');
        if ($hmacSecret === '') {
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

    private function extractBookingAndUserIds(string $merchantOrderId): array
    {
        preg_match('/(?:booking|session)_(\d+)_user_(\d+)/', $merchantOrderId, $matches);

        $bookingId = isset($matches[1]) ? (int) $matches[1] : null;
        $userId    = isset($matches[2]) ? (int) $matches[2] : null;

        return [$bookingId, $userId];
    }
}
