<?php

namespace App\Services;

use App\Models\Club;
use App\Models\Court;
use Illuminate\Http\JsonResponse;

class BookableCourtValidator
{
    public static function bookingUnavailableResponse(Court $court): ?JsonResponse
    {
        $court->loadMissing('club');

        if (! $court->is_active) {
            return response()->json([
                'message' => 'This court is not available for booking.',
            ], 422);
        }

        $club = $court->club;
        if (! $club instanceof Club) {
            return response()->json([
                'message' => 'This court is not linked to an active club.',
            ], 422);
        }

        if ($club->registration_status !== 'approved') {
            return response()->json([
                'message' => 'Bookings are not available until the club registration is approved.',
            ], 422);
        }

        if (! in_array($club->subscription_status, ['active', 'trial'], true)) {
            return response()->json([
                'message' => 'This club is not accepting bookings at the moment.',
            ], 422);
        }

        return null;
    }
}
