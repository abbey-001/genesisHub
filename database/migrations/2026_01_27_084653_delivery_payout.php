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
        // Create payouts table
        Schema::create('delivery_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->constrained('riders')->onDelete('cascade');
            $table->string('reference_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'approved', 'paid', 'rejected'])->default('pending');
            
            // Request details
            $table->timestamp('requested_at');
            
            // Approval details
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Payment details
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('payment_method')->nullable(); // bank_transfer, cash, cheque, online_transfer
            $table->string('transaction_reference')->nullable();
            
            // Rejection details
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('rejection_reason')->nullable();
            
            // Bank details (snapshot at time of request)
            $table->string('bank_name');
            $table->string('account_number', 20);
            $table->string('account_name');
            
            // Additional info
            $table->text('notes')->nullable();
            $table->integer('deliveries_count')->default(0);
            $table->timestamp('period_from')->nullable();
            $table->timestamp('period_to')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('rider_id');
            $table->index('status');
            $table->index('reference_number');
            $table->index('requested_at');
            $table->index('paid_at');
        });

        // Create payout_deliveries pivot table
        Schema::create('payout_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payout_id')->constrained()->onDelete('cascade');
            $table->foreignId('delivery_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Unique constraint - a delivery can only be in one payout
            $table->unique(['payout_id', 'delivery_id']);
            $table->index('payout_id');
            $table->index('delivery_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payout_deliveries');
        Schema::dropIfExists('payouts');
    }
};