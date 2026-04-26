<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourtAvailabilityResource extends JsonResource
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
            'name' => $this->name,
            'sport_type' => $this->sport_type,
            'price_per_hour' => (float) $this->price_per_hour,
            'capacity' => (int) $this->capacity,
            'slot_duration_minutes' => (int) $this->slot_duration_minutes,
            'booked_slots' => $this->whenLoaded('bookings', function () {
                return $this->bookings->map(function ($booking) {
                    return [
                        'start_time' => $booking->start_time,
                        'end_time' => $booking->end_time,
                        'match_type' => $booking->match_type,
                        'session_type' => $booking->session_type,
                        'coach_user_id' => $booking->coach_user_id,
                        'max_players' => (int) $booking->max_players,
                    ];
                })->values();
            }, []),
            'academy_sessions' => $this->whenLoaded('academySessions', function () {
                return $this->academySessions->map(function ($session) {
                    return [
                        'id' => $session->id,
                        'title' => $session->title,
                        'start_time' => $session->start_time,
                        'end_time' => $session->end_time,
                        'session_type' => $session->session_type,
                        'status' => $session->status,
                        'max_players' => (int) $session->max_players,
                    ];
                })->values();
            }, []),
            'slot_templates' => $this->whenLoaded('slots', function () {
                return $this->slots->map(function ($slot) {
                    return [
                        'id' => $slot->id,
                        'title' => $slot->title,
                        'slot_type' => $slot->slot_type,
                        'day_of_week' => (int) $slot->day_of_week,
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time,
                        'max_players' => (int) $slot->max_players,
                        'is_active' => (bool) $slot->is_active,
                    ];
                })->values();
            }, []),
        ];
    }
}
