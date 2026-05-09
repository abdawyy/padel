<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClubResource;
use App\Models\Club;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClubController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clubs = Club::query()
            ->where('subscription_status', 'active')
            ->withCount('courts')
            ->latest()
            ->paginate();

        return ClubResource::collection($clubs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sport_type' => ['nullable', 'string', 'max:100'],
            'address' => ['required', 'string'],
            'subscription_status' => ['nullable', 'string', 'max:100'],
            'settings' => ['nullable', 'array'],
        ]);

        $club = Club::query()->create([
            'name' => $validated['name'],
            'sport_type' => $validated['sport_type'] ?? 'padel',
            'address' => $validated['address'],
            'subscription_status' => $validated['subscription_status'] ?? 'active',
            'settings' => $validated['settings'] ?? null,
        ]);

        $request->user()->clubs()->attach($club->id, ['role' => 'owner']);

        return new ClubResource($club);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): ClubResource
    {
        $query = Club::query()
            ->where('clubs.id', $id)
            ->withCount('courts');

        $user = request()->user();

        if ($user?->isSuperAdmin() || $user?->belongsToClub((int) $id)) {
            $club = $query->firstOrFail();
        } else {
            $club = $query->where('subscription_status', 'active')->firstOrFail();
        }

        return new ClubResource($club);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): ClubResource
    {
        $club = Club::query()->findOrFail($id);

        abort_unless($request->user()?->canManageClub($club), 403, 'Unauthorized club access.');

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'sport_type' => ['sometimes', 'string', 'max:100'],
            'address' => ['sometimes', 'string'],
            'subscription_status' => ['sometimes', 'string', 'max:100'],
            'settings' => ['nullable', 'array'],
        ]);

        $club->update($validated);

        return new ClubResource($club->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $club = Club::query()->findOrFail($id);

        abort_unless(request()->user()?->canManageClub($club), 403, 'Unauthorized club access.');

        $club->delete();

        return response()->json([], 204);
    }

    /**
     * Return sport-specific rules for a club.
     * GET /api/clubs/{club}/sport-rules/{sport}
     */
    public function sportRules(Club $club, string $sport): JsonResponse
    {
        return response()->json([
            'sport' => $sport,
            'rules' => $club->getRulesForSport($sport),
        ]);
    }
}
