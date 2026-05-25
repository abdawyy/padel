<?php

namespace App\Filament\Player\Pages;

use App\Models\AcademySession;
use App\Models\Club;
use App\Services\PackageConsumptionService;
use App\Services\PaymobService;
use Illuminate\Support\Facades\DB;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BrowseAcademy extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'Academy Sessions';

    protected static ?string $title = 'Browse Academy';

    protected static ?int $navigationSort = 4;

    public ?string $clubId = null;

    public ?string $sessionDate = null;

    public ?string $paymentIframeUrl = null;

    public function getView(): string
    {
        return 'filament.player.pages.browse-academy';
    }

    public function getClubs(): Collection
    {
        return Club::query()
            ->where('registration_status', 'approved')
            ->whereIn('subscription_status', ['active', 'trial'])
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getSessions(): Collection
    {
        return AcademySession::query()
            ->whereIn('status', ['scheduled', 'active'])
            ->where('start_time', '>=', now())
            ->whereHas('club', fn (Builder $q) => $q
                ->where('registration_status', 'approved')
                ->whereIn('subscription_status', ['active', 'trial']))
            ->when($this->clubId, fn (Builder $q) => $q->where('club_id', (int) $this->clubId))
            ->when($this->sessionDate, fn (Builder $q) => $q->whereDate('start_time', $this->sessionDate))
            ->withCount('players')
            ->with(['club:id,name', 'court:id,name', 'coach:id,name'])
            ->orderBy('start_time')
            ->limit(50)
            ->get();
    }

    public function enroll(int $sessionId): void
    {
        $user = auth()->user();
        $session = AcademySession::query()->with('club')->findOrFail($sessionId);

        if ($session->players()->where('users.id', $user->id)->exists()) {
            Notification::make()->title('Already enrolled')->warning()->send();

            return;
        }

        if ($session->players_count >= $session->max_players) {
            Notification::make()->title('Session is full')->danger()->send();

            return;
        }

        $fee = (float) $session->price_per_player;

        if ($fee <= 0) {
            try {
                DB::transaction(function () use ($session, $user) {
                    if ($session->players()->where('users.id', $user->id)->exists()) {
                        throw new \RuntimeException('already_registered');
                    }
                    if ($session->players()->count() >= (int) $session->max_players) {
                        throw new \RuntimeException('session_full');
                    }
                    $session->players()->attach($user->id, ['status' => 'registered', 'notes' => null]);
                    app(PackageConsumptionService::class)->consumeSessionForUser($user, (int) $session->club_id);
                });
                Notification::make()->title('Enrolled successfully')->success()->send();
            } catch (\RuntimeException $e) {
                Notification::make()->title(match ($e->getMessage()) {
                    'already_registered' => 'Already enrolled',
                    'session_full' => 'Session is full',
                    default => 'Enrollment failed',
                })->danger()->send();
            }

            return;
        }

        try {
            $payment = app(PaymobService::class)->createPaymentSessionForEnrollment($session, $user, $fee);
            $this->paymentIframeUrl = $payment['iframe_url'] ?? null;

            Notification::make()->title('Complete payment')->body('Use the payment window to confirm enrollment.')->success()->send();
        } catch (\Throwable $exception) {
            Notification::make()->title('Enrollment failed')->body($exception->getMessage())->danger()->send();
        }
    }

    public function closePayment(): void
    {
        $this->paymentIframeUrl = null;
    }
}
