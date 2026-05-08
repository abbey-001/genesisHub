<?php

namespace App\Notifications;

use App\Models\DeliveryPayout;
use App\Models\Rider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayoutRequestCreated extends Notification
{
    use Queueable;

    protected $payout;
    protected $company;

    /**
     * Create a new notification instance.
     */
    public function __construct(DeliveryPayout $payout, Rider $company)
    {
        $this->payout = $payout;
        $this->company = $company;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Payout Request - ' . $this->payout->reference_number)
            ->line('A new payout request has been submitted by ' . $this->company->company_name . '.')
            ->line('Amount: ₦' . number_format($this->payout->amount, 2))
            ->line('Reference: ' . $this->payout->reference_number)
            ->line('Deliveries: ' . $this->payout->deliveries_count)
            ->action('Review Payout Request', url('/admin/payouts/' . $this->payout->id))
            ->line('Please review and approve this payout request.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payout_request_created',
            'payout_id' => $this->payout->id,
            'reference_number' => $this->payout->reference_number,
            'amount' => $this->payout->amount,
            'company_id' => $this->company->id,
            'company_name' => $this->company->company_name,
            'deliveries_count' => $this->payout->deliveries_count,
            'message' => $this->company->company_name . ' requested a payout of ₦' . number_format($this->payout->amount, 2),
        ];
    }
}