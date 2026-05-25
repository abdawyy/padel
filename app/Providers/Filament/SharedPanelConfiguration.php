<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\Support\Colors\Color;

trait SharedPanelConfiguration
{
    protected function applyBrand(Panel $panel): Panel
    {
        $primary = config('brand.primary_hex', '#d97706');

        return $panel
            ->brandLogo(asset(config('brand.logo')))
            ->favicon(asset(config('brand.favicon')))
            ->colors([
                'primary' => Color::hex($primary),
            ]);
    }
}
