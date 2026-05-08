<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // super_admin, administrator, finance_manager, etc.
            $table->string('display_name'); // Super Admin, Administrator, etc.
            $table->text('description')->nullable();
            $table->integer('level')->default(0); // Higher = more power
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};