<?php

namespace App\Models;

use App\Services\SellerWalletService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'rider_id',
        'seller_id',
        'bundle_id',
        'status',
        'pickup_address',
        'pickup_latitude',
        'pickup_longitude',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
        'package_weight',
        'package_notes',
        'delivery_fee',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
        'paid_to_rider_at',
        'payout_batch_id',
        'failed_at',
        'failure_reason',
        'failure_notes',
        'pickup_photo',
        'delivery_proof',
        'failure_photo',
    ];

    protected $casts = [
        'assigned_at'  => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at'    => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function bundle()
    {
        return $this->belongsTo(DeliveryBundle::class, 'bundle_id');
    }

    public function items()
    {
        return $this->belongsToMany(OrderItem::class, 'delivery_items');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['assigned', 'picked_up']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // ─── Accessors ───────────────────────────────────────────────

    public function getStatusBadgeAttribute()
    {
        return [
            'pending'   => 'secondary',
            'assigned'  => 'info',
            'picked_up' => 'warning',
            'delivered' => 'success',
            'failed'    => 'danger',
        ][$this->status] ?? 'secondary';
    }

    public function getStatusLabelAttribute()
    {
        return [
            'pending'   => 'Pending Assignment',
            'assigned'  => 'Assigned to Rider',
            'picked_up' => 'Picked Up',
            'delivered' => 'Delivered',
            'failed'    => 'Delivery Failed',
        ][$this->status] ?? 'Unknown';
    }

    // ─── State transitions ───────────────────────────────────────

    public function markAsPickedUp(?string $photoPath = null): void
    {
        $this->update([
            'status'       => 'picked_up',
            'picked_up_at' => now(),
            'pickup_photo' => $photoPath,
        ]);
    }

    /**
     * Mark this delivery as delivered.
     *
     * Full lifecycle in one call:
     *   1. Update the Delivery record itself.
     *   2. Mark every linked OrderItem as 'delivered' (idempotent).
     *   3. Credit each item's seller pending balance via SellerWalletService.
     *      Hold timer starts NOW — at actual delivery, not at payment time.
     *   4. Sync the parent Order status from all item statuses.
     *   5. Increment the rider's completed-delivery counter.
     *
     * Wallet failures are logged but do NOT abort the delivery state update —
     * a broken delivery record is unrecoverable without customer support;
     * a missed wallet credit can be corrected by an admin in minutes.
     */
    public function markAsDelivered(?string $proofPhotoPath = null): void
    {
        $this->update([
            'status'         => 'delivered',
            'delivered_at'   => now(),
            'delivery_proof' => $proofPhotoPath,
        ]);

        $this->loadMissing('items');

        /** @var SellerWalletService $walletService */
        $walletService = app(SellerWalletService::class);

        foreach ($this->items as $item) {
            // Idempotent — skip items already marked delivered by a previous call.
            $wasAlreadyDelivered = $item->status === 'delivered';

            // Update the item status first so the idempotency guard in
            // processItemDelivered() doesn't fire on a race condition.
            if (! $wasAlreadyDelivered) {
                $item->update(['status' => 'delivered']);
            }

            // Pass the freshly-reloaded item so processItemDelivered()
            // reads the just-written status from the DB, not a stale
            // in-memory copy that still says 'picked_up'.
            try {
                $walletService->processItemDelivered($item->fresh());
            } catch (\Exception $e) {
                Log::error('Delivery::markAsDelivered — wallet credit failed', [
                    'delivery_id'   => $this->id,
                    'order_item_id' => $item->id,
                    'seller_id'     => $item->seller_id,
                    'error'         => $e->getMessage(),
                ]);
            }
        }

        // Sync the order-level status (delivered / shipped / etc.).
        $this->order->syncStatusFromItems();

        // Update rider stats.
        $this->rider?->incrementCompletedDeliveries();
    }

    public function markAsFailed(string $reason, string $notes, ?string $photoPath = null): void
    {
        $this->update([
            'status'         => 'failed',
            'failed_at'      => now(),
            'failure_reason' => $reason,
            'failure_notes'  => $notes,
            'failure_photo'  => $photoPath,
        ]);

        $this->rider?->incrementFailedDeliveries();
    }
}
