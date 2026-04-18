<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Conversations table
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['team_group', 'coach_player_private', 'team_announcement']);
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('subject')->nullable(); // For private chats: "Coach John - Player Mike"
            $table->timestamps();
            
            $table->index(['team_id', 'type']);
        });

        // Conversation participants
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();
            
            $table->unique(['conversation_id', 'user_id']);
            $table->index('user_id');
        });

        // Messages table
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->json('metadata')->nullable(); // For attachments, reactions, etc.
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index('conversation_id');
            $table->index(['conversation_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
    }
};
