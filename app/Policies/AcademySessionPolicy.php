<?php

namespace App\Policies;

use App\Models\AcademySession;
use App\Models\Club;
use App\Models\User;

class AcademySessionPolicy
{
    public function viewAny(User $user, Club $club): bool
    {
        return $user->hasAdminAccess($club);
    }

    public function create(User $user, Club $club): bool
    {
        return $user->hasAdminAccess($club);
    }

    public function update(User $user, AcademySession $session): bool
    {
        return $user->hasAdminAccess($session->club);
    }

    public function cancel(User $user, AcademySession $session): bool
    {
        return $user->hasAdminAccess($session->club);
    }

    public function enrollOthers(User $user, AcademySession $session): bool
    {
        return $user->hasAdminAccess($session->club);
    }
}
