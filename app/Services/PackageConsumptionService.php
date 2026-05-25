<?php

namespace App\Services;

use App\Models\Club;
use App\Models\PackageSubscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PackageConsumptionService
{
    public function consumeSessionForUser(User $user, Club|int $club): bool
    {
        $clubId = $club instanceof Club ? $club->id : $club;

        return (bool) DB::transaction(function () use ($user, $clubId) {
            $subscription = PackageSubscription::query()
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->where('expires_at', '>=', now()->toDateString())
                ->whereHas('package', fn ($q) => $q->where('club_id', $clubId)->where('is_active', true))
                ->where(function ($q) {
                    $q->whereNull('sessions_remaining')
                        ->orWhere('sessions_remaining', '>', 0);
                })
                ->lockForUpdate()
                ->orderBy('expires_at')
                ->first();

            if (! $subscription) {
                return false;
            }

            if ($subscription->sessions_remaining === null) {
                return true;
            }

            if ($subscription->sessions_remaining <= 0) {
                return false;
            }

            $subscription->decrement('sessions_remaining');

            if ($subscription->sessions_remaining <= 0) {
                $subscription->update(['status' => 'expired']);
            }

            return true;
        });
    }
}
