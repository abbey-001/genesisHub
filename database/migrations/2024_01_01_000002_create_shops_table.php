<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('seller_id')
                  ->constrained()
                  ->cascadeOnDelete();
        
            $table->string('name');
            $table->string('slug')->unique();
        
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
        
            $table->string('email');
            $table->string('phone_number');
        
            $table->boolean('is_active')->default(true);
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('followers_count')->default(0);
        
            $table->timestamps();
        
            $table->index(['seller_id', 'is_active']);
        });
        
        
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
