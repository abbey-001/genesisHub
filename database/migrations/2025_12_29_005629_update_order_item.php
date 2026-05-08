<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // If table exists, modify it
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                // Make seller_id nullable if it isn't already
                if (Schema::hasColumn('order_items', 'seller_id')) {
                    $table->foreignId('seller_id')->nullable()->change();
                }
                
                // Add indexes if they don't exist
                if (!Schema::hasColumn('order_items', 'seller_id')) {
                    $table->foreignId('seller_id')->nullable()->constrained('sellers')->onDelete('set null');
                }
            });
        } else {
            // Create table if it doesn't exist
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->onDelete('cascade');
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->foreignId('seller_id')->nullable()->constrained('sellers')->onDelete('set null');
                
                $table->string('product_name');
                $table->string('product_sku')->nullable();
                $table->integer('quantity');
                $table->decimal('price', 10, 2);
                $table->decimal('total', 10, 2);
                $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'])
                      ->default('pending');
                
                $table->timestamps();
                
                // Indexes
                $table->index('order_id');
                $table->index('product_id');
                $table->index('seller_id');
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop the table, just remove the changes if needed
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                // Only drop foreign key if it exists
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexesFound = $sm->listTableIndexes('order_items');
                
                if (array_key_exists('order_items_seller_id_foreign', $indexesFound)) {
                    $table->dropForeign(['seller_id']);
                }
            });
        }
    }
};