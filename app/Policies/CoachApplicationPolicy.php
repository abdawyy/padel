<?php

namespace App\Policies;

use App\Models\AcademySession;
use App\Models\CoachApplication;
use App\Models\User;

class CoachApplicationPolicy
{
    public function apply(User $user, AcademySession $session): bool
    {
        return $user->role === 'coach'
            && $user->belongsToClub($session->club)
            && in_array($session->status, ['scheduled', 'active'], true)
            && $session->coach_user_id === null;
    }

    public function viewAny(User $user, AcademySession $session): bool
    {
        return $user->hasAdminAccess($session->club) || $user->isSuperAdmin();
    }

    public function respond(User $user, CoachApplication $application): bool
    {
        $session = $application->session;

        return $session && ($user->hasAdminAccess($session->club) || $user->isSuperAdmin());
    }

    public function withdraw(User $user, CoachApplication $application): bool
    {
        return $application->coach_user_id === $user->id && $application->isPending();
    }
}
