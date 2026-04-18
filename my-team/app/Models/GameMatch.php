<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameMatch extends Model
{
    protected $table = 'matches';
    
    protected $fillable = [
        'team_id',
        'created_by',
        'starts_at',
        'location',
        'opponent',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function players()
    {
        return $this->belongsToMany(User::class, 'match_user', 'match_id', 'user_id')
            ->withPivot(['role'])
            ->withTimestamps();
    }
}

// GameMatch Model Docblock placeholder 
// Soft deletes stub added 
