<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SaasPlan;
use Illuminate\Http\JsonResponse;

class SaasPlanController extends Controller
{
    /**
     * List all active SaaS plans (public).
     */
    public function index(): JsonResponse
    {
        $plans = SaasPlan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (SaasPlan $plan) => [
                'id'            => $plan->id,
                'name'          => $plan->name,
                'slug'          => $plan->slug,
                'description'   => $plan->description,
                'monthly_price' => (float) $plan->monthly_price,
                'yearly_price'  => (float) $plan->yearly_price,
                'features'      => $plan->features ?? [],
            ]);

        return response()->json(['data' => $plans]);
    }
}
