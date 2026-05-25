<?php

namespace App\Services;

use App\Exceptions\BookingCancellationException;
use App\Models\Booking;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Notifications\BookingCancelledNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class BookingCancellationService
{
    public function __construct(private readonly PaymobService $paymobService) {}

    /**
     * @return array{booking: Booking, refunds: array<int, array<string, mixed>>}
     */
    public function cancel(Booking $booking, User $actor, ?string $reason = null): array
    {
        $this->authorizeCancel($booking, $actor);
        $this->assertCancellable($booking);

        return DB::transaction(function () use ($booking, $reason) {
            $booking = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            $this->assertCancellable($booking);

            $refunds = $this->refundPaidParticipants($booking);

            $booking->update(['status' => 'cancelled']);

            $this->notifyParticipants($booking->fresh(['court.club', 'participants']), $reason);

            return [
                'booking' => $booking->fresh(['court.club', 'owner', 'coach', 'participants']),
                'refunds' => $refunds,
            ];
        });
    }

    /**
     * @return array{booking: Booking, refund: array<string, mixed>|null}
     */
    public function leave(Booking $booking, User $participant): array
    {
        if (! in_array($booking->status, ['pending', 'confirmed'], true)) {
            throw new BookingCancellationException('This match is no longer active.');
        }

        if ($booking->owner_user_id === $participant->id) {
            throw new BookingCancellationException('Booking owners must cancel the entire booking instead of leaving.');
        }

        if (! $booking->participants()->where('users.id', $participant->id)->exists()) {
            throw new BookingCancellationException('You are not a participant in this booking.', 403);
        }

        if (Carbon::parse($booking->start_time)->lte(now())) {
            throw new BookingCancellationException('You cannot leave a match that has already started.');
        }

        return DB::transaction(function () use ($booking, $participant) {
            $refund = $this->refundParticipantIfEligible($booking, $participant->id);

            $booking->participants()->detach($participant->id);

            return [
                'booking' => $booking->fresh(['court.club', 'owner', 'coach', 'participants']),
                'refund' => $refund,
            ];
        });
    }

    private function authorizeCancel(Booking $booking, User $actor): void
    {
        if ((int) $booking->owner_user_id === (int) $actor->id) {
            return;
        }

        $booking->loadMissing('court.club');

        if ($actor->hasAdminAccess($booking->court?->club)) {
            return;
        }

        throw new BookingCancellationException('You are not allowed to cancel this booking.', 403);
    }

    private function assertCancellable(Booking $booking): void
    {
        if ($booking->status === 'cancelled') {
            throw new BookingCancellationException('This booking is already cancelled.');
        }

        if (! in_array($booking->status, ['pending', 'confirmed'], true)) {
            throw new BookingCancellationException('This booking cannot be cancelled.');
        }

        if (
            ! config('booking.cancellation.allow_cancel_after_start', false)
            && Carbon::parse($booking->start_time)->lte(now())
        ) {
            throw new BookingCancellationException('Bookings cannot be cancelled after they have started.');
        }
    }

  /**
     * @return array<int, array<string, mixed>>
     */
    private function refundPaidParticipants(Booking $booking): array
    {
        $refunds = [];

        $paidParticipantIds = DB::table('booking_participants')
            ->where('booking_id', $booking->id)
            ->where('payment_status', 'paid')
            ->pluck('user_id');

        foreach ($paidParticipantIds as $userId) {
            $refund = $this->refundParticipantIfEligible($booking, (int) $userId);
            if ($refund !== null) {
                $refunds[] = $refund;
            }
        }

        return $refunds;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function refundParticipantIfEligible(Booking $booking, int $userId): ?array
    {
        $participant = DB::table('booking_participants')
            ->where('booking_id', $booking->id)
            ->where('user_id', $userId)
            ->first();

        if (! $participant || $participant->payment_status !== 'paid') {
            return null;
        }

        if (! $this->isRefundEligible($booking)) {
            return null;
        }

        $amount = (float) $participant->amount_due;
        $payment = PaymentTransaction::query()
            ->where('booking_id', $booking->id)
            ->where('user_id', $userId)
            ->where('status', 'success')
            ->latest('id')
            ->first();

        $refundStatus = 'refund_pending';
        $providerPayload = ['source' => 'booking_cancellation'];

        if ($payment && $this->paymobConfigured()) {
            try {
                $this->paymobService->refundTransaction($payment->paymob_transaction_id, $amount);
                $refundStatus = 'refunded';
                $providerPayload['paymob_transaction_id'] = $payment->paymob_transaction_id;
            } catch (RuntimeException $exception) {
                Log::warning('Paymob refund failed during booking cancellation.', [
                    'booking_id' => $booking->id,
                    'user_id' => $userId,
                    'error' => $exception->getMessage(),
                ]);
            }
        } elseif ($payment) {
            $refundStatus = 'refunded';
            $providerPayload['manual'] = true;
        }

        PaymentTransaction::query()->create([
            'booking_id' => $booking->id,
            'user_id' => $userId,
            'paymob_transaction_id' => sprintf(
                'refund_booking_%d_user_%d_%s',
                $booking->id,
                $userId,
                now()->format('YmdHis'),
            ),
            'amount' => $amount,
            'status' => $refundStatus,
            'provider_payload' => $providerPayload,
        ]);

        DB::table('booking_participants')
            ->where('booking_id', $booking->id)
            ->where('user_id', $userId)
            ->update([
                'payment_status' => $refundStatus,
                'updated_at' => now(),
            ]);

        return [
            'user_id' => $userId,
            'amount' => $amount,
            'status' => $refundStatus,
        ];
    }

    private function isRefundEligible(Booking $booking): bool
    {
        $hoursBefore = (int) config('booking.cancellation.full_refund_hours_before', 24);

        return Carbon::parse($booking->start_time)->greaterThan(now()->addHours($hoursBefore));
    }

    private function paymobConfigured(): bool
    {
        return filled(config('services.paymob.api_key'));
    }

    private function notifyParticipants(Booking $booking, ?string $reason): void
    {
        $participantIds = DB::table('booking_participants')
            ->where('booking_id', $booking->id)
            ->pluck('user_id')
            ->push($booking->owner_user_id)
            ->unique()
            ->filter();

        User::query()
            ->whereIn('id', $participantIds)
            ->get()
            ->each(fn (User $user) => $user->notify(new BookingCancelledNotification($booking, $reason)));
    }
}
