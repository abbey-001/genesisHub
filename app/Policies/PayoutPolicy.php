<?php

namespace App\Policies;

use App\Models\Payout;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PayoutPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view the payout
     */
    public function view(User $user, Payout $payout)
    {
        // User must own this payout through their seller account
        return $user->seller && $user->seller->id === $payout->seller_id;
    }

    /**
     * Determine if the user can view any payouts
     */
    public function viewAny(User $user)
    {
        // User must have a seller account
        return $user->seller !== null;
    }

    /**
     * Determine if the user can cancel the payout
     */
    public function cancel(User $user, Payout $payout)
    {
        // Must own the payout
        if (!$user->seller || $user->seller->id !== $payout->seller_id) {
            return false;
        }

        // Can only cancel pending payouts
        return $payout->status === 'pending';
    }

    /**
     * Determine if the user can create a payout
     */
    public function create(User $user)
    {
        // User must have a seller account
        if (!$user->seller) {
            return false;
        }

        // Seller must be verified
        if (!$user->seller->is_verified) {
            return false;
        }

        return true;
    }
}