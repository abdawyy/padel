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
        $graceDays = 3;

        // Subscriptions past grace period → expire and deactivate club
        $expiredPaid = ClubSaasSubscription::query()
            ->where('status', 'active')
            ->where('ends_at', '<', now()->subDays($graceDays)->toDateString())
            ->get();

        foreach ($expiredPaid as $sub) {
            $sub->update(['status' => 'expired']);
            $sub->syncClubStatus();
        }

        // Subscriptions within grace period → mark as past_due (club stays active)
        $pastDue = ClubSaasSubscription::query()
            ->where('status', 'active')
            ->whereBetween('ends_at', [
                now()->subDays($graceDays)->toDateString(),
                now()->toDateString(),
            ])
            ->get();

        foreach ($pastDue as $sub) {
            $sub->update(['status' => 'past_due']);
            $sub->club()->update(['subscription_status' => 'active']); // still active during grace
        }

        // Expire trials — no grace period for trials
        $expiredTrials = ClubSaasSubscription::query()
            ->where('status', 'trial')
            ->where('ends_at', '<', now()->toDateString())
            ->get();

        foreach ($expiredTrials as $sub) {
            $sub->update(['status' => 'expired']);
            $sub->club()->update(['subscription_status' => 'inactive']);
        }

        $this->info("Expired: {$expiredPaid->count()} paid, {$expiredTrials->count()} trial. Past-due (grace): {$pastDue->count()}.");
    }
}
