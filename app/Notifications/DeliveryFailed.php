<?php

namespace App\Notifications;

use App\Models\Delivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliveryFailed extends Notification
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
                ->subject('Delivery Failed - Order #' . $this->delivery->order->order_number)
                ->greeting('Hello ' . $notifiable->name . '!')
                ->line('Unfortunately, the delivery attempt for your order has failed.')
                ->line('')
                ->line('**Order:** #' . $this->delivery->order->order_number)
                ->line('**Reason:** ' . ucfirst(str_replace('_', ' ', $this->delivery->failure_reason)))
                ->line('**Details:** ' . ($this->delivery->failure_notes ?? 'N/A'))
                ->line('')
                ->line('We are working to resolve this issue and will attempt redelivery.')
                ->action('View Order Details', route('seller.orders.show', $this->delivery->order_id))
                ->line('Our support team will contact you if needed.');
        }
        
        // Customer notification
        return (new MailMessage)
            ->subject('Delivery Attempt Failed - Order #' . $this->delivery->order->order_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('We attempted to deliver your order but were unable to complete it.')
            ->line('')
            ->line('**Order:** #' . $this->delivery->order->order_number)
            ->line('**Reason:** ' . ucfirst(str_replace('_', ' ', $this->delivery->failure_reason)))
            ->line('')
            ->line('Don\'t worry! We will attempt redelivery or contact you to arrange an alternative.')
            ->action('Contact Support', route('support'))
            ->line('We apologize for any inconvenience.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'delivery_failed',
            'delivery_id' => $this->delivery->id,
            'order_number' => $this->delivery->order->order_number,
            'failure_reason' => $this->delivery->failure_reason,
            'message' => 'Delivery failed for Order #' . $this->delivery->order->order_number,
        ];
    }
}