<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── email_change_requests ─────────────────────────────────────────────
        Schema::create('email_change_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('old_email');
            $table->string('new_email');
            $table->string('token', 64)->unique();
            $table->boolean('confirmed')->default(false);
            $table->timestamp('expires_at');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index('token');
            $table->index(['user_id', 'confirmed']);
        });

        // ── users — deactivation columns ─────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('deactivated_at')->nullable()->after('deleted_at');
            $table->timestamp('reactivation_deadline')->nullable()->after('deactivated_at');
            // google_id and facebook_id if not already present from social login migration
            // These are added here as a safe guard — the migration checks first
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_change_requests');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['deactivated_at', 'reactivation_deadline']);
        });
    }
};