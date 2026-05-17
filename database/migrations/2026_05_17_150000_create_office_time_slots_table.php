<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('office_time_slots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('office_id')
                ->constrained('offices')
                ->cascadeOnDelete();

            $table->foreignId('staff_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->unsignedTinyInteger('day_of_week');

            $table->time('start_time');
            $table->time('end_time');

            $table->unsignedSmallInteger('slot_duration_minutes')->default(30);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['office_id', 'day_of_week', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_time_slots');
    }
};
