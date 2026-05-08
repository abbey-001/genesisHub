<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Address;
use App\Services\CartTotalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Coupon;
use App\Models\CouponUsage;

class CartController extends Controller
{
    protected CartTotalService $cartTotalService;

    public function __construct(CartTotalService $cartTotalService)
    {
        $this->cartTotalService = $cartTotalService;
    }

    /**
     * Display the cart page
     */
 public function index()
    {
        $cart               = session()->get('cart', []);
        $cartTotal          = $this->cartTotalService->calculateSubtotal($cart);
        $categoriesWithSubs = Category::with('subcategories')->get();
 
        $defaultAddress  = null;
        $userAddresses   = [];
        $deliveryFee     = null;
 
        if (Auth::check()) {
            $defaultAddress = Auth::user()->addresses()->where('is_default', true)->first();
            $userAddresses  = Auth::user()->addresses()->orderBy('is_default', 'desc')->get();
 
            if (!$defaultAddress && $userAddresses->count() > 0) {
                $defaultAddress = $userAddresses->first();
            }
 
            if ($defaultAddress && $defaultAddress->delivery_zone && count($cart) > 0) {
                $deliveryFee = $this->cartTotalService->calculateDeliveryFee(
                    $cart,
                    $defaultAddress->delivery_zone
                );
            }
        }
 
        // ── NEW: delivery estimate ──────────────────────────────────────────
        // Calculated for all visitors (logged in or not) because the estimate
        // depends only on the products in the cart, not on the address.
        $deliveryEstimate = count($cart) > 0
            ? $this->cartTotalService->calculateDeliveryEstimate($cart)
            : null;
        // ───────────────────────────────────────────────────────────────────
 
        return view('cart.index', compact(
            'cart',
            'cartTotal',
            'categoriesWithSubs',
            'defaultAddress',
            'userAddresses',
            'deliveryFee',
            'deliveryEstimate'     // ← NEW
        ));
    }
    /**
     * Add product to cart (with optional variant selection)
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id'       => 'required|exists:products,id',
            'quantity'         => 'required|integer|min:1',
            // variants is an optional key=>value map e.g. {"Size":"XL","Color":"Red"}
            'selected_variants' => 'nullable|array',
        ]);

        $product = Product::with(['images', 'variants'])->findOrFail($request->product_id);
        
        if ($product->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available'
            ], 400);
        }

        // ── Resolve variant price adjustment ──────────────────────────────
$selectedVariants = $request->input('selected_variants', []);
$priceAdjustment  = 0;
$variantLabel     = '';

if (!empty($selectedVariants)) {
    foreach ($selectedVariants as $variantName => $variantValue) {
        $variantRow = ProductVariant::where('product_id', $product->id)
            ->whereRaw('LOWER(TRIM(variant_name))  = ?', [strtolower(trim($variantName))])
            ->whereRaw('LOWER(TRIM(variant_value)) = ?', [strtolower(trim($variantValue))])
            ->first();

        if ($variantRow) {
            $priceAdjustment += (float) $variantRow->price_adjustment;
        } else {
            // Log mismatches so you can diagnose in storage/logs/laravel.log
            \Log::warning('CartController: variant not found', [
                'product_id'    => $product->id,
                'variant_name'  => $variantName,
                'variant_value' => $variantValue,
            ]);
        }
    }

    $variantLabel = collect($selectedVariants)
        ->map(fn($v, $k) => "{$k}: {$v}")
        ->implode(', ');
}

        $basePrice    = (float) ($product->sale_price ?? $product->price);
        $finalPrice   = max(0, $basePrice + $priceAdjustment);
      
        $cart = session()->get('cart', []);

        // Cart key: product_id + variant fingerprint so different variants are separate line items
        $variantKey  = !empty($selectedVariants)
            ? md5(json_encode($selectedVariants))
            : 'default';
        $cartKey     = $product->id . '_' . $variantKey;

        if (isset($cart[$cartKey])) {
            $newQuantity = $cart[$cartKey]['quantity'] + $request->quantity;
            
            if ($newQuantity > $product->stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot add more than available stock'
                ], 400);
            }
            
            $cart[$cartKey]['quantity'] = $newQuantity;
        } else {
            $cart[$cartKey] = [
                'id'               => $product->id,
                'cart_key'         => $cartKey,
                'name'             => $product->name,
                'slug'             => $product->slug,
                'price'            => $finalPrice,
                'base_price'       => $basePrice,
                'price_adjustment' => $priceAdjustment,
                'quantity'         => $request->quantity,
                'image'            => $product->main_image,
                'stock'            => $product->stock,
                // Variant info
                'variant_options'  => $selectedVariants,      // ['Size' => 'XL']
                'variant_label'    => $variantLabel,          // 'Size: XL, Color: Red'
            ];
        }

        session()->put('cart', $cart);
        $cartTotal = $this->cartTotalService->calculateSubtotal($cart);
        session()->put('cart_total', $cartTotal);

        return response()->json([
            'success'     => true,
            'message'     => 'Product added to cart',
            'cart_count'  => count($cart),
            'cart_total'  => number_format($cartTotal, 2),
            'variant_label' => $variantLabel,
            'final_price' => number_format($finalPrice, 2),
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|string',   // now accepts "123_abc" cart keys
            'quantity'   => 'required|integer|min:1'
        ]);

        $cart    = session()->get('cart', []);
        $cartKey = $request->product_id;   // frontend sends the full cart key

        if (isset($cart[$cartKey])) {
            // Derive real product id (first segment before underscore)
            $productId = $cart[$cartKey]['id'];
            $product   = Product::findOrFail($productId);
            
            if ($request->quantity > $product->stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot exceed available stock'
                ], 400);
            }

            $cart[$cartKey]['quantity'] = $request->quantity;
            session()->put('cart', $cart);
            
            $cartTotal = $this->cartTotalService->calculateSubtotal($cart);
            session()->put('cart_total', $cartTotal);

            $deliveryFee = null;
            if (Auth::check()) {
                $addressId = session()->get('selected_address_id');
                $address   = $addressId
                    ? Address::where('id', $addressId)->where('user_id', Auth::id())->first()
                    : Auth::user()->addresses()->where('is_default', true)->first();

                if ($address && $address->delivery_zone) {
                    $deliveryFee = $this->cartTotalService->calculateDeliveryFee($cart, $address->delivery_zone);
                }
            }

            $grandTotal = $this->cartTotalService->calculateGrandTotal($cart, $deliveryFee);

            return response()->json([
                'success'      => true,
                'message'      => 'Cart updated',
                'cart_count'   => count($cart),
                'cart_total'   => number_format($cartTotal, 2),
                'item_total'   => number_format($cart[$cartKey]['price'] * $request->quantity, 2),
                'subtotal'     => number_format($cartTotal, 2),
                'delivery_fee' => $deliveryFee,
                'grand_total'  => number_format($grandTotal, 2),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Product not found in cart'
        ], 404);
    }

    /**
     * Remove product from cart
     */
    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|string'
        ]);

        $cart    = session()->get('cart', []);
        $cartKey = $request->product_id;

        if (isset($cart[$cartKey])) {
            unset($cart[$cartKey]);
            session()->put('cart', $cart);
            
            $cartTotal = $this->cartTotalService->calculateSubtotal($cart);
            session()->put('cart_total', $cartTotal);

            return response()->json([
                'success'    => true,
                'message'    => 'Product removed from cart',
                'cart_count' => count($cart),
                'cart_total' => number_format($cartTotal, 2)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Product not found in cart'
        ], 404);
    }

    /**
     * Clear entire cart
     */
    public function clear()
    {
        session()->forget(['cart', 'cart_total', 'selected_address_id']);

        return response()->json([
            'success'    => true,
            'message'    => 'Cart cleared',
            'cart_count' => 0,
            'cart_total' => '0.00'
        ]);
    }

    /**
     * Get cart sidebar HTML
     */
    public function sidebar()
    {
        $cart      = session()->get('cart', []);
        $cartTotal = session()->get('cart_total', 0);
        
        return view('partials.cart-sidebar', compact('cart', 'cartTotal'));
    }

    /**
     * Update selected shipping address in cart
     */
    public function updateAddress(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id'
        ]);

        $address = Address::where('id', $request->address_id)
                          ->where('user_id', Auth::id())
                          ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found or does not belong to you'
            ], 404);
        }

        session()->put('selected_address_id', $request->address_id);

        $cart        = session()->get('cart', []);
        $deliveryFee = null;

        if ($address->delivery_zone && count($cart) > 0) {
            $deliveryFee = $this->cartTotalService->calculateDeliveryFee($cart, $address->delivery_zone);
        }

        $cartTotal  = $this->cartTotalService->calculateSubtotal($cart);
        $grandTotal = $this->cartTotalService->calculateGrandTotal($cart, $deliveryFee);

        return response()->json([
            'success' => true,
            'message' => 'Shipping address updated successfully',
            'address' => [
                'id'            => $address->id,
                'address'       => $address->address,
                'city'          => $address->city,
                'state'         => $address->state,
                'postal_code'   => $address->postal_code,
                'country'       => $address->country,
                'delivery_zone' => $address->delivery_zone,
                'is_default'    => $address->is_default,
            ],
            'delivery_fee' => $deliveryFee,
            'grand_total'  => number_format($grandTotal, 2),
        ]);
    }

    /**
     * Get selected or default address
     */
    public function getSelectedAddress()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $selectedAddressId = session()->get('selected_address_id');
        
        if ($selectedAddressId) {
            $address = Address::where('id', $selectedAddressId)
                              ->where('user_id', Auth::id())
                              ->first();
        } else {
            $address = Auth::user()->addresses()->where('is_default', true)->first();
        }

        if (!$address && Auth::user()->addresses()->count() > 0) {
            $address = Auth::user()->addresses()->first();
        }

        if (!$address) {
            return response()->json(['success' => false, 'message' => 'No address found'], 404);
        }

        return response()->json([
            'success' => true,
            'address' => [
                'id'          => $address->id,
                'address'     => $address->address,
                'city'        => $address->city,
                'state'       => $address->state,
                'postal_code' => $address->postal_code,
                'country'     => $address->country,
                'is_default'  => $address->is_default
            ]
        ]);
    }

    /**
     * AJAX — delivery fee for a specific address
     */
    public function getDeliveryFee(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $request->validate(['address_id' => 'required|exists:addresses,id']);

        $address = Address::where('id', $request->address_id)
                          ->where('user_id', Auth::id())
                          ->first();

        if (!$address) {
            return response()->json(['success' => false, 'message' => 'Address not found'], 404);
        }

        $cart      = session()->get('cart', []);
        $cartTotal = $this->cartTotalService->calculateSubtotal($cart);

        if (!$address->delivery_zone || count($cart) === 0) {
            return response()->json([
                'success'      => true,
                'delivery_fee' => null,
                'grand_total'  => number_format($cartTotal, 2),
                'message'      => 'Delivery fee unavailable — ensure your address has a zone set.',
            ]);
        }

        $deliveryFee = $this->cartTotalService->calculateDeliveryFee($cart, $address->delivery_zone);
        $grandTotal  = $this->cartTotalService->calculateGrandTotal($cart, $deliveryFee);

        return response()->json([
            'success'      => true,
            'delivery_fee' => $deliveryFee,
            'grand_total'  => number_format($grandTotal, 2),
        ]);
    }

    /**
     * Get cart count
     */
    public function count()
    {
        $cart      = session()->get('cart', []);
        $cartTotal = session()->get('cart_total', 0);
        
        return response()->json([
            'cart_count' => count($cart),
            'cart_total' => number_format($cartTotal, 2)
        ]);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|max:50',
        ]);

        $code = strtoupper(trim($request->coupon_code));
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Your cart is empty.'], 400);
        }

        $coupon = \App\Models\Coupon::with('product:id,name')
            ->where('code', $code)
            ->active()
            ->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired coupon code.'], 400);
        }

        $shop = \App\Models\Shop::where('seller_id', $coupon->seller_id)->first();

        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'This coupon is no longer available.'], 400);
        }

        $productIds   = array_map(fn($item) => $item['id'], $cart);
        $shopProducts = \App\Models\Product::whereIn('id', $productIds)
            ->where('shop_id', $shop->id)
            ->pluck('shop_id', 'id');

        $eligibleItems = [];
        foreach ($cart as $cartKey => $item) {
            if (!isset($shopProducts[$item['id']])) {
                continue;
            }
            if ($coupon->product_id !== null && (int) $coupon->product_id !== (int) $item['id']) {
                continue;
            }
            $eligibleItems[$cartKey] = $item;
        }

        if (empty($eligibleItems)) {
            $scopeLabel = $coupon->product_id
                ? 'the specific product this coupon applies to'
                : 'any products from this seller';

            return response()->json([
                'success' => false,
                'message' => "Your cart doesn't contain {$scopeLabel}.",
            ], 400);
        }

        $applicableSubtotal = collect($eligibleItems)->sum(fn($i) => $i['price'] * $i['quantity']);

        $userId     = auth()->id() ?? 0;
        $validation = $coupon->validate($userId, $applicableSubtotal);

        if (!$validation['valid']) {
            return response()->json(['success' => false, 'message' => $validation['message']], 400);
        }

        $discount = $coupon->calculateDiscount($applicableSubtotal);

        session()->put('applied_coupon', [
            'id'          => $coupon->id,
            'code'        => $coupon->code,
            'discount'    => $discount,
            'type'        => $coupon->type,
            'value'       => $coupon->value,
            'scope'       => $coupon->scope_label,
            'description' => $coupon->description,
        ]);

        $cartTotal   = $this->cartTotalService->calculateSubtotal($cart);
        $deliveryFee = null;

        if (Auth::check()) {
            $addressId = session()->get('selected_address_id');
            $address   = $addressId
                ? \App\Models\Address::where('id', $addressId)->where('user_id', Auth::id())->first()
                : Auth::user()->addresses()->where('is_default', true)->first();

            if ($address && $address->delivery_zone) {
                $deliveryFee = $this->cartTotalService->calculateDeliveryFee($cart, $address->delivery_zone);
            }
        }

        $grandTotal = $this->cartTotalService->calculateGrandTotal($cart, $deliveryFee) - $discount;

        return response()->json([
            'success'        => true,
            'message'        => '🎉 Coupon applied! You saved ₦' . number_format($discount, 2),
            'coupon_code'    => $coupon->code,
            'discount_label' => $coupon->discount_label,
            'discount'       => number_format($discount, 2),
            'scope'          => $coupon->scope_label,
            'subtotal'       => number_format($cartTotal, 2),
            'grand_total'    => number_format(max(0, $grandTotal), 2),
        ]);
    }

    public function removeCoupon()
    {
        session()->forget('applied_coupon');

        $cart      = session()->get('cart', []);
        $cartTotal = $this->cartTotalService->calculateSubtotal($cart);

        $deliveryFee = null;
        if (Auth::check()) {
            $addressId = session()->get('selected_address_id');
            $address   = $addressId
                ? \App\Models\Address::where('id', $addressId)->where('user_id', Auth::id())->first()
                : Auth::user()->addresses()->where('is_default', true)->first();

            if ($address && $address->delivery_zone) {
                $deliveryFee = $this->cartTotalService->calculateDeliveryFee($cart, $address->delivery_zone);
            }
        }

        $grandTotal = $this->cartTotalService->calculateGrandTotal($cart, $deliveryFee);

        return response()->json([
            'success'     => true,
            'message'     => 'Coupon removed.',
            'subtotal'    => number_format($cartTotal, 2),
            'grand_total' => number_format($grandTotal, 2),
        ]);
    }
}