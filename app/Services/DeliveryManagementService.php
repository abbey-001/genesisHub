<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Rider;
use App\Models\DeliveryBroadcast;
use App\Events\DeliveryNeedsRider;
use App\Events\DeliveryStatusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryManagementService
{
    /**
     * Get delivery statistics
     */
    public function getDeliveryStatistics($dateFrom = null, $dateTo = null): array
    {
        $query = Delivery::query();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return [
            'total' => $query->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'active' => (clone $query)->whereIn('status', [
                'assigned', 'en_route_pickup', 'picked_up', 'en_route_delivery'
            ])->count(),
            'completed' => (clone $query)->where('status', 'delivered')->count(),
            'failed' => (clone $query)->where('status', 'failed')->count(),
            'average_delivery_time' => $this->calculateAverageDeliveryTime($dateFrom, $dateTo),
            'success_rate' => $this->calculateSuccessRate($dateFrom, $dateTo),
        ];
    }

    /**
     * Calculate average delivery time
     */
    protected function calculateAverageDeliveryTime($dateFrom = null, $dateTo = null): ?int
    {
        $query = Delivery::where('status', 'delivered')
            ->whereNotNull('assigned_at')
            ->whereNotNull('delivered_at');

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $deliveries = $query->get();

        if ($deliveries->isEmpty()) {
            return null;
        }

        $totalMinutes = $deliveries->sum(function($delivery) {
            return $delivery->assigned_at->diffInMinutes($delivery->delivered_at);
        });

        return (int) ($totalMinutes / $deliveries->count());
    }

    /**
     * Calculate success rate
     */
    protected function calculateSuccessRate($dateFrom = null, $dateTo = null): float
    {
        $query = Delivery::query();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $total = $query->count();

        if ($total === 0) {
            return 100.0;
        }

        $successful = (clone $query)->where('status', 'delivered')->count();

        return round(($successful / $total) * 100, 2);
    }

    /**
     * Get rider performance metrics
     */
    public function getRiderPerformance(Rider $rider, $dateFrom = null, $dateTo = null): array
    {
        $query = $rider->deliveries();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $deliveries = $query->get();

        return [
            'total_deliveries' => $deliveries->count(),
            'completed' => $deliveries->where('status', 'delivered')->count(),
            'failed' => $deliveries->where('status', 'failed')->count(),
            'success_rate' => $deliveries->count() > 0 
                ? round(($deliveries->where('status', 'delivered')->count() / $deliveries->count()) * 100, 2)
                : 100.0,
            'average_delivery_time' => $this->calculateRiderAverageTime($deliveries),
            'total_earnings' => $deliveries->where('status', 'delivered')->sum('delivery_fee'),
        ];
    }

    /**
     * Calculate rider average delivery time
     */
    protected function calculateRiderAverageTime($deliveries): ?int
    {
        $completed = $deliveries->where('status', 'delivered')
            ->filter(function($d) {
                return $d->assigned_at && $d->delivered_at;
            });

        if ($completed->isEmpty()) {
            return null;
        }

        $totalMinutes = $completed->sum(function($delivery) {
            return $delivery->assigned_at->diffInMinutes($delivery->delivered_at);
        });

        return (int) ($totalMinutes / $completed->count());
    }

    /**
     * Get unassigned deliveries with priority
     */
    public function getUnassignedQueue(): array
    {
        $deliveries = Delivery::where('status', 'pending')
            ->whereNull('rider_id')
            ->with(['order', 'seller.shop', 'items'])
            ->get();

        // Sort by priority (age, value, distance, etc.)
        return $deliveries->sortByDesc(function($delivery) {
            $priority = 0;

            // Age priority (older = higher priority)
            $ageHours = $delivery->created_at->diffInHours(now());
            $priority += min($ageHours * 10, 100);

            // Value priority
            if ($delivery->delivery_fee > 1000) {
                $priority += 50;
            }

            // Item count priority
            $priority += $delivery->items->count() * 5;

            return $priority;
        })->values()->all();
    }

    /**
     * Monitor delivery health
     */
    public function monitorDeliveryHealth(): array
    {
        $issues = [];

        // Check for long-pending deliveries
        $longPending = Delivery::where('status', 'pending')
            ->where('created_at', '<', now()->subHours(2))
            ->count();

        if ($longPending > 0) {
            $issues[] = [
                'type' => 'long_pending',
                'severity' => 'high',
                'count' => $longPending,
                'message' => "{$longPending} deliveries pending for over 2 hours"
            ];
        }

        // Check for stuck deliveries
        $stuckInTransit = Delivery::whereIn('status', ['en_route_pickup', 'en_route_delivery'])
            ->where('updated_at', '<', now()->subHours(3))
            ->count();

        if ($stuckInTransit > 0) {
            $issues[] = [
                'type' => 'stuck_in_transit',
                'severity' => 'medium',
                'count' => $stuckInTransit,
                'message' => "{$stuckInTransit} deliveries stuck in transit"
            ];
        }

        // Check failed delivery rate
        $failedToday = Delivery::where('status', 'failed')
            ->whereDate('failed_at', today())
            ->count();

        $totalToday = Delivery::whereDate('created_at', today())->count();

        if ($totalToday > 0 && ($failedToday / $totalToday) > 0.1) {
            $issues[] = [
                'type' => 'high_failure_rate',
                'severity' => 'high',
                'count' => $failedToday,
                'message' => "High failure rate today: " . round(($failedToday / $totalToday) * 100, 1) . "%"
            ];
        }

        return [
            'healthy' => empty($issues),
            'issues' => $issues,
            'timestamp' => now()
        ];
    }

    /**
     * Get delivery zones performance
     */
    public function getZonePerformance(): array
    {
        // This would analyze deliveries by geographic zones
        // For now, returning a basic structure
        return [
            'zones' => [],
            'busiest_zone' => null,
            'slowest_zone' => null,
        ];
    }

    /**
     * Estimate delivery time
     */
    public function estimateDeliveryTime(Delivery $delivery): array
    {
        $baseTime = 30; // Base 30 minutes

        // Add time based on distance (if coordinates available)
        if ($delivery->pickup_latitude && $delivery->delivery_latitude) {
            $distance = $this->calculateDistance(
                $delivery->pickup_latitude,
                $delivery->pickup_longitude,
                $delivery->delivery_latitude,
                $delivery->delivery_longitude
            );

            // Assume 20 km/h average speed
            $travelTime = ($distance / 20) * 60;
            $baseTime += $travelTime;
        }

        // Add time for multiple items
        $baseTime += ($delivery->items->count() - 1) * 5;

        return [
            'estimated_minutes' => (int) $baseTime,
            'estimated_pickup' => now()->addMinutes(15),
            'estimated_delivery' => now()->addMinutes($baseTime),
        ];
    }

    /**
     * Calculate distance between two points (Haversine formula)
     */
    protected function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get live delivery tracking data
     */
    public function getLiveTrackingData(): array
    {
        $activeDeliveries = Delivery::with(['rider', 'order'])
            ->whereIn('status', [
                'assigned', 'en_route_pickup', 'picked_up', 'en_route_delivery'
            ])
            ->get();

        return $activeDeliveries->map(function($delivery) {
            return [
                'id' => $delivery->id,
                'order_number' => $delivery->order->order_number,
                'status' => $delivery->status,
                'rider' => $delivery->rider ? [
                    'id' => $delivery->rider->id,
                    'name' => $delivery->rider->full_name,
                    'latitude' => $delivery->rider->current_latitude,
                    'longitude' => $delivery->rider->current_longitude,
                    'last_update' => $delivery->rider->last_location_update,
                ] : null,
                'pickup' => [
                    'latitude' => $delivery->pickup_latitude,
                    'longitude' => $delivery->pickup_longitude,
                    'address' => $delivery->pickup_address,
                ],
                'delivery' => [
                    'latitude' => $delivery->delivery_latitude,
                    'longitude' => $delivery->delivery_longitude,
                    'address' => $delivery->delivery_address,
                ],
            ];
        })->toArray();
    }
}