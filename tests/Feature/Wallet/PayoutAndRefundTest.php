<?php

namespace Tests\Feature\Wallet;

use App\Models\Order;
use App\Models\Payout;
use App\Models\Seller;
use App\Models\SellerWallet;
use App\Services\SellerWalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayoutAndRefundTest extends TestCase
{
    use RefreshDatabase;

    private SellerWalletService $wallets;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(); // Telegram alerts must never leave the test suite.
        $this->wallets = app(SellerWalletService::class);
    }

    private function sellerWithBalance(float $balance, array $sellerState = []): Seller
    {
        $seller = Seller::factory()->create($sellerState);
        $this->wallets->getWallet($seller)->update(['balance' => $balance]);

        return $seller;
    }

    public function test_payout_request_moves_money_out_of_the_available_balance(): void
    {
        $seller = $this->sellerWithBalance(50000);

        $payout = $this->wallets->requestPayout($seller, ['amount' => 20000, 'payout_method' => 'bank_transfer']);

        $wallet = $seller->wallet()->first();
        $this->assertSame('pending', $payout->status);
        $this->assertEquals(30000, $wallet->balance);
        $this->assertEquals(20000, $wallet->total_withdrawn);
        $this->assertNotNull($payout->fresh()->wallet_transaction_id);
    }

    public function test_unverified_sellers_cannot_withdraw(): void
    {
        $seller = $this->sellerWithBalance(50000, ['is_verified' => false]);

        $this->expectExceptionMessage('Only verified sellers');
        $this->wallets->requestPayout($seller, ['amount' => 1000, 'payout_method' => 'bank_transfer']);
    }

    public function test_sellers_cannot_withdraw_more_than_their_balance(): void
    {
        $seller = $this->sellerWithBalance(5000);

        try {
            $this->wallets->requestPayout($seller, ['amount' => 5000.01, 'payout_method' => 'bank_transfer']);
            $this->fail('Expected an insufficient balance error.');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Insufficient available balance', $e->getMessage());
        }

        $this->assertEquals(5000, $seller->wallet()->first()->balance);
        $this->assertSame(0, Payout::count());
    }

    public function test_only_one_payout_can_be_in_progress_at_a_time(): void
    {
        $seller = $this->sellerWithBalance(50000);
        $this->wallets->requestPayout($seller, ['amount' => 10000, 'payout_method' => 'bank_transfer']);

        $this->expectExceptionMessage('already have a payout in progress');
        $this->wallets->requestPayout($seller, ['amount' => 10000, 'payout_method' => 'bank_transfer']);
    }

    public function test_failed_payout_returns_the_money_and_corrects_lifetime_totals(): void
    {
        $seller = $this->sellerWithBalance(50000);
        $payout = $this->wallets->requestPayout($seller, ['amount' => 20000, 'payout_method' => 'bank_transfer']);

        $this->wallets->failPayout($payout->fresh(), 'Bank rejected transfer');

        $wallet = $seller->wallet()->first();
        $this->assertEquals(50000, $wallet->balance);
        $this->assertEquals(0, $wallet->total_withdrawn);
        $this->assertEquals(0, $wallet->total_earned, 'A reversal is not new income.');
        $this->assertSame('failed', $payout->fresh()->status);
        $this->assertSame('reversed', $payout->fresh()->walletTransaction->status);
    }

    public function test_payouts_follow_pending_processing_completed_order(): void
    {
        $seller = $this->sellerWithBalance(50000);
        $payout = $this->wallets->requestPayout($seller, ['amount' => 20000, 'payout_method' => 'bank_transfer']);

        try {
            $this->wallets->completePayout($payout->fresh(), []);
            $this->fail('A pending payout must not be completed directly.');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Only processing payouts', $e->getMessage());
        }

        $this->wallets->markPayoutProcessing($payout->fresh());
        $this->wallets->completePayout($payout->fresh(), ['transaction_reference' => 'TRF-123']);

        $this->assertSame('completed', $payout->fresh()->status);
        $this->assertSame('TRF-123', $payout->fresh()->transaction_id);

        $this->expectExceptionMessage('Cannot reverse');
        $this->wallets->failPayout($payout->fresh(), 'too late');
    }

    public function test_refund_is_deducted_from_the_seller_balance(): void
    {
        $seller = $this->sellerWithBalance(10000);
        $order  = Order::factory()->paid()->create();

        $this->wallets->processRefund($order, $seller->id, 4000);

        $this->assertEquals(6000, $seller->wallet()->first()->balance);
    }

    public function test_refund_larger_than_balance_overdraws_the_wallet_exactly_once(): void
    {
        $seller = $this->sellerWithBalance(3000);
        $order  = Order::factory()->paid()->create();

        $tx = $this->wallets->processRefund($order, $seller->id, 5000);

        $wallet = SellerWallet::where('seller_id', $seller->id)->first();
        $this->assertEquals(-2000, $wallet->balance);
        $this->assertEquals(-2000, $tx->balance_after);
        $this->assertTrue($wallet->hasNegativeBalance());
    }
}
