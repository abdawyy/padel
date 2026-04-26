<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\UserBookingResource;
use App\Http\Controllers\Controller;
use App\Models\AcademySession;
use App\Models\Booking;
use App\Models\Court;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function userBookings(Request $request)
    {
        $user = $request->user();
        $type = (string) $request->query('type', 'upcoming');

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

        if (! empty($validated['coach_user_id']) && ! $court->club?->users()->where('users.id', $validated['coach_user_id'])->exists()) {
            return response()->json([
                'message' => 'The selected coach must belong to the same club as the court.',
            ], 422);
        }

        $startTime = Carbon::parse($validated['start_time']);
        $endTime = Carbon::parse($validated['end_time']);

        $hasOverlap = Booking::query()
            ->where('court_id', $court->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        $hasAcademyOverlap = AcademySession::query()
            ->where('court_id', $court->id)
            ->whereIn('status', ['scheduled', 'active'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        if ($hasOverlap || $hasAcademyOverlap) {
            return response()->json([
                'message' => 'This court is not available for the selected time range.',
            ], 422);
        }

        $durationMinutes = max($startTime->diffInMinutes($endTime), 1);
        $durationHours = $durationMinutes / 60;
        $coachFee = (float) ($validated['coach_fee'] ?? 0);
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

        $amountDue = round($totalPrice / max($allParticipantIds->count(), 1), 2);
        $sessionType = $validated['session_type'] ?? (! empty($validated['coach_user_id'])
            ? ($validated['match_type'] === 'open_match' ? 'coached_match' : 'private_training')
            : ($validated['match_type'] === 'open_match' ? 'open_match' : 'standard'));

        $booking = DB::transaction(function () use ($validated, $user, $court, $startTime, $endTime, $totalPrice, $coachFee, $allParticipantIds, $amountDue, $maxPlayers, $sessionType) {
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
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            $participantPayload = [];
            foreach ($allParticipantIds as $participantId) {
                $participantPayload[$participantId] = [
                    'amount_due' => $amountDue,
                    'payment_status' => 'pending',
                ];
            }

            $booking->participants()->attach($participantPayload);

            return $booking->load(['court', 'owner', 'coach', 'participants']);
        });

        return new BookingResource($booking);
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
            'coach_fee' => ['sometimes', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

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
