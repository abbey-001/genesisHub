<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DeliveryBroadcastRider extends Pivot
{
    protected $table = 'delivery_broadcast_rider';

    protected $fillable = [
        'delivery_broadcast_id',
        'rider_id',
        'response',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    // 🔥 Clean helper methods

    public function markViewed()
    {
        if (!$this->viewed_at) {
            $this->update(['viewed_at' => now()]);
        }
    }

    public function accept()
    {
        $this->update(['response' => 'accepted']);
    }

    public function reject()
    {
        $this->update(['response' => 'rejected']);
    }

    public function ignore()
    {
        $this->update(['response' => 'ignored']);
    }
}