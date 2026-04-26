<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AcademySessionResource;
use App\Http\Resources\CourtSlotResource;
use App\Models\AcademySession;
use App\Models\Booking;
use App\Models\Club;
use App\Models\Court;
use App\Models\CourtSlot;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourtSlotController extends Controller
{
    public function index(Request $request, Club $club)
    {
        $this->abortIfUnauthorized($request, $club);

        $dayOfWeek = $request->filled('date')
            ? Carbon::parse((string) $request->query('date'))->dayOfWeek
            : $request->query('day_of_week');

        $slots = CourtSlot::query()
            ->whereHas('court', function ($query) use ($club) {
                $query->where('club_id', $club->id);
            })
            ->when($dayOfWeek !== null && $dayOfWeek !== '', function ($query) use ($dayOfWeek) {
                $query->where('day_of_week', (int) $dayOfWeek);
            })
            ->when($request->filled('court_id'), function ($query) use ($request) {
                $query->where('court_id', (int) $request->query('court_id'));
            })
            ->when($request->boolean('active_only', true), function ($query) {
                $query->where('is_active', true);
            })
            ->with(['court:id,club_id,name,sport_type', 'coach:id,name,email'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate();

        return CourtSlotResource::collection($slots);
    }

    public function store(Request $request, Club $club): CourtSlotResource|JsonResponse
    {
        $this->abortIfUnauthorized($request, $club);

        $validated = $request->validate([
            'court_id' => ['required', 'integer', 'exists:courts,id'],
            'title' => ['required', 'string', 'max:255'],
            'sport_type' => ['nullable', 'string', 'max:100'],
            'slot_type' => ['required', 'in:open_match,coached_match,training,academy_class,private_training'],
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'coach_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'max_players' => ['nullable', 'integer', 'min:1', 'max:32'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'skill_level' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $court = Court::query()
            ->where('club_id', $club->id)
            ->findOrFail($validated['court_id']);

        if (! empty($validated['coach_user_id']) && ! $club->users()->where('users.id', $validated['coach_user_id'])->exists()) {
            return response()->json(['message' => 'The selected coach must belong to the club.'], 422);
        }

        $slot = CourtSlot::query()->create([
            'court_id' => $court->id,
            'title' => $validated['title'],
            'sport_type' => $validated['sport_type'] ?? $court->sport_type ?? $club->sport_type ?? 'padel',
            'slot_type' => $validated['slot_type'],
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'coach_user_id' => $validated['coach_user_id'] ?? null,
            'max_players' => $validated['max_players'] ?? $court->capacity ?? 4,
            'price' => $validated['price'] ?? 0,
            'skill_level' => $validated['skill_level'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return new CourtSlotResource($slot->load(['court:id,club_id,name,sport_type', 'coach:id,name,email']));
    }

    public function update(Request $request, CourtSlot $courtSlot): CourtSlotResource|JsonResponse
    {
        $club = $courtSlot->court()->with('club')->firstOrFail()->club;
        $this->abortIfUnauthorized($request, $club);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'sport_type' => ['sometimes', 'string', 'max:100'],
            'slot_type' => ['sometimes', 'in:open_match,coached_match,training,academy_class,private_training'],
            'day_of_week' => ['sometimes', 'integer', 'between:0,6'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i', 'after:start_time'],
            'coach_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'max_players' => ['sometimes', 'integer', 'min:1', 'max:32'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'skill_level' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (! empty($validated['coach_user_id']) && ! $club->users()->where('users.id', $validated['coach_user_id'])->exists()) {
            return response()->json(['message' => 'The selected coach must belong to the club.'], 422);
        }

        $courtSlot->update($validated);

        return new CourtSlotResource($courtSlot->fresh()->load(['court:id,club_id,name,sport_type', 'coach:id,name,email']));
    }

    public function destroy(Request $request, CourtSlot $courtSlot): JsonResponse
    {
        $club = $courtSlot->court()->with('club')->firstOrFail()->club;
        $this->abortIfUnauthorized($request, $club);

        $courtSlot->delete();

        return response()->json([], 204);
    }

    public function schedule(Request $request, Club $club, CourtSlot $courtSlot): AcademySessionResource|JsonResponse
    {
        $this->abortIfUnauthorized($request, $club);

        if ($courtSlot->court?->club_id !== $club->id) {
            return response()->json(['message' => 'The selected slot does not belong to this club.'], 422);
        }

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'title' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:scheduled,active,completed,cancelled'],
            'notes' => ['nullable', 'string'],
            'price_per_player' => ['nullable', 'numeric', 'min:0'],
            'player_ids' => ['nullable', 'array', 'max:32'],
            'player_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);

        $date = Carbon::parse($validated['date']);
        if ($date->dayOfWeek !== (int) $courtSlot->day_of_week) {
            return response()->json(['message' => 'The selected date does not match the slot day of week.'], 422);
        }

        $startTime = Carbon::parse($date->toDateString().' '.$courtSlot->start_time);
        $endTime = Carbon::parse($date->toDateString().' '.$courtSlot->end_time);

        if ($endTime->lte($startTime)) {
            $endTime->addDay();
        }

        if ($this->hasCourtConflict($courtSlot->court_id, $startTime, $endTime)) {
            return response()->json(['message' => 'This court already has a booking or training session in the selected slot.'], 422);
        }

        $session = AcademySession::query()->create([
            'club_id' => $club->id,
            'court_id' => $courtSlot->court_id,
            'coach_user_id' => $courtSlot->coach_user_id,
            'created_by_user_id' => $request->user()->id,
            'title' => $validated['title'] ?? $courtSlot->title,
            'sport_type' => $courtSlot->sport_type,
            'session_type' => $courtSlot->slot_type,
            'skill_level' => $courtSlot->skill_level,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'max_players' => $courtSlot->max_players,
            'price_per_player' => $validated['price_per_player'] ?? $courtSlot->price,
            'status' => $validated['status'] ?? 'scheduled',
            'notes' => $validated['notes'] ?? null,
        ]);

        $playerIds = collect($validated['player_ids'] ?? [])->unique()->take($courtSlot->max_players);
        if ($playerIds->isNotEmpty()) {
            $session->players()->attach(
                $playerIds->mapWithKeys(fn (int $playerId) => [$playerId => ['status' => 'assigned', 'notes' => null]])->all()
            );
        }

        return new AcademySessionResource($session->load(['court:id,club_id,name,sport_type,price_per_hour', 'coach:id,name,email', 'players:id,name,email'])->loadCount('players'));
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

    private function abortIfUnauthorized(Request $request, Club $club): void
    {
        abort_if(
            ! $request->user()?->hasAdminAccess($club),
            403,
            'Unauthorized club access.'
        );
    }
}
