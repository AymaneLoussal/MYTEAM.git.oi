<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.custom');
    }

    /**
     * Get logged user teams - as coach (owns) and player (joined).
     * Uses relationships for dynamic roles.
     */
    public function index(Request $request)
    {
        $userId = session('user_id');
        $user = User::find($userId);
        
        return response()->json([
            'coach_teams' => $user->coachTeams,
            'player_teams' => $user->playerTeams
        ]);
    }

    /**
     * Create new team - logged user becomes coach (coach_id set).
     * Policy authorize + unique name validate.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Team::class);
        $request->validate(['name' => 'required|string|max:255|unique:teams,name']);
        
        $userId = session('user_id');
        $team = Team::create([
            'name' => $request->name,
            'coach_id' => $userId
        ]);
        
        return response()->json(['team' => $team], 201);
    }

    /**
     * Join team as player - attach to pivot if not member.
     * Policy + dup check.
     */
    public function join(Team $team)
    {
        $this->authorize('join', $team);
        $userId = session('user_id');
        if ($team->players()->where('user_id', $userId)->exists()) {
            return response()->json(['error' => 'Already a member'], 400);
        }
        $team->players()->attach($userId);
        
        return response()->json(['message' => 'Joined team successfully']);
    }

    /**
     * Admin-only - list all users (for management).
     */
    public function users()
    {
        return response()->json(User::all(['id', 'name', 'email', 'is_admin']));
    }
}
