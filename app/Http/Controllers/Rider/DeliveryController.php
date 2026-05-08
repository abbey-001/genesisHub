<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryBroadcast;
use App\Models\DeliveryBundle;
use App\Models\DeliveryZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\NewDeliveryAssigned;

class DeliveryController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    //  AVAILABLE — full page load
    // ═══════════════════════════════════════════════════════════════

    public function available()
    {
        $company    = Auth::user()->rider;
        $broadcasts = $this->fetchActiveBroadcasts();
        $fees       = $this->computeDisplayFees($broadcasts);

        return view('rider.deliveries.available', compact('broadcasts', 'company', 'fees'));
    }

    // ═══════════════════════════════════════════════════════════════
    //  AVAILABLE POLL — AJAX-only JSON endpoint
    //
    //  Returns:
    //    count  — total active broadcasts (updates the header badge)
    //    ids    — current broadcast ids (change-detection in JS)
    //    html   — rendered _available_cards partial (replaces grid div)
    // ═══════════════════════════════════════════════════════════════

    public function availablePoll()
    {
        $broadcasts = $this->fetchActiveBroadcasts();
        $fees       = $this->computeDisplayFees($broadcasts);

        $html = view('rider.deliveries._available_cards', compact('broadcasts', 'fees'))->render();

        return response()->json([
            'count' => $broadcasts->count(),
            'ids'   => $broadcasts->pluck('id')->toArray(),
            'html'  => $html,
        ]);
    }

    // ───────────────────────────────────────────────────────────────
    //  PRIVATE — shared query used by both available() and availablePoll()
    // ───────────────────────────────────────────────────────────────

    private function fetchActiveBroadcasts()
    {
        $singles = DeliveryBroadcast::where('status', 'active')
            ->whereNotNull('delivery_id')
            ->whereNull('bundle_id')
            ->whereHas('delivery', fn($q) => $q->where('status', 'pending'))
            ->with(['delivery.order', 'delivery.seller.shop', 'delivery.items'])
            ->latest()
            ->get();

        $bundles = DeliveryBroadcast::where('status', 'active')
            ->whereNotNull('bundle_id')
            ->whereNull('locked_at')                    // locked = already accepted, skip it
            ->whereHas('bundle', fn($q) => $q->whereIn('status', ['ready', 'growing', 'partial']))
            ->with(['bundle.deliveries.seller.shop', 'bundle.deliveries.items', 'bundle.order'])
            ->latest()
            ->get();

        return $singles->merge($bundles)->sortByDesc('created_at')->values();
    }

    // ───────────────────────────────────────────────────────────────
    //  PRIVATE — compute the correct display fee per broadcast
    //
    //  The blade was doing $bundle->deliveries->sum('delivery_fee').
    //  That is wrong for two reasons:
    //    1. Partial bundles: not all deliveries exist yet, so the sum
    //       is always short by the missing sellers' share.
    //    2. Even for complete bundles: the sum happens to be correct
    //       only because calculateDeliveryFee() splits the discounted
    //       total proportionally — but we're relying on arithmetic
    //       coincidence rather than the source of truth.
    //
    //  The source of truth is the zone-pricing matrix + the same
    //  15% multi-zone discount rule CartTotalService applies.
    //  We replicate that here, isolated to this bundle's pickup zone.
    //
    //  Returns array<int, int>  broadcast_id => fee (naira, rounded)
    // ───────────────────────────────────────────────────────────────

    public function computeDisplayFees($broadcasts): array
    {
        $fees = [];

        foreach ($broadcasts as $broadcast) {

            // Single delivery: fee is already stored correctly on the row.
            if (!$broadcast->is_bundle) {
                $fees[$broadcast->id] = (int) ($broadcast->delivery->delivery_fee ?? 0);
                continue;
            }

            $bundle       = $broadcast->bundle;
            $order        = $bundle->order;
            $deliveryZone = $order->shipping_zone ?? null;

            // No delivery zone stored — fall back to summing existing rows.
            if (!$deliveryZone) {
                $fees[$broadcast->id] = (int) $bundle->deliveries->sum('delivery_fee');
                continue;
            }

            // Step 1: count ALL distinct pickup zones across the entire order.
            // We need the full count (not just this bundle's zone) to decide
            // whether the 15% multi-zone discount applies.
            $allZoneCount = DB::table('order_items')
                ->join('sellers', 'order_items.seller_id', '=', 'sellers.id')
                ->join('shops',   'sellers.id',            '=', 'shops.seller_id')
                ->where('order_items.order_id', $order->id)
                ->distinct()
                ->count(DB::raw("COALESCE(NULLIF(shops.delivery_zone, ''), 'Not Included')"));

            $discount = $allZoneCount >= 2 ? 0.85 : 1.0;

            // Step 2: raw fee for this bundle's pickup zone only.
            $pickupZone = $bundle->pickup_zone;
            $rawFee     = DeliveryZone::getPrice($pickupZone, $deliveryZone);

            if ($rawFee === null && $pickupZone !== 'Not Included') {
                $rawFee = DeliveryZone::getPrice('Not Included', $deliveryZone);
            }

            // Step 3: apply the discount the customer was actually charged.
            $fees[$broadcast->id] = (int) round((int) ($rawFee ?? 0) * $discount);
        }

        return $fees;
    }

    // ───────────────────────────────────────────────────────────────
    //  PRIVATE — true total fee the rider earns for one bundle zone
    //
    //  Used by active() and show() to display the right total, and by
    //  complete() to write the correct per-row delivery_fee back to the
    //  database before the rows are marked delivered (so that all
    //  earnings queries — which sum delivery_fee on status='delivered'
    //  rows — always produce the correct figure).
    //
    //  Rules (matching CartTotalService and DeliveryService exactly):
    //    • One raw fee per unique pickup zone in the order.
    //    • 15% discount only when 2+ DISTINCT zones exist in the order.
    //    • Same-zone sellers split the zone fee equally among themselves.
    //    • The total fee for a bundle = zoneRawFee × discount.
    //      (Each delivery row stores that ÷ sellersInZone, and summing
    //       all rows in the bundle gives this total back.)
    //
    //  Returns: int  total naira the rider earns for completing the bundle.
    // ───────────────────────────────────────────────────────────────

    private function computeBundleTotalFee(DeliveryBundle $bundle): int
    {
        $order        = $bundle->order;
        $deliveryZone = $order->shipping_zone ?? null;

        if (!$deliveryZone) {
            // No shipping zone stored — fall back to summing stored rows.
            return (int) $bundle->deliveries->sum('delivery_fee');
        }

        // All (seller, zone) pairs in the order — using the correct join path.
        $rows = DB::table('order_items')
            ->join('sellers', 'order_items.seller_id', '=', 'sellers.id')
            ->join('shops',   'sellers.id',            '=', 'shops.seller_id')
            ->where('order_items.order_id', $order->id)
            ->select('order_items.seller_id', 'shops.delivery_zone')
            ->distinct()
            ->get();

        // Count distinct zones across the whole order (for discount decision).
        $distinctZones = $rows->map(fn($r) => $r->delivery_zone ?: 'Not Included')->unique();
        $discount      = $distinctZones->count() >= 2 ? 0.85 : 1.0;

        // Raw fee for this bundle's pickup zone only.
        $pickupZone = $bundle->pickup_zone;
        $rawFee     = DeliveryZone::getPrice($pickupZone, $deliveryZone);

        if ($rawFee === null && $pickupZone !== 'Not Included') {
            $rawFee = DeliveryZone::getPrice('Not Included', $deliveryZone);
        }

        return (int) round((int) ($rawFee ?? 0) * $discount);
    }

    // ───────────────────────────────────────────────────────────────
    //  PRIVATE — correct per-row delivery_fee for one delivery in a bundle
    //
    //  = computeBundleTotalFee ÷ number of sellers sharing that zone.
    //  Written back to each delivery row in complete() so earnings
    //  queries are always correct regardless of when the row was created.
    // ───────────────────────────────────────────────────────────────

    private function computePerDeliveryFee(Delivery $delivery, DeliveryBundle $bundle): int
    {
        $totalFee = $this->computeBundleTotalFee($bundle);

        // Count how many delivery rows exist in this bundle (all sellers in zone).
        $sellersInBundle = $bundle->deliveries()->count();
        if ($sellersInBundle <= 0) {
            return $totalFee;
        }

        return (int) round($totalFee / $sellersInBundle);
    }

    // ═══════════════════════════════════════════════════════════════
    //  ACCEPT
    // ═══════════════════════════════════════════════════════════════

    public function accept(DeliveryBroadcast $broadcast)
    {
        $company = Auth::user()->rider;

        if (!$company->is_active || !$company->is_verified) {
            return back()->with('error', 'Your company account is not active.');
        }

        if ($company->activeDeliveries()->count() >= 10) {
            return back()->with('error', 'You have reached the maximum number of active deliveries (10).');
        }

        DB::beginTransaction();
        try {
            if ($broadcast->is_bundle) {
                $bundle = $broadcast->bundle;

                // Accept is valid for 'ready' (all sellers confirmed), 'growing'
                // (broadcast live but not all sellers ready yet — rider gets the
                // stops confirmed so far; late sellers get solo broadcasts), and
                // 'partial' (legacy timeout path, kept for backwards compat).
                if (!in_array($bundle->status, ['ready', 'growing', 'partial'])) {
                    DB::rollBack();
                    return back()->with('error', 'This bundle has already been accepted or is no longer available.');
                }

                // Assign only the deliveries that exist RIGHT NOW (pending status).
                // Any sellers who mark ready after this lock will get their own
                // solo broadcast — DeliveryService::handleSellerReady() checks
                // for locked_at before deciding to append vs. solo-broadcast.
                $bundle->deliveries()->where('status', 'pending')->update([
                    'rider_id'    => $company->id,
                    'status'      => 'assigned',
                    'assigned_at' => now(),
                ]);

                $bundle->update(['status' => 'accepted']);

                // markAsAccepted() sets accepted_at AND locked_at atomically,
                // preventing any further stops from being appended to this broadcast.
                $broadcast->markAsAccepted($company);

                DB::commit();

                $firstDelivery = $bundle->deliveries()->orderBy('id')->first();
                $firstDelivery->load(['order', 'seller.shop', 'items', 'bundle.deliveries.seller.shop']);
                $firstDelivery->rider->user?->notify(new NewDeliveryAssigned($firstDelivery));

                $stopCount = $bundle->deliveries()->where('status', 'assigned')->count();
                return redirect()->route('rider.deliveries.show', $firstDelivery)
                    ->with('success', "Bundle accepted! You have been assigned {$stopCount} pickup stop(s) in {$bundle->pickup_zone}.");

            } else {
                $delivery = $broadcast->delivery;

                if ($delivery->status !== 'pending') {
                    DB::rollBack();
                    return back()->with('error', 'This delivery has already been accepted by another company.');
                }

                $delivery->update([
                    'rider_id'    => $company->id,
                    'status'      => 'assigned',
                    'assigned_at' => now(),
                ]);

                // Lock the broadcast so it disappears from the available list.
                $broadcast->markAsAccepted($company);

                DB::commit();
                $delivery->load(['order', 'seller.shop', 'items']);
                $delivery->rider->user?->notify(new NewDeliveryAssigned($delivery));

                return redirect()->route('rider.deliveries.show', $delivery)
                    ->with('success', 'Delivery accepted! Please proceed to pickup location.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to accept: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  INDEX
    // ═══════════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $rider = Auth::user()->rider;

        $query = Delivery::where('rider_id', $rider->id)
            ->with(['order.user', 'seller.shop', 'items', 'bundle']);

        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('created_at', '<=', $request->date_to);
        if ($request->filled('search')) {
            $query->whereHas('order', fn($q) =>
                $q->where('order_number', 'like', '%' . $request->search . '%')
            );
        }

        $deliveries = $query->latest()->paginate(20);

        $stats = [
            'all'       => Delivery::where('rider_id', $rider->id)->count(),
            'active'    => Delivery::where('rider_id', $rider->id)->whereIn('status', ['assigned', 'picked_up'])->count(),
            'completed' => Delivery::where('rider_id', $rider->id)->where('status', 'delivered')->count(),
            'failed'    => Delivery::where('rider_id', $rider->id)->where('status', 'failed')->count(),
        ];

        return view('rider.deliveries.index', compact('deliveries', 'stats'));
    }

    // ═══════════════════════════════════════════════════════════════
    //  ACTIVE
    // ═══════════════════════════════════════════════════════════════

    public function active()
    {
        $rider = Auth::user()->rider;

        $deliveries = Delivery::where('rider_id', $rider->id)
            ->whereIn('status', ['assigned', 'picked_up'])
            ->with(['order.user', 'seller.shop', 'items', 'bundle.deliveries.seller.shop', 'bundle.order'])
            ->orderByRaw("FIELD(status, 'picked_up', 'assigned')")
            ->get();

        $bundleGroups   = $deliveries->whereNotNull('bundle_id')->groupBy('bundle_id');
        $soloDeliveries = $deliveries->whereNull('bundle_id');

        // Pre-compute the correct total fee for every bundle group.
        // Keyed by bundle_id so the blade does a simple lookup.
        // We use computeBundleTotalFee() (zone-matrix + discount) rather than
        // summing stored delivery_fee rows, which may be wrong for deliveries
        // created before the fee calculation fix was deployed.
        $bundleFees = [];
        foreach ($bundleGroups as $bundleId => $bundleDeliveries) {
            $bundle = $bundleDeliveries->first()->bundle;
            $bundleFees[$bundleId] = $this->computeBundleTotalFee($bundle);
        }

        return view('rider.deliveries.active', compact('deliveries', 'bundleGroups', 'soloDeliveries', 'bundleFees'));
    }

    // ═══════════════════════════════════════════════════════════════
    //  COMPLETED
    // ═══════════════════════════════════════════════════════════════

    public function completed()
    {
        $rider = Auth::user()->rider;

        $deliveries = Delivery::where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->with(['order', 'seller.shop', 'bundle'])
            ->latest('delivered_at')
            ->paginate(20);

        $totalEarnings = Delivery::where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->sum('delivery_fee');

        return view('rider.deliveries.completed', compact('deliveries', 'totalEarnings'));
    }

    // ═══════════════════════════════════════════════════════════════
    //  FAILED
    // ═══════════════════════════════════════════════════════════════

    public function failed()
    {
        $rider = Auth::user()->rider;

        $deliveries = Delivery::where('rider_id', $rider->id)
            ->where('status', 'failed')
            ->with(['order', 'seller.shop'])
            ->latest('failed_at')
            ->paginate(20);

        return view('rider.deliveries.failed', compact('deliveries'));
    }

    // ═══════════════════════════════════════════════════════════════
    //  SHOW
    // ═══════════════════════════════════════════════════════════════

    public function show(Delivery $delivery)
    {
        $this->authorize('view', $delivery);

        $delivery->load([
            'order.user', 'seller.shop', 'items.product',
            'bundle.deliveries.seller.shop', 'bundle.deliveries.items', 'bundle.order',
        ]);

        $bundleSiblings = $delivery->bundle_id
            ? $delivery->bundle->deliveries->where('id', '!=', $delivery->id)->values()
            : collect();

        // Correct total fee the rider earns for the whole bundle (or single delivery).
        // Passed to the blade so the sidebar "Total Fee / Delivery Fee" card always
        // shows the right amount regardless of what is stored in delivery_fee rows.
        $bundleTotalFee = $delivery->bundle_id
            ? $this->computeBundleTotalFee($delivery->bundle)
            : (int) $delivery->delivery_fee;

        return view('rider.deliveries.show', compact('delivery', 'bundleSiblings', 'bundleTotalFee'));
    }

    // ═══════════════════════════════════════════════════════════════
    //  CONFIRM PICKUP
    // ═══════════════════════════════════════════════════════════════

    public function confirmPickup(Request $request, Delivery $delivery)
    {
        $this->authorize('update', $delivery);

        $request->validate([
            'pickup_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'notes'        => 'nullable|string|max:500',
        ]);

        if ($delivery->status !== 'assigned') {
            return back()->with('error', 'Cannot confirm pickup for this delivery.');
        }

        DB::beginTransaction();
        try {
            $photoPath = $request->file('pickup_photo')->store('deliveries/pickups', 'public');

            $delivery->update(['status' => 'picked_up', 'picked_up_at' => now(), 'pickup_photo' => $photoPath]);

            $itemIds = $delivery->items()->pluck('order_item_id')->toArray();
            \App\Models\OrderItem::whereIn('id', $itemIds)->update(['status' => 'picked_up']);

            $order = $delivery->order;
            if ($order->status === 'processing' && !$order->shipped_at) {
                $order->update(['status' => 'shipped', 'shipped_at' => now()]);
            }

            if ($delivery->bundle_id) {
                foreach ($delivery->bundle->deliveries()->where('id', '!=', $delivery->id)->where('status', 'assigned')->get() as $sibling) {
                    $sibling->update(['status' => 'picked_up', 'picked_up_at' => now(), 'pickup_photo' => $photoPath]);
                    \App\Models\OrderItem::whereIn('id', $sibling->items()->pluck('order_item_id')->toArray())->update(['status' => 'picked_up']);
                }
            }

            DB::commit();
            $delivery->load(['order', 'rider']);          // ensure relations fresh
            $order->user?->notify(new \App\Notifications\OrderShipped($delivery));

            return redirect()->route('rider.deliveries.show', $delivery)
                ->with('success', $delivery->bundle_id ? 'All packages in this zone picked up!' : 'Package picked up successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to confirm pickup: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  COMPLETE DELIVERY
    // ═══════════════════════════════════════════════════════════════

    public function complete(Request $request, Delivery $delivery)
    {
        $this->authorize('update', $delivery);

        $request->validate([
            'delivery_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'notes'          => 'nullable|string|max:500',
        ]);

        if ($delivery->status !== 'picked_up') {
            return back()->with('error', 'Invalid delivery status.');
        }

        // For bundle deliveries every sibling must be picked_up first.
        // The blade blocks the button, but we enforce it server-side too
        // so it cannot be bypassed via direct POST (curl, dev-tools, etc.).
        if ($delivery->bundle_id) {
            $unpickedSiblings = $delivery->bundle->deliveries()
                ->where('id', '!=', $delivery->id)
                ->where('status', '!=', 'picked_up')
                ->count();

            if ($unpickedSiblings > 0) {
                return back()->with('error',
                    "You must pick up all packages in this bundle before completing delivery. " .
                    "{$unpickedSiblings} package(s) have not been picked up yet."
                );
            }
        }

        // Use DB::transaction() (not manual beginTransaction) so that Laravel's
        // savepoint mechanism works correctly with the nested DB::transaction()
        // calls inside markAsDelivered() → processItemDelivered() → addPending().
        // manual beginTransaction() + nested DB::transaction() still uses savepoints
        // in Laravel, but DB::transaction() gives us cleaner rollback semantics and
        // ensures the closure is retried cleanly on deadlock.
        try {
            DB::transaction(function () use ($request, $delivery) {
                $photoPath = $request->file('delivery_photo')->store('deliveries/proofs', 'public');

                if ($delivery->bundle_id) {
                    $bundle = $delivery->bundle;
                    $bundle->load('deliveries.order');

                    $correctPerRowFee = $this->computePerDeliveryFee($delivery, $bundle);

                    $siblings = $bundle->deliveries()
                        ->where('id', '!=', $delivery->id)
                        ->get();

                    // Persist the corrected rider fee BEFORE markAsDelivered() so
                    // the row is correct when any earnings query reads delivery_fee.
                    $delivery->update(['delivery_fee' => $correctPerRowFee]);
                    $delivery->markAsDelivered($photoPath);

                    foreach ($siblings as $sibling) {
                        $sibling->update(['delivery_fee' => $correctPerRowFee]);
                        $sibling->markAsDelivered($photoPath);
                    }

                    $bundle->update(['status' => 'completed']);

                } else {
                    $delivery->markAsDelivered($photoPath);
                }
            });

            // Notification is sent OUTSIDE the transaction — a slow mail/queue
            // server cannot cause a DB rollback of an already-committed delivery.
            $delivery->load('order');
            $delivery->order->user?->notify(new \App\Notifications\OrderDelivered($delivery));

            return redirect()->route('rider.deliveries.completed')
                ->with('success', 'Delivery completed successfully! 🎉');

        } catch (\Exception $e) {
            Log::error('Failed to complete delivery', [
                'delivery_id' => $delivery->id,
                'error'       => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to complete delivery: ' . $e->getMessage());
        }
    }
    
    
    
    
    

    // ═══════════════════════════════════════════════════════════════
    //  MARK FAILED
    // ═══════════════════════════════════════════════════════════════

    public function fail(Request $request, Delivery $delivery)
    {
        $this->authorize('update', $delivery);

        $request->validate([
            'failure_reason' => 'required|in:customer_unavailable,wrong_address,refused,access_issue,damaged,other',
            'failure_notes'  => 'required|string|min:10|max:500',
            'failure_photo'  => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if (!in_array($delivery->status, ['assigned', 'picked_up'])) {
            return back()->with('error', 'Cannot mark this delivery as failed.');
        }

        DB::beginTransaction();
        try {
            $photoPath = $request->file('failure_photo')->store('deliveries/failures', 'public');

            $delivery->update([
                'status'         => 'failed',
                'failed_at'      => now(),
                'failure_reason' => $request->failure_reason,
                'failure_notes'  => $request->failure_notes,
                'failure_photo'  => $photoPath,
            ]);

            \App\Models\OrderItem::whereIn('id', $delivery->items()->pluck('order_item_id')->toArray())
                ->update(['status' => 'delivery_failed']);

            if ($delivery->rider) {
                $delivery->rider->increment('failed_deliveries');
            }

            // Sync order-level status so it reflects that items have failed
            // (e.g. order stays 'shipped' with mixed statuses rather than
            // incorrectly staying 'processing' when everything has failed).
            $delivery->order->syncStatusFromItems();

            DB::commit();

            return redirect()->route('rider.deliveries.failed')
                ->with('info', 'Delivery marked as failed. The admin team will review.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update delivery: ' . $e->getMessage());
        }
    }
}