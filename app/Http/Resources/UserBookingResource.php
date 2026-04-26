<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $participant = $this->whenLoaded('participants', fn () => $this->participants->first());

        return [
            'id' => $this->id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'match_type' => $this->match_type,
            'status' => $this->status,
            'total_price' => (float) $this->total_price,
            'court' => [
                'id' => $this->court?->id,
                'name' => $this->court?->name,
            ],
            'club' => [
                'id' => $this->court?->club?->id,
                'name' => $this->court?->club?->name,
            ],
            'user_payment' => [
                'amount_due' => $participant ? (float) $participant->pivot->amount_due : null,
                'payment_status' => $participant?->pivot->payment_status,
            ],
        ];
    }
}
