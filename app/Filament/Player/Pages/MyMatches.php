<?php

namespace App\Filament\Player\Pages;

use App\Models\Booking;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MyMatches extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    public function getView(): string
    {
        return 'filament.player.pages.my-matches';
    }

    protected static ?string $navigationLabel = 'My Matches';

    protected static ?string $title = 'My Matches';

    protected static ?int $navigationSort = 4;

    public function getMatches(): Collection
    {
        return Booking::query()
            ->where(function (Builder $q) {
                $q->where('owner_user_id', auth()->id())
                    ->orWhereHas('participants', fn (Builder $sub) => $sub->where('users.id', auth()->id()));
            })
            ->with(['court.club', 'owner', 'coach'])
            ->orderByRaw("FIELD(status,'pending','confirmed','completed','cancelled')")
            ->orderBy('start_time')
            ->get();
    }

    public function cancelBooking(int $bookingId): void
    {
        $booking = Booking::query()
            ->where('owner_user_id', auth()->id())
            ->whereIn('status', ['pending', 'confirmed'])
            ->findOrFail($bookingId);

        $booking->update(['status' => 'cancelled']);

        Notification::make()
            ->title('Match cancelled')
            ->body('Your booking for "' . ($booking->court->name ?? 'court') . '" has been cancelled.')
            ->success()
            ->send();
    }

    public function leaveMatch(int $bookingId): void
    {
        $booking = Booking::query()
            ->whereHas('participants', fn (Builder $q) => $q->where('users.id', auth()->id()))
            ->whereIn('status', ['pending', 'confirmed'])
            ->findOrFail($bookingId);

        $booking->participants()->detach(auth()->id());

        Notification::make()
            ->title('Left match')
            ->body('You have left the match at "' . ($booking->court->name ?? 'court') . '".')
            ->success()
            ->send();
    }
}
