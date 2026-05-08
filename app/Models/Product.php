<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

/**
 * Product model — Algolia Scout integration + fulfillment types.
 *
 * Fulfillment types:
 *   in_stock      — item is on the shelf; platform enforces IN_STOCK_MAX_DAYS deadline.
 *   pre_order     — item will exist; seller sets max_ready_days promise.
 *   made_to_order — item is crafted per order; seller sets max_ready_days promise.
 *
 * Scout changes from the Typesense version:
 *   - searchableAs() now includes the scout prefix (required by Algolia replica naming)
 *   - toSearchableArray() includes fulfillment_type + max_ready_days so search result
 *     views can show fulfillment badges without an extra DB query
 *   - Algolia maps the model's primary key (id) to objectID automatically
 *   - shouldBeSearchable() and makeAllSearchableUsing() are unchanged
 */
class Product extends Model
{
    use HasFactory, Searchable;

    // ── Platform constant ─────────────────────────────────────────────────────
    const IN_STOCK_MAX_DAYS = 2;

    // ── Transit buffer ────────────────────────────────────────────────────────
    const TRANSIT_DAYS = 1;

    protected $fillable = [
        'name', 'slug', 'short_description', 'description',
        'price', 'sale_price', 'stock', 'is_active', 'is_featured',
        'category_id', 'subcategory_id', 'brand_id', 'shop_id',
        'rating', 'review_count', 'sold_count',
        'tags', 'search_keywords', 'specifications', 'use_cases',
        'target_audience', 'search_vector',
        'meta_title', 'meta_description', 'model_number', 'condition',
        'fulfillment_type', 'max_ready_days',
    ];

    protected $casts = [
        'sale_price'     => 'decimal:2',
        'price'          => 'decimal:2',
        'is_active'      => 'boolean',
        'is_featured'    => 'boolean',
        'rating'         => 'decimal:1',
        'specifications' => 'array',
        'max_ready_days' => 'integer',
    ];

    // =========================================================================
    // ALGOLIA SCOUT INTEGRATION
    // =========================================================================

    /**
     * Algolia index name.
     * Includes the scout prefix from config so replica index names stay consistent.
     * e.g. prefix '' → 'products', prefix 'dev_' → 'dev_products'
     */
    public function searchableAs(): string
    {
        return config('scout.prefix', '') . 'products';
    }

    /**
     * The document indexed in Algolia.
     *
     * Design decisions:
     *  - effective_price pre-computed  → fast numeric range filtering
     *  - popularity_score pre-computed → used in Algolia customRanking
     *  - brand_name, category_name denormalised → no joins at query time
     *  - fulfillment_type + max_ready_days included → badges in results without extra DB hit
     *  - image_url denormalised → display without extra DB call
     *
     * Algolia maps the model's getKey() (id) to objectID automatically.
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing(['brand', 'category', 'subcategory', 'images']);

        $effectivePrice = ($this->sale_price && $this->sale_price > 0 && $this->sale_price < $this->price)
            ? (float) $this->sale_price
            : (float) $this->price;

        $popularityScore = (float)(
            ($this->sold_count    ?? 0) * 0.5 +
            ($this->rating        ?? 0) * 10  +
            ($this->review_count  ?? 0) * 0.1 +
            ($this->is_featured   ? 50 : 0)
        );

        $primaryImage = $this->images->firstWhere('is_primary', true) ?? $this->images->first();
        $imageUrl = $primaryImage
            ? asset('storage/' . $primaryImage->image_path)
            : asset('img/default-product.jpg');

        return [
            // Core
            'id'               => (string) $this->id,
            'name'             => (string) $this->name,
            'slug'             => (string) $this->slug,

            // Searchable text
            'short_description'=> (string) ($this->short_description ?? ''),
            'tags'             => (string) ($this->tags              ?? ''),
            'search_keywords'  => (string) ($this->search_keywords   ?? ''),
            'model_number'     => (string) ($this->model_number      ?? ''),
            'brand_name'       => (string) ($this->brand?->name      ?? ''),
            'category_name'    => (string) ($this->category?->name   ?? ''),
            'subcategory_name' => (string) ($this->subcategory?->name ?? ''),

            // Pricing
            'price'            => (float) $this->price,
            'sale_price'       => $this->sale_price ? (float) $this->sale_price : null,
            'effective_price'  => $effectivePrice,

            // Inventory & social proof
            'stock'            => (int)   ($this->stock        ?? 0),
            'rating'           => (float) ($this->rating       ?? 0),
            'review_count'     => (int)   ($this->review_count ?? 0),
            'sold_count'       => (int)   ($this->sold_count   ?? 0),

            // Boolean facets
            'is_active'        => (bool) $this->is_active,
            'is_featured'      => (bool) $this->is_featured,
            'in_stock'         => (bool) ($this->stock > 0),
            'on_sale'          => (bool) ($this->sale_price && $this->sale_price < $this->price),

            // Facet keys (filterable slugs / IDs)
            'category_id'      => $this->category_id    ? (string) $this->category_id    : null,
            'category_slug'    => $this->category?->slug  ?? null,
            'subcategory_id'   => $this->subcategory_id  ? (string) $this->subcategory_id : null,
            'brand_id'         => $this->brand_id        ? (string) $this->brand_id        : null,
            'brand_slug'       => $this->brand?->slug    ?? null,
            'shop_id'          => $this->shop_id         ? (string) $this->shop_id         : null,
            'condition'        => $this->condition        ?? null,
            'target_audience'  => $this->target_audience ?? null,

            // Fulfillment — lets search results render badges / filter by type
            'fulfillment_type' => $this->fulfillment_type ?? 'in_stock',
            'max_ready_days'   => $this->getMaxReadyDays(),

            // Sort / rank
            'created_at_ts'    => (int) ($this->created_at?->timestamp ?? 0),
            'popularity_score' => $popularityScore,

            // Display only (not searched)
            'image_url'        => $imageUrl,
        ];
    }

    /**
     * Eager-load relations when Scout runs php artisan scout:import.
     * Prevents N+1 queries during bulk indexing.
     */
    public function makeAllSearchableUsing($query)
    {
        return $query->with(['brand', 'category', 'subcategory', 'images']);
    }

