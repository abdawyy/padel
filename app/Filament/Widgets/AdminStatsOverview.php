<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\PaymentTransaction;
use App\Support\AdminClubContext;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $bookingsQuery = Booking::query()
            ->whereBetween('start_time', [$todayStart, $todayEnd])
            ->whereIn('status', ['pending', 'confirmed']);

        $this->scopeToClub($bookingsQuery);

        $todayBookings = (clone $bookingsQuery)->count();
        $todayRevenue = (float) (clone $bookingsQuery)->sum('total_price');

        $paymentsQuery = PaymentTransaction::query()
            ->where('status', 'success')
            ->whereDate('created_at', today());

        if ($clubId = AdminClubContext::id()) {
            $paymentsQuery->where(function (Builder $q) use ($clubId) {
                $q->whereHas('booking.court', fn (Builder $c) => $c->where('club_id', $clubId))
                    ->orWhereHas('academySession', fn (Builder $s) => $s->where('club_id', $clubId));
            });
        } elseif ($user = auth()->user()) {
            if (! $user->isSuperAdmin()) {
                $clubIds = $user->accessibleClubIds();
                $paymentsQuery->where(function (Builder $q) use ($clubIds) {
                    $q->whereHas('booking.court', fn (Builder $c) => $c->whereIn('club_id', $clubIds))
                        ->orWhereHas('academySession', fn (Builder $s) => $s->whereIn('club_id', $clubIds));
                });
            }
        }

        $paymentsToday = (float) $paymentsQuery->sum('amount');

        return [
            Stat::make("Today's bookings", (string) $todayBookings)
                ->description('Scheduled for today')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('primary'),
            Stat::make("Today's booking value", number_format($todayRevenue, 0).' '.config('brand.currency', 'EGP'))
                ->description('Total price of today\'s bookings')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),
            Stat::make('Payments today', number_format($paymentsToday, 0).' '.config('brand.currency', 'EGP'))
                ->description('Successful Paymob transactions')
                ->descriptionIcon('heroicon-o-credit-card')
                ->color('info'),
        ];
    }

    private function scopeToClub(Builder $query): void
    {
        if ($clubId = AdminClubContext::id()) {
            $query->whereHas('court', fn (Builder $q) => $q->where('club_id', $clubId));

            return;
        }

        $user = auth()->user();
        if ($user && ! $user->isSuperAdmin()) {
            $clubIds = $user->accessibleClubIds();
            $query->whereHas('court', fn (Builder $q) => $q->whereIn('club_id', $clubIds));
        }
    }
}
