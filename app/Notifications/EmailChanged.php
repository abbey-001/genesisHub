<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailChanged extends Notification
{
    public function __construct(
        private string $oldEmail,
        private string $newEmail
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Security alert: Email address changed — ' . config('app.name'))
            ->greeting('Your email address was changed')
            ->line('The email address on your ' . config('app.name') . ' account was just updated.')
            ->line('**New email address:** ' . $this->newEmail)
            ->line('**Changed at:** ' . now()->format('d M Y, g:ia') . ' (WAT)')
            ->line('If you made this change, you can ignore this message.')
            ->line('**If you did NOT make this change**, your account may be compromised. Please contact us immediately:')
            ->action('Contact Support', 'mailto:support@genesishub.ng')
            ->salutation('— The ' . config('app.name') . ' Security Team');
    }
}