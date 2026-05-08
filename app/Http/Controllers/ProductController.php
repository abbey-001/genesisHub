<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Services\ProductListingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Collection;


/**
 * ProductController — refactored to use ProductListingService.
 *
 * Changes from original:
 *  1. All listing / filtering logic delegated to ProductListingService.
 *  2. clearCache() no longer calls Cache::flush() (which wiped everything);
 *     it now calls the service's targeted cache clearing helper.
 *  3. Recently-viewed tracking is unchanged.
 *  4. CompareController logic left here for now but marked for extraction.
 */
class ProductController extends Controller
{
    public function __construct(protected ProductListingService $listing) {}

    // ─────────────────────────────────────────────────────────────
    // INDEX / LISTING
    // ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $data = $this->listing->build($request);
        return view('products.index', $data);
    }

    // ─────────────────────────────────────────────────────────────
    // CATEGORY / BRAND SHORTCUTS
    // ─────────────────────────────────────────────────────────────

    public function category(string $slug, Request $request)
    {
        Category::with('subcategories')->where('slug', $slug)->firstOrFail();
        $request->merge(['category' => $slug]);
        return $this->index($request);
    }

    public function brand(string $slug, Request $request)
    {
        Brand::where('slug', $slug)->firstOrFail();
        $request->merge(['brand' => $slug]);
        return $this->index($request);
    }

    // ─────────────────────────────────────────────────────────────
    // PRODUCT DETAIL
    // ─────────────────────────────────────────────────────────────

    public function show(int $id)
    {
        $cacheKey = "product_detail_{$id}";

        $product = Cache::remember($cacheKey, 1800, fn () =>
            Product::with([
                'images',
                'shop:id,seller_id,shop_name,slug,is_active',
                'shop.seller:id,business_type,verification_status',
                'brand:id,name,slug,logo',
                'category:id,name,slug,image',
                'subcategory:id,name,slug',
            ])
            ->where('id', $id)
            ->active()
            ->firstOrFail()
        );

        $this->trackRecentlyViewed($product->id);

        $canReview       = false;
        $eligibleOrderItem = null;

        if (auth()->check()) {
            $canReview         = $product->canBeReviewedBy(auth()->id());
            $eligibleOrderItem = $product->getEligibleOrderItemForUser(auth()->id());
        }

        $ratingBreakdown = $product->getRatingBreakdown();

        $relatedProducts = Cache::remember("related_{$product->id}", 1800, fn () =>
            Product::with([
                    'shop:id,seller_id,shop_name,slug,is_active',
                    'shop.seller:id,business_type,verification_status',
                    'brand:id,name,slug',
                    'images' => fn ($q) => $q->where('is_primary', true)->limit(1),
                ])
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->active()
                ->inStock()
                ->limit(8)
                ->get()
        );

        // Recently viewed — resolved from session (not cached)
        $recentlyViewed = $this->getRecentlyViewedProducts($product->id);

        $categoriesWithSubs = Cache::remember('sidebar_categories', 1800, fn () =>
            Category::select('id', 'name', 'slug', 'image')
                ->with(['subcategories' => fn ($q) =>
                    $q->select('id', 'category_id', 'name', 'slug')
                      ->orderBy('sort_order')
                      ->limit(10)
                ])
                ->limit(10)
                ->get()
        );

        return view('products.show', compact(
            'product',
            'relatedProducts',
            'categoriesWithSubs',
            'canReview',
            'eligibleOrderItem',
            'ratingBreakdown',
            'recentlyViewed',
        ));
    }

    // ─────────────────────────────────────────────────────────────
    // AJAX FILTER ENDPOINT
    // ─────────────────────────────────────────────────────────────

    public function filterProducts(Request $request)
    {
        try {
            $data = $this->listing->buildForAjax($request);

            if ($request->ajax() || $request->wantsJson()) {
                $af = $data['activeFilters'];

                // Compute the count for the filter badge (Bug 3 fix)
                $filterCount = 0;
                if (!empty($af['search']))         $filterCount++;
                if (!empty($af['brands']))         $filterCount += count($af['brands']);
                if (!empty($af['categories']))     $filterCount += count($af['categories']);
                if (!empty($af['price_range']))    $filterCount++;
                if (!empty($af['rating']))         $filterCount++;
                if (!empty($af['filters']))        $filterCount += count($af['filters']);
                if (!empty($af['conditions']))     $filterCount += count($af['conditions']);
                if (!empty($af['seller_types']))   $filterCount += count($af['seller_types']);
                if (!empty($af['delivery_zones'])) $filterCount += count($af['delivery_zones']);

                return response()->json([
                    'success'           => true,
                    'html'              => view('products.partials.product-grid', ['products' => $data['products']])->render(),
                    'pagination'        => view('products.partials.pagination', ['products' => $data['products']])->render(),
                    // Bug 5 fix: send re-rendered filter bar so JS can swap it in without a page reload
                    'activeFiltersHtml' => view('products.partials.active-filters-bar', ['activeFilters' => $af])->render(),
                    'totalResults'      => $data['totalResults'],
                    'activeFilters'     => $af,
                    'filterCount'       => $filterCount,
                ]);
            }

            return view('products.index', $data);

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error loading products.'], 500);
            }
            return back()->with('error', 'Error loading products.');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // COMPARE
    // ─────────────────────────────────────────────────────────────

    public function compare()
    {
        $compareIds = session()->get('compare', []);
        $products   = Product::with(['images', 'brand', 'category'])
            ->whereIn('id', $compareIds)
            ->get();

        $categoriesWithSubs = Cache::remember('sidebar_categories', 1800, fn () =>
            Category::select('id', 'name', 'slug', 'image')
                ->with(['subcategories' => fn ($q) =>
                    $q->select('id', 'category_id', 'name', 'slug')->orderBy('sort_order')->limit(10)
                ])
                ->limit(10)
                ->get()
        );

        return view('products.compare', compact('products', 'categoriesWithSubs'));
    }

    public function addToCompare(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);
        $compare = session()->get('compare', []);

        if (count($compare) >= 4) {
            return response()->json(['success' => false, 'message' => 'Maximum 4 products can be compared.'], 400);
        }

        if (!in_array($request->product_id, $compare)) {
            $compare[] = $request->product_id;
            session()->put('compare', $compare);
        }

        return response()->json(['success' => true, 'compare_count' => count($compare)]);
    }

    public function removeFromCompare(Request $request)
    {
        $request->validate(['product_id' => 'required|integer']);
        $compare = array_diff(session()->get('compare', []), [$request->product_id]);
        session()->put('compare', array_values($compare));

        return response()->json(['success' => true, 'compare_count' => count($compare)]);
    }

    // ─────────────────────────────────────────────────────────────
    // TRACK VIEW
    // ─────────────────────────────────────────────────────────────

    public function trackView(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);
        $this->trackRecentlyViewed($request->product_id);
        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────────────────────
    // QUICK VIEW (AJAX)
    // ─────────────────────────────────────────────────────────────

    public function quickView(int $id)
    {
        $product = Product::with(['images', 'brand', 'category'])->findOrFail($id);
        return view('products.partials.quick-view', compact('product'))->render();
    }

    // ─────────────────────────────────────────────────────────────
    // CACHE
    // ─────────────────────────────────────────────────────────────

    /**
     * Targeted cache clear — safe, does NOT flush the entire cache store.
     */
    public function clearCache()
    {
        $this->listing->clearListingCache();
        return redirect()->back()->with('success', 'Product listing cache cleared.');
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────

    private function trackRecentlyViewed(int $productId): void
    {
        $viewed = session()->get('recently_viewed', []);
        $viewed = array_diff($viewed, [$productId]);
        array_unshift($viewed, $productId);
        session()->put('recently_viewed', array_slice($viewed, 0, 20));
    }


private function getRecentlyViewedProducts(int $excludeId): Collection
{
    $ids = array_values(array_diff(session()->get('recently_viewed', []), [$excludeId]));
    $ids = array_slice($ids, 0, 8);

    if (empty($ids)) {
        return new Collection();
    }

    return Product::with([
        'images' => fn ($q) => $q->where('is_primary', true)->limit(1),
        'brand:id,name,slug',
        'shop:id,seller_id,shop_name,slug,is_active',
        'shop.seller:id,business_type,verification_status',
    ])
    ->select(
        'id','name','slug','short_description',
        'price','sale_price','stock',
        'brand_id','category_id','shop_id',
        'rating','review_count','sold_count',
        'is_featured','is_active',
        'condition','created_at'
    )
    ->whereIn('id', $ids)
    ->active()
    ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')')
    ->get();
}
}