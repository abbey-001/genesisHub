<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'phone_number',
        'vehicle_type',
        'status',
        'is_verified',
        'is_active',
        'bank_name',
        'account_number',
        'account_name',
        'completed_deliveries',
        'failed_deliveries',
        'telegram_linked_at',
        'telegram_chat_id',
        'telegram_link_token',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'completed_deliveries' => 'integer',
        'failed_deliveries' => 'integer',
         'telegram_linked_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function activeDeliveries()
    {
        return $this->hasMany(Delivery::class)
            ->whereIn('status', ['assigned', 'picked_up']);
    }

    public function completedDeliveries()
    {
        return $this->hasMany(Delivery::class)
            ->where('status', 'delivered');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('is_verified', true);
    }

    // Methods
    public function canAcceptDelivery()
    {
        return $this->is_active && $this->is_verified;
    }

    public function getSuccessRateAttribute()
    {
        $total = $this->completed_deliveries + $this->failed_deliveries;
        
        if ($total === 0) {
            return 100;
        }
        
        return round(($this->completed_deliveries / $total) * 100, 1);
    }

    public function incrementCompletedDeliveries()
    {
        $this->increment('completed_deliveries');
    }

    public function incrementFailedDeliveries()
    {
        $this->increment('failed_deliveries');
    }
}