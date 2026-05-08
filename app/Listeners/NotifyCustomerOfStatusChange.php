<?php

namespace App\Listeners;

use App\Events\DeliveryStatusUpdated;
use App\Notifications\OrderShipped;
use App\Notifications\DeliveryCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyCustomerOfStatusChange implements ShouldQueue
{
    public function handle(DeliveryStatusUpdated $event)
    {
        $delivery = $event->delivery;
        $customer = $delivery->order->user;

        switch ($event->newStatus) {
            case 'picked_up':
                $customer->notify(new OrderShipped($delivery));
                break;
            
            case 'delivered':
                $customer->notify(new DeliveryCompleted($delivery));
                break;
        }
    }
}