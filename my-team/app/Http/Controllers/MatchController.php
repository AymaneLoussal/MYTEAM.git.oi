<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.custom');
    }

    private function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }

    public function index(Request $request)
    {
        $user = User::findOrFail(session('user_id'));

        $teamIds = $user->coachTeams()->pluck('id')
            ->merge($user->playerTeams()->pluck('teams.id'))
            ->unique()
            ->values();

        $matches = GameMatch::with('team')
            ->whereIn('team_id', $teamIds)
            ->orderBy('starts_at')
            ->get();

        if ($this->wantsJson($request)) {
            return response()->json($matches);
        }

        return view('matches.index', [
            'user' => $user,
            'matches' => $matches,
        ]);
    }

    public function team(Request $request, Team $team)
    {
        $user = User::findOrFail(session('user_id'));

        if (! $this->canViewTeam($user, $team)) {
            abort(403, 'Forbidden');
        }

        $matches = $team->matches()->orderBy('starts_at')->get();

        if ($this->wantsJson($request)) {
            return response()->json($matches);
        }

        return view('matches.team', [
            'user' => $user,
            'team' => $team,
            'matches' => $matches,
            'isCoach' => $team->coach_id === $user->id,
        ]);
    }

    public function create(Team $team)
    {
        $user = User::findOrFail(session('user_id'));

        if ($team->coach_id !== $user->id) {
            abort(403, 'Only the coach can create matches.');
        }

        return view('matches.create', [
            'team' => $team,
        ]);
    }

    public function store(Request $request, Team $team)
    {
        $userId = session('user_id');
        \Log::info('Match store attempt', ['user_id' => $userId, 'team_coach_id' => $team->coach_id, 'session_data' => session()->all()]);
        
        $user = User::findOrFail($userId);

        if ($team->coach_id !== $user->id) {
            \Log::error('Authorization failed', ['user_id' => $user->id, 'team_coach_id' => $team->coach_id]);
            abort(403, 'Only the coach can create matches.');
        }

        $validated = $request->validate([
            'starts_at' => 'required|date_format:Y-m-d\\TH:i|after_or_equal:now',
            'location' => 'nullable|string|max:255',
            'opponent' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            \Log::info('Creating match', [
                'team_id' => $team->id,
                'user_id' => $user->id, 
                'starts_at' => $validated['starts_at'],
            ]);
            
            $match = GameMatch::create([
                'team_id' => $team->id,
                'created_by' => $user->id,
                'starts_at' => Carbon::parse($validated['starts_at']),
                'location' => $validated['location'] ?? null,
                'opponent' => $validated['opponent'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
            
            \Log::info('Match created', [
                'match_id' => $match->id ?? 'NULL',
                'match_team_id' => $match->team_id ?? 'NULL',
                'match_data' => $match->toArray(),
            ]);

            if (!$match || !$match->id) {
                \Log::error('Match creation failed', [
                    'team_id' => $team->id,
                    'user_id' => $user->id,
                    'starts_at' => $validated['starts_at'],
                    'match' => $match,
                ]);
                return back()->withErrors(['error' => 'Failed to create match. Please try again.']);
            }
        } catch (\Exception $e) {
            \Log::error('Match creation exception: ' . $e->getMessage(), [
                'team_id' => $team->id,
                'user_id' => $user->id,
                'starts_at' => $validated['starts_at'],
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => 'Error creating match: ' . $e->getMessage()]);
        }

        if ($this->wantsJson($request)) {
            return response()->json($match, 201);
        }

        return redirect()->route('matches.lineup', $match)->with('status', 'Match created. Set your lineup.');
    }

    public function show(Request $request, GameMatch $gameMatch)
    {
        $user = User::findOrFail(session('user_id'));
        $gameMatch->load(['team', 'players']);

        if (!$gameMatch->team || !$this->canViewTeam($user, $gameMatch->team)) {
            abort(403, 'Forbidden');
        }

        $rolesByUserId = $gameMatch->players->pluck('pivot.role', 'id')->all();

        if ($this->wantsJson($request)) {
            return response()->json([
                'match' => $gameMatch,
                'roles' => $rolesByUserId,
            ]);
        }

        return view('matches.show', [
            'user' => $user,
            'match' => $gameMatch,
            'rolesByUserId' => $rolesByUserId,
            'isCoach' => $gameMatch->team->coach_id === $user->id,
        ]);
    }

    public function edit(GameMatch $gameMatch)
    {
        $user = User::findOrFail(session('user_id'));
        $gameMatch->load('team');

        if (!$gameMatch->team || $gameMatch->team->coach_id !== $user->id) {
            abort(403, 'Only the coach can edit matches.');
        }

        return view('matches.edit', [
            'match' => $gameMatch,
        ]);
    }

    public function update(Request $request, GameMatch $gameMatch)
    {
        $user = User::findOrFail(session('user_id'));
        $gameMatch->load('team');

        if (!$gameMatch->team || $gameMatch->team->coach_id !== $user->id) {
            abort(403, 'Only the coach can edit matches.');
        }

        $validated = $request->validate([
            'starts_at' => 'required|date',
            'location' => 'nullable|string|max:255',
            'opponent' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $gameMatch->update($validated);

        if ($this->wantsJson($request)) {
            return response()->json(['message' => 'Match updated']);
        }

        return redirect()->route('matches.show', $gameMatch)->with('status', 'Match updated.');
    }

    public function destroy(Request $request, GameMatch $gameMatch)
    {
        $user = User::findOrFail(session('user_id'));
        $gameMatch->load('team');

        if (!$gameMatch->team || $gameMatch->team->coach_id !== $user->id) {
            abort(403, 'Only the coach can delete matches.');
        }

        $team = $gameMatch->team;
        $gameMatch->delete();

        if ($this->wantsJson($request)) {
            return response()->json(['message' => 'Match deleted']);
        }

        return redirect()->route('matches.team', $team)->with('status', 'Match deleted.');
    }

    public function lineup(GameMatch $gameMatch)
    {
        $user = User::findOrFail(session('user_id'));
        $gameMatch->load('team');

        \Log::info('Lineup access attempt', [
            'user_id' => $user->id,
            'match_id' => $gameMatch->id,
            'match_team_id' => $gameMatch->team_id,
            'team_exists' => $gameMatch->team ? true : false,
            'team_coach_id' => $gameMatch->team?->coach_id,
        ]);

        if (!$gameMatch->team) {
            abort(403, 'Match is not associated with a team. Match ID: ' . $gameMatch->id . ', Team ID: ' . $gameMatch->team_id);
        }

        if ($gameMatch->team->coach_id !== $user->id) {
            abort(403, 'Only the coach can edit the lineup. You are user #' . $user->id . ', coach is #' . $gameMatch->team->coach_id);
        }

        $teamPlayers = $gameMatch->team->players()->orderBy('name')->get();

        $existingRoles = DB::table('match_user')
            ->where('match_id', $gameMatch->id)
            ->pluck('role', 'user_id')
            ->all();

        return view('matches.lineup', [
            'match' => $gameMatch,
            'teamPlayers' => $teamPlayers,
            'existingRoles' => $existingRoles,
        ]);
    }

    public function updateLineup(Request $request, GameMatch $gameMatch)
    {
        $user = User::findOrFail(session('user_id'));
        $gameMatch->load('team');

        if (!$gameMatch->team || $gameMatch->team->coach_id !== $user->id) {
            abort(403, 'Only the coach can edit the lineup.');
        }

        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'nullable|in:starter,substitute,not_selected',
        ]);

        $teamPlayerIds = $gameMatch->team->players()->pluck('users.id')->all();
        $roles = $validated['roles'];

        $starterCount = collect($roles)->filter(fn ($r) => $r === 'starter')->count();
        if ($starterCount > 11) {
            return back()->withErrors(['roles' => 'Maximum 11 starters allowed.'])->withInput();
        }

        DB::transaction(function () use ($gameMatch, $roles, $teamPlayerIds) {
            // Keep only roles for players that are actually in the team.
            $filtered = [];
            foreach ($roles as $userId => $role) {
                $userId = (int) $userId;
                if (! in_array($userId, $teamPlayerIds, true)) {
                    continue;
                }
                $filtered[$userId] = $role ?: 'not_selected';
            }

            foreach ($teamPlayerIds as $userId) {
                $role = $filtered[$userId] ?? 'not_selected';

                DB::table('match_user')->updateOrInsert(
                    ['match_id' => $gameMatch->id, 'user_id' => $userId],
                    ['role' => $role, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        });

        return redirect()->route('matches.show', $gameMatch)->with('status', 'Lineup saved.');
    }

    private function canViewTeam(User $user, Team $team): bool
    {
        if ($team->coach_id === $user->id) {
            return true;
        }

        return $team->players()->where('user_id', $user->id)->exists();
    }
}

