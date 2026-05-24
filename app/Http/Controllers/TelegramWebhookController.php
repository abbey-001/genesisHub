<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\DeliveryBroadcast;
use App\Models\DeliveryBundle;
use App\Models\OrderItem;
use App\Models\Rider;
use App\Notifications\NewDeliveryAssigned;
use App\Services\PayoutService;
use App\Services\TelegramService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class TelegramWebhookController extends Controller
{
    public function __construct(
        protected TelegramService $telegram,
        protected PayoutService   $payoutService,
    ) {}

    // ═══════════════════════════════════════════════════════════════
    //  WEBHOOK ENTRY POINT
    // ═══════════════════════════════════════════════════════════════

    public function handle(Request $request)
    {
        if ($request->header('X-Telegram-Bot-Api-Secret-Token') !== config('services.telegram.webhook_secret')) {
            return response('Unauthorized', 401);
        }

        $update = $request->all();
        Log::debug('Telegram update', $update);

        if (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
        } elseif (isset($update['message'])) {
            $this->handleMessage($update['message']);
        }

        return response('OK');
    }

    // ═══════════════════════════════════════════════════════════════
    //  MESSAGE HANDLER
    // ═══════════════════════════════════════════════════════════════

    protected function handleMessage(array $message): void
    {
        $chatId = (string) $message['chat']['id'];
        $text   = trim($message['text'] ?? '');

        // /start — account linking
        if (str_starts_with($text, '/start')) {
            $parts = explode(' ', $text, 2);
            $token = $parts[1] ?? null;
            $token ? $this->linkAccount($chatId, $token) : $this->sendWelcomeOrStatus($chatId);
            return;
        }

        $rider = Rider::where('telegram_chat_id', $chatId)->first();

        if (!$rider) {
            $this->telegram->sendMessage($chatId,
                "👋 Hi! Please link your Telegram account first.\n\n"
                . "Go to <b>Profile → Connect Telegram</b> on the website."
            );
            return;
        }

        // Incoming photo — check cache state
        if (isset($message['photo'])) {
            $this->handleIncomingPhoto($chatId, $message, $rider);
            return;
        }

        match (true) {
            $text === '/deliveries' => $this->showActiveDeliveries($chatId, $rider),
            $text === '/available'  => $this->showAvailableDeliveries($chatId, $rider),
            $text === '/earnings'   => $this->showEarnings($chatId, $rider),
            $text === '/payout'     => $this->showPayoutMenu($chatId, $rider),
            $text === '/help'       => $this->sendHelp($chatId),
            $text === '/cancel'     => $this->cancelPendingState($chatId),
            default                 => $this->telegram->sendMessage($chatId,
                "I didn't understand that. Type /help to see available commands."
            ),
        };
    }

    // ═══════════════════════════════════════════════════════════════
    //  CALLBACK QUERY HANDLER
    // ═══════════════════════════════════════════════════════════════

    protected function handleCallbackQuery(array $callbackQuery): void
    {
        $chatId          = (string) $callbackQuery['message']['chat']['id'];
        $messageId       = $callbackQuery['message']['message_id'];
        $callbackQueryId = $callbackQuery['id'];
        $data            = $callbackQuery['data'];

        if ($data === 'noop') {
            $this->telegram->answerCallbackQuery($callbackQueryId);
            return;
        }

        $rider = Rider::where('telegram_chat_id', $chatId)->first();

        if (!$rider) {
            $this->telegram->answerCallbackQuery($callbackQueryId, 'Account not linked.', true);
            return;
        }

        $parts  = explode(':', $data, 3);
        $action = $parts[0];
        $id     = isset($parts[1]) ? (int) $parts[1] : null;
        $extra  = $parts[2] ?? null;

        match ($action) {
            'accept_broadcast'          => $this->acceptBroadcast($chatId, $messageId, $callbackQueryId, $rider, $id),
            'reject_broadcast'          => $this->rejectBroadcast($chatId, $messageId, $callbackQueryId, $rider, $id),
            'pickup'                    => $this->promptPickupPhoto($chatId, $callbackQueryId, $rider, $id),
            'confirm_pickup_nophoto'    => $this->confirmPickupNoPhoto($chatId, $messageId, $callbackQueryId, $rider, $id),
            'confirm_delivered'         => $this->promptDeliveryPhoto($chatId, $callbackQueryId, $rider, $id),
            'confirm_delivered_nophoto' => $this->confirmDeliveredNoPhoto($chatId, $messageId, $callbackQueryId, $rider, $id),
            'fail_delivery'             => $this->promptFailReason($chatId, $callbackQueryId, $id),
            'fail_reason'               => $this->processFail($chatId, $messageId, $callbackQueryId, $rider, $id, $extra),
            'payout_request'            => $this->processPayoutRequest($chatId, $callbackQueryId, $rider),
            'payout_confirm'            => $this->confirmPayoutRequest($chatId, $messageId, $callbackQueryId, $rider),
            default                     => $this->telegram->answerCallbackQuery($callbackQueryId, 'Unknown action'),
        };
    }

    // ═══════════════════════════════════════════════════════════════
    //  ACCOUNT LINKING
    // ═══════════════════════════════════════════════════════════════

    protected function linkAccount(string $chatId, string $token): void
    {
        Log::info('Telegram link attempt', ['chatId' => $chatId, 'token' => $token]);

        $rider = Rider::where('telegram_link_token', $token)->first();

        if (!$rider) {
            $this->telegram->sendMessage($chatId,
                '❌ Invalid or expired link. Please generate a new one from your profile page on the website.'
            );
            return;
        }

        if ($rider->telegram_chat_id && $rider->telegram_chat_id !== $chatId) {
            $this->telegram->sendMessage($chatId,
                '⚠️ This account is already linked to another Telegram account. Unlink it first from the website.'
            );
            return;
        }

        $rider->update([
            'telegram_chat_id'    => $chatId,
            'telegram_link_token' => null,
            'telegram_linked_at'  => now(),
        ]);

        $this->telegram->sendMessage($chatId,
            "🎉 <b>Account linked successfully!</b>\n\n"
            . "Welcome, <b>{$rider->full_name}</b>!\n\n"
            . "/deliveries — View &amp; manage active deliveries\n"
            . "/available — See available broadcasts\n"
            . "/earnings — Check your earnings\n"
            . "/payout — Request a payout\n"
            . "/help — Show this menu"
        );
    }

    protected function sendWelcomeOrStatus(string $chatId): void
    {
        $rider = Rider::where('telegram_chat_id', $chatId)->first();

        if ($rider) {
            $this->telegram->sendMessage($chatId,
                "👋 Welcome back, <b>{$rider->full_name}</b>!\n\n"
                . "/deliveries — Active deliveries\n"
                . "/available — Available broadcasts\n"
                . "/earnings — Earnings summary\n"
                . "/payout — Request payout\n"
                . "/help — Help"
            );
        } else {
            $this->telegram->sendMessage($chatId,
                "👋 Hi! Link your account from <b>Profile → Connect Telegram</b> on the website."
            );
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  ACTIVE DELIVERIES
    //
    //  Bundles are shown as ONE card with ALL pickup stops listed,
    //  matching the show.blade.php experience exactly.
    //  We deduplicate by bundle_id so we never send multiple cards
    //  for the same bundle (one card per Delivery row would be wrong).
    // ═══════════════════════════════════════════════════════════════

    protected function showActiveDeliveries(string $chatId, Rider $rider): void
    {
        $deliveries = Delivery::where('rider_id', $rider->id)
            ->whereIn('status', ['assigned', 'picked_up'])
            ->with(['order', 'seller.shop', 'items', 'bundle.deliveries.seller.shop'])
            ->latest()
            ->get();

        if ($deliveries->isEmpty()) {
            $this->telegram->sendMessage($chatId,
                "📭 No active deliveries right now.\n\nUse /available to see open broadcasts."
            );
            return;
        }

        $sentBundleIds = [];

        foreach ($deliveries as $delivery) {
            if ($delivery->bundle_id) {
                if (in_array($delivery->bundle_id, $sentBundleIds)) {
                    continue; // already sent a card for this bundle
                }
                $sentBundleIds[] = $delivery->bundle_id;
                $this->sendBundleCard($chatId, $delivery->bundle, $delivery);
            } else {
                $this->sendSoloDeliveryCard($chatId, $delivery);
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  AVAILABLE BROADCASTS
    // ═══════════════════════════════════════════════════════════════

    protected function showAvailableDeliveries(string $chatId, Rider $rider): void
    {
        $singles = DeliveryBroadcast::where('status', 'active')
            ->whereNotNull('delivery_id')
            ->whereNull('bundle_id')
            ->whereNull('locked_at')
            ->whereHas('delivery', fn($q) => $q->where('status', 'pending'))
            ->with(['delivery.order', 'delivery.seller.shop'])
            ->latest()
            ->get();

        $bundles = DeliveryBroadcast::where('status', 'active')
            ->whereNotNull('bundle_id')
            ->whereNull('locked_at')
            ->whereHas('bundle', fn($q) => $q->whereIn('status', ['ready', 'growing', 'partial']))
            ->with(['bundle.deliveries.seller.shop', 'bundle.order'])
            ->latest()
            ->get();

        $broadcasts = $singles->merge($bundles)->sortByDesc('created_at')->values();

        if ($broadcasts->isEmpty()) {
            $this->telegram->sendMessage($chatId,
                "📭 No available deliveries right now.\n\nYou'll be notified automatically when new ones arrive."
            );
            return;
        }

        foreach ($broadcasts as $broadcast) {
            $fee = $broadcast->is_bundle
                ? (int) $broadcast->bundle->deliveries->sum('delivery_fee')
                : (int) ($broadcast->delivery->delivery_fee ?? 0);

            $this->telegram->notifyNewBroadcast($chatId, $broadcast, $fee);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  ACCEPT BROADCAST — mirrors DeliveryController::accept()
    // ═══════════════════════════════════════════════════════════════

    protected function acceptBroadcast(
        string $chatId, int $messageId, string $cbId, Rider $rider, int $broadcastId
    ): void {
        $broadcast = DeliveryBroadcast::with(['delivery', 'bundle.deliveries'])->find($broadcastId);

        if (!$broadcast || !$broadcast->isAvailable()) {
            $this->telegram->answerCallbackQuery($cbId, '⚠️ This delivery is no longer available.', true);
            $this->telegram->editMessageText($chatId, $messageId,
                "❌ <b>No longer available</b>\nAnother rider accepted this first."
            );
            return;
        }

        if (!$rider->is_active || !$rider->is_verified) {
            $this->telegram->answerCallbackQuery($cbId, 'Your account is not active.', true);
            return;
        }

        if ($rider->activeDeliveries()->count() >= 10) {
            $this->telegram->answerCallbackQuery($cbId,
                'You have reached the maximum of 10 active deliveries.', true
            );
            return;
        }

        DB::beginTransaction();
        try {
            if ($broadcast->is_bundle) {
                $bundle = $broadcast->bundle;

                if (!in_array($bundle->status, ['ready', 'growing', 'partial'])) {
                    DB::rollBack();
                    $this->telegram->answerCallbackQuery($cbId,
                        'This bundle has already been accepted or is no longer available.', true
                    );
                    return;
                }

                $bundle->deliveries()->where('status', 'pending')->update([
                    'rider_id'    => $rider->id,
                    'status'      => 'assigned',
                    'assigned_at' => now(),
                ]);

                $bundle->update(['status' => 'accepted']);
                $broadcast->markAsAccepted($rider);

                DB::commit();

                $bundle->load(['deliveries.seller.shop', 'deliveries.items', 'order']);
                $firstDelivery = $bundle->deliveries->sortBy('id')->first();
                $firstDelivery->rider->user?->notify(new NewDeliveryAssigned($firstDelivery));

                $stopCount = $bundle->deliveries->where('status', 'assigned')->count();

                $this->telegram->answerCallbackQuery($cbId, '✅ Bundle accepted!');
                $this->telegram->editMessageText($chatId, $messageId,
                    "✅ <b>Bundle Accepted!</b>\n\n"
                    . "You've been assigned <b>{$stopCount} pickup stop(s)</b> in <b>{$bundle->pickup_zone}</b>.\n"
                    . "Collect from all shops before confirming pickup."
                );

                // Send the full bundle card showing all stops
                $this->sendBundleCard($chatId, $bundle, $firstDelivery);

            } else {
                $delivery = $broadcast->delivery;

                if ($delivery->status !== 'pending') {
                    DB::rollBack();
                    $this->telegram->answerCallbackQuery($cbId,
                        'This delivery has already been accepted by another company.', true
                    );
                    return;
                }

                $delivery->update([
                    'rider_id'    => $rider->id,
                    'status'      => 'assigned',
                    'assigned_at' => now(),
                ]);

                $broadcast->markAsAccepted($rider);

                DB::commit();

                $delivery->load(['order', 'seller.shop', 'items']);
                $delivery->rider->user?->notify(new NewDeliveryAssigned($delivery));

                $this->telegram->answerCallbackQuery($cbId, '✅ Delivery accepted!');
                $this->telegram->editMessageText($chatId, $messageId,
                    "✅ <b>Delivery Accepted!</b>\n\nPlease proceed to the pickup location."
                );

                $this->sendSoloDeliveryCard($chatId, $delivery);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Telegram accept broadcast failed', ['error' => $e->getMessage()]);
            $this->telegram->answerCallbackQuery($cbId, '❌ Something went wrong. Please try again.', true);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  REJECT BROADCAST
    // ═══════════════════════════════════════════════════════════════

    protected function rejectBroadcast(
        string $chatId, int $messageId, string $cbId, Rider $rider, int $broadcastId
    ): void {
        $broadcast = DeliveryBroadcast::find($broadcastId);
        if ($broadcast) {
            $broadcast->incrementRejectCount();
        }

        $this->telegram->answerCallbackQuery($cbId, 'Rejected');
        $this->telegram->editMessageText($chatId, $messageId, "❌ <b>Delivery rejected.</b>");
    }

    // ═══════════════════════════════════════════════════════════════
    //  CONFIRM PICKUP — mirrors DeliveryController::confirmPickup()
    //
    //  For bundles: $deliveryId is any delivery in the bundle.
    //  We show all stops before asking for the photo, and one
    //  confirmation covers all shops — same as the web modal.
    // ═══════════════════════════════════════════════════════════════

    protected function promptPickupPhoto(
        string $chatId, string $cbId, Rider $rider, int $deliveryId
    ): void {
        $delivery = Delivery::where('id', $deliveryId)
            ->where('rider_id', $rider->id)
            ->where('status', 'assigned')
            ->with(['order', 'bundle.deliveries.seller.shop'])
            ->first();

        if (!$delivery) {
            $this->telegram->answerCallbackQuery($cbId, 'Delivery not found or already picked up.', true);
            return;
        }

        $this->telegram->answerCallbackQuery($cbId);

        if ($delivery->bundle_id) {
            $bundle   = $delivery->bundle;
            $allStops = $bundle->deliveries->sortBy('id')->values();

            $stopLines = $allStops->map(function ($stop, $i) {
                $shop  = $stop->seller->shop->shop_name ?? 'Shop';
                $phone = $stop->seller->shop->phone_number ?? null;
                $line  = ($i + 1) . ". <b>{$shop}</b>\n    📍 {$stop->pickup_address}";
                if ($phone) $line .= "\n    📞 {$phone}";
                return $line;
            })->implode("\n\n");

            $prompt = "📷 <b>Confirm Bundle Pickup — {$allStops->count()} Shops</b>\n\n"
                . "Zone: <b>{$bundle->pickup_zone}</b>\n\n"
                . "Collect from all stops below before confirming:\n\n"
                . "{$stopLines}\n\n"
                . "Send a photo of all packages, or tap to confirm without one.\n"
                . "Type /cancel to abort.";
        } else {
            $shop   = $delivery->seller->shop->shop_name ?? 'Shop';
            $prompt = "📷 <b>Confirm Pickup — Order {$delivery->order->order_number}</b>\n\n"
                . "🏪 {$shop}\n"
                . "📍 {$delivery->pickup_address}\n\n"
                . "Send a photo of the package, or tap to confirm without one.\n"
                . "Type /cancel to abort.";
        }

        cache()->put("telegram_state:{$chatId}", "awaiting_pickup_photo:{$deliveryId}", now()->addMinutes(10));

        $this->telegram->sendMessage($chatId, $prompt, [
            'inline_keyboard' => [[
                ['text' => '✅ Confirm Without Photo', 'callback_data' => "confirm_pickup_nophoto:{$deliveryId}"],
            ]],
        ]);
    }

    protected function confirmPickupNoPhoto(
        string $chatId, int $messageId, string $cbId, Rider $rider, int $deliveryId
    ): void {
        cache()->forget("telegram_state:{$chatId}");
        $this->telegram->answerCallbackQuery($cbId);
        $this->doConfirmPickup($chatId, $rider, $deliveryId, null);
    }

    /**
     * Shared pickup logic. Marks the delivery AND all assigned siblings
     * as picked_up — identical to DeliveryController::confirmPickup().
     */
    protected function doConfirmPickup(
        string $chatId, Rider $rider, int $deliveryId, ?string $photoPath
    ): void {
        $delivery = Delivery::where('id', $deliveryId)
            ->where('rider_id', $rider->id)
            ->where('status', 'assigned')
            ->with(['order', 'bundle.deliveries.items', 'items'])
            ->first();

        if (!$delivery) {
            $this->telegram->sendMessage($chatId, '❌ Delivery not found or already picked up.');
            return;
        }

        DB::beginTransaction();
        try {
            $delivery->update([
                'status'       => 'picked_up',
                'picked_up_at' => now(),
                'pickup_photo' => $photoPath,
            ]);

            OrderItem::whereIn('id', $delivery->items()->pluck('order_item_id')->toArray())
                ->update(['status' => 'picked_up']);

            $order = $delivery->order;
            if ($order->status === 'processing' && !$order->shipped_at) {
                $order->update(['status' => 'shipped', 'shipped_at' => now()]);
            }

            $bundleNote = '';
            if ($delivery->bundle_id) {
                $siblings = $delivery->bundle->deliveries()
                    ->where('id', '!=', $delivery->id)
                    ->where('status', 'assigned')
                    ->get();

                foreach ($siblings as $sibling) {
                    $sibling->update([
                        'status'       => 'picked_up',
                        'picked_up_at' => now(),
                        'pickup_photo' => $photoPath,
                    ]);
                    OrderItem::whereIn('id', $sibling->items()->pluck('order_item_id')->toArray())
                        ->update(['status' => 'picked_up']);
                }

                $totalStops = $delivery->bundle->deliveries()->count();
                $bundleNote = "\n📦 All {$totalStops} packages in bundle marked as picked up.";
            }

            DB::commit();

            $order->user?->notify(new \App\Notifications\OrderShipped($delivery));

            $this->telegram->sendMessage($chatId,
                "🚴 <b>Pickup confirmed!</b>\n\n"
                . "Order: <code>{$order->order_number}</code>{$bundleNote}\n\n"
                . "📍 Now deliver to: {$delivery->delivery_address}\n"
                . "👤 {$order->customer_name} — {$order->customer_phone}\n\n"
                . "Tap below when delivery is done.",
                [
                    'inline_keyboard' => [[
                        ['text' => '✅ Complete Delivery', 'callback_data' => "confirm_delivered:{$delivery->id}"],
                        ['text' => '❌ Report Failed',     'callback_data' => "fail_delivery:{$delivery->id}"],
                    ]],
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Telegram pickup failed', ['delivery_id' => $deliveryId, 'error' => $e->getMessage()]);
            $this->telegram->sendMessage($chatId, '❌ Failed to confirm pickup: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  COMPLETE DELIVERY — mirrors DeliveryController::complete()
    // ═══════════════════════════════════════════════════════════════

    protected function promptDeliveryPhoto(
        string $chatId, string $cbId, Rider $rider, int $deliveryId
    ): void {
        $delivery = Delivery::where('id', $deliveryId)
            ->where('rider_id', $rider->id)
            ->where('status', 'picked_up')
            ->with(['order', 'bundle.deliveries'])
            ->first();

        if (!$delivery) {
            $this->telegram->answerCallbackQuery($cbId, 'Delivery not found or wrong status.', true);
            return;
        }

        if ($delivery->bundle_id) {
            $unpicked = $delivery->bundle->deliveries()
                ->where('id', '!=', $delivery->id)
                ->where('status', '!=', 'picked_up')
                ->count();

            if ($unpicked > 0) {
                $this->telegram->answerCallbackQuery($cbId,
                    "⚠️ {$unpicked} package(s) not yet picked up. Collect all before completing.", true
                );
                return;
            }
        }

        $this->telegram->answerCallbackQuery($cbId);

        $label = $delivery->bundle_id
            ? 'Bundle — ' . $delivery->bundle->pickup_zone
            : 'Order ' . $delivery->order->order_number;

        cache()->put("telegram_state:{$chatId}", "awaiting_delivery_photo:{$deliveryId}", now()->addMinutes(15));

        $this->telegram->sendMessage($chatId,
            "📷 <b>Proof of Delivery — {$label}</b>\n\n"
            . "Send a photo of the delivered package(s) at the customer's location.\n\n"
            . "<i>Or tap below to confirm without a photo.</i>\n"
            . "Type /cancel to abort.",
            [
                'inline_keyboard' => [[
                    ['text' => '✅ Confirm Without Photo', 'callback_data' => "confirm_delivered_nophoto:{$deliveryId}"],
                ]],
            ]
        );
    }

    protected function confirmDeliveredNoPhoto(
        string $chatId, int $messageId, string $cbId, Rider $rider, int $deliveryId
    ): void {
        cache()->forget("telegram_state:{$chatId}");
        $this->telegram->answerCallbackQuery($cbId);
        $this->doCompleteDelivery($chatId, $rider, $deliveryId, null);
    }

    /**
     * Shared completion logic. Mirrors DeliveryController::complete() exactly —
     * recalculates fees, marks all siblings delivered, syncs order status.
     */
    protected function doCompleteDelivery(
        string $chatId, Rider $rider, int $deliveryId, ?string $photoPath
    ): void {
        $delivery = Delivery::where('id', $deliveryId)
            ->where('rider_id', $rider->id)
            ->where('status', 'picked_up')
            ->with(['order', 'bundle.deliveries.items', 'rider', 'items'])
            ->first();

        if (!$delivery) {
            $this->telegram->sendMessage($chatId, '❌ Delivery not found or wrong status.');
            return;
        }

        if ($delivery->bundle_id) {
            $unpicked = $delivery->bundle->deliveries()
                ->where('id', '!=', $delivery->id)
                ->where('status', '!=', 'picked_up')
                ->count();

            if ($unpicked > 0) {
                $this->telegram->sendMessage($chatId,
                    "⚠️ {$unpicked} package(s) still need to be picked up before completing."
                );
                return;
            }
        }

        DB::beginTransaction();
        try {
            $siblingCount = 0;
            $totalFee     = 0;

            if ($delivery->bundle_id) {
                $bundle = $delivery->bundle;
                $bundle->load('deliveries.order');

                $bundleTotalFee   = $this->computeBundleTotalFee($bundle);
                $sellersInBundle  = $bundle->deliveries()->count();
                $correctPerRowFee = $sellersInBundle > 0
                    ? (int) round($bundleTotalFee / $sellersInBundle)
                    : $bundleTotalFee;

                $siblings = $bundle->deliveries()
                    ->where('id', '!=', $delivery->id)
                    ->get();

                $delivery->update(['delivery_fee' => $correctPerRowFee]);
                $delivery->markAsDelivered($photoPath);

                foreach ($siblings as $sibling) {
                    $sibling->update(['delivery_fee' => $correctPerRowFee]);
                    $sibling->markAsDelivered($photoPath);
                    $siblingCount++;
                }

                $bundle->update(['status' => 'completed']);
                $totalFee = $bundleTotalFee;

            } else {
                $delivery->markAsDelivered($photoPath);
                $totalFee = (int) $delivery->delivery_fee;
            }

            $order = $delivery->order;
            $order->load('items');
            if ($order->items->count() > 0 && $order->items->every(fn($i) => $i->status === 'delivered')) {
                $order->update(['status' => 'delivered', 'delivered_at' => now()]);
            }

            DB::commit();

            $order->user?->notify(new \App\Notifications\OrderDelivered($delivery));

            $packagesNote = $siblingCount > 0
                ? "\n📦 " . (1 + $siblingCount) . " packages delivered."
                : '';

            $this->telegram->sendMessage($chatId,
                "🎉 <b>Delivery Completed!</b>\n\n"
                . "Order: <code>{$order->order_number}</code>{$packagesNote}\n"
                . "💰 Earned: ₦" . number_format($totalFee) . "\n\n"
                . "Use /earnings to see your updated balance."
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Telegram complete delivery failed', ['delivery_id' => $deliveryId, 'error' => $e->getMessage()]);
            $this->telegram->sendMessage($chatId, '❌ Failed to complete delivery: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  FAIL DELIVERY — mirrors DeliveryController::fail()
    // ═══════════════════════════════════════════════════════════════

    protected function promptFailReason(string $chatId, string $cbId, int $deliveryId): void
    {
        $this->telegram->answerCallbackQuery($cbId);
        $this->telegram->sendMessage($chatId,
            "⚠️ <b>Mark Delivery as Failed</b>\n\nSelect a reason:",
            [
                'inline_keyboard' => [
                    [['text' => '🚫 Customer Unavailable', 'callback_data' => "fail_reason:{$deliveryId}:customer_unavailable"]],
                    [['text' => '📍 Wrong Address',         'callback_data' => "fail_reason:{$deliveryId}:wrong_address"]],
                    [['text' => '🙅 Customer Refused',      'callback_data' => "fail_reason:{$deliveryId}:refused"]],
                    [['text' => '🔒 Access Issue',          'callback_data' => "fail_reason:{$deliveryId}:access_issue"]],
                    [['text' => '📦 Package Damaged',       'callback_data' => "fail_reason:{$deliveryId}:damaged"]],
                    [['text' => '❓ Other',                  'callback_data' => "fail_reason:{$deliveryId}:other"]],
                ],
            ]
        );
    }

    protected function processFail(
        string $chatId, int $messageId, string $cbId, Rider $rider,
        int $deliveryId, string $reason
    ): void {
        $delivery = Delivery::where('id', $deliveryId)
            ->where('rider_id', $rider->id)
            ->whereIn('status', ['assigned', 'picked_up'])
            ->with('items')
            ->first();

        if (!$delivery) {
            $this->telegram->answerCallbackQuery($cbId, 'Cannot fail this delivery.', true);
            return;
        }

        DB::beginTransaction();
        try {
            $delivery->update([
                'status'         => 'failed',
                'failed_at'      => now(),
                'failure_reason' => $reason,
                'failure_notes'  => 'Reported via Telegram',
            ]);

            OrderItem::whereIn('id', $delivery->items()->pluck('order_item_id')->toArray())
                ->update(['status' => 'delivery_failed']);

            if ($delivery->rider) {
                $delivery->rider->increment('failed_deliveries');
            }

            DB::commit();

            $this->telegram->answerCallbackQuery($cbId, 'Marked as failed');
            $this->telegram->editMessageText($chatId, $messageId,
                "❌ <b>Delivery marked as failed.</b>\n\n"
                . "Reason: <b>{$reason}</b>\n\n"
                . "The admin team will review this."
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Telegram fail delivery', ['delivery_id' => $deliveryId, 'error' => $e->getMessage()]);
            $this->telegram->answerCallbackQuery($cbId, '❌ Something went wrong.', true);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  PAYOUT
    // ═══════════════════════════════════════════════════════════════

    protected function processPayoutRequest(string $chatId, string $cbId, Rider $rider): void
    {
        $this->telegram->answerCallbackQuery($cbId);
        $this->showPayoutMenu($chatId, $rider);
    }

    protected function showPayoutMenu(string $chatId, Rider $rider): void
    {
        $balance   = $this->payoutService->calculateAvailableBalance($rider);
        $available = $balance['available_balance'] ?? 0;

        if ($available < 1000) {
            $this->telegram->sendMessage($chatId,
                "💳 <b>Payout</b>\n\n"
                . "Available balance: ₦" . number_format($available) . "\n"
                . "Minimum payout is ₦1,000."
            );
            return;
        }

        $this->telegram->sendMessage($chatId,
            "💸 <b>Request Payout</b>\n\n"
            . "Available Balance: <b>₦" . number_format($available) . "</b>\n\n"
            . "Tap <b>Confirm</b> to request your full available balance.\n"
            . "For a custom amount, use the website.",
            [
                'inline_keyboard' => [[
                    ['text' => '✅ Confirm Payout', 'callback_data' => 'payout_confirm'],
                    ['text' => '❌ Cancel',          'callback_data' => 'noop'],
                ]],
            ]
        );
    }

    protected function confirmPayoutRequest(
        string $chatId, int $messageId, string $cbId, Rider $rider
    ): void {
        $balance   = $this->payoutService->calculateAvailableBalance($rider);
        $available = $balance['available_balance'] ?? 0;

        if ($available < 1000) {
            $this->telegram->answerCallbackQuery($cbId, 'Insufficient balance.', true);
            return;
        }

        try {
            $payout = $this->payoutService->createPayoutRequest($rider, $available, null);

            $this->telegram->answerCallbackQuery($cbId, 'Payout requested!');
            $this->telegram->editMessageText($chatId, $messageId,
                "✅ <b>Payout Request Submitted!</b>\n\n"
                . "Reference: <code>{$payout->reference_number}</code>\n"
                . "Amount: ₦" . number_format($payout->amount, 2) . "\n\n"
                . "You'll be notified here when it's approved or paid."
            );
        } catch (\Exception $e) {
            $this->telegram->answerCallbackQuery($cbId, $e->getMessage(), true);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  EARNINGS
    // ═══════════════════════════════════════════════════════════════

    protected function showEarnings(string $chatId, Rider $rider): void
    {
        $base = Delivery::where('rider_id', $rider->id)->where('status', 'delivered');

        $todayAmt = (clone $base)->whereDate('delivered_at', now()->startOfDay())->sum('delivery_fee');
        $weekAmt  = (clone $base)->where('delivered_at', '>=', now()->startOfWeek())->sum('delivery_fee');
        $monthAmt = (clone $base)->where('delivered_at', '>=', now()->startOfMonth())->sum('delivery_fee');
        $allTime  = (clone $base)->sum('delivery_fee');

        $balance = $this->payoutService->calculateAvailableBalance($rider);
        $avail   = $balance['available_balance'] ?? 0;
        $pending = $balance['pending_payout'] ?? 0;

        $markup = $avail >= 1000
            ? ['inline_keyboard' => [[['text' => '💸 Request Payout', 'callback_data' => 'payout_request']]]]
            : [];

        $this->telegram->sendMessage($chatId,
            "💰 <b>Your Earnings</b>\n\n"
            . "📅 Today: ₦" . number_format($todayAmt) . "\n"
            . "📅 This Week: ₦" . number_format($weekAmt) . "\n"
            . "📅 This Month: ₦" . number_format($monthAmt) . "\n"
            . "📈 All Time: ₦" . number_format($allTime) . "\n\n"
            . "💳 <b>Available Balance: ₦" . number_format($avail) . "</b>\n"
            . "⏳ Pending Payout: ₦" . number_format($pending),
            $markup
        );
    }

    // ═══════════════════════════════════════════════════════════════
    //  INCOMING PHOTO HANDLER
    // ═══════════════════════════════════════════════════════════════

    protected function handleIncomingPhoto(string $chatId, array $message, Rider $rider): void
    {
        $state = cache()->get("telegram_state:{$chatId}");

        if (!$state) {
            $this->telegram->sendMessage($chatId,
                "I wasn't expecting a photo. Use /deliveries to see your active jobs."
            );
            return;
        }

        [$action, $deliveryId] = explode(':', $state, 2);
        $deliveryId = (int) $deliveryId;

        $photoPath = $this->downloadTelegramPhoto($message['photo'], $action);

        if (!$photoPath) {
            $this->telegram->sendMessage($chatId,
                "⚠️ Couldn't save the photo. Try again or use the 'Confirm Without Photo' button."
            );
            return;
        }

        cache()->forget("telegram_state:{$chatId}");

        if ($action === 'awaiting_pickup_photo') {
            $this->doConfirmPickup($chatId, $rider, $deliveryId, $photoPath);
        } elseif ($action === 'awaiting_delivery_photo') {
            $this->doCompleteDelivery($chatId, $rider, $deliveryId, $photoPath);
        }
    }

    protected function downloadTelegramPhoto(array $photos, string $action): ?string
    {
        try {
            $token    = config('services.telegram.bot_token');
            $fileId   = collect($photos)->last()['file_id'];
            $fileInfo = Http::get("https://api.telegram.org/bot{$token}/getFile?file_id={$fileId}")->json();
            $filePath = $fileInfo['result']['file_path'] ?? null;

            if (!$filePath) return null;

            $contents  = Http::get("https://api.telegram.org/file/bot{$token}/{$filePath}")->body();
            $directory = str_contains($action, 'pickup') ? 'deliveries/pickups' : 'deliveries/proofs';
            $stored    = $directory . '/tg_' . uniqid() . '.jpg';

            Storage::disk('public')->put($stored, $contents);
            return $stored;

        } catch (\Exception $e) {
            Log::error('Telegram photo download failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  CARD BUILDERS
    //
    //  sendBundleCard() matches show.blade.php exactly:
    //    - Lists EVERY pickup stop with shop name, address, phone
    //    - Shows the total fee for the whole bundle (not per-row)
    //    - One set of action buttons covers the entire bundle
    //
    //  sendSoloDeliveryCard() handles single deliveries.
    // ═══════════════════════════════════════════════════════════════

    /**
     * One card for the whole bundle — all pickup stops, total fee, one action.
     * $anchorDelivery is the first delivery in the bundle; its id is used in
     * callback data since our methods accept any delivery id in the bundle.
     */
    protected function sendBundleCard(string $chatId, DeliveryBundle $bundle, Delivery $anchorDelivery): void
    {
        $bundle->loadMissing(['deliveries.seller.shop', 'order']);

        $allStops  = $bundle->deliveries->sortBy('id')->values();
        $totalFee  = $this->computeBundleTotalFee($bundle);
        $order     = $anchorDelivery->order;
        $status    = $anchorDelivery->status;

        $statusEmoji = match($status) {
            'assigned'  => '📦',
            'picked_up' => '🚴',
            default     => '📋',
        };

        // List every shop the rider needs to visit
        $stopLines = $allStops->map(function ($stop, $i) {
            $shop  = $stop->seller->shop->shop_name ?? 'Shop';
            $phone = $stop->seller->shop->phone_number ?? null;
            $line  = ($i + 1) . ". <b>{$shop}</b>\n    📍 {$stop->pickup_address}";
            if ($phone) $line .= "\n    📞 {$phone}";
            return $line;
        })->implode("\n\n");

        $text = "{$statusEmoji} <b>Bundle Delivery — {$allStops->count()} Pickup Stops</b>\n"
              . "Zone: <b>{$bundle->pickup_zone}</b>\n"
              . "Status: <b>{$anchorDelivery->status_label}</b>\n\n"
              . "<b>Pickup Stops:</b>\n{$stopLines}\n\n"
              . "📍 <b>Deliver to:</b> {$anchorDelivery->delivery_address}\n"
              . "👤 {$order->customer_name} — {$order->customer_phone}\n"
              . "💰 <b>Total Fee: ₦" . number_format($totalFee) . "</b>";

        $buttons = match($status) {
            'assigned'  => [[['text' => '📷 Confirm All Packages Picked Up', 'callback_data' => "pickup:{$anchorDelivery->id}"]]],
            'picked_up' => [[
                ['text' => '✅ Complete Delivery', 'callback_data' => "confirm_delivered:{$anchorDelivery->id}"],
                ['text' => '❌ Report Failed',     'callback_data' => "fail_delivery:{$anchorDelivery->id}"],
            ]],
            default => [],
        };

        $markup = $buttons ? ['inline_keyboard' => $buttons] : [];
        $this->telegram->sendMessage($chatId, $text, $markup);
    }

    /**
     * Simple card for a single (non-bundle) delivery.
     */
    protected function sendSoloDeliveryCard(string $chatId, Delivery $delivery): void
    {
        $delivery->loadMissing(['order', 'seller.shop']);

        $statusEmoji = match($delivery->status) {
            'assigned'  => '📦',
            'picked_up' => '🚴',
            default     => '📋',
        };

        $shop  = $delivery->seller->shop->shop_name ?? 'Shop';
        $phone = $delivery->seller->shop->phone_number ?? null;

        $text = "{$statusEmoji} <b>Order {$delivery->order->order_number}</b>\n"
              . "Status: <b>{$delivery->status_label}</b>\n\n"
              . "🏪 <b>{$shop}</b>\n"
              . "    📍 {$delivery->pickup_address}"
              . ($phone ? "\n    📞 {$phone}" : '') . "\n\n"
              . "📍 <b>Deliver to:</b> {$delivery->delivery_address}\n"
              . "👤 {$delivery->order->customer_name} — {$delivery->order->customer_phone}\n"
              . "💰 Fee: ₦" . number_format($delivery->delivery_fee);

        $buttons = match($delivery->status) {
            'assigned'  => [[['text' => '📷 Confirm Pickup', 'callback_data' => "pickup:{$delivery->id}"]]],
            'picked_up' => [[
                ['text' => '✅ Mark Delivered', 'callback_data' => "confirm_delivered:{$delivery->id}"],
                ['text' => '❌ Report Failed',  'callback_data' => "fail_delivery:{$delivery->id}"],
            ]],
            default => [],
        };

        $markup = $buttons ? ['inline_keyboard' => $buttons] : [];
        $this->telegram->sendMessage($chatId, $text, $markup);
    }

    // ═══════════════════════════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════════════════════════

    protected function cancelPendingState(string $chatId): void
    {
        if (cache()->has("telegram_state:{$chatId}")) {
            cache()->forget("telegram_state:{$chatId}");
            $this->telegram->sendMessage($chatId, "✅ Action cancelled.");
        } else {
            $this->telegram->sendMessage($chatId, "Nothing to cancel.");
        }
    }

    protected function sendHelp(string $chatId): void
    {
        $this->telegram->sendMessage($chatId,
            "🤖 <b>GenesisHub Delivery Bot</b>\n\n"
            . "/deliveries — View &amp; manage active deliveries\n"
            . "/available — See available broadcasts\n"
            . "/earnings — Check your earnings summary\n"
            . "/payout — Request a payout\n"
            . "/cancel — Cancel a pending photo upload\n"
            . "/help — Show this menu\n\n"
            . "<i>Tap buttons in notifications to act directly.</i>"
        );
    }

    /**
     * Compute total bundle fee — mirrors DeliveryController::computeBundleTotalFee().
     */
    protected function computeBundleTotalFee(DeliveryBundle $bundle): int
    {
        $bundle->loadMissing(['order', 'deliveries']);
        $order        = $bundle->order;
        $deliveryZone = $order->shipping_zone ?? null;

        if (!$deliveryZone) {
            return (int) $bundle->deliveries->sum('delivery_fee');
        }

        $rows = DB::table('order_items')
            ->join('sellers', 'order_items.seller_id', '=', 'sellers.id')
            ->join('shops',   'sellers.id',            '=', 'shops.seller_id')
            ->where('order_items.order_id', $order->id)
            ->select('order_items.seller_id', 'shops.delivery_zone')
            ->distinct()
            ->get();

        $distinctZones = $rows->map(fn($r) => $r->delivery_zone ?: 'Not Included')->unique();
        $discount      = $distinctZones->count() >= 2 ? 0.85 : 1.0;

        $pickupZone = $bundle->pickup_zone;
        $rawFee     = \App\Models\DeliveryZone::getPrice($pickupZone, $deliveryZone);

        if ($rawFee === null && $pickupZone !== 'Not Included') {
            $rawFee = \App\Models\DeliveryZone::getPrice('Not Included', $deliveryZone);
        }

        return (int) round((int) ($rawFee ?? 0) * $discount);
    }
}
