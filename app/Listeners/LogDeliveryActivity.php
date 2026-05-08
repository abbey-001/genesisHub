<?php

namespace App\Listeners;

use App\Events\DeliveryStatusUpdated;
use Illuminate\Support\Facades\Log;

class LogDeliveryActivity
{
    public function handle(DeliveryStatusUpdated $event)
    {
        Log::channel('deliveries')->info('Delivery status updated', [
            'delivery_id' => $event->delivery->id,
            'order_number' => $event->delivery->order->order_number,
            'previous_status' => $event->previousStatus,
            'new_status' => $event->newStatus,
            'rider_id' => $event->delivery->rider_id,
            'timestamp' => now(),
        ]);
    }
}