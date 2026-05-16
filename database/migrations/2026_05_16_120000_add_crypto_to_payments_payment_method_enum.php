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

        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_method', ['card', 'cash', 'crypto'])->change();
        });
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

        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_method', ['card', 'cash'])->change();
        });
    }
};
