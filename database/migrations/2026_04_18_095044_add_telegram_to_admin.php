<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds Telegram linking fields to the admins table.
 *
 * Unlike sellers, admins are registered manually by a super-admin
 * directly in the database or via a seeder — there is no self-service
 * linking flow. The telegram_chat_id is therefore set directly, and
 * telegram_link_token is kept for a future optional secure-link flow.
 *
 * telegram_notify_* columns let a super-admin configure per-admin
 * notification subscriptions without changing code. Each column maps
 * to one notification category. Default = true so a freshly registered
 * admin gets everything; they can be toggled off as needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            // Core linking fields
            $table->string('telegram_chat_id')->nullable()->unique()->after('avatar');
            $table->string('telegram_link_token', 64)->nullable()->unique()->after('telegram_chat_id');
            $table->timestamp('telegram_linked_at')->nullable()->after('telegram_link_token');

            // Notification category toggles
            $table->boolean('telegram_notify_orders')->default(true)->after('telegram_linked_at');
            $table->boolean('telegram_notify_payouts')->default(true)->after('telegram_notify_orders');
            $table->boolean('telegram_notify_sellers')->default(true)->after('telegram_notify_payouts');
            $table->boolean('telegram_notify_reviews')->default(true)->after('telegram_notify_sellers');
            $table->boolean('telegram_notify_system')->default(true)->after('telegram_notify_reviews');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn([
                'telegram_chat_id',
                'telegram_link_token',
                'telegram_linked_at',
                'telegram_notify_orders',
                'telegram_notify_payouts',
                'telegram_notify_sellers',
                'telegram_notify_reviews',
                'telegram_notify_system',
            ]);
        });
    }
};