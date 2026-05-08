<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SellerRejected extends Notification
{
    use Queueable;

    public string $reason;

    public function __construct(string $reason = '')
    {
        $this->reason = $reason;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Update on Your Seller Application')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Thank you for your interest in becoming a seller on ' . config('app.name') . '.')
            ->line('After reviewing your application, we are unable to approve it at this time.');

        if ($this->reason) {
            $mail->line('**Reason:** ' . $this->reason);
        }

        return $mail
            ->line('You are welcome to re-apply after addressing the above concerns.')
            ->line('If you believe this decision was made in error, please contact our support team.')
            ->salutation('Regards, ' . config('app.name') . ' Team');
    }
}