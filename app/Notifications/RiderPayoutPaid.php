<?php

namespace App\Notifications;

use App\Models\DeliveryPayout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the rider when an admin marks their payout as paid
 * (after DeliveryPayout::markAsPaid() runs).
 *
 * Dispatch from your admin payout paid action, after
 * $payout->markAsPaid($admin, $txRef) is called:
 *
 *   $payout->company->user?->notify(new RiderPayoutPaid($payout));
 */
class RiderPayoutPaid extends Notification
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
            ->subject('Payout sent — ₦' . number_format($this->payout->amount, 2) . ' is on its way')
            ->view('emails.rider.payout-paid', [
                'payout'     => $this->payout->loadMissing('company'),
                'notifiable' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'                 => 'rider_payout_paid',
            'payout_id'            => $this->payout->id,
            'reference_number'     => $this->payout->reference_number,
            'amount'               => $this->payout->amount,
            'paid_at'              => $this->payout->paid_at,
            'payment_method'       => $this->payout->payment_method,
            'transaction_reference'=> $this->payout->transaction_reference,
            'message'              => '₦' . number_format($this->payout->amount, 2) . ' has been sent to your bank account.',
        ];
    }
}