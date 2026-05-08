<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Delivery window shown to the buyer — computed at checkout from
            // the slowest item in the cart + a fixed transit buffer.
            $table->unsignedSmallInteger('est_delivery_days_min')
                  ->nullable()
                  ->after('shipping_fee')
                  ->comment('Minimum estimated days from payment to doorstep.');

            $table->unsignedSmallInteger('est_delivery_days_max')
                  ->nullable()
                  ->after('est_delivery_days_min')
                  ->comment('Maximum estimated days from payment to doorstep.');

            // True when at least one item in the order is pre_order or
            // made_to_order — drives the apology message on the cart & order pages.
            $table->boolean('has_preorder_items')
                  ->default(false)
                  ->after('est_delivery_days_max');

            // Name of the product that makes this order take the longest,
            // surfaced in the apology message so it feels personal.
            $table->string('slowest_item_name')
                  ->nullable()
                  ->after('has_preorder_items');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'est_delivery_days_min',
                'est_delivery_days_max',
                'has_preorder_items',
                'slowest_item_name',
            ]);
        });
    }
};