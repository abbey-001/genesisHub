<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE sellers ALTER commission_rate SET DEFAULT 10');
        DB::table('sellers')
            ->where('commission_rate', 0)
            ->update(['commission_rate' => 10]);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE sellers ALTER commission_rate SET DEFAULT 0');
    }
};
