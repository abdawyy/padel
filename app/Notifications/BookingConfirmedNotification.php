<?php

namespace App\Notifications;

use App\Filament\Player\Pages\MyMatches;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
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
        $booking = $this->booking;
        $court   = $booking->court?->name ?? 'Court';
        $club    = $booking->court?->club?->name ?? '';
        $date    = $booking->start_time?->format('Y-m-d') ?? 'N/A';
        $start   = $booking->start_time?->format('H:i') ?? '';
        $end     = $booking->end_time?->format('H:i') ?? '';

        return (new MailMessage())
            ->subject("Booking Confirmed – {$court} on {$date}")
            ->greeting("Hello {$notifiable->name},")
            ->line('Your booking has been confirmed!')
            ->line("**Court:** {$court}".($club ? " at {$club}" : ''))
            ->line("**Date:** {$date}  |  **Time:** {$start} – {$end}")
            ->line("**Total:** {$booking->total_price} EGP")
            ->action('View in Player Portal', MyMatches::getUrl(panel: 'player').'#booking-'.$booking->id)
            ->line('Thank you for booking with us!');
    }
}
