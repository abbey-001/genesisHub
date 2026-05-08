<?php

namespace App\Notifications;

use App\Models\DeliveryPayout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the rider (User) when they submit a payout request,
 * confirming receipt before admin reviews it.
 *
 * NOTE: The existing PayoutRequestCreated notification targets the
 * admin (it's the admin alert). This is the rider-facing confirmation.
 *
 * Dispatch wherever riders request payouts (e.g. your rider payout
 * request action), after the DeliveryPayout record is created:
 *
 *   $payout->company->user?->notify(new RiderPayoutRequested($payout));
 */
class RiderPayoutRequested extends Notification
{
    use Queueable;

    public function __construct(protected DeliveryPayout $payout) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payout request received — ' . $this->payout->reference_number)
            ->view('emails.rider.payout-requested', [
                'payout'     => $this->payout->loadMissing('company'),
                'notifiable' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'rider_payout_requested',
            'payout_id'        => $this->payout->id,
            'reference_number' => $this->payout->reference_number,
            'amount'           => $this->payout->amount,
            'deliveries_count' => $this->payout->deliveries_count,
            'message'          => 'Your payout request of ₦' . number_format($this->payout->amount, 2) . ' has been received.',
        ];
    }
}