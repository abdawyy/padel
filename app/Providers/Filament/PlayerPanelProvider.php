<?php

namespace App\Providers\Filament;

use App\Filament\Player\Pages\MyMatches;
use App\Filament\Player\Pages\MyPackages;
use App\Filament\Player\Pages\MyTraining;
use App\Filament\Player\Pages\PlayerDashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PlayerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('player')
            ->path('player')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->brandName(config('app.name') . ' — Player Portal')
            ->pages([
                PlayerDashboard::class,
                MyPackages::class,
                MyTraining::class,
                MyMatches::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
