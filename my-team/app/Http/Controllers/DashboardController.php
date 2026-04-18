<?php

namespace App\Http\Controllers;

use App\Mail\TeamInvitationMail;
use App\Models\Invitation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

        // Check if user is already coaching a team
        if ($user->coachTeams()->exists()) {
            return back()->withErrors(['name' => 'You are already coaching a team. You must transfer or delete your current team before creating a new one.']);
        }

        // Check if user is a player in another team
        if ($user->playerTeams()->exists()) {
            return back()->withErrors(['name' => 'You are already a member of another team. You must leave that team before creating a new one.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name',
        ]);

        $team = Team::create([
            'name' => $validated['name'],
            'coach_id' => $user->id,
        ]);

        return redirect()->route('teams.show', $team)->with('status', 'Team created successfully! Start by inviting players.');
    }

    public function sendInvite(Request $request, Team $team)
    {
        $coach = User::findOrFail(session('user_id'));

        if ($team->coach_id !== $coach->id) {
            abort(403, 'Forbidden');
        }

        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower(trim($validated['email']));
        if ($email === strtolower($coach->email)) {
            return back()->withErrors(['email' => 'Coach cannot invite themself']);
        }

        $targetUser = User::where('email', $email)->first();
        if ($targetUser && $team->players()->where('user_id', $targetUser->id)->exists()) {
            return back()->withErrors(['email' => 'User is already in the team']);
        }

        $existingInvitation = Invitation::pending()
            ->where('team_id', $team->id)
            ->where('email', $email)
            ->first();

        if ($existingInvitation && ! $existingInvitation->isExpired()) {
            return back()->withErrors(['email' => 'A pending invitation already exists for this email']);
        }

        $payload = [
            'team_id' => $team->id,
            'user_id' => $targetUser?->id,
            'email' => $email,
            'invited_by' => $coach->id,
            'token' => Str::random(64),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
            'responded_at' => null,
        ];

        if ($existingInvitation) {
            $existingInvitation->update($payload);
            $invitation = $existingInvitation->fresh();
        } else {
            $invitation = Invitation::create($payload);
        }

        $invitation->load(['team', 'coach']);
        Mail::to($email)->send(new TeamInvitationMail($invitation));

        return back()->with('status', 'Invitation email sent.');
    }
}

