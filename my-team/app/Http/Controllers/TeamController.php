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
        
        $coachTeams = $user->coachTeams()->with('players', 'coach')->latest()->get();
        $playerTeams = $user->playerTeams()->with('players', 'coach')->latest()->get();

        return view('teams.index', [
            'coachTeams' => $coachTeams,
            'playerTeams' => $playerTeams,
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

    /**
     * Coach-only: Update player notes for team member.
     */
    public function updatePlayerNotes(Request $request, Team $team, User $player)
    {
        $this->authorize('manage', $team);
        
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $player->update(['notes' => $validated['notes']]);

        return back()->with('status', 'Notes updated for ' . $player->name);
    }

    /**
     * Show single team with players (for notes).
     */
    public function show(Team $team)
    {
        $user = User::find(session('user_id'));
        
        if (! $this->canViewTeam($user, $team)) {
            abort(403);
        }

        $players = $team->players()->get();

        return view('teams.show', [
            'team' => $team->load('coach'),
            'players' => $players,
            'isCoach' => $team->coach_id === $user->id,
            'notes' => $players->pluck('notes', 'id')->toArray(),
        ]);
    }

    private function canViewTeam(User $user, Team $team): bool
    {
        return $team->coach_id === $user->id || $team->players()->where('user_id', $user->id)->exists();
    }

    public function leave(Team $team)
    {
        $user = User::find(session('user_id'));

        // Verify user is a player in this team
        if (!$team->players()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['error' => 'You are not a member of this team.']);
        }

        // Remove player from team
        $team->players()->detach($user->id);

        return redirect()->route('teams.index')->with('status', 'You have left the team successfully.');
    }
}

