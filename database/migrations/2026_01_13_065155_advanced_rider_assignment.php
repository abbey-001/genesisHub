<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================
// Migration 1: Update Riders Table
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->timestamp('last_location_update')->nullable()->after('current_longitude');
            
            // Activity tracking
            $table->timestamp('last_active_at')->nullable()->after('status');
            $table->string('last_activity')->nullable()->after('last_active_at');
            
            // Performance metrics
            $table->integer('failed_deliveries')->default(0)->after('completed_deliveries');
            $table->integer('cancelled_deliveries')->default(0)->after('failed_deliveries');
            $table->decimal('acceptance_rate', 5, 2)->default(100)->after('cancelled_deliveries');
            
            // Availability zones
            $table->json('service_areas')->nullable()->after('acceptance_rate');
            $table->decimal('max_delivery_distance', 8, 2)->default(30)->after('service_areas');
            
            // Add indexes
            $table->index(['status', 'is_active']);
            $table->index(['current_latitude', 'current_longitude']);
            $table->index('last_active_at');
        });
    }

    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropColumn([
                'current_latitude',
                'current_longitude',
                'last_location_update',
                'last_active_at',
                'last_activity',
                'completed_deliveries',
                'failed_deliveries',
                'cancelled_deliveries',
                'acceptance_rate',
                'service_areas',
                'max_delivery_distance',
            ]);
        });
    }
};


