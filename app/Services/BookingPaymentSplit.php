<?php

namespace App\Services;

/**
 * Split a booking total across N participants so amounts sum exactly to the total.
 * The first slot receives any rounding remainder (typically the owner).
 */
class BookingPaymentSplit
{
    /**
     * @return list<float>
     */
    public static function split(float $totalPrice, int $participantCount): array
    {
        $count = max($participantCount, 1);
        $totalCents = (int) round($totalPrice * 100);
        $baseCents = intdiv($totalCents, $count);
        $remainderCents = $totalCents - ($baseCents * $count);

        $amounts = array_fill(0, $count, round($baseCents / 100, 2));
        $amounts[0] = round(($baseCents + $remainderCents) / 100, 2);

        return $amounts;
    }

    public static function amountForSlot(float $totalPrice, int $maxPlayers, int $slotIndex): float
    {
        $amounts = self::split($totalPrice, $maxPlayers);

        return $amounts[$slotIndex] ?? $amounts[0];
    }
}
