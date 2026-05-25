<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackageResource;
use App\Models\Club;
use App\Models\Package;
use App\Models\PackageSubscription;
use App\Services\PaymobService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    public function index(Club $club)
    {
        $packages = Package::query()
            ->where('club_id', $club->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return PackageResource::collection($packages);
    }

    public function subscribe(Request $request, Club $club, PaymobService $paymobService): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'package_id' => ['required', 'integer', 'exists:packages,id'],
        ]);

        $package = Package::query()
            ->where('club_id', $club->id)
            ->where('is_active', true)
            ->findOrFail($validated['package_id']);

        $price = (float) $package->price;

        if ($price <= 0) {
            $subscription = $this->activateSubscription($package, $user);

            return response()->json([
                'message' => 'Package subscribed successfully.',
                'subscription' => $this->subscriptionPayload($subscription),
            ], 201);
        }

        $subscription = PackageSubscription::query()->create([
            'package_id' => $package->id,
            'user_id' => $user->id,
            'starts_at' => now()->toDateString(),
            'expires_at' => now()->addDays(max((int) $package->duration_days, 30))->toDateString(),
            'sessions_remaining' => $package->session_count,
            'status' => 'suspended',
            'notes' => 'Awaiting payment.',
        ]);

        $payment = $paymobService->createPaymentSessionForPackageSubscription($subscription, $user, $price);

        return response()->json([
            'message' => 'Payment required to activate package.',
            'subscription_id' => $subscription->id,
            'amount_due' => $price,
            'payment' => $payment,
        ], 402);
    }

    public static function activateSubscription(Package $package, User $user): PackageSubscription
    {
        $startsAt = now()->startOfDay();
        $expiresAt = $startsAt->copy()->addDays(max((int) $package->duration_days, 30));

        return PackageSubscription::query()->updateOrCreate(
            [
                'package_id' => $package->id,
                'user_id' => $user->id,
                'status' => 'active',
            ],
            [
                'starts_at' => $startsAt->toDateString(),
                'expires_at' => $expiresAt->toDateString(),
                'sessions_remaining' => $package->session_count,
                'notes' => null,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function subscriptionPayload(PackageSubscription $subscription): array
    {
        $subscription->loadMissing('package');

        return [
            'id' => $subscription->id,
            'package_id' => $subscription->package_id,
            'status' => $subscription->status,
            'starts_at' => $subscription->starts_at?->toDateString(),
            'expires_at' => $subscription->expires_at?->toDateString(),
            'sessions_remaining' => $subscription->sessions_remaining,
            'package' => $subscription->package ? new PackageResource($subscription->package) : null,
        ];
    }
}
