<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('push_notifications_enabled')->default(true)->after('is_active');
            $table->boolean('email_notifications_enabled')->default(true)->after('push_notifications_enabled');
            $table->boolean('sms_notifications_enabled')->default(false)->after('email_notifications_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'push_notifications_enabled',
                'email_notifications_enabled',
                'sms_notifications_enabled',
            ]);
        });
    }
};
