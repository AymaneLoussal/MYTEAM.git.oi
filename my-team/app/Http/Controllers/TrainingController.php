<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Training;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TrainingController extends Controller
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

        $teamIds = $user->coachTeams()->pluck('teams.id')
            ->merge($user->playerTeams()->pluck('teams.id'))
            ->unique()
            ->values();

        $trainings = Training::with('team')
            ->whereIn('team_id', $teamIds)
            ->orderBy('starts_at')
            ->get();

        if ($this->wantsJson(request())) {
            return response()->json($trainings);
        }

        return view('trainings.index', [
            'user' => $user,
            'trainings' => $trainings,
        ]);
    }

    public function team(Team $team)
    {
        $user = User::findOrFail(session('user_id'));

        if (! $this->canViewTeam($user, $team)) {
            abort(403, 'Forbidden');
        }

        $trainings = $team->trainings()->orderBy('starts_at')->get();

        if ($this->wantsJson(request())) {
            return response()->json($trainings);
        }

        return view('trainings.team', [
            'user' => $user,
            'team' => $team,
            'trainings' => $trainings,
            'isCoach' => $team->coach_id === $user->id,
        ]);
    }

    public function create(Team $team)
    {
        $user = User::findOrFail(session('user_id'));

        if ($team->coach_id !== $user->id) {
            abort(403, 'Only the coach can schedule trainings.');
        }

        return view('trainings.create', [
            'team' => $team,
        ]);
    }

    public function store(Request $request, Team $team)
    {
        $user = User::findOrFail(session('user_id'));

        if ($team->coach_id !== $user->id) {
            abort(403, 'Only the coach can schedule trainings.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'starts_at' => 'required|date_format:Y-m-d\\TH:i|after_or_equal:now',
            'ends_at' => 'nullable|date_format:Y-m-d\\TH:i|after_or_equal:starts_at',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        Training::create([
            'team_id' => $team->id,
            'created_by' => $user->id,
            'title' => $validated['title'],
            'starts_at' => Carbon::parse($validated['starts_at']),
            'ends_at' => isset($validated['ends_at']) ? Carbon::parse($validated['ends_at']) : null,
            'location' => $validated['location'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($this->wantsJson($request)) {
            return response()->json(['message' => 'Training scheduled'], 201);
        }

        return redirect()->route('trainings.team', $team)->with('status', 'Training scheduled.');
    }

    public function show(Training $training)
    {
        $user = User::findOrFail(session('user_id'));
        $training->load('team');

        if (! $this->canViewTeam($user, $training->team)) {
            abort(403, 'Forbidden');
        }

        if ($this->wantsJson(request())) {
            return response()->json($training);
        }

        return view('trainings.show', [
            'user' => $user,
            'training' => $training,
            'isCoach' => $training->team->coach_id === $user->id,
        ]);
    }

    public function edit(Training $training)
    {
        $user = User::findOrFail(session('user_id'));
        $training->load('team');

        if ($training->team->coach_id !== $user->id) {
            abort(403, 'Only the coach can edit trainings.');
        }

        return view('trainings.edit', [
            'training' => $training,
        ]);
    }

    public function update(Request $request, Training $training)
    {
        $user = User::findOrFail(session('user_id'));
        $training->load('team');

        if ($training->team->coach_id !== $user->id) {
            abort(403, 'Only the coach can edit trainings.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $training->update($validated);

        if ($this->wantsJson($request)) {
            return response()->json(['message' => 'Training updated']);
        }

        return redirect()->route('trainings.show', $training)->with('status', 'Training updated.');
    }

    public function destroy(Training $training)
    {
        $user = User::findOrFail(session('user_id'));
        $training->load('team');

        if ($training->team->coach_id !== $user->id) {
            abort(403, 'Only the coach can delete trainings.');
        }

        $team = $training->team;
        $training->delete();

        if ($this->wantsJson(request())) {
            return response()->json(['message' => 'Training deleted']);
        }

        return redirect()->route('trainings.team', $team)->with('status', 'Training deleted.');
    }

    private function canViewTeam(User $user, Team $team): bool
    {
        if ($team->coach_id === $user->id) {
            return true;
        }

        return $team->players()->where('user_id', $user->id)->exists();
    }
}
