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
        Schema::create('delivery_broadcast_rider', function (Blueprint $table) {
    $table->id();
    $table->foreignId('delivery_broadcast_id')->constrained()->cascadeOnDelete();
    $table->foreignId('rider_id')->constrained()->cascadeOnDelete();

    $table->enum('response', ['pending', 'accepted', 'rejected', 'ignored'])->default('pending');
    $table->timestamp('viewed_at')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
