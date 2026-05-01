<?php

namespace App\Notifications;

use App\Models\ClubSaasSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ClubSaasSubscription $subscription,
        private readonly int $daysRemaining,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $clubName = $this->subscription->club->name ?? 'your academy';

        return (new MailMessage())
            ->subject("Action required: subscription for '{$clubName}' expires in {$this->daysRemaining} day(s)")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your SaaS subscription for **{$clubName}** will expire in **{$this->daysRemaining} day(s)**.")
            ->line('Please renew your subscription to avoid service interruption.')
            ->action('Renew Subscription', url('/admin'))
            ->line('After expiry, a 3-day grace period applies before the academy is deactivated.');
    }
}
