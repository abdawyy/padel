<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BookingParticipantCapacity
{
    public static function holdMinutes(): int
    {
        return max(1, (int) config('booking.pending_payment_hold_minutes', 30));
    }

    public static function pendingHoldCutoff(): \Illuminate\Support\Carbon
    {
        return now()->subMinutes(self::holdMinutes());
    }

    /**
     * Participants that consume a capacity slot (paid, or pending within the hold window).
     */
    public static function capacityQuery(int $bookingId): \Illuminate\Database\Query\Builder
    {
        $cutoff = self::pendingHoldCutoff();

        return DB::table('booking_participants')
            ->where('booking_id', $bookingId)
            ->where(function ($query) use ($cutoff) {
                $query->where('payment_status', 'paid')
                    ->orWhere(function ($pending) use ($cutoff) {
                        $pending->where('payment_status', 'pending')
                            ->where('created_at', '>=', $cutoff);
                    });
            });
    }

    public static function countForBooking(int $bookingId): int
    {
        return (int) self::capacityQuery($bookingId)->count();
    }

    public static function isFull(Booking $booking): bool
    {
        $maxPlayers = max((int) $booking->max_players, 1);

        return self::countForBooking((int) $booking->id) >= $maxPlayers;
    }

    public static function addCapacityCount(Builder $query, string $alias = 'capacity_slots_used'): Builder
    {
        $cutoff = self::pendingHoldCutoff();

        return $query->withCount([
            'participants as '.$alias => function ($participantQuery) use ($cutoff) {
                $participantQuery->where('payment_status', 'paid')
                    ->orWhere(function ($pending) use ($cutoff) {
                        $pending->where('payment_status', 'pending')
                            ->where('booking_participants.created_at', '>=', $cutoff);
                    });
            },
        ]);
    }

    public static function expireStalePendingParticipants(): int
    {
        return DB::table('booking_participants')
            ->where('payment_status', 'pending')
            ->where('created_at', '<', self::pendingHoldCutoff())
            ->delete();
    }
}
