<?php

namespace App\Services;

use App\Models\AcademySession;
use App\Models\Booking;
use App\Models\Club;
use App\Models\CourtSlot;
use App\Models\User;
use App\Support\CourtSlotSessionTypeMapper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CourtSlotSchedulingService
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function schedule(
        CourtSlot $courtSlot,
        Club $club,
        User $createdBy,
        Carbon $date,
        array $options = [],
    ): AcademySession {
        if ($courtSlot->court?->club_id !== $club->id) {
            throw new \InvalidArgumentException('The selected slot does not belong to this club.');
        }

        if ($date->dayOfWeek !== (int) $courtSlot->day_of_week) {
            throw new \RuntimeException('day_mismatch');
        }

        $startTime = Carbon::parse($date->toDateString().' '.$courtSlot->start_time);
        $endTime = Carbon::parse($date->toDateString().' '.$courtSlot->end_time);

        if ($endTime->lte($startTime)) {
            $endTime->addDay();
        }

        $playerIds = collect($options['player_ids'] ?? [])->unique()->take($courtSlot->max_players);

        foreach ($playerIds as $playerId) {
            if ($this->playerHasConflict((int) $playerId, $startTime, $endTime)) {
                throw new \RuntimeException('player_conflict');
            }
        }

        return DB::transaction(function () use ($courtSlot, $club, $createdBy, $startTime, $endTime, $playerIds, $options) {
            if ($this->hasCourtConflict($courtSlot->court_id, $startTime, $endTime)) {
                throw new \RuntimeException('scheduling_conflict');
            }

            $session = AcademySession::query()->create([
                'club_id' => $club->id,
                'court_id' => $courtSlot->court_id,
                'coach_user_id' => $courtSlot->coach_user_id,
                'created_by_user_id' => $createdBy->id,
                'title' => $options['title'] ?? $courtSlot->title,
                'sport_type' => $courtSlot->sport_type,
                'session_type' => CourtSlotSessionTypeMapper::toSessionType($courtSlot->slot_type),
                'skill_level' => $courtSlot->skill_level,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'max_players' => $courtSlot->max_players,
                'price_per_player' => $options['price_per_player'] ?? $courtSlot->price,
                'status' => $options['status'] ?? 'scheduled',
                'notes' => $options['notes'] ?? null,
            ]);

            if ($playerIds->isNotEmpty()) {
                $session->players()->attach(
                    $playerIds->mapWithKeys(fn (int $playerId) => [$playerId => ['status' => 'assigned', 'notes' => null]])->all()
                );
            }

            return $session;
        });
    }

    private function hasCourtConflict(int $courtId, Carbon $startTime, Carbon $endTime): bool
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

        return AcademySession::query()
            ->where('court_id', $courtId)
            ->whereIn('status', ['scheduled', 'active'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->lockForUpdate()
            ->exists();
    }

    private function playerHasConflict(int $playerId, Carbon $startTime, Carbon $endTime): bool
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

        return AcademySession::query()
            ->whereIn('status', ['scheduled', 'active'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->whereHas('players', fn ($q) => $q->where('users.id', $playerId))
            ->exists();
    }
}
