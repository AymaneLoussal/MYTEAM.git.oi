<?php

namespace App\Http\Controllers;

use App\Mail\TeamInvitationMail;
use App\Models\Invitation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function send(Request $request, Team $team)
    {
        $coach = $this->currentUser();

        if ($team->coach_id !== $coach->id) {
            return response()->json(['message' => 'forbidden'], 403);
        }

        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower(trim($validated['email']));
        $targetUser = User::where('email', $email)->first();

        if ($email === strtolower($coach->email)) {
            return response()->json(['message' => 'Coach cannot invite themself'], 422);
        }

        if ($targetUser && $team->players()->where('user_id', $targetUser->id)->exists()) {
            return response()->json(['message' => 'User already in team'], 422);
        }

        $existingInvitation = Invitation::pending()
            ->where('team_id', $team->id)
            ->where('email', $email)
            ->first();

        if ($existingInvitation && ! $existingInvitation->isExpired()) {
            return response()->json(['message' => 'A pending invitation already exists for this email'], 422);
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

        $invitation->load(['team', 'coach', 'user']);

        Mail::to($email)->send(new TeamInvitationMail($invitation));

        return response()->json([
            'message' => 'Invitation sent successfully',
            'invitation' => $invitation,
        ], 201);
    }

    public function index()
    {
        $user = $this->currentUser();

        $invitations = Invitation::with(['team', 'coach'])
            ->pending()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('email', $user->email);
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json($invitations);
    }

    public function show(string $token)
    {
        $invitation = Invitation::with(['team', 'coach'])->where('token', $token)->firstOrFail();

        if ($invitation->isExpired() && $invitation->status === 'pending') {
            $invitation->update(['status' => 'expired']);
            $invitation->refresh();
        }

        return view('invitations.show', [
            'invitation' => $invitation,
            'currentUser' => $this->currentUser(false),
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if ($invitation->status !== 'pending') {
            return redirect()->route('dashboard')->with('error', 'This invitation has already been processed.');
        }

        if ($invitation->isExpired()) {
            $invitation->update(['status' => 'expired']);
            return redirect()->route('dashboard')->with('error', 'This invitation has expired.');
        }

        // Check if user exists
        $existingUser = User::where('email', strtolower(trim($invitation->email)))->first();

        if ($existingUser) {
            // User exists - check if logged in
            $currentUser = $this->currentUser(false);
            
            if (!$currentUser) {
                // Not logged in - redirect to login
                session(['invitation_token' => $token]);
                return redirect()->route('login.form')->with('message', 'Please log in with your email to join the team.');
            }

            if (strtolower($currentUser->email) !== strtolower($invitation->email)) {
                return back()->with('error', 'Please log in with the invited email address.');
            }

            // Add to team if not already member
            if (!$invitation->team->players()->where('user_id', $currentUser->id)->exists()) {
                $invitation->team->players()->attach($currentUser->id);
            }

            $invitation->update([
                'user_id' => $currentUser->id,
                'status' => 'accepted',
                'responded_at' => now(),
            ]);

            return redirect()->route('teams.show', $invitation->team)
                ->with('status', 'Invitation accepted! You have joined the team.');
        } else {
            // User doesn't exist - show registration form
            return view('invitations.register', [
                'invitation' => $invitation,
            ]);
        }
    }

    public function registerAndAccept(Request $request, string $token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if ($invitation->status !== 'pending') {
            return back()->with('error', 'This invitation has already been processed.');
        }

        if ($invitation->isExpired()) {
            $invitation->update(['status' => 'expired']);
            return back()->with('error', 'This invitation has expired.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Create new user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $invitation->email,
            'password' => bcrypt($validated['password']),
        ]);

        // Add to team
        $invitation->team->players()->attach($user->id);

        // Mark invitation as accepted
        $invitation->update([
            'user_id' => $user->id,
            'status' => 'accepted',
            'responded_at' => now(),
        ]);

        // Auto login
        session(['user_id' => $user->id]);

        return redirect()->route('teams.show', $invitation->team)
            ->with('status', 'Welcome! Your account has been created and you have joined the team.');
    }

    public function reject(Request $request, string $token)
    {
        $user = $this->currentUser();
        $invitation = $this->findPendingInvitationByToken($token);

        if ($this->invitationEmailMismatch($invitation, $user)) {
            return response()->json(['message' => 'Please sign in with the invited email address'], 403);
        }

        $invitation->update([
            'user_id' => $user->id,
            'status' => 'rejected',
            'responded_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Invitation rejected successfully']);
        }

        return redirect()->route('invitations.show', $invitation->token)
            ->with('status', 'Invitation rejected.');
    }

    private function currentUser(bool $required = true): ?User
    {
        $user = User::find(session('user_id'));

        if ($required && ! $user) {
            abort(401, 'Authentication required.');
        }

        return $user;
    }

    private function findPendingInvitationByToken(string $token): Invitation
    {
        $invitation = Invitation::with('team')->where('token', $token)->firstOrFail();

        if ($invitation->status !== 'pending') {
            abort(410, 'This invitation has already been processed.');
        }

        if ($invitation->isExpired()) {
            $invitation->update(['status' => 'expired']);
            abort(410, 'This invitation has expired.');
        }

        return $invitation;
    }

    private function invitationEmailMismatch(Invitation $invitation, User $user): bool
    {
        return strtolower($invitation->email) !== strtolower($user->email);
    }
}
