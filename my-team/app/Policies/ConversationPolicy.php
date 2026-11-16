<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        $team = $conversation->team;
        if (! $team || ! $team->hasMember($user) || ! $conversation->participants()->where('users.id', $user->id)->exists()) {
            return false;
        }
        if ($conversation->type === 'coach_player_private') {
            $ids = $conversation->participants()->pluck('users.id');
            return $ids->count() === 2 && $ids->contains($team->coach_id)
                && $ids->contains($conversation->private_player_id)
                && $conversation->private_player_id !== $team->coach_id
                && $team->players()->whereIn('users.id', $ids->all())->exists();
        }
        return true;
    }

    public function send(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation)
            && ($conversation->type !== 'team_announcement' || $conversation->team->coach_id === $user->id);
    }
}
