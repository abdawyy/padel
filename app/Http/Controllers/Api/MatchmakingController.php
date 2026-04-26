<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OpenMatchResource;
use App\Models\Booking;
use App\Services\PaymobService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchmakingController extends Controller
{
    public function index(Request $request)
    {
        $matches = Booking::query()
            ->where('match_type', 'open_match')
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_time', '>', now())
            ->when($request->filled('session_type'), function ($query) use ($request) {
                $query->where('session_type', (string) $request->query('session_type'));
            })
            ->when($request->boolean('coached_only'), function ($query) {
                $query->whereNotNull('coach_user_id');
            })
            ->when($request->filled('club_id'), function ($query) use ($request) {
                $clubId = (int) $request->query('club_id');

                $query->whereHas('court', function ($courtQuery) use ($clubId) {
                    $courtQuery->where('club_id', $clubId);
                });
            })
            ->withCount('participants')
            ->havingRaw('participants_count < max_players')
            ->with([
                'court:id,club_id,name,price_per_hour',
                'court.club:id,name',
                'coach:id,name,email',
                'participants:id,name',
            ])
            ->orderBy('start_time')
            ->paginate();

        return OpenMatchResource::collection($matches);
    }

    public function join(Request $request, Booking $booking, PaymobService $paymobService): JsonResponse
    {
        $user = $request->user();

        if ($booking->match_type !== 'open_match') {
            return response()->json(['message' => 'This booking is not an open match.'], 422);
        }

        if (! in_array($booking->status, ['pending', 'confirmed'], true)) {
            return response()->json(['message' => 'This match is no longer joinable.'], 422);
        }

        if (Carbon::parse($booking->start_time)->lte(now())) {
            return response()->json(['message' => 'This match has already started.'], 422);
        }

        try {
            $paymentSession = DB::transaction(function () use ($booking, $user, $paymobService) {
            $freshBooking = Booking::query()
                ->whereKey($booking->id)
                ->lockForUpdate()
                ->withCount('participants')
                ->firstOrFail();

                $alreadyJoined = DB::table('booking_participants')
                    ->where('booking_id', $freshBooking->id)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($alreadyJoined) {
                    throw new \RuntimeException('already_joined');
                }

                $maxPlayers = max((int) $freshBooking->max_players, 1);

                if ((int) $freshBooking->participants_count >= $maxPlayers) {
                    throw new \RuntimeException('full');
                }

                $amountDue = round(((float) $freshBooking->total_price) / $maxPlayers, 2);

                DB::table('booking_participants')->insert([
                    'booking_id' => $freshBooking->id,
                    'user_id' => $user->id,
                    'amount_due' => $amountDue,
                    'payment_status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return $paymobService->createPaymentSessionForParticipant($freshBooking, $user, $amountDue);
            });
        } catch (\RuntimeException $exception) {
            if ($exception->getMessage() === 'already_joined') {
                return response()->json(['message' => 'You already joined this match.'], 409);
            }

            if ($exception->getMessage() === 'full') {
                return response()->json(['message' => 'This match is already full.'], 422);
            }

            throw $exception;
        }

        return response()->json($paymentSession);
    }
}
