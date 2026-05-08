<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    protected $fillable = [
        'pickup_zone',
        'delivery_zone',
        'price',
    ];

    protected $casts = [
        'price' => 'integer',
    ];

    /**
     * Look up the delivery price for a pickup/delivery zone pair.
     * Falls back to "Not Included" pickup pricing if the pickup zone
     * is not in the known list.
     */
    public static function getPrice(string $pickupZone, string $deliveryZone): ?int
    {
        // Try exact pickup zone match first
        $row = static::where('pickup_zone', $pickupZone)
            ->where('delivery_zone', $deliveryZone)
            ->first();

        if ($row) {
            return $row->price;
        }

        // Fall back to "Not Included" pickup zone
        $fallback = static::where('pickup_zone', 'Not Included')
            ->where('delivery_zone', $deliveryZone)
            ->first();

        return $fallback?->price;
    }

    /**
     * Get all distinct pickup zones (excludes "Not Included" fallback).
     * Used to populate seller zone dropdowns.
     */
    public static function pickupZones(): array
    {
        return static::where('pickup_zone', '!=', 'Not Included')
            ->distinct()
            ->orderBy('pickup_zone')
            ->pluck('pickup_zone')
            ->toArray();
    }

    /**
     * Get all distinct delivery zones.
     * Used to populate buyer address zone dropdowns.
     */
    public static function deliveryZones(): array
    {
        return static::where('pickup_zone', 'Not Included')
            ->orderBy('delivery_zone')
            ->pluck('delivery_zone')
            ->toArray();
    }
}