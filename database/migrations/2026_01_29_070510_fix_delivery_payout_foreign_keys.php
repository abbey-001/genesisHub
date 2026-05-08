<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_payouts', function (Blueprint $table) {
            // Drop old foreign keys (pointing to users)
            $table->dropForeign(['approved_by_user_id']);
            $table->dropForeign(['paid_by_user_id']);
            $table->dropForeign(['rejected_by_user_id']);

            // Add new foreign keys pointing to admins
            $table->foreign('approved_by_user_id')
                  ->references('id')
                  ->on('admins')
                  ->onDelete('set null');

            $table->foreign('paid_by_user_id')
                  ->references('id')
                  ->on('admins')
                  ->onDelete('set null');

            $table->foreign('rejected_by_user_id')
                  ->references('id')
                  ->on('admins')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_payouts', function (Blueprint $table) {
            // Drop the admin foreign keys
            $table->dropForeign(['approved_by_user_id']);
            $table->dropForeign(['paid_by_user_id']);
            $table->dropForeign(['rejected_by_user_id']);

            // Optional: add back foreign keys to users if needed
            $table->foreign('approved_by_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->foreign('paid_by_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->foreign('rejected_by_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }
};
