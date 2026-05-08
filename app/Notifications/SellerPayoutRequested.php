<?php

namespace App\Notifications;

use App\Models\Payout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the seller when they submit a payout request.
 * This is a seller-facing confirmation (not the admin alert).
 *
 * Dispatch in PayoutController::request() after $payout is returned:
 *
 *   Auth::user()->notify(
 *       new \App\Notifications\SellerPayoutRequested($payout)
 *   );
 *
 * Note: The existing PayoutRequestCreated notification targets the
 * admin/rider context (uses DeliveryPayout + Rider). This new one
 * targets the Seller's own Payout model.
 */
class SellerPayoutRequested extends Notification
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
            ->subject('Payout request received — ₦' . number_format($this->payout->amount, 2))
            ->view('emails.seller.payout-requested', [
                'payout'     => $this->payout->loadMissing('seller.shop'),
                'notifiable' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'seller_payout_requested',
            'payout_id'     => $this->payout->id,
            'amount'        => $this->payout->amount,
            'net_amount'    => $this->payout->net_amount,
            'payout_method' => $this->payout->payout_method,
            'message'       => 'Your payout request of ₦' . number_format($this->payout->amount, 2) . ' has been received.',
        ];
    }
}