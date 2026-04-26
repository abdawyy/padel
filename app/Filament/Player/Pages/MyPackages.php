<?php

namespace App\Filament\Player\Pages;

use App\Models\PackageSubscription;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class MyPackages extends Page
{

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    public function getView(): string
    {
        return 'filament.player.pages.my-packages';
    }

    protected static ?string $navigationLabel = 'My Packages';

    protected static ?string $title = 'My Packages';

    protected static ?int $navigationSort = 2;

    public function getSubscriptions(): Collection
    {
        return PackageSubscription::query()
            ->where('user_id', auth()->id())
            ->with('package.club')
            ->orderByRaw("FIELD(status,'active','suspended','expired','cancelled')")
            ->orderBy('expires_at')
            ->get();
    }

    public function typeColor(string $type): string
    {
        return match ($type) {
            'sessions'  => '#0ea5e9',
            'monthly'   => '#10b981',
            'quarterly' => '#f59e0b',
            'yearly'    => '#8b5cf6',
            default     => '#6b7280',
        };
    }
}
