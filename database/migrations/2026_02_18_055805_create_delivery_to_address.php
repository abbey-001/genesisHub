<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            // Stores the buyer's delivery zone name, e.g. "Campus", "Sabo"
            // NULL means zone wasn't set — D6 will treat this as unresolvable and flag it
            $table->string('delivery_zone')->nullable()->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('delivery_zone');
        });
    }
};