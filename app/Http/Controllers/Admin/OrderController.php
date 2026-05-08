<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Shop;
use App\Services\AdminOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(AdminOrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display all orders
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.product', 'deliveries']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by amount range
        if ($request->filled('min_amount')) {
            $query->where('total', '>=', $request->min_amount);
        }
        if ($request->filled('max_amount')) {
            $query->where('total', '<=', $request->max_amount);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $orders = $query->paginate(20);

        // Statistics
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total'),
            'pending_payment' => Order::where('payment_status', 'pending')->sum('total'),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'today_revenue' => Order::whereDate('created_at', today())
                ->where('payment_status', 'paid')
                ->sum('total'),
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    /**
     * Show order details
     */
    public function show(Order $order)
    {
        $order->load([
            'user',
            'items.product',
            'items.seller',
            'deliveries.rider',
            'deliveries.items'
        ]);

        // Calculate commission
        $platformCommission = $this->orderService->calculatePlatformCommission($order);

        // Order timeline
        $timeline = $this->orderService->getOrderTimeline($order);

        return view('admin.orders.show', compact('order', 'platformCommission', 'timeline'));
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->orderService->updateOrderStatus($order, $request->status, $request->notes);

            return back()->with('success', 'Order status updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update order status: ' . $e->getMessage());
        }
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $oldStatus = $order->payment_status;

            $order->update([
                'payment_status' => $request->payment_status,
                'paid_at' => $request->payment_status === 'paid' ? now() : $order->paid_at,
            ]);

            // Log the change
            activity()
                ->performedOn($order)
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $request->payment_status,
                    'notes' => $request->notes,
                ])
                ->log('Payment status updated');

            return back()->with('success', 'Payment status updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update payment status: ' . $e->getMessage());
        }
    }

    /**
     * Process refund
     */
    public function refund(Request $request, Order $order)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0|max:' . $order->total,
            'reason' => 'required|string|max:500',
        ]);

        if ($order->payment_status !== 'paid') {
            return back()->with('error', 'Can only refund paid orders.');
        }

        try {
            $this->orderService->processRefund($order, $request->amount, $request->reason);

            return back()->with('success', 'Refund processed successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process refund: ' . $e->getMessage());
        }
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, Order $order)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if (!$order->canBeCancelled()) {
            return back()->with('error', 'This order cannot be cancelled.');
        }

        try {
            $this->orderService->cancelOrder($order, $request->reason);

            return back()->with('success', 'Order cancelled successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }

    /**
     * Print invoice
     */
    public function invoice(Order $order)
    {
        $order->load(['items.product', 'user']);

        return view('admin.orders.invoice', compact('order'));
    }

    /**
     * Export orders
     */
    public function export(Request $request)
    {
        $query = Order::with(['user', 'items']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->get();

        $filename = 'orders_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Order #', 'Date', 'Customer', 'Email', 'Phone', 
                'Items', 'Subtotal', 'Tax', 'Shipping', 'Total', 
                'Payment Method', 'Payment Status', 'Order Status'
            ]);
            
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->created_at->format('Y-m-d H:i'),
                    $order->customer_name,
                    $order->customer_email,
                    $order->customer_phone,
                    $order->items->count(),
                    $order->subtotal,
                    $order->tax,
                    $order->shipping_fee,
                    $order->total,
                    ucfirst(str_replace('_', ' ', $order->payment_method)),
                    ucfirst($order->payment_status),
                    ucfirst($order->status),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:mark_processing,mark_shipped,mark_delivered,cancel,export',
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
        ]);

        $orders = Order::whereIn('id', $request->order_ids)->get();

        try {
            DB::beginTransaction();

            foreach ($orders as $order) {
                switch ($request->action) {
                    case 'mark_processing':
                        if ($order->status === 'pending') {
                            $this->orderService->updateOrderStatus($order, 'processing', 'Bulk action');
                        }
                        break;
                    case 'mark_shipped':
                        if (in_array($order->status, ['pending', 'processing'])) {
                            $this->orderService->updateOrderStatus($order, 'shipped', 'Bulk action');
                        }
                        break;
                    case 'mark_delivered':
                        if ($order->status === 'shipped') {
                            $this->orderService->updateOrderStatus($order, 'delivered', 'Bulk action');
                        }
                        break;
                    case 'cancel':
                        if ($order->canBeCancelled()) {
                            $this->orderService->cancelOrder($order, 'Bulk cancellation');
                        }
                        break;
                }
            }

            DB::commit();

            return back()->with('success', 'Bulk action completed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Bulk action failed: ' . $e->getMessage());
        }
    }

    /**
     * Order analytics
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', 'month'); // day, week, month, year

        $analytics = $this->orderService->getOrderAnalytics($period);

        return view('admin.orders.analytics', compact('analytics', 'period'));
    }
}