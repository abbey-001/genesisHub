<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SellerSuspended extends Notification
{
    use Queueable;

    public string $reason;

    public function __construct(string $reason)
    {
        $this->reason = $reason;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Your Seller Account Has Been Suspended')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We regret to inform you that your seller account has been **suspended**.')
            ->line('**Reason:** ' . $this->reason)
            ->line('As a result of this suspension:')
            ->line('- Your shop has been deactivated and is no longer visible to buyers.')
            ->line('- All your active product listings have been deactivated.')
            ->line('If you believe this action was taken in error or would like to appeal, please contact our support team.')
            ->salutation('Regards, ' . config('app.name') . ' Support Team');
    }
}