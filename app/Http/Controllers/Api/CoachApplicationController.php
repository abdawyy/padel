<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademySession;
use App\Models\CoachApplication;
use App\Notifications\CoachApplicationSubmittedNotification;
use App\Services\CoachApplicationResponseService;
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

        $this->authorize('apply', $academySession);

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

        $application->load('coach');
        $academySession->load('club');

        $academySession->club?->users()
            ->wherePivotIn('role', ['owner', 'manager'])
            ->get()
            ->each(fn ($manager) => $manager->notify(
                new CoachApplicationSubmittedNotification($application, $academySession)
            ));

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

        $this->authorize('viewAny', $academySession);

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
        $this->authorize('respond', $coachApplication);

        $user = $request->user();

        $validated = $request->validate([
            'status'        => ['required', 'in:accepted,declined'],
            'response_note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $result = app(CoachApplicationResponseService::class)->respond(
                $coachApplication,
                $user,
                $validated['status'],
                $validated['response_note'] ?? null,
            );
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

        $this->authorize('withdraw', $coachApplication);

        $coachApplication->delete();

        return response()->json(['message' => 'Application withdrawn.'], 200);
    }
}
