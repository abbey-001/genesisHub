<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payout_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->decimal('total_amount', 12, 2);
            $table->integer('total_riders');
            $table->integer('total_deliveries');
            $table->enum('status', ['pending', 'processing', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index('batch_number');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payout_batches');
    }
};


    