<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Team;
use App\Models\User;
use App\Services\TeamMembership;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.custom');
    }

    public function index()
    {
        $user = User::findOrFail(session('user_id'));

        $coachTeams = $user->coachTeams()->with('players')->latest()->get();
        $playerTeams = $user->playerTeams()->with('coach')->latest()->get();

        $pendingInvitations = Invitation::with(['team', 'coach'])
            ->pending()
            ->where(function ($query) { $query->whereNull('expires_at')->orWhere('expires_at', '>', now()); })
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)->orWhere('email', $user->email);
            })
            ->latest()
            ->get();

        return view('dashboard', [
            'user' => $user,
            'coachTeams' => $coachTeams,
            'playerTeams' => $playerTeams,
            'pendingInvitations' => $pendingInvitations,
        ]);
    }

    public function createTeam(Request $request)
    {
        $user = User::findOrFail(session('user_id'));

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name',
        ]);

        $team = app(TeamMembership::class)->createTeam($user, $validated['name']);

        return redirect()->route('teams.show', $team)->with('status', 'Team created successfully! Start by inviting players.');
    }

    public function sendInvite(Request $request, Team $team)
    {
        return app(InvitationController::class)->send($request, $team);
    }
}
