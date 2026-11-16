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
            'preset' => $validated['preset'] ?? null,
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
            'preset' => $validated['preset'] ?? $formation->preset,
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

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'preset' => ['sometimes', 'required', Rule::in(array_keys(config('tactics.presets')))],
            'positions' => 'required|array|min:1',
            'positions.*' => 'required|array:player_id,x,y,role,status,position,slot',
            'positions.*.position' => ['sometimes', 'required', Rule::in(array_keys(config('tactics.roles')))],
            'positions.*.slot' => 'sometimes|nullable|integer|min:0|max:10',
            'positions.*.player_id' => ['required', 'integer', 'distinct', Rule::exists('team_user', 'user_id')->where('team_id', $team->id)],
            'positions.*.x' => 'required|numeric|min:0|max:100',
            'positions.*.y' => 'required|numeric|min:0|max:100',
            'positions.*.role' => 'required|in:GK,DEF,MID,FWD,starter,substitute',
            'positions.*.status' => 'sometimes|required|in:starter,substitute',
        ]);
        $starters = 0; $slots = [];
        foreach ($validated['positions'] as &$position) {
            $status = $position['status'] ?? ($position['role'] === 'substitute' ? 'substitute' : 'starter');
            if (isset($position['status']) && in_array($position['role'], ['starter', 'substitute'])) {
                throw \Illuminate\Validation\ValidationException::withMessages(['positions' => 'Tactical role must be GK, DEF, MID or FWD.']);
            }
            if ($status === 'starter') $starters++;
            if (isset($validated['preset']) || isset($position['position']) || array_key_exists('slot', $position)) {
                if (!isset($position['position'], $position['status']) || config('tactics.roles.'.$position['position']) !== $position['role']) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['positions'=>'Specific position must match the broad role and include squad status.']);
                }
                if ($status === 'starter') {
                    if (!isset($position['slot']) || in_array($position['slot'], $slots)) throw \Illuminate\Validation\ValidationException::withMessages(['positions'=>'Each starter requires a unique tactical slot.']);
                    $slots[] = $position['slot'];
                } elseif (isset($position['slot'])) throw \Illuminate\Validation\ValidationException::withMessages(['positions'=>'Substitutes cannot occupy pitch slots.']);
            }
        }
        unset($position);
        if ($starters > 11) throw \Illuminate\Validation\ValidationException::withMessages(['positions' => 'Maximum 11 starters.']);
        return $validated;
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
