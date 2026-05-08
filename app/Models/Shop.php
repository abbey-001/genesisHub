<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'shop_name',
        'shop_description',
        'shop_logo',
        'banner',
        'phone_number',
        'email',
        'website',
        'address',
        'delivery_zone',
        'city',
        'state',
        'postal_code',
        'country',
        'is_active',
        'rating',
        'total_products',
        'followers_count',
        'slug',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rating' => 'float',
    ];

    protected static function booted()
    {
        static::creating(function ($shop) {
            if (empty($shop->slug)) {
                $shop->slug = Str::slug($shop->shop_name);
            }
        });

        static::updating(function ($shop) {
            if ($shop->isDirty('shop_name') && empty($shop->slug)) {
                $shop->slug = Str::slug($shop->shop_name);
            }
        });
    }

    // ============================================
    // RELATIONSHIPS
    // ============================================

    /**
     * Get the seller that owns the shop
     */
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Get all products belonging to this shop
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all active products
     */
    public function activeProducts()
    {
        return $this->hasMany(Product::class)->where('is_active', true);
    }

    /**
     * Get all reviews for products in this shop
     * Note: Using a more efficient query to avoid ambiguous column issues
     */
    public function reviews()
    {
        return Review::whereIn('product_id', function($query) {
            $query->select('id')
                  ->from('products')
                  ->where('shop_id', $this->id);
        });
    }

    /**
     * Get approved reviews only
     */
    public function approvedReviews()
    {
        return Review::whereIn('product_id', function($query) {
            $query->select('id')
                  ->from('products')
                  ->where('shop_id', $this->id);
        })
        ->where('is_approved', true)
        ->where('status', 'approved');
    }

    // ============================================
    // RATING & REVIEWS METHODS
    // ============================================

    /**
     * Calculate average rating from all approved reviews
     * FIXED: No more ambiguous column errors
     */
    public function calculateRating()
    {
        // Use a direct query to avoid ambiguous column issues
        $avgRating = DB::table('reviews')
            ->join('products', 'products.id', '=', 'reviews.product_id')
            ->where('products.shop_id', $this->id)
            ->where('reviews.is_approved', 1)
            ->where('reviews.status', 'approved')
            ->avg('reviews.rating');
        
        if ($avgRating !== null) {
            $rounded = round($avgRating, 2);
            // Update the rating field in database
            $this->update(['rating' => $rounded]);
            return $rounded;
        }

        return 0;
    }

    /**
     * Get the average rating (from cached field)
     */
    public function getAverageRating()
    {
        return $this->rating ?? 0;
    }

    /**
     * Get total count of approved reviews
     */
    public function getTotalReviewsCount()
    {
        return DB::table('reviews')
            ->join('products', 'products.id', '=', 'reviews.product_id')
            ->where('products.shop_id', $this->id)
            ->where('reviews.is_approved', 1)
            ->where('reviews.status', 'approved')
            ->count();
    }

    /**
     * Get review distribution by rating
     */
    public function getReviewDistribution()
    {
        // Get review counts grouped by rating
        $reviewCounts = DB::table('reviews')
            ->join('products', 'products.id', '=', 'reviews.product_id')
            ->where('products.shop_id', $this->id)
            ->where('reviews.is_approved', 1)
            ->where('reviews.status', 'approved')
            ->select('reviews.rating', DB::raw('COUNT(*) as count'))
            ->groupBy('reviews.rating')
            ->pluck('count', 'rating');

        $distribution = [];
        $totalReviews = $reviewCounts->sum();
        
        for ($i = 5; $i >= 1; $i--) {
            $count = $reviewCounts->get($i, 0);
            $distribution[$i] = [
                'rating' => $i,
                'count' => $count,
                'percentage' => $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0
            ];
        }

        return $distribution;
    }

    /**
     * Get recent approved reviews with relationships
     */
    public function getRecentReviews($limit = 5)
    {
        $productIds = $this->products()->pluck('id');
        
        return Review::whereIn('product_id', $productIds)
            ->where('is_approved', true)
            ->where('status', 'approved')
            ->with(['user', 'product.images'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    // ============================================
    // STATISTICS METHODS
    // ============================================

    /**
     * Get total sales count
     */
    public function getTotalSales()
    {
        return $this->products()->sum('sold_count');
    }

    /**
     * Get total revenue (if you track this)
     */
    public function getTotalRevenue()
    {
        return DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('products.shop_id', $this->id)
            ->where('orders.status', 'completed')
            ->sum(DB::raw('order_items.quantity * order_items.price'));
    }

    /**
     * Update product count
     */
    public function updateProductCount()
    {
        $count = $this->activeProducts()->count();
        $this->update(['total_products' => $count]);
        return $count;
    }

    /**
     * Get response rate (percentage of reviews with seller responses)
     */
    public function getResponseRate()
    {
        $totalReviews = $this->getTotalReviewsCount();
        
        if ($totalReviews === 0) {
            return 0;
        }

        $responsedReviews = DB::table('reviews')
            ->join('products', 'products.id', '=', 'reviews.product_id')
            ->where('products.shop_id', $this->id)
            ->where('reviews.is_approved', 1)
            ->where('reviews.status', 'approved')
            ->whereNotNull('reviews.seller_response')
            ->count();

        return round(($responsedReviews / $totalReviews) * 100);
    }

    /**
     * Get shop performance metrics
     */
    public function getPerformanceMetrics()
    {
        return [
            'total_products' => $this->activeProducts()->count(),
            'total_reviews' => $this->getTotalReviewsCount(),
            'average_rating' => $this->getAverageRating(),
            'total_sales' => $this->getTotalSales(),
            'response_rate' => $this->getResponseRate(),
            'review_distribution' => $this->getReviewDistribution(),
        ];
    }

    // ============================================
    // QUERY SCOPES
    // ============================================

    /**
     * Scope to only active shops
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to shops with products
     */
    public function scopeHasProducts($query)
    {
        return $query->has('products');
    }

    /**
     * Scope to top rated shops
     */
    public function scopeTopRated($query, $limit = 10)
    {
        return $query->where('rating', '>=', 4.0)
            ->orderByDesc('rating')
            ->limit($limit);
    }

    /**
     * Scope to shops with minimum rating
     */
    public function scopeMinRating($query, $rating)
    {
        return $query->where('rating', '>=', $rating);
    }

    /**
     * Search shops by name or description
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('shop_name', 'like', "%{$term}%")
              ->orWhere('shop_description', 'like', "%{$term}%");
        });
    }

    // ============================================
    // ACCESSORS & HELPERS
    // ============================================

    /**
     * Get shop logo URL
     */
    public function getLogoUrlAttribute()
    {
        if ($this->shop_logo) {
            return asset('public/storage/' . $this->shop_logo);
        }
        
        // Return default logo
        return asset('images/default-shop-logo.png');
    }

    /**
     * Get shop banner URL
     */
    public function getBannerUrlAttribute()
    {
        if ($this->banner) {
            return asset('public/storage/' . $this->banner);
        }
        
        // Return default banner
        return asset('images/default-shop-banner.png');
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get star rating HTML
     */
    public function getStarRatingHtmlAttribute()
    {
        $rating = $this->getAverageRating();
        $html = '';
        
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= floor($rating)) {
                $html .= '<i class="fas fa-star text-warning"></i>';
            } elseif ($i - 0.5 <= $rating) {
                $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
            } else {
                $html .= '<i class="far fa-star text-warning"></i>';
            }
        }
        
        return $html;
    }

    /**
     * Check if shop is verified
     */
    public function isVerified()
    {
        return $this->seller && $this->seller->is_verified;
    }

    /**
     * Get route to shop page
     */
    public function url()
    {
        return route('shop.show', $this->slug ?? $this->id);
    }

    // ============================================
    // MUTATORS
    // ============================================

    /**
     * Set shop name and auto-generate slug
     */
    public function setShopNameAttribute($value)
    {
        $this->attributes['shop_name'] = $value;
        
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }
}