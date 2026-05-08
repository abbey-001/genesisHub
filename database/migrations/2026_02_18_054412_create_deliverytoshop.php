<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // Stores the seller's pickup zone name, e.g. "Campus", "Mayfair"
            // NULL means the zone wasn't set — delivery fee will use "Not Included" fallback
            $table->string('delivery_zone')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('delivery_zone');
        });
    }
};