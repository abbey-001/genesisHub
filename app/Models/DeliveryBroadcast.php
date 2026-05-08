<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryBroadcast extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'bundle_id',
        'is_partial',
        'status',
        'accepted_by_rider_id',
        'accepted_at',
        'locked_at',        // set when a rider accepts — no more stops can be added
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'locked_at'   => 'datetime',
        'is_partial'  => 'boolean',
    ];

    // ─── Relationships ───────────────────────────────────────────

    /** Single-delivery broadcasts (zone singletons) */
    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    /** Bundle broadcasts (multi-seller same-zone) */
    public function bundle()
    {
        return $this->belongsTo(DeliveryBundle::class, 'bundle_id');
    }

    public function acceptedBy()
    {
        return $this->belongsTo(Rider::class, 'accepted_by_rider_id');
    }

    /**
     * All riders who have been notified about this broadcast.
     * The pivot tracks their response and when they first viewed it.
     */
    public function riders()
    {
        return $this->belongsToMany(Rider::class, 'delivery_broadcast_rider')
            ->using(DeliveryBroadcastRider::class) // 👈 important
            ->withPivot('response', 'viewed_at')
            ->withTimestamps();
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Find an active, not-yet-locked bundle broadcast for a given bundle.
     * Used by DeliveryService to check whether a growing broadcast still
     * accepts new stops before a rider locks it.
     */
    public function scopeOpenBundle($query, int $bundleId)
    {
        return $query->where('bundle_id', $bundleId)
                     ->where('status', 'active')
                     ->whereNull('locked_at');
    }

    // ─── Accessors ───────────────────────────────────────────────

    /**
     * Whether this broadcast covers multiple sellers (bundle) or just one.
     */
    public function getIsBundleAttribute(): bool
    {
        return $this->bundle_id !== null;
    }

    /**
     * Human-readable summary for the delivery company.
     * Shows all sellers/shops they need to visit in one trip.
     */
    public function getPickupSummaryAttribute(): string
    {
        if ($this->is_bundle) {
            $deliveries = $this->bundle->readyDeliveries()->load('seller.shop');
            $shops = $deliveries->map(fn($d) =>
                ($d->seller->shop->shop_name ?? 'Unknown Shop') .
                ' — ' . ($d->pickup_address ?? 'Address not set')
            )->implode("\n");

            $partial = $this->is_partial ? ' (partial — some sellers not yet ready)' : '';
            return "Bundle pickup from {$this->bundle->pickup_zone}{$partial}:\n{$shops}";
        }

        return $this->delivery->pickup_address ?? 'Pickup address not set';
    }

    // ─── Methods ─────────────────────────────────────────────────

    /**
     * Whether this broadcast has been locked by a rider accepting it.
     * Once locked, no new delivery stops can be appended to it.
     */
    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    /**
     * Lock the broadcast at acceptance time — called inside markAsAccepted().
     * Separated so it can be used independently if needed.
     */
    public function lock(): void
    {
        if (!$this->locked_at) {
            $this->update(['locked_at' => now()]);
        }
    }

    public function markAsAccepted(Rider $company)
    {
        $this->update([
            'status'               => 'accepted',
            'accepted_by_rider_id' => $company->id,
            'accepted_at'          => now(),
            'locked_at'            => $this->locked_at ?? now(), // lock on acceptance
        ]);
        
        $this->riders()->updateExistingPivot($company->id, [
                'response' => 'accepted',
            ]);
        $this->riders()
                ->wherePivot('rider_id', '!=', $company->id)
                ->wherePivot('response', 'pending')
                ->update(['response' => 'ignored']);

        if ($this->is_bundle) {
            // Bundle broadcast — assign every pending delivery in the bundle
            $this->bundle->deliveries()->where('status', 'pending')->update([
                'status'      => 'assigned',
                'rider_id'    => $company->id,
                'assigned_at' => now(),
            ]);
            $this->bundle->update(['status' => 'accepted']);
        } else {
            // Single-delivery broadcast — assign the one delivery directly.
            // Without this branch the broadcast is marked accepted but the
            // Delivery row stays 'pending' with no rider_id, leaving it
            // orphaned and invisible to the rider's active deliveries page.
            $this->delivery->update([
                'status'      => 'assigned',
                'rider_id'    => $company->id,
                'assigned_at' => now(),
            ]);
        }
    }

    public function isAvailable(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        // A locked broadcast has been accepted — it is no longer available.
        if ($this->locked_at !== null) {
            return false;
        }

        if ($this->is_bundle) {
            return in_array($this->bundle->status, ['ready', 'partial', 'growing']);
        }

        return $this->delivery->status === 'pending';
    }
}