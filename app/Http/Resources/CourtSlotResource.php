<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourtSlotResource extends JsonResource
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
            'court_id' => $this->court_id,
            'title' => $this->title,
            'sport_type' => $this->sport_type,
            'slot_type' => $this->slot_type,
            'day_of_week' => (int) $this->day_of_week,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'max_players' => (int) $this->max_players,
            'price' => (float) $this->price,
            'skill_level' => $this->skill_level,
            'is_active' => (bool) $this->is_active,
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
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
