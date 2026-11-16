<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::created(fn (Team $team) => app(\App\Services\TeamChat::class)->ensureGroup($team));
        static::updated(function (Team $team) {
            if ($team->wasChanged('name')) {
                $team->conversations()->where('type', 'team_group')->update(['subject' => $team->name]);
            }
        });
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    protected $fillable = ['name', 'coach_id'];

    /**
     * Coach/owner of the team.
     */
    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    /**
     * Players/members of the team via pivot.
     */
    public function players()
    {
        return $this->belongsToMany(User::class, 'team_user')->withPivot('notes');
    }

    /**
     * Invitations related to this team.
     */
    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function trainings()
    {
        return $this->hasMany(Training::class);
    }

    public function matches()
    {
        return $this->hasMany(GameMatch::class);
    }

    public function formations()
    {
        return $this->hasMany(Formation::class);
    }

    public function hasMember(User $user): bool
    {
        return $this->coach_id === $user->id || $this->players()->where('users.id', $user->id)->exists();
    }
}
// Team Model Docblock placeholder
// Soft deletes stub added
