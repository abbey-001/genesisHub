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
        // Drop the existing foreign key constraint
        Schema::table('payout_deliveries', function (Blueprint $table) {
            $table->dropForeign(['payout_id']);
        });

        // Add the correct foreign key constraint
        Schema::table('payout_deliveries', function (Blueprint $table) {
            $table->foreign('payout_id')
                ->references('id')
                ->on('delivery_payouts')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the corrected foreign key
        Schema::table('payout_deliveries', function (Blueprint $table) {
            $table->dropForeign(['payout_id']);
        });

        // Restore the old (incorrect) foreign key
        Schema::table('payout_deliveries', function (Blueprint $table) {
            $table->foreign('payout_id')
                ->references('id')
                ->on('payouts')
                ->onDelete('cascade');
        });
    }
};