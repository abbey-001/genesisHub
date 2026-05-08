<?php

namespace App\Notifications;

use App\Models\Delivery;
use App\Models\DeliveryBundle;
use App\Models\DeliveryBroadcast;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Broadcast to all active riders when a new delivery (or bundle) is
 * available for acceptance.
 *
 * Dispatch point: DeliveryService::broadcastToCompanies() and
 * DeliveryService::broadcastBundle() — no code change needed there,
 * this class is a drop-in replacement for the existing one.
 */
class NewDeliveryAvailable extends Notification
{
    use Queueable;

    public ?Delivery       $delivery;
    public DeliveryBroadcast $broadcast;
    public ?DeliveryBundle $bundle;

    public function __construct(
        ?Delivery $delivery,
        DeliveryBroadcast $broadcast,
        ?DeliveryBundle $bundle = null
    ) {
        $this->delivery  = $delivery;
        $this->broadcast = $broadcast;
        $this->bundle    = $bundle;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->bundle
                ? 'New bundle delivery available — ' . ($this->bundle->pickup_zone ?? 'Multiple zones')
                : 'New delivery available — #' . ($this->delivery?->order->order_number ?? 'N/A')
            )
            ->view('emails.rider.delivery-available', [
                'delivery'   => $this->delivery?->loadMissing([
                    'order', 'seller.shop', 'items.product',
                ]),
                'bundle'     => $this->bundle?->loadMissing([
                    'order', 'deliveries.seller.shop', 'deliveries.items',
                ]),
                'broadcast'  => $this->broadcast,
                'notifiable' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        if ($this->bundle) {
            $deliveries  = $this->bundle->deliveries;
            $totalFee    = $deliveries->sum('delivery_fee');
            $totalItems  = $deliveries->sum(fn ($d) =>
                $d->relationLoaded('items') ? $d->items->count() : 0
            );

            return [
                'type'             => 'bundle_available',
                'title'            => 'New Bundle Delivery Available!',
                'message'          => 'Order #' . ($this->bundle->order->order_number ?? 'N/A')
                                    . ' — ' . $deliveries->count() . ' seller(s) in ' . $this->bundle->pickup_zone,
                'bundle_id'        => $this->bundle->id,
                'broadcast_id'     => $this->broadcast->id,
                'delivery_fee'     => $totalFee,
                'items_count'      => $totalItems,
                'pickup_zone'      => $this->bundle->pickup_zone,
                'delivery_address' => $this->bundle->order->shipping_address ?? null,
                'action_url'       => route('rider.broadcasts.show', $this->broadcast),
                'icon'             => 'bx-package',
            ];
        }

        return [
            'type'             => 'delivery_available',
            'title'            => 'New Delivery Available!',
            'message'          => 'Order #' . ($this->delivery?->order->order_number ?? 'N/A') . ' is ready for pickup',
            'delivery_id'      => $this->delivery?->id,
            'broadcast_id'     => $this->broadcast->id,
            'delivery_fee'     => $this->delivery?->delivery_fee,
            'items_count'      => $this->delivery?->relationLoaded('items') ? $this->delivery->items->count() : 0,
            'pickup_address'   => $this->delivery?->pickup_address,
            'delivery_address' => $this->delivery?->delivery_address,
            'action_url'       => route('rider.broadcasts.show', $this->broadcast),
            'icon'             => 'bx-package',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        if ($this->bundle) {
            $totalFee = $this->bundle->deliveries->sum('delivery_fee');

            return new BroadcastMessage([
                'title'        => 'New Bundle Delivery Available!',
                'message'      => 'Zone: ' . $this->bundle->pickup_zone . ' — ₦' . number_format($totalFee),
                'bundle_id'    => $this->bundle->id,
                'broadcast_id' => $this->broadcast->id,
            ]);
        }

        return new BroadcastMessage([
            'title'        => 'New Delivery Available!',
            'message'      => 'Order #' . ($this->delivery?->order->order_number ?? 'N/A')
                            . ' — ₦' . number_format($this->delivery?->delivery_fee),
            'delivery_id'  => $this->delivery?->id,
            'broadcast_id' => $this->broadcast->id,
        ]);
    }
}