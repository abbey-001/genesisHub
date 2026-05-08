
<?php

// database/migrations/xxxx_xx_xx_add_payout_fields_to_deliveries.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->timestamp('paid_to_rider_at')->nullable()->after('delivered_at');
            $table->foreignId('payout_batch_id')->nullable()->after('paid_to_rider_at')->constrained('payout_batches')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropForeign(['payout_batch_id']);
            $table->dropColumn(['paid_to_rider_at', 'payout_batch_id']);
        });
    }
};