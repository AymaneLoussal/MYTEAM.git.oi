<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Team;
use App\Services\TeamChat;

return new class extends Migration {
    public function up(): void
    {
        Team::orderBy('id')->each(fn (Team $team) => app(TeamChat::class)->ensureGroup($team));
    }
    public function down(): void {} // Backfilled conversations and history are retained.
};
