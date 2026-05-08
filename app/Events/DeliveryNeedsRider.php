<?php

// ============================================
// app/Events/DeliveryNeedsRider.php
// ============================================
namespace App\Events;

use App\Models\Delivery;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryNeedsRider implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $delivery;
    public $riders;

    public function __construct(Delivery $delivery, $riders)
    {
        $this->delivery = $delivery;
        $this->riders = $riders;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [];
        
        // Broadcast to each rider's private channel
        foreach ($this->riders as $rider) {
            $channels[] = new PrivateChannel('rider.' . $rider->user_id);
        }
        
        // Also broadcast to general riders channel
        $channels[] = new Channel('riders');
        
        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'delivery.broadcast';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'delivery_id' => $this->delivery->id,
            'order_number' => $this->delivery->order->order_number,
            'delivery_fee' => $this->delivery->delivery_fee,
            'items_count' => $this->delivery->items->count(),
            'pickup_address' => $this->delivery->pickup_address,
            'delivery_address' => $this->delivery->delivery_address,
            'broadcast_id' => $this->delivery->broadcast_id,
            'expires_at' => $this->delivery->broadcast->expires_at ?? null,
            'message' => '🚨 New broadcast delivery! ₦' . number_format($this->delivery->delivery_fee, 0),
        ];
    }
}