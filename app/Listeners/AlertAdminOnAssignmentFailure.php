<?php

namespace App\Listeners;

use App\Events\RiderAssignmentFailed;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;

class AlertAdminOnAssignmentFailure
{
    public function handle(RiderAssignmentFailed $event)
    {
        // Get all admin users
        $admins = User::where('role', 'admin')->get();
        
        // Send notification
        foreach ($admins as $admin) {
            $admin->notify(new class($event->delivery) extends BaseNotification {
                protected $delivery;
                
                public function __construct($delivery)
                {
                    $this->delivery = $delivery;
                }
                
                public function via($notifiable)
                {
                    return ['mail', 'database'];
                }
                
                public function toMail($notifiable)
                {
                    return (new MailMessage)
                        ->subject('URGENT: Manual Rider Assignment Needed')
                        ->greeting('Hello Admin!')
                        ->line('A delivery requires manual rider assignment.')
                        ->line('')
                        ->line('**Order:** #' . $this->delivery->order->order_number)
                        ->line('**Delivery ID:** ' . $this->delivery->id)
                        ->line('**Customer:** ' . $this->delivery->order->customer_name)
                        ->line('**Address:** ' . $this->delivery->delivery_address)
                        ->line('')
                        ->line('All automatic assignment attempts have been exhausted.')
                        ->action('Assign Rider Manually', route('admin.deliveries.unassigned'))
                        ->line('Please assign a rider as soon as possible.');
                }
                
                public function toArray($notifiable)
                {
                    return [
                        'type' => 'assignment_failed',
                        'delivery_id' => $this->delivery->id,
                        'order_number' => $this->delivery->order->order_number,
                        'message' => 'Manual assignment needed for Order #' . $this->delivery->order->order_number,
                        'url' => route('admin.deliveries.unassigned'),
                    ];
                }
            });
        }
    }
}