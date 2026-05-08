<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentController;
use App\Models\Order;
use App\Services\SellerWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefundController extends Controller
{
    protected $walletService;
    protected $paymentController;

    public function __construct(SellerWalletService $walletService, PaymentController $paymentController)
    {
        $this->walletService     = $walletService;
        $this->paymentController = $paymentController;
    }

    /**
     * List all refund requests.
     *
     * Refund pipeline:
     *   refund_pending  — customer cancelled a paid order; awaiting admin action
     *   refunded        — admin processed the Paystack refund
     *   refund_rejected — admin rejected the refund request
     */
    public function index(Request $request)
    {
        $status   = $request->get('status');
        $search   = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        $query = Order::with(['user', 'items.seller.shop'])
            ->whereIn('payment_status', ['refund_pending', 'refunded', 'refund_rejected'])
            ->where('status', 'cancelled');

        if ($status && in_array($status, ['refund_pending', 'refunded', 'refund_rejected'])) {
            $query->where('payment_status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        if ($dateFrom) {
            $query->whereDate('cancelled_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('cancelled_at', '<=', $dateTo);
        }

        $refunds = $query->latest('cancelled_at')->paginate(20);

        $stats = [
            'pending'        => Order::where('status', 'cancelled')
                                     ->where('payment_status', 'refund_pending')->count(),
            'completed'      => Order::where('payment_status', 'refunded')->count(),
            'rejected'       => Order::where('payment_status', 'refund_rejected')->count(),
            'total_refunded' => (float) Order::where('payment_status', 'refunded')->sum('refund_amount'),
        ];

        return view('admin.finance.refunds.index', compact('refunds', 'stats'));
    }

    /**
     * Show refund request details.
     */
    public function show(Order $order)
    {
        if ($order->status !== 'cancelled' ||
            !in_array($order->payment_status, ['refund_pending', 'refunded', 'refund_rejected'])) {
            abort(404, 'Not a refund request');
        }

        $order->load(['user', 'items.product', 'items.seller.shop', 'items.seller.wallet']);

        return view('admin.finance.refunds.show', compact('order'));
    }

    /**
     * Process a refund.
     *
     * 1. Calls Paystack refund API via PaymentController::initiateRefund().
     * 2. Deducts each seller's wallet proportionally.
     * 3. Marks the order as refunded.
     */
    public function process(Request $request, Order $order)
    {
        if ($order->payment_status !== 'refund_pending') {
            return back()->with('error',
                'This order is not awaiting a refund (current status: ' . $order->payment_status . ').'
            );
        }

        $request->validate([
            'refund_amount' => 'required|numeric|min:0.01|max:' . $order->total,
            'notes'         => 'nullable|string|max:500',
        ]);

        $refundAmount    = (float) $request->refund_amount;
        $refundReference = null;

        // Call Paystack only when the order was paid via Paystack
        if ($order->payment_method === 'paystack' && $order->payment_reference) {
            $amountKobo = (int) round($refundAmount * 100);
            $reason     = $request->notes
                ?: "Customer cancellation refund for Order #{$order->order_number}";

            $paystackResult = $this->paymentController->initiateRefund(
                $order->payment_reference,
                $amountKobo,
                $reason
            );

            if (!$paystackResult['success']) {
                return back()->with('error',
                    'Paystack refund failed: ' . $paystackResult['message'] .
                    ' — Please try again or process manually.'
                );
            }

            $refundReference = $paystackResult['refund_reference'];
        }

        try {
            DB::beginTransaction();

            // Deduct from each seller's wallet proportionally
            $order->load('items.seller');

            foreach ($order->items->groupBy('seller_id') as $sellerId => $sellerItems) {
                $sellerTotal  = $sellerItems->sum('total_price');
                $sellerRefund = $order->subtotal > 0
                    ? round(($sellerTotal / $order->subtotal) * $refundAmount, 2)
                    : 0;

                if ($sellerRefund <= 0) {
                    continue;
                }

                $seller = $sellerItems->first()->seller;

                if (!$seller) {
                    Log::warning('Refund: seller not found', [
                        'order_id' => $order->id, 'seller_id' => $sellerId,
                    ]);
                    continue;
                }

                try {
                    $this->walletService->processRefund($order, $sellerId, $sellerRefund);
                } catch (\Exception $e) {
                    Log::error('Refund: failed to deduct seller wallet', [
                        'order_id'      => $order->id,
                        'seller_id'     => $sellerId,
                        'seller_refund' => $sellerRefund,
                        'error'         => $e->getMessage(),
                    ]);
                    // Don't abort — wallet failure should not block the refund record
                }
            }

            $noteAppend = "\nRefund of ₦" . number_format($refundAmount, 2) .
                          " processed by admin on " . now()->format('d M Y H:i') .
                          ($refundReference ? ". Paystack ref: {$refundReference}." : '.') .
                          ($request->notes ? " Notes: {$request->notes}" : '');

            $order->update([
                'payment_status' => 'refunded',
                'refund_amount'  => $refundAmount,
                'refunded_at'    => now(),
                'notes'          => trim(($order->notes ?? '') . $noteAppend),
            ]);

            try {
                activity()
                    ->performedOn($order)
                    ->causedBy(auth()->guard('admin')->user())
                    ->withProperties(['refund_amount' => $refundAmount, 'paystack_ref' => $refundReference])
                    ->log('Refund processed by admin');
            } catch (\Throwable $e) { /* activitylog may not be installed */ }

            DB::commit();

            $successMsg = 'Refund of ₦' . number_format($refundAmount, 2) . ' processed successfully!';
            if ($refundReference) {
                $successMsg .= " Paystack reference: {$refundReference}";
            }

            return redirect()
                ->route('admin.finance.refunds.show', $order)
                ->with('success', $successMsg);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Refund process failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Refund processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Reject a refund request.
     */
    public function reject(Request $request, Order $order)
    {
        if ($order->payment_status !== 'refund_pending') {
            return back()->with('error', 'Only pending refund requests can be rejected.');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $order->update([
                'payment_status' => 'refund_rejected',
                'notes'          => trim(
                    ($order->notes ?? '') .
                    "\nRefund request rejected by admin on " . now()->format('d M Y H:i') .
                    ". Reason: {$request->reason}"
                ),
            ]);

            try {
                activity()
                    ->performedOn($order)
                    ->causedBy(auth()->guard('admin')->user())
                    ->log('Refund request rejected: ' . $request->reason);
            } catch (\Throwable $e) { /* swallow silently */ }

            // Optionally notify customer:
            // $order->user?->notify(new RefundRejected($order, $request->reason));

            return back()->with('success', 'Refund request rejected.');

        } catch (\Exception $e) {
            return back()->with('error', 'Rejection failed: ' . $e->getMessage());
        }
    }
}