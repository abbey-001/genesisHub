<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiderAssignmentHistory extends Model
{
    use HasFactory;

    protected $table = 'rider_assignment_history';

    protected $fillable = [
        'delivery_id',
        'rider_id',
        'action',
        'method',
        'strategy',
        'reason',
        'metadata',
        'performed_by_user_id',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // Relationships
    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }

    // Scopes
    public function scopeForDelivery($query, $deliveryId)
    {
        return $query->where('delivery_id', $deliveryId)
            ->orderBy('created_at');
    }

    public function scopeForRider($query, $riderId)
    {
        return $query->where('rider_id', $riderId)
            ->orderByDesc('created_at');
    }

    // Static methods for logging
    public static function logAssignment(Delivery $delivery, Rider $rider, string $method, string $strategy = null)
    {
        return static::create([
            'delivery_id' => $delivery->id,
            'rider_id' => $rider->id,
            'action' => 'assigned',
            'method' => $method,
            'strategy' => $strategy,
            'performed_by_user_id' => auth()->id(),
        ]);
    }

    public static function logUnassignment(Delivery $delivery, Rider $rider, string $reason)
    {
        return static::create([
            'delivery_id' => $delivery->id,
            'rider_id' => $rider->id,
            'action' => 'unassigned',
            'reason' => $reason,
            'performed_by_user_id' => auth()->id(),
        ]);
    }

    public static function logRejection(Delivery $delivery, Rider $rider, string $reason)
    {
        return static::create([
            'delivery_id' => $delivery->id,
            'rider_id' => $rider->id,
            'action' => 'rejected',
            'reason' => $reason,
        ]);
    }
}