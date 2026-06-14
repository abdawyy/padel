<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AdminClubQuery
{
    /**
     * Apply admin panel club filtering (selected club or accessible clubs).
     */
    public static function forUser(Builder $query, User $user, string $column = 'club_id'): Builder
    {
        if ($clubId = AdminClubContext::id()) {
            return $query->where($column, $clubId);
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        $clubIds = $user->accessibleClubIds();

        return empty($clubIds)
            ? $query->whereRaw('1 = 0')
            : $query->whereIn($column, $clubIds);
    }
}
