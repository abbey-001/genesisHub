<?php

/**
 * ============================================
 * PHASE 3: Database Migration Guide
 * ============================================
 * 
 * This file contains all database changes needed for Phase 3
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Phase 3
     */
    public function up()
    {
        // 1. Create simplified delivery_broadcasts table
        if (!Schema::hasTable('delivery_broadcasts')) {
            Schema::create('delivery_broadcasts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('delivery_id')->constrained()->onDelete('cascade');
                $table->enum('status', ['active', 'accepted', 'expired'])->default('active');
                $table->timestamp('expires_at');
                $table->foreignId('accepted_by_rider_id')->nullable()->constrained('riders')->onDelete('set null');
                $table->timestamp('accepted_at')->nullable();
                $table->timestamps();
                
                $table->index(['status', 'expires_at']);
                $table->index('delivery_id');
            });
        }
        
        // 2. Ensure riders table has bank fields (from Phase 1)
        if (!Schema::hasColumn('riders', 'bank_name')) {
            Schema::table('riders', function (Blueprint $table) {
                $table->string('bank_name')->nullable()->after('vehicle_type');
                $table->string('account_number', 20)->nullable()->after('bank_name');
                $table->string('account_name')->nullable()->after('account_number');
            });
        }
        
        // 3. Ensure deliveries table is simplified (from Phase 1)
        $columnsToCheck = [
            'delivery_otp',
            'estimated_pickup_time',
            'estimated_delivery_time',
            'customer_signature',
            'current_latitude',
            'current_longitude',
            'last_location_update'
        ];
        
        Schema::table('deliveries', function (Blueprint $table) use ($columnsToCheck) {
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('deliveries', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
    
    /**
     * Reverse the migrations
     */
    public function down()
    {
        Schema::dropIfExists('delivery_broadcasts');
        
        Schema::table('riders', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'account_number', 'account_name']);
        });
    }
};