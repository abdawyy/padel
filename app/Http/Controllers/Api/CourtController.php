<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourtResource;
use App\Models\Club;
use App\Models\Court;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourtController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $courts = Court::query()
            ->whereHas('club', function ($query) {
                $query->where('subscription_status', 'active');
            })
            ->when($request->filled('club_id'), function ($query) use ($request) {
                $query->where('club_id', (int) $request->query('club_id'));
            })
            ->when($request->filled('sport_type'), function ($query) use ($request) {
                $query->where('sport_type', (string) $request->query('sport_type'));
            })
            ->when($request->boolean('active_only', true), function ($query) {
                $query->where('is_active', true);
            })
            ->with('club')
            ->latest()
            ->paginate();

        return CourtResource::collection($courts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'sport_type' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:indoor,outdoor'],
            'price_per_hour' => ['required', 'numeric', 'min:0'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:32'],
            'slot_duration_minutes' => ['nullable', 'integer', 'min:15', 'max:180'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $club = Club::query()->findOrFail($validated['club_id']);

        if (! $request->user()?->hasAdminAccess($club)) {
            return response()->json(['message' => 'Unauthorized club access.'], 403);
        }

        $court = Court::query()->create($validated);

        return new CourtResource($court->load('club'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): CourtResource
    {
        $court = Court::query()
            ->where('id', $id)
            ->whereHas('club', function ($query) {
                $query->where('subscription_status', 'active');
            })
            ->with('club')
            ->firstOrFail();

        return new CourtResource($court);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): CourtResource
    {
        $court = Court::query()->with('club')->findOrFail($id);

        abort_unless($request->user()?->hasAdminAccess($court->club), 403, 'Unauthorized club access.');

        $validated = $request->validate([
            'sport_type' => ['sometimes', 'string', 'max:100'],
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:indoor,outdoor'],
            'price_per_hour' => ['sometimes', 'numeric', 'min:0'],
            'capacity' => ['sometimes', 'integer', 'min:1', 'max:32'],
            'slot_duration_minutes' => ['sometimes', 'integer', 'min:15', 'max:180'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $court->update($validated);

        return new CourtResource($court->fresh()->load('club'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $court = Court::query()->with('club')->findOrFail($id);

        abort_unless(request()->user()?->hasAdminAccess($court->club), 403, 'Unauthorized club access.');

        $court->delete();

        return response()->json([], 204);
    }
}
