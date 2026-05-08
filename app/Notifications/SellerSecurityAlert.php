<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerSecurityAlert extends Notification
{
    /**
     * @param string $eventType  e.g. 'seller_profile_changed'
     * @param array  $changes    [['field' => 'Bank Account Number', 'old_value' => '****1234', 'new_value' => '****5678']]
     */
    public function __construct(
        private string $eventType,
        private array  $changes
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Security alert: Seller account details changed — ' . config('app.name'))
            ->greeting('Account changes detected')
            ->line('The following details were changed on your ' . config('app.name') . ' seller account:');

        foreach ($this->changes as $change) {
            $message->line('**' . $change['field'] . ':** ' . $change['old_value'] . ' → ' . $change['new_value']);
        }

        $message
            ->line('**Changed at:** ' . now()->format('d M Y, g:ia') . ' (WAT)')
            ->line('If you made these changes, you can ignore this message.')
            ->line('**If you did NOT make these changes**, please contact us immediately to secure your account and protect your payouts.')
            ->action('Contact Support', 'mailto:support@genesishub.ng')
            ->salutation('— The ' . config('app.name') . ' Security Team');

        return $message;
    }
}