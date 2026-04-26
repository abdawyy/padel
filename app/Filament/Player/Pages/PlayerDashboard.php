<?php

namespace App\Filament\Player\Pages;

use App\Models\Booking;
use App\Models\User;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class PlayerDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    public function getView(): string
    {
        return 'filament.player.pages.player-dashboard';
    }

    protected static ?string $title = 'My Padel Profile';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 1;

    public function getUser(): User
    {
        return auth()->user();
    }

    public function getSkillLabel(): string
    {
        $level = $this->getUser()->skill_level;

        return $level ? User::skillLevelLabel($level) : 'Not set';
    }

    public function getActivePackages(): Collection
    {
        return $this->getUser()
            ->packages()
            ->wherePivot('status', 'active')
            ->with('club')
            ->get();
    }

    public function getUpcomingTraining(): Collection
    {
        return $this->getUser()
            ->academySessions()
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->with(['club', 'coach'])
            ->take(5)
            ->get();
    }

    public function getUpcomingMatches(): Collection
    {
        return Booking::query()
            ->where('start_time', '>=', now())
            ->where(function ($q) {
                $q->where('owner_user_id', auth()->id())
                    ->orWhereHas('participants', fn ($sub) => $sub->where('users.id', auth()->id()));
            })
            ->orderBy('start_time')
            ->with(['court.club'])
            ->take(5)
            ->get();
    }

    public function getTotalMatchesCount(): int
    {
        return Booking::query()
            ->where(function ($q) {
                $q->where('owner_user_id', auth()->id())
                    ->orWhereHas('participants', fn ($sub) => $sub->where('users.id', auth()->id()));
            })
            ->count();
    }

    public function getActivePackagesCount(): int
    {
        return $this->getUser()
            ->packages()
            ->wherePivot('status', 'active')
            ->count();
    }

    public function getUpcomingSessionsCount(): int
    {
        return $this->getUser()
            ->academySessions()
            ->where('start_time', '>=', now())
            ->count();
    }
}
