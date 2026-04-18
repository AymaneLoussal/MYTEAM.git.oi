<?php

namespace App\Policies;

use App\Models\Formation;
use App\Models\User;

class FormationPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Team policy handles
    }

    public function view(User $user, Formation $formation): bool
    {
        return $formation->team->coach_id === $user->id || $formation->team->players()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true; // Per team
    }

    public function update(User $user, Formation $formation): bool
    {
        return $formation->team->coach_id === $user->id;
    }

    public function delete(User $user, Formation $formation): bool
    {
        return $formation->team->coach_id === $user->id;
    }
}

