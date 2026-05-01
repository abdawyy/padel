<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClubResource;
use App\Models\Club;
use App\Models\ClubSaasSubscription;
use App\Models\SaasPlan;
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

        $plan = SaasPlan::query()->where('is_active', true)->findOrFail($validated['plan_id']);
        $cycle = $validated['billing_cycle'];
        $price = $plan->priceFor($cycle);

        $club = DB::transaction(function () use ($validated, $plan, $cycle, $price, $request) {
            $club = Club::query()->create([
                'name'                => $validated['name'],
                'sport_type'          => $validated['sport_type'] ?? 'padel',
                'address'             => $validated['address'],
                'subscription_status' => 'active',
                'settings'            => $validated['settings'] ?? null,
            ]);

            // Attach the authenticated user as owner
            $request->user()->clubs()->attach($club->id, ['role' => 'owner']);

            // Create SaaS subscription
            $endsAt = $cycle === 'yearly' ? now()->addYear() : now()->addMonth();

            ClubSaasSubscription::query()->create([
                'club_id'       => $club->id,
                'saas_plan_id'  => $plan->id,
                'billing_cycle' => $cycle,
                'amount_paid'   => $price,
                'starts_at'     => now()->toDateString(),
                'ends_at'       => $endsAt->toDateString(),
                'status'        => 'active',
            ]);

            return $club;
        });

        return response()->json([
            'message' => 'Club registered successfully.',
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
                'starts_at'         => $sub->starts_at->toDateString(),
                'ends_at'           => $sub->ends_at->toDateString(),
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
    public function renew(Request $request, Club $club): JsonResponse
    {
        abort_unless($request->user()?->canManageClub($club) || $request->user()?->isSuperAdmin(), 403);

        $validated = $request->validate([
            'plan_id'            => ['required', 'integer', 'exists:saas_plans,id'],
            'billing_cycle'      => ['required', 'in:monthly,yearly'],
            'payment_reference'  => ['nullable', 'string', 'max:255'],
        ]);

        $plan = SaasPlan::query()->where('is_active', true)->findOrFail($validated['plan_id']);
        $cycle = $validated['billing_cycle'];
        $price = $plan->priceFor($cycle);
        $endsAt = $cycle === 'yearly' ? now()->addYear() : now()->addMonth();

        $sub = ClubSaasSubscription::query()->create([
            'club_id'           => $club->id,
            'saas_plan_id'      => $plan->id,
            'billing_cycle'     => $cycle,
            'amount_paid'       => $price,
            'starts_at'         => now()->toDateString(),
            'ends_at'           => $endsAt->toDateString(),
            'status'            => 'active',
            'payment_reference' => $validated['payment_reference'] ?? null,
        ]);

        $sub->syncClubStatus();

        return response()->json([
            'message' => 'Subscription renewed successfully.',
            'data'    => [
                'id'             => $sub->id,
                'plan'           => ['id' => $plan->id, 'name' => $plan->name],
                'billing_cycle'  => $sub->billing_cycle,
                'amount_paid'    => (float) $sub->amount_paid,
                'starts_at'      => $sub->starts_at->toDateString(),
                'ends_at'        => $sub->ends_at->toDateString(),
                'status'         => $sub->status,
                'days_remaining' => $sub->daysRemaining(),
            ],
        ]);
    }
}
