<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// database/migrations/xxxx_create_newsletter_subscriptions_table.php

public function up(): void
{
    Schema::create('newsletter_subscriptions', function (Blueprint $table) {
        $table->id();
        $table->string('email')->unique();
        $table->string('token', 64)->unique(); // for future unsubscribe link
        $table->boolean('is_active')->default(true);
        $table->timestamp('subscribed_at')->useCurrent();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscriptions');
    }
};
