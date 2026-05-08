<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds Telegram linking fields to the sellers table.
 *
 * Fields mirror the rider pattern exactly (telegram_chat_id,
 * telegram_link_token, telegram_linked_at) so the linking flow
 * is identical across user types.
 *
 * telegram_notifications_enabled lets each seller opt-out without
 * unlinking their account — useful for sellers who want to keep
 * the account linked but temporarily disable messages.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            // Core linking fields
            $table->string('telegram_chat_id')->nullable()->unique()->after('country');
            $table->string('telegram_link_token', 64)->nullable()->unique()->after('telegram_chat_id');
            $table->timestamp('telegram_linked_at')->nullable()->after('telegram_link_token');

            // Per-seller opt-out toggle
            $table->boolean('telegram_notifications_enabled')->default(true)->after('telegram_linked_at');
        });
    }

    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn([
                'telegram_chat_id',
                'telegram_link_token',
                'telegram_linked_at',
                'telegram_notifications_enabled',
            ]);
        });
    }
};