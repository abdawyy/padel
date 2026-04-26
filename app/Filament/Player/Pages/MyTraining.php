<?php

namespace App\Filament\Player\Pages;

use App\Models\AcademySession;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MyTraining extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    public function getView(): string
    {
        return 'filament.player.pages.my-training';
    }

    protected static ?string $navigationLabel = 'My Training';

    protected static ?string $title = 'My Training Sessions';

    protected static ?int $navigationSort = 3;

    public function getSessions(): Collection
    {
        return AcademySession::query()
            ->whereHas('players', fn (Builder $q) => $q->where('users.id', auth()->id()))
            ->with(['club', 'court', 'coach'])
            ->withCount('players')
            ->orderByRaw("FIELD(status,'scheduled','ongoing','completed','cancelled')")
            ->orderBy('start_time')
            ->get();
    }

    public function withdraw(int $sessionId): void
    {
        $session = AcademySession::query()
            ->whereHas('players', fn (Builder $q) => $q->where('users.id', auth()->id()))
            ->whereIn('status', ['scheduled'])
            ->findOrFail($sessionId);

        $session->players()->detach(auth()->id());

        Notification::make()
            ->title('Withdrawn from session')
            ->body('You have been removed from "' . $session->title . '".')
            ->success()
            ->send();
    }
}
