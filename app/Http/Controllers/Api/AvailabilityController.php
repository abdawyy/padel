<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourtAvailabilityResource;
use App\Models\Club;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function index(Request $request, Club $club): JsonResponse|mixed
    {
        abort_unless(
            $request->user()?->hasAdminAccess($club) || $request->user()?->belongsToClub($club),
            403,
            'Unauthorized club access.'
        );

        $dateInput = (string) $request->query('date', now()->toDateString());

        try {
            $date = Carbon::parse($dateInput)->toDateString();
        } catch (\Exception) {
            return response()->json(['message' => 'Invalid date format.'], 422);
        }

        $dayStart = Carbon::parse($date)->startOfDay();
        $dayEnd = Carbon::parse($date)->endOfDay();
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        $courts = $club->courts()
            ->with([
                'bookings' => function ($query) use ($dayStart, $dayEnd) {
                    $query->where('start_time', '<', $dayEnd)
                        ->where('end_time', '>', $dayStart)
                        ->whereIn('status', ['pending', 'confirmed'])
                        ->orderBy('start_time');
                },
                'academySessions' => function ($query) use ($dayStart, $dayEnd) {
                    $query->where('start_time', '<', $dayEnd)
                        ->where('end_time', '>', $dayStart)
                        ->whereIn('status', ['scheduled', 'active'])
                        ->orderBy('start_time');
                },
                'slots' => function ($query) use ($dayOfWeek) {
                    $query->where('day_of_week', $dayOfWeek)
                        ->where('is_active', true)
                        ->orderBy('start_time');
                },
            ])
            ->orderBy('name')
            ->get();

        return CourtAvailabilityResource::collection($courts);
    }
}
