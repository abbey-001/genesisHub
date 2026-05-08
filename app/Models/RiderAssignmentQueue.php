<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiderAssignmentQueue extends Model
{
    use HasFactory;

    protected $table = 'rider_assignment_queue';

    protected $fillable = [
        'delivery_id',
        'priority',
        'status',
        'attempts',
        'max_attempts',
        'next_attempt_at',
        'last_error',
        'attempted_strategies',
        'metadata',
    ];

    protected $casts = [
        'next_attempt_at' => 'datetime',
        'attempted_strategies' => 'array',
        'metadata' => 'array',
    ];

    // Relationships
    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReadyForProcessing($query)
    {
        return $query->where('status', 'pending')
            ->where('next_attempt_at', '<=', now())
            ->where('attempts', '<', \DB::raw('max_attempts'));
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority')
            ->orderBy('created_at');
    }

    // Methods
    public function canRetry()
    {
        return $this->attempts < $this->max_attempts;
    }

    public function recordAttempt(string $error = null)
    {
        $this->increment('attempts');
        
        $this->update([
            'next_attempt_at' => now()->addMinutes(5 * $this->attempts),
            'last_error' => $error,
        ]);
    }

    public function markCompleted()
    {
        $this->update(['status' => 'completed']);
    }

    public function markFailed()
    {
        $this->update(['status' => 'failed']);
    }
}