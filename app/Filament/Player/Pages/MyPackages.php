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

    public string $statusFilter = 'all';

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
            ->when($this->statusFilter === 'active', fn ($q) => $q->where('status', 'active'))
            ->when($this->statusFilter === 'expired', fn ($q) => $q->whereIn('status', ['expired', 'cancelled']))
            ->with('package.club')
            ->orderByRaw("FIELD(status,'active','suspended','expired','cancelled')")
            ->orderBy('expires_at')
            ->get();
    }

    public function setStatusFilter(string $filter): void
    {
        if (! in_array($filter, ['all', 'active', 'expired'], true)) {
            return;
        }

        $this->statusFilter = $filter;
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
