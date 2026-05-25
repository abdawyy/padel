<?php

namespace App\Notifications;

use App\Models\Club;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ClubRegistrationPendingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Club $club) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Academy registration submitted – {$this->club->name}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your academy **{$this->club->name}** has been submitted for review.")
            ->line('You will receive another email once a super admin approves or rejects your application.')
            ->line('After approval, you receive a 14-day free trial to get started.');
    }
}
