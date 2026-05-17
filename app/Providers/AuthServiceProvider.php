<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\Delivery;
use App\Models\Order;
use App\Policies\DeliveryPolicy;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Delivery::class => DeliveryPolicy::class,
        Order::class => OrderPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user, string $ability) {
            if (! $user instanceof Admin) {
                return null;
            }

            $permissionAliases = [
                'deliveries.update' => 'deliveries.manage',
                'deliveries.cancel' => 'deliveries.manage',
                'finance.wallets.view' => 'wallets.view',
                'finance.wallets.adjust' => 'wallets.adjust',
            ];

            $permission = $permissionAliases[$ability] ?? $ability;

            return $user->hasPermission($permission) ? true : null;
        });
    }
}
