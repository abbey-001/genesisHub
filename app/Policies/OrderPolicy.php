<?php
namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Order $order)
    {
        // Customer can view their own orders
        if ($user->id === $order->user_id) {
            return true;
        }

        // Seller can view if they have items in the order
        if ($user->seller) {
            return $order->items()->where('seller_id', $user->seller->id)->exists();
        }

        // Admin can view all
        return $user->hasRole('admin');
    }

    public function updateItems(User $user, Order $order)
    {
        // Only seller can update their items
        return $user->seller && 
               $order->items()->where('seller_id', $user->seller->id)->exists();
    }
}