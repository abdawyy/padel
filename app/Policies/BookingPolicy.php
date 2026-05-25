<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        return $booking->owner_user_id === $user->id
            || $booking->participants()->where('users.id', $user->id)->exists()
            || $user->hasAdminAccess($booking->court?->club);
    }

    public function pay(User $user, Booking $booking): bool
    {
        return $booking->participants()->where('users.id', $user->id)->exists();
    }
}
