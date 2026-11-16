<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TeamMembership
{
    public function createTeam(User $user, string $name): Team
    {
        return DB::transaction(function () use ($user, $name) {
            $user = User::lockForUpdate()->findOrFail($user->id);
            $this->ensureAvailable($user, 'name');

            return Team::create(['name' => $name, 'coach_id' => $user->id]);
        });
    }

    // Call only after authorization (an invitation or existing membership).
    public function addPlayer(User $user, Team $team): void
    {
        DB::transaction(function () use ($user, $team) {
            $user = User::lockForUpdate()->findOrFail($user->id);
            if (! $team->players()->where('users.id', $user->id)->exists()) {
                $this->ensureAvailable($user, 'team');
                $team->players()->syncWithoutDetaching([$user->id]);
            }
            app(TeamChat::class)->ensureGroup($team);
            foreach (Conversation::where('team_id', $team->id)->whereIn('type', ['team_group', 'team_announcement'])->get() as $chat) {
                $chat->participants()->syncWithoutDetaching([$user->id]);
            }
        });
    }

    public function leave(User $user, Team $team): void
    {
        DB::transaction(function () use ($user, $team) {
            User::lockForUpdate()->findOrFail($user->id);
            $team->players()->detach($user->id);
            DB::table('conversation_participants')->where('user_id', $user->id)
                ->whereIn('conversation_id', Conversation::where('team_id', $team->id)->whereIn('type', ['team_group', 'team_announcement'])->select('id'))->delete();
            DB::table('match_user')->where('user_id', $user->id)
                ->whereIn('match_id', $team->matches()->select('id'))->delete();
        });
    }

    private function ensureAvailable(User $user, string $field): void
    {
        if ($user->coachTeams()->exists() || $user->playerTeams()->exists()) {
            throw ValidationException::withMessages([$field => 'You already belong to a team. Leave it before joining or creating another team.']);
        }
    }
}
