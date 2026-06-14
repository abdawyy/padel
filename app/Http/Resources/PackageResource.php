<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'club_id' => $this->club_id,
            'name' => $this->name,
            'sport_type' => $this->sport_type,
            'type' => $this->type,
            'session_count' => $this->session_count,
            'max_players' => (int) $this->max_players,
            'duration_days' => $this->duration_days,
            'price' => (float) $this->price,
            'price_per_player' => (float) $this->price_per_player,
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
