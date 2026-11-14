<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_user', fn (Blueprint $table) => $table->text('notes')->nullable());
        // Legacy global notes are retained in users, not copied across team boundaries.
    }

    public function down(): void
    {
        Schema::table('team_user', fn (Blueprint $table) => $table->dropColumn('notes'));
    }
};
