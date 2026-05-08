<?php

namespace App\Notifications;

use App\Models\Delivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the customer when DeliveryController::complete() marks the order delivered.
 *
 * Dispatch in DeliveryController::complete() after DB::commit():
 *
 *   $order->user?->notify(new \App\Notifications\OrderDelivered($delivery));
 */
class OrderDelivered extends Notification
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
            ->subject('Your order has been delivered! — #' . $this->delivery->order->order_number)
            ->view('emails.orders.delivered', [
                'delivery'   => $this->delivery->loadMissing([
                    'order.items.product',
                    'order.items.seller.shop',
                ]),
                'notifiable' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'order_delivered',
            'delivery_id'  => $this->delivery->id,
            'order_id'     => $this->delivery->order_id,
            'order_number' => $this->delivery->order->order_number,
            'delivered_at' => $this->delivery->delivered_at,
            'message'      => 'Your order #' . $this->delivery->order->order_number . ' has been delivered!',
        ];
    }
}