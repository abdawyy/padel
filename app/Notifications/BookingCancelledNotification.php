<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BookingCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Booking $booking,
        private readonly ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $court = $this->booking->court?->name ?? 'Court';
        $club = $this->booking->court?->club?->name ?? '';
        $date = $this->booking->start_time?->format('Y-m-d') ?? 'N/A';
        $start = $this->booking->start_time?->format('H:i') ?? '';
        $end = $this->booking->end_time?->format('H:i') ?? '';

        $message = (new MailMessage())
            ->subject("Booking Cancelled – {$court} on {$date}")
            ->greeting("Hello {$notifiable->name},")
            ->line('A booking you are part of has been cancelled.')
            ->line("**Court:** {$court}".($club ? " at {$club}" : ''))
            ->line("**Date:** {$date}  |  **Time:** {$start} – {$end}");

        if ($this->reason) {
            $message->line('**Reason:** '.$this->reason);
        }

        return $message->action('View My Matches', url('/player/my-matches'));
    }
}
