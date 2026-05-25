<?php

namespace App\Notifications;

use App\Models\AcademySession;
use App\Models\CoachApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CoachApplicationRespondedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly CoachApplication $application,
        private readonly AcademySession $session,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $accepted = $this->application->status === 'accepted';

        $mail = (new MailMessage())
            ->subject($accepted ? 'Coach application accepted' : 'Coach application declined')
            ->greeting("Hello {$notifiable->name},")
            ->line($accepted
                ? "Your application to coach **{$this->session->title}** was accepted."
                : "Your application to coach **{$this->session->title}** was declined.");

        if ($this->application->response_note) {
            $mail->line('Note: '.$this->application->response_note);
        }

        return $mail->action('View Sessions', url('/coach'));
    }
}
