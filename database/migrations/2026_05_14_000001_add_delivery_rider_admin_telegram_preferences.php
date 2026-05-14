<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->boolean('telegram_notify_deliveries')->default(true)->after('telegram_notify_reviews');
            $table->boolean('telegram_notify_riders')->default(true)->after('telegram_notify_deliveries');
            $table->timestamp('telegram_invited_at')->nullable()->after('telegram_linked_at');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn([
                'telegram_notify_deliveries',
                'telegram_notify_riders',
                'telegram_invited_at',
            ]);
        });
    }
};
