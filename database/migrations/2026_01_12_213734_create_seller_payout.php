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
        // Main wallet table - stores current balance for each seller
        Schema::create('seller_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->unique()->constrained()->onDelete('cascade');
            $table->decimal('balance', 15, 2)->default(0); // Current available balance
            $table->decimal('pending_balance', 15, 2)->default(0); // Earnings not yet available
            $table->decimal('total_earned', 15, 2)->default(0); // Lifetime earnings
            $table->decimal('total_withdrawn', 15, 2)->default(0); // Lifetime withdrawals
            $table->decimal('reserved_balance', 15, 2)->default(0); // Reserved for refunds/disputes
            $table->timestamp('last_transaction_at')->nullable();
            $table->timestamps();
            
            $table->index('seller_id');
        });

        // Transaction ledger - complete audit trail of all balance changes
        Schema::create('seller_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->onDelete('cascade');
            $table->foreignId('wallet_id')->constrained('seller_wallets')->onDelete('cascade');
            
            // Transaction details
            $table->string('type'); // credit, debit, reserve, release, payout
            $table->string('source'); // order, refund, payout, adjustment, commission
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            
            // Reference to source
            $table->string('transactable_type');
            $table->unsignedBigInteger('transactable_id');

            $table->index(
                ['transactable_type', 'transactable_id'],
                'wallet_transactable_idx'
            );

            $table->string('transaction_id')->unique()->nullable(); // External transaction ID
            
            // Metadata
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Store additional info (commission rate, fees, etc.)
            $table->string('status')->default('completed'); // completed, pending, failed, reversed
            
            $table->timestamps();
            
            $table->index(['seller_id', 'created_at']);
            $table->index(['wallet_id', 'type']);
            $table->index('transactable_type');
            $table->index('status');
        });

        // Payout schedules and settings per seller
        Schema::create('seller_payout_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->unique()->constrained()->onDelete('cascade');
            
            // Payout preferences
            $table->decimal('minimum_payout', 10, 2)->default(10.00);
            $table->string('preferred_method')->default('bank_transfer');
            $table->enum('payout_schedule', ['manual', 'weekly', 'biweekly', 'monthly'])->default('manual');
            $table->integer('payout_day')->nullable(); // Day of week/month
            
            // Auto-payout settings
            $table->boolean('auto_payout_enabled')->default(false);
            $table->decimal('auto_payout_threshold', 10, 2)->nullable();
            
            // Hold period settings (days to hold funds before available)
            $table->integer('hold_period_days')->default(7);
            
            $table->timestamps();
        });

        // Update payouts table to add more tracking
        Schema::table('payouts', function (Blueprint $table) {
            $table->foreignId('wallet_transaction_id')->nullable()->after('seller_id')
                ->constrained('seller_wallet_transactions')->nullOnDelete();
            $table->decimal('fee_amount', 10, 2)->default(0)->after('amount');
            $table->decimal('net_amount', 10, 2)->after('fee_amount');
            $table->string('failure_reason')->nullable()->after('status');
            $table->timestamp('failed_at')->nullable()->after('processed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropForeign(['wallet_transaction_id']);
            $table->dropColumn(['wallet_transaction_id', 'fee_amount', 'net_amount', 'failure_reason', 'failed_at']);
        });
        
        Schema::dropIfExists('seller_payout_settings');
        Schema::dropIfExists('seller_wallet_transactions');
        Schema::dropIfExists('seller_wallets');
    }
};