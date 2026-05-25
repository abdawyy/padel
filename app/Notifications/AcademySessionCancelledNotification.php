<?php

namespace App\Notifications;

use App\Models\AcademySession;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AcademySessionCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly AcademySession $session,
        private readonly ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $court = $this->session->court?->name ?? 'Court';
        $club = $this->session->club?->name ?? '';
        $date = $this->session->start_time?->format('Y-m-d') ?? 'N/A';
        $start = $this->session->start_time?->format('H:i') ?? '';
        $end = $this->session->end_time?->format('H:i') ?? '';

        $message = (new MailMessage())
            ->subject("Training Cancelled – {$this->session->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line('A training session you were enrolled in has been cancelled.')
            ->line("**Session:** {$this->session->title}")
            ->line("**Court:** {$court}".($club ? " at {$club}" : ''))
            ->line("**Date:** {$date}  |  **Time:** {$start} – {$end}");

        if ($this->reason) {
            $message->line('**Reason:** '.$this->reason);
        }

        return $message->action('View My Training', url('/player/my-training'));
    }
}
