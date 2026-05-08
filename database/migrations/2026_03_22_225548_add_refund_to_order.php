<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add refund tracking columns to orders and extend the payment_status
     * enum to include 'refund_pending' and 'refund_rejected'.
     *
     * Refund pipeline:
     *   paid → (customer cancels) → refund_pending → (admin approves) → refunded
     *                                              → (admin rejects)  → refund_rejected
     */
    public function up(): void
    {
        // ── 1. Add columns ────────────────────────────────────────────────
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'refund_amount')) {
                $table->decimal('refund_amount', 10, 2)->nullable()->after('total');
            }

            if (! Schema::hasColumn('orders', 'refund_method')) {
                // original_payment | wallet | bank_transfer
                $table->string('refund_method', 50)->nullable()->after('refund_amount');
            }

            if (! Schema::hasColumn('orders', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable()->after('refund_method');
            }
        });

        // ── 2. Extend payment_status ENUM (MySQL/MariaDB only) ────────────
        //
        // If payment_status is an ENUM we need to add the two new values.
        // If it's a plain VARCHAR/string column the values work without change.
        //
        // We read the current COLUMN_TYPE, parse out the existing values, merge
        // in the new ones, and ALTER only if something is actually missing.

        if (DB::connection()->getDriverName() === 'mysql') {
            $column = DB::selectOne("
                SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME   = 'orders'
                  AND COLUMN_NAME  = 'payment_status'
            ");

            if ($column && str_contains(strtolower((string) $column->COLUMN_TYPE), 'enum')) {
                $newValues   = ['refund_pending', 'refund_rejected'];
                $needsChange = false;

                foreach ($newValues as $val) {
                    if (! str_contains($column->COLUMN_TYPE, "'{$val}'")) {
                        $needsChange = true;
                        break;
                    }
                }

                if ($needsChange) {
                    // Strip enum( ... ) wrapper, split existing values
                    $inner    = preg_replace("/^enum\(|\)$/i", '', $column->COLUMN_TYPE);
                    $existing = array_map(
                        fn($v) => trim($v, "'"),
                        str_getcsv($inner, ',', "'")
                    );

                    // Merge, keeping order, no duplicates
                    $all = array_unique(array_merge($existing, $newValues));

                    $enumDef = "enum('" . implode("','", $all) . "')";
                    $null    = $column->IS_NULLABLE === 'YES' ? 'NULL' : 'NOT NULL';
                    $default = $column->COLUMN_DEFAULT !== null
                        ? "DEFAULT '{$column->COLUMN_DEFAULT}'"
                        : '';

                    DB::statement(
                        "ALTER TABLE orders MODIFY COLUMN payment_status {$enumDef} {$null} {$default}"
                    );
                }
            }
        }
    }

    /**
     * Reverse the migration.
     *
     * NOTE: Removing ENUM values is risky when rows may already hold those values.
     * The rollback drops the new columns but leaves the ENUM as-is to avoid data loss.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $cols = array_filter(
                ['refund_amount', 'refund_method', 'refunded_at'],
                fn($c) => Schema::hasColumn('orders', $c)
            );

            if ($cols) {
                $table->dropColumn(array_values($cols));
            }
        });

        // Intentionally NOT removing 'refund_pending' / 'refund_rejected' from
        // the enum on rollback — doing so would corrupt any rows that hold those values.
    }
};