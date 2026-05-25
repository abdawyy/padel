<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\CourtSlot;
use App\Models\User;

class CourtSlotPolicy
{
    public function viewAny(User $user, Club $club): bool
    {
        return $user->hasAdminAccess($club);
    }

    public function create(User $user, Club $club): bool
    {
        return $user->hasAdminAccess($club);
    }

    public function update(User $user, CourtSlot $courtSlot): bool
    {
        return $user->hasAdminAccess($courtSlot->court?->club);
    }

    public function delete(User $user, CourtSlot $courtSlot): bool
    {
        return $user->hasAdminAccess($courtSlot->court?->club);
    }

    public function schedule(User $user, Club $club, CourtSlot $courtSlot): bool
    {
        return $user->hasAdminAccess($club)
            && $courtSlot->court?->club_id === $club->id;
    }
}
