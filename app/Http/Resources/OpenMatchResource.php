<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpenMatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $participantsCount = (int) ($this->participants_count ?? $this->participants->count());
        $maxPlayers = (int) ($this->max_players ?? 4);

        return [
            'id' => $this->id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'match_type' => $this->match_type,
            'session_type' => $this->session_type,
            'sport_type' => $this->sport_type,
            'total_price' => (float) $this->total_price,
            'coach_fee' => (float) $this->coach_fee,
            'max_players' => $maxPlayers,
            'participants_count' => $participantsCount,
            'spots_left' => max(0, $maxPlayers - $participantsCount),
            'skill_level' => $this->skill_level,
            'skill_min'   => $this->skill_min,
            'skill_max'   => $this->skill_max,
            'coach' => $this->coach ? [
                'id' => $this->coach->id,
                'name' => $this->coach->name,
                'email' => $this->coach->email,
            ] : null,
            'court' => [
                'id' => $this->court?->id,
                'name' => $this->court?->name,
                'price_per_hour' => $this->court ? (float) $this->court->price_per_hour : null,
            ],
            'club' => [
                'id' => $this->court?->club?->id,
                'name' => $this->court?->club?->name,
            ],
            'participants' => $this->participants->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'name' => $participant->name,
                    'avatar' => null,
                ];
            })->values(),
        ];
    }
}
