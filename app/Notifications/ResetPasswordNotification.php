<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = rtrim((string) config('app.frontend_url', ''), '/');
        $resetUrl = $frontendUrl !== ''
            ? $frontendUrl.'/reset-password?token='.$this->token.'&email='.urlencode($notifiable->email)
            : url('/reset-password/'.$this->token.'?email='.urlencode($notifiable->email));

        return (new MailMessage())
            ->subject('Reset Your Password')
            ->greeting("Hello {$notifiable->name},")
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $resetUrl)
            ->line('If you did not request a password reset, no further action is required.')
            ->line('You can also reset via the API using your email, this token, and a new password.');
    }
}
