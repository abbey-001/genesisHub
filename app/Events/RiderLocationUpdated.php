<?php

namespace App\Events;

use App\Models\Rider;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RiderLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rider;
    public $latitude;
    public $longitude;

    public function __construct(Rider $rider, $latitude, $longitude)
    {
        $this->rider = $rider;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function broadcastOn()
    {
        $channels = [];
        
        // Broadcast to each active delivery
        foreach ($this->rider->activeDeliveries as $delivery) {
            $channels[] = new Channel('delivery.' . $delivery->id);
        }
        
        return $channels;
    }

    public function broadcastWith()
    {
        return [
            'rider_id' => $this->rider->id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'timestamp' => now()->toISOString(),
        ];
    }
}