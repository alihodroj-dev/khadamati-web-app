<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('father_name')->nullable()->after('last_name');
            $table->string('mother_name')->nullable()->after('father_name');
            $table->date('date_of_birth')->nullable()->after('mother_name');
            $table->string('id_front_path')->nullable()->after('id_document_path');
            $table->string('id_back_path')->nullable()->after('id_front_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'father_name',
                'mother_name',
                'date_of_birth',
                'id_front_path',
                'id_back_path',
            ]);
        });
    }
};
