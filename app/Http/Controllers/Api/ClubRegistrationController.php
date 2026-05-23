<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClubResource;
use App\Models\Club;
use App\Models\ClubSaasSubscription;
use App\Models\SaasPlan;
use App\Services\PaymobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClubRegistrationController extends Controller
{
    /**
     * Register a new academy/club and subscribe to a SaaS plan.
     * POST /api/register-club
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'sport_type'    => ['nullable', 'string', 'max:100'],
            'address'       => ['required', 'string'],
            'settings'      => ['nullable', 'array'],
            'plan_id'       => ['required', 'integer', 'exists:saas_plans,id'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
        ]);

        $user = $request->user();

        if ($user->clubs()->where('registration_status', 'pending')->exists()) {
            return response()->json([
                'message' => 'You already have a club registration awaiting approval.',
            ], 422);
        }

        $plan = SaasPlan::query()->where('is_active', true)->findOrFail($validated['plan_id']);
        $cycle = $validated['billing_cycle'];
        $price = $plan->priceFor($cycle);

        $club = DB::transaction(function () use ($validated, $plan, $cycle, $price, $user) {
            $club = Club::query()->create([
                'name'                => $validated['name'],
                'sport_type'          => $validated['sport_type'] ?? 'padel',
                'address'             => $validated['address'],
                'subscription_status' => 'inactive',
                'registration_status' => 'pending',
                'settings'            => $validated['settings'] ?? null,
            ]);

            $user->clubs()->attach($club->id, ['role' => 'owner']);

            ClubSaasSubscription::query()->create([
                'club_id'       => $club->id,
                'saas_plan_id'  => $plan->id,
                'billing_cycle' => $cycle,
                'amount_paid'   => $price,
                'starts_at'     => null,
                'ends_at'       => null,
                'status'        => 'pending',
            ]);

            return $club;
        });

        return response()->json([
            'message' => 'Club registration submitted. Awaiting super admin approval.',
            'club'    => new ClubResource($club->load(['activeSaasSubscription.plan'])),
        ], 201);
    }

    /**
     * Get the current SaaS subscription for a club.
     * GET /api/clubs/{club}/saas-subscription
     */
    public function show(Request $request, Club $club): JsonResponse
    {
        abort_unless($request->user()?->canManageClub($club) || $request->user()?->isSuperAdmin(), 403);

        $sub = $club->latestSaasSubscription()->with('plan')->first();

        if (! $sub) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => [
                'id'                => $sub->id,
                'plan'              => $sub->plan ? [
                    'id'   => $sub->plan->id,
                    'name' => $sub->plan->name,
                    'slug' => $sub->plan->slug,
                ] : null,
                'billing_cycle'     => $sub->billing_cycle,
                'amount_paid'       => (float) $sub->amount_paid,
                'starts_at'         => $sub->starts_at?->toDateString(),
                'ends_at'           => $sub->ends_at?->toDateString(),
                'status'            => $sub->status,
                'days_remaining'    => $sub->daysRemaining(),
                'is_active'         => $sub->isActive(),
            ],
        ]);
    }

    /**
     * Renew or change the SaaS subscription for a club.
     * POST /api/clubs/{club}/saas-subscription
     */
    public function renew(Request $request, Club $club, PaymobService $paymobService): JsonResponse
    {
        abort_unless($request->user()?->canManageClub($club) || $request->user()?->isSuperAdmin(), 403);

        $validated = $request->validate([
            'plan_id'       => ['required', 'integer', 'exists:saas_plans,id'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
        ]);

        $plan = SaasPlan::query()->where('is_active', true)->findOrFail($validated['plan_id']);
        $cycle = $validated['billing_cycle'];
        $price = $plan->priceFor($cycle);
        ClubSaasSubscription::query()
            ->where('club_id', $club->id)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        $sub = ClubSaasSubscription::query()->create([
            'club_id'       => $club->id,
            'saas_plan_id'  => $plan->id,
            'billing_cycle' => $cycle,
            'amount_paid'   => $price,
            'starts_at'     => null,
            'ends_at'       => null,
            'status'        => 'pending',
        ]);

        $payment = $paymobService->createPaymentSessionForSaasSubscription(
            $sub,
            $request->user(),
            $price,
        );

        return response()->json([
            'message' => 'Payment required to activate subscription.',
            'data'    => [
                'id'             => $sub->id,
                'plan'           => ['id' => $plan->id, 'name' => $plan->name],
                'billing_cycle'  => $sub->billing_cycle,
                'amount_paid'    => (float) $sub->amount_paid,
                'starts_at'      => $sub->starts_at?->toDateString(),
                'ends_at'        => $sub->ends_at?->toDateString(),
                'status'         => $sub->status,
                'days_remaining' => $sub->daysRemaining(),
            ],
            'payment' => $payment,
        ], 402);
    }
}
