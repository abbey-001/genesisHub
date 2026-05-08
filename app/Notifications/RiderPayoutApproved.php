<?php

namespace App\Notifications;

use App\Models\DeliveryPayout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the rider when an admin approves their payout request.
 * This is the rider-facing version of PayoutApproved (which now
 * handles the seller Payout model).
 *
 * Dispatch from your admin payout approval action, after
 * $payout->approve($admin) is called:
 *
 *   $payout->company->user?->notify(new RiderPayoutApproved($payout));
 *
 * The existing PayoutApproved on DeliveryPayout::approve() has no
 * notification hook yet — add it there.
 */
class RiderPayoutApproved extends Notification
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
            ->subject('Payout approved — ₦' . number_format($this->payout->amount, 2) . ' is being processed')
            ->view('emails.rider.payout-approved', [
                'payout'     => $this->payout->loadMissing('company'),
                'notifiable' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'rider_payout_approved',
            'payout_id'        => $this->payout->id,
            'reference_number' => $this->payout->reference_number,
            'amount'           => $this->payout->amount,
            'approved_at'      => $this->payout->approved_at,
            'message'          => 'Your payout of ₦' . number_format($this->payout->amount, 2) . ' has been approved.',
        ];
    }
}