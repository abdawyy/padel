<?php

namespace App\Services;

use App\Exceptions\BookingCancellationException;
use App\Models\AcademySession;
use App\Models\User;
use App\Notifications\AcademySessionCancelledNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AcademySessionCancellationService
{
    public function cancel(AcademySession $session, User $actor, ?string $reason = null): AcademySession
    {
        $this->authorize($session, $actor);

        if ($session->status === 'cancelled') {
            throw new BookingCancellationException('This session is already cancelled.');
        }

        if (! in_array($session->status, ['scheduled', 'active'], true)) {
            throw new BookingCancellationException('This session cannot be cancelled.');
        }

        $session->update([
            'status' => 'cancelled',
            'notes' => trim(($session->notes ? $session->notes."\n" : '').($reason ?? 'Cancelled.')),
        ]);

        $session = $session->fresh(['club', 'court', 'coach', 'players']);

        $session->players->each(
            fn (User $player) => $player->notify(new AcademySessionCancelledNotification($session, $reason))
        );

        return $session;
    }

    private function authorize(AcademySession $session, User $actor): void
    {
        $session->loadMissing('club');

        if ($actor->isSuperAdmin() || $actor->hasAdminAccess($session->club)) {
            return;
        }

        throw new BookingCancellationException('You are not allowed to cancel this session.', 403);
    }
}
