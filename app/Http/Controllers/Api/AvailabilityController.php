<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourtAvailabilityResource;
use App\Models\Club;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function index(Request $request, Club $club)
    {
        $dateInput = (string) $request->query('date', now()->toDateString());
        $date = Carbon::parse($dateInput)->toDateString();
        $dayOfWeek = Carbon::parse($dateInput)->dayOfWeek;

        $courts = $club->courts()
            ->with([
                'bookings' => function ($query) use ($date) {
                    $query->whereDate('start_time', $date)
                        ->whereIn('status', ['pending', 'confirmed'])
                        ->orderBy('start_time');
                },
                'academySessions' => function ($query) use ($date) {
                    $query->whereDate('start_time', $date)
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
