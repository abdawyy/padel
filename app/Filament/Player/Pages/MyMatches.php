<?php

namespace App\Filament\Player\Pages;

use App\Exceptions\BookingCancellationException;
use App\Models\Booking;
use App\Services\BookingCancellationService;
use App\Services\BookingPaymentService;
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

    public ?string $paymentIframeUrl = null;

    public function getMatches(): Collection
    {
        return Booking::query()
            ->where(function (Builder $q) {
                $q->where('owner_user_id', auth()->id())
                    ->orWhereHas('participants', fn (Builder $sub) => $sub->where('users.id', auth()->id()));
            })
            ->with(['court.club', 'owner', 'coach', 'participants'])
            ->orderByRaw("FIELD(status,'pending','confirmed','cancelled')")
            ->orderBy('start_time')
            ->get()
            ->sortBy([
                fn (Booking $booking) => match ($booking->display_status) {
                    'pending' => 0,
                    'confirmed' => 1,
                    'completed' => 2,
                    'cancelled' => 3,
                    default => 4,
                },
                fn (Booking $booking) => $booking->start_time?->timestamp ?? 0,
            ])
            ->values();
    }

    public function payOutstanding(int $bookingId): void
    {
        $booking = Booking::query()
            ->whereHas('participants', fn (Builder $q) => $q->where('users.id', auth()->id()))
            ->whereIn('status', ['pending', 'confirmed'])
            ->with('participants')
            ->findOrFail($bookingId);

        try {
            $paymentSession = app(BookingPaymentService::class)
                ->createParticipantPayment($booking, auth()->user());

            $this->paymentIframeUrl = $paymentSession['iframe_url'] ?? null;

            Notification::make()
                ->title('Complete payment')
                ->body('Use the checkout window to complete your match payment.')
                ->success()
                ->send();
        } catch (\RuntimeException $exception) {
            Notification::make()
                ->title(match ($exception->getMessage()) {
                    'already_paid' => 'Already paid',
                    'cancelled_booking' => 'Booking is cancelled',
                    'not_participant' => 'You are not a participant',
                    default => 'Payment could not be started',
                })
                ->danger()
                ->send();
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Payment failed')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function cancelBooking(int $bookingId): void
    {
        $booking = Booking::query()
            ->where('owner_user_id', auth()->id())
            ->whereIn('status', ['pending', 'confirmed'])
            ->findOrFail($bookingId);

        try {
            app(BookingCancellationService::class)->cancel($booking, auth()->user());

            Notification::make()
                ->title('Match cancelled')
                ->body('Your booking for "' . ($booking->court->name ?? 'court') . '" has been cancelled.')
                ->success()
                ->send();
        } catch (BookingCancellationException $exception) {
            Notification::make()
                ->title('Cancellation failed')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function leaveMatch(int $bookingId): void
    {
        $booking = Booking::query()
            ->whereHas('participants', fn (Builder $q) => $q->where('users.id', auth()->id()))
            ->whereIn('status', ['pending', 'confirmed'])
            ->findOrFail($bookingId);

        try {
            app(BookingCancellationService::class)->leave($booking, auth()->user());

            Notification::make()
                ->title('Left match')
                ->body('You have left the match at "' . ($booking->court->name ?? 'court') . '".')
                ->success()
                ->send();
        } catch (BookingCancellationException $exception) {
            Notification::make()
                ->title('Could not leave match')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function closePayment(): void
    {
        $this->paymentIframeUrl = null;
    }
}
