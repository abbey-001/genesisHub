<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Add notifiable_type and notifiable_id if they don't exist
            if (!Schema::hasColumn('notifications', 'notifiable_type')) {
                $table->string('notifiable_type')->after('id');
            }
            if (!Schema::hasColumn('notifications', 'notifiable_id')) {
                $table->unsignedBigInteger('notifiable_id')->after('notifiable_type');
            }
            if (!Schema::hasColumn('notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('data');
            }
            
            // Add index for polymorphic relationship
            if (!Schema::hasColumn('notifications', 'data')) {
                $table->index(['notifiable_type', 'notifiable_id']);
            }
        });
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Drop columns if needed
            $table->dropColumn(['notifiable_type', 'notifiable_id', 'read_at']);
        });
    }
};
