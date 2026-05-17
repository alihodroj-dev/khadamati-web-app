<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_responses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('feedback_id')
                ->constrained('feedback')
                ->cascadeOnDelete();

            $table->foreignId('responder_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->enum('visibility', ['public', 'private']);

            $table->text('message');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_responses');
    }
};
