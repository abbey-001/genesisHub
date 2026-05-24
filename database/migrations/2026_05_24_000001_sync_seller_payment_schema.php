<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payouts')) {
            Schema::create('payouts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seller_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 10, 2);
                $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
                $table->string('payout_method')->nullable();
                $table->string('transaction_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('requested_at')->useCurrent();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->index(['seller_id', 'status']);
                $table->index('requested_at');
            });
        }

        Schema::table('sellers', function (Blueprint $table) {
            if (!Schema::hasColumn('sellers', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('country');
            }

            if (!Schema::hasColumn('sellers', 'commission_rate')) {
                $table->decimal('commission_rate', 5, 2)
                    ->default(10)
                    ->after('verification_status');
            }
        });

        DB::table('sellers')->update([
            'is_verified' => DB::raw("verification_status = 'verified'"),
        ]);

        DB::statement(
            "ALTER TABLE sellers MODIFY verification_status " .
            "ENUM('pending','verified','rejected','suspended') NOT NULL DEFAULT 'pending'"
        );

        if (Schema::hasTable('seller_wallet_transactions')) {
            DB::statement('ALTER TABLE seller_wallet_transactions MODIFY transactable_type VARCHAR(255) NULL');
            DB::statement('ALTER TABLE seller_wallet_transactions MODIFY transactable_id BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sellers', 'commission_rate')) {
            Schema::table('sellers', function (Blueprint $table) {
                $table->dropColumn('commission_rate');
            });
        }

        if (Schema::hasColumn('sellers', 'is_verified')) {
            Schema::table('sellers', function (Blueprint $table) {
                $table->dropColumn('is_verified');
            });
        }
    }
};
