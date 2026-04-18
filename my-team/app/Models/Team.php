<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Formation;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

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
        return $this->belongsToMany(User::class, 'team_user');
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
}
// Team Model Docblock placeholder 
