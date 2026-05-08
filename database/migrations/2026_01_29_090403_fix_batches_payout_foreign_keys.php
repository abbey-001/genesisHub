<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payout_batches', function (Blueprint $table) {
            // Drop the existing foreign key
            $table->dropForeign(['processed_by']);

            // Change the column type if necessary (should match admins.id)
            $table->unsignedBigInteger('processed_by')->nullable()->change();

            // Add new foreign key pointing to admins table
            $table->foreign('processed_by')
                ->references('id')
                ->on('admins')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('payout_batches', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['processed_by']);

            // Change back to reference users table
            $table->foreign('processed_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
};
