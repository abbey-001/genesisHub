<?php

namespace App\Notifications;

use App\Models\Delivery;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeliveryReassignmentAlert extends Notification
{
    use Queueable;

    protected $delivery;
    protected $reason;

    public function __construct(Delivery $delivery, string $reason)
    {
        $this->delivery = $delivery;
        $this->reason = $reason;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'delivery_reassigned',
            'delivery_id' => $this->delivery->id,
            'order_number' => $this->delivery->order->order_number,
            'reason' => $this->reason,
            'message' => 'Delivery #' . $this->delivery->order->order_number . ' has been reassigned.',
        ];
    }
}