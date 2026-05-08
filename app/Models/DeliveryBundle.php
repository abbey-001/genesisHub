<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\DeliveryBroadcast;

class DeliveryBundle extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'pickup_zone',
        'status',
        'expected_count',
        'ready_count',
        'first_ready_at',
        'broadcast_at',
        'timeout_at',
    ];

    protected $casts = [
        'first_ready_at' => 'datetime',
        'broadcast_at'   => 'datetime',
        'timeout_at'     => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'bundle_id');
    }

    public function broadcast()
    {
        return $this->hasOne(DeliveryBroadcast::class, 'bundle_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    /** Bundles waiting for sellers to be ready AND past their timeout deadline */
    public function scopeTimedOut($query)
    {
        return $query->where('status', 'waiting')
                     ->whereNotNull('timeout_at')
                     ->where('timeout_at', '<=', now());
    }

    // ─── State helpers ───────────────────────────────────────────

    public function isComplete(): bool
    {
        return $this->ready_count >= $this->expected_count;
    }

    public function hasTimedOut(): bool
    {
        return $this->timeout_at && $this->timeout_at->isPast();
    }

    /**
     * A bundle is broadcastable as soon as the first seller is ready
     * (status = 'growing') or all are ready (status = 'ready').
     * The old 'waiting' gate is removed — we no longer hold back the broadcast.
     */
    public function isBroadcastable(): bool
    {
        return $this->ready_count > 0
            && in_array($this->status, ['waiting', 'growing', 'ready']);
    }

    /**
     * Returns the open (unlocked, active) broadcast for this bundle, or null.
     * Used to decide whether to create a new broadcast or append to an existing one.
     */
    public function openBroadcast(): ?DeliveryBroadcast
    {
        return DeliveryBroadcast::openBundle($this->id)->first();
    }

    /**
     * Called when a seller's delivery becomes ready.
     *
     * Uses a raw DB increment so concurrent requests don't clobber each
     * other with a read-then-write (the old `$this->ready_count + 1`).
     * Sets first_ready_at / timeout_at on the very first call only.
     *
     * Returns true when the bundle is now complete and should be broadcast
     * immediately (all expected sellers are ready).
     */
    public function markOneReady(int $timeoutHours = 2): bool
    {
        // Set the timeout clock only on the first seller to become ready.
        // Use a conditional update so two simultaneous "first" calls don't
        // both overwrite first_ready_at with slightly different timestamps.
        DB::table('delivery_bundles')
            ->where('id', $this->id)
            ->whereNull('first_ready_at')   // only the actual first call wins
            ->update([
                'first_ready_at' => now(),
                'timeout_at'     => now()->addHours($timeoutHours),
            ]);

        // Atomic increment — safe under concurrent requests.
        DB::table('delivery_bundles')
            ->where('id', $this->id)
            ->increment('ready_count');

        // Reload from DB so isComplete() sees the true persisted value.
        $this->refresh();

        return $this->isComplete();
    }

    /**
     * Returns the ready deliveries in this bundle (status = pending means
     * the Delivery record exists and the seller is ready for pickup).
     */
    public function readyDeliveries()
    {
        return $this->deliveries()->where('status', 'pending')->get();
    }
}