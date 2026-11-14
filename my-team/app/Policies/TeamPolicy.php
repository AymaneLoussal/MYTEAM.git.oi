<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

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
     * A current invitation is required; the controller also verifies its token.
     */
    public function join(User $user, Team $team): bool
    {
        return $team->coach_id !== $user->id && $team->invitations()->pending()
            ->whereRaw('LOWER(email) = ?', [strtolower($user->email)])
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))->exists();
    }
}
