<?php

namespace App\Notifications;

use App\Models\PackageSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PackageExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly PackageSubscription $subscription,
        private readonly int $daysRemaining,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $packageName = $this->subscription->package?->name ?? 'your package';
        $clubName = $this->subscription->package?->club?->name ?? 'the club';

        return (new MailMessage())
            ->subject("Package expiring in {$this->daysRemaining} day(s)")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your package **{$packageName}** at **{$clubName}** expires in **{$this->daysRemaining} day(s)**.")
            ->line('Sessions remaining: '.($this->subscription->sessions_remaining ?? 'unlimited'))
            ->action('View My Packages', url('/player/my-packages'));
    }
}
