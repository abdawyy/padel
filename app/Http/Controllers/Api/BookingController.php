<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\UserBookingResource;
use App\Http\Controllers\Controller;
use App\Models\AcademySession;
use App\Models\Booking;
use App\Models\Court;
use App\Services\BookableCourtValidator;
use App\Services\BookingPaymentSplit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function userBookings(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'type' => ['sometimes', 'in:upcoming,past'],
        ]);

        $type = $validated['type'] ?? 'upcoming';

        $bookings = Booking::query()
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->with([
                'court:id,club_id,name',
                'court.club:id,name',
                'participants' => function ($query) use ($user) {
                    $query->where('users.id', $user->id)->select('users.id', 'users.name');
                },
            ])
            ->when($type === 'past', function ($query) {
                $query->where('start_time', '<', now())
                    ->orderByDesc('start_time');
            }, function ($query) {
                $query->where('start_time', '>=', now())
                    ->orderBy('start_time');
            })
            ->paginate();

        return UserBookingResource::collection($bookings);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = request()->user();

        $bookings = Booking::query()
            ->with(['court', 'owner', 'coach', 'participants'])
            ->where(function ($query) use ($user) {
                $query->where('owner_user_id', $user->id)
                    ->orWhereHas('participants', function ($participantQuery) use ($user) {
                        $participantQuery->where('users.id', $user->id);
                    });
            })
            ->latest()
            ->paginate();

        return BookingResource::collection($bookings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookingRequest $request): BookingResource|JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $court = Court::query()->with('club.users')->findOrFail($validated['court_id']);

        if ($unavailable = BookableCourtValidator::bookingUnavailableResponse($court)) {
            return $unavailable;
        }

        if (! empty($validated['coach_user_id']) && ! $court->club?->users()->where('users.id', $validated['coach_user_id'])->exists()) {
            return response()->json([
                'message' => 'The selected coach must belong to the same club as the court.',
            ], 422);
        }

        $startTime = Carbon::parse($validated['start_time']);
        $endTime = Carbon::parse($validated['end_time']);

        $durationMinutes = max($startTime->diffInMinutes($endTime), 1);
        $durationHours = $durationMinutes / 60;
        $coachFee = $this->resolveCoachFee($court, $validated['coach_user_id'] ?? null, $durationHours);
        $totalPrice = round((((float) $court->price_per_hour) * $durationHours) + $coachFee, 2);

        $participantIds = $validated['participant_ids'] ?? [];
        $allParticipantIds = collect([$user->id])
            ->merge($participantIds)
            ->unique()
            ->values();

        $maxPlayers = (int) ($validated['max_players'] ?? ($validated['match_type'] === 'open_match' ? 4 : max($allParticipantIds->count(), 1)));

        if ($allParticipantIds->count() > $maxPlayers) {
            return response()->json([
                'message' => 'The selected participants exceed the maximum allowed players for this booking.',
            ], 422);
        }

        $participantCount = max($allParticipantIds->count(), 1);
        $splitAmounts = BookingPaymentSplit::split($totalPrice, $participantCount);
        $sessionType = $validated['session_type'] ?? (! empty($validated['coach_user_id'])
            ? ($validated['match_type'] === 'open_match' ? 'coached_match' : 'private_training')
            : ($validated['match_type'] === 'open_match' ? 'open_match' : 'standard'));

        [$skillMin, $skillMax] = $this->resolveSkillRange($validated);

        foreach ($allParticipantIds as $participantId) {
            if ($this->playerHasSchedulingConflict((int) $participantId, $startTime, $endTime)) {
                return response()->json([
                    'message' => 'One or more participants have a conflicting booking or training session.',
                ], 422);
            }
        }

        try {
            $booking = DB::transaction(function () use ($validated, $user, $court, $startTime, $endTime, $totalPrice, $coachFee, $allParticipantIds, $splitAmounts, $maxPlayers, $sessionType, $skillMin, $skillMax) {
                if ($this->courtHasSchedulingConflict($court->id, $startTime, $endTime)) {
                    throw new \RuntimeException('scheduling_conflict');
                }

                $booking = Booking::query()->create([
                    'court_id' => $court->id,
                    'sport_type' => $validated['sport_type'] ?? $court->sport_type ?? 'padel',
                    'owner_user_id' => $user->id,
                    'coach_user_id' => $validated['coach_user_id'] ?? null,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'total_price' => $totalPrice,
                    'coach_fee' => $coachFee,
                    'match_type' => $validated['match_type'],
                    'session_type' => $sessionType,
                    'max_players' => $maxPlayers,
                    'skill_level' => $validated['skill_level'] ?? null,
                    'skill_min' => $skillMin,
                    'skill_max' => $skillMax,
                    'status' => 'pending',
                    'notes' => $validated['notes'] ?? null,
                ]);

                $participantPayload = [];
                foreach ($allParticipantIds->values() as $index => $participantId) {
                    $participantPayload[$participantId] = [
                        'amount_due' => $splitAmounts[$index],
                        'payment_status' => 'pending',
                    ];
                }

                $booking->participants()->attach($participantPayload);

                return $booking->load(['court', 'owner', 'coach', 'participants']);
            });
        } catch (\RuntimeException $exception) {
            if ($exception->getMessage() === 'scheduling_conflict') {
                return response()->json([
                    'message' => 'This court is not available for the selected time range.',
                ], 422);
            }

            throw $exception;
        }

        return new BookingResource($booking);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{0: ?int, 1: ?int}
     */
    private function resolveCoachFee(Court $court, ?int $coachUserId, float $durationHours): float
    {
        if (empty($coachUserId)) {
            return 0.0;
        }

        $ratePerHour = (float) data_get($court->club?->settings, 'coach_fee_per_hour', 0);

        return round($ratePerHour * $durationHours, 2);
    }

    private function resolveSkillRange(array $validated): array
    {
        $skillMin = isset($validated['skill_min']) ? (int) $validated['skill_min'] : null;
        $skillMax = isset($validated['skill_max']) ? (int) $validated['skill_max'] : null;

        if ($skillMin === null && $skillMax === null && isset($validated['skill_level']) && is_numeric($validated['skill_level'])) {
            $level = (int) $validated['skill_level'];
            if ($level >= 1 && $level <= 7) {
                $skillMin = $skillMax = $level;
            }
        }

        return [$skillMin, $skillMax];
    }

    private function allParticipantsPaid(Booking $booking): bool
    {
        return ! DB::table('booking_participants')
            ->where('booking_id', $booking->id)
            ->where('payment_status', '!=', 'paid')
            ->exists();
    }

    private function playerHasSchedulingConflict(int $playerId, Carbon $startTime, Carbon $endTime): bool
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

        if ($bookingConflict) {
            return true;
        }

        return AcademySession::query()
            ->whereIn('status', ['scheduled', 'active'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->whereHas('players', function ($query) use ($playerId) {
                $query->where('users.id', $playerId);
            })
            ->exists();
    }

    private function courtHasSchedulingConflict(int $courtId, Carbon $startTime, Carbon $endTime): bool
    {
        $bookingOverlap = Booking::query()
            ->where('court_id', $courtId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->lockForUpdate()
            ->exists();

        if ($bookingOverlap) {
            return true;
        }

        return AcademySession::query()
            ->where('court_id', $courtId)
            ->whereIn('status', ['scheduled', 'active'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->lockForUpdate()
            ->exists();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): BookingResource|JsonResponse
    {
        $user = request()->user();

        $booking = Booking::query()
            ->with(['court', 'owner', 'coach', 'participants'])
            ->findOrFail($id);

        $isAllowed = $booking->owner_user_id === $user->id
            || $booking->participants->contains('id', $user->id);

        if (! $isAllowed) {
            return response()->json(['message' => 'Unauthorized booking access.'], 403);
        }

        return new BookingResource($booking);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();

        $booking = Booking::query()->findOrFail($id);
        if ($booking->owner_user_id !== $user->id) {
            return response()->json(['message' => 'Only the booking owner can update this booking.'], 403);
        }

        $validated = $request->validate([
            'status' => ['sometimes', 'in:pending,confirmed,cancelled'],
            'match_type' => ['sometimes', 'in:private,open_match'],
            'session_type' => ['sometimes', 'in:standard,open_match,coached_match,group_training,private_training,academy_class'],
            'coach_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'max_players' => ['sometimes', 'integer', 'min:1', 'max:32'],
            'skill_level' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        unset($validated['coach_fee']);

        if (array_key_exists('coach_user_id', $validated) && ! empty($validated['coach_user_id'])) {
            $booking->loadMissing('court.club');
            $club = $booking->court?->club;

            if (! $club || ! $club->users()->where('users.id', $validated['coach_user_id'])->exists()) {
                return response()->json([
                    'message' => 'The selected coach must belong to the same club as the court.',
                ], 422);
            }
        }

        if (($validated['status'] ?? null) === 'confirmed' && ! $this->allParticipantsPaid($booking)) {
            return response()->json([
                'message' => 'Cannot confirm booking until all participants have completed payment.',
            ], 422);
        }

        $booking->update($validated);

        return new BookingResource($booking->load(['court', 'owner', 'coach', 'participants']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = request()->user();

        $booking = Booking::query()->findOrFail($id);
        if ($booking->owner_user_id !== $user->id) {
            return response()->json(['message' => 'Only the booking owner can delete this booking.'], 403);
        }

        $booking->delete();

        return response()->noContent();
    }
}
