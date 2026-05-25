<?php

namespace App\Services;

use App\Models\AcademySession;
use App\Models\CoachApplication;
use App\Models\User;
use App\Notifications\CoachApplicationRespondedNotification;
use Illuminate\Support\Facades\DB;

class CoachApplicationResponseService
{
    /**
     * @throws \RuntimeException
     */
    public function respond(CoachApplication $coachApplication, User $responder, string $status, ?string $responseNote = null): CoachApplication
    {
        if (! in_array($status, ['accepted', 'declined'], true)) {
            throw new \InvalidArgumentException('Invalid status.');
        }

        return DB::transaction(function () use ($coachApplication, $responder, $status, $responseNote) {
            $application = CoachApplication::query()
                ->with('coach')
                ->whereKey($coachApplication->id)
                ->lockForUpdate()
                ->firstOrFail();

            $session = AcademySession::query()
                ->whereKey($application->academy_session_id)
                ->lockForUpdate()
                ->with('club')
                ->firstOrFail();

            \Illuminate\Support\Facades\Gate::forUser($responder)->authorize('respond', $application);

            if (! $application->isPending()) {
                throw new \RuntimeException('already_responded');
            }

            if ($status === 'accepted') {
                if ($session->coach_user_id !== null) {
                    throw new \RuntimeException('coach_already_assigned');
                }

                if (! $application->coach?->belongsToClub($session->club)) {
                    throw new \RuntimeException('coach_not_in_club');
                }

                $session->update(['coach_user_id' => $application->coach_user_id]);

                CoachApplication::query()
                    ->where('academy_session_id', $session->id)
                    ->where('id', '!=', $application->id)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'declined',
                        'response_note' => 'Another coach was selected.',
                        'responded_at' => now(),
                    ]);
            }

            $application->update([
                'status' => $status,
                'response_note' => $responseNote,
                'responded_at' => now(),
            ]);

            $fresh = $application->fresh(['coach']);
            $session->load('club');
            $fresh->coach?->notify(new CoachApplicationRespondedNotification($fresh, $session));

            return $fresh;
        });
    }
}
