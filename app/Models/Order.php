<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_postal_code',
        'shipping_country',
        'shipping_zone',
        'subtotal',
        'tax',
        'shipping_fee',
        'discount',
        'total',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'payment_details',
        'notes',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'refund_amount',
        'refund_method',
        'refunded_at',
        // ── NEW: delivery estimate fields ──
        'est_delivery_days_min',
        'est_delivery_days_max',
        'has_preorder_items',
        'slowest_item_name',
    ];

    protected $casts = [
        'subtotal'              => 'decimal:2',
        'tax'                   => 'decimal:2',
        'shipping_fee'          => 'decimal:2',
        'discount'              => 'decimal:2',
        'total'                 => 'decimal:2',
        'paid_at'               => 'datetime',
        'shipped_at'            => 'datetime',
        'delivered_at'          => 'datetime',
        'cancelled_at'          => 'datetime',
        'refunded_at'           => 'datetime',
        'refund_amount'         => 'decimal:2',
        'payment_details'       => 'array',
        'has_preorder_items'    => 'boolean',
        'est_delivery_days_min' => 'integer',
        'est_delivery_days_max' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }
        });
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function deliveryBundles()
    {
        return $this->hasMany(\App\Models\DeliveryBundle::class);
    }

    // =========================================================================
    // DELIVERY ESTIMATE HELPERS
    // =========================================================================

    /**
     * Human-readable estimated delivery range, e.g. "3–5 days" or "Today".
     */
    public function getDeliveryEstimateTextAttribute(): string
    {
        $min = $this->est_delivery_days_min;
        $max = $this->est_delivery_days_max;

        if ($min === null || $max === null) {
            return 'Calculating…';
        }

        if ($max === 0) {
            return 'Today';
        }

        if ($min === $max) {
            return "{$min} day" . ($min === 1 ? '' : 's');
        }

        return "{$min}–{$max} days";
    }

    /**
     * Estimated delivery date range as human text, e.g. "Mon 14 – Wed 16 Jan".
     * Based on paid_at so the window shifts correctly per order.
     */
    public function getDeliveryDateRangeAttribute(): string
    {
        if (!$this->paid_at || $this->est_delivery_days_min === null) {
            return 'Pending';
        }

        $earliest = $this->paid_at->copy()->addDays($this->est_delivery_days_min);
        $latest   = $this->paid_at->copy()->addDays($this->est_delivery_days_max);

        if ($earliest->isSameDay($latest)) {
            return $earliest->format('D d M');
        }

        return $earliest->format('D d') . ' – ' . $latest->format('D d M');
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending'    => 'warning',
            'processing' => 'info',
            'shipped'    => 'primary',
            'delivered'  => 'success',
            'cancelled'  => 'danger',
        ];
        return $badges[$this->status] ?? 'secondary';
    }

    public function getPaymentStatusBadgeAttribute()
    {
        $badges = [
            'pending'         => 'warning',
            'paid'            => 'success',
            'failed'          => 'danger',
            'refunded'        => 'info',
            'refund_rejected' => 'danger',
        ];
        return $badges[$this->payment_status] ?? 'secondary';
    }

    public function getFormattedTotalAttribute()
    {
        return '₦' . number_format($this->total, 2);
    }

    public function getFormattedSubtotalAttribute()
    {
        return '₦' . number_format($this->subtotal, 2);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeForSeller($query, $sellerId)
    {
        return $query->whereHas('items', fn($q) => $q->where('seller_id', $sellerId));
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // =========================================================================
    // STATE CHECKS
    // =========================================================================

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    // =========================================================================
    // MULTI-SELLER HELPERS
    // =========================================================================

    public function allItemsDelivered(): bool
    {
        $this->loadMissing('items');

        if ($this->items->isEmpty()) {
            return false;
        }

        return $this->items->every(fn($item) => $item->status === 'delivered');
    }

    public function hasDeliveredItems(): bool
    {
        return $this->items()->where('status', 'delivered')->exists();
    }

    /**
     * True when EVERY seller across the entire order has marked all their
     * items as ready_for_pickup. This is the trigger for creating delivery
     * records and broadcasting to riders.
     */
    public function allSellersReady(): bool
    {
        $this->loadMissing('items');

        if ($this->items->isEmpty()) {
            return false;
        }

        return $this->items->every(
            fn($item) => in_array($item->status, ['ready_for_pickup', 'picked_up', 'delivered'])
        );
    }

    public function getDeliveryProgressAttribute(): int
    {
        $total = $this->items->count();
        if ($total === 0) {
            return 0;
        }

        $delivered = $this->items->where('status', 'delivered')->count();
        return (int) round(($delivered / $total) * 100);
    }

    public function getItemsBySellerAttribute()
    {
        return $this->items->groupBy('seller_id');
    }

    public function getSellerCountAttribute(): int
    {
        return $this->items->pluck('seller_id')->unique()->count();
    }

    public function syncStatusFromItems(): void
    {
        $this->loadMissing('items');

        if ($this->items->isEmpty()) {
            return;
        }

        $statuses = $this->items->pluck('status')->unique();

        if ($statuses->count() === 1 && $statuses->first() === 'delivered') {
            $this->update(['status' => 'delivered', 'delivered_at' => $this->delivered_at ?? now()]);
        } elseif ($statuses->count() === 1 && $statuses->first() === 'cancelled') {
            $this->update(['status' => 'cancelled', 'cancelled_at' => $this->cancelled_at ?? now()]);
        } elseif ($statuses->contains('shipped') || $statuses->contains('picked_up')) {
            if ($this->status === 'processing') {
                $this->update(['status' => 'shipped', 'shipped_at' => $this->shipped_at ?? now()]);
            }
        } elseif ($this->status === 'delivered' && !$this->allItemsDelivered()) {
            $this->update(['status' => 'shipped']);
        }
    }

    public function getSellerSummary()
    {
        return $this->items->groupBy('seller_id')->map(function ($items) {
            return [
                'seller_id'     => $items->first()->seller_id,
                'seller_name'   => $items->first()->seller->name ?? 'Unknown',
                'item_count'    => $items->count(),
                'total'         => $items->sum('total_price'),
                'statuses'      => $items->pluck('status')->unique()->values(),
                'all_delivered' => $items->every(fn($item) => $item->status === 'delivered'),
                'any_delivered' => $items->contains(fn($item) => $item->status === 'delivered'),
            ];
        });
    }

    public function isMultiSellerOrder(): bool
    {
        return $this->seller_count > 1;
    }

    public function getPendingDeliveriesCountAttribute(): int
    {
        return $this->deliveries()->whereIn('status', ['pending', 'assigned', 'picked_up'])->count();
    }

    public function allDeliveriesCompleted(): bool
    {
        $total = $this->deliveries()->count();

        if ($total === 0) {
            return false;
        }

        return $this->deliveries()->where('status', 'delivered')->count() === $total;
    }
}