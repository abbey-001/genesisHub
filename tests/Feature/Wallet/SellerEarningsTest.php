<?php

namespace Tests\Feature\Wallet;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Seller;
use App\Models\SellerPayoutSettings;
use App\Models\SellerWalletTransaction;
use App\Services\SellerWalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Seller earnings only enter the wallet when an item is delivered, minus the
 * platform commission, and sit in a pending "hold" before becoming withdrawable.
 */
class SellerEarningsTest extends TestCase
{
    use RefreshDatabase;

    private SellerWalletService $wallets;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wallets = app(SellerWalletService::class);
    }

    private function deliveredItem(Seller $seller, float $total, bool $paid = true): OrderItem
    {
        $order = $paid ? Order::factory()->paid()->create() : Order::factory()->create();

        return OrderItem::factory()->create([
            'order_id'    => $order->id,
            'seller_id'   => $seller->id,
            'price'       => $total,
            'total_price' => $total,
            'status'      => 'delivered',
        ]);
    }

    public function test_delivered_item_credits_pending_balance_minus_commission(): void
    {
        $seller = Seller::factory()->create(['commission_rate' => 10]);

        $tx = $this->wallets->processItemDelivered($this->deliveredItem($seller, 25000));

        $wallet = $this->wallets->getWallet($seller)->fresh();
        $this->assertEquals(22500, $tx->amount);             // 25,000 minus 10%
        $this->assertEquals(22500, $wallet->pending_balance);
        $this->assertEquals(0, $wallet->balance);             // not withdrawable yet
        $this->assertEquals(22500, $wallet->total_earned);
        $this->assertSame('pending', $tx->status);
    }

    public function test_unpaid_orders_never_credit_the_seller(): void
    {
        $seller = Seller::factory()->create();

        $this->assertNull($this->wallets->processItemDelivered($this->deliveredItem($seller, 10000, paid: false)));
        $this->assertEquals(0, $this->wallets->getWallet($seller)->fresh()->pending_balance);
    }

    public function test_the_same_item_cannot_be_credited_twice(): void
    {
        $seller = Seller::factory()->create();
        $item   = $this->deliveredItem($seller, 10000);

        $this->wallets->processItemDelivered($item);
        $second = $this->wallets->processItemDelivered($item);

        $this->assertNull($second);
        $this->assertEquals(9000, $this->wallets->getWallet($seller)->fresh()->pending_balance);
        $this->assertSame(1, SellerWalletTransaction::where('transactable_id', $item->id)->count());
    }

    public function test_sellers_with_no_hold_period_are_credited_immediately(): void
    {
        $seller = Seller::factory()->create();
        SellerPayoutSettings::create(['seller_id' => $seller->id, 'hold_period_days' => 0]);

        $this->wallets->processItemDelivered($this->deliveredItem($seller, 10000));

        $wallet = $this->wallets->getWallet($seller)->fresh();
        $this->assertEquals(9000, $wallet->balance);
        $this->assertEquals(0, $wallet->pending_balance);
    }

    public function test_pending_funds_are_released_only_after_the_hold_period(): void
    {
        $seller = Seller::factory()->create();
        $this->wallets->processItemDelivered($this->deliveredItem($seller, 10000)); // default 7-day hold

        $this->travel(6)->days();
        $this->assertSame(0, $this->wallets->releasePendingFunds()['released_count']);
        $this->assertEquals(0, $this->wallets->getWallet($seller)->fresh()->balance);

        $this->travel(2)->days();
        $this->assertSame(1, $this->wallets->releasePendingFunds()['released_count']);

        $wallet = $this->wallets->getWallet($seller)->fresh();
        $this->assertEquals(9000, $wallet->balance);
        $this->assertEquals(0, $wallet->pending_balance);

        // Running the daily job again must not release the same funds twice.
        $this->assertSame(0, $this->wallets->releasePendingFunds()['released_count']);
        $this->assertEquals(9000, $wallet->fresh()->balance);
    }
}
