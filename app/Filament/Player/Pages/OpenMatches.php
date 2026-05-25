<?php

namespace App\Filament\Player\Pages;

use App\Models\Booking;
use App\Models\Club;
use App\Services\BookingParticipantCapacity;
use App\Services\BookingPaymentSplit;
use App\Services\PaymobService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OpenMatches extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public function getView(): string
    {
        return 'filament.player.pages.open-matches';
    }

    protected static ?string $navigationLabel = 'Find Matches';

    protected static ?string $title = 'Open Matches';

    protected static ?int $navigationSort = 3;

    public ?string $clubId = null;

    public ?string $sportType = null;

    public ?string $skillLevel = null;

    public ?string $paymentIframeUrl = null;

    public function getOpenMatches(): Collection
    {
        return BookingParticipantCapacity::addCapacityCount(
            Booking::query()
                ->where('match_type', 'open_match')
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('start_time', '>', now())
                ->when($this->clubId, fn (Builder $q) => $q->whereHas(
                    'court',
                    fn (Builder $court) => $court->where('club_id', (int) $this->clubId)
                ))
                ->when($this->sportType, fn (Builder $q) => $q->where('sport_type', $this->sportType))
                ->when($this->skillLevel, function (Builder $q) {
                    $skill = (int) $this->skillLevel;
                    $q->where(fn (Builder $inner) => $inner->whereNull('skill_min')->orWhere('skill_min', '<=', $skill))
                        ->where(fn (Builder $inner) => $inner->whereNull('skill_max')->orWhere('skill_max', '>=', $skill));
                })
                ->havingRaw('capacity_slots_used < max_players')
        )->with(['court.club', 'coach', 'participants'])
            ->orderBy('start_time')
            ->limit(50)
            ->get();
    }

    public function getClubs(): Collection
    {
        return Club::query()
            ->where('registration_status', 'approved')
            ->whereIn('subscription_status', ['active', 'trial'])
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function joinMatch(int $bookingId): void
    {
        $user = auth()->user();
        $booking = Booking::query()->findOrFail($bookingId);

        if ($booking->match_type !== 'open_match') {
            Notification::make()->title('Not an open match')->danger()->send();

            return;
        }

        $userSkill = (int) ($user->skill_level ?? 0);
        if ($userSkill > 0 && ! $booking->isSkillCompatible($userSkill)) {
            Notification::make()
                ->title('Skill level mismatch')
                ->body('Your skill level does not meet this match requirements.')
                ->danger()
                ->send();

            return;
        }

        try {
            [$freshBooking, $amountDue] = DB::transaction(function () use ($booking, $user) {
                $freshBooking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();

                if ($freshBooking->participants()->where('users.id', $user->id)->exists()) {
                    throw new \RuntimeException('already_joined');
                }

                $maxPlayers = max((int) $freshBooking->max_players, 1);
                $usedSlots = BookingParticipantCapacity::countForBooking((int) $freshBooking->id);

                if ($usedSlots >= $maxPlayers) {
                    throw new \RuntimeException('full');
                }

                $amountDue = BookingPaymentSplit::amountForSlot(
                    (float) $freshBooking->total_price,
                    $maxPlayers,
                    $usedSlots,
                );

                DB::table('booking_participants')->insert([
                    'booking_id' => $freshBooking->id,
                    'user_id' => $user->id,
                    'amount_due' => $amountDue,
                    'payment_status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return [$freshBooking, $amountDue];
            });

            $payment = app(PaymobService::class)->createPaymentSessionForParticipant($freshBooking, $user, $amountDue);
            $this->paymentIframeUrl = $payment['iframe_url'] ?? null;

            Notification::make()
                ->title('Complete payment')
                ->body('Use the payment window below to confirm your spot.')
                ->success()
                ->send();
        } catch (\RuntimeException $exception) {
            Notification::make()
                ->title(match ($exception->getMessage()) {
                    'already_joined' => 'Already joined',
                    'full' => 'Match is full',
                    default => 'Could not join',
                })
                ->danger()
                ->send();
        } catch (\Throwable $exception) {
            DB::table('booking_participants')
                ->where('booking_id', $booking->id)
                ->where('user_id', $user->id)
                ->where('payment_status', 'pending')
                ->delete();

            Notification::make()->title('Payment setup failed')->body($exception->getMessage())->danger()->send();
        }
    }

    public function closePayment(): void
    {
        $this->paymentIframeUrl = null;
    }
}
