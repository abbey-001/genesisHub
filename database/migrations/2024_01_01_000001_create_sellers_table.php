<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();
        
            $table->enum('seller_type', ['individual', 'company']);
            $table->string('business_name')->nullable();
            $table->string('tax_id')->nullable()->unique();
        
            $table->string('phone_number');
            $table->text('address');
        
            $table->enum('status', ['pending', 'active', 'suspended', 'banned'])
                  ->default('pending');
        
            $table->timestamp('verified_at')->nullable();
        
            $table->timestamps();
        
            $table->unique('user_id');
        });
        
        
    }

    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
