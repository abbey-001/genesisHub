<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Brand;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\BrandService;
use Illuminate\Http\JsonResponse;


class ProductController extends Controller
{
     public function __construct(private BrandService $brandService) {}
    // =========================================================================
    // SHARED VALIDATION RULES
    // =========================================================================

 private function productRules(bool $isUpdate = false): array
    {
        return [
            'name'              => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'required|string',
            'price'             => 'required|numeric|min:0',
            'sale_price'        => 'nullable|numeric|min:0|lt:price',
            'stock'             => 'required|integer|min:0',
            'category_id'       => 'required|exists:categories,id',
            'subcategory_id'    => 'nullable|exists:subcategories,id',
            'brand_id'          => 'nullable|exists:brands,id',
            'is_active'         => 'boolean',
            'is_featured'       => 'boolean',
 
            // Search / discovery fields
            'tags'              => 'nullable|string|max:500',
            'search_keywords'   => 'nullable|string|max:1000',
            'use_cases'         => 'nullable|string|max:500',
            'target_audience'   => 'nullable|string|in:men,women,kids,unisex,business,all',
            'meta_title'        => 'nullable|string|max:160',
            'meta_description'  => 'nullable|string|max:320',
            'model_number'      => 'nullable|string|max:100',
            'condition'         => 'nullable|string|in:new,used,refurbished',
 
            // ── NEW: fulfillment type ──────────────────────────────────────
            // max_ready_days is required only when fulfillment_type is NOT in_stock.
            // The 'required_if' rule handles this — in_stock products leave it null.
            'fulfillment_type'  => 'nullable|string|in:in_stock,pre_order,made_to_order',
            'max_ready_days'    => 'nullable|integer|min:1|max:365|required_if:fulfillment_type,pre_order|required_if:fulfillment_type,made_to_order',
            // ─────────────────────────────────────────────────────────────
 
            // Specifications
            'spec_keys'         => 'nullable|array',
            'spec_keys.*'       => 'nullable|string|max:100',
            'spec_values'       => 'nullable|array',
            'spec_values.*'     => 'nullable|string|max:255',
 
            // Variants
            'variant_names'                  => 'nullable|array',
            'variant_names.*'                => 'nullable|string|max:100',
            'variant_values'                 => 'nullable|array',
            'variant_values.*'               => 'nullable|string|max:500',
            'variant_price_adjustments'      => 'nullable|array',
            'variant_price_adjustments.*'    => 'nullable|numeric',
 
            ...($isUpdate ? [] : ['images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120']),
        ];
    }

    /**
     * Build the specifications JSON from parallel spec_keys / spec_values arrays.
     */
 private function buildSpecifications(Request $request): ?array
    {
        $keys   = $request->input('spec_keys', []);
        $values = $request->input('spec_values', []);
        if (empty($keys)) return null;
        $specs = [];
        foreach ($keys as $i => $key) {
            $key   = trim($key);
            $value = trim($values[$i] ?? '');
            if ($key !== '' && $value !== '') {
                $specs[$key] = $value;
            }
        }
        return !empty($specs) ? $specs : null;
    }
 
    private function buildVariants(Request $request): ?array
    {
        $names  = $request->input('variant_names', []);
        $values = $request->input('variant_values', []);
        if (empty($names)) return null;
        $variants = [];
        foreach ($names as $i => $name) {
            $name = trim($name);
            $raw  = trim($values[$i] ?? '');
            if ($name !== '' && $raw !== '') {
                $variants[$name] = array_map('trim', explode(',', $raw));
            }
        }
        return !empty($variants) ? $variants : null;
    }
 
    private function normaliseTags(?string $tags): ?string
    {
        if (!$tags) return null;
        $arr = array_unique(array_filter(array_map('trim', explode(',', strtolower($tags)))));
        return implode(', ', $arr);
    }

    /**
     * Persist uploaded product images and promote the first available image to primary.
     */
    private function storeProductImages(Product $product, array $images): array
    {
        $hasPrimary  = $product->images()->where('is_primary', true)->exists();
        $makePrimary = ! $hasPrimary;
        $stored      = [];

        foreach ($images as $image) {
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $path     = $image->storeAs('products', $filename, 'public');

            $record = ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
                'is_primary' => $makePrimary,
            ]);

            $stored[] = [
                'id'         => $record->id,
                'path'       => $path,
                'url'        => asset('public/storage/' . $path),
                'storageUrl' => Storage::url($path),
            ];

