<?php

// app/Events/DeliveryCreated.php
namespace App\Events;

use App\Models\Delivery;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $delivery;

    public function __construct(Delivery $delivery)
    {
        $this->delivery = $delivery;
    }

    public function broadcastOn()
    {
        return [
            new Channel('deliveries'),
            new PresenceChannel('rider.' . $this->delivery->rider_id),
        ];
    }

    public function broadcastWith()
    {
        return [
            'delivery_id' => $this->delivery->id,
            'order_number' => $this->delivery->order->order_number,
            'pickup_address' => $this->delivery->pickup_address,
            'delivery_fee' => $this->delivery->delivery_fee,
        ];
    }
}