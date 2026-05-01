<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Booking $booking) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $booking  = $this->booking;
        $court    = $booking->court?->name ?? 'Court';
        $club     = $booking->court?->club?->name ?? '';
        $date     = $booking->booking_date ?? 'N/A';
        $start    = $booking->start_time ?? '';
        $end      = $booking->end_time ?? '';

        return (new MailMessage())
            ->subject("Booking Confirmed – {$court} on {$date}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your booking has been confirmed!")
            ->line("**Court:** {$court}" . ($club ? " at {$club}" : ''))
            ->line("**Date:** {$date}  |  **Time:** {$start} – {$end}")
            ->line("**Total:** {$booking->total_price} EGP")
            ->action('View Booking', url('/'))
            ->line('Thank you for booking with us!');
    }
}
