<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * ProductListingService — now powered by Algolia.
 *
 * FIXES in this version:
 *  1. TypesenseSearchService → AlgoliaSearchService (was crashing the container)
 *  2. getFilterOptionsFromFacets() rewritten for Algolia's facet shape
 *     (Algolia: ['brand_slug' => ['apple' => 12]] vs Typesense: [['field_name'=>'brand_slug','counts'=>[...]]])
 *  3. Debug log lines added throughout — grep "PLS_DEBUG" in laravel.log
 *  4. buildFromTypesenseResult renamed → buildFromAlgoliaResult (cosmetic, no logic change)
 */
class ProductListingService
{
    public function __construct(
        private AlgoliaSearchService $algolia   // ← was TypesenseSearchService
    ) {}

    // ─────────────────────────────────────────────────────────────
    // PUBLIC API
    // ─────────────────────────────────────────────────────────────

    public function build(Request $request): array
    {
        $cacheKey = $this->buildCacheKey($request);
        return Cache::remember($cacheKey, 300, fn () => $this->buildListingData($request));
    }

    public function buildForAjax(Request $request): array
    {
        return $this->buildListingData($request);
    }

    // ─────────────────────────────────────────────────────────────
    // CORE BUILD
    // ─────────────────────────────────────────────────────────────

    private function buildListingData(Request $request): array
    {
        $searchTerm = trim($request->input('search', $request->input('q', '')));
        $sortBy     = $request->get('sort_by', 'default');
        $page       = max(1, (int) $request->get('page', 1));
        $perPage    = 24;

        $showOutOfStock = $request->boolean('include_out_of_stock')
            || $request->input('in_stock_only') === '0'
            || $request->input('in_stock_only') === 0;

        $filters = $this->parseFilters($request, $showOutOfStock);

        // ── DEBUG ─────────────────────────────────────────────────
        Log::debug('PLS_DEBUG: buildListingData called', [
            'searchTerm' => $searchTerm,
            'sortBy'     => $sortBy,
            'page'       => $page,
            'filters'    => $filters,
        ]);

        // ── Try Algolia ───────────────────────────────────────────
        try {
            $algoliaResult = $this->algolia->search($searchTerm, $filters, $sortBy, $page, $perPage);

            Log::debug('PLS_DEBUG: Algolia search returned', [
                'total'      => $algoliaResult['total'] ?? 'N/A',
                'hits_count' => count($algoliaResult['hits'] ?? []),
                'took_ms'    => $algoliaResult['took_ms'] ?? 'N/A',
                'fallback'   => $algoliaResult['_fallback'] ?? false,
            ]);

        } catch (\Throwable $e) {
            Log::error('PLS_DEBUG: Algolia search threw an exception', [
                'message' => $e->getMessage(),
                'class'   => get_class($e),
            ]);
            // Force fallback to MySQL
            $algoliaResult = ['hits' => [], 'total' => 0, 'facets' => [], 'took_ms' => 0, '_fallback' => true];
        }

        $usedAlgolia = !empty($algoliaResult['hits'])
            || ($algoliaResult['total'] === 0 && !($algoliaResult['_fallback'] ?? false));

        Log::debug('PLS_DEBUG: engine decision', [
            'usedAlgolia' => $usedAlgolia,
            'hits_empty'  => empty($algoliaResult['hits']),
            'total'       => $algoliaResult['total'],
            'is_fallback' => $algoliaResult['_fallback'] ?? false,
        ]);

        if ($usedAlgolia) {
            return $this->buildFromAlgoliaResult($algoliaResult, $request, $searchTerm, $sortBy, $perPage, $page, $showOutOfStock);
        }

        // ── Fallback: MySQL ───────────────────────────────────────
        Log::debug('PLS_DEBUG: falling back to MySQL');
        return $this->buildFromMySQL($request, $searchTerm, $sortBy, $perPage, $showOutOfStock);
    }

    // ─────────────────────────────────────────────────────────────
    // ALGOLIA RESULT → STANDARD FORMAT
    // ─────────────────────────────────────────────────────────────

