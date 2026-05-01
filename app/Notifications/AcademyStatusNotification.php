<?php

namespace App\Notifications;

use App\Models\Club;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AcademyStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Club $club,
        private readonly string $status, // 'approved' | 'rejected'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->status === 'approved') {
            return (new MailMessage())
                ->subject("Your academy '{$this->club->name}' has been approved!")
                ->greeting("Hello {$notifiable->name},")
                ->line("Great news! Your academy **{$this->club->name}** has been approved.")
                ->line('You now have a 14-day free trial. Log in to the admin panel to get started.')
                ->action('Go to Admin Panel', url('/admin'))
                ->line('Thank you for joining our platform!');
        }

        return (new MailMessage())
            ->subject("Your academy registration was not approved")
            ->greeting("Hello {$notifiable->name},")
            ->line("Unfortunately, your academy **{$this->club->name}** was not approved.")
            ->line('Reason: ' . ($this->club->rejection_reason ?? 'No reason provided.'))
            ->line('If you believe this is a mistake, please contact support.')
            ->action('Contact Support', url('/'));
    }
}
