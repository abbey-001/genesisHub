<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Seller;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the seller (User) when a customer successfully pays for
 * items belonging to that seller.
 *
 * Dispatch in PaymentController::creditSellersWallets() after the
 * wallet is credited successfully, inside the foreach loop:
 *
 *   $seller->user?->notify(
 *       new \App\Notifications\NewOrderReceived($order, $seller)
 *   );
 *
 * The $seller variable is already available in that loop as:
 *   $seller = Seller::findOrFail($sellerId);
 */
class NewOrderReceived extends Notification
{
    use Queueable;

    public function __construct(
        protected Order  $order,
        protected Seller $seller,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New order received — #' . $this->order->order_number)
            ->view('emails.seller.new-order', [
                'order'      => $this->order->loadMissing([
                    'items' => fn ($q) => $q->where('seller_id', $this->seller->id)
                                            ->with('product'),
                ]),
                'seller'     => $this->seller->loadMissing('shop'),
                'notifiable' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $sellerItems  = $this->order->items->where('seller_id', $this->seller->id);
        $sellerTotal  = $sellerItems->sum('total_price');
        $commission   = $this->seller->commission_rate ?? 10;
        $net          = $sellerTotal * (1 - $commission / 100);

        return [
            'type'         => 'new_order_received',
            'order_id'     => $this->order->id,
            'order_number' => $this->order->order_number,
            'items_count'  => $sellerItems->count(),
            'gross_amount' => $sellerTotal,
            'net_amount'   => round($net, 2),
            'message'      => 'You have a new order #' . $this->order->order_number,
        ];
    }
}