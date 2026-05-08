<?php

namespace App\Listeners;

use App\Events\DeliveryStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateRiderStatistics implements ShouldQueue
{
    public function handle(DeliveryStatusUpdated $event)
    {
        $delivery = $event->delivery;
        
        if ($event->newStatus === 'delivered' && $delivery->rider) {
            $delivery->rider->increment('completed_deliveries');
            
            // Update rider status to available if no other active deliveries
            if ($delivery->rider->activeDeliveries()->count() === 0) {
                $delivery->rider->update(['status' => 'available']);
            }
        }
        
        if ($event->newStatus === 'failed' && $delivery->rider) {
            $delivery->rider->increment('failed_deliveries');
            $delivery->rider->update(['status' => 'available']);
        }
    }
}
