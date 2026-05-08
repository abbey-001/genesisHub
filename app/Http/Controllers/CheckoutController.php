<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    /**
     * Display checkout page
     */
    public function index()
    {
        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty');
        }
        
        $cartTotal = $this->calculateCartTotal($cart);
        
        $categoriesWithSubs = Category::select('id', 'name', 'slug', 'image')
            ->with(['subcategories' => fn($q) => 
                $q->select('id', 'category_id', 'name', 'slug')
                  ->orderBy('sort_order')
                  ->limit(10)
            ])
            ->limit(10)
            ->get();
        
        return view('checkout.index', compact('cart', 'cartTotal', 'categoriesWithSubs'));
    }
    
    /**
     * Process checkout
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'notes' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:bank_transfer,check,cash_on_delivery',
            'terms' => 'required|accepted',
        ]);
        
        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty');
        }
        
        try {
            DB::beginTransaction();
            
            // Calculate totals
            $subtotal = 0;
            foreach ($cart as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            
            $shipping = $subtotal >= 200 ? 0 : 10;
            $tax = $subtotal * 0.10;
            $total = $subtotal + $shipping + $tax;
            
            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'customer_name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'customer_email' => $validated['email'],
                'customer_phone' => $validated['phone'],
                'shipping_address' => $validated['address_line1'] . ' ' . ($validated['address_line2'] ?? ''),
                'shipping_city' => $validated['city'],
                'shipping_state' => $validated['state'],
                'shipping_postal_code' => $validated['postal_code'],
                'shipping_country' => $validated['country'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_fee' => $shipping,
                'discount' => 0,
                'total' => $total,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'],
            ]);
            
            // Create order items
            foreach ($cart as $productId => $item) {
                $product = Product::find($productId);
                
                if (!$product) {
                    throw new \Exception("Product {$productId} not found");
                }

                // Validate stock availability
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }
                
                // Ensure seller_id is set
                $sellerId = $product->shop_id;
                if (!$sellerId) {
                    throw new \Exception("Product {$product->name} does not have a seller assigned");
                }
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'seller_id' => $sellerId,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $product->sale_price ?? $product->price,
                    'total' => ($product->sale_price ?? $product->price) * $item['quantity'],
                    'status' => 'pending',
                ]);
                
                // Update product stock
                $product->decrement('stock', $item['quantity']);
                $product->increment('sold_count', $item['quantity']);
            }
            
            DB::commit();
            
            // Clear cart
            session()->forget(['cart', 'cart_total']);
            
            return redirect()->route('checkout.success', $order->id)
                ->with('success', 'Order placed successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to process order. Please try again.');
        }
    }
    
    /**
     * Show order success page
     */
    public function success($orderId)
    {
        $order = Order::with('items.product')
            ->where('user_id', auth()->id())
            ->findOrFail($orderId);
        
        $categoriesWithSubs = Category::select('id', 'name', 'slug', 'image')
            ->with(['subcategories' => fn($q) => 
                $q->select('id', 'category_id', 'name', 'slug')
                  ->orderBy('sort_order')
                  ->limit(10)
            ])
            ->limit(10)
            ->get();
        
        return view('checkout.success', compact('order', 'categoriesWithSubs'));
    }
    
    /**
     * Calculate cart total
     */
    private function calculateCartTotal(array $cart): float
    {
        $total = 0;
        
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        return $total;
    }
}