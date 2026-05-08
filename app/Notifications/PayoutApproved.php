<?php

namespace App\Notifications;

use App\Models\Payout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the seller when an admin marks their payout as approved /
 * processing.
 *
 * NOTE: The original PayoutApproved used DeliveryPayout (rider model).
 * This version is rebuilt for the seller Payout model. If you need the
 * rider version intact, rename the original to RiderPayoutApproved.
 *
 * Dispatch from your admin payout approval action, e.g.:
 *
 *   $payout->seller->user?->notify(new \App\Notifications\PayoutApproved($payout));
 */
class PayoutApproved extends Notification
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
            ->subject('Payout approved — ₦' . number_format($this->payout->amount, 2) . ' is being processed')
            ->view('emails.seller.payout-approved', [
                'payout'     => $this->payout->loadMissing('seller.shop'),
                'notifiable' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'seller_payout_approved',
            'payout_id'  => $this->payout->id,
            'amount'     => $this->payout->amount,
            'net_amount' => $this->payout->net_amount,
            'status'     => $this->payout->status,
            'message'    => 'Your payout of ₦' . number_format($this->payout->amount, 2) . ' has been approved.',
        ];
    }
}