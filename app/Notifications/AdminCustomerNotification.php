<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AdminCustomerNotification extends Notification
{
    use Queueable;

    public string $subject;
    public string $messageBody;
    public string $adminName;

    /**
     * @param string $subject     The notification subject line.
     * @param string $messageBody The plain-text message body from the admin.
     * @param string $adminName   Name of the admin sending the message.
     */
    public function __construct(string $subject, string $messageBody, string $adminName)
    {
        $this->subject     = $subject;
        $this->messageBody = $messageBody;
        $this->adminName   = $adminName;
    }

    /**
     * Deliver via email only.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail message.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->messageBody)
            ->line('---')
            ->line('This message was sent to you by our support team.')
            ->salutation('Regards, ' . $this->adminName . ' — ' . config('app.name') . ' Support');
    }

    /**
     * Array representation (for database channel if ever added).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subject'     => $this->subject,
            'message'     => $this->messageBody,
            'admin_name'  => $this->adminName,
        ];
    }
}