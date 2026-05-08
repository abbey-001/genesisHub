<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Mail\OrderConfirmation;
use App\Services\CartTotalService;
use App\Services\DeliveryService;
use App\Services\SellerWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Notifications\NewOrderReceived;

class PaymentController extends Controller
{
    private string $paystackSecretKey;
    private string $paystackPublicKey;
    private string $flutterwaveSecretKey;
    private string $flutterwavePublicKey;
    protected SellerWalletService $walletService;
    protected CartTotalService $cartTotalService;
    protected DeliveryService $deliveryService;

    public function __construct(
        SellerWalletService $walletService,
        CartTotalService $cartTotalService,
        DeliveryService $deliveryService
    ) {
        $this->paystackSecretKey    = config('services.paystack.secret_key');
        $this->paystackPublicKey    = config('services.paystack.public_key');
        $this->flutterwaveSecretKey = config('services.flutterwave.secret_key');
        $this->flutterwavePublicKey = config('services.flutterwave.public_key');
        $this->walletService        = $walletService;
        $this->cartTotalService     = $cartTotalService;
        $this->deliveryService      = $deliveryService;
    }

    // =========================================================================
    // CHECKOUT — CREATE ORDER + INITIALISE PAYMENT
    // =========================================================================

    /**
     * Validate the cart, create the Order + OrderItems, and return the
     * gateway initialisation payload to the frontend.
     *
     * Changes from original:
     *  - Delivery estimate is calculated and stored on the order.
     *  - Each OrderItem stores fulfillment_type (snapshot) and expected_ready_by
     *    (deadline date for the seller to mark the item ready).
     *  - Stock is still NOT decremented here — only after payment is confirmed.
     */
    public function initialize(Request $request)
    {
        try {
            $user    = Auth::user();
            $cart    = session()->get('cart', []);
            $gateway = in_array($request->input('gateway'), ['paystack', 'flutterwave'])
                ? $request->input('gateway')
                : 'paystack';

            if (empty($cart)) {
                return response()->json(['success' => false, 'message' => 'Your cart is empty.'], 400);
            }

            $address = $user->addresses()->where('is_default', true)->first();

            if (!$address) {
                $addresses = $user->addresses()->get();

                if ($addresses->count() > 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please choose a default address.',
                    ], 400);
                }

                if ($addresses->count() === 1) {
                    $address = $addresses->first();
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please select a shipping address.',
                    ], 400);
                }
            }

            $subtotal    = $this->cartTotalService->calculateSubtotal($cart);
            $deliveryFee = $address->delivery_zone
                ? $this->cartTotalService->calculateDeliveryFee($cart, $address->delivery_zone)
                : 0.0;
            $total       = $this->cartTotalService->calculateGrandTotal($cart, $deliveryFee);

            // ── Calculate delivery estimate BEFORE entering the transaction ──
            // This reads from the DB but doesn't write, so it's safe outside
            // the transaction and avoids holding a lock while doing extra reads.
            $estimate = $this->cartTotalService->calculateDeliveryEstimate($cart);

            DB::beginTransaction();

            try {
                $order = Order::create([
                    'user_id'               => $user->id,
                    'order_number'          => 'ORD-' . strtoupper(Str::random(10)),
                    'customer_name'         => $user->name,
                    'customer_email'        => $user->email,
                    'customer_phone'        => $user->phone,
                    'shipping_address'      => $address->address,
                    'shipping_city'         => $address->city,
                    'shipping_state'        => $address->state,
                    'shipping_postal_code'  => $address->postal_code,
                    'shipping_country'      => $address->country,
                    'shipping_zone'         => $address->delivery_zone,
                    'subtotal'              => $subtotal,
                    'tax'                   => 0.0,
                    'shipping_fee'          => $deliveryFee,
                    'discount'              => 0,
                    'total'                 => $total,
                    'status'                => 'pending',
                    'payment_status'        => 'pending',
                    'payment_method'        => $gateway,
                    // ── NEW: delivery estimate fields ──
                    'est_delivery_days_min' => $estimate['min'],
                    'est_delivery_days_max' => $estimate['max'],
                    'has_preorder_items'    => $estimate['has_preorder'],
                    'slowest_item_name'     => $estimate['slowest_item'],
                ]);

                foreach ($cart as $cartKey => $item) {
                    $realProductId = $item['id'] ?? $cartKey;

                    $product = Product::with('shop.seller')->find($realProductId);

                    if (!$product) {
                        throw new \Exception("Product not found: {$item['name']}");
                    }

                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Insufficient stock for: {$product->name}");
                    }

                    if (!$product->shop) {
                        throw new \Exception("Product {$product->id} ({$product->name}) has no shop assigned.");
                    }

                    if (!$product->shop->seller) {
                        throw new \Exception("Shop for product {$product->id} ({$product->name}) has no seller assigned.");
                    }

                    $variantOptions = $item['variant_options'] ?? [];

                    // expected_ready_by is set at order-creation time using now() as
                    // the base. When payment is confirmed (fulfilOrder), it is
                    // recalculated from paid_at for accuracy — this value acts as a
                    // pre-payment estimate in case the page is reviewed before payment.
                    $expectedReadyBy = now()->addDays($product->getMaxReadyDays())->toDateString();

                    OrderItem::create([
                        'order_id'         => $order->id,
                        'product_id'       => $product->id,
                        'seller_id'        => $product->shop->seller->id,
                        'product_name'     => $product->name,
                        'product_sku'      => !empty($variantOptions)
                            ? ($product->sku ?? 'N/A') . '-' . strtoupper(implode('-', $variantOptions))
                            : ($product->sku ?? 'N/A'),
                        'quantity'         => $item['quantity'],
                        'price'            => $item['price'],
                        'total_price'      => $item['price'] * $item['quantity'],
                        'status'           => 'pending',
                        'variant_options'  => $variantOptions,
                        // ── NEW: fulfillment snapshot ──
                        'fulfillment_type' => $product->fulfillment_type,
                        'expected_ready_by'=> $expectedReadyBy,
                    ]);
                }

                $reference = 'PAY-' . $order->order_number . '-' . time();
                $order->update(['payment_reference' => $reference]);

                DB::commit();

                Log::info('Order created, awaiting payment', [
                    'order_id'     => $order->id,
                    'order_number' => $order->order_number,
                    'user_id'      => $user->id,
                    'total'        => $total,
                    'gateway'      => $gateway,
                    'estimate_min' => $estimate['min'],
                    'estimate_max' => $estimate['max'],
                    'has_preorder' => $estimate['has_preorder'],
                ]);

                return response()->json([
                    'success' => true,
                    'gateway' => $gateway,
                    'data'    => [
                        'public_key'   => $gateway === 'paystack'
                            ? $this->paystackPublicKey
                            : $this->flutterwavePublicKey,
                        'email'        => $user->email,
                        'amount'       => $gateway === 'paystack'
                            ? (int) round($total * 100)   // Paystack expects kobo
                            : round($total, 2),            // Flutterwave expects full Naira
                        'reference'    => $reference,
                        'order_id'     => $order->id,
                        'order_number' => $order->order_number,
                        'name'         => $user->name,
                        'phone'        => $user->phone ?? '',
                    ],
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Order creation failed', ['error' => $e->getMessage(), 'user_id' => $user->id]);
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

        } catch (\Exception $e) {
            Log::error('Payment initialisation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to initialise payment.'], 500);
        }
    }

    // =========================================================================
    // PAYSTACK CALLBACK
    // =========================================================================

    public function paystackCallback(Request $request)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            return redirect()->route('payment.failed')->with('error', 'Invalid payment reference.');
        }

        try {
            $result = $this->verifyPaystackTransaction($reference);

            if ($result['status'] && ($result['data']['status'] ?? '') === 'success') {

                $order = Order::where('payment_reference', $reference)
                    ->with('items.product')
                    ->first();

                if (!$order) {
                    Log::error('Paystack callback: order not found', ['reference' => $reference]);
                    return redirect()->route('payment.failed')->with('error', 'Order not found.');
                }

                $this->fulfilOrder($order, $result['data']);

                session()->forget(['cart', 'cart_total', 'selected_address_id']);

                return redirect()->route('payment.success', ['order' => $order->id])
                    ->with('success', 'Payment successful!');

            } else {
                $order = Order::where('payment_reference', $reference)->first();
                if ($order && $order->payment_status !== 'paid') {
                    $order->update(['payment_status' => 'failed', 'status' => 'cancelled']);
                }
                return redirect()->route('payment.failed')->with('error', 'Payment was not successful.');
            }

        } catch (\Exception $e) {
            Log::error('Paystack callback error', ['reference' => $reference, 'error' => $e->getMessage()]);
            return redirect()->route('payment.failed')
                ->with('error', 'An error occurred. Please contact support with reference: ' . $reference);
        }
    }

    // =========================================================================
    // FLUTTERWAVE CALLBACK
    // =========================================================================

    public function flutterwaveCallback(Request $request)
    {
        $transactionId = $request->query('transaction_id');
        $reference     = $request->query('tx_ref');

        if (!$transactionId || !$reference) {
            return redirect()->route('payment.failed')->with('error', 'Invalid payment reference.');
        }

        try {
            $result = $this->verifyFlutterwaveTransaction($transactionId);

            if (($result['status'] ?? '') === 'success' && ($result['data']['status'] ?? '') === 'successful') {

                $order = Order::where('payment_reference', $reference)
                    ->with('items.product')
                    ->first();

                if (!$order) {
                    Log::error('Flutterwave callback: order not found', ['reference' => $reference]);
                    return redirect()->route('payment.failed')->with('error', 'Order not found.');
                }

                $this->fulfilOrder($order, $result['data']);

                session()->forget(['cart', 'cart_total', 'selected_address_id']);

                return redirect()->route('payment.success', ['order' => $order->id])
                    ->with('success', 'Payment successful!');

            } else {
                $order = Order::where('payment_reference', $reference)->first();
                if ($order && $order->payment_status !== 'paid') {
                    $order->update(['payment_status' => 'failed', 'status' => 'cancelled']);
                }
                return redirect()->route('payment.failed')->with('error', 'Payment was not successful.');
            }

        } catch (\Exception $e) {
            Log::error('Flutterwave callback error', ['reference' => $reference, 'error' => $e->getMessage()]);
            return redirect()->route('payment.failed')
                ->with('error', 'An error occurred. Please contact support with reference: ' . $reference);
        }
    }

    // =========================================================================
    // PAYSTACK WEBHOOK
    // =========================================================================

    public function paystackWebhook(Request $request)
    {
        $signature = $request->header('x-paystack-signature');

        if (!$signature || $signature !== hash_hmac('sha512', $request->getContent(), $this->paystackSecretKey)) {
            Log::warning('Paystack webhook: invalid signature', ['ip' => $request->ip()]);
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $event = $request->input('event');
        $data  = $request->input('data');

        Log::info('Paystack webhook received', ['event' => $event, 'reference' => $data['reference'] ?? null]);

        try {
            match ($event) {
                'charge.success' => $this->handlePaystackSuccessfulCharge($data),
                'charge.failed'  => $this->handlePaystackFailedCharge($data),
                default          => null,
            };

            return response()->json(['message' => 'OK'], 200);

        } catch (\Exception $e) {
            Log::error('Paystack webhook processing error', ['event' => $event, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Error logged'], 200);
        }
    }

    // =========================================================================
    // FLUTTERWAVE WEBHOOK
    // =========================================================================

    public function flutterwaveWebhook(Request $request)
    {
        $hash = $request->header('verif-hash');

        if (!$hash || $hash !== config('services.flutterwave.secret_hash')) {
            Log::warning('Flutterwave webhook: invalid hash', ['ip' => $request->ip()]);
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $event = $request->input('event');
        $data  = $request->input('data');

        Log::info('Flutterwave webhook received', ['event' => $event, 'reference' => $data['tx_ref'] ?? null]);

        try {
            match ($event) {
                'charge.completed' => $this->handleFlutterwaveSuccessfulCharge($data),
                'charge.failed'    => $this->handleFlutterwaveFailedCharge($data),
                default            => null,
            };

            return response()->json(['message' => 'OK'], 200);

        } catch (\Exception $e) {
            Log::error('Flutterwave webhook processing error', ['event' => $event, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Error logged'], 200);
        }
    }

    // =========================================================================
    // ORDER FULFILMENT (shared by both gateways — idempotent)
    // =========================================================================

    /**
     * Mark an order as paid, decrement stock, set per-item deadlines,
     * initialise delivery bundles, and notify sellers.
     *
     * IDEMPOTENT — if the order is already paid this is a safe no-op.
     *
     * Key change from original:
     *   Each OrderItem's expected_ready_by and fulfillment_type are set here
     *   using the confirmed paid_at timestamp so the deadline is exact.
     *   (At order creation time we used now() as an estimate; here we use
     *   the real paid_at which is accurate to the second.)
     */
    private function fulfilOrder(Order $order, array $gatewayData): void
    {
        if ($order->payment_status === 'paid') {
            Log::info('fulfilOrder: already paid, skipping', ['order_id' => $order->id]);
            return;
        }

        DB::transaction(function () use ($order, $gatewayData) {

            $fresh = Order::lockForUpdate()->find($order->id);

            if ($fresh->payment_status === 'paid') {
                return;
            }

            $paidAt = now();

            $fresh->update([
                'payment_status'  => 'paid',
                'status'          => 'processing',
                'paid_at'         => $paidAt,
                'payment_details' => json_encode($gatewayData),
            ]);

            $fresh->load('items.product');

            foreach ($fresh->items as $item) {
                // ── Recalculate the exact deadline from confirmed paid_at ──
                // The product may have been updated between cart and payment,
                // so we re-read fulfillment_type and max_ready_days from the
                // product rather than the snapshot on the item.
                $product        = $item->product;
                $fulfillmentType = $product?->fulfillment_type ?? 'in_stock';
                $maxReadyDays   = $product ? $product->getMaxReadyDays() : Product::IN_STOCK_MAX_DAYS;
                $expectedReadyBy = $paidAt->copy()->addDays($maxReadyDays)->toDateString();

                $item->update([
                    'status'           => 'processing',
                    'fulfillment_type' => $fulfillmentType,
                    'expected_ready_by'=> $expectedReadyBy,
                ]);

                if ($product) {
                    Product::where('id', $item->product_id)
                        ->decrement('stock', $item->quantity);

                    Product::where('id', $item->product_id)
                        ->increment('sold_count', $item->quantity);
                }
            }

            $this->deliveryService->initialiseBundles($fresh);
            $this->notifySellers($fresh);
        });

        try {
            Mail::to($order->customer_email)->send(new OrderConfirmation($order));
        } catch (\Exception $e) {
            Log::error('Order confirmation email failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }

        Log::info('Order fulfilled after payment', ['order_id' => $order->id]);
    }

    /**
     * Send NewOrderReceived notifications to each seller in the order.
     */
    private function notifySellers(Order $order): void
    {
        $sellerIds = $order->items->pluck('seller_id')->unique();
        

        foreach ($sellerIds as $sellerId) {
            $seller = \App\Models\Seller::where('seller_id', $sellerId);
            try {
                $seller = \App\Models\Seller::with(['shop', 'user'])->find($sellerId);
                $seller?->user?->notify(new NewOrderReceived($order, $seller));
            } catch (\Exception $e) {
                Log::error('Failed to notify seller of new order', [
                    'order_id'  => $order->id,
                    'seller_id' => $sellerId,
                    'error'     => $e->getMessage(),
                ]);
            }
            try {
                 app(\App\Services\Telegram\SellerTelegramService::class)
                      ->notifyNewOrder($seller, $order);
              } catch (\Exception $e) {
                  \Log::warning('Seller Telegram new order alert failed', ['error' => $e->getMessage()]);
              }
        }
    }

    // =========================================================================
    // WEBHOOK HANDLERS — PAYSTACK
    // =========================================================================

    private function handlePaystackSuccessfulCharge(array $data): void
    {
        $reference = $data['reference'];

        $order = Order::where('payment_reference', $reference)
            ->with('items.product')
            ->first();

        if (!$order) {
            Log::error('Paystack webhook: order not found for charge.success', ['reference' => $reference]);
            return;
        }

        $this->fulfilOrder($order, $data);
    }

    private function handlePaystackFailedCharge(array $data): void
    {
        $reference = $data['reference'];

        $order = Order::where('payment_reference', $reference)->first();

        if ($order && $order->payment_status !== 'paid') {
            $order->update(['payment_status' => 'failed', 'status' => 'cancelled']);
            Log::info('Paystack webhook: order marked failed', ['order_id' => $order->id, 'reference' => $reference]);
        }
    }

    // =========================================================================
    // WEBHOOK HANDLERS — FLUTTERWAVE
    // =========================================================================

    private function handleFlutterwaveSuccessfulCharge(array $data): void
    {
        $reference = $data['tx_ref'];

        $order = Order::where('payment_reference', $reference)
            ->with('items.product')
            ->first();

        if (!$order) {
            Log::error('Flutterwave webhook: order not found for charge.completed', ['reference' => $reference]);
            return;
        }

        $this->fulfilOrder($order, $data);
    }

    private function handleFlutterwaveFailedCharge(array $data): void
    {
        $reference = $data['tx_ref'];

        $order = Order::where('payment_reference', $reference)->first();

        if ($order && $order->payment_status !== 'paid') {
            $order->update(['payment_status' => 'failed', 'status' => 'cancelled']);
            Log::info('Flutterwave webhook: order marked failed', ['order_id' => $order->id, 'reference' => $reference]);
        }
    }

    // =========================================================================
    // ORDER CANCELLATION
    // =========================================================================

    public function cancelOrder(Request $request, $orderId)
    {
        $user  = Auth::user();
        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->with('items.product')
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        if ($order->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Order is already cancelled.'], 422);
        }

        if (!$order->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'This order can no longer be cancelled. Please contact support.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            if ($order->payment_status === 'paid') {
                foreach ($order->items as $item) {
                    if ($item->product) {
                        Product::where('id', $item->product_id)
                            ->increment('stock', $item->quantity);

                        Product::where('id', $item->product_id)
                            ->decrement('sold_count', $item->quantity);
                    }
                }

                $order->update([
                    'status'         => 'cancelled',
                    'payment_status' => 'refund_pending',
                    'cancelled_at'   => now(),
                    'notes'          => trim(
                        ($order->notes ?? '') .
                        "\nCustomer requested cancellation & refund on " . now()->format('d M Y H:i') . '.'
                    ),
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Order cancelled. Your refund request is under review and will be processed within 3–5 business days.',
                ]);

            } else {
                $order->update([
                    'status'       => 'cancelled',
                    'cancelled_at' => now(),
                    'notes'        => trim(
                        ($order->notes ?? '') .
                        "\nCancelled by customer on " . now()->format('d M Y H:i') . '.'
                    ),
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Order cancelled successfully.',
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation failed', [
                'order_id' => $orderId,
                'user_id'  => $user->id,
                'error'    => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to cancel order. Please try again.'], 500);
        }
    }

    // =========================================================================
    // PAYSTACK API HELPERS
    // =========================================================================

    private function verifyPaystackTransaction(string $reference): array
    {
        $response = Http::withToken($this->paystackSecretKey)
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        if ($response->failed()) {
            Log::error('Paystack verification request failed', [
                'reference' => $reference,
                'status'    => $response->status(),
            ]);
            throw new \Exception('Failed to verify payment with Paystack.');
        }

        $data = $response->json();

        if (!is_array($data)) {
            throw new \Exception('Invalid response from Paystack verification API.');
        }

        return $data;
    }

    public function initiatePaystackRefund(string $reference, int $amountKobo = 0, string $reason = ''): array
    {
        try {
            $payload = ['transaction' => $reference];

            if ($amountKobo > 0) {
                $payload['amount'] = $amountKobo;
            }

            if ($reason) {
                $payload['merchant_note'] = $reason;
            }

            $response = Http::withToken($this->paystackSecretKey)
                ->post('https://api.paystack.co/refund', $payload);

            $data = $response->json();

            if ($response->successful() && ($data['status'] ?? false)) {
                return [
                    'success'          => true,
                    'refund_reference' => $data['data']['id'] ?? null,
                    'message'          => 'Refund initiated successfully.',
                ];
            }

            return [
                'success'          => false,
                'refund_reference' => null,
                'message'          => $data['message'] ?? 'Paystack declined the refund request.',
            ];

        } catch (\Exception $e) {
            Log::error('Paystack refund API error', ['reference' => $reference, 'error' => $e->getMessage()]);
            return [
                'success'          => false,
                'refund_reference' => null,
                'message'          => 'Refund API call failed: ' . $e->getMessage(),
            ];
        }
    }

    // =========================================================================
    // FLUTTERWAVE API HELPERS
    // =========================================================================

    private function verifyFlutterwaveTransaction(string $transactionId): array
    {
        $response = Http::withToken($this->flutterwaveSecretKey)
            ->get("https://api.flutterwave.com/v3/transactions/{$transactionId}/verify");

        if ($response->failed()) {
            Log::error('Flutterwave verification request failed', [
                'transaction_id' => $transactionId,
                'status'         => $response->status(),
            ]);
            throw new \Exception('Failed to verify payment with Flutterwave.');
        }

        $data = $response->json();

        if (!is_array($data)) {
            throw new \Exception('Invalid response from Flutterwave verification API.');
        }

        return $data;
    }

    public function initiateFlutterwaveRefund(string $transactionId, int $amountKobo = 0, string $reason = ''): array
    {
        try {
            $payload = [];

            if ($amountKobo > 0) {
                $payload['amount'] = $amountKobo / 100;
            }

            if ($reason) {
                $payload['comments'] = $reason;
            }

            $response = Http::withToken($this->flutterwaveSecretKey)
                ->post("https://api.flutterwave.com/v3/transactions/{$transactionId}/refund", $payload);

            $data = $response->json();

            if ($response->successful() && ($data['status'] ?? '') === 'success') {
                return [
                    'success'          => true,
                    'refund_reference' => $data['data']['id'] ?? null,
                    'message'          => 'Refund initiated successfully.',
                ];
            }

            return [
                'success'          => false,
                'refund_reference' => null,
                'message'          => $data['message'] ?? 'Flutterwave declined the refund request.',
            ];

        } catch (\Exception $e) {
            Log::error('Flutterwave refund API error', ['transaction_id' => $transactionId, 'error' => $e->getMessage()]);
            return [
                'success'          => false,
                'refund_reference' => null,
                'message'          => 'Refund API call failed: ' . $e->getMessage(),
            ];
        }
    }

    // =========================================================================
    // SHARED BANK HELPERS
    // =========================================================================

    public function resolve(Request $request)
    {
        $request->validate([
            'account_number' => 'required|digits:10',
            'bank_code'      => 'required|string',
        ]);

        $gateway = in_array($request->input('gateway'), ['paystack', 'flutterwave'])
            ? $request->input('gateway')
            : 'paystack';

        try {
            if ($gateway === 'flutterwave') {
                $response = Http::withToken($this->flutterwaveSecretKey)
                    ->post('https://api.flutterwave.com/v3/accounts/resolve', [
                        'account_number' => $request->account_number,
                        'account_bank'   => $request->bank_code,
                    ]);
            } else {
                $response = Http::withToken($this->paystackSecretKey)
                    ->get('https://api.paystack.co/bank/resolve', [
                        'account_number' => $request->account_number,
                        'bank_code'      => $request->bank_code,
                    ]);
            }

            $data = $response->json();

            $ok = $gateway === 'flutterwave'
                ? ($data['status'] ?? '') === 'success'
                : ($data['status'] ?? false);

            if ($response->successful() && $ok) {
                return response()->json([
                    'status'       => true,
                    'account_name' => $data['data']['account_name'] ?? '',
                ]);
            }

            return response()->json([
                'status'  => false,
                'message' => $data['message'] ?? 'Could not resolve account name. Check the number and bank.',
            ], 422);

        } catch (\Exception $e) {
            Log::error('Account resolve failed', ['gateway' => $gateway, 'error' => $e->getMessage()]);
            return response()->json([
                'status'  => false,
                'message' => 'Unable to verify account at this time. Please try again.',
            ], 500);
        }
    }

    public function list()
    {
        $banks = cache()->remember('paystack_banks', now()->addHours(24), function () {
            $response = Http::withToken($this->paystackSecretKey)
                ->get('https://api.paystack.co/bank', ['country' => 'nigeria', 'perPage' => 100]);

            return $response->successful() ? ($response->json()['data'] ?? []) : [];
        });

        return response()->json(['status' => true, 'data' => $banks]);
    }

    // =========================================================================
    // VIEW RESPONSES
    // =========================================================================

    public function success(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        $categoriesWithSubs = Category::select('id', 'name', 'slug', 'image')
            ->with(['subcategories' => fn ($q) =>
                $q->select('id', 'category_id', 'name', 'slug')
                  ->orderBy('sort_order')
                  ->limit(10)
            ])
            ->limit(10)
            ->get();

        return view('payment.success', compact('order', 'categoriesWithSubs'));
    }

    public function failed()
    {
        $categoriesWithSubs = Category::select('id', 'name', 'slug', 'image')
            ->with(['subcategories' => fn ($q) =>
                $q->select('id', 'category_id', 'name', 'slug')
                  ->orderBy('sort_order')
                  ->limit(10)
            ])
            ->limit(10)
            ->get();

        return view('payment.failed', compact('categoriesWithSubs'));
    }
}