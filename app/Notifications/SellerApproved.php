<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SellerApproved extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🎉 Your Seller Application Has Been Approved!')
            ->greeting('Congratulations, ' . $notifiable->name . '!')
            ->line('We are pleased to inform you that your seller application has been reviewed and **approved**.')
            ->line('Your shop is now active and you can start listing products and receiving orders.')
            ->action('Go to Your Seller Dashboard', url('/seller/dashboard'))
            ->line('If you have any questions, feel free to contact our support team.')
            ->salutation('Welcome aboard, ' . config('app.name') . ' Team');
    }
}