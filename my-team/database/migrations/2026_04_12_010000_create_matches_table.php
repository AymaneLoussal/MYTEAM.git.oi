<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('starts_at');
            $table->string('location')->nullable();
            $table->string('opponent')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};