            $makePrimary = false;
        }

        return $stored;
    }
 
    private function checkProductOwnership(Product $product): void
    {
        $seller = Auth::guard('seller')->user()->seller;
        if ($product->shop_id !== $seller->shop->id) {
            abort(403, 'Unauthorized action.');
        }
    }

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index(Request $request)
    {
        $seller = Auth::guard('seller')->user()->seller;

        $query = Product::where('shop_id', $seller->shop->id)
            ->with(['category', 'brand', 'images']);

        if ($request->filled('search')) {
            $term = $request->search;
            // Use the Product::search scope for consistent behaviour
            $query->search($term);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('audience')) {
            $query->where('target_audience', $request->audience);
        }

        $products   = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::all();

        return view('seller.products.index', compact('products', 'categories'));
    }

    // =========================================================================
    // CREATE
    // =========================================================================

    public function create()
    {
        $categories = Category::with('subcategories')->get();
        $brands     = Brand::orderBy('name')->get();
        $product    = null;

        return view('seller.products.create', compact('categories', 'brands', 'product'));
    }

    // =========================================================================
    // STORE
    // =========================================================================
public function store(Request $request)
{
    $seller    = Auth::guard('seller')->user()->seller;
    $validated = $request->validate($this->productRules());

    $validated['shop_id']          = $seller->shop->id;
    $validated['slug']             = Str::slug($validated['name']);
    $validated['is_active']        = $request->has('is_active');
    $validated['is_featured']      = $request->has('is_featured');
    $validated['tags']             = $this->normaliseTags($request->tags);
    $validated['specifications']   = $this->buildSpecifications($request);
    $validated['variants']         = $this->buildVariants($request);
 
    // ── NEW: fulfillment type defaults ────────────────────────────────────
    // If the seller didn't pick a type (old form submission), default to in_stock.
    $validated['fulfillment_type'] = $request->input('fulfillment_type', 'in_stock');
 
    // max_ready_days is null for in_stock products — the platform constant
    // Product::IN_STOCK_MAX_DAYS is used instead at estimate-calculation time.
    $validated['max_ready_days']   = $validated['fulfillment_type'] === 'in_stock'
        ? null
        : (int) $request->input('max_ready_days');
    // ─────────────────────────────────────────────────────────────────────

    $product = Product::create($validated);

    $this->syncVariants($product, $request);

    if ($request->hasFile('images')) {
        $this->storeProductImages($product, $request->file('images'));
    }

    // AJAX form submission — return product ID so JS can upload images
    if ($request->expectsJson()) {
        return response()->json([
            'success'    => true,
            'product_id' => $product->id,
            'redirect'   => route('seller.products.show', $product),
        ]);
    }

    return redirect()
        ->route('seller.products.show', $product)
        ->with('success', 'Product created successfully.');
}

/**
 * POST /seller/products/{product}/images/chunked
 * Accepts a small batch of images (JSON response).
 * The first image uploaded to a product with no images yet becomes primary.
 */
