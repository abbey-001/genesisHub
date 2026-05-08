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
        // Check if columns exist before adding them
        Schema::table('payouts', function (Blueprint $table) {
            // Add wallet_transaction_id if it doesn't exist
            if (!Schema::hasColumn('payouts', 'wallet_transaction_id')) {
                $table->unsignedBigInteger('wallet_transaction_id')->nullable()->after('seller_id');
                $table->foreign('wallet_transaction_id')
                    ->references('id')
                    ->on('seller_wallet_transactions')
                    ->onDelete('set null');
            }

            // Add fee_amount if it doesn't exist
            if (!Schema::hasColumn('payouts', 'fee_amount')) {
                $table->decimal('fee_amount', 10, 2)->default(0)->after('amount');
            }

            // Add net_amount if it doesn't exist
            if (!Schema::hasColumn('payouts', 'net_amount')) {
                $table->decimal('net_amount', 10, 2)->default(0)->after('fee_amount');
            }

            // Add failure_reason if it doesn't exist
            if (!Schema::hasColumn('payouts', 'failure_reason')) {
                $table->text('failure_reason')->nullable()->after('notes');
            }

            // Add failed_at if it doesn't exist
            if (!Schema::hasColumn('payouts', 'failed_at')) {
                $table->timestamp('failed_at')->nullable()->after('processed_at');
            }
        });

        // Ensure seller_wallet_transactions has status column
        Schema::table('seller_wallet_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('seller_wallet_transactions', 'status')) {
                $table->string('status', 20)->default('completed')->after('metadata');
                $table->index('status');
            }
        });

        // Ensure seller_wallets has last_transaction_at column
        Schema::table('seller_wallets', function (Blueprint $table) {
            if (!Schema::hasColumn('seller_wallets', 'last_transaction_at')) {
                $table->timestamp('last_transaction_at')->nullable()->after('reserved_balance');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            if (Schema::hasColumn('payouts', 'wallet_transaction_id')) {
                $table->dropForeign(['wallet_transaction_id']);
                $table->dropColumn('wallet_transaction_id');
            }
            if (Schema::hasColumn('payouts', 'fee_amount')) {
                $table->dropColumn('fee_amount');
            }
            if (Schema::hasColumn('payouts', 'net_amount')) {
                $table->dropColumn('net_amount');
            }
            if (Schema::hasColumn('payouts', 'failure_reason')) {
                $table->dropColumn('failure_reason');
            }
            if (Schema::hasColumn('payouts', 'failed_at')) {
                $table->dropColumn('failed_at');
            }
        });

        Schema::table('seller_wallet_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('seller_wallet_transactions', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('seller_wallets', function (Blueprint $table) {
            if (Schema::hasColumn('seller_wallets', 'last_transaction_at')) {
                $table->dropColumn('last_transaction_at');
            }
        });
    }
};