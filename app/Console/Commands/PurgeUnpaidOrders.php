<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PurgeUnpaidOrders
 *
 * Runs daily and cancels orders that have been sitting in "pending" status
 * (payment_status = 'pending') for longer than the configured grace period.
 *
 * A "pending" order means the user initialised checkout but never completed
 * payment — the gateway popup was closed, the session expired, or they simply
 * abandoned the cart. These orders lock stock unnecessarily and pollute seller
 * dashboards.
 *
 * What this command does:
 *  1. Finds orders older than GRACE_HOURS hours with:
 *       - status          = 'pending'
 *       - payment_status  = 'pending'
 *  2. Cancels each order in a transaction.
 *  3. Does NOT touch stock — stock is only decremented after payment is confirmed
 *     (in PaymentController::fulfilOrder), so there is nothing to restore here.
 *  4. Logs every cancellation and prints a summary to the console.
 *
 * Usage:
 *   php artisan orders:purge-unpaid          (uses default 24-hour grace period)
 *   php artisan orders:purge-unpaid --hours=48
 *   php artisan orders:purge-unpaid --dry-run (shows what would be cancelled)
 */
class PurgeUnpaidOrders extends Command
{
    protected $signature = 'orders:purge-unpaid
                            {--hours=24 : Grace period in hours before an unpaid order is cancelled}
                            {--dry-run  : Preview which orders would be cancelled without changing anything}';

    protected $description = 'Cancel pending orders that were never paid for, freeing up seller dashboards.';

    public function handle(): int
    {
        $graceHours = (int) $this->option('hours');
        $isDryRun   = (bool) $this->option('dry-run');
        $cutoff     = now()->subHours($graceHours);

        if ($isDryRun) {
            $this->info("DRY RUN — no changes will be made.");
        }

        $this->info("Looking for unpaid orders created before {$cutoff->toDateTimeString()} ({$graceHours}h grace period)...");

        // Fetch the candidate orders. We only need a few columns for the log
        // and the cancellation update — no need to load full relations.
        $orders = Order::where('status', 'pending')
            ->where('payment_status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->select('id', 'order_number', 'user_id', 'total', 'created_at')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No unpaid orders found. Nothing to do.');
            return self::SUCCESS;
        }

        $this->info("Found {$orders->count()} unpaid order(s) to cancel.");

        if ($isDryRun) {
            // Just print a table and exit without writing anything.
            $this->table(
                ['ID', 'Order Number', 'Total', 'Created At'],
                $orders->map(fn($o) => [
                    $o->id,
                    $o->order_number,
                    '₦' . number_format($o->total, 2),
                    $o->created_at->toDateTimeString(),
                ])->toArray()
            );
            return self::SUCCESS;
        }

        $cancelled = 0;
        $failed    = 0;

        foreach ($orders as $order) {
            try {
                DB::transaction(function () use ($order) {
                    // Cancel the order itself.
                    $order->update([
                        'status'       => 'cancelled',
                        'cancelled_at' => now(),
                        'notes'        => trim(
                            ($order->notes ?? '') .
                            "\nAuto-cancelled: payment not received within the grace period. [" .
                            now()->toDateTimeString() . ']'
                        ),
                    ]);

                    // Cancel every order item so seller dashboards show
                    // "cancelled" rather than "pending" for these ghost orders.
                    $order->items()->update(['status' => 'cancelled']);
                });

                $cancelled++;

                Log::info('PurgeUnpaidOrders: cancelled order', [
                    'order_id'     => $order->id,
                    'order_number' => $order->order_number,
                    'total'        => $order->total,
                    'age_hours'    => round(now()->diffInMinutes($order->created_at) / 60, 1),
                ]);

            } catch (\Exception $e) {
                $failed++;

                Log::error('PurgeUnpaidOrders: failed to cancel order', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);

                $this->warn("  ✗ Failed to cancel order #{$order->order_number}: {$e->getMessage()}");
            }
        }

        // Summary
        $this->newLine();
        $this->info("Done. Cancelled: {$cancelled}  |  Failed: {$failed}");

        Log::info('PurgeUnpaidOrders: run complete', [
            'grace_hours' => $graceHours,
            'found'       => $orders->count(),
            'cancelled'   => $cancelled,
            'failed'      => $failed,
        ]);

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}