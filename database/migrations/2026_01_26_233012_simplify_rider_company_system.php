<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        /*
        |--------------------------------------------------------------------------
        | STEP 1: Drop unnecessary tables (disable FK checks)
        |--------------------------------------------------------------------------
        */
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::dropIfExists('broadcast_rider');
        Schema::dropIfExists('delivery_broadcasts');
        Schema::dropIfExists('rider_assignment_history');
        Schema::dropIfExists('rider_assignment_queue');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        /*
        |--------------------------------------------------------------------------
        | STEP 2: Simplify riders table
        |--------------------------------------------------------------------------
        */
        Schema::table('riders', function (Blueprint $table) {

            $columnsToDrop = [
                'current_latitude',
                'current_longitude',
                'last_location_update',
                'last_active_at',
                'last_activity',
                'rating',
                'acceptance_rate',
                'profile_photo',
                'vehicle_registration',
                'license_number',
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('riders', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (!Schema::hasColumn('riders', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('vehicle_type');
            }

            if (!Schema::hasColumn('riders', 'account_number')) {
                $table->string('account_number', 20)->nullable()->after('bank_name');
            }

            if (!Schema::hasColumn('riders', 'account_name')) {
                $table->string('account_name')->nullable()->after('account_number');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | STEP 3: Simplify deliveries table
        |--------------------------------------------------------------------------
        */
        Schema::table('deliveries', function (Blueprint $table) {

            $columnsToDrop = [
                'current_latitude',
                'current_longitude',
                'last_location_update',
                'delivery_otp',
                'estimated_pickup_time',
                'estimated_delivery_time',
                'customer_signature',
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('deliveries', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        /*
        |--------------------------------------------------------------------------
        | STEP 4: Simplify delivery statuses
        |--------------------------------------------------------------------------
        */
        DB::table('deliveries')
            ->where('status', 'en_route_pickup')
            ->update(['status' => 'assigned']);

        DB::table('deliveries')
            ->where('status', 'en_route_delivery')
            ->update(['status' => 'picked_up']);
    }

    public function down()
    {
        // Destructive migration — no rollback
    }
};
