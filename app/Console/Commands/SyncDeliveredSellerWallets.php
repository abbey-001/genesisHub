<?php

namespace App\Console\Commands;

use App\Models\OrderItem;
use App\Services\SellerWalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncDeliveredSellerWallets extends Command
{
    protected $signature = 'wallet:sync-delivered
        {--seller_id= : Limit sync to one seller}
        {--order_id= : Limit sync to one order}
        {--dry-run : Show what would be credited without writing}';

    protected $description = 'Credit seller wallets for delivered order items that do not yet have wallet transactions';

    public function handle(SellerWalletService $walletService): int
    {
        $query = OrderItem::query()
            ->with(['order', 'seller'])
            ->where('status', 'delivered')
            ->whereHas('order', fn ($order) => $order->where('payment_status', 'paid'))
            ->whereNotExists(function ($tx) {
                $tx->select(DB::raw(1))
                    ->from('seller_wallet_transactions')
                    ->whereColumn('seller_wallet_transactions.transactable_id', 'order_items.id')
                    ->where('seller_wallet_transactions.transactable_type', OrderItem::class)
                    ->whereIn('seller_wallet_transactions.source', ['order_pending', 'order']);
            });

        if ($sellerId = $this->option('seller_id')) {
            $query->where('seller_id', $sellerId);
        }

        if ($orderId = $this->option('order_id')) {
            $query->where('order_id', $orderId);
        }

        $items = $query->orderBy('id')->get();

        if ($items->isEmpty()) {
            $this->info('No delivered seller items need wallet sync.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("Delivered items needing wallet sync: {$items->count()}");
            foreach ($items as $item) {
                $rate = $item->seller?->commission_rate ?? config('platform.commission_rate', 10);
                $net = round((float) $item->total_price * (1 - ((float) $rate / 100)), 2);
                $this->line("Order item #{$item->id} | order #{$item->order_id} | seller #{$item->seller_id} | net {$net}");
            }
            return self::SUCCESS;
        }

        $synced = 0;
        $failed = 0;

        foreach ($items as $item) {
            try {
                $walletService->processItemDelivered($item);
                $synced++;
            } catch (\Throwable $e) {
                $failed++;
                $this->error("Order item #{$item->id} failed: {$e->getMessage()}");
            }
        }

        $this->info("Wallet sync completed. Synced: {$synced}. Failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
