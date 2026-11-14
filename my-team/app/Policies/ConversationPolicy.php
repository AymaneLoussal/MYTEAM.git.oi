<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->team->hasMember($user)
            && $conversation->participants()->where('users.id', $user->id)->exists();
    }

    public function send(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation)
            && ($conversation->type !== 'team_announcement' || $conversation->team->coach_id === $user->id);
    }
}
