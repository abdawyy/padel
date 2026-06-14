<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BookingReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Booking $booking,
        private readonly int $hoursBefore,
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

        return (new MailMessage())
            ->subject("Reminder: match in {$this->hoursBefore}h – {$court}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your match starts in **{$this->hoursBefore} hour(s)**.")
            ->line("**Court:** {$court}".($club ? " at {$club}" : ''))
            ->line("**Date:** {$date}  |  **Time:** {$start} – {$end}")
            ->action('View My Matches', url('/player/my-matches'));
    }
}
