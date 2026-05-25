<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PlayerBookingService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{booking: Booking, owner_amount_due: float}
     */
    public function create(User $user, Court $court, Carbon $startTime, Carbon $endTime, array $data): array
    {
        if ($unavailable = BookableCourtValidator::bookingUnavailableResponse($court)) {
            throw new \RuntimeException(json_encode($unavailable->getData(true)));
        }

        $pricing = app(BookingPriceCalculator::class)->calculate(
            $court,
            $startTime,
            $endTime,
            $data['coach_user_id'] ?? null,
        );
        $totalPrice = $pricing['total_price'];
        $coachFee = $pricing['coach_fee'];

        $matchType = $data['match_type'] ?? 'private_match';
        $maxPlayers = (int) ($data['max_players'] ?? ($matchType === 'open_match' ? 4 : 1));
        $participantCount = max($maxPlayers, 1);
        $splitAmounts = BookingPaymentSplit::split($totalPrice, $participantCount);
        $ownerAmount = (float) ($splitAmounts[0] ?? $totalPrice);

        $sessionType = ! empty($data['coach_user_id'])
            ? ($matchType === 'open_match' ? 'coached_match' : 'private_training')
            : ($matchType === 'open_match' ? 'open_match' : 'standard');

        $booking = DB::transaction(function () use ($user, $court, $startTime, $endTime, $totalPrice, $coachFee, $matchType, $maxPlayers, $sessionType, $data, $splitAmounts, $ownerAmount) {
            if ($this->courtHasSchedulingConflict($court->id, $startTime, $endTime)) {
                throw new \RuntimeException('scheduling_conflict');
            }

            if ($this->playerHasSchedulingConflict($user->id, $startTime, $endTime)) {
                throw new \RuntimeException('player_conflict');
            }

            $booking = Booking::query()->create([
                'court_id' => $court->id,
                'sport_type' => $data['sport_type'] ?? $court->sport_type ?? 'padel',
                'owner_user_id' => $user->id,
                'coach_user_id' => $data['coach_user_id'] ?? null,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'total_price' => $totalPrice,
                'coach_fee' => $coachFee,
                'match_type' => $matchType,
                'session_type' => $sessionType,
                'max_players' => $maxPlayers,
                'skill_level' => $data['skill_level'] ?? null,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            $booking->participants()->attach([
                $user->id => [
                    'amount_due' => $ownerAmount,
                    'payment_status' => 'pending',
                ],
            ]);

            return $booking->load(['court.club', 'owner']);
        });

        return ['booking' => $booking, 'owner_amount_due' => $ownerAmount];
    }

    private function courtHasSchedulingConflict(int $courtId, Carbon $startTime, Carbon $endTime): bool
    {
        if (Booking::query()
            ->where('court_id', $courtId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->lockForUpdate()
            ->exists()) {
            return true;
        }

        return \App\Models\AcademySession::query()
            ->where('court_id', $courtId)
            ->whereIn('status', ['scheduled', 'active'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->lockForUpdate()
            ->exists();
    }

    private function playerHasSchedulingConflict(int $playerId, Carbon $startTime, Carbon $endTime): bool
    {
        $bookingConflict = Booking::query()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->where(function ($query) use ($playerId) {
                $query->where('owner_user_id', $playerId)
                    ->orWhereHas('participants', fn ($q) => $q->where('users.id', $playerId));
            })
            ->exists();

        if ($bookingConflict) {
            return true;
        }

        return \App\Models\AcademySession::query()
            ->whereIn('status', ['scheduled', 'active'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->whereHas('players', fn ($q) => $q->where('users.id', $playerId))
            ->exists();
    }
}
