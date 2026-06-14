<?php

namespace App\Support;

use App\Models\User;

class AdminClubContext
{
    public const SESSION_KEY = 'admin_club_id';

    public static function id(): ?int
    {
        $value = session(self::SESSION_KEY);

        return $value !== null && $value !== '' ? (int) $value : null;
    }

    public static function set(?int $clubId): void
    {
        if ($clubId === null) {
            session()->forget(self::SESSION_KEY);

            return;
        }

        session([self::SESSION_KEY => $clubId]);
    }

    public static function shouldFilter(?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user || ! static::id()) {
            return false;
        }

        return true;
    }
}
