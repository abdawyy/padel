<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AcademySessionResource;
use App\Models\AcademySession;
use App\Models\Booking;
use App\Models\Club;
use App\Models\Court;
use App\Models\User;
use App\Exceptions\BookingCancellationException;
use App\Services\AcademySessionCancellationService;
use App\Services\EnrollmentPaymentService;
use App\Services\PackageConsumptionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademySessionController extends Controller
{
    public function publicIndex(Request $request)
    {
        $sessions = AcademySession::query()
            ->whereIn('status', ['scheduled', 'active'])
            ->where('start_time', '>=', now())
            ->whereHas('club', function ($query) {
                $query->where('registration_status', 'approved')
                    ->whereIn('subscription_status', ['active', 'trial']);
            })
            ->when($request->filled('club_id'), function ($query) use ($request) {
                $query->where('club_id', (int) $request->query('club_id'));
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->whereDate('start_time', (string) $request->query('date'));
            })
            ->when($request->filled('session_type'), function ($query) use ($request) {
                $query->where('session_type', (string) $request->query('session_type'));
            })
            ->withCount('players')
            ->with([
                'club:id,name,sport_type',
                'court:id,club_id,name,sport_type,price_per_hour',
                'coach:id,name,email',
            ])
            ->orderBy('start_time')
            ->paginate();

        return AcademySessionResource::collection($sessions);
    }

    public function mySessions(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'type' => ['sometimes', 'in:upcoming,past'],
        ]);

        $type = $validated['type'] ?? 'upcoming';

        $sessions = AcademySession::query()
            ->whereHas('players', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->when($type === 'past', function ($query) {
                $query->where('start_time', '<', now())
                    ->orderByDesc('start_time');
            }, function ($query) {
                $query->where('start_time', '>=', now())
                    ->orderBy('start_time');
            })
            ->withCount('players')
            ->with([
                'club:id,name,sport_type',
                'court:id,club_id,name,sport_type,price_per_hour',
                'coach:id,name,email',
                'players:id,name,email',
            ])
            ->paginate();

        return AcademySessionResource::collection($sessions);
    }

    public function index(Request $request, Club $club)
    {
        $this->authorize('viewAny', [$club]);

        $sessions = AcademySession::query()
            ->where('club_id', $club->id)
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->whereDate('start_time', (string) $request->query('date'));
            })
            ->when($request->filled('session_type'), function ($query) use ($request) {
                $query->where('session_type', (string) $request->query('session_type'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', (string) $request->query('status'));
            })
            ->when($request->filled('coach_user_id'), function ($query) use ($request) {
                $query->where('coach_user_id', (int) $request->query('coach_user_id'));
            })
            ->withCount('players')
            ->with([
                'club:id,name,sport_type',
                'court:id,club_id,name,sport_type,price_per_hour',
                'coach:id,name,email',
                'players:id,name,email',
            ])
            ->orderBy('start_time')
            ->paginate();

        return AcademySessionResource::collection($sessions);
    }

    public function store(Request $request, Club $club): AcademySessionResource|JsonResponse
    {
        $this->authorize('create', [$club]);

        $validated = $request->validate([
            'court_id' => ['required', 'integer', 'exists:courts,id'],
            'coach_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'sport_type' => ['nullable', 'string', 'max:100'],
            'session_type' => ['required', 'in:open_match,coached_match,group_training,private_training,academy_class'],
            'skill_level' => ['nullable', 'string', 'max:100'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'max_players' => ['nullable', 'integer', 'min:1', 'max:32'],
            'price_per_player' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:scheduled,active,completed,cancelled'],
            'notes' => ['nullable', 'string'],
            'player_ids' => ['nullable', 'array', 'max:32'],
            'player_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);

        $court = Court::query()
            ->where('club_id', $club->id)
            ->findOrFail($validated['court_id']);

        if (! empty($validated['coach_user_id']) && ! $club->users()->where('users.id', $validated['coach_user_id'])->exists()) {
            return response()->json(['message' => 'The selected coach must belong to the club.'], 422);
        }

        $startTime = Carbon::parse($validated['start_time']);
        $endTime = Carbon::parse($validated['end_time']);

        if ($this->hasCourtConflict($court->id, $startTime, $endTime)) {
            return response()->json(['message' => 'This court already has a booking or training session in the selected time range.'], 422);
        }

        $playerIds = collect($validated['player_ids'] ?? [])->unique();
        if ($playerIds->count() > ($validated['max_players'] ?? $court->capacity ?? 4)) {
            return response()->json(['message' => 'The selected players exceed the session capacity.'], 422);
        }

        foreach ($playerIds as $playerId) {
            if ($this->playerHasConflict((int) $playerId, $startTime, $endTime)) {
                return response()->json(['message' => 'One of the selected players has a conflicting booking or training session.'], 422);
            }
        }

        $session = DB::transaction(function () use ($validated, $club, $court, $request, $playerIds, $startTime, $endTime) {
            $session = AcademySession::query()->create([
                'club_id' => $club->id,
                'court_id' => $court->id,
                'coach_user_id' => $validated['coach_user_id'] ?? null,
                'created_by_user_id' => $request->user()->id,
                'title' => $validated['title'],
                'sport_type' => $validated['sport_type'] ?? $court->sport_type ?? $club->sport_type ?? 'padel',
                'session_type' => $validated['session_type'],
                'skill_level' => $validated['skill_level'] ?? null,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'max_players' => $validated['max_players'] ?? $court->capacity ?? 4,
                'price_per_player' => $validated['price_per_player'] ?? 0,
                'status' => $validated['status'] ?? 'scheduled',
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($playerIds->isNotEmpty()) {
                $session->players()->attach(
                    $playerIds->mapWithKeys(fn (int $playerId) => [$playerId => ['status' => 'assigned', 'notes' => null]])->all()
                );
            }

            return $session;
        });

        return new AcademySessionResource($session->load(['court:id,club_id,name,sport_type,price_per_hour', 'coach:id,name,email', 'players:id,name,email'])->loadCount('players'));
    }

    public function show(Request $request, AcademySession $academySession): AcademySessionResource
    {
        $academySession->load([
            'club:id,name,sport_type',
            'court:id,club_id,name,sport_type,price_per_hour',
            'coach:id,name,email',
        ])->loadCount('players');

        $user = $request->user();

        if (
            $user?->isSuperAdmin()
            || $user?->hasAdminAccess($academySession->club)
            || $user?->academySessions()->where('academy_sessions.id', $academySession->id)->exists()
        ) {
            $academySession->load('players:id,name,email');
        }

        return new AcademySessionResource($academySession);
    }

    public function update(Request $request, AcademySession $academySession): AcademySessionResource|JsonResponse
    {
        $academySession->loadMissing('club');
        $this->authorize('update', $academySession);

        if ($academySession->status === 'cancelled') {
            return response()->json(['message' => 'Cancelled sessions cannot be updated.'], 422);
        }

        $validated = $request->validate([
            'court_id' => ['sometimes', 'integer', 'exists:courts,id'],
            'coach_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'sport_type' => ['sometimes', 'string', 'max:100'],
            'session_type' => ['sometimes', 'in:open_match,coached_match,group_training,private_training,academy_class'],
            'skill_level' => ['nullable', 'string', 'max:100'],
            'start_time' => ['sometimes', 'date'],
            'end_time' => ['sometimes', 'date', 'after:start_time'],
            'max_players' => ['sometimes', 'integer', 'min:1', 'max:32'],
            'price_per_player' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:scheduled,active,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        $club = $academySession->club;

        if (! empty($validated['coach_user_id']) && ! $club->users()->where('users.id', $validated['coach_user_id'])->exists()) {
            return response()->json(['message' => 'The selected coach must belong to the club.'], 422);
        }

        if (isset($validated['court_id'])) {
            $court = Court::query()->where('club_id', $club->id)->findOrFail($validated['court_id']);
            $validated['court_id'] = $court->id;
        }

        $startTime = isset($validated['start_time'])
            ? Carbon::parse($validated['start_time'])
            : Carbon::parse($academySession->start_time);
        $endTime = isset($validated['end_time'])
            ? Carbon::parse($validated['end_time'])
            : Carbon::parse($academySession->end_time);

        $courtId = (int) ($validated['court_id'] ?? $academySession->court_id);

        if ($this->hasCourtConflictExcluding($courtId, $startTime, $endTime, $academySession->id)) {
            return response()->json(['message' => 'This court already has a booking or training session in the selected time range.'], 422);
        }

        $academySession->update($validated);

        return new AcademySessionResource(
            $academySession->fresh()->load([
                'court:id,club_id,name,sport_type,price_per_hour',
                'coach:id,name,email',
                'players:id,name,email',
            ])->loadCount('players')
        );
    }

    public function cancel(Request $request, AcademySession $academySession, AcademySessionCancellationService $cancellationService): JsonResponse
    {
        try {
            $session = $cancellationService->cancel(
                $academySession,
                $request->user(),
                $request->input('reason'),
            );
        } catch (BookingCancellationException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->status);
        }

        return response()->json([
            'message' => 'Session cancelled successfully.',
            'session' => new AcademySessionResource($session->loadCount('players')),
        ]);
    }

    public function enroll(Request $request, AcademySession $academySession): JsonResponse
    {
        $validated = $request->validate([
            'player_id' => ['nullable', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $playerId = (int) ($validated['player_id'] ?? $user->id);
        $isSelfEnrollment = $playerId === $user->id;

        if (! $isSelfEnrollment) {
            $this->authorize('enrollOthers', $academySession);
        }

        if (! in_array($academySession->status, ['scheduled', 'active'], true)) {
            return response()->json(['message' => 'This session is not open for registration.'], 422);
        }

        if (Carbon::parse($academySession->start_time)->lte(now())) {
            return response()->json(['message' => 'This session has already started.'], 422);
        }

        if ($this->playerHasConflict($playerId, Carbon::parse($academySession->start_time), Carbon::parse($academySession->end_time))) {
            return response()->json(['message' => 'This player already has a conflicting booking or training session.'], 422);
        }

        $fee = (float) ($academySession->price_per_player ?? 0);
        if ($fee > 0) {
            $capacityError = $this->sessionEnrollmentCapacityError($academySession, $playerId);
            if ($capacityError !== null) {
                return $capacityError;
            }

            $payer = $isSelfEnrollment
                ? $user
                : User::query()->findOrFail($playerId);

            try {
                $paymentData = app(EnrollmentPaymentService::class)->createEnrollmentPayment(
                    $academySession,
                    $payer,
                    $fee,
                    $request->header('X-Idempotency-Key'),
                );
            } catch (\RuntimeException $exception) {
                return match ($exception->getMessage()) {
                    'session_closed' => response()->json(['message' => 'This session is not open for registration.'], 422),
                    default => throw $exception,
                };
            }

            return response()->json([
                'message'    => $isSelfEnrollment
                    ? 'Payment required to complete enrollment.'
                    : 'Payment required for the assigned player to complete enrollment.',
                'payment'    => $paymentData,
                'session_id' => $academySession->id,
                'amount_due' => $fee,
                'player_id'  => $playerId,
            ], 402);
        }

        try {
            $this->attachPlayerToSession($academySession, $playerId, $validated['notes'] ?? null);
        } catch (\RuntimeException $exception) {
            return match ($exception->getMessage()) {
                'already_registered' => response()->json(['message' => 'This player is already assigned to the session.'], 409),
                'session_full' => response()->json(['message' => 'This session is already full.'], 422),
                default => throw $exception,
            };
        }

        return response()->json([
            'message' => 'Player assigned successfully.',
            'session' => new AcademySessionResource(
                $academySession->fresh()->load([
                    'court:id,club_id,name,sport_type,price_per_hour',
                    'coach:id,name,email',
                    'players:id,name,email',
                ])->loadCount('players')
            ),
        ]);
    }

    private function sessionEnrollmentCapacityError(AcademySession $academySession, int $playerId): ?JsonResponse
    {
        try {
            $this->assertSessionHasCapacity($academySession, $playerId);

            return null;
        } catch (\RuntimeException $exception) {
            return match ($exception->getMessage()) {
                'already_registered' => response()->json(['message' => 'This player is already assigned to the session.'], 409),
                'session_full' => response()->json(['message' => 'This session is already full.'], 422),
                default => throw $exception,
            };
        }
    }

    private function assertSessionHasCapacity(AcademySession $academySession, int $playerId): void
    {
        DB::transaction(function () use ($academySession, $playerId) {
            $session = AcademySession::query()
                ->whereKey($academySession->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($session->players()->where('users.id', $playerId)->exists()) {
                throw new \RuntimeException('already_registered');
            }

            if ($session->players()->count() >= (int) $session->max_players) {
                throw new \RuntimeException('session_full');
            }
        });
    }

    private function attachPlayerToSession(AcademySession $academySession, int $playerId, ?string $notes): void
    {
        DB::transaction(function () use ($academySession, $playerId, $notes) {
            $session = AcademySession::query()
                ->whereKey($academySession->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($session->players()->where('users.id', $playerId)->exists()) {
                throw new \RuntimeException('already_registered');
            }

            if ($session->players()->count() >= (int) $session->max_players) {
                throw new \RuntimeException('session_full');
            }

            $session->players()->attach($playerId, [
                'status' => 'registered',
                'notes' => $notes,
            ]);

            $player = User::query()->find($playerId);
            if ($player && $session->club_id) {
                app(PackageConsumptionService::class)->consumeSessionForUser($player, (int) $session->club_id);
            }
        });
    }

    private function hasCourtConflictExcluding(int $courtId, Carbon $startTime, Carbon $endTime, int $excludeSessionId): bool
    {
        $bookingConflict = Booking::query()
            ->where('court_id', $courtId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        if ($bookingConflict) {
            return true;
        }

        return AcademySession::query()
            ->where('court_id', $courtId)
            ->where('id', '!=', $excludeSessionId)
            ->whereIn('status', ['scheduled', 'active'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }

    private function hasCourtConflict(int $courtId, Carbon $startTime, Carbon $endTime): bool
    {
        $bookingConflict = Booking::query()
            ->where('court_id', $courtId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        $academyConflict = AcademySession::query()
            ->where('court_id', $courtId)
            ->whereIn('status', ['scheduled', 'active'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        return $bookingConflict || $academyConflict;
    }

    private function playerHasConflict(int $playerId, Carbon $startTime, Carbon $endTime): bool
    {
        $bookingConflict = Booking::query()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->where(function ($query) use ($playerId) {
                $query->where('owner_user_id', $playerId)
                    ->orWhereHas('participants', function ($participantQuery) use ($playerId) {
                        $participantQuery->where('users.id', $playerId);
                    });
            })
            ->exists();

        $academyConflict = AcademySession::query()
            ->whereIn('status', ['scheduled', 'active'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->whereHas('players', function ($query) use ($playerId) {
                $query->where('users.id', $playerId);
            })
            ->exists();

        return $bookingConflict || $academyConflict;
    }

}
