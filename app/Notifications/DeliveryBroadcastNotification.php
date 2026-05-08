<?php

// ============================================
// app/Notifications/DeliveryBroadcastNotification.php
// ============================================
namespace App\Notifications;

use App\Models\Delivery;
use App\Models\DeliveryBroadcast;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class DeliveryBroadcastNotification extends Notification
{
    use Queueable;

    protected $delivery;
    protected $broadcast;

    public function __construct(Delivery $delivery, DeliveryBroadcast $broadcast)
    {
        $this->delivery = $delivery;
        $this->broadcast = $broadcast;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database', 'mail']; // Add 'sms' if configured
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🚨 New Delivery Broadcast - First to Accept Wins!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new delivery has been broadcasted to riders in your area.')
            ->line('**This is a first-come-first-served delivery!**')
            ->line('')
            ->line('**Order:** #' . $this->delivery->order->order_number)
            ->line('**Delivery Fee:** ₦' . number_format($this->delivery->delivery_fee, 0))
            ->line('**Items:** ' . $this->delivery->items->count())
            ->line('**Pickup:** ' . $this->delivery->pickup_address)
            ->line('')
            ->action('View & Accept Delivery', route('rider.broadcasts.show', $this->broadcast))
            ->line('This broadcast expires at **' . $this->broadcast->expires_at->format('h:i A') . '**.')
            ->line('Be quick - the first rider to accept gets the delivery!')
            ->salutation('Good luck!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'delivery_broadcast',
            'delivery_id' => $this->delivery->id,
            'broadcast_id' => $this->broadcast->id,
            'order_number' => $this->delivery->order->order_number,
            'delivery_fee' => $this->delivery->delivery_fee,
            'items_count' => $this->delivery->items->count(),
            'pickup_address' => $this->delivery->pickup_address,
            'delivery_address' => $this->delivery->delivery_address,
            'expires_at' => $this->broadcast->expires_at,
            'message' => 'New broadcast delivery available! ₦' . number_format($this->delivery->delivery_fee, 0),
            'url' => route('rider.broadcasts.show', $this->broadcast),
        ];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return $this->toArray($notifiable);
    }
}