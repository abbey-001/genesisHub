<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Product;
use App\Models\Category;
use App\Models\Review;
use App\Models\Seller;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Display shop listing page
     */
    public function index(Request $request)
    {
        $query = Shop::with('seller.user')
            ->where('is_active', true)
            ->withCount('products');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('shop_name', 'like', "%{$search}%")
                  ->orWhere('shop_description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'name':
                $query->orderBy('shop_name', 'asc');
                break;
            case 'rating':
                $query->orderByDesc('rating');
                break;
            case 'products':
                $query->orderByDesc('total_products');
                break;
            default:
                $query->latest();
        }

        $shops = $query->paginate(12);
        
        $categoriesWithSubs = Category::select('id', 'name', 'slug', 'image')
            ->with(['subcategories' => fn($q) => 
                $q->select('id', 'category_id', 'name', 'slug')
                  ->orderBy('sort_order')
                  ->limit(10)
            ])
            ->limit(10)
            ->get();
            
        $sellers = Seller::paginate(10);

        return view('shop.index', compact('shops', 'categoriesWithSubs', 'sellers'));
    }

    /**
     * Display individual shop page
     */
    public function show($slugOrId)
    {
        // Try to find by slug first, then by ID
        $shop = Shop::where('slug', $slugOrId)
            ->orWhere('id', $slugOrId)
            ->where('is_active', true)
            ->with(['seller.user'])
            ->firstOrFail();

        // Get shop products with filters
        $productsQuery = $shop->products()
            ->where('is_active', true)
            ->with(['images', 'category', 'brand']);

        // Category filter
        if (request()->filled('category')) {
            $productsQuery->where('category_id', request('category'));
        }

        // Search within shop
        if (request()->filled('search')) {
            $search = request('search');
            $productsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Price range
        if (request()->filled('min_price')) {
            $productsQuery->where('price', '>=', request('min_price'));
        }
        if (request()->filled('max_price')) {
            $productsQuery->where('price', '<=', request('max_price'));
        }

        // Sort
        $sortBy = request()->get('sort', 'latest');
        switch ($sortBy) {
            case 'price_low':
                $productsQuery->orderBy('price', 'asc');
                break;
            case 'price_high':
                $productsQuery->orderBy('price', 'desc');
                break;
            case 'rating':
                $productsQuery->orderByDesc('rating');
                break;
            case 'popular':
                $productsQuery->orderByDesc('sold_count');
                break;
            default:
                $productsQuery->latest();
        }

        $products = $productsQuery->paginate(24);

        // Get shop statistics
        $stats = [
            'total_products' => $shop->products()->where('is_active', true)->count(),
            'total_reviews' => Review::whereIn('product_id', $shop->products()->pluck('id'))
                ->where('is_approved', true)
                ->count(),
            'avg_rating' => $shop->calculateRating(),
            'total_sold' => $shop->products()->sum('sold_count'),
        ];

        // Get categories available in this shop
        $shopCategories = Category::whereHas('products', function($q) use ($shop) {
            $q->where('shop_id', $shop->id)
              ->where('is_active', true);
        })->withCount(['products' => function($q) use ($shop) {
            $q->where('shop_id', $shop->id)
              ->where('is_active', true);
        }])->get();

        // Get recent reviews
        $recentReviews = Review::whereIn('product_id', $shop->products()->pluck('id'))
            ->where('is_approved', true)
            ->with(['user', 'product.images'])
            ->latest()
            ->take(5)
            ->get();

        // Get categories for navigation
        $categoriesWithSubs = Category::select('id', 'name', 'slug', 'image')
            ->with(['subcategories' => fn($q) => 
                $q->select('id', 'category_id', 'name', 'slug')
                  ->orderBy('sort_order')
                  ->limit(10)
            ])
            ->limit(10)
            ->get();

        return view('shop.show', compact(
            'shop',
            'products',
            'stats',
            'shopCategories',
            'recentReviews',
            'categoriesWithSubs'
        ));
    }

    /**
     * Get shop reviews
     */
    public function reviews($slugOrId)
    {
        $shop = Shop::where('slug', $slugOrId)
            ->orWhere('id', $slugOrId)
            ->where('is_active', true)
            ->firstOrFail();

        $reviews = Review::whereIn('product_id', $shop->products()->pluck('id'))
            ->where('is_approved', true)
            ->with(['user', 'product.images'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total_reviews' => $reviews->total(),
            'avg_rating' => $shop->calculateRating(),
        ];

        // Rating breakdown
        $ratingBreakdown = [];
        $totalReviews = Review::whereIn('product_id', $shop->products()->pluck('id'))
            ->where('is_approved', true)
            ->get();

        if ($totalReviews->count() > 0) {
            for ($i = 5; $i >= 1; $i--) {
                $count = $totalReviews->where('rating', $i)->count();
                $percentage = ($count / $totalReviews->count()) * 100;
                $ratingBreakdown[$i] = [
                    'count' => $count,
                    'percentage' => round($percentage, 1)
                ];
            }
        }

        // Get categories for navigation
        $categoriesWithSubs = Category::select('id', 'name', 'slug', 'image')
            ->with(['subcategories' => fn($q) => 
                $q->select('id', 'category_id', 'name', 'slug')
                  ->orderBy('sort_order')
                  ->limit(10)
            ])
            ->limit(10)
            ->get();

        return view('shops.reviews', compact(
            'shop',
            'reviews',
            'stats',
            'ratingBreakdown',
            'categoriesWithSubs'
        ));
    }
}