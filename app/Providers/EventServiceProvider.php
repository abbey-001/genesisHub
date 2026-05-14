<?php

namespace App\Providers;

use App\Events\DeliveryCreated;
use App\Events\RiderAssignmentFailed;
use App\Events\DeliveryStatusUpdated;
use App\Events\RiderLocationUpdated;
use App\Listeners\AlertAdminOnAssignmentFailure;
use App\Listeners\NotifyRiderOfNewDelivery;
use App\Listeners\NotifyCustomerOfStatusChange;
use App\Listeners\UpdateRiderStatistics;
use App\Listeners\LogDeliveryActivity;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        DeliveryCreated::class => [
            NotifyRiderOfNewDelivery::class,
        ],
        
        DeliveryStatusUpdated::class => [
            NotifyCustomerOfStatusChange::class,
            UpdateRiderStatistics::class,
            LogDeliveryActivity::class,
        ],

        RiderAssignmentFailed::class => [
            AlertAdminOnAssignmentFailure::class,
        ],
    ];

    public function boot()
    {
        //
    }
}
