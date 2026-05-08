<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

class WarmUpCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warmup {--clear : Clear existing cache before warming up}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm up the application cache by pre-loading frequently accessed data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔥 Starting cache warm-up...');

        // Clear cache if flag is set
        if ($this->option('clear')) {
            $this->warn('Clearing existing cache...');
            Cache::flush();
            $this->info('✓ Cache cleared');
        }

        $this->newLine();
        $startTime = microtime(true);

        // Warm up categories
        $this->warmupCategories();
        
        // Warm up brands
        $this->warmupBrands();
        
        // Warm up products
        $this->warmupProducts();
        
        // Warm up homepage data
        $this->warmupHomepage();

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->newLine();
        $this->info("✅ Cache warm-up completed in {$duration} seconds!");
    }

    /**
     * Warm up categories cache
     */
    protected function warmupCategories()
    {
        $this->task('Warming up categories', function () {
            Cache::remember('featured_categories', 3600, function () {
                return Category::select('id', 'name', 'slug', 'image')
                    ->where('is_featured', true)
                    ->withCount('products')
                    ->orderBy('name')
                    ->limit(10)
                    ->get();
            });

            Cache::remember('categories_with_subs', 3600, function () {
                return Category::select('id', 'name', 'slug', 'image')
                    ->with(['subcategories' => fn($q) => 
                        $q->select('id', 'category_id', 'name', 'slug')
                          ->where('is_active', true)
                          ->orderBy('sort_order')
                          ->limit(10)
                    ])
                    ->limit(10)
                    ->get();
            });

            return true;
        });
    }

    /**
     * Warm up brands cache
     */
    protected function warmupBrands()
    {
        $this->task('Warming up brands', function () {
            Cache::remember('popular_brands', 3600, function () {
                return Brand::select('id', 'name', 'slug', 'logo')
                    ->withCount('products')
                    ->having('products_count', '>', 0)
                    ->orderBy('products_count', 'desc')
                    ->limit(10)
                    ->get();
            });

            return true;
        });
    }

    /**
     * Warm up products cache
     */
    protected function warmupProducts()
    {
        $this->task('Warming up products', function () {
            // Best sellers
            Cache::remember('best_sellers', 1800, function () {
                return Product::select('id', 'name', 'slug', 'price', 'sale_price', 'stock', 'brand_id')
                    ->with([
                        'images' => fn($q) => $q->select('id', 'product_id', 'image_path')->where('is_primary', true)->limit(1),
                        'brand:id,name'
                    ])
                    ->where('is_active', true)
                    ->where('stock', '>', 0)
                    ->orderBy('sold_count', 'desc')
                    ->limit(12)
                    ->get();
            });

            // Featured products
            Cache::remember('featured_products', 1800, function () {
                return Product::select('id', 'name', 'slug', 'price', 'sale_price', 'stock', 'brand_id')
                    ->with([
                        'images' => fn($q) => $q->select('id', 'product_id', 'image_path')->where('is_primary', true)->limit(1),
                        'brand:id,name'
                    ])
                    ->where('is_active', true)
                    ->where('is_featured', true)
                    ->where('stock', '>', 0)
                    ->orderBy('created_at', 'desc')
                    ->limit(12)
                    ->get();
            });

            // New arrivals
            Cache::remember('new_arrivals', 1800, function () {
                return Product::select('id', 'name', 'slug', 'price', 'sale_price', 'stock', 'brand_id', 'created_at')
                    ->with([
                        'images' => fn($q) => $q->select('id', 'product_id', 'image_path')->where('is_primary', true)->limit(1),
                        'brand:id,name'
                    ])
                    ->where('is_active', true)
                    ->where('stock', '>', 0)
                    ->orderBy('created_at', 'desc')
                    ->limit(12)
                    ->get();
            });

            return true;
        });
    }

    /**
     * Warm up homepage data
     */
    protected function warmupHomepage()
    {
        $this->task('Warming up homepage', function () {
            Cache::remember('homepage_data', 3600, function () {
                return [
                    'featuredCategories' => Category::select('id', 'name', 'slug', 'image')
                        ->where('is_featured', true)
                        ->withCount('products')
                        ->orderBy('name')
                        ->limit(10)
                        ->get(),

                    'popularBrands' => Brand::select('id', 'name', 'slug', 'logo')
                        ->withCount('products')
                        ->having('products_count', '>', 0)
                        ->orderBy('products_count', 'desc')
                        ->limit(10)
                        ->get(),

                    'bestSellers' => Product::select('id', 'name', 'slug', 'price', 'sale_price', 'stock', 'brand_id')
                        ->with([
                            'images' => fn($q) => $q->select('id', 'product_id', 'image_path')->where('is_primary', true)->limit(1),
                            'brand:id,name'
                        ])
                        ->where('is_active', true)
                        ->where('stock', '>', 0)
                        ->orderBy('sold_count', 'desc')
                        ->limit(12)
                        ->get(),
                ];
            });

            return true;
        });
    }
}