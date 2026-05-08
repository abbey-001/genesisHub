<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Services\DeliveryService;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $seller = Auth::guard('seller')->user()->seller;

        $query = Order::with(['items' => function ($q) use ($seller) {
            $q->where('seller_id', $seller->id)->with('product');
        }, 'user'])
        ->whereHas('items', function ($q) use ($seller) {
            $q->where('seller_id', $seller->id);
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                  ->orWhere('customer_name', 'like', '%' . $search . '%')
                  ->orWhere('customer_email', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status'))         $query->where('status', $request->status);
        if ($request->filled('payment_status')) $query->where('payment_status', $request->payment_status);
        if ($request->filled('date_from'))      $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))        $query->whereDate('created_at', '<=', $request->date_to);

        $orders = $query->latest()->paginate(20);

        return view('seller.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $seller = Auth::guard('seller')->user()->seller;

        $hasItems = $order->items()->where('seller_id', $seller->id)->exists();

        if (!$hasItems) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->load([
            'items' => function ($q) use ($seller) {
                $q->where('seller_id', $seller->id)->with('product');
            },
            'user',
        ]);

        $commissionRate = config('platform.commission_rate');

        $sellerTotal = $order->items->sum(function ($item) use ($commissionRate) {
            return $item->total_price * (1 - $commissionRate);
        });

        return view('seller.orders.show', compact('order', 'sellerTotal'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $seller = Auth::guard('seller')->user()->seller;

        $validated = $request->validate([
            'status'  => 'required|in:pending,processing,shipped,delivered,cancelled',
            'item_id' => 'required|exists:order_items,id',
        ]);

        $item = $order->items()
            ->where('id', $validated['item_id'])
            ->where('seller_id', $seller->id)
            ->firstOrFail();

        $item->update(['status' => $validated['status']]);

        if ($validated['status'] === 'shipped' && !$order->shipped_at) {
            $order->update(['shipped_at' => now()]);
        } elseif ($validated['status'] === 'delivered' && !$order->delivered_at) {
            $order->update(['delivered_at' => now()]);
        } elseif ($validated['status'] === 'cancelled' && !$order->cancelled_at) {
            $order->update(['cancelled_at' => now()]);
        }

        return back()->with('success', 'Order status updated successfully.');
    }

    public function invoice(Order $order)
    {
        $seller = Auth::guard('seller')->user()->seller;

        $hasItems = $order->items()->where('seller_id', $seller->id)->exists();

        if (!$hasItems) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->load([
            'items' => function ($q) use ($seller) {
                $q->where('seller_id', $seller->id)->with('product');
            },
            'user',
        ]);

        $sellerTotal = $order->items->sum('total_price');

        return view('seller.orders.invoice', compact('order', 'sellerTotal', 'seller'));
    }

    // =========================================================================
    // MARK ITEM READY FOR PICKUP
    // =========================================================================

    /**
     * Seller marks one of their items as packaged and ready for collection.
     *
     * Changed from original:
     *  - In-stock items are checked against the platform deadline (expected_ready_by).
     *    If overdue, admin is notified but the seller can still mark ready.
     *  - Once ALL of a seller's items for this order are ready, DeliveryService
     *    is called. It now waits until every seller in the order is ready before
     *    broadcasting to riders (wait-for-all strategy).
     */
    public function markItemReady(Request $request, OrderItem $item)
    {
        $seller = Auth::guard('seller')->user()->seller()->with('shop')->first();

        if ($item->seller_id !== $seller->id) {
            abort(403);
        }

        $validated = $request->validate([
            'package_weight' => 'required|numeric|min:0.1|max:500',
            'package_notes'  => 'nullable|string|max:500',
        ]);

        // ── Overdue check ──────────────────────────────────────────────────
        // If an in-stock item is past its expected_ready_by date, flag it for
        // admin review. We still allow the seller to proceed — blocking them
        // would make the situation worse for the buyer.
        if ($item->expected_ready_by && $item->isOverdue()) {
            $this->notifyAdminOfOverdueItem($item, $seller);

            Log::warning('Seller marked item ready after deadline', [
                'order_item_id'    => $item->id,
                'order_id'         => $item->order_id,
                'seller_id'        => $seller->id,
                'expected_ready_by'=> $item->expected_ready_by->toDateString(),
                'marked_ready_at'  => now()->toDateTimeString(),
                'days_late'        => abs($item->days_until_deadline),
                'fulfillment_type' => $item->fulfillment_type,
            ]);
        }

        try {
            DB::beginTransaction();

            $item->update([
                'status'         => 'ready_for_pickup',
                'package_weight' => $validated['package_weight'],
                'package_notes'  => $validated['package_notes'],
                'ready_at'       => now(),
            ]);

            // Re-fetch all this seller's items for this order within the transaction
            // so the check sees the just-updated status.
            $order       = $item->order;
            $sellerItems = $order->items()->where('seller_id', $seller->id)->get();
            $allReady    = $sellerItems->every(fn($i) => $i->status === 'ready_for_pickup');

            $result = null;

            if ($allReady) {
                // All this seller's items are ready — hand off to DeliveryService.
                // It will create the Delivery record and check if every other seller
                // in the order is also ready before broadcasting to riders.
                $deliveryService = app(DeliveryService::class);
                $result          = $deliveryService->handleSellerReady($order, $seller, $sellerItems);
            }

            DB::commit();

            if ($result) {
                return back()->with('success', $result['message']);
            }

            // Not all of this seller's items are ready yet.
            $remaining = $sellerItems->where('status', '!=', 'ready_for_pickup')->count();
            return back()->with('success',
                "Item marked as ready! You have {$remaining} more item(s) to prepare before a rider can be assigned."
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark item ready', [
                'order_item_id' => $item->id,
                'seller_id'     => $seller->id,
                'error'         => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to mark item ready: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Notify admin when a seller marks an item ready after its deadline.
     * Uses the database notification channel so admins see it in their dashboard.
     */
    private function notifyAdminOfOverdueItem(OrderItem $item, $seller): void
    {
        try {
            $admins = \App\Models\Admin::all();

            foreach ($admins as $admin) {
                $admin->notify(
                    new \App\Notifications\SellerReadyAfterDeadline($item, $seller)
                );
            }
        } catch (\Exception $e) {
            // Never let a notification failure block the seller from marking ready.
            Log::error('Failed to notify admin of overdue item', [
                'order_item_id' => $item->id,
                'error'         => $e->getMessage(),
            ]);
        }
    }
}