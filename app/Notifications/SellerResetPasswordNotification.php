<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class SellerResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = route('seller.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Reset your seller password - ' . config('app.name'))
            ->line('We received a request to reset the password for your seller account.')
            ->action('Reset Seller Password', $url)
            ->line('This password reset link will expire in ' . config('auth.passwords.sellers.expire', 60) . ' minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}
