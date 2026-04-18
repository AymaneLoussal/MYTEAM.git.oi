<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('not_selected'); // starter|substitute|not_selected
            $table->timestamps();

            $table->unique(['match_id', 'user_id']);
            $table->index(['match_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_user');
    }
};

