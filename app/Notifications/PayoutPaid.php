<?php

namespace App\Notifications;

use App\Models\Payout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the seller when the payout is marked completed and funds
 * have been transferred.
 *
 * Dispatch from your admin payout completion action, e.g.:
 *
 *   $payout->seller->user?->notify(new \App\Notifications\PayoutPaid($payout));
 */
class PayoutPaid extends Notification
{
    use Queueable;

    public function __construct(protected Payout $payout) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payout sent — ₦' . number_format($this->payout->net_amount, 2) . ' is on its way')
            ->view('emails.seller.payout-paid', [
                'payout'     => $this->payout->loadMissing('seller.shop'),
                'notifiable' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'                => 'seller_payout_paid',
            'payout_id'           => $this->payout->id,
            'amount'              => $this->payout->amount,
            'net_amount'          => $this->payout->net_amount,
            'payout_method'       => $this->payout->payout_method,
            'transaction_id'      => $this->payout->transaction_id,
            'processed_at'        => $this->payout->processed_at,
            'message'             => '₦' . number_format($this->payout->net_amount, 2) . ' has been sent to your bank account.',
        ];
    }
}