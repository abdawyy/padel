<?php

namespace App\Services;

use App\Models\Court;
use Carbon\Carbon;

class BookingPriceCalculator
{
    /**
     * @return array{total_price: float, coach_fee: float, duration_hours: float}
     */
    public function calculate(Court $court, Carbon $startTime, Carbon $endTime, ?int $coachUserId = null): array
    {
        $durationMinutes = max($startTime->diffInMinutes($endTime), 1);
        $durationHours = $durationMinutes / 60;
        $coachFee = $this->coachFee($court, $coachUserId, $durationHours);
        $courtFee = round(((float) $court->price_per_hour) * $durationHours, 2);
        $totalPrice = round($courtFee + $coachFee, 2);

        return [
            'total_price' => $totalPrice,
            'coach_fee' => $coachFee,
            'duration_hours' => $durationHours,
        ];
    }

    public function coachFee(Court $court, ?int $coachUserId, float $durationHours): float
    {
        if (empty($coachUserId)) {
            return 0.0;
        }

        $ratePerHour = (float) data_get($court->club?->settings, 'coach_fee_per_hour', 0);

        return round($ratePerHour * $durationHours, 2);
    }
}
