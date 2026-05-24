<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\DeliveryBundle;
use App\Models\DeliveryBroadcast;
use App\Models\Rider;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Seller;
use App\Notifications\NewDeliveryAvailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * DeliveryService — simplified wait-for-all strategy.
 *
 * Core rule: riders are ONLY notified when EVERY seller in the ENTIRE order
 * has marked their items ready. This guarantees:
 *   • One delivery trip per pickup zone (maximum cost savings).
 *   • No partial or growing broadcasts — riders always see complete jobs.
 *   • No race-condition complexity from concurrent seller ready-marks.
 *
 * Flow when a seller marks items ready:
 *   1. Create the Delivery record for that seller (status = pending).
 *   2. Check if ALL sellers across the whole order are now ready.
 *   3. If not yet: silently wait. Seller sees a "waiting for others" message.
 *   4. If yes: build bundles per zone, create broadcasts, notify riders.
 */
class DeliveryService
{
    public function __construct(
        protected TelegramService $telegram,
    ) {}

    // ═══════════════════════════════════════════════════════════════
    //  BUNDLE INITIALISATION — called once per order at payment time
    // ═══════════════════════════════════════════════════════════════

    /**
     * Pre-create one DeliveryBundle per pickup zone at payment time.
     * Groups all shops by their delivery_zone and sets the expected_count.
     * Idempotent — safe to call multiple times.
     */
    public function initialiseBundles(Order $order): void
    {
        $sellerZones = $order->items()
            ->join('sellers', 'order_items.seller_id', '=', 'sellers.id')
            ->join('shops',   'sellers.id',            '=', 'shops.seller_id')
            ->select('order_items.seller_id', 'shops.delivery_zone')
            ->distinct()
            ->get()
            ->groupBy(fn($row) => $row->delivery_zone ?: 'Not Included');

        foreach ($sellerZones as $zone => $rows) {
            // Singletons get a bundle too — simplifies the "all ready" check.
            $exists = DeliveryBundle::where('order_id', $order->id)
                                    ->where('pickup_zone', $zone)
                                    ->exists();
            if ($exists) {
                continue;
            }

            DeliveryBundle::create([
                'order_id'       => $order->id,
                'pickup_zone'    => $zone,
                'status'         => 'waiting',
                'expected_count' => $rows->count(),
                'ready_count'    => 0,
            ]);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  SELLER READY — called from OrderController::markItemReady()
    // ═══════════════════════════════════════════════════════════════

    /**
     * Called when ALL of a seller's items in an order are marked ready.
     *
     * Steps:
     *   1. Create the Delivery record for this seller.
     *   2. Increment the ready_count on their zone's bundle.
     *   3. Check if every seller across the ENTIRE order is now ready.
     *   4. If all ready: broadcast all zones to riders.
     *   5. If not: return a "waiting for others" message to the seller.
     *
     * Returns an array with:
     *   broadcast (bool) — whether riders were notified
     *   message   (string) — shown as a flash message to the seller
     */
    public function handleSellerReady(Order $order, Seller $seller, $sellerItems): array
    {
        DB::beginTransaction();
        try {
            $seller->loadMissing('shop');
            $zone = $seller->shop->delivery_zone ?? 'Not Included';

            // Step 1: create this seller's Delivery record.
            $delivery = $this->createDelivery($order, $seller, $sellerItems);

            // Step 2: get or create the bundle for this zone and increment.
            $bundle = $this->getOrCreateBundle($order, $zone);
            $bundle->markOneReady();
            $bundle->refresh();

            // Step 3: are ALL sellers across the entire order ready?
            $orderAllReady = $this->isEntireOrderReady($order);

            if (!$orderAllReady) {
                DB::commit();

                // Tell the seller how many others are still preparing.
                $pendingCount = $this->countPendingSellers($order);

                return [
                    'broadcast' => false,
                    'message'   => "Your items are ready! Waiting for {$pendingCount} other seller(s) in this order to prepare their items before a rider is assigned. This ensures everything arrives in one trip.",
                ];
            }

            // Step 4: all sellers ready — build all zone bundles and broadcast.
            DB::commit();

            $this->broadcastAllZones($order);

            return [
                'broadcast' => true,
                'message'   => 'All sellers are ready! Riders have been notified and will collect all items for this order.',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  BROADCAST ALL ZONES — fires when the entire order is ready
    // ═══════════════════════════════════════════════════════════════

    /**
     * Create one broadcast per pickup zone for the order.
     * Zones with a single seller get a solo-delivery broadcast.
     * Zones with multiple sellers get a bundle broadcast.
     *
     * Called only when isEntireOrderReady() is true.
     */
    public function broadcastAllZones(Order $order): void
    {
        $order->load(['deliveryBundles.deliveries.seller.shop', 'deliveryBundles.deliveries.items']);

        foreach ($order->deliveryBundles as $bundle) {
            // Only broadcast bundles that are ready and haven't been broadcast yet.
            if ($bundle->status !== 'waiting' && $bundle->status !== 'ready') {
                continue;
            }

            $bundle->update(['status' => 'ready', 'broadcast_at' => now()]);

            if ($bundle->deliveries->count() === 1) {
                // Single seller in zone — solo broadcast.
                $delivery = $bundle->deliveries->first();
                $this->broadcastSingleDelivery($delivery);
            } else {
                // Multiple sellers in zone — bundle broadcast.
                $this->broadcastBundle($bundle);
            }
        }
    }

    /**
     * Broadcast a single delivery (one seller, one zone).
     */
    private function broadcastSingleDelivery(Delivery $delivery): DeliveryBroadcast
    {
        DB::beginTransaction();
        try {
            $broadcast = DeliveryBroadcast::create([
                'delivery_id' => $delivery->id,
                'bundle_id'   => null,
                'is_partial'  => false,
                'status'      => 'active',
                'locked_at'   => null,
            ]);

            $companies = $this->getActiveCompanies();

            foreach ($companies as $company) {
                if ($company->user) {
                    $company->user->notify(new NewDeliveryAvailable($delivery, $broadcast));
                }

                $broadcast->riders()->attach($company->id, [
                    'response'  => 'pending',
                    'viewed_at' => null,
                ]);

                if ($company->telegram_chat_id) {
                    $fee = (int) ($delivery->delivery_fee ?? 0);
                    $this->telegram->notifyNewBroadcast($company->telegram_chat_id, $broadcast, $fee);
                }
            }

            DB::commit();
            return $broadcast;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Broadcast a bundle (multiple sellers in one zone).
     * All sellers are ready — no partial flags, no growing state.
     */
    private function broadcastBundle(DeliveryBundle $bundle): DeliveryBroadcast
    {
        DB::beginTransaction();
        try {
            $broadcast = DeliveryBroadcast::create([
                'delivery_id' => null,
                'bundle_id'   => $bundle->id,
                'is_partial'  => false,   // always false — we only broadcast complete bundles
                'status'      => 'active',
                'locked_at'   => null,
            ]);

            $bundle->load(['deliveries.seller.shop', 'order']);

            $companies = $this->getActiveCompanies();

            foreach ($companies as $company) {
                if ($company->user) {
                    $company->user->notify(
                        new NewDeliveryAvailable($bundle->deliveries->first(), $broadcast, $bundle)
                    );
                }

                $broadcast->riders()->attach($company->id, [
                    'response'  => 'pending',
                    'viewed_at' => null,
                ]);

                $this->sendTelegramBroadcastNotification($company, $broadcast, $bundle);
            }

            DB::commit();
            return $broadcast;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  DELIVERY LIFECYCLE (unchanged from original)
    // ═══════════════════════════════════════════════════════════════

    public function markPickedUp(Delivery $delivery, $pickupPhoto): Delivery
    {
        DB::beginTransaction();
        try {
            $photoPath = null;
            if ($pickupPhoto) {
                $photoPath = $pickupPhoto->store('deliveries/pickup-photos', 'public');
            }

            $delivery->update([
                'status'       => 'picked_up',
                'picked_up_at' => now(),
                'pickup_photo' => $photoPath,
            ]);

            OrderItem::whereIn(
                'id',
                $delivery->items()->pluck('order_item_id')->toArray()
            )->update(['status' => 'picked_up']);

            DB::commit();

            $delivery->load('order');
            if ($delivery->rider?->telegram_chat_id) {
                $this->telegram->sendMessage(
                    $delivery->rider->telegram_chat_id,
                    "🚴 <b>Package picked up!</b>\n\n"
                    . "Order: <code>{$delivery->order->order_number}</code>\n"
                    . "📍 Deliver to: {$delivery->delivery_address}\n\n"
                    . "Tap below when you've completed the delivery.",
                    [
                        'inline_keyboard' => [[
                            ['text' => '✅ Mark Delivered', 'callback_data' => "confirm_delivered:{$delivery->id}"],
                            ['text' => '❌ Report Failed',  'callback_data' => "fail_delivery:{$delivery->id}"],
                        ]],
                    ]
                );
            }

            return $delivery;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function completeDelivery(Delivery $delivery, $proofPhotoPath = null): Delivery
    {
        DB::beginTransaction();
        try {
            $delivery->markAsDelivered($proofPhotoPath);

            if ($delivery->bundle_id) {
                $bundle  = $delivery->bundle;
                $allDone = $bundle->deliveries()->where('status', '!=', 'delivered')->doesntExist();
                if ($allDone) {
                    $bundle->update(['status' => 'completed']);
                }
            }

            $order = $delivery->order;
            $order->load('items');
            if ($order->items->count() > 0 && $order->items->every(fn($i) => $i->status === 'delivered')) {
                $order->update(['status' => 'delivered', 'delivered_at' => now()]);
            }

            DB::commit();

            if ($delivery->rider?->telegram_chat_id) {
                $fee = number_format($delivery->delivery_fee ?? 0);
                $this->telegram->sendMessage(
                    $delivery->rider->telegram_chat_id,
                    "🎉 <b>Delivery Completed!</b>\n\n"
                    . "Order: <code>{$order->order_number}</code>\n"
                    . "💰 Earned: ₦{$fee}\n\n"
                    . "Use /earnings to see your updated balance."
                );
            }

            return $delivery;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function markFailed(Delivery $delivery, array $data): Delivery
    {
        DB::beginTransaction();
        try {
            $photoPath = null;
            if (isset($data['failure_photo'])) {
                $photoPath = $data['failure_photo']->store('deliveries/failure-photos', 'public');
            }

            $delivery->update([
                'status'         => 'failed',
                'failed_at'      => now(),
                'failure_reason' => $data['failure_reason'],
                'failure_notes'  => $data['failure_notes'] ?? 'Reported via Telegram',
                'failure_photo'  => $photoPath,
            ]);

            OrderItem::whereIn(
                'id',
                $delivery->items()->pluck('order_item_id')->toArray()
            )->update(['status' => 'delivery_failed']);

            $delivery->rider?->increment('failed_deliveries');

            DB::commit();

            if ($delivery->rider?->telegram_chat_id) {
                $this->telegram->sendMessage(
                    $delivery->rider->telegram_chat_id,
                    "⚠️ <b>Delivery marked as failed.</b>\n\n"
                    . "Order: <code>{$delivery->order->order_number}</code>\n"
                    . "Reason: {$delivery->failure_reason}\n\n"
                    . "The admin team will review this."
                );
            }

            return $delivery;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  CORE DELIVERY RECORD CREATION
    // ═══════════════════════════════════════════════════════════════

    /**
     * Create a single Delivery record for one seller.
     * Always creates with bundle_id = null; the bundle relationship is managed
     * separately through DeliveryBundle. Broadcasts are NOT fired here.
     */
    public function createDelivery(Order $order, Seller $seller, $sellerItems, ?int $bundleId = null): Delivery
    {
        DB::beginTransaction();
        try {
            $seller->loadMissing('shop');
            $shop = $seller->shop;

            $deliveryAddress = $order->shipping_address;
            if ($order->shipping_city)        $deliveryAddress .= ', ' . $order->shipping_city;
            if ($order->shipping_state)       $deliveryAddress .= ', ' . $order->shipping_state;
            if ($order->shipping_postal_code) $deliveryAddress .= ' '  . $order->shipping_postal_code;
            if ($order->shipping_country)     $deliveryAddress .= ', ' . $order->shipping_country;

            // Find the bundle for this seller's zone so we can link the delivery.
            if ($bundleId === null) {
                $zone   = $shop?->delivery_zone ?? 'Not Included';
                $bundle = DeliveryBundle::where('order_id', $order->id)
                                        ->where('pickup_zone', $zone)
                                        ->first();
                $bundleId = $bundle?->id;
            }

            $delivery = Delivery::create([
                'order_id'         => $order->id,
                'seller_id'        => $seller->id,
                'bundle_id'        => $bundleId,
                'status'           => 'pending',
                'pickup_address'   => $shop?->address ?? $seller->address ?? 'Pickup address not set',
                'pickup_latitude'  => null,
                'pickup_longitude' => null,
                'delivery_address' => $deliveryAddress,
                'package_weight'   => $sellerItems->sum('package_weight') ?: 0,
                'package_notes'    => $sellerItems->pluck('package_notes')->filter()->implode('; ') ?: null,
                'delivery_fee'     => $this->calculateDeliveryFee($order, $seller),
            ]);

            foreach ($sellerItems as $item) {
                $delivery->items()->attach($item->id);
            }

            DB::commit();
            return $delivery;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  FEE CALCULATION
    // ═══════════════════════════════════════════════════════════════

    protected function calculateDeliveryFee(Order $order, Seller $seller): int
    {
        $deliveryZone = $order->shipping_zone ?? null;
        if (!$deliveryZone) {
            throw new \RuntimeException(
                "Cannot calculate delivery fee: order #{$order->order_number} has no shipping zone."
            );
        }

        $thisPickupZone = $seller->shop->delivery_zone ?? 'Not Included';

        $rows = DB::table('order_items')
            ->join('sellers', 'order_items.seller_id', '=', 'sellers.id')
            ->join('shops',   'sellers.id',            '=', 'shops.seller_id')
            ->where('order_items.order_id', $order->id)
            ->select('order_items.seller_id', 'shops.delivery_zone')
            ->distinct()
            ->get();

        $zoneFeeMap = [];
        foreach ($rows as $row) {
            $zone = $row->delivery_zone ?: 'Not Included';
            if (!array_key_exists($zone, $zoneFeeMap)) {
                $fee = \App\Models\DeliveryZone::getPrice($zone, $deliveryZone);
                if ($fee === null && $zone !== 'Not Included') {
                    $fee = \App\Models\DeliveryZone::getPrice('Not Included', $deliveryZone);
                }
                $zoneFeeMap[$zone] = (int) ($fee ?? 0);
            }
        }

        $zoneSellerCount = $rows
            ->groupBy(fn($r) => $r->delivery_zone ?: 'Not Included')
            ->map(fn($group) => $group->count());

        $uniqueZoneCount = count($zoneFeeMap);
        $discount        = $uniqueZoneCount >= 2 ? 0.85 : 1.0;

        $thisRawFee      = $zoneFeeMap[$thisPickupZone] ?? 0;
        $sellersInMyZone = $zoneSellerCount[$thisPickupZone] ?? 1;

        if ($thisRawFee === 0) {
            return 0;
        }

        return (int) round(($thisRawFee * $discount) / $sellersInMyZone);
    }

    public static function lookupFee(string $pickupZone, string $deliveryZone): ?int
    {
        return \App\Models\DeliveryZone::getPrice($pickupZone, $deliveryZone);
    }

    // ═══════════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════

    /**
     * True when every seller in the order has marked all their items
     * as ready_for_pickup (or beyond).
     */
    private function isEntireOrderReady(Order $order): bool
    {
        $order->loadMissing('items');

        if ($order->items->isEmpty()) {
            return false;
        }

        return $order->items->every(
            fn($item) => in_array($item->status, ['ready_for_pickup', 'picked_up', 'delivered'])
        );
    }

    /**
     * Count sellers in this order who have NOT yet marked all items ready.
     */
    private function countPendingSellers(Order $order): int
    {
        $order->loadMissing('items');

        $notReadyStatuses = ['pending', 'processing'];

        return $order->items
            ->filter(fn($item) => in_array($item->status, $notReadyStatuses))
            ->pluck('seller_id')
            ->unique()
            ->count();
    }

    private function getOrCreateBundle(Order $order, string $zone): DeliveryBundle
    {
        return DeliveryBundle::firstOrCreate(
            ['order_id' => $order->id, 'pickup_zone' => $zone],
            [
                'status'         => 'waiting',
                'expected_count' => $this->countSellersInZone($order, $zone),
                'ready_count'    => 0,
            ]
        );
    }

    private function countSellersInZone(Order $order, string $zone): int
    {
        return $order->items()
            ->join('sellers', 'order_items.seller_id', '=', 'sellers.id')
            ->join('shops',   'sellers.id',            '=', 'shops.seller_id')
            ->where(function ($q) use ($zone) {
                if ($zone === 'Not Included') {
                    $q->whereNull('shops.delivery_zone')
                      ->orWhere('shops.delivery_zone', '');
                } else {
                    $q->where('shops.delivery_zone', $zone);
                }
            })
            ->distinct()
            ->count('order_items.seller_id');
    }

    private function getActiveCompanies()
    {
        return Rider::where('is_active', true)
                    ->where('is_verified', true)
                    ->with('user')
                    ->get();
    }

    private function sendTelegramBroadcastNotification(
        Rider             $company,
        DeliveryBroadcast $broadcast,
        ?DeliveryBundle   $bundle = null
    ): void {
        if (!$company->telegram_chat_id) {
            return;
        }

        try {
            $fee = $bundle
                ? (int) $bundle->deliveries->sum('delivery_fee')
                : (int) ($broadcast->delivery->delivery_fee ?? 0);

            $this->telegram->notifyNewBroadcast($company->telegram_chat_id, $broadcast, $fee);
        } catch (\Exception $e) {
            Log::warning("Telegram notification failed for rider #{$company->id}: " . $e->getMessage());
        }
    }
}
