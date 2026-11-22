<?php

namespace Tests\Feature\Payments;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Paystack calls this endpoint server-to-server, so it must work without a
 * logged-in user or CSRF token, and must only trust correctly signed payloads.
 */
class PaystackWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
        Mail::fake();
        Notification::fake();
    }

    private function sendWebhook(array $payload, ?string $signature = null)
    {
        $body      = json_encode($payload);
        $signature ??= hash_hmac('sha512', $body, config('services.paystack.secret_key'));

        return $this->call(
            'POST',
            '/payment/paystack/webhook',
            [],
            [],
            [],
            ['HTTP_X_PAYSTACK_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $body
        );
    }

    private function pendingOrderFor(Product $product, int $quantity): Order
    {
        $order = Order::factory()->create();
        OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => $quantity,
        ]);

        return $order;
    }

    public function test_webhook_with_an_invalid_signature_is_rejected(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $order   = $this->pendingOrderFor($product, 2);

        $this->sendWebhook(
            ['event' => 'charge.success', 'data' => ['reference' => $order->payment_reference]],
            'forged-signature'
        )->assertStatus(401);

        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_signed_charge_success_marks_the_order_paid_and_reserves_stock(): void
    {
        $product = Product::factory()->create(['stock' => 10, 'sold_count' => 0]);
        $order   = $this->pendingOrderFor($product, 3);

        $this->sendWebhook(['event' => 'charge.success', 'data' => ['reference' => $order->payment_reference]])
            ->assertOk();

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('processing', $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertSame(7, $product->fresh()->stock);
        $this->assertSame(3, $product->fresh()->sold_count);
    }

    public function test_repeated_webhooks_do_not_decrement_stock_twice(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $order   = $this->pendingOrderFor($product, 2);
        $payload = ['event' => 'charge.success', 'data' => ['reference' => $order->payment_reference]];

        $this->sendWebhook($payload)->assertOk();
        $this->sendWebhook($payload)->assertOk(); // Paystack retries deliveries

        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_charge_failed_cancels_an_unpaid_order(): void
    {
        $product = Product::factory()->create();
        $order   = $this->pendingOrderFor($product, 1);

        $this->sendWebhook(['event' => 'charge.failed', 'data' => ['reference' => $order->payment_reference]])
            ->assertOk();

        $this->assertSame('failed', $order->fresh()->payment_status);
        $this->assertSame('cancelled', $order->fresh()->status);
    }
}
