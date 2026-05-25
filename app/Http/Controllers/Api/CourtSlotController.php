<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AcademySessionResource;
use App\Http\Resources\CourtSlotResource;
use App\Models\Club;
use App\Models\Court;
use App\Models\CourtSlot;
use App\Services\CourtSlotSchedulingService;
use App\Support\CourtSlotTimeValidation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CourtSlotController extends Controller
{
    public function index(Request $request, Club $club)
    {
        $this->authorizeClub($club);

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
        $this->authorizeClub($club, 'create');

        $validator = Validator::make($request->all(), [
            'court_id' => ['required', 'integer', 'exists:courts,id'],
            'title' => ['required', 'string', 'max:255'],
            'sport_type' => ['nullable', 'string', 'max:100'],
            'slot_type' => ['required', 'in:open_match,coached_match,training,academy_class,private_training'],
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'coach_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'max_players' => ['nullable', 'integer', 'min:1', 'max:32'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'skill_level' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        CourtSlotTimeValidation::applyToValidator($validator);

        $validated = $validator->validate();

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
        $this->authorize('update', $courtSlot);

        $validator = Validator::make($request->all(), [
            'title' => ['sometimes', 'string', 'max:255'],
            'sport_type' => ['sometimes', 'string', 'max:100'],
            'slot_type' => ['sometimes', 'in:open_match,coached_match,training,academy_class,private_training'],
            'day_of_week' => ['sometimes', 'integer', 'between:0,6'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i'],
            'coach_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'max_players' => ['sometimes', 'integer', 'min:1', 'max:32'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'skill_level' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validator->after(function ($validator) use ($request, $courtSlot) {
            $start = (string) ($request->input('start_time') ?? $courtSlot->start_time);
            $end = (string) ($request->input('end_time') ?? $courtSlot->end_time);

            if ($start === $end) {
                $validator->errors()->add('end_time', 'End time must be different from start time.');
            }
        });

        $validated = $validator->validate();

        if (! empty($validated['coach_user_id']) && ! $club->users()->where('users.id', $validated['coach_user_id'])->exists()) {
            return response()->json(['message' => 'The selected coach must belong to the club.'], 422);
        }

        $courtSlot->update($validated);

        return new CourtSlotResource($courtSlot->fresh()->load(['court:id,club_id,name,sport_type', 'coach:id,name,email']));
    }

    public function destroy(Request $request, CourtSlot $courtSlot): JsonResponse
    {
        $this->authorize('delete', $courtSlot);

        $courtSlot->delete();

        return response()->json([], 204);
    }

    public function schedule(Request $request, Club $club, CourtSlot $courtSlot): AcademySessionResource|JsonResponse
    {
        $this->authorize('schedule', [$club, $courtSlot]);

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

        try {
            $session = app(CourtSlotSchedulingService::class)->schedule(
                $courtSlot,
                $club,
                $request->user(),
                $date,
                [
                    'title' => $validated['title'] ?? null,
                    'status' => $validated['status'] ?? 'scheduled',
                    'notes' => $validated['notes'] ?? null,
                    'price_per_player' => $validated['price_per_player'] ?? null,
                    'player_ids' => $validated['player_ids'] ?? [],
                ],
            )->load(['court:id,club_id,name,sport_type,price_per_hour', 'coach:id,name,email', 'players:id,name,email'])
                ->loadCount('players');
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (\RuntimeException $exception) {
            return match ($exception->getMessage()) {
                'day_mismatch' => response()->json(['message' => 'The selected date does not match the slot day of week.'], 422),
                'player_conflict' => response()->json(['message' => 'One or more players have a conflicting booking or training session.'], 422),
                'scheduling_conflict' => response()->json(['message' => 'This court already has a booking or training session in the selected slot.'], 422),
                default => throw $exception,
            };
        }

        return new AcademySessionResource($session);
    }

    private function authorizeClub(Club $club, string $ability = 'viewAny'): void
    {
        $this->authorize($ability, [$club]);
    }
}
