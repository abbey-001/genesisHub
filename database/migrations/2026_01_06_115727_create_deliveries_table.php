// database/migrations/2024_01_xx_create_deliveries_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('rider_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('seller_id')->constrained('sellers')->onDelete('cascade');
            
            $table->string('status')->default('pending'); 
            // pending, assigned, en_route_pickup, picked_up, en_route_delivery, delivered, failed
            
            $table->text('pickup_address');
            $table->decimal('pickup_latitude', 10, 8)->nullable();
            $table->decimal('pickup_longitude', 11, 8)->nullable();
            
            $table->text('delivery_address');
            $table->decimal('delivery_latitude', 10, 8)->nullable();
            $table->decimal('delivery_longitude', 11, 8)->nullable();
            
            $table->decimal('package_weight', 8, 2)->nullable();
            $table->text('package_notes')->nullable();
            $table->decimal('delivery_fee', 10, 2)->default(0);
            
            $table->string('delivery_otp', 6)->nullable();
            
            $table->timestamp('estimated_pickup_time')->nullable();
            $table->timestamp('estimated_delivery_time')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            
            $table->string('failure_reason')->nullable();
            $table->text('failure_notes')->nullable();
            $table->string('pickup_photo')->nullable();
            $table->string('delivery_proof')->nullable();
            $table->string('failure_photo')->nullable();
            $table->text('customer_signature')->nullable();
            
            $table->decimal('current_latitude', 10, 8)->nullable();
            $table->decimal('current_longitude', 11, 8)->nullable();
            $table->timestamp('last_location_update')->nullable();
            
            $table->timestamps();
            
            $table->index(['status', 'rider_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('deliveries');
    }
};