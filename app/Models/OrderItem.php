<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'product_id', 'seller_id', 'product_name',
        'product_sku', 'quantity', 'price', 'total_price', 'status',
        'variant_options',
        'package_weight', 'package_notes', 'ready_at',
        // ── NEW ──
        'expected_ready_by',  // date: deadline for seller to mark this item ready
        'fulfillment_type',   // snapshot of product fulfillment_type at purchase
    ];

    protected $casts = [
        'price'            => 'decimal:2',
        'total_price'      => 'decimal:2',
        'ready_at'         => 'datetime',
        'expected_ready_by'=> 'date',
        'variant_options'  => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    public function shop()
    {
        return $this->hasOneThrough(
            Shop::class,
            Seller::class,
            'id',
            'seller_id',
            'seller_id',
            'id'
        );
    }

    public function deliveryItems()
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(\App\Models\Review::class);
    }

    /**
     * Human-readable variant summary, e.g. "Size: XL, Color: Red"
     */
    public function getVariantSummaryAttribute(): string
    {
        if (empty($this->variant_options)) {
            return '';
        }

        return collect($this->variant_options)
            ->map(fn($value, $key) => "{$key}: {$value}")
            ->implode(', ');
    }

    /**
     * True when this item requires the buyer to wait longer than an in-stock item.
     */
    public function requiresWaiting(): bool
    {
        return in_array($this->fulfillment_type, ['pre_order', 'made_to_order']);
    }

    /**
     * Human-readable fulfillment label for the seller's order page.
     */
    public function getFulfillmentLabelAttribute(): string
    {
        return match ($this->fulfillment_type) {
            'pre_order'     => 'Pre-Order',
            'made_to_order' => 'Made to Order',
            default         => 'In Stock',
        };
    }

    /**
     * Whether the seller has missed their promised ready-by deadline.
     */
    public function isOverdue(): bool
    {
        if (!$this->expected_ready_by) {
            return false;
        }

        return $this->status !== 'ready_for_pickup'
            && $this->expected_ready_by->isPast();
    }

    /**
     * Days remaining until the seller's deadline. Negative means overdue.
     */
    public function getDaysUntilDeadlineAttribute(): int
    {
        if (!$this->expected_ready_by) {
            return 0;
        }

        return (int) now()->startOfDay()->diffInDays($this->expected_ready_by->startOfDay(), false);
    }
}