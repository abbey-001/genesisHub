<?php

namespace App\Events;

use App\Models\Delivery;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $delivery;
    public $previousStatus;
    public $newStatus;

    public function __construct(Delivery $delivery, $previousStatus, $newStatus)
    {
        $this->delivery = $delivery;
        $this->previousStatus = $previousStatus;
        $this->newStatus = $newStatus;
    }

    public function broadcastOn()
    {
        return [
            new Channel('order.' . $this->delivery->order_id),
            new Channel('seller.' . $this->delivery->seller_id),
        ];
    }

    public function broadcastWith()
    {
        return [
            'delivery_id' => $this->delivery->id,
            'previous_status' => $this->previousStatus,
            'new_status' => $this->newStatus,
            'status_label' => $this->delivery->status_label,
        ];
    }
}