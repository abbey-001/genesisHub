<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Category;
use App\Models\Order;
use App\Models\Review;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PaymentController;

class AccountController extends Controller
{

    public function index()
    {
        $categoriesWithSubs = Category::select('id', 'name', 'slug', 'image')
                    ->with(['subcategories' => fn($q) => 
                        $q->select('id', 'category_id', 'name', 'slug')
                          ->orderBy('sort_order')
                          ->limit(10)
                    ])
                    ->limit(10)
                    ->get();

        $deliveryZones = \App\Models\DeliveryZone::deliveryZones();

        return view('account.index', compact('categoriesWithSubs', 'deliveryZones'));
    }

    public function getProfile()
    {
        $user = Auth::user();
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ]);
    }

public function updateProfile(Request $request)
{
    $user = Auth::user();

    $validated = $request->validate([
        'name'  => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'phone' => 'nullable|string|max:20',
    ]);

    $user->fill($validated)->save();

    // Return ONLY the fields the frontend actually needs —
    // never return the full Eloquent model to avoid leaking
    // password hash, remember_token, etc.
    return response()->json([
        'success' => true,
        'message' => 'Profile updated successfully',
        'user' => [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ],
    ]);
}


    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ]);
    }

public function getOrders()
{
    try {
        $user = auth()->user();
        $orders = Order::where('user_id', $user->id)
            ->with(['items.product.images', 'items.seller'])
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedOrders = $orders->map(function($order) {
            // Get all items with details
            $items = $order->items->map(function($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_image' => $item->product && $item->product->images->first() 
                        ? $item->product->images->first()->image_url 
                        : null,
                    'quantity' => $item->quantity,
                    'price' => number_format($item->price, 2),
                    'total_price' => number_format($item->total_price, 2),
                    'status' => $item->status,
                    'seller_id' => $item->seller_id,
                    'seller_name' => $item->seller ? $item->seller->name : 'Unknown Seller',
                ];
            });

            // Get unique seller count
            $sellerCount = $order->items->pluck('seller_id')->unique()->count();

            // Calculate delivery progress
            $totalItems = $order->items->count();
            $deliveredItems = $order->items->where('status', 'delivered')->count();
            $deliveryProgress = $totalItems > 0 ? round(($deliveredItems / $totalItems) * 100) : 0;

            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'date' => $order->created_at->format('M d, Y'),
                'payment_status' => ucfirst($order->payment_status),
                'status' => ucfirst($order->status),
                'total' => number_format($order->total, 2),
                'items' => $items,
                'item_count' => $totalItems,
                'seller_count' => $sellerCount,
                'delivery_progress' => $deliveryProgress,
                'is_multi_seller' => $sellerCount > 1,
                'can_cancel' => $order->canBeCancelled(),
            ];
        });

        return response()->json($formattedOrders);
    } catch (\Exception $e) {
        \Log::error('Failed to load orders: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to load orders: ' . $e->getMessage()], 500);
    }
}

    public function getAddresses()
    {
        $user = Auth::user();
        $addresses = $user->addresses()->get();

        return response()->json($addresses);
    }

    public function storeAddress(Request $request)
    {
        $validated = $request->validate([
            'address'       => 'required|string|max:255',
            'city'          => 'required|string|max:255',
            'state'         => 'nullable|string|max:255',
            'postal_code'   => 'required|string|max:20',
            'country'       => 'required|string|max:255',
            'delivery_zone' => 'required|string|exists:delivery_zones,delivery_zone',
            'is_default'    => 'boolean',
        ]);

        $user = Auth::user();

        // If setting as default, unset other defaults
        if ($request->boolean('is_default')) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Address added successfully',
            'address' => $address,
        ]);
    }

    public function updateAddress(Request $request, Address $address)
    {
        // Check if user owns this address
        if ($address->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'address'       => 'required|string|max:255',
            'city'          => 'required|string|max:255',
            'state'         => 'nullable|string|max:255',
            'postal_code'   => 'required|string|max:20',
            'country'       => 'required|string|max:255',
            'delivery_zone' => 'required|string|exists:delivery_zones,delivery_zone',
            'is_default'    => 'boolean',
        ]);

        // If setting as default, unset other defaults
        if ($request->boolean('is_default')) {
            Auth::user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully',
            'address' => $address,
        ]);
    }

    public function deleteAddress(Address $address)
    {
        // Check if user owns this address
        if ($address->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully',
        ]);
    }

    public function setDefaultAddress(Request $request, Address $address)
    {
        // Check if user owns this address
        if ($address->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        Auth::user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default address updated successfully',
            'address' => $address,
        ]);
    }

    public function showOrder($id)
    {
        try {
            $user = auth()->user();
            $order = Order::where('user_id', $user->id)
                ->with(['items.product.images', 'items.seller'])
                ->findOrFail($id);

            $categoriesWithSubs = Category::select('id', 'name', 'slug', 'image')
                ->with(['subcategories' => fn($q) => 
                    $q->select('id', 'category_id', 'name', 'slug')
                      ->orderBy('sort_order')
                      ->limit(10)
                ])
                ->limit(10)
                ->get();

            return view('account.order-detail', compact('order', 'categoriesWithSubs'));
        } catch (\Exception $e) {
            return redirect()->route('account.index')->with('error', 'Order not found');
        }
    }

public function cancelOrder($id)
{
    DB::beginTransaction();
    try {
        $user  = auth()->user();
        $order = Order::where('user_id', $user->id)
            ->with(['items', 'deliveries'])
            ->findOrFail($id);

        if (!$order->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be cancelled at this stage.',
            ], 400);
        }

        // Cancel the order
        $order->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Cancel all order items
        $order->items()->update(['status' => 'cancelled']);

        // Cancel associated deliveries
        $order->deliveries()
            ->whereIn('status', ['pending', 'assigned'])
            ->update([
                'status'         => 'cancelled',
                'failed_at'      => now(),
                'failure_reason' => 'customer_cancelled',
                'failure_notes'  => 'Order cancelled by customer',
            ]);

        DB::table('delivery_items')
            ->whereIn('delivery_id', $order->deliveries->pluck('id'))
            ->update(['updated_at' => now()]);

        // Restore product stock
        foreach ($order->items as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
                $item->product->decrement('sold_count', $item->quantity);
            }
        }

        DB::commit();

        // ── REFUND ────────────────────────────────────────────────
        // Trigger a Paystack refund only when the order was actually
        // paid. Unpaid/pending orders just get cancelled with no refund.
        $refundResult = ['initiated' => false];

        if ($order->payment_status === 'paid' && $order->payment_reference) {
            try {
                $paymentController = app(\App\Http\Controllers\PaymentController::class);
                $refundResult      = $paymentController->initiateRefund(
                    $order->payment_reference,
                    (int) round($order->total * 100), // kobo
                    "Order {$order->order_number} cancelled by customer"
                );

                if ($refundResult['success']) {
                    $order->update([
                        'payment_status' => 'refund_pending',
                        'refund_reference'=> $refundResult['refund_reference'] ?? null,
                    ]);

                    try {
                        app(\App\Services\Telegram\AdminTelegramService::class)->notifyRefundRequest($order);
                    } catch (\Exception $e) {
                        \Log::warning('Admin Telegram refund request alert failed', [
                            'order_id' => $order->id,
                            'error'    => $e->getMessage(),
                        ]);
                    }

                    \Log::info('Refund initiated for cancelled order', [
                        'order_id'         => $order->id,
                        'refund_reference' => $refundResult['refund_reference'] ?? null,
                    ]);
                } else {
                    // Refund call failed — flag for manual review, don't block cancellation
                    \Log::error('Refund initiation failed for cancelled order', [
                        'order_id' => $order->id,
                        'reason'   => $refundResult['message'] ?? 'Unknown',
                    ]);
                }
            } catch (\Exception $e) {
                // Never block cancellation because of a refund error
                \Log::error('Exception during refund initiation', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }
        // ── END REFUND ────────────────────────────────────────────

        $message = 'Order cancelled successfully.';
        if ($order->payment_status === 'refund_pending') {
            $message .= ' Your refund is being processed and will appear within 5–10 business days.';
        } elseif ($order->payment_status === 'paid' && !($refundResult['success'] ?? false)) {
            $message .= ' Our team will process your refund manually — please contact support if you don\'t hear back within 48 hours.';
        }

        return response()->json([
            'success'        => true,
            'message'        => $message,
            'refund_pending' => $order->payment_status === 'refund_pending',
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        \Log::error('Failed to cancel order', [
            'order_id' => $id,
            'error'    => $e->getMessage(),
            'trace'    => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to cancel order: ' . $e->getMessage(),
        ], 500);
    }
}


    // Track Order
    public function trackOrder($id)
    {
        try {
            $user = auth()->user();
            $order = Order::where('user_id', $user->id)
                ->with(['items.deliveryItems.delivery.rider', 'deliveries.items.orderItem'])
                ->findOrFail($id);

            // Get tracking information
            $trackingData = [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'created_at' => $order->created_at->format('M d, Y h:i A'),
                'paid_at' => $order->paid_at ? $order->paid_at->format('M d, Y h:i A') : null,
                'shipped_at' => $order->shipped_at ? $order->shipped_at->format('M d, Y h:i A') : null,
                'delivered_at' => $order->delivered_at ? $order->delivered_at->format('M d, Y h:i A') : null,
                'cancelled_at' => $order->cancelled_at ? $order->cancelled_at->format('M d, Y h:i A') : null,
                'deliveries' => $order->deliveries->map(function($delivery) {
                    return [
                        'id' => $delivery->id,
                        'status' => $delivery->status,
                        'status_label' => $delivery->status_label,
                        'rider_name' => $delivery->rider ? $delivery->rider->name : 'Not assigned',
                        'rider_phone' => $delivery->rider ? $delivery->rider->phone : null,
                        'assigned_at' => $delivery->assigned_at ? $delivery->assigned_at->format('M d, Y h:i A') : null,
                        'picked_up_at' => $delivery->picked_up_at ? $delivery->picked_up_at->format('M d, Y h:i A') : null,
                        'delivered_at' => $delivery->delivered_at ? $delivery->delivered_at->format('M d, Y h:i A') : null,
                        'failed_at' => $delivery->failed_at ? $delivery->failed_at->format('M d, Y h:i A') : null,
                        'failure_reason' => $delivery->failure_reason,
                        'items' => $delivery->items->map(function($item) {
                            return [
                                'product_name' => $item->product_name,
                                'quantity' => $item->quantity,
                            ];
                        }),
                    ];
                }),
            ];

            return response()->json($trackingData);
        } catch (\Exception $e) {
            \Log::error('Failed to load tracking information: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load tracking information: ' . $e->getMessage()], 500);
        }
    }

    // Get items available for review
    public function getReviewableItems()
    {
        try {
            $user = auth()->user();
            
            // Get delivered order items that haven't been reviewed yet
            $items = OrderItem::whereHas('order', function($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->where('status', 'delivered');
                })
                ->whereDoesntHave('order.reviews', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['product.images', 'order'])
                ->get();

            $formattedItems = $items->map(function($item) {
                return [
                    'order_item_id' => $item->id,
                    'order_id' => $item->order_id,
                    'order_number' => $item->order->order_number,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_image' => $item->product->images->first()?->image_url ?? '/images/placeholder.png',
                    'delivered_at' => $item->order->delivered_at?->format('M d, Y'),
                ];
            });

            return response()->json($formattedItems);
        } catch (\Exception $e) {
            \Log::error('Failed to load reviewable items: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load reviewable items: ' . $e->getMessage()], 500);
        }
    }

    // Submit a review
    public function submitReview(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        $user = auth()->user();

        // Verify order belongs to user and is delivered
        $order = Order::where('id', $validated['order_id'])
            ->where('user_id', $user->id)
            ->where('status', 'delivered')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid order or order not yet delivered',
            ], 400);
        }

        // Check if already reviewed
        $existingReview = Review::where('order_id', $validated['order_id'])
            ->where('product_id', $validated['product_id'])
            ->where('user_id', $user->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this product',
            ], 400);
        }

        // Create review
        $review = Review::create([
            'product_id' => $validated['product_id'],
            'user_id' => $user->id,
            'order_id' => $validated['order_id'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'is_verified_purchase' => true,
            'is_approved' => false, // Requires admin approval
            'status' => 'pending',
        ]);

        try {
            app(\App\Services\Telegram\AdminTelegramService::class)
                ->notifyNewReviewPending($review->loadMissing(['product.shop', 'user']));
        } catch (\Exception $e) {
            \Log::warning('Admin Telegram review alert failed', [
                'review_id' => $review->id,
                'error'     => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your review has been submitted and is pending approval.',
            'review' => $review,
        ]);
    }

    // Get user's reviews
    public function getReviews()
    {
        try {
            $user = auth()->user();
            $reviews = Review::where('user_id', $user->id)
                ->with(['product.images', 'order'])
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedReviews = $reviews->map(function($review) {
                return [
                    'id' => $review->id,
                    'product_name' => $review->product->name,
                    'product_image' => $review->product->images->first()?->image_url ?? '/images/placeholder.png',
                    'order_number' => $review->order->order_number,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'is_approved' => $review->is_approved,
                    'seller_response' => $review->seller_response,
                    'created_at' => $review->created_at->format('M d, Y'),
                ];
            });

            return response()->json($formattedReviews);
        } catch (\Exception $e) {
            \Log::error('Failed to load reviews: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load reviews: ' . $e->getMessage()], 500);
        }
    }
}