    private function buildFromAlgoliaResult(
        array $algoliaResult, Request $request, string $searchTerm,
        string $sortBy, int $perPage, int $page, bool $showOutOfStock
    ): array {
        $hitIds = array_map(
            fn($hit) => (int) ($hit['document']['id'] ?? $hit['objectID'] ?? 0),
            $algoliaResult['hits']
        );

        Log::debug('PLS_DEBUG: buildFromAlgoliaResult — hit IDs', ['ids' => $hitIds]);

        $dbProducts = Product::with([
                'shop:id,seller_id,shop_name,slug,is_active',
                'shop.seller:id,business_type,verification_status',
                'brand:id,name,slug',
                'category:id,name,slug',
                'images' => fn($q) => $q->where('is_primary', true)->limit(1),
            ])
            ->whereIn('id', $hitIds)
            ->get()
            ->keyBy('id');

        Log::debug('PLS_DEBUG: DB product fetch', [
            'requested' => count($hitIds),
            'found'     => $dbProducts->count(),
        ]);

        $ordered = collect($algoliaResult['hits'])->map(function ($hit) use ($dbProducts) {
            $id      = (int) ($hit['document']['id'] ?? $hit['objectID'] ?? 0);
            $product = $dbProducts->get($id);
            if (!$product) return null;
            $product->ts_highlight = $hit['highlights'][0]['snippet'] ?? null;
            return $product;
        })->filter()->values();

        $paginator = new LengthAwarePaginator(
            $ordered,
            $algoliaResult['total'],
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => 'page']
        );
        $paginator->appends($request->except('page'));

        $filterOptions      = $this->getFilterOptionsFromFacets($algoliaResult['facets']);
        $activeFilters      = $this->getActiveFilters($request);
        $pageData           = $this->buildPageMetadata($request, $algoliaResult['total']);
        $categoriesWithSubs = $this->getCategoriesWithSubs();

