<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'product_id',
        'code',
        'description',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'max_uses',
        'used_count',
        'max_uses_per_user',
        'is_active',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'value'              => 'decimal:2',
        'min_order_amount'   => 'decimal:2',
        'max_discount_amount'=> 'decimal:2',
        'is_active'          => 'boolean',
        'starts_at'          => 'datetime',
        'expires_at'         => 'datetime',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    /** Null = applies to ALL products from this seller */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
    }

    public function scopeForSeller($query, int $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }

    // ============================================================
    // VALIDATION / BUSINESS LOGIC
    // ============================================================

    /**
     * Check whether this coupon is valid for a given user & cart subset.
     *
     * @param  int    $userId
     * @param  float  $applicableSubtotal  sum of eligible cart item totals
     * @return array{valid: bool, message: string}
     */
    public function validate(int $userId, float $applicableSubtotal): array
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'This coupon is no longer active.'];
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return ['valid' => false, 'message' => 'This coupon is not yet valid.'];
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return ['valid' => false, 'message' => 'This coupon has expired.'];
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return ['valid' => false, 'message' => 'This coupon has reached its usage limit.'];
        }

        if ($applicableSubtotal < $this->min_order_amount) {
            return [
                'valid'   => false,
                'message' => 'A minimum order of ₦' . number_format($this->min_order_amount, 2) . ' is required.',
            ];
        }

        $userUsageCount = $this->usages()->where('user_id', $userId)->count();
        if ($userUsageCount >= $this->max_uses_per_user) {
            return ['valid' => false, 'message' => 'You have already used this coupon.'];
        }

        return ['valid' => true, 'message' => 'Coupon applied successfully!'];
    }

    /**
     * Calculate the discount amount for the given subtotal.
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'percent') {
            $discount = $subtotal * ($this->value / 100);
            if ($this->max_discount_amount !== null) {
                $discount = min($discount, (float) $this->max_discount_amount);
            }
        } else {
            $discount = min((float) $this->value, $subtotal);
        }

        return round($discount, 2);
    }

    /**
     * Human-readable label, e.g. "20% OFF" or "₦500 OFF".
     */
    public function getDiscountLabelAttribute(): string
    {
        if ($this->type === 'percent') {
            $label = $this->value . '% OFF';
            if ($this->max_discount_amount) {
                $label .= ' (max ₦' . number_format($this->max_discount_amount, 0) . ')';
            }
            return $label;
        }

        return '₦' . number_format($this->value, 2) . ' OFF';
    }

    /**
     * Scope label for display — "All Products" or the product name.
     */
    public function getScopeLabelAttribute(): string
    {
        return $this->product_id ? ($this->product->name ?? 'Specific Product') : 'All My Products';
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /** Generate a random uppercase coupon code. */
    public static function generateCode(int $length = 8): string
    {
        do {
            $code = strtoupper(Str::random($length));
        } while (static::where('code', $code)->exists());

        return $code;
    }
}