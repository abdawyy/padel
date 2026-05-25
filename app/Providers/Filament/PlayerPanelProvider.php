<?php

namespace App\Providers\Filament;

use App\Filament\Player\Pages\BookCourt;
use App\Filament\Player\Pages\BrowseAcademy;
use App\Filament\Player\Pages\MyMatches;
use App\Filament\Player\Pages\MyPackages;
use App\Filament\Player\Pages\MyTraining;
use App\Filament\Player\Pages\OpenMatches;
use App\Filament\Player\Pages\PlayerDashboard;
use App\Http\Middleware\SetPlayerLocale;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PlayerPanelProvider extends PanelProvider
{
    use SharedPanelConfiguration;

    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->id('player')
            ->path('player')
            ->login()
            ->brandName(config('app.name').' — Player Portal')
            ->pages([
                PlayerDashboard::class,
                BookCourt::class,
                MyPackages::class,
                MyTraining::class,
                MyMatches::class,
                OpenMatches::class,
                BrowseAcademy::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                SetPlayerLocale::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);

        return $this->applyBrand($panel);
    }
}
