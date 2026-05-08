<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {


        /*
        |--------------------------------------------------------------------------
        | 2. Delivery Broadcasts Table
        |--------------------------------------------------------------------------
        */
        Schema::create('delivery_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['active', 'accepted', 'expired', 'cancelled'])->default('active');

            // Broadcast stats
            $table->integer('broadcast_to_count')->default(0);
            $table->integer('max_responders')->default(5);
            $table->integer('view_count')->default(0);
            $table->integer('reject_count')->default(0);

            // Timing
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();

            // Assignment
            $table->foreignId('accepted_by_rider_id')
                ->nullable()
                ->constrained('riders')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'expires_at']);
            $table->index('delivery_id');
        });

        /*
        |--------------------------------------------------------------------------
        | 3. Broadcast ↔ Rider Pivot
        |--------------------------------------------------------------------------
        */
        Schema::create('broadcast_rider', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broadcast_id')->constrained('delivery_broadcasts')->cascadeOnDelete();
            $table->foreignId('rider_id')->constrained()->cascadeOnDelete();

            $table->enum('response', ['pending', 'viewed', 'accepted', 'rejected'])->default('pending');
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->unique(['broadcast_id', 'rider_id']);
            $table->index('response');
        });

        /*
        |--------------------------------------------------------------------------
        | 4. Rider Assignment Queue
        |--------------------------------------------------------------------------
        */
        Schema::create('rider_assignment_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained()->cascadeOnDelete();

            // Queue logic
            $table->integer('priority')->default(3);
            $table->enum('status', [
                'pending', 'processing', 'completed', 'failed', 'cancelled'
            ])->default('pending');

            // Retry logic
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(10);
            $table->timestamp('next_attempt_at')->nullable();

            // Debugging / metadata
            $table->text('last_error')->nullable();
            $table->json('attempted_strategies')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['status', 'priority', 'next_attempt_at']);
            $table->index('delivery_id');
        });

        /*
        |--------------------------------------------------------------------------
        | 5. Update Deliveries Table
        |--------------------------------------------------------------------------
        */
        Schema::table('deliveries', function (Blueprint $table) {
            $table->string('assignment_method')->nullable()->after('rider_id');
            $table->foreignId('broadcast_id')
                ->nullable()
                ->after('assignment_method')
                ->constrained('delivery_broadcasts')
                ->nullOnDelete();

            $table->integer('assignment_attempts')->default(0)->after('broadcast_id');
            $table->integer('priority')->default(3)->after('status');

            $table->index('assignment_method');
            $table->index('priority');
        });

        /*
        |--------------------------------------------------------------------------
        | 6. Rider Assignment History
        |--------------------------------------------------------------------------
        */
        Schema::create('rider_assignment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rider_id')->nullable()->constrained()->nullOnDelete();

            $table->string('action');
            $table->string('method');
            $table->string('strategy')->nullable();

            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();

            $table->foreignId('performed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['delivery_id', 'created_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rider_assignment_history');
        Schema::dropIfExists('rider_assignment_queue');
        Schema::dropIfExists('broadcast_rider');

        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropForeign(['broadcast_id']);
            $table->dropColumn([
                'assignment_method',
                'broadcast_id',
                'assignment_attempts',
                'priority',
            ]);
        });

        Schema::dropIfExists('delivery_broadcasts');

       
    }
};
