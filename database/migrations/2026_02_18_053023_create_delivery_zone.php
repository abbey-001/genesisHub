<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->string('pickup_zone');    // e.g. "Mayfair", "Campus", "Not Included"
            $table->string('delivery_zone');  // e.g. "Parakin", "Sabo", "Opa"
            $table->unsignedInteger('price'); // in Naira
            $table->timestamps();

            // Fast lookup index
            $table->index(['pickup_zone', 'delivery_zone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_zones');
    }
};