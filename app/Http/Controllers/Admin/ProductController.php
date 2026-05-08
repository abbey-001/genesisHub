<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Shop;
use App\Services\AdminProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(AdminProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display all products
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'shop', 'images']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhereHas('shop', function($q2) use ($search) {
                      $q2->where('shop_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by brand
        if ($request->filled('brand')) {
            $query->where('brand_id', $request->brand);
        }

        // Filter by shop
        if ($request->filled('shop')) {
            $query->where('shop_id', $request->shop);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'in_stock') {
                $query->where('stock', '>', 0);
            } elseif ($request->stock_status === 'out_of_stock') {
                $query->where('stock', '<=', 0);
            } elseif ($request->stock_status === 'low_stock') {
                $query->where('stock', '>', 0)->where('stock', '<=', 10);
            }
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate(20);

        // Statistics
        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'inactive' => Product::where('is_active', false)->count(),
            'out_of_stock' => Product::where('stock', '<=', 0)->count(),
            'low_stock' => Product::where('stock', '>', 0)->where('stock', '<=', 10)->count(),
            'featured' => Product::where('is_featured', true)->count(),
        ];

        // For filters
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $shops = Shop::orderBy('shop_name')->get();

        return view('admin.products.index', compact('products', 'stats', 'categories', 'brands', 'shops'));
    }

    /**
     * Show product details
     */
    public function show(Product $product)
    {
        $product->load(['category', 'subcategory', 'brand', 'shop', 'images', 'reviews.user']);

        // Calculate statistics
        $stats = [
            'total_sold' => $product->sold_count ?? 0,
            'total_revenue' => ($product->sold_count ?? 0) * $product->price,
            'avg_rating' => $product->rating ?? 0,
            'total_reviews' => $product->reviews->count(),
            'stock_value' => $product->stock * $product->price,
        ];

        return view('admin.products.show', compact('product', 'stats'));
    }

    /**
     * Show edit form
     */
    public function edit(Product $product)
    {
        $product->load('images');
        $categories = Category::with('subcategories')->get();
        $brands = Brand::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    /**
     * Update product
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');

        $product->update($validated);

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Product updated successfully!');
    }

    /**
     * Toggle product status
     */
    public function toggleStatus(Product $product)
    {
        $product->update([
            'is_active' => !$product->is_active,
        ]);

        $status = $product->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Product {$status} successfully!");
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Product $product)
    {
        $product->update([
            'is_featured' => !$product->is_featured,
        ]);

        $status = $product->is_featured ? 'featured' : 'unfeatured';

        return back()->with('success', "Product {$status} successfully!");
    }

    /**
     * Delete product
     */
    public function destroy(Product $product)
    {
        try {
            // Check if product has orders
            if ($product->orderItems()->exists()) {
                return back()->with('error', 'Cannot delete product with existing orders.');
            }

            // Delete images
            foreach ($product->images as $image) {
                if (file_exists(storage_path('app/public/' . $image->image_path))) {
                    unlink(storage_path('app/public/' . $image->image_path));
                }
                $image->delete();
            }

            $product->delete();

            return redirect()->route('admin.products.index')
                ->with('success', 'Product deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete product: ' . $e->getMessage());
        }
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,feature,unfeature,delete',
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        $products = Product::whereIn('id', $request->product_ids)->get();

        try {
            DB::beginTransaction();

            foreach ($products as $product) {
                switch ($request->action) {
                    case 'activate':
                        $product->update(['is_active' => true]);
                        break;
                    case 'deactivate':
                        $product->update(['is_active' => false]);
                        break;
                    case 'feature':
                        $product->update(['is_featured' => true]);
                        break;
                    case 'unfeature':
                        $product->update(['is_featured' => false]);
                        break;
                    case 'delete':
                        if (!$product->orderItems()->exists()) {
                            $product->delete();
                        }
                        break;
                }
            }

            DB::commit();

            return back()->with('success', "Bulk action completed successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Bulk action failed: ' . $e->getMessage());
        }
    }

    /**
     * Export products
     */
    public function export(Request $request)
    {
        $query = Product::with(['category', 'brand', 'shop']);

        // Apply same filters as index
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $products = $query->get();

        $filename = 'products_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'ID', 'Name', 'SKU', 'Category', 'Brand', 'Shop', 
                'Price', 'Sale Price', 'Stock', 'Status', 'Featured', 'Created'
            ]);
            
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->sku ?? 'N/A',
                    $product->category->name ?? 'N/A',
                    $product->brand->name ?? 'N/A',
                    $product->shop->shop_name ?? 'N/A',
                    $product->price,
                    $product->sale_price ?? 'N/A',
                    $product->stock,
                    $product->is_active ? 'Active' : 'Inactive',
                    $product->is_featured ? 'Yes' : 'No',
                    $product->created_at->format('Y-m-d'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}