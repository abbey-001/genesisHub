<?php

// app/Policies/DeliveryPolicy.php
namespace App\Policies;

use App\Models\Delivery;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DeliveryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->rider !== null;
    }

    public function view(User $user, Delivery $delivery)
    {
        // Rider can view their assigned deliveries
        if ($user->rider && $delivery->rider_id === $user->rider->id) {
            return true;
        }

        // Seller can view deliveries for their items
        if ($user->seller && $delivery->seller_id === $user->seller->id) {
            return true;
        }

        // Admin can view all
        return $user->hasRole('admin');
    }

    public function update(User $user, Delivery $delivery)
    {
        // Only assigned rider can update
        return $user->rider && $delivery->rider_id === $user->rider->id;
    }

    public function accept(User $user, Delivery $delivery)
    {
        // Only available riders can accept pending deliveries
        return $user->rider && 
               $user->rider->status === 'available' && 
               $delivery->status === 'pending' &&
               $user->rider->is_verified &&
               $user->rider->is_active;
    }
}
