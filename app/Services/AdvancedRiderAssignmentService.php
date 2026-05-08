<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Rider;
use App\Models\RiderAssignmentQueue;
use App\Models\DeliveryBroadcast;
use App\Notifications\DeliveryBroadcastNotification;
use App\Notifications\NewDeliveryAssigned;
use App\Notifications\DeliveryReassignmentAlert;
use App\Events\DeliveryNeedsRider;
use App\Events\RiderAssignmentFailed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use App\Services\DeliveryService;



class AdvancedRiderAssignmentService
{
    protected $deliveryService;
    
    // Assignment strategies
    const STRATEGY_NEAREST = 'nearest';
    const STRATEGY_HIGHEST_RATED = 'highest_rated';
    const STRATEGY_MOST_EXPERIENCED = 'most_experienced';
    const STRATEGY_LEAST_BUSY = 'least_busy';
    const STRATEGY_BROADCAST = 'broadcast';
    
    // Assignment priorities
    const PRIORITY_URGENT = 1;      // VIP orders, time-sensitive
    const PRIORITY_HIGH = 2;        // Standard premium
    const PRIORITY_NORMAL = 3;      // Regular orders
    const PRIORITY_LOW = 4;         // Bulk/scheduled
    
    public function __construct(DeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
    }
    
    /**
     * Intelligent rider assignment with multiple strategies
     */
    public function assignRider(Delivery $delivery, array $options = [])
    {
        $strategy = $options['strategy'] ?? $this->determineStrategy($delivery);
        $maxAttempts = $options['max_attempts'] ?? 3;
        $attempt = 0;
        
        Log::info("Starting rider assignment", [
            'delivery_id' => $delivery->id,
            'strategy' => $strategy,
        ]);
        
        while ($attempt < $maxAttempts) {
            $attempt++;
            
            try {
                DB::beginTransaction();
                
                // Find best rider using chosen strategy
                $rider = $this->findRiderByStrategy($delivery, $strategy);
                
                if ($rider) {
                    // Assign the rider
                    $success = $this->performAssignment($delivery, $rider);
                    
                    if ($success) {
                        DB::commit();
                        
                        Log::info("Rider assigned successfully", [
                            'delivery_id' => $delivery->id,
                            'rider_id' => $rider->id,
                            'strategy' => $strategy,
                            'attempt' => $attempt,
                        ]);
                        
                        return [
                            'success' => true,
                            'rider' => $rider,
                            'strategy' => $strategy,
                            'attempt' => $attempt,
                        ];
                    }
                }
                
                DB::rollBack();
                
                // Try different strategy on next attempt
                $strategy = $this->getNextStrategy($strategy);
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Assignment attempt failed", [
                    'delivery_id' => $delivery->id,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        // All direct assignment attempts failed - use fallback
        return $this->handleAssignmentFailure($delivery);
    }
    
    /**
     * Determine best assignment strategy based on delivery context
     */
    protected function determineStrategy(Delivery $delivery)
    {
        $order = $delivery->order;
        $timeOfDay = now()->hour;
        
        // Urgent/VIP orders - prioritize nearest rider
        if ($delivery->priority === self::PRIORITY_URGENT || $order->is_vip) {
            return self::STRATEGY_NEAREST;
        }
        
        // High-value orders - prioritize experienced riders
        if ($order->total > 50000) {
            return self::STRATEGY_HIGHEST_RATED;
        }
        
        // Peak hours (12pm-2pm, 6pm-9pm) - use least busy
        if (($timeOfDay >= 12 && $timeOfDay <= 14) || ($timeOfDay >= 18 && $timeOfDay <= 21)) {
            return self::STRATEGY_LEAST_BUSY;
        }
        
        // Default to nearest
        return self::STRATEGY_NEAREST;
    }
    
    /**
     * Find rider using specified strategy
     */
    protected function findRiderByStrategy(Delivery $delivery, string $strategy)
    {
        $baseQuery = $this->getAvailableRidersQuery($delivery);
        
        switch ($strategy) {
            case self::STRATEGY_NEAREST:
                return $this->findNearestRider($baseQuery, $delivery);
                
            case self::STRATEGY_HIGHEST_RATED:
                return $this->findHighestRatedRider($baseQuery);
                
            case self::STRATEGY_MOST_EXPERIENCED:
                return $this->findMostExperiencedRider($baseQuery);
                
            case self::STRATEGY_LEAST_BUSY:
                return $this->findLeastBusyRider($baseQuery);
                
            default:
                return $this->findNearestRider($baseQuery, $delivery);
        }
    }
    
    /**
     * Get base query for available riders
     */
    protected function getAvailableRidersQuery(Delivery $delivery)
    {
        return Rider::where('is_verified', true)
            ->where('is_active', true)
            ->where('status', 'available')
            ->whereDoesntHave('activeDeliveries', function($q) {
                $q->where('rider_deliveries.created_at', '>', now()->subHours(4));
            })
            ->orWhereHas('activeDeliveries', function($q) {
                $q->havingRaw('COUNT(*) < 3'); // Max 3 concurrent deliveries
            });
    }
    
    /**
     * Find nearest rider
     */
    protected function findNearestRider($query, Delivery $delivery)
    {
        if ($delivery->pickup_latitude && $delivery->pickup_longitude) {
            return $query->selectRaw("*, 
                (6371 * acos(cos(radians(?)) 
                * cos(radians(current_latitude)) 
                * cos(radians(current_longitude) - radians(?)) 
                + sin(radians(?)) 
                * sin(radians(current_latitude)))) AS distance", 
                [$delivery->pickup_latitude, $delivery->pickup_longitude, $delivery->pickup_latitude])
                ->whereNotNull('current_latitude')
                ->whereNotNull('current_longitude')
                ->having('distance', '<', 30) // Within 30km
                ->orderBy('distance')
                ->first();
        }
        
        return $query->inRandomOrder()->first();
    }
    
    /**
     * Find highest rated rider
     */
    protected function findHighestRatedRider($query)
    {
        return $query->where('rating', '>=', 4.0)
            ->orderByDesc('rating')
            ->orderByDesc('completed_deliveries')
            ->first();
    }
    
    /**
     * Find most experienced rider
     */
    protected function findMostExperiencedRider($query)
    {
        return $query->where('completed_deliveries', '>=', 10)
            ->orderByDesc('completed_deliveries')
            ->orderByDesc('rating')
            ->first();
    }
    
    /**
     * Find least busy rider
     */
    protected function findLeastBusyRider($query)
    {
        return $query->withCount(['activeDeliveries'])
            ->orderBy('active_deliveries_count')
            ->orderByDesc('rating')
            ->first();
    }
    
    /**
     * Get next strategy to try
     */
    protected function getNextStrategy(string $currentStrategy)
    {
        $strategies = [
            self::STRATEGY_NEAREST,
            self::STRATEGY_LEAST_BUSY,
            self::STRATEGY_HIGHEST_RATED,
            self::STRATEGY_MOST_EXPERIENCED,
        ];
        
        $currentIndex = array_search($currentStrategy, $strategies);
        $nextIndex = ($currentIndex + 1) % count($strategies);
        
        return $strategies[$nextIndex];
    }
    
    /**
     * Perform the actual assignment
     */
    protected function performAssignment(Delivery $delivery, Rider $rider)
    {
        // Double-check rider is still available
        $rider->refresh();
        
        if ($rider->status !== 'available') {
            Log::warning("Rider no longer available during assignment", [
                'rider_id' => $rider->id,
                'status' => $rider->status,
            ]);
            return false;
        }
        
        // Check concurrent delivery limit
        if ($rider->activeDeliveries()->count() >= 3) {
            Log::warning("Rider has too many active deliveries", [
                'rider_id' => $rider->id,
                'active_count' => $rider->activeDeliveries()->count(),
            ]);
            return false;
        }
        
        // Assign delivery
        $delivery->update([
            'rider_id' => $rider->id,
            'status' => 'assigned',
            'assigned_at' => now(),
            'estimated_pickup_time' => $this->calculatePickupETA($delivery, $rider),
            'assignment_method' => 'auto',
        ]);
        
        // Update rider
        $rider->update(['status' => 'busy']);
        
        // Notify rider
        $rider->user->notify(new NewDeliveryAssigned($delivery));
        
        return true;
    }
    
    /**
     * Handle assignment failure with fallback strategies
     */
    protected function handleAssignmentFailure(Delivery $delivery)
    {
        Log::warning("All assignment attempts failed, using fallback", [
            'delivery_id' => $delivery->id,
        ]);
        
        // Strategy 1: Broadcast to all riders within range
        $broadcast = $this->broadcastDeliveryToRiders($delivery);
        
        if ($broadcast) {
            return [
                'success' => false,
                'fallback' => 'broadcast',
                'message' => 'Delivery broadcasted to available riders',
                'broadcast_id' => $broadcast->id,
            ];
        }
        
        // Strategy 2: Add to assignment queue
        $queued = $this->addToAssignmentQueue($delivery);
        
        if ($queued) {
            return [
                'success' => false,
                'fallback' => 'queued',
                'message' => 'Delivery added to assignment queue',
            ];
        }
        
        // Strategy 3: Notify admin for manual assignment
        $this->notifyAdminForManualAssignment($delivery);
        
        return [
            'success' => false,
            'fallback' => 'admin_notification',
            'message' => 'Admin notified for manual assignment',
        ];
    }
    
    /**
     * Broadcast delivery to multiple riders (first-come-first-served)
     */
    protected function broadcastDeliveryToRiders(Delivery $delivery)
    {
        try {
            DB::beginTransaction();
            
            // Get riders within range
            $riders = $this->getRidersInRange($delivery, 50); // 50km radius
            
            if ($riders->isEmpty()) {
                // Expand search to offline riders who were recently active
                $riders = $this->getRecentlyActiveRiders($delivery);
            }
            
            if ($riders->isEmpty()) {
                DB::rollBack();
                return null;
            }
            
            // Create broadcast record
            $broadcast = DeliveryBroadcast::create([
                'delivery_id' => $delivery->id,
                'status' => 'active',
                'expires_at' => now()->addDays(30),
                'max_responders' => 5,
                'broadcast_to_count' => $riders->count(),
            ]);
            
            // Attach riders to broadcast
            $broadcast->riders()->attach($riders->pluck('id'));
            
            // Update delivery status
            $delivery->update([
                'status' => 'broadcast',
                'broadcast_id' => $broadcast->id,
            ]);
            
            DB::commit();
            
            // Send notifications
            // Notification::send($riders->pluck('user'), new DeliveryBroadcastNotification($delivery, $broadcast));
            
            // // Emit event for real-time updates
            // event(new DeliveryNeedsRider($delivery, $riders));
            
            Log::info("Delivery broadcasted to riders", [
                'delivery_id' => $delivery->id,
                'broadcast_id' => $broadcast->id,
                'rider_count' => $riders->count(),
            ]);
            
            return $broadcast;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to broadcast delivery", [
                'delivery_id' => $delivery->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
    
    /**
     * Get riders within specified range
     */
    protected function getRidersInRange(Delivery $delivery, int $radiusKm)
    {
        if (!$delivery->pickup_latitude || !$delivery->pickup_longitude) {
            // No coordinates, get any available riders
            return Rider::where('is_active', true)
                ->where('status', 'available')
                ->limit(20)
                ->get();
        }
        
        return Rider::where('is_active', true)
            ->where('status', 'available')
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->selectRaw("*, 
                (6371 * acos(cos(radians(?)) 
                * cos(radians(current_latitude)) 
                * cos(radians(current_longitude) - radians(?)) 
                + sin(radians(?)) 
                * sin(radians(current_latitude)))) AS distance", 
                [$delivery->pickup_latitude, $delivery->pickup_longitude, $delivery->pickup_latitude])
            ->having('distance', '<', $radiusKm)
            ->orderBy('distance')
            ->limit(20)
            ->get();
    }
    
    /**
     * Get recently active riders (offline but may come back online)
     */
    protected function getRecentlyActiveRiders(Delivery $delivery)
    {
        return Rider::where('is_active', true)
            ->where('last_active_at', '>', now()->subHours(2))
            ->whereIn('status', ['offline', 'break'])
            ->limit(10)
            ->get();
    }
    
    /**
     * Add delivery to assignment queue for auto-retry
     */
    protected function addToAssignmentQueue(Delivery $delivery)
    {
        try {
            RiderAssignmentQueue::create([
                'delivery_id' => $delivery->id,
                'priority' => $delivery->priority ?? self::PRIORITY_NORMAL,
                'attempts' => 0,
                'max_attempts' => 10,
                'next_attempt_at' => now()->addMinutes(5),
                'status' => 'pending',
            ]);
            
            $delivery->update(['status' => 'pending_assignment']);
            
            Log::info("Delivery added to assignment queue", [
                'delivery_id' => $delivery->id,
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to add to assignment queue", [
                'delivery_id' => $delivery->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    /**
     * Process assignment queue (run via scheduler)
     */
    public function processAssignmentQueue()
    {
        $queuedItems = RiderAssignmentQueue::where('status', 'pending')
            ->where('next_attempt_at', '<=', now())
            ->where('attempts', '<', DB::raw('max_attempts'))
            ->orderBy('priority')
            ->orderBy('created_at')
            ->limit(50)
            ->get();
        
        foreach ($queuedItems as $item) {
            try {
                $item->increment('attempts');
                $item->update(['next_attempt_at' => now()->addMinutes(5 * $item->attempts)]);
                
                $delivery = $item->delivery;
                
                if (!$delivery || $delivery->status !== 'pending_assignment') {
                    $item->update(['status' => 'cancelled']);
                    continue;
                }
                
                $result = $this->assignRider($delivery);
                
                if ($result['success']) {
                    $item->update(['status' => 'completed']);
                    Log::info("Queue item successfully assigned", [
                        'queue_id' => $item->id,
                        'delivery_id' => $delivery->id,
                    ]);
                } else {
                    Log::info("Queue item assignment still pending", [
                        'queue_id' => $item->id,
                        'attempts' => $item->attempts,
                    ]);
                }
                
            } catch (\Exception $e) {
                Log::error("Failed to process queue item", [
                    'queue_id' => $item->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        // Mark expired items as failed
        RiderAssignmentQueue::where('status', 'pending')
            ->where('attempts', '>=', DB::raw('max_attempts'))
            ->update(['status' => 'failed']);
    }
    
    /**
     * Rider accepts broadcasted delivery
     */
    public function acceptBroadcastedDelivery(Delivery $delivery, Rider $rider)
    {
        DB::beginTransaction();
        
        try {
            // Check if delivery is still available
            if ($delivery->status !== 'broadcast') {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'This delivery is no longer available',
                ];
            }
            
            // Check rider eligibility
            if ($rider->status !== 'available') {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'You must be available to accept deliveries',
                ];
            }
            
            // Assign to rider
            $success = $this->performAssignment($delivery, $rider);
            
            if (!$success) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Failed to assign delivery. Please try again.',
                ];
            }
            
            // Update broadcast
            $broadcast = $delivery->broadcast;
            if ($broadcast) {
                $broadcast->update([
                    'status' => 'accepted',
                    'accepted_by_rider_id' => $rider->id,
                    'accepted_at' => now(),
                ]);
            }
            
            DB::commit();
            
            Log::info("Broadcast delivery accepted", [
                'delivery_id' => $delivery->id,
                'broadcast_id' => $broadcast->id ?? null,
                'rider_id' => $rider->id,
            ]);
            
            return [
                'success' => true,
                'message' => 'Delivery accepted successfully!',
                'delivery' => $delivery,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to accept broadcast", [
                'delivery_id' => $delivery->id,
                'rider_id' => $rider->id,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred. Please try again.',
            ];
        }
    }
    
    /**
     * Notify admin for manual assignment
     */
    protected function notifyAdminForManualAssignment(Delivery $delivery)
    {
        event(new RiderAssignmentFailed($delivery));
        
        // You can also send email/SMS to admin
        Log::critical("Manual assignment needed", [
            'delivery_id' => $delivery->id,
            'order_id' => $delivery->order_id,
        ]);
    }
    
    /**
     * Calculate pickup ETA based on rider location
     */
    protected function calculatePickupETA(Delivery $delivery, Rider $rider)
    {
        $baseMinutes = 30;
        
        if ($delivery->pickup_latitude && $rider->current_latitude) {
            $distance = $this->calculateDistance(
                $rider->current_latitude,
                $rider->current_longitude,
                $delivery->pickup_latitude,
                $delivery->pickup_longitude
            );
            
            // Assume 30km/h average speed
            $travelMinutes = ($distance / 30) * 60;
            $baseMinutes = max(15, min(90, $travelMinutes)); // Between 15-90 mins
        }
        
        return now()->addMinutes($baseMinutes);
    }
    
    /**
     * Calculate distance between two points
     */
    protected function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
    
    /**
     * Reassign delivery if rider cancels or fails
     */
    public function reassignDelivery(Delivery $delivery, string $reason)
    {
        DB::beginTransaction();
        
        try {
            $oldRider = $delivery->rider;
            
            // Reset delivery
            $delivery->update([
                'rider_id' => null,
                'status' => 'pending',
                'assigned_at' => null,
            ]);
            
            // Update old rider
            if ($oldRider) {
                if ($oldRider->activeDeliveries()->count() === 0) {
                    $oldRider->update(['status' => 'available']);
                }
            }
            
            DB::commit();
            
            // Try to assign again
            $result = $this->assignRider($delivery, ['max_attempts' => 5]);
            
            Log::info("Delivery reassigned", [
                'delivery_id' => $delivery->id,
                'old_rider_id' => $oldRider?->id,
                'reason' => $reason,
                'reassignment_success' => $result['success'],
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}