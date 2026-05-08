<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

/**
 * Custom email verification notification.
 *
 * Key differences from the default Laravel notification:
 *  - NOT queued (no ShouldQueue / Queueable) — sends synchronously so the
 *    email is delivered immediately, even on shared hosting with no queue worker.
 *  - Forces HTTPS on the signed URL so the signature always matches APP_URL
 *    regardless of how the HTTP request arrived at the server.
 *  - 24-hour expiry instead of 60 minutes, giving users enough time to open
 *    their inbox without the link expiring.
 *  - Routes customers to 'verification.verify' and sellers to
 *    'seller.verification.verify' so the correct guard session is used.
 */
class VerifyEmailNotification extends BaseVerifyEmail
{
    /**
     * Build the mail message.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Your Email Address — ' . config('app.name'))
            ->view('emails.auth.verify-email', [
                'user'            => $notifiable,
                'verificationUrl' => $verificationUrl,
            ]);
    }

    /**
     * Generate the signed verification URL.
     *
     * forceScheme('https') ensures the generated URL always starts with
     * https://, so the HMAC signature matches no matter whether the server
     * received the original request over http or https.
     *
     * Customers  → 'verification.verify'         (Fortify / web guard)
     * Sellers    → 'seller.verification.verify'  (seller guard)
     */
    protected function verificationUrl($notifiable): string
    {
        $route = $notifiable->user_type === 'seller'
            ? 'seller.verification.verify'
            : 'verification.verify';

        URL::forceScheme('https');

        return URL::temporarySignedRoute(
            $route,
            Carbon::now()->addHours(Config::get('auth.verification.expire', 24)),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}