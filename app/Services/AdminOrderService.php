<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class AdminOrderService
{
    /**
     * Update order status
     */
    public function updateOrderStatus(Order $order, $status, $notes = null)
    {
        DB::beginTransaction();
        try {
            $oldStatus = $order->status;
            $updates   = ['status' => $status];

            switch ($status) {
                case 'shipped':
                    $updates['shipped_at'] = now();
                    break;
                case 'delivered':
                    $updates['delivered_at'] = now();
                    break;
                case 'cancelled':
                    $updates['cancelled_at'] = now();
                    break;
            }

            $order->update($updates);

            activity()
                ->performedOn($order)
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'notes'      => $notes,
                ])
                ->log('Order status updated');

            // $order->user->notify(new OrderStatusUpdated($order));

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Process refund — marks the order for the refund page to handle.
     * Full seller-wallet deduction is handled by RefundController::process().
     */
    public function processRefund(Order $order, $amount, $reason)
    {
        DB::beginTransaction();
        try {
            $order->update([
                'payment_status' => 'refunded',
            ]);

            activity()
                ->performedOn($order)
                ->withProperties([
                    'amount' => $amount,
                    'reason' => $reason,
                ])
                ->log('Refund processed');

            // TODO: Actual payment gateway refund
            // $order->user->notify(new OrderRefunded($order, $amount));

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cancel order
     */
    public function cancelOrder(Order $order, $reason)
    {
        DB::beginTransaction();
        try {
            $order->update([
                'status'       => 'cancelled',
                'cancelled_at' => now(),
            ]);

            // Restore product stock
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                    $item->product->decrement('sold_count', $item->quantity);
                }
            }

            activity()
                ->performedOn($order)
                ->withProperties(['reason' => $reason])
                ->log('Order cancelled');

            // $order->user->notify(new OrderCancelled($order, $reason));

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate platform commission for an order.
     * Uses each seller's individual commission_rate if available, falls back to 10%.
     */
    public function calculatePlatformCommission(Order $order)
    {
        $commission = 0;

        foreach ($order->items as $item) {
            $rate        = $item->seller?->commission_rate ?? 10;
            $commission += $item->total_price * ($rate / 100);
        }

        return $commission;
    }

    /**
     * Get order timeline
     */
    public function getOrderTimeline(Order $order)
    {
        $timeline = [];

        $timeline[] = [
            'status'    => 'placed',
            'label'     => 'Order Placed',
            'timestamp' => $order->created_at,
            'icon'      => 'shopping-cart',
            'color'     => 'primary',
        ];

        if ($order->paid_at) {
            $timeline[] = [
                'status'    => 'paid',
                'label'     => 'Payment Confirmed',
                'timestamp' => $order->paid_at,
                'icon'      => 'credit-card',
                'color'     => 'success',
            ];
        }

        if ($order->shipped_at) {
            $timeline[] = [
                'status'    => 'shipped',
                'label'     => 'Order Shipped',
                'timestamp' => $order->shipped_at,
                'icon'      => 'truck',
                'color'     => 'info',
            ];
        }

        if ($order->delivered_at) {
            $timeline[] = [
                'status'    => 'delivered',
                'label'     => 'Order Delivered',
                'timestamp' => $order->delivered_at,
                'icon'      => 'check-circle',
                'color'     => 'success',
            ];
        }

        if ($order->cancelled_at) {
            $timeline[] = [
                'status'    => 'cancelled',
                'label'     => 'Order Cancelled',
                'timestamp' => $order->cancelled_at,
                'icon'      => 'x-circle',
                'color'     => 'danger',
            ];
        }

        return $timeline;
    }

    /**
     * Get order analytics for a given period.
     */
    public function getOrderAnalytics($period = 'month')
    {
        $startDate = match($period) {
            'day'   => now()->startOfDay(),
            'week'  => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year'  => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $totalOrders = Order::where('created_at', '>=', $startDate)->count();
        $totalRevenue = Order::where('created_at', '>=', $startDate)
            ->where('payment_status', 'paid')
            ->sum('total');

        // Guard against null — avg() returns null when there are no rows
        $avgOrderValue = Order::where('created_at', '>=', $startDate)
            ->where('payment_status', 'paid')
            ->avg('total') ?? 0;

        return [
            'total_orders'        => $totalOrders,
            'total_revenue'       => (float) $totalRevenue,
            'average_order_value' => (float) $avgOrderValue,
            'pending_orders'      => Order::where('created_at', '>=', $startDate)
                ->where('status', 'pending')->count(),
            'completed_orders'    => Order::where('created_at', '>=', $startDate)
                ->where('status', 'delivered')->count(),
            'cancelled_orders'    => Order::where('created_at', '>=', $startDate)
                ->where('status', 'cancelled')->count(),
            'top_customers'       => $this->getTopCustomers($startDate),
            'daily_revenue'       => $this->getDailyRevenue(),
        ];
    }

    /**
     * Get top customers
     */
    protected function getTopCustomers($startDate, $limit = 10)
    {
        return Order::select('user_id', 'customer_name', 'customer_email')
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('SUM(total) as total_spent')
            ->where('created_at', '>=', $startDate)
            ->where('payment_status', 'paid')
            ->groupBy('user_id', 'customer_name', 'customer_email')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get();
    }

    /**
     * Get daily revenue chart data (always last 30 days regardless of period,
     * so the chart always has meaningful data)
     */
    protected function getDailyRevenue()
    {
        $days    = [];
        $revenue = [];

        for ($i = 29; $i >= 0; $i--) {
            $date      = now()->subDays($i)->startOfDay();
            $days[]    = $date->format('M d');
            $revenue[] = (float) Order::whereDate('created_at', $date)
                ->where('payment_status', 'paid')
                ->sum('total');
        }

        return [
            'labels' => $days,
            'data'   => $revenue,
        ];
    }

    /**
     * Get order statistics (used by the index page)
     */
    public function getOrderStats()
    {
        return [
            'total'              => Order::count(),
            'pending'            => Order::where('status', 'pending')->count(),
            'processing'         => Order::where('status', 'processing')->count(),
            'shipped'            => Order::where('status', 'shipped')->count(),
            'delivered'          => Order::where('status', 'delivered')->count(),
            'cancelled'          => Order::where('status', 'cancelled')->count(),
            'total_revenue'      => (float) Order::where('payment_status', 'paid')->sum('total'),
            'pending_payment'    => (float) Order::where('payment_status', 'pending')->sum('total'),
            'today_orders'       => Order::whereDate('created_at', today())->count(),
            'today_revenue'      => (float) Order::whereDate('created_at', today())
                ->where('payment_status', 'paid')->sum('total'),
            'this_month_orders'  => Order::whereMonth('created_at', now()->month)->count(),
            'this_month_revenue' => (float) Order::whereMonth('created_at', now()->month)
                ->where('payment_status', 'paid')->sum('total'),
        ];
    }
}