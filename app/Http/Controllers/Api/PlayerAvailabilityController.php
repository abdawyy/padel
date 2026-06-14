<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourtAvailabilityResource;
use App\Models\Club;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerAvailabilityController extends Controller
{
    public function show(Request $request, Club $club): JsonResponse|mixed
    {
        if (! $this->clubAcceptsBookings($club)) {
            return response()->json(['message' => 'This club is not accepting bookings.'], 422);
        }

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
            ->where('is_active', true)
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
            ])
            ->orderBy('name')
            ->get();

        return CourtAvailabilityResource::collection($courts);
    }

    private function clubAcceptsBookings(Club $club): bool
    {
        return $club->registration_status === 'approved'
            && in_array($club->subscription_status, ['active', 'trial'], true);
    }
}
