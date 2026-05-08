<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AdminSellerNotification extends Notification
{
    use Queueable;

    public string $subject;
    public string $messageBody;
    public string $adminName;

    public function __construct(string $subject, string $messageBody, string $adminName)
    {
        $this->subject     = $subject;
        $this->messageBody = $messageBody;
        $this->adminName   = $adminName;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->messageBody)
            ->line('---')
            ->line('This message was sent to you by our seller support team.')
            ->salutation('Regards, ' . $this->adminName . ' — ' . config('app.name') . ' Support');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subject'    => $this->subject,
            'message'    => $this->messageBody,
            'admin_name' => $this->adminName,
        ];
    }
}