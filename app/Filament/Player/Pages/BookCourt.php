<?php

namespace App\Filament\Player\Pages;

use App\Models\Club;
use App\Models\Court;
use App\Services\PaymobService;
use App\Services\PlayerBookingService;
use App\Services\PlayerCourtSlotAvailability;
use BackedEnum;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class BookCourt extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $navigationLabel = 'Book a Court';

    protected static ?string $title = 'Book a Court';

    protected static ?int $navigationSort = 2;

    public ?string $clubId = null;

    public ?string $courtId = null;

    public ?string $bookingDate = null;

    public ?string $slotStart = null;

    public ?string $slotEnd = null;

    public string $matchType = 'private_match';

    public ?string $notes = null;

    public ?string $paymentIframeUrl = null;

    public function mount(): void
    {
        $this->bookingDate = now()->toDateString();
    }

    public function getView(): string
    {
        return 'filament.player.pages.book-court';
    }

    public function getClubs(): Collection
    {
        return Club::query()
            ->where('registration_status', 'approved')
            ->whereIn('subscription_status', ['active', 'trial'])
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getCourts(): Collection
    {
        if (! $this->clubId) {
            return collect();
        }

        return Court::query()
            ->where('club_id', (int) $this->clubId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sport_type', 'price_per_hour', 'slot_duration_minutes']);
    }

    public function getAvailableSlots(): Collection
    {
        if (! $this->courtId || ! $this->bookingDate) {
            return collect();
        }

        $court = Court::query()->find((int) $this->courtId);

        if (! $court) {
            return collect();
        }

        try {
            $date = Carbon::parse($this->bookingDate);
        } catch (\Exception) {
            return collect();
        }

        return app(PlayerCourtSlotAvailability::class)->availableSlots($court, $date);
    }

    public function createBooking(): void
    {
        $user = auth()->user();

        if (! $this->clubId || ! $this->courtId || ! $this->bookingDate || ! $this->slotStart || ! $this->slotEnd) {
            Notification::make()->title('Complete all fields')->danger()->send();

            return;
        }

        $court = Court::query()->with('club')->findOrFail((int) $this->courtId);

        if ((int) $court->club_id !== (int) $this->clubId) {
            Notification::make()->title('Invalid court')->danger()->send();

            return;
        }

        $startTime = Carbon::parse($this->slotStart);
        $endTime = Carbon::parse($this->slotEnd);

        try {
            $result = app(PlayerBookingService::class)->create($user, $court, $startTime, $endTime, [
                'match_type' => $this->matchType,
                'notes' => $this->notes,
            ]);

            $payment = app(PaymobService::class)->createPaymentSessionForParticipant(
                $result['booking'],
                $user,
                $result['owner_amount_due'],
            );

            $this->paymentIframeUrl = $payment['iframe_url'] ?? null;

            Notification::make()
                ->title('Booking created')
                ->body('Complete payment below to confirm your reservation.')
                ->success()
                ->send();
        } catch (\RuntimeException $exception) {
            $message = match ($exception->getMessage()) {
                'scheduling_conflict' => 'This court is not available for the selected time.',
                'player_conflict' => 'You already have a booking or session at this time.',
                default => 'Could not create booking.',
            };

            Notification::make()->title($message)->danger()->send();
        } catch (\Throwable $exception) {
            Notification::make()->title('Booking failed')->body($exception->getMessage())->danger()->send();
        }
    }

    public function selectSlot(string $start, string $end): void
    {
        $this->slotStart = $start;
        $this->slotEnd = $end;
    }

    public function closePayment(): void
    {
        $this->paymentIframeUrl = null;
        $this->slotStart = null;
        $this->slotEnd = null;
    }
}
