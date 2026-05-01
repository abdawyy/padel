<?php

namespace App\Filament\Saas\Widgets;

use App\Models\Club;
use App\Models\ClubSaasSubscription;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SaasStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalAcademies = Club::query()->count();

        $activeSubscriptions = ClubSaasSubscription::query()
            ->where('status', 'active')
            ->where('ends_at', '>=', now())
            ->count();

        $trialSubscriptions = ClubSaasSubscription::query()
            ->where('status', 'trial')
            ->where('ends_at', '>=', now())
            ->count();

        $expiringSoon = ClubSaasSubscription::query()
            ->where('status', 'active')
            ->whereBetween('ends_at', [now(), now()->addDays(7)])
            ->count();

        $revenueThisMonth = ClubSaasSubscription::query()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount_paid');

        $revenueThisYear = ClubSaasSubscription::query()
            ->whereYear('created_at', now()->year)
            ->sum('amount_paid');

        return [
            Stat::make('Total Academies', $totalAcademies)
                ->description('All registered clubs/academies')
                ->descriptionIcon('heroicon-o-building-storefront')
                ->color('primary'),

            Stat::make('Active Subscriptions', $activeSubscriptions)
                ->description("{$trialSubscriptions} on trial")
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Expiring in 7 Days', $expiringSoon)
                ->description('Subscriptions needing renewal')
                ->descriptionIcon('heroicon-o-clock')
                ->color($expiringSoon > 0 ? 'warning' : 'success'),

            Stat::make('Revenue This Month', '$' . number_format((float) $revenueThisMonth, 2))
                ->description('Year total: $' . number_format((float) $revenueThisYear, 2))
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('info'),
        ];
    }
}
