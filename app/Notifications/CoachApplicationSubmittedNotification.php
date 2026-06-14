<?php

namespace App\Notifications;

use App\Models\AcademySession;
use App\Models\CoachApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CoachApplicationSubmittedNotification extends Notification implements ShouldQueue
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
        $coach = $this->application->coach;

        $mail = (new MailMessage())
            ->subject("New coach application – {$this->session->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("**{$coach?->name}** applied to coach session **{$this->session->title}**.");

        if ($this->application->message) {
            $mail->line('Message: '.$this->application->message);
        }

        return $mail->action('Review Applications', url('/admin/academy-sessions/'.$this->session->id));
    }
}
