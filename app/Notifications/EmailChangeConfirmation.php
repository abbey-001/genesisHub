<?php

namespace App\Notifications;

use App\Models\EmailChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailChangeConfirmation extends Notification
{
    // No queue — shared hosting
    public function __construct(private EmailChangeRequest $changeRequest) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $confirmUrl = route('account.email.confirm', $this->changeRequest->token);

        return (new MailMessage)
            ->subject('Confirm your new email address — ' . config('app.name'))
            ->greeting('Confirm your email change')
            ->line('We received a request to change the email address on your ' . config('app.name') . ' account.')
            ->line('**Current email:** ' . $this->changeRequest->old_email)
            ->line('**New email:** ' . $this->changeRequest->new_email)
            ->action('Confirm Email Change', $confirmUrl)
            ->line('This link expires in **24 hours**.')
            ->line('If you did not request this change, you can safely ignore this email. Your current email address will remain unchanged.')
            ->salutation('— The ' . config('app.name') . ' Team');
    }
}