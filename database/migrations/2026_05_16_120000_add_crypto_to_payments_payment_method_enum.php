<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE payments MODIFY payment_method ENUM('card', 'cash', 'crypto') NOT NULL"
            );

            return;
        }

        DB::statement("ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_payment_method_check");
        DB::statement("ALTER TABLE payments ALTER COLUMN payment_method TYPE VARCHAR(255)");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_payment_method_check CHECK (payment_method IN ('card', 'cash', 'crypto'))");
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE payments MODIFY payment_method ENUM('card', 'cash') NOT NULL"
            );

            return;
        }

        DB::statement("ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_payment_method_check");
        DB::statement("ALTER TABLE payments ALTER COLUMN payment_method TYPE VARCHAR(255)");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_payment_method_check CHECK (payment_method IN ('card', 'cash'))");
    }
};