    /**
     * Only active products are indexed.
     * Setting is_active = false automatically removes the product from Algolia.
     */
    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_active;
    }

    // =========================================================================
    // BOOT
    // =========================================================================

    protected static function boot()
    {
        parent::boot();

        static::saving(function (Product $product) {
            $product->search_vector = $product->buildSearchVector();
        });
    }

    public function buildSearchVector(): string
    {
        $parts = [];
        if ($this->name)              $parts[] = str_repeat($this->name . ' ', 3);
        if ($this->brand_id) {
            $brand = Brand::find($this->brand_id);
            if ($brand) $parts[] = $brand->name;
        }
        if ($this->category_id) {
            $cat = Category::find($this->category_id);
            if ($cat) $parts[] = $cat->name;
        }
        if ($this->subcategory_id) {
            $sub = Subcategory::find($this->subcategory_id);
            if ($sub) $parts[] = $sub->name;
        }
        if ($this->tags)              $parts[] = str_repeat($this->tags . ' ', 2);
        if ($this->search_keywords)   $parts[] = str_repeat($this->search_keywords . ' ', 2);
        if ($this->short_description) $parts[] = $this->short_description;
        if ($this->description)       $parts[] = strip_tags($this->description);
        if ($this->use_cases)         $parts[] = $this->use_cases;
        if ($this->target_audience)   $parts[] = $this->target_audience;
        if ($this->model_number)      $parts[] = $this->model_number;
        if ($this->specifications && is_array($this->specifications)) {
            foreach ($this->specifications as $k => $v) $parts[] = $k . ' ' . $v;
        }
        if ($this->variants && is_array($this->variants)) {
            foreach ($this->variants as $group => $values) {
                if (is_array($values)) $parts[] = $group . ' ' . implode(' ', $values);
            }
        }
        if ($this->condition) $parts[] = $this->condition;
        return implode(' ', $parts);
    }

    // =========================================================================
    // FULFILLMENT HELPERS
    // =========================================================================

    public function isInStock(): bool
    {
        return $this->fulfillment_type === 'in_stock';
    }

    public function isPreOrder(): bool
    {
        return $this->fulfillment_type === 'pre_order';
    }

    public function isMadeToOrder(): bool
    {
        return $this->fulfillment_type === 'made_to_order';
    }

    public function requiresWaiting(): bool
    {
        return $this->isPreOrder() || $this->isMadeToOrder();
    }

    public function getMaxReadyDays(): int
    {
        if ($this->isInStock()) {
            return self::IN_STOCK_MAX_DAYS;
        }

        return $this->max_ready_days ?? self::IN_STOCK_MAX_DAYS;
    }

    public function getFulfillmentLabelAttribute(): string
    {
        return match ($this->fulfillment_type) {
            'pre_order'     => 'Pre-Order',
            'made_to_order' => 'Made to Order',
            default         => 'In Stock',
        };
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActive($query)        { return $query->where('is_active', true); }
    public function scopeFeatured($query)       { return $query->where('is_featured', true); }
    public function scopeInStock($query)        { return $query->where('stock', '>', 0); }
    public function scopeForAudience($query, string $audience) { return $query->where('target_audience', $audience); }
    public function scopeCondition($query, string $condition)  { return $query->where('condition', $condition); }
    public function scopeWithTag($query, string $tag) {
        return $query->whereRaw('FIND_IN_SET(?, REPLACE(tags, ", ", ","))', [trim($tag)]);
    }

    public function scopeSearch($query, string $term)
    {
        if (empty(trim($term))) return $query;
        $sanitised = $this->sanitiseSearchTerm($term);
        return $query->whereRaw('MATCH(search_vector) AGAINST(? IN BOOLEAN MODE)', [$sanitised])
                     ->orderByRaw('MATCH(search_vector) AGAINST(? IN BOOLEAN MODE) DESC', [$sanitised]);
    }

    public function scopeSearchWithScore($query, string $term)
    {
        if (empty(trim($term))) return $query->selectRaw('products.*, 0 as relevance_score');
        $sanitised = $this->sanitiseSearchTerm($term);
        $plain     = addslashes(trim($term));
        return $query->selectRaw("products.*,
            (MATCH(search_vector) AGAINST(? IN BOOLEAN MODE) * 10 +
             MATCH(name, short_description) AGAINST(? IN BOOLEAN MODE) * 5 +
             IF(name LIKE ?, 20, 0) +
             IF(tags LIKE ?, 8, 0) +
             IF(model_number = ?, 30, 0)) AS relevance_score",
            [$sanitised, $sanitised, "%{$plain}%", "%{$plain}%", trim($term)])
            ->orderByDesc('relevance_score');
    }

    public function sanitiseSearchTerm(string $term): string
    {
        $term  = preg_replace('/[+\-><\(\)~*"@]+/', ' ', $term);
        $words = array_filter(explode(' ', trim($term)));
        if (empty($words)) return '';
        return implode(' ', array_map(fn($w) => '+' . $w . '*', $words));
    }

    public function getTagsArrayAttribute(): array
    {
        if (!$this->tags) return [];
        return array_map('trim', explode(',', $this->tags));
    }
    public function getVariantsArrayAttribute(): array        { return $this->variants       ?? []; }
    public function getSpecificationsArrayAttribute(): array  { return $this->specifications ?? []; }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function category()        { return $this->belongsTo(Category::class); }
    public function subcategory()     { return $this->belongsTo(Subcategory::class); }
    public function brand()           { return $this->belongsTo(Brand::class); }
    public function shop()            { return $this->belongsTo(Shop::class); }
    public function seller()          { return $this->belongsTo(Shop::class, 'shop_id'); }
    public function images()          { return $this->hasMany(ProductImage::class); }
    public function orderItems()      { return $this->hasMany(OrderItem::class); }
    public function reviews()         { return $this->hasMany(Review::class); }
    public function approvedReviews() { return $this->hasMany(Review::class)->approved()->latest(); }
    public function pendingReviews()  { return $this->hasMany(Review::class)->pending()->latest(); }
    public function variants()        { return $this->hasMany(ProductVariant::class); }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getMainImageAttribute()
    {
        return $this->images()->where('is_primary', true)->first()?->image_path
            ?? $this->images()->first()?->image_path
            ?? 'img/default-product.jpg';
    }

    public function getDiscountPercentageAttribute()
    {
        if (!$this->sale_price || $this->sale_price >= $this->price) return null;
        return round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    // =========================================================================
    // REVIEW METHODS
    // =========================================================================

    public function recalculateRating()
    {
        $stats = $this->approvedReviews()
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as review_count')
            ->first();
        $this->update(['rating' => round($stats->avg_rating ?? 0, 1), 'review_count' => $stats->review_count ?? 0]);
        return $this;
    }

    public function getRatingBreakdown()
    {
        $breakdown = $this->approvedReviews()->reorder()
            ->selectRaw('rating, COUNT(*) as count')->groupBy('rating')
            ->get()->pluck('count', 'rating')->toArray();
        for ($i = 1; $i <= 5; $i++) { if (!isset($breakdown[$i])) $breakdown[$i] = 0; }
        krsort($breakdown);
        return $breakdown;
    }

    public function getAverageRating()    { return $this->rating ?? 0; }
    public function getTotalReviewCount() { return $this->review_count ?? 0; }

    public function getFiveStarPercentage()
    {
        if ($this->review_count == 0) return 0;
        return round(($this->approvedReviews()->where('rating', 5)->count() / $this->review_count) * 100, 1);
    }

    public function canBeReviewedBy($userId)
    {
        return OrderItem::whereHas('order', fn($q) => $q->where('user_id', $userId)
            ->where('status', 'delivered')
            ->where('updated_at', '>=', now()->subDays(config('reviews.review_window_days', 90))))
            ->where('product_id', $this->id)
            ->whereDoesntHave('reviews', fn($q) => $q->where('user_id', $userId))
            ->exists();
    }

    public function getEligibleOrderItemForUser($userId)
    {
        return OrderItem::whereHas('order', fn($q) => $q->where('user_id', $userId)
            ->where('status', 'delivered')
            ->where('updated_at', '>=', now()->subDays(config('reviews.review_window_days', 90))))
            ->where('product_id', $this->id)
            ->whereDoesntHave('reviews', fn($q) => $q->where('user_id', $userId))
            ->with('order')->first();
    }

    public function getRecentReviews($limit = 10)
    {
        return $this->approvedReviews()->with(['user', 'images'])->take($limit)->get();
    }

    public function getMostHelpfulReviews($limit = 5)
    {
        return $this->approvedReviews()->with(['user', 'images'])->orderByDesc('helpful_count')->take($limit)->get();
    }
}