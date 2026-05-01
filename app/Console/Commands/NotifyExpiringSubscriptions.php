<?php

namespace App\Console\Commands;

use App\Models\ClubSaasSubscription;
use App\Models\User;
use App\Notifications\SubscriptionExpiringNotification;
use Illuminate\Console\Command;

class NotifyExpiringSubscriptions extends Command
{
    protected $signature   = 'saas:notify-expiring';
    protected $description = 'Send 7-day expiry warning emails to academy owners.';

    public function handle(): int
    {
        $subscriptions = ClubSaasSubscription::query()
            ->with('club.users')
            ->whereIn('status', ['active', 'trial'])
            ->whereDate('ends_at', now()->addDays(7)->toDateString())
            ->get();

        foreach ($subscriptions as $subscription) {
            $daysRemaining = $subscription->daysRemaining();

            $subscription->club->users()
                ->whereHas('clubUsers', fn ($q) => $q->where('role', 'owner'))
                ->get()
                ->each(fn (User $owner) => $owner->notify(
                    new SubscriptionExpiringNotification($subscription, $daysRemaining)
                ));
        }

        $this->info("Notified {$subscriptions->count()} expiring subscriptions.");

        return self::SUCCESS;
    }
}
