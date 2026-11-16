<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class TeamChat
{
    public function ensureGroup(Team $team): Conversation
    {
        return DB::transaction(function () use ($team) {
            $team = Team::whereKey($team->id)->lockForUpdate()->firstOrFail();
            $chat = Conversation::firstOrCreate(
                ['team_id' => $team->id, 'type' => 'team_group'],
                ['created_by' => $team->coach_id, 'subject' => $team->name]
            );
            if ($chat->subject !== $team->name) $chat->update(['subject' => $team->name]);
            $chat->participants()->sync($team->players()->pluck('users.id')->push($team->coach_id)->unique()->all());
            return $chat;
        });
    }
}
