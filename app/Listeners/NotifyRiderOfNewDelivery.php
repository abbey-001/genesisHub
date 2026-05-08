<?php

namespace App\Listeners;

use App\Events\DeliveryCreated;
use App\Notifications\NewDeliveryAssigned;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyRiderOfNewDelivery implements ShouldQueue
{
    public function handle(DeliveryCreated $event)
    {
        if ($event->delivery->rider) {
            $event->delivery->rider->user->notify(
                new NewDeliveryAssigned($event->delivery)
            );
        }
    }
}
