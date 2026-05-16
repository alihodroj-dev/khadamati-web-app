<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_verification_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_token')->unique();
            $table->string('id_front_path');
            $table->string('id_back_path');
            $table->longText('ocr_raw_text')->nullable();
            $table->json('extracted_data')->nullable();
            $table->enum('status', ['pending', 'verified', 'failed'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_verification_sessions');
    }
};
