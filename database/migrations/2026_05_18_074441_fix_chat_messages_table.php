<?php
// database/migrations/2026_05_18_100000_fix_chat_messages_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('chat_messages');
        
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('service_request_id')
                ->constrained()
                ->cascadeOnDelete();
            
            $table->foreignId('sender_id')
                ->constrained('users')
                ->cascadeOnDelete();
            
            $table->text('message');
            
            $table->timestamp('read_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['service_request_id', 'created_at']);
            $table->index(['sender_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};