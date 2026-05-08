<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('user_type', 20)->default('customer'); // customer | seller | rider
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device', 100)->nullable();   // parsed: "Chrome on Windows"
            $table->string('location', 150)->nullable(); // "Lagos, NG" — from IP geo (optional)
            $table->boolean('successful')->default(true);
            $table->string('failure_reason', 100)->nullable(); // "invalid_password", "account_deactivated"
            $table->timestamp('logged_in_at');
            $table->timestamps();

            $table->index(['user_id', 'user_type']);
            $table->index('logged_in_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_activities');
    }
};