<?php

namespace App\Support;

class ClubSubscriptionStatus
{
    public const ACTIVE = 'active';

    public const TRIAL = 'trial';

    public const INACTIVE = 'inactive';

    public const PAUSED = 'paused';

    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            self::ACTIVE => 'Active',
            self::TRIAL => 'Trial',
            self::PAUSED => 'Paused',
            self::INACTIVE => 'Inactive',
        ];
    }

    /** Statuses that allow public bookings and sessions. */
    public static function bookable(): array
    {
        return [self::ACTIVE, self::TRIAL];
    }

    public static function badgeColor(string $status): string
    {
        return match ($status) {
            self::ACTIVE => 'success',
            self::TRIAL => 'warning',
            self::PAUSED => 'info',
            self::INACTIVE => 'danger',
            default => 'gray',
        };
    }

    /** Normalize legacy values (cancelled → inactive). */
    public static function normalize(?string $status): string
    {
        return match ($status) {
            'cancelled' => self::INACTIVE,
            default => in_array($status, array_keys(self::options()), true) ? $status : self::INACTIVE,
        };
    }
}
