<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('office_time_slot_blocks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('office_id')
                ->constrained('offices')
                ->cascadeOnDelete();

            $table->foreignId('staff_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');

            $table->string('reason')->nullable();

            $table->timestamps();

            $table->index(['office_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_time_slot_blocks');
    }
};