        return [
            'products'           => $paginator,
            'activeFilters'      => $activeFilters,
            'filterOptions'      => $filterOptions,
            'sortBy'             => $sortBy,
            'perPage'            => $perPage,
            'totalResults'       => $algoliaResult['total'],
            'pageTitle'          => $pageData['pageTitle'],
            'pageDescription'    => $pageData['pageDescription'],
            'categoriesWithSubs' => $categoriesWithSubs,
            'showOutOfStock'     => $showOutOfStock,
            'searchTerm'         => $searchTerm,
            'searchEngine'       => 'algolia',      // ← updated label
            'searchTookMs'       => $algoliaResult['took_ms'],
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // MYSQL FALLBACK
    // ─────────────────────────────────────────────────────────────

    private function buildFromMySQL(Request $request, string $searchTerm, string $sortBy, int $perPage, bool $showOutOfStock): array
    {
        $query = Product::query()
            ->with([
                'shop:id,seller_id,shop_name,slug,is_active',
                'shop.seller:id,business_type,verification_status',
                'brand:id,name,slug',
                'category:id,name,slug',
                'images' => fn ($q) => $q->where('is_primary', true)->limit(1),
            ])
            ->select(
                'id','name','slug','short_description',
                'price','sale_price','stock',
                'brand_id','category_id','subcategory_id','shop_id',
                'rating','review_count','sold_count',
                'is_featured','is_active',
                'condition','tags','model_number',
                'target_audience','meta_title','meta_description',
                'created_at'
            )
            ->active();

        if (!$showOutOfStock) {
            $query->inStock();
        }

        if (!empty($searchTerm)) {
            $this->applySearchFilter($query, $searchTerm);
        }

        $this->applyRequestFilters($query, $request);
        $this->applySorting($query, $sortBy, $searchTerm);

        $products = $query->paginate($perPage)->withQueryString();

        $filterOptions      = $this->getAvailableFilters($request);
        $activeFilters      = $this->getActiveFilters($request);
        $pageData           = $this->buildPageMetadata($request, $products->total());
        $categoriesWithSubs = $this->getCategoriesWithSubs();

        return [
            'products'           => $products,
            'activeFilters'      => $activeFilters,
            'filterOptions'      => $filterOptions,
            'sortBy'             => $sortBy,
            'perPage'            => $perPage,
            'totalResults'       => $products->total(),
            'pageTitle'          => $pageData['pageTitle'],
            'pageDescription'    => $pageData['pageDescription'],
            'categoriesWithSubs' => $categoriesWithSubs,
            'showOutOfStock'     => $showOutOfStock,
            'searchTerm'         => $searchTerm,
            'searchEngine'       => 'mysql',
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // FILTER PARSING (for Algolia)
    // ─────────────────────────────────────────────────────────────

    private function parseFilters(Request $request, bool $showOutOfStock): array
    {
        $filters = [];

        if (!$showOutOfStock) $filters['in_stock_only'] = true;

        if ($request->filled('category')) {
            $filters['category_slug'] = is_array($request->category)
                ? $request->category
                : explode(',', $request->category);
        }
        if ($request->filled('brand')) {
            $filters['brand_slug'] = is_array($request->brand)
                ? $request->brand
                : explode(',', $request->brand);
        }
        if ($request->filled('min_price'))  $filters['min_price']  = (float) $request->min_price;
        if ($request->filled('max_price'))  $filters['max_price']  = (float) $request->max_price;
        if ($request->filled('min_rating')) $filters['min_rating'] = (float) $request->min_rating;
        if ($request->filled('condition')) {
            $filters['condition'] = is_array($request->condition)
                ? $request->condition
                : explode(',', $request->condition);
        }
        if ($request->filled('seller_type')) {
            $filters['seller_type'] = is_array($request->seller_type)
                ? $request->seller_type
                : explode(',', $request->seller_type);
        }
        if ($request->filled('delivery_zone')) {
            $filters['delivery_zone'] = is_array($request->delivery_zone)
                ? $request->delivery_zone
                : explode(',', $request->delivery_zone);
        }
        if ($request->filled('filter')) {
            $f = is_array($request->filter) ? $request->filter : explode(',', $request->filter);
            if (in_array('sale', $f))        $filters['on_sale']   = true;
            if (in_array('new', $f))         $filters['new']       = true;
            if (in_array('featured', $f))    $filters['featured']  = true;
        }

        return $filters;
    }

    // ─────────────────────────────────────────────────────────────
    // MYSQL FILTER APPLICATION
    // ─────────────────────────────────────────────────────────────

    private function applyRequestFilters($query, Request $request): void
    {
        if ($request->filled('category')) {
            $slugs = is_array($request->category) ? $request->category : explode(',', $request->category);
            $ids   = Category::whereIn('slug', $slugs)->pluck('id');
            $query->whereIn('category_id', $ids);
        }
        if ($request->filled('brand')) {
            $slugs = is_array($request->brand) ? $request->brand : explode(',', $request->brand);
            $ids   = Brand::whereIn('slug', $slugs)->pluck('id');
            $query->whereIn('brand_id', $ids);
        }
        if ($request->filled('subcategory')) {
            $slugs = is_array($request->subcategory) ? $request->subcategory : explode(',', $request->subcategory);
            $ids   = Subcategory::whereIn('slug', $slugs)->pluck('id');
            $query->whereIn('subcategory_id', $ids);
        }
        if ($request->filled('condition')) {
            $conditions = is_array($request->condition) ? $request->condition : explode(',', $request->condition);
            $query->whereIn('condition', $conditions);
        }
        if ($request->filled('seller_type')) {
            $types = array_filter(is_array($request->seller_type) ? $request->seller_type : explode(',', $request->seller_type));
            if (!empty($types)) {
                $query->whereHas('shop.seller', fn ($q) => $q->whereIn('business_type', $types));
            }
        }
        if ($request->filled('delivery_zone')) {
            $zones = array_values(array_filter(is_array($request->delivery_zone) ? $request->delivery_zone : explode(',', $request->delivery_zone)));
            if (!empty($zones)) {
                $query->whereHas('shop', function ($q) use ($zones) {
                    $q->where(function ($inner) use ($zones) {
                        foreach ($zones as $zone) {
                            $inner->orWhereRaw(
                                "LOWER(REPLACE(REPLACE(REPLACE(delivery_zone, ' ', '-'), '/', '-'), '\\'', '')) = ?",
                                [strtolower($zone)]
                            );
                        }
                    });
                });
            }
        }
        if ($request->filled('min_price')) $query->whereRaw('COALESCE(NULLIF(sale_price, 0), price) >= ?', [(float) $request->min_price]);
        if ($request->filled('max_price')) $query->whereRaw('COALESCE(NULLIF(sale_price, 0), price) <= ?', [(float) $request->max_price]);
        if ($request->filled('min_rating')) $query->where('rating', '>=', (float) $request->min_rating);
        if ($request->filled('filter')) {
            $filters = is_array($request->filter) ? $request->filter : explode(',', $request->filter);
            foreach ($filters as $filter) {
                match ($filter) {
                    'featured'    => $query->where('is_featured', true),
                    'sale'        => $query->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price'),
                    'new'         => $query->where('created_at', '>=', now()->subDays(30)),
                    'bestsellers' => $query->where('sold_count', '>', 0)->orderBy('sold_count', 'desc'),
                    'hot'         => $query->where('sold_count', '>', 100),
                    default       => null,
                };
            }
        }
    }

    private function applySearchFilter($query, string $search): void
    {
        $escaped = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $search);
        $query->where(function ($q) use ($escaped) {
            $q->where('name', 'LIKE', "%{$escaped}%")
              ->orWhere('short_description', 'LIKE', "%{$escaped}%")
              ->orWhere('description', 'LIKE', "%{$escaped}%");
        });
        $query->addSelect(DB::raw("
            (CASE WHEN name LIKE '{$escaped}%' THEN 3 ELSE 0 END +
             CASE WHEN name LIKE '%{$escaped}%' THEN 2 ELSE 0 END +
             CASE WHEN short_description LIKE '%{$escaped}%' THEN 1 ELSE 0 END) AS relevance_score
        "));
    }

    private function applySorting($query, ?string $sortBy, string $searchTerm = ''): void
    {
        switch ($sortBy) {
            case 'price_low':   $query->orderByRaw('COALESCE(NULLIF(sale_price,0), price) ASC'); break;
            case 'price_high':  $query->orderByRaw('COALESCE(NULLIF(sale_price,0), price) DESC'); break;
            case 'name':        $query->orderBy('name', 'asc'); break;
            case 'rating':      $query->orderBy('rating', 'desc')->orderBy('review_count', 'desc'); break;
            case 'bestseller':  $query->orderBy('sold_count', 'desc'); break;
            case 'newest':      $query->orderBy('created_at', 'desc'); break;
            default:
                if (!empty($searchTerm)) {
                    $query->orderBy('relevance_score', 'desc')->orderBy('sold_count', 'desc')->orderBy('rating', 'desc');
                } else {
                    $query->orderBy('is_featured', 'desc')
                          ->orderByRaw('(sold_count * 0.5 + rating * 10 + review_count * 0.1) DESC')
                          ->orderBy('created_at', 'desc');
                }
        }
    }

    // ─────────────────────────────────────────────────────────────
    // FACET → FILTER OPTIONS
    // ─────────────────────────────────────────────────────────────

    /**
     * Convert Algolia facet arrays into filter options for the sidebar.
     *
     * Algolia returns:
     *   [ 'brand_slug' => ['apple' => 12, 'samsung' => 8], 'category_slug' => [...] ]
     *
     * (Previously written for Typesense shape — now fixed for Algolia.)
     */
    private function getFilterOptionsFromFacets(array $facets): array
    {
        Log::debug('PLS_DEBUG: getFilterOptionsFromFacets raw facets', ['facets' => $facets]);

        try {
            // ── Brands ───────────────────────────────────────────
            // Algolia facets: ['brand_slug' => ['apple' => 12, 'samsung' => 8]]
            $brandFacet  = (array) ($facets['brand_slug'] ?? []);
            $brandSlugs  = array_keys($brandFacet);

            if (!empty($brandSlugs)) {
                $brands = Brand::whereIn('slug', $brandSlugs)->get()
                    ->each(fn ($b) => $b->products_count = $brandFacet[$b->slug] ?? 0)
                    ->sortByDesc('products_count')->values();
            } else {
                $brands = Cache::remember('filter_brands', 600, fn () =>
                    Brand::withCount(['products as products_count' => fn ($q) => $q->where('is_active', true)])
                        ->having('products_count', '>', 0)->orderByDesc('products_count')->get()
                );
            }

            // ── Categories ───────────────────────────────────────
            $catFacet  = (array) ($facets['category_slug'] ?? []);
            $catSlugs  = array_keys($catFacet);

            if (!empty($catSlugs)) {
                $categories = Category::whereIn('slug', $catSlugs)->get()
                    ->each(fn ($c) => $c->products_count = $catFacet[$c->slug] ?? 0)
                    ->sortByDesc('products_count')->values();
            } else {
                $categories = Cache::remember('filter_categories', 600, fn () =>
                    Category::withCount(['products as products_count' => fn ($q) => $q->where('is_active', true)])
                        ->having('products_count', '>', 0)->orderByDesc('products_count')->get()
                );
            }

            // ── Price range ──────────────────────────────────────
            // Algolia doesn't return numeric stats in regular facets;
            // fall back to MySQL for price min/max.
            $ps = Product::active()->inStock()
                ->selectRaw('MIN(COALESCE(NULLIF(sale_price,0), price)) as min_price, MAX(COALESCE(NULLIF(sale_price,0), price)) as max_price')
                ->first();
            $priceStats = ['min' => floor($ps->min_price ?? 0), 'max' => ceil($ps->max_price ?? 500000)];

            Log::debug('PLS_DEBUG: getFilterOptionsFromFacets resolved', [
                'brands_count'     => $brands->count(),
                'categories_count' => $categories->count(),
                'price_range'      => $priceStats,
            ]);

            return [
                'brands'      => $brands,
                'categories'  => $categories,
                'price_range' => $priceStats,
                'ratings'     => [5, 4, 3, 2, 1],
            ];

        } catch (\Throwable $e) {
            Log::warning('PLS_DEBUG: getFilterOptionsFromFacets failed, falling back to MySQL: ' . $e->getMessage());
            return $this->getAvailableFilters(request());
        }
    }

    private function getAvailableFilters(Request $request): array
    {
        $brands = Cache::remember('filter_brands', 600, fn () =>
            Brand::withCount(['products as products_count' => fn ($q) => $q->where('is_active', true)])
                ->having('products_count', '>', 0)->orderByDesc('products_count')->get()
        );
        $categories = Cache::remember('filter_categories', 600, fn () =>
            Category::withCount(['products as products_count' => fn ($q) => $q->where('is_active', true)])
                ->having('products_count', '>', 0)->orderByDesc('products_count')->get()
        );
        $priceStats = Product::active()->inStock()
            ->selectRaw('MIN(COALESCE(NULLIF(sale_price,0), price)) as min_price, MAX(COALESCE(NULLIF(sale_price,0), price)) as max_price')
            ->first();
        return [
            'brands'      => $brands,
            'categories'  => $categories,
            'price_range' => ['min' => floor($priceStats->min_price ?? 0), 'max' => ceil($priceStats->max_price ?? 500000)],
            'ratings'     => [5, 4, 3, 2, 1],
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // SHARED HELPERS
    // ─────────────────────────────────────────────────────────────

    private function getActiveFilters(Request $request): array
    {
        $active = [];
        if ($request->filled('search') || $request->filled('q'))
            $active['search'] = $request->input('search', $request->input('q'));
        if ($request->filled('brand')) {
            $slugs = is_array($request->brand) ? $request->brand : explode(',', $request->brand);
            $active['brands'] = Brand::whereIn('slug', $slugs)->get();
        }
        if ($request->filled('category')) {
            $slugs = is_array($request->category) ? $request->category : explode(',', $request->category);
            $active['categories'] = Category::whereIn('slug', $slugs)->get();
        }
        if ($request->filled('min_price') || $request->filled('max_price'))
            $active['price_range'] = ['min' => $request->get('min_price', 0), 'max' => $request->get('max_price', 999999)];
        if ($request->filled('min_rating')) $active['rating'] = $request->min_rating;
        if ($request->filled('filter'))
            $active['filters'] = is_array($request->filter) ? $request->filter : explode(',', $request->filter);
        if ($request->filled('condition')) {
            $active['conditions'] = array_filter(
                is_array($request->condition) ? $request->condition : explode(',', $request->condition)
            );
        }
        if ($request->filled('seller_type')) {
            $active['seller_types'] = array_filter(
                is_array($request->seller_type) ? $request->seller_type : explode(',', $request->seller_type)
            );
        }
        if ($request->filled('delivery_zone')) {
            $active['delivery_zones'] = array_filter(
                is_array($request->delivery_zone) ? $request->delivery_zone : explode(',', $request->delivery_zone)
            );
        }
        return $active;
    }

    private function getCategoriesWithSubs(): mixed
    {
        return Cache::remember('sidebar_categories', 1800, fn () =>
            Category::select('id', 'name', 'slug', 'image')
                ->with(['subcategories' => fn ($q) => $q->select('id','category_id','name','slug')->orderBy('sort_order')->limit(10)])
                ->limit(10)->get()
        );
    }

    private function buildPageMetadata(Request $request, int $totalResults): array
    {
        $title = 'Shop Products';
        $description = 'Browse our collection of products';
        if ($request->filled('search') || $request->filled('q')) {
            $s = $request->input('search', $request->input('q'));
            $title = "Search results for \"{$s}\"";
            $description = number_format($totalResults) . " products found for \"{$s}\"";
        }
        if ($request->filled('filter')) {
            $map = ['featured'=>'Featured Products','sale'=>'Products On Sale','new'=>'New Arrivals','bestsellers'=>'Best Sellers','hot'=>'Hot Products'];
            $f = is_array($request->filter) ? $request->filter[0] : $request->filter;
            $title = $map[$f] ?? $title;
        }
        if ($request->filled('category')) {
            $slugs = is_array($request->category) ? $request->category : explode(',', $request->category);
            $cat = Category::whereIn('slug', $slugs)->first();
            if ($cat) { $title = $cat->name . ' Products'; $description = $cat->description ?? "Browse all {$cat->name} products"; }
        }
        if ($request->filled('brand')) {
            $slugs = is_array($request->brand) ? $request->brand : explode(',', $request->brand);
            $brand = Brand::whereIn('slug', $slugs)->first();
            if ($brand) { $title = $brand->name . ' Products'; $description = "Shop all {$brand->name} products"; }
        }
        return ['pageTitle' => $title, 'pageDescription' => $description];
    }

    private function buildCacheKey(Request $request): string
    {
        $version = Cache::get('products_listing_version', 1);
        $params  = $request->only(['q','search','brand','category','subcategory','min_price','max_price','min_rating','filter','sort_by','page','in_stock_only','include_out_of_stock','condition','seller_type','delivery_zone']);
        return 'products_listing_v' . $version . '_' . md5(json_encode($params));
    }

    public function clearListingCache(): void
    {
        Cache::forget('sidebar_categories');
        Cache::forget('filter_brands');
        Cache::forget('filter_categories');
        $version = (int) Cache::get('products_listing_version', 1) + 1;
        Cache::put('products_listing_version', $version, now()->addDays(7));
    }
}