<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryPayout;
use App\Models\Rider;
use App\Services\PayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryPayoutController extends Controller
{
    protected $payoutService;

    public function __construct(PayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }

    /**
     * Display all payout requests
     */
    public function index(Request $request)
    {
        $query = DeliveryPayout::with(['company.user', 'approvedBy', 'paidBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('company_id')) {
            $query->where('rider_id', $request->company_id);
        }

        // Search by reference number
        if ($request->filled('search')) {
            $query->where('reference_number', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('requested_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('requested_at', '<=', $request->date_to);
        }

        $payouts = $query->latest('requested_at')->paginate(20);

        // Stats from PayoutService — keys: total_paid, total_pending, total_approved,
        // count_paid, count_pending, count_approved, count_rejected
        $stats = $this->payoutService->getPayoutStats();

        // All active companies for the filter dropdown
        $companies = Rider::where('is_active', true)->orderBy('full_name')->get();

        return view('admin.payouts.delivery.index', compact('payouts', 'stats', 'companies'));
    }

    /**
     * Show payout details
     */
    public function show(DeliveryPayout $payout)
    {
        $payout->load(['company.user', 'deliveries.order', 'approvedBy', 'paidBy', 'rejectedBy']);

        return view('admin.payouts.delivery.show', compact('payout'));
    }

    /**
     * Approve payout request
     */
    public function approve(Request $request, DeliveryPayout $payout)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $admin = Auth::guard('admin')->user();

            $this->payoutService->approvePayout($payout, $admin, $request->notes);

            return back()->with('success', 'Payout request approved successfully!');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mark payout as paid
     */
    public function markAsPaid(Request $request, DeliveryPayout $payout)
    {
        $request->validate([
            'transaction_reference' => 'required|string|max:255',
            'payment_method'        => 'required|in:bank_transfer,cash,cheque,online_transfer',
        ]);

        try {
            $this->payoutService->markAsPaid(
                $payout,
                Auth::guard('admin')->user(),
                $request->transaction_reference,
                $request->payment_method
            );

            return back()->with('success', 'Payout marked as paid successfully!');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject payout request
     */
    public function reject(Request $request, DeliveryPayout $payout)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            $admin = Auth::guard('admin')->user();

            $this->payoutService->rejectPayout($payout, $admin, $request->rejection_reason);

            return back()->with('success', 'Payout request rejected. The company has been notified.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Export payouts report (paid payouts only)
     */
    public function export(Request $request)
    {
        $startDate = $request->filled('date_from')
            ? \Carbon\Carbon::parse($request->date_from)
            : null;

        $endDate = $request->filled('date_to')
            ? \Carbon\Carbon::parse($request->date_to)
            : null;

        $payouts  = $this->payoutService->generatePayoutReport($startDate, $endDate);
        $filename = 'delivery_payouts_report_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($payouts) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Reference', 'Company', 'Amount', 'Status',
                'Requested At', 'Approved At', 'Approved By',
                'Paid At', 'Paid By', 'Payment Method',
                'Transaction Ref', 'Deliveries Count',
            ]);

            foreach ($payouts as $payout) {
                fputcsv($file, [
                    $payout->reference_number,
                    $payout->company->full_name,
                    $payout->amount,
                    $payout->status,
                    $payout->requested_at->format('Y-m-d H:i:s'),
                    $payout->approved_at?->format('Y-m-d H:i:s') ?? '',
                    $payout->approvedBy?->name ?? '',
                    $payout->paid_at?->format('Y-m-d H:i:s') ?? '',
                    $payout->paidBy?->name ?? '',
                    $payout->payment_method ?? '',
                    $payout->transaction_reference ?? '',
                    $payout->deliveries_count,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Batch approve multiple pending payouts
     */
    public function batchApprove(Request $request)
    {
        $request->validate([
            'payout_ids'   => 'required|array|min:1',
            // Validate against the correct table name
            'payout_ids.*' => 'exists:delivery_payouts,id',
        ]);

        $admin    = Auth::guard('admin')->user();
        $approved = 0;
        $failed   = 0;

        foreach ($request->payout_ids as $payoutId) {
            try {
                // Use the correct model — DeliveryPayout, not Payout
                $payout = DeliveryPayout::findOrFail($payoutId);
                $this->payoutService->approvePayout($payout, $admin);
                $approved++;
            } catch (\Exception $e) {
                $failed++;
                \Log::warning("Batch approve failed for payout #{$payoutId}: " . $e->getMessage());
            }
        }

        $message = "Approved {$approved} payout(s) successfully.";
        if ($failed > 0) {
            $message .= " {$failed} failed (already processed or invalid).";
        }

        return back()->with('success', $message);
    }
}