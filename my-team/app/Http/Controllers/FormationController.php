<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FormationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.custom');
    }

    private function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }

    public function index()
    {
        $user = User::findOrFail(session('user_id'));

        $coachTeams = $user->coachTeams()->with('formations')->latest()->get();
        $playerTeams = $user->playerTeams()->with('formations')->latest()->get();

        // Get all formations from teams the user has access to
        $formations = Formation::whereIn('team_id',
            $coachTeams->pluck('id')->merge($playerTeams->pluck('id'))
        )->latest()->get();

        if ($this->wantsJson(request())) {
            return response()->json($formations);
        }

        return view('formations.index', [
            'user' => $user,
            'formations' => $formations,
            'coachTeams' => $coachTeams,
            'playerTeams' => $playerTeams,
        ]);
    }

    public function team(Team $team)
    {
        $user = User::findOrFail(session('user_id'));

        if (! $this->canViewTeam($user, $team)) {
            abort(403);
        }

        $formations = $team->formations()->latest()->get();

        if ($this->wantsJson(request())) {
            return response()->json($formations);
        }

        return view('formations.team', [
            'user' => $user,
            'team' => $team,
            'formations' => $formations,
            'isCoach' => $team->coach_id === $user->id,
        ]);
    }

    public function create(Team $team)
    {
        $user = User::findOrFail(session('user_id'));

        if ($team->coach_id !== $user->id) {
            abort(403, 'Only coach can create formations.');
        }

        $players = $team->players()->orderBy('name')->get();

        return view('formations.create', [
            'team' => $team,
            'players' => $players,
        ]);
    }

    public function store(Request $request, Team $team)
    {
        $user = User::findOrFail(session('user_id'));

        if ($team->coach_id !== $user->id) {
            abort(403);
        }

        $validated = $this->validatePositions($request, $team);

        Formation::create([
            'team_id' => $team->id,
            'created_by' => $user->id,
            'title' => $validated['title'],
            'positions' => $validated['positions'],
        ]);

        if ($this->wantsJson($request)) {
            return response()->json(['message' => 'Formation saved'], 201);
        }

        return redirect()->route('formations.team', $team)->with('status', 'Formation created.');
    }

    public function show(Formation $formation)
    {
        $user = User::findOrFail(session('user_id'));
        $formation->load('team');

        if (! $this->canViewTeam($user, $formation->team)) {
            abort(403);
        }

        if ($this->wantsJson(request())) {
            return response()->json($formation);
        }

        $players = $formation->team->players()->orderBy('name')->get();

        return view('formations.show', [
            'user' => $user,
            'formation' => $formation,
            'players' => $players,
            'isCoach' => $formation->team->coach_id === $user->id,
        ]);
    }

    public function update(Request $request, Formation $formation)
    {
        $user = User::findOrFail(session('user_id'));
        $formation->load('team');

        if ($formation->team->coach_id !== $user->id) {
            abort(403);
        }

        $validated = $this->validatePositions($request, $formation->team);

        $formation->update([
            'title' => $validated['title'],
            'positions' => $validated['positions'],
        ]);

        return $request->expectsJson()
            ? response()->json(['message' => 'Formation updated'])
            : redirect()->route('formations.show', $formation)->with('status', 'Formation updated.');
    }

    private function validatePositions(Request $request, Team $team): array
    {
        if (is_string($request->input('positions'))) {
            $request->merge(['positions' => json_decode($request->input('positions'), true)]);
        }

        return $request->validate([
            'title' => 'required|string|max:255',
            'positions' => 'required|array|min:1|max:11',
            'positions.*' => 'required|array:player_id,x,y,role',
            'positions.*.player_id' => ['required', 'integer', 'distinct', Rule::exists('team_user', 'user_id')->where('team_id', $team->id)],
            'positions.*.x' => 'required|numeric|min:0|max:100',
            'positions.*.y' => 'required|numeric|min:0|max:100',
            'positions.*.role' => 'required|in:GK,DEF,MID,FWD,starter,substitute',
        ]);
    }

    public function edit(Formation $formation)
    {
        $this->authorize('update', $formation);

        return view('formations.create', [
            'team' => $formation->team, 'formation' => $formation,
            'players' => $formation->team->players()->orderBy('name')->get(),
        ]);
    }

    public function destroy(Request $request, Formation $formation)
    {
        $this->authorize('delete', $formation);
        $team = $formation->team;
        $formation->delete();

        return $request->expectsJson()
            ? response()->json(['message' => 'Formation deleted'])
            : redirect()->route('formations.team', $team)->with('status', 'Formation deleted.');
    }

    private function canViewTeam(User $user, Team $team): bool
    {
        return $team->coach_id === $user->id || $team->players()->where('user_id', $user->id)->exists();
    }
}

// FormationController Docblock placeholder
