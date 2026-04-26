<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademySessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'club_id' => $this->club_id,
            'club' => $this->whenLoaded('club', function () {
                return $this->club ? [
                    'id' => $this->club->id,
                    'name' => $this->club->name,
                    'sport_type' => $this->club->sport_type,
                ] : null;
            }),
            'court_id' => $this->court_id,
            'title' => $this->title,
            'sport_type' => $this->sport_type,
            'session_type' => $this->session_type,
            'skill_level' => $this->skill_level,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'max_players' => (int) $this->max_players,
            'players_count' => (int) ($this->players_count ?? $this->players->count()),
            'spots_left' => max(0, (int) $this->max_players - (int) ($this->players_count ?? $this->players->count())),
            'price_per_player' => (float) $this->price_per_player,
            'status' => $this->status,
            'notes' => $this->notes,
            'coach' => $this->coach ? [
                'id' => $this->coach->id,
                'name' => $this->coach->name,
                'email' => $this->coach->email,
            ] : null,
            'court' => $this->whenLoaded('court', function () {
                return [
                    'id' => $this->court?->id,
                    'name' => $this->court?->name,
                    'sport_type' => $this->court?->sport_type,
                    'price_per_hour' => $this->court ? (float) $this->court->price_per_hour : null,
                ];
            }),
            'players' => $this->whenLoaded('players', function () {
                return $this->players->map(function ($player) {
                    return [
                        'id' => $player->id,
                        'name' => $player->name,
                        'email' => $player->email,
                        'status' => $player->pivot?->status,
                        'notes' => $player->pivot?->notes,
                    ];
                })->values();
            }, []),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
