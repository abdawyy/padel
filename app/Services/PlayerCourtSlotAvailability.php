<?php

namespace App\Services;

use App\Models\AcademySession;
use App\Models\Booking;
use App\Models\Court;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PlayerCourtSlotAvailability
{
    /**
     * @return Collection<int, array{start: string, end: string, label: string}>
     */
    public function availableSlots(Court $court, Carbon $date): Collection
    {
        $duration = max((int) $court->slot_duration_minutes, 30);
        $dayStart = $date->copy()->startOfDay()->setTime(6, 0);
        $dayEnd = $date->copy()->startOfDay()->setTime(23, 0);

        $busy = $this->busyIntervals($court, $date);

        $slots = collect();
        $cursor = $dayStart->copy();

        while ($cursor->copy()->addMinutes($duration)->lte($dayEnd)) {
            $slotEnd = $cursor->copy()->addMinutes($duration);

            if ($slotEnd->lte(now())) {
                $cursor->addMinutes($duration);
                continue;
            }

            $overlaps = $busy->contains(function (array $interval) use ($cursor, $slotEnd) {
                return $cursor->lt($interval['end']) && $slotEnd->gt($interval['start']);
            });

            if (! $overlaps) {
                $slots->push([
                    'start' => $cursor->toIso8601String(),
                    'end' => $slotEnd->toIso8601String(),
                    'label' => $cursor->format('H:i').' – '.$slotEnd->format('H:i'),
                ]);
            }

            $cursor->addMinutes($duration);
        }

        return $slots;
    }

    /**
     * @return Collection<int, array{start: Carbon, end: Carbon}>
     */
    private function busyIntervals(Court $court, Carbon $date): Collection
    {
        $dayStart = $date->copy()->startOfDay();
        $dayEnd = $date->copy()->endOfDay();

        $bookings = Booking::query()
            ->where('court_id', $court->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_time', '<', $dayEnd)
            ->where('end_time', '>', $dayStart)
            ->get(['start_time', 'end_time']);

        $sessions = AcademySession::query()
            ->where('court_id', $court->id)
            ->whereIn('status', ['scheduled', 'active'])
            ->where('start_time', '<', $dayEnd)
            ->where('end_time', '>', $dayStart)
            ->get(['start_time', 'end_time']);

        return $bookings->map(fn (Booking $b) => [
            'start' => $b->start_time,
            'end' => $b->end_time,
        ])->merge($sessions->map(fn (AcademySession $s) => [
            'start' => $s->start_time,
            'end' => $s->end_time,
        ]));
    }
}
