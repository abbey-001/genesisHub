<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class AdminProductService
{
    /**
     * Get product statistics
     */
    public function getProductStats()
    {
        return [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'inactive' => Product::where('is_active', false)->count(),
            'featured' => Product::where('is_featured', true)->count(),
            'out_of_stock' => Product::where('stock', '<=', 0)->count(),
            'low_stock' => Product::where('stock', '>', 0)->where('stock', '<=', 10)->count(),
            'total_value' => Product::where('is_active', true)->sum(DB::raw('price * stock')),
        ];
    }

    /**
     * Get trending products
     */
    public function getTrendingProducts($limit = 10)
    {
        return Product::where('is_active', true)
            ->orderBy('sold_count', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts($threshold = 10)
    {
        return Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->where('stock', '<=', $threshold)
            ->orderBy('stock')
            ->get();
    }

    /**
     * Bulk update product status
     */
    public function bulkUpdateStatus(array $productIds, $status)
    {
        DB::beginTransaction();
        try {
            Product::whereIn('id', $productIds)->update([
                'is_active' => $status,
                'updated_at' => now(),
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get product performance analytics
     */
    public function getProductAnalytics(Product $product)
    {
        return [
            'total_orders' => $product->orderItems()->count(),
            'total_sold' => $product->sold_count ?? 0,
            'total_revenue' => ($product->sold_count ?? 0) * $product->price,
            'average_rating' => round($product->rating ?? 0, 2),
            'total_reviews' => $product->reviews()->count(),
            'conversion_rate' => $this->calculateConversionRate($product),
            'stock_turnover' => $this->calculateStockTurnover($product),
        ];
    }

    /**
     * Calculate conversion rate
     */
    protected function calculateConversionRate(Product $product)
    {
        // Views to purchase conversion
        // This is a placeholder - implement based on your tracking
        return 0;
    }

    /**
     * Calculate stock turnover
     */
    protected function calculateStockTurnover(Product $product)
    {
        $soldLast30Days = $product->orderItems()
            ->where('created_at', '>=', now()->subDays(30))
            ->sum('quantity');

        return $soldLast30Days;
    }
}