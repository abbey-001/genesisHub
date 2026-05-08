<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    // Columns needed by the product-card partial every time
    private const PRODUCT_CARD_SELECT = [
        'id', 'name', 'slug', 'short_description',
        'price', 'sale_price', 'stock',
        'brand_id', 'shop_id', 'category_id',
        'rating', 'review_count', 'sold_count',
        'is_featured', 'is_active', 'condition',
        'created_at',
    ];

    // Relations needed by the product-card partial every time
    private function productCardWith(): array
    {
        return [
            'images'       => fn ($q) => $q->select('id', 'product_id', 'image_path')
                                           ->where('is_primary', true)->limit(1),
            'brand:id,name,slug',
            'shop:id,seller_id,shop_name,slug,is_active',
            'shop.seller:id,business_type,verification_status',
        ];
    }

    /**
     * Display the homepage with optimized queries
     */
    public function index()
    {
        $homeData = Cache::remember('homepage_data', 3600, function () {
            return [
                'featuredCategories' => Category::select('id', 'name', 'slug', 'image')
                    ->where('is_featured', true)
                    ->withCount('products')
                    ->orderBy('name')
                    ->limit(10)
                    ->get(),

                'topShops' => Seller::select('id')
                    ->with('shop:seller_id,shop_name,shop_logo,slug,email,is_active')
                    ->withCount('products')
                    ->having('products_count', '>', 0)
                    ->whereHas('shop', fn ($q) => $q->where('is_active', true))
                    ->orderBy('products_count', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(fn ($seller) => (object)[
                        'id'             => $seller->id,
                        'name'           => $seller->shop->shop_name,
                        'slug'           => $seller->shop->slug,
                        'logo'           => $seller->shop->shop_logo,
                        'email'          => $seller->shop->email,
                        'products_count' => $seller->products_count,
                    ]),

                'bestSellers' => Product::select(self::PRODUCT_CARD_SELECT)
                    ->with($this->productCardWith())
                    ->active()->inStock()
                    ->orderBy('sold_count', 'desc')
                    ->limit(12)->get(),

                'featuredProducts' => Product::select(self::PRODUCT_CARD_SELECT)
                    ->with($this->productCardWith())
                    ->active()->featured()->inStock()
                    ->orderBy('created_at', 'desc')
                    ->limit(12)->get(),

                'newArrivals' => Product::select(self::PRODUCT_CARD_SELECT)
                    ->with($this->productCardWith())
                    ->active()->inStock()
                    ->orderBy('created_at', 'desc')
                    ->limit(12)->get(),

                'categoriesWithSubs' => Category::select('id', 'name', 'slug', 'image')
                    ->with(['subcategories' => fn ($q) =>
                        $q->select('id', 'category_id', 'name', 'slug')
                          ->orderBy('sort_order')
                          ->limit(10)
                    ])
                    ->limit(10)->get(),

                'electronicsProducts'  => $this->getProductsByCategory('Electronics', 6),
                'furnitureProducts'    => $this->getProductsByCategory('Furniture', 6),
                'healthBeautyProducts' => $this->getProductsByCategory('Health & Beauty', 6),
                'clothingProducts'     => $this->getProductsByCategory('Clothing', 6),
            ];
        });

        $homeData['recentlyViewed'] = $this->getRecentlyViewedProducts();

        return view('home', $homeData);
    }

    /**
     * Display a seller's shop page with their products
     */
    public function show(string $slugOrId)
    {
        $seller = Seller::whereHas('shop', fn ($q) => $q->where('slug', $slugOrId))
            ->orWhere('id', $slugOrId)
            ->with('shop')
            ->firstOrFail();

        $products = Product::select(self::PRODUCT_CARD_SELECT)
            ->with($this->productCardWith())
            ->where('shop_id', $seller->shop->id)
            ->active()
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $relatedSellers = Seller::select('id')
            ->with('shop:id,seller_id,shop_name,shop_logo,slug')
            ->where('id', '!=', $seller->id)
            ->whereHas('shop', fn ($q) => $q->where('is_active', true))
            ->withCount('products')
            ->orderBy('products_count', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($s) => (object)[
                'id'             => $s->id,
                'name'           => $s->shop->shop_name,
                'slug'           => $s->shop->slug,
                'logo'           => $s->shop->shop_logo,
                'products_count' => $s->products_count,
            ]);

        return view('seller.shop', compact('seller', 'products', 'relatedSellers'));
    }

    /**
     * Display all sellers/shops with pagination
     */
    public function indexSellers(Request $request)
    {
        $query = Seller::select('id', 'created_at')
            ->with('shop:id,seller_id,shop_name,slug,shop_logo,shop_description,email,is_active')
            ->withCount('products')
            ->whereHas('shop', fn ($q) => $q->where('is_active', true));

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('shop', fn ($q) =>
                $q->where('shop_name', 'like', "%{$search}%")
                  ->orWhere('shop_description', 'like', "%{$search}%")
            );
        }

        $sort = $request->input('sort', 'popular');
        match ($sort) {
            'newest'   => $query->orderBy('created_at', 'desc'),
            'products' => $query->orderBy('products_count', 'desc'),
            default    => $query->orderBy('products_count', 'desc'),
        };

        $sellers = $query->paginate(12);

        $categoriesWithSubs = Category::select('id', 'name', 'slug', 'image')
            ->with(['subcategories' => fn ($q) =>
                $q->select('id', 'category_id', 'name', 'slug')
                  ->orderBy('sort_order')->limit(10)
            ])
            ->limit(10)->get();

        return view('seller.index', compact('sellers', 'categoriesWithSubs'));
    }

    /**
     * Get products by category name
     */
    private function getProductsByCategory(string $categoryName, int $limit = 6)
    {
        return Cache::remember("category_{$categoryName}_products", 1800, function () use ($categoryName, $limit) {
            $category = Category::where('name', 'like', "%{$categoryName}%")->first();

            if (!$category) {
                return collect([]);
            }

            return Product::select(self::PRODUCT_CARD_SELECT)
                ->with($this->productCardWith())
                ->where('category_id', $category->id)
                ->active()->inStock()
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get recently viewed products from session
     */
    private function getRecentlyViewedProducts()
    {
        $productIds = session()->get('recently_viewed', []);

        if (empty($productIds)) {
            return collect([]);
        }

        $productIds = array_slice($productIds, 0, 12);

        return Product::select(self::PRODUCT_CARD_SELECT)
            ->with($this->productCardWith())
            ->whereIn('id', $productIds)
            ->active()->inStock()
            ->orderByRaw('FIELD(id, ' . implode(',', $productIds) . ')')
            ->get();
    }

    /**
     * Search products (autocomplete)
     */
    public function search(Request $request)
    {
        $query = $request->input('search', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $cacheKey = 'search_' . md5($query);

        $results = Cache::remember($cacheKey, 600, function () use ($query) {
            return Product::select('id', 'name', 'slug', 'price', 'sale_price')
                ->with(['images' => fn ($q) => $q->select('id', 'product_id', 'image_path')
                    ->where('is_primary', true)->limit(1)])
                ->where(fn ($q) => $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%"))
                ->active()->inStock()
                ->limit(5)
                ->get()
                ->map(fn ($product) => [
                    'id'             => $product->id,
                    'name'           => $product->name,
                    'slug'           => $product->slug,
                    'price'          => $product->sale_price ?? $product->price,
                    'original_price' => $product->price,
                    'image'          => $product->main_image,
                    'url'            => route('product.show', $product->slug),
                ]);
        });

        return response()->json($results);
    }

    /**
     * Clear homepage cache
     */
    public function clearCache()
    {
        Cache::forget('homepage_data');

        foreach (['Electronics', 'Furniture', 'Health & Beauty', 'Clothing'] as $cat) {
            Cache::forget("category_{$cat}_products");
        }

        Cache::forget('sidebar_categories');

        return redirect()->route('home')->with('success', 'Cache cleared successfully!');
    }
}