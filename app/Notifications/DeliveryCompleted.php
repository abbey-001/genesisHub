<?php

namespace App\Notifications;

use App\Models\Delivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliveryCompleted extends Notification
{
    use Queueable;

    protected $delivery;

    public function __construct(Delivery $delivery)
    {
        $this->delivery = $delivery;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $isSeller = $notifiable->id === $this->delivery->seller->user_id;
        
        if ($isSeller) {
            return (new MailMessage)
                ->subject('Delivery Completed - Order #' . $this->delivery->order->order_number)
                ->greeting('Hello ' . $notifiable->name . '!')
                ->line('Your order has been successfully delivered to the customer.')
                ->line('')
                ->line('**Order:** #' . $this->delivery->order->order_number)
                ->line('**Delivered at:** ' . $this->delivery->delivered_at->format('M d, Y h:i A'))
                ->line('**Items:** ' . $this->delivery->items->count())
                ->line('')
                ->line('Your earnings for this order have been added to your wallet.')
                ->action('View Wallet', route('seller.payouts.index'))
                ->line('Thank you for being a valued seller!');
        }
        
        // Customer notification
        return (new MailMessage)
            ->subject('Order Delivered Successfully!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your order has been delivered successfully!')
            ->line('')
            ->line('**Order:** #' . $this->delivery->order->order_number)
            ->line('**Delivered at:** ' . $this->delivery->delivered_at->format('M d, Y h:i A'))
            ->line('')
            ->line('We hope you enjoy your purchase!')
            ->action('Rate Your Experience', route('orders.review', $this->delivery->order_id))
            ->line('Thank you for shopping with us!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'delivery_completed',
            'delivery_id' => $this->delivery->id,
            'order_number' => $this->delivery->order->order_number,
            'message' => 'Order #' . $this->delivery->order->order_number . ' delivered successfully!',
        ];
    }
}