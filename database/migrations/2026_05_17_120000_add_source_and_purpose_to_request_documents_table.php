<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_documents', function (Blueprint $table) {
            $table->string('source', 32)
                ->default('citizen')
                ->after('uploaded_by');

            $table->string('purpose', 32)
                ->default('requirement')
                ->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('request_documents', function (Blueprint $table) {
            $table->dropColumn(['source', 'purpose']);
        });
    }
};
