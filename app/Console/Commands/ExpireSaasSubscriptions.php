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
        $expired = ClubSaasSubscription::query()
            ->where('status', 'active')
            ->where('ends_at', '<', now()->toDateString())
            ->get();

        foreach ($expired as $sub) {
            $sub->update(['status' => 'expired']);
            $sub->syncClubStatus();
        }

        $this->info("Marked {$expired->count()} subscription(s) as expired.");
    }
}
