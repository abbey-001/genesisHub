<?php

namespace App\Notifications;

use App\Models\Delivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the rider after they accept a broadcast and the delivery
 * (or bundle) is officially assigned to them.
 *
 * Dispatch point: DeliveryController::accept() after DB::commit().
 *
 * For single deliveries:
 *   $delivery->rider->user?->notify(new NewDeliveryAssigned($delivery));
 *
 * For bundles, fire once with the first delivery (it carries the bundle):
 *   $firstDelivery = $bundle->deliveries()->orderBy('id')->first();
 *   $firstDelivery->rider->user?->notify(new NewDeliveryAssigned($firstDelivery));
 */
class NewDeliveryAssigned extends Notification
{
    use Queueable;

    public function __construct(protected Delivery $delivery) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Delivery assigned — #' . $this->delivery->order->order_number)
            ->view('emails.rider.delivery-assigned', [
                'delivery'   => $this->delivery->loadMissing([
                    'order', 'seller.shop', 'items.product',
                    'bundle.deliveries.seller.shop',
                ]),
                'notifiable' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'delivery_assigned',
            'delivery_id'    => $this->delivery->id,
            'order_number'   => $this->delivery->order->order_number,
            'pickup_address' => $this->delivery->pickup_address,
            'delivery_fee'   => $this->delivery->delivery_fee,
            'bundle_id'      => $this->delivery->bundle_id,
            'message'        => 'Delivery #' . $this->delivery->order->order_number . ' has been assigned to you.',
        ];
    }
}