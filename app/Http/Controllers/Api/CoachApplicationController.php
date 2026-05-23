<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademySession;
use App\Models\CoachApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoachApplicationController extends Controller
{
    /**
     * Coach applies to a session.
     * POST /api/academy-sessions/{session}/coach-apply
     */
    public function apply(Request $request, AcademySession $academySession): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'coach') {
            return response()->json(['message' => 'Only coaches can apply to sessions.'], 403);
        }

        $academySession->loadMissing('club');

        if (! $academySession->club || ! $user->belongsToClub($academySession->club)) {
            return response()->json([
                'message' => 'You must be a member of this club to apply as coach.',
            ], 403);
        }

        if (! in_array($academySession->status, ['scheduled', 'active'], true)) {
            return response()->json(['message' => 'This session is not accepting coach applications.'], 422);
        }

        if ($academySession->coach_user_id !== null) {
            return response()->json(['message' => 'This session already has an assigned coach.'], 409);
        }

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $existing = CoachApplication::query()
            ->where('academy_session_id', $academySession->id)
            ->where('coach_user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You already applied to this session.',
                'status'  => $existing->status,
            ], 409);
        }

        $application = CoachApplication::query()->create([
            'academy_session_id' => $academySession->id,
            'coach_user_id'      => $user->id,
            'status'             => 'pending',
            'message'            => $validated['message'] ?? null,
        ]);

        return response()->json([
            'message' => 'Application submitted successfully.',
            'data'    => [
                'id'         => $application->id,
                'session_id' => $academySession->id,
                'status'     => $application->status,
            ],
        ], 201);
    }

    /**
     * List applications for a session (club manager/admin).
     * GET /api/academy-sessions/{session}/coach-applications
     */
    public function index(Request $request, AcademySession $academySession): JsonResponse
    {
        $user = $request->user();

        abort_unless(
            $user?->hasAdminAccess($academySession->club) || $user?->isSuperAdmin(),
            403
        );

        $applications = $academySession->coachApplications()
            ->with('coach:id,name,email,skill_level')
            ->latest()
            ->get()
            ->map(fn (CoachApplication $app) => [
                'id'            => $app->id,
                'status'        => $app->status,
                'message'       => $app->message,
                'response_note' => $app->response_note,
                'responded_at'  => $app->responded_at,
                'coach'         => [
                    'id'          => $app->coach->id,
                    'name'        => $app->coach->name,
                    'email'       => $app->coach->email,
                    'skill_level' => $app->coach->skill_level,
                ],
            ]);

        return response()->json(['data' => $applications]);
    }

    /**
     * Accept or decline a coach application.
     * PATCH /api/coach-applications/{application}
     */
    public function respond(Request $request, CoachApplication $coachApplication): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'status'        => ['required', 'in:accepted,declined'],
            'response_note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $result = DB::transaction(function () use ($coachApplication, $validated, $user) {
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

                abort_unless(
                    $user?->hasAdminAccess($session->club) || $user?->isSuperAdmin(),
                    403
                );

                if (! $application->isPending()) {
                    throw new \RuntimeException('already_responded');
                }

                if ($validated['status'] === 'accepted') {
                    if ($session->coach_user_id !== null) {
                        throw new \RuntimeException('coach_already_assigned');
                    }

                    if (! $application->coach->belongsToClub($session->club)) {
                        throw new \RuntimeException('coach_not_in_club');
                    }

                    $session->update(['coach_user_id' => $application->coach_user_id]);

                    CoachApplication::query()
                        ->where('academy_session_id', $session->id)
                        ->where('id', '!=', $application->id)
                        ->where('status', 'pending')
                        ->update([
                            'status'        => 'declined',
                            'response_note' => 'Another coach was selected.',
                            'responded_at'  => now(),
                        ]);
                }

                $application->update([
                    'status'        => $validated['status'],
                    'response_note' => $validated['response_note'] ?? null,
                    'responded_at'  => now(),
                ]);

                return $application->fresh();
            });
        } catch (\RuntimeException $exception) {
            return match ($exception->getMessage()) {
                'already_responded' => response()->json(['message' => 'This application has already been responded to.'], 409),
                'coach_already_assigned' => response()->json(['message' => 'This session already has an assigned coach.'], 409),
                'coach_not_in_club' => response()->json(['message' => 'The selected coach is not a member of this club.'], 422),
                default => throw $exception,
            };
        }

        return response()->json([
            'message' => "Application {$validated['status']} successfully.",
            'data'    => [
                'id'            => $result->id,
                'status'        => $result->status,
                'response_note' => $result->response_note,
            ],
        ]);
    }

    /**
     * Coach withdraws their own application.
     * DELETE /api/coach-applications/{application}
     */
    public function withdraw(Request $request, CoachApplication $coachApplication): JsonResponse
    {
        $user = $request->user();

        if ($coachApplication->coach_user_id !== $user->id) {
            return response()->json(['message' => 'You can only withdraw your own applications.'], 403);
        }

        if (! $coachApplication->isPending()) {
            return response()->json(['message' => 'Only pending applications can be withdrawn.'], 409);
        }

        $coachApplication->delete();

        return response()->json(['message' => 'Application withdrawn.'], 200);
    }
}
