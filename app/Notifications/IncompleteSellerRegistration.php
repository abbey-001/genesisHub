<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncompleteSellerRegistration extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $completionUrl = route('seller.social.complete.form');

        return (new MailMessage)
            ->subject('Complete your seller registration — ' . config('app.name'))
            ->greeting('You\'re almost there, ' . $notifiable->name . '!')
            ->line('You connected your account to ' . config('app.name') . ' but haven\'t finished setting up your shop yet.')
            ->line('It only takes a few minutes to complete your seller profile and start selling to thousands of customers.')
            ->action('Complete Registration', $completionUrl)
            ->line('Your account details have been saved — you just need to add your shop name, business info, and bank details.')
            ->line('If you no longer wish to become a seller on ' . config('app.name') . ', you can simply ignore this email.')
            ->salutation('— The ' . config('app.name') . ' Team');
    }
}