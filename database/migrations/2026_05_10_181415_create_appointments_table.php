<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_request_id')
                ->constrained('service_requests')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('staff_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('appointment_date');
            $table->time('appointment_time');

            $table->enum('status', [
                'scheduled',
                'confirmed',
                'cancelled',
                'completed',
                'missed'
            ])->default('scheduled');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
