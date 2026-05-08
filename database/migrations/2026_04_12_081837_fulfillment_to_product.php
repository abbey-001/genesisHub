<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Type of sale — drives delivery estimate logic
            $table->enum('fulfillment_type', ['in_stock', 'pre_order', 'made_to_order'])
                  ->default('in_stock')
                  ->after('condition');

            // Only set for pre_order and made_to_order products.
            // How many days after payment the seller promises to have the item ready.
            // null means in_stock (platform enforces IN_STOCK_MAX_DAYS constant instead).
            $table->unsignedSmallInteger('max_ready_days')
                  ->nullable()
                  ->after('fulfillment_type')
                  ->comment('Days after order for seller to mark ready. Null = in_stock.');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['fulfillment_type', 'max_ready_days']);
        });
    }
};