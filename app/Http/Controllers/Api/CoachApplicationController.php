<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademySession;
use App\Models\CoachApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $session = $coachApplication->session()->with('club')->first();

        abort_unless(
            $user?->hasAdminAccess($session->club) || $user?->isSuperAdmin(),
            403
        );

        $validated = $request->validate([
            'status'        => ['required', 'in:accepted,declined'],
            'response_note' => ['nullable', 'string', 'max:500'],
        ]);

        if (! $coachApplication->isPending()) {
            return response()->json(['message' => 'This application has already been responded to.'], 409);
        }

        $coachApplication->update([
            'status'        => $validated['status'],
            'response_note' => $validated['response_note'] ?? null,
            'responded_at'  => now(),
        ]);

        // If accepted, assign coach to the session and decline all others
        if ($validated['status'] === 'accepted') {
            $session->update(['coach_user_id' => $coachApplication->coach_user_id]);

            CoachApplication::query()
                ->where('academy_session_id', $session->id)
                ->where('id', '!=', $coachApplication->id)
                ->where('status', 'pending')
                ->update([
                    'status'        => 'declined',
                    'response_note' => 'Another coach was selected.',
                    'responded_at'  => now(),
                ]);
        }

        return response()->json([
            'message' => "Application {$validated['status']} successfully.",
            'data'    => [
                'id'            => $coachApplication->id,
                'status'        => $coachApplication->status,
                'response_note' => $coachApplication->response_note,
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
