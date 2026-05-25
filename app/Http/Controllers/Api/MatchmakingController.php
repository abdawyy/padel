<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OpenMatchResource;
use App\Models\Booking;
use App\Services\BookingParticipantCapacity;
use App\Services\BookingPaymentSplit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchmakingController extends Controller
{
    public function index(Request $request)
    {
        $matches = BookingParticipantCapacity::addCapacityCount(
            Booking::query()
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
            ->when($request->filled('skill_level'), function ($query) use ($request) {
                $skill = (int) $request->query('skill_level');
                $query->where(function ($q) use ($skill) {
                    $q->whereNull('skill_min')->orWhere('skill_min', '<=', $skill);
                })->where(function ($q) use ($skill) {
                    $q->whereNull('skill_max')->orWhere('skill_max', '>=', $skill);
                });
            })
            ->when($request->filled('sport_type'), function ($query) use ($request) {
                $query->where('sport_type', (string) $request->query('sport_type'));
            })
            ->havingRaw('capacity_slots_used < max_players')
        )->with([
                'court:id,club_id,name,price_per_hour',
                'court.club:id,name',
                'coach:id,name,email',
                'participants:id,name',
            ])
            ->orderBy('start_time')
            ->paginate();

        return OpenMatchResource::collection($matches);
    }

    public function join(Request $request, Booking $booking): JsonResponse
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

        $userSkill = (int) ($user->skill_level ?? 0);
        if ($userSkill > 0 && ! $booking->isSkillCompatible($userSkill)) {
            return response()->json([
                'message'    => 'Your skill level does not meet the requirements for this match.',
                'skill_min'  => $booking->skill_min,
                'skill_max'  => $booking->skill_max,
                'your_level' => $userSkill,
            ], 403);
        }

        try {
            [$freshBooking, $amountDue] = DB::transaction(function () use ($booking, $user) {
                $freshBooking = Booking::query()
                    ->whereKey($booking->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $alreadyJoined = DB::table('booking_participants')
                    ->where('booking_id', $freshBooking->id)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($alreadyJoined) {
                    throw new \RuntimeException('already_joined');
                }

                $maxPlayers = max((int) $freshBooking->max_players, 1);
                $usedSlots = BookingParticipantCapacity::countForBooking((int) $freshBooking->id);

                if ($usedSlots >= $maxPlayers) {
                    throw new \RuntimeException('full');
                }

                $amountDue = BookingPaymentSplit::amountForSlot(
                    (float) $freshBooking->total_price,
                    $maxPlayers,
                    $usedSlots,
                );

                DB::table('booking_participants')->insert([
                    'booking_id' => $freshBooking->id,
                    'user_id' => $user->id,
                    'amount_due' => $amountDue,
                    'payment_status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return [$freshBooking, $amountDue];
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

        try {
            $paymentSession = app(\App\Services\BookingPaymentService::class)->createParticipantPayment(
                $freshBooking,
                $user,
                $request->header('X-Idempotency-Key'),
            );
        } catch (\Throwable $exception) {
            DB::table('booking_participants')
                ->where('booking_id', $freshBooking->id)
                ->where('user_id', $user->id)
                ->where('payment_status', 'pending')
                ->delete();

            throw $exception;
        }

        return response()->json($paymentSession);
    }
}
