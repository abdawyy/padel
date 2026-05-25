<?php

namespace App\Models\Concerns;

use App\Models\Scopes\AdminClubScope;
use Illuminate\Database\Eloquent\Builder;

trait ScopedToAdminClub
{
    public static function bootScopedToAdminClub(): void
    {
        static::addGlobalScope(new AdminClubScope(
            static::adminClubScopeColumn(),
        ));
    }

    protected static function adminClubScopeColumn(): string
    {
        return 'club_id';
    }

    public static function withoutAdminClubScope(): Builder
    {
        return static::withoutGlobalScope(AdminClubScope::class);
    }
}