public function uploadImagesChunked(Request $request, Product $product): JsonResponse
{
    $this->checkProductOwnership($product);

    $request->validate([
        'images'   => 'required|array|max:5',
        'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
    ]);

    $stored = $this->storeProductImages($product, $request->file('images'));

    return response()->json(['success' => true, 'images' => $stored]);
}

    // =========================================================================
    // SHOW
    // =========================================================================

    public function show(Product $product)
    {
        $this->checkProductOwnership($product);
        $product->load(['category', 'subcategory', 'brand', 'images', 'reviews.user']);

        return view('seller.products.show', compact('product'));
    }

    // =========================================================================
    // EDIT
    // =========================================================================

    public function edit(Product $product)
    {
        $this->checkProductOwnership($product);

        $categories = Category::with('subcategories')->get();
        $brands     = Brand::orderBy('name')->get();
        $product->load('images');

        return view('seller.products.edit', compact('product', 'categories', 'brands'));
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(Request $request, Product $product)
    {
        $this->checkProductOwnership($product);
        $validated = $request->validate($this->productRules(true));
 
        $validated['is_active']        = $request->has('is_active');
        $validated['is_featured']      = $request->has('is_featured');
        $validated['tags']             = $this->normaliseTags($request->tags);
        $validated['specifications']   = $this->buildSpecifications($request);
        $validated['variants']         = $this->buildVariants($request);
     
        // ── NEW ──────────────────────────────────────────────────────────────
        $validated['fulfillment_type'] = $request->input('fulfillment_type', 'in_stock');
        $validated['max_ready_days']   = $validated['fulfillment_type'] === 'in_stock'
            ? null
            : (int) $request->input('max_ready_days');
        // ─────────────────────────────────────────────────────────────────────
     
        $product->update($validated);
        $this->syncVariants($product, $request);
     
        return redirect()
            ->route('seller.products.show', $product)
            ->with('success', 'Product updated successfully.');
    }
    
     private function syncVariants(Product $product, Request $request): void
    {
        $names       = $request->input('variant_names', []);
        $values      = $request->input('variant_values', []);
        $adjustments = $request->input('variant_price_adjustments', []);

        // Check whether the seller actually filled in any variant data.
        // Filter out rows where both name and value are blank — if nothing
        // meaningful was submitted, skip the whole thing and don't touch the DB.
        $hasRealData = false;
        foreach ($names as $i => $name) {
            if (trim($name) !== '' && trim($values[$i] ?? '') !== '') {
                $hasRealData = true;
                break;
            }
        }

        // Wipe existing rows; re-insert cleanly
        ProductVariant::where('product_id', $product->id)->delete();

        if (! $hasRealData) {
            return; // Nothing to insert — seller left variants empty
        }

        foreach ($names as $i => $name) {
            $name = trim($name);
            $raw  = trim($values[$i] ?? '');

            if ($name === '' || $raw === '') {
                continue;
            }

            // Default to 0 when the seller leaves the price adjustment blank.
            // The DB column is NOT NULL, so null would cause a constraint violation.
            $priceAdj = (isset($adjustments[$i]) && $adjustments[$i] !== '')
                ? (float) $adjustments[$i]
                : 0.0;

            $individualValues = array_filter(
                array_map('trim', explode(',', $raw))
            );

            foreach ($individualValues as $value) {
                $sku = strtoupper(
                    Str::slug($product->name) . '-' .
                    Str::slug($name) . '-' .
                    Str::slug($value)
                );

                ProductVariant::create([
                    'product_id'       => $product->id,
                    'variant_name'     => $name,
                    'variant_value'    => $value,
                    'sku'              => $sku,
                    'price_adjustment' => $priceAdj,
                    'stock'            => $product->stock, // Default to product stock; seller can adjust later
                ]);
            }
        }
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    public function destroy(Product $product)
    {
        $this->checkProductOwnership($product);

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $product->delete();

        return redirect()->route('seller.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    // =========================================================================
    // IMAGE MANAGEMENT (unchanged from original)
    // =========================================================================

    public function uploadImages(Request $request, Product $product)
    {
        $this->checkProductOwnership($product);

        $request->validate(['images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120']);

        if ($request->hasFile('images')) {
            $this->storeProductImages($product, $request->file('images'));
        }

        return back()->with('success', 'Images uploaded successfully.');
    }

    public function deleteImage(ProductImage $image)
    {
        $this->checkProductOwnership($image->product);
        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return back()->with('success', 'Image deleted successfully.');
    }

    public function setPrimaryImage(Request $request, Product $product)
    {
        $this->checkProductOwnership($product);
        $request->validate(['image_id' => 'required|exists:product_images,id']);

        ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);
        ProductImage::where('id', $request->image_id)->update(['is_primary' => true]);

        return back()->with('success', 'Primary image set successfully.');
    }
    
    
    
    //BRANDS
     public function search(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));
 
        if (strlen($q) < 2) {
            return response()->json([]);
        }
 
        $brands = Brand::where('name', 'like', '%' . $q . '%')
            ->orWhere('slug', 'like', '%' . str_replace(' ', '-', strtolower($q)) . '%')
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name']);
 
        return response()->json($brands);
    }
 
    // -------------------------------------------------------------------------
    // POST /seller/brands/check
    // Fuzzy-check before creation. Returns:
    //   { status: 'safe' }                   — no similar brands found
    //   { status: 'similar', brands: [...] } — seller should review
    //   { status: 'exists',  brand: {...}  } — exact slug match, use this ID
    // -------------------------------------------------------------------------
    public function check(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:255']);
 
        $result = $this->brandService->createBrand(
            rawName: $request->input('name'),
            force: false
        );
 
        return match ($result['status']) {
            'exists' => response()->json([
                'status' => 'exists',
                'brand'  => ['id' => $result['brand']->id, 'name' => $result['brand']->name],
            ]),
            'similar' => response()->json([
                'status' => 'similar',
                'brands' => collect($result['brands'])->map(fn($b) => [
                    'id'   => $b->id,
                    'name' => $b->name,
                ])->values(),
            ]),
            default => response()->json(['status' => 'safe']),
        };
    }
 
    // -------------------------------------------------------------------------
    // POST /seller/brands
    // Actually create the brand. $force=true means user already reviewed the
    // fuzzy matches and still wants to proceed.
    // -------------------------------------------------------------------------
    public function storeBrand(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:255']);
 
        $force  = (bool) $request->boolean('force', false);
        $result = $this->brandService->createBrand(
            rawName: $request->input('name'),
            force: $force
        );
 
        return match ($result['status']) {
            'created' => response()->json([
                'status'  => 'created',
                'brand'   => ['id' => $result['brand']->id, 'name' => $result['brand']->name],
                'message' => "Brand \"{$result['brand']->name}\" created successfully.",
            ], 201),
 
            'exists' => response()->json([
                'status'  => 'exists',
                'brand'   => ['id' => $result['brand']->id, 'name' => $result['brand']->name],
                'message' => "This brand already exists as \"{$result['brand']->name}\".",
            ]),
 
            // 'similar' should not reach here if the UI flow is correct,
            // but guard anyway.
            'similar' => response()->json([
                'status'  => 'similar',
                'brands'  => collect($result['brands'])->map(fn($b) => [
                    'id'   => $b->id,
                    'name' => $b->name,
                ])->values(),
                'message' => 'Similar brands already exist. Please confirm you want to create a new one.',
            ], 409),
        };
    }
}