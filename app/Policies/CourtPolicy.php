<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\Court;
use App\Models\User;

class CourtPolicy
{
    public function viewAny(User $user, Club $club): bool
    {
        return $user->hasAdminAccess($club);
    }

    public function create(User $user, Club $club): bool
    {
        return $user->hasAdminAccess($club);
    }

    public function update(User $user, Court $court): bool
    {
        return $user->hasAdminAccess($court->club);
    }

    public function delete(User $user, Court $court): bool
    {
        return $user->hasAdminAccess($court->club);
    }
}
