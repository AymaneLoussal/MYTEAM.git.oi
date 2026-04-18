<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'password', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

protected $fillable = ['name', 'email', 'password', 'avatar', 'notes'];

    protected $appends = ['avatar_url'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Teams where this user is coach (owner) - dynamic role via coach_id relation.
     */
    public function coachTeams()
    {
        return $this->hasMany(Team::class, 'coach_id');
    }

    /**
     * Teams this user is player/member of - via team_user pivot.
     */
    public function playerTeams()
    {
        return $this->belongsToMany(Team::class, 'team_user');
    }

    /**
     * Check if user is admin (boolean flag).
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * Avatar URL accessor - full public URL or default.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? Storage::url($this->avatar) : asset('images/default-avatar.svg');
    }
    public function userInvitations()
    {
        return $this->hasMany(Invitation::class);
    }
}
// User Model Docblock placeholder 
