<?php

namespace App\Console\Commands;

use App\Models\ClubSaasSubscription;
use Illuminate\Console\Command;

class ExpireSaasSubscriptions extends Command
{
    protected $signature = 'saas:expire-subscriptions';

    protected $description = 'Mark expired SaaS subscriptions and update club statuses';

    public function handle(): void
    {
        // Expire active paid subscriptions
        $expiredPaid = ClubSaasSubscription::query()
            ->where('status', 'active')
            ->where('ends_at', '<', now()->toDateString())
            ->get();

        foreach ($expiredPaid as $sub) {
            $sub->update(['status' => 'expired']);
            $sub->syncClubStatus();
        }

        // Expire trials — club goes inactive, awaiting paid subscription
        $expiredTrials = ClubSaasSubscription::query()
            ->where('status', 'trial')
            ->where('ends_at', '<', now()->toDateString())
            ->get();

        foreach ($expiredTrials as $sub) {
            $sub->update(['status' => 'expired']);
            $sub->club()->update(['subscription_status' => 'inactive']);
        }

        $total = $expiredPaid->count() + $expiredTrials->count();
        $this->info("Marked {$expiredPaid->count()} paid and {$expiredTrials->count()} trial subscription(s) as expired. Total: {$total}.");
    }
}
