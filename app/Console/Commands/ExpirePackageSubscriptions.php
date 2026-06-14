<?php

namespace App\Console\Commands;

use App\Models\PackageSubscription;
use App\Models\User;
use App\Notifications\PackageExpiringNotification;
use Illuminate\Console\Command;

class ExpirePackageSubscriptions extends Command
{
    protected $signature = 'packages:expire {--notify-days=3 : Notify users this many days before expiry}';

    protected $description = 'Expire package subscriptions past their end date and notify users before expiry';

    public function handle(): int
    {
        $notifyDays = (int) $this->option('notify-days');

        $expired = PackageSubscription::query()
            ->where('status', 'active')
            ->whereDate('expires_at', '<', now()->toDateString())
            ->update(['status' => 'expired']);

        $this->info("Expired {$expired} package subscription(s).");

        $expiringSoon = PackageSubscription::query()
            ->with(['package.club', 'user'])
            ->where('status', 'active')
            ->whereDate('expires_at', now()->addDays($notifyDays)->toDateString())
            ->get();

        foreach ($expiringSoon as $subscription) {
            $subscription->user?->notify(
                new PackageExpiringNotification($subscription, $notifyDays)
            );
        }

        $this->info("Notified {$expiringSoon->count()} user(s) about expiring packages.");

        return self::SUCCESS;
    }
}
