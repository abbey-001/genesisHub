<?php

namespace App\Notifications;

use App\Models\Delivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the customer when confirmPickup() is called in DeliveryController
 * and the order status transitions to 'shipped'.
 *
 * Dispatch in DeliveryController::confirmPickup() after DB::commit():
 *
 *   $order->user?->notify(new \App\Notifications\OrderShipped($delivery));
 */
class OrderShipped extends Notification
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
            ->subject('Your order is on its way! — #' . $this->delivery->order->order_number)
            ->view('emails.orders.shipped', [
                'delivery' => $this->delivery->loadMissing([
                    'order.items.product',
                    'order.items.seller.shop',
                    'rider',
                ]),
                'notifiable' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'order_shipped',
            'delivery_id'   => $this->delivery->id,
            'order_id'      => $this->delivery->order_id,
            'order_number'  => $this->delivery->order->order_number,
            'message'       => 'Your order #' . $this->delivery->order->order_number . ' is out for delivery.',
        ];
    }
}