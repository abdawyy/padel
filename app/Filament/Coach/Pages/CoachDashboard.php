<?php

namespace App\Filament\Coach\Pages;

use App\Models\AcademySession;
use App\Models\Booking;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class CoachDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $title = 'Coach Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 1;

    public function getView(): string
    {
        return 'filament.coach.pages.coach-dashboard';
    }

    public function getUpcomingSessionsCount(): int
    {
        return AcademySession::query()
            ->where('coach_user_id', auth()->id())
            ->where('start_time', '>=', now())
            ->count();
    }

    public function getUpcomingMatchesCount(): int
    {
        return Booking::query()
            ->where('coach_user_id', auth()->id())
            ->where('start_time', '>=', now())
            ->count();
    }

    public function getTotalCoachedSessionsCount(): int
    {
        return AcademySession::query()
            ->where('coach_user_id', auth()->id())
            ->count();
    }

    public function getUniquePlayersCount(): int
    {
        return AcademySession::query()
            ->where('coach_user_id', auth()->id())
            ->join('academy_session_user', 'academy_sessions.id', '=', 'academy_session_user.academy_session_id')
            ->distinct('academy_session_user.user_id')
            ->count('academy_session_user.user_id');
    }
}
