<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Team;

class TeamPolicy
{
    /**
     * Allow any authenticated user to create a team (auto becomes coach).
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only team coach can manage (update/delete) the team.
     */
    public function manage(User $user, Team $team): bool
    {
        return $team->coach_id === $user->id;
    }

    /**
     * Open join - anyone can join any team (validation/pivot handles rest).
     */
    public function join(User $user, Team $team): bool
    {
        return true;
    }
}
