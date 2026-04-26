<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourtResource extends JsonResource
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
            'sport_type' => $this->sport_type,
            'name' => $this->name,
            'type' => $this->type,
            'price_per_hour' => (float) $this->price_per_hour,
            'capacity' => (int) $this->capacity,
            'slot_duration_minutes' => (int) $this->slot_duration_minutes,
            'is_active' => (bool) $this->is_active,
            'club' => ClubResource::make($this->whenLoaded('club')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
