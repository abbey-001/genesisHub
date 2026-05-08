<?php

namespace App\Notifications;

use App\Models\Delivery;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliveryPickedUp extends Notification
{
    use Queueable;

    protected $delivery;

    public function __construct(Delivery $delivery)
    {
        $this->delivery = $delivery;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Package Picked Up')
            ->line('Your package has been picked up by the rider.')
            ->line('Order: ' . $this->delivery->order->order_number)
            ->line('Rider: ' . $this->delivery->rider->full_name)
            ->action('Track Delivery', route('orders.track', $this->delivery->order))
            ->line('The package is now on its way to the customer.');
    }

    public function toArray($notifiable)
    {
        return [
            'delivery_id' => $this->delivery->id,
            'order_number' => $this->delivery->order->order_number,
            'rider_name' => $this->delivery->rider->full_name,
            'type' => 'delivery_picked_up'
        ];
    }
}
