<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Delivery;
use App\Models\SellerWalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialReportController extends Controller
{
    /**
     * Main financial reports dashboard
     */
    public function index(Request $request)
    {
        

        $dateFrom = $request->get('date_from', now()->startOfMonth());
        $dateTo = $request->get('date_to', now());

        // Revenue Overview
        $revenue = [
            'total_orders' => Order::whereBetween('created_at', [$dateFrom, $dateTo])->sum('total'),
            'total_paid' => Order::where('payment_status', 'paid')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('total'),
            'total_refunded' => Order::where('payment_status', 'refunded')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('total'),
            'net_revenue' => 0,
        ];

        $revenue['net_revenue'] = $revenue['total_paid'] - $revenue['total_refunded'];

        // Commission Earned
        $commission = [
            'total' => Order::where('payment_status', 'paid')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum(DB::raw('total * 0.10')), // Assuming 10% commission
            'from_deliveries' => Delivery::where('status', 'delivered')
                ->whereBetween('delivered_at', [$dateFrom, $dateTo])
                ->sum(DB::raw('delivery_fee * 0.10')),
        ];

        // Payout Summary
        $payouts = [
            'total_requested' => Payout::whereBetween('requested_at', [$dateFrom, $dateTo])
                ->sum('amount'),
            'total_paid' => Payout::where('status', 'completed')
                ->whereBetween('processed_at', [$dateFrom, $dateTo])
                ->sum('amount'),
            'pending' => Payout::where('status', 'pending')
                ->sum('amount'),
        ];

        // Daily revenue trend
        $dailyRevenue = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top earning sellers
        $topSellers = SellerWalletTransaction::where('type', 'credit')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with('seller.user', 'seller.shop')
            ->selectRaw('seller_id, SUM(amount) as total_earned')
            ->groupBy('seller_id')
            ->orderByDesc('total_earned')
            ->take(10)
            ->get();

        return view('admin.finance.reports.index', compact(
            'revenue',
            'commission',
            'payouts',
            'dailyRevenue',
            'topSellers',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Export financial report
     */
    public function export(Request $request)
    {
        

        $dateFrom = $request->get('date_from', now()->startOfMonth());
        $dateTo = $request->get('date_to', now());
        $type = $request->get('type', 'revenue');

        $filename = "financial_report_{$type}_" . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($type, $dateFrom, $dateTo) {
            $file = fopen('php://output', 'w');

            switch ($type) {
                case 'revenue':
                    $this->exportRevenueReport($file, $dateFrom, $dateTo);
                    break;
                case 'payouts':
                    $this->exportPayoutsReport($file, $dateFrom, $dateTo);
                    break;
                case 'commissions':
                    $this->exportCommissionsReport($file, $dateFrom, $dateTo);
                    break;
                default:
                    $this->exportRevenueReport($file, $dateFrom, $dateTo);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export revenue report
     */
    protected function exportRevenueReport($file, $dateFrom, $dateTo)
    {
        fputcsv($file, ['Date', 'Order Count', 'Gross Revenue', 'Refunds', 'Net Revenue']);

        $data = Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('
                DATE(created_at) as date,
                COUNT(*) as order_count,
                SUM(CASE WHEN payment_status = "paid" THEN total ELSE 0 END) as gross,
                SUM(CASE WHEN payment_status = "refunded" THEN total ELSE 0 END) as refunds
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        foreach ($data as $row) {
            fputcsv($file, [
                $row->date,
                $row->order_count,
                $row->gross,
                $row->refunds,
                $row->gross - $row->refunds
            ]);
        }
    }

    /**
     * Export payouts report
     */
    protected function exportPayoutsReport($file, $dateFrom, $dateTo)
    {
        fputcsv($file, ['Date', 'Seller', 'Amount', 'Status', 'Method', 'Transaction Ref']);

        $payouts = Payout::with('seller.user')
            ->whereBetween('requested_at', [$dateFrom, $dateTo])
            ->get();

        foreach ($payouts as $payout) {
            fputcsv($file, [
                $payout->requested_at->format('Y-m-d'),
                $payout->seller->user->name,
                $payout->amount,
                $payout->status,
                $payout->payout_method,
                $payout->transaction_id ?? 'N/A'
            ]);
        }
    }

    /**
     * Export commissions report
     */
    protected function exportCommissionsReport($file, $dateFrom, $dateTo)
    {
        fputcsv($file, ['Date', 'Order #', 'Order Total', 'Commission Rate', 'Commission Amount']);

        $orders = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get();

        foreach ($orders as $order) {
            $commissionRate = 0.10; // 10%
            $commission = $order->total * $commissionRate;

            fputcsv($file, [
                $order->created_at->format('Y-m-d'),
                $order->order_number,
                $order->total,
                ($commissionRate * 100) . '%',
                $commission
            ]);
        }
    }

    /**
     * Cash flow analysis
     */
    public function cashFlow(Request $request)
    {
        

        $dateFrom = $request->get('date_from', now()->startOfMonth());
        $dateTo = $request->get('date_to', now());

        // Inflows
        $inflows = [
            'order_payments' => Order::where('payment_status', 'paid')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('total'),
        ];

        // Outflows
        $outflows = [
            'seller_payouts' => Payout::where('status', 'completed')
                ->whereBetween('processed_at', [$dateFrom, $dateTo])
                ->sum('amount'),
            'refunds' => Order::where('payment_status', 'refunded')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('total'),
        ];

        $netCashFlow = array_sum($inflows) - array_sum($outflows);

        return view('admin.finance.reports.cash-flow', compact(
            'inflows',
            'outflows',
            'netCashFlow',
            'dateFrom',
            'dateTo'
        ));
    }
}