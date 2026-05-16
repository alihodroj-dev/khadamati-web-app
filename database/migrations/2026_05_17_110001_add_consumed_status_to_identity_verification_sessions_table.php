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
            DB::statement("ALTER TABLE identity_verification_sessions MODIFY status ENUM('pending', 'verified', 'failed', 'consumed') NOT NULL DEFAULT 'pending'");
        } else {
            Schema::table('identity_verification_sessions', function (Blueprint $table) {
                $table->string('status')->default('pending')->change();
            });
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE identity_verification_sessions MODIFY status ENUM('pending', 'verified', 'failed') NOT NULL DEFAULT 'pending'");
        }
    }
};
