<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Computed at order creation: paid_at + product's max_ready_days
            // (or paid_at + IN_STOCK_MAX_DAYS for in_stock products).
            // Used for admin escalation alerts and seller deadline display.
            $table->date('expected_ready_by')
                  ->nullable()
                  ->after('ready_at')
                  ->comment('Date by which seller must mark this item ready.');

            // Snapshot the fulfillment type at purchase time so historical
            // orders still show the correct type even if the product changes.
            $table->enum('fulfillment_type', ['in_stock', 'pre_order', 'made_to_order'])
                  ->default('in_stock')
                  ->after('expected_ready_by');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['expected_ready_by', 'fulfillment_type']);
        });
    }
};