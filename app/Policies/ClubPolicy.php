<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\User;

class ClubPolicy
{
    public function view(User $user, Club $club): bool
    {
        return $user->belongsToClub($club) || $user->isSuperAdmin();
    }

    public function update(User $user, Club $club): bool
    {
        return $user->canManageClub($club) || $user->isSuperAdmin();
    }

    public function delete(User $user, Club $club): bool
    {
        return $user->canManageClub($club) || $user->isSuperAdmin();
    }

    public function manageStaff(User $user, Club $club): bool
    {
        return $user->canManageClub($club) || $user->isSuperAdmin();
    }

    public function viewAvailability(User $user, Club $club): bool
    {
        return $user->hasAdminAccess($club) || $user->belongsToClub($club);
    }

    public function manageSubscription(User $user, Club $club): bool
    {
        return $user->canManageClub($club) || $user->isSuperAdmin();
    }
}
