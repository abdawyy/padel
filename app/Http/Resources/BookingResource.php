<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'sport_type' => $this->sport_type,
            'owner_user_id' => $this->owner_user_id,
            'coach_user_id' => $this->coach_user_id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'total_price' => (float) $this->total_price,
            'coach_fee' => (float) $this->coach_fee,
            'match_type' => $this->match_type,
            'session_type' => $this->session_type,
            'max_players' => (int) $this->max_players,
            'skill_level' => $this->skill_level,
            'status' => $this->status,
            'notes' => $this->notes,
            'court' => CourtResource::make($this->whenLoaded('court')),
            'owner' => [
                'id' => $this->whenLoaded('owner', fn () => $this->owner?->id),
                'name' => $this->whenLoaded('owner', fn () => $this->owner?->name),
                'email' => $this->whenLoaded('owner', fn () => $this->owner?->email),
            ],
            'coach' => $this->whenLoaded('coach', function () {
                return $this->coach ? [
                    'id' => $this->coach->id,
                    'name' => $this->coach->name,
                    'email' => $this->coach->email,
                ] : null;
            }),
            'participants' => $this->whenLoaded('participants', function () {
                return $this->participants->map(function ($participant) {
                    return [
                        'id' => $participant->id,
                        'name' => $participant->name,
                        'email' => $participant->email,
                        'amount_due' => (float) $participant->pivot->amount_due,
                        'payment_status' => $participant->pivot->payment_status,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
