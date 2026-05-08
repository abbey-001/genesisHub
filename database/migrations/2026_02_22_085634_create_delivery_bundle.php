<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_bundles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // The shared pickup zone for all sellers in this bundle
            $table->string('pickup_zone');

            // Statuses:
            //   waiting   — not all sellers in zone are ready yet, holding broadcast
            //   ready     — all sellers ready, broadcast fired
            //   partial   — timeout elapsed, broadcast fired for whoever was ready
            //   accepted  — a company has accepted the bundle broadcast
            //   completed — all deliveries in the bundle are delivered
            $table->enum('status', ['waiting', 'ready', 'partial', 'accepted', 'completed'])
                  ->default('waiting');

            // How many seller deliveries are expected in this bundle
            $table->unsignedTinyInteger('expected_count');

            // How many are actually ready right now
            $table->unsignedTinyInteger('ready_count')->default(0);

            // When the FIRST seller in this bundle marked ready (starts the timeout clock)
            $table->timestamp('first_ready_at')->nullable();

            // When the broadcast was actually fired
            $table->timestamp('broadcast_at')->nullable();

            // Timeout deadline — set when first seller marks ready
            // Default: 2 hours after first_ready_at
            $table->timestamp('timeout_at')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'pickup_zone']);
            $table->index(['status', 'timeout_at']); // for the scheduled timeout job
        });

        // Add bundle_id to deliveries so each Delivery knows its bundle
        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreignId('bundle_id')
                  ->nullable()
                  ->after('seller_id')
                  ->constrained('delivery_bundles')
                  ->nullOnDelete();
        });

        // Add bundle_id to delivery_broadcasts so a broadcast can cover a whole bundle
        Schema::table('delivery_broadcasts', function (Blueprint $table) {
            $table->foreignId('bundle_id')
                  ->nullable()
                  ->after('delivery_id')
                  ->constrained('delivery_bundles')
                  ->nullOnDelete();

            // Flag so we can tell if this was a partial (timeout) broadcast
            $table->boolean('is_partial')->default(false)->after('bundle_id');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_broadcasts', function (Blueprint $table) {
            $table->dropForeign(['bundle_id']);
            $table->dropColumn(['bundle_id', 'is_partial']);
        });

        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropForeign(['bundle_id']);
            $table->dropColumn('bundle_id');
        });

        Schema::dropIfExists('delivery_bundles');
    }
};