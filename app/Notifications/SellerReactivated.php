<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SellerReactivated extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('✅ Your Seller Account Has Been Reactivated')
            ->greeting('Good news, ' . $notifiable->name . '!')
            ->line('Your seller account has been **reactivated** and your shop is now live again.')
            ->line('You can log in and resume selling immediately.')
            ->action('Go to Your Seller Dashboard', url('/seller/dashboard'))
            ->line('Thank you for your patience.')
            ->salutation('Regards, ' . config('app.name') . ' Team');
    }
}