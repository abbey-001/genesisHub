<?php

namespace App\Services;

use App\Models\DeliveryZone;
use App\Models\Product;

/**
 * CartTotalService
 *
 * Single source of truth for all cart-related calculations.
 * Both CartController and PaymentController delegate here so
 * the checkout display and the payment charge are always identical.
 */
class CartTotalService
{
    /**
     * Sum of (price × quantity) for every item in the cart.
     */
    public function calculateSubtotal(array $cart): float
    {
        $subtotal = 0.0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        return $subtotal;
    }

    /**
     * Calculate the total delivery fee for a cart against a buyer's delivery zone.
     *
     * Rules:
     *  - Items from sellers in the SAME zone  → charged as ONE delivery (deduplicated).
     *  - Items from sellers in DIFFERENT zones → each unique zone charged separately,
     *    then a 15% multi-seller discount is applied.
     *  - A seller with no zone set is treated as "Not Included" (fallback pricing).
     */
    public function calculateDeliveryFee(array $cart, string $deliveryZone): float
    {
        if (empty($cart)) {
            return 0.0;
        }

        $cartKeys   = array_keys($cart);
        $productIds = array_unique(array_map(fn($k) => (int) explode('_', $k)[0], $cartKeys));

        $products = Product::whereIn('id', $productIds)
            ->with('shop')
            ->get()
            ->keyBy('id');

        $zoneFeeMap = [];

        foreach ($productIds as $productId) {
            $product    = $products->get($productId);
            $pickupZone = $product?->shop?->delivery_zone ?? 'Not Included';

            if (array_key_exists($pickupZone, $zoneFeeMap)) {
                continue;
            }

            $fee = DeliveryZone::getPrice($pickupZone, $deliveryZone);

            if ($fee === null && $pickupZone !== 'Not Included') {
                $fee = DeliveryZone::getPrice('Not Included', $deliveryZone);
            }

            $zoneFeeMap[$pickupZone] = $fee ?? 0;
        }

        $uniqueZoneCount = count($zoneFeeMap);
        $rawTotal        = (float) array_sum($zoneFeeMap);

        return $uniqueZoneCount >= 2
            ? round($rawTotal * 0.85, 2)
            : $rawTotal;
    }

    /**
     * Calculate the grand total: subtotal + delivery fee.
     * Pass $deliveryFee = null when fee is not yet known.
     */
    public function calculateGrandTotal(array $cart, ?float $deliveryFee): float
    {
        return $this->calculateSubtotal($cart) + ($deliveryFee ?? 0.0);
    }

    // =========================================================================
    // DELIVERY ESTIMATE
    // =========================================================================

    /**
     * Calculate the delivery estimate for the items currently in the cart.
     *
     * Logic:
     *   1. Load the product for each unique product ID in the cart.
     *   2. Find the "slowest" product — highest max_ready_days.
     *      In-stock products use Product::IN_STOCK_MAX_DAYS (platform constant).
     *      Pre-order / made-to-order products use their declared max_ready_days.
     *   3. Add Product::TRANSIT_DAYS (pickup + delivery buffer) to the ready window.
     *   4. For in-stock-only carts the estimate is [1, IN_STOCK_MAX_DAYS + TRANSIT_DAYS].
     *      For carts with any non-in-stock item the estimate is
     *      [TRANSIT_DAYS + 1, slowestMaxReadyDays + TRANSIT_DAYS].
     *
     * Returns:
     *   [
     *     'min'            => int,   // minimum days from payment to doorstep
     *     'max'            => int,   // maximum days
     *     'has_preorder'   => bool,  // true when any item requires waiting
     *     'slowest_item'   => string|null,  // name of the item causing the longest wait
     *     'slowest_days'   => int,   // the max_ready_days of the slowest item
     *   ]
     */
    public function calculateDeliveryEstimate(array $cart): array
    {
        if (empty($cart)) {
            return $this->emptyEstimate();
        }

        $productIds = array_unique(
            array_map(fn($k) => (int) explode('_', $k)[0], array_keys($cart))
        );

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $slowestDays     = 0;
        $slowestItemName = null;
        $hasPreorder     = false;

        foreach ($productIds as $productId) {
            $product = $products->get($productId);

            if (!$product) {
                continue;
            }

            $readyDays = $product->getMaxReadyDays();

            if ($product->requiresWaiting()) {
                $hasPreorder = true;
            }

            if ($readyDays > $slowestDays) {
                $slowestDays     = $readyDays;
                $slowestItemName = $product->name;
            }
        }

        // If somehow every product was missing from DB, fall back gracefully.
        if ($slowestDays === 0) {
            $slowestDays = Product::IN_STOCK_MAX_DAYS;
        }

        $transit = Product::TRANSIT_DAYS;

        // Min: at best everything is ready same-day and rider delivers next day.
        // For in-stock-only carts min = 1 (ready day 1, delivered day 1+transit).
        // For carts with non-in-stock items min = transit + 1 (optimistic but honest).
        $min = $hasPreorder ? ($transit + 1) : 1;
        $max = $slowestDays + $transit;

        // Clamp: max must always be ≥ min.
        $max = max($max, $min);

        return [
            'min'          => $min,
            'max'          => $max,
            'has_preorder' => $hasPreorder,
            'slowest_item' => $slowestItemName,
            'slowest_days' => $slowestDays,
        ];
    }

    /**
     * Pick a random funny apology message for pre-order/made-to-order delays.
     * The $productName is woven into the message so it feels personal.
     */
public function getApologyMessage(string $productName): string
{
    $messages = [
        "Ahhh, we go lie for you? Your <strong>{$productName}</strong> never ready yet 😅 The seller dey try, but this one no be indomie wey go done in 2 minutes. Abeg bear with us small.",

        "Omo… your <strong>{$productName}</strong> still dey production line 😭 Dem dey handle am like say na wedding cake. We sorry well well, e go soon land.",

        "No vex abeg 🙏 Your <strong>{$productName}</strong> dey under construction. The seller no ghost you, dem just dey hustle to make am correct. Small patience, e dey come.",

        "We check am, your <strong>{$productName}</strong> never enter delivery stage. E still dey ‘in the oven’ 🔥 No be scam, we promise. Just give am small time.",

        "Chai 😭 Your <strong>{$productName}</strong> still dey cook. Not literally sha… but you get the idea. We dey beg, no vex for the delay.",

        "If we fit rush am, we for don do am. But your <strong>{$productName}</strong> na premium vibes only 😌 Seller dey prepare am well well. Sorry for the wait!",

        "We no go sugarcoat am — your <strong>{$productName}</strong> still dey process 😅 But e go land, and when e land, you go happy say you wait small.",

        "Your <strong>{$productName}</strong> dey come… but e dey come like Lagos traffic 🚗💨 Small hold-up here and there. We dey sorry!",

        "Make we no lie, your <strong>{$productName}</strong> still dey find road come your side 😭 But everything dey under control. E go soon reach you.",

        "Abeg no vex 🙏 Your <strong>{$productName}</strong> no ready yet, but dem dey work on am like say na final year project. E go worth the wait!",
    ];

    return $messages[array_rand($messages)];
}
    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function emptyEstimate(): array
    {
        return [
            'min'          => 1,
            'max'          => Product::IN_STOCK_MAX_DAYS + Product::TRANSIT_DAYS,
            'has_preorder' => false,
            'slowest_item' => null,
            'slowest_days' => Product::IN_STOCK_MAX_DAYS,
        ];
    }
}