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
        $graceCutoff = now()->subDays($graceDays)->toDateString();
        $today = now()->toDateString();

        // Active or past_due subscriptions past grace → expired and deactivate club
        $expiredPaid = ClubSaasSubscription::query()
            ->whereIn('status', ['active', 'past_due'])
            ->where('ends_at', '<', $graceCutoff)
            ->get();

        foreach ($expiredPaid as $sub) {
            $sub->update(['status' => 'expired']);
            $sub->syncClubStatus();
        }

        // Active subscriptions within grace → past_due (club stays active)
        $pastDue = ClubSaasSubscription::query()
            ->where('status', 'active')
            ->whereBetween('ends_at', [$graceCutoff, $today])
            ->get();

        foreach ($pastDue as $sub) {
            $sub->update(['status' => 'past_due']);
            $sub->syncClubStatus();
        }

        // Expire trials — no grace period for trials
        $expiredTrials = ClubSaasSubscription::query()
            ->where('status', 'trial')
            ->where('ends_at', '<', $today)
            ->get();

        foreach ($expiredTrials as $sub) {
            $sub->update(['status' => 'expired']);
            $sub->syncClubStatus();
        }

        $this->info("Expired: {$expiredPaid->count()} paid/past-due, {$expiredTrials->count()} trial. Past-due (grace): {$pastDue->count()}.");
    }
}
