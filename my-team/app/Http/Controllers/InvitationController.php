<?php

namespace App\Http\Controllers;

use App\Mail\TeamInvitationMail;
use App\Models\Invitation;
use App\Models\Team;
use App\Models\User;
use App\Services\TeamMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvitationController extends Controller
{
    public function send(Request $request, Team $team)
    {
        $email = strtolower(trim((string) $request->input('email')));
        $lock = \Illuminate\Support\Facades\Cache::lock('invite-create:'.$team->id.':'.hash('sha256', $email), 120);
        if (! $lock->get()) throw ValidationException::withMessages(['email' => 'An invitation request is already in progress.']);
        try { return $this->sendInvitation($request, $team); }
        finally { $lock->release(); }
    }

    private function sendInvitation(Request $request, Team $team)
    {
        $coach = $this->currentUser();

        if ($team->coach_id !== $coach->id) {
            return response()->json(['message' => 'forbidden'], 403);
        }

        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower(trim($validated['email']));
        $targetUser = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($email === strtolower($coach->email)) {
            throw ValidationException::withMessages(['email' => 'Coach cannot invite themself']);
        }

        if ($targetUser && $team->players()->where('user_id', $targetUser->id)->exists()) {
            throw ValidationException::withMessages(['email' => 'User already in team']);
        }

        $existingInvitation = Invitation::pending()
            ->where('team_id', $team->id)
            ->where('email', $email)
            ->first();

        if ($existingInvitation && ! $existingInvitation->isExpired()) {
            throw ValidationException::withMessages(['email' => 'A pending invitation already exists for this email']);
        }

        if (config('mail.default') === 'log') {
            throw ValidationException::withMessages(['email' => 'Email delivery is not configured. Ask an administrator to configure a mail transport before sending invitations.']);
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

        return $this->deliver($request, $invitation);
    }

    public function resend(Request $request, Invitation $invitation)
    {
        abort_unless($invitation->team->coach_id === $this->currentUser()->id, 403);
        abort_if($invitation->status !== 'pending' || $invitation->isExpired(), 410, 'This invitation is no longer active.');
        $member = User::whereRaw('LOWER(email) = ?', [strtolower($invitation->email)])->first();
        abort_if($member && $invitation->team->hasMember($member), 422, 'This user already belongs to the team.');
        if (config('mail.default') === 'log') throw ValidationException::withMessages(['email'=>'Email delivery is not configured.']);
        return $this->deliver($request, $invitation);
    }

    private function deliver(Request $request, Invitation $invitation)
    {
        $key = 'invitation-delivery:'.$invitation->id;
        if (! \Illuminate\Support\Facades\Cache::add($key, true, 60)) {
            throw ValidationException::withMessages(['email'=>'Please wait one minute before resending this invitation.']);
        }
        try {
            Mail::to($invitation->email)->send(new TeamInvitationMail($invitation));
        } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $exception) {
            \Illuminate\Support\Facades\Log::warning('Invitation transport submission failed', ['invitation_id'=>$invitation->id, 'exception_type'=>get_class($exception)]);
            throw ValidationException::withMessages(['email'=>'Email submission failed. The invitation remains pending; wait one minute, then use Resend Invitation. Inbox delivery has not been confirmed.']);
        }

        if (! $request->expectsJson()) {
            return back()->with('status', 'Invitation submitted to the mail server. Inbox delivery is not yet confirmed.');
        }

        return response()->json([
            'message' => 'Invitation submitted to the mail server; inbox delivery is not confirmed.',
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
            Invitation::whereKey($invitation->id)->where('status', 'pending')->update(['status' => 'expired']);
            $invitation->refresh();
        }

        if (! $this->currentUser(false) && $invitation->status === 'pending') {
            session(['invitation_token' => $token]);
        }

        return view('invitations.show', [
            'invitation' => $invitation,
            'currentUser' => $this->currentUser(false),
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $invitation = $this->findPendingInvitationByToken($token);
        $existingUser = User::whereRaw('LOWER(email) = ?', [strtolower($invitation->email)])->first();
        if (! $existingUser) {
            return view('invitations.show', [
                'invitation' => $invitation->load(['team', 'coach']),
                'currentUser' => null,
                'showRegistration' => true,
            ]);
        }
        $user = $this->currentUser(false);
        if (! $user) {
            session(['invitation_token' => $token]);

            return redirect()->route('login.form')->with('status', 'Please sign in with the invited email.');
        }
        abort_if($this->invitationEmailMismatch($invitation, $user), 403, 'Please sign in with the invited email.');
        DB::transaction(function () use ($token, $user) {
            $invitation = $this->findPendingInvitationByToken($token, true);
            app(TeamMembership::class)->addPlayer($user, $invitation->team);
            $invitation->update(['user_id' => $user->id, 'status' => 'accepted', 'responded_at' => now()]);
        });

        return redirect()->route('teams.show', $invitation->team)->with('status', 'Invitation accepted!');
    }

    public function registerAndAccept(Request $request, string $token)
    {
        $invitation = $this->findPendingInvitationByToken($token);
        if (User::whereRaw('LOWER(email) = ?', [strtolower($invitation->email)])->exists()) {
            session(['invitation_token' => $token]);

            return redirect()->route('login.form')->with('status', 'An account already exists. Please sign in to accept.');
        }
        abort_if($this->currentUser(false), 403, 'Sign out before registering another account.');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);
        $user = DB::transaction(function () use ($token, $validated) {
            $invitation = $this->findPendingInvitationByToken($token, true);
            if (User::whereRaw('LOWER(email) = ?', [strtolower($invitation->email)])->exists()) {
                throw ValidationException::withMessages(['email' => 'An account already exists. Please sign in.']);
            }
            $user = User::create([
                'name' => $validated['name'], 'email' => strtolower($invitation->email),
                'password' => bcrypt($validated['password']),
            ]);
            app(TeamMembership::class)->addPlayer($user, $invitation->team);
            $invitation->update(['user_id' => $user->id, 'status' => 'accepted', 'responded_at' => now()]);

            return $user;
        });
        session(['user_id' => $user->id]);
        session()->regenerate();
        session()->forget('invitation_token');

        return redirect()->route('teams.show', $invitation->team)->with('status', 'Welcome! You have joined the team.');
    }

    public function reject(Request $request, string $token)
    {
        $user = $this->currentUser();
        $invitation = DB::transaction(function () use ($user, $token) {
            $invitation = $this->findPendingInvitationByToken($token, true);
            abort_if($this->invitationEmailMismatch($invitation, $user), 403, 'Please sign in with the invited email.');
            $invitation->update(['user_id' => $user->id, 'status' => 'rejected', 'responded_at' => now()]);

            return $invitation;
        });

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

    private function findPendingInvitationByToken(string $token, bool $lock = false): Invitation
    {
        $query = Invitation::with('team')->where('token', $token);
        $invitation = ($lock ? $query->lockForUpdate() : $query)->firstOrFail();

        if ($invitation->status !== 'pending') {
            abort(410, 'This invitation has already been processed.');
        }

        if ($invitation->isExpired()) {
            Invitation::whereKey($invitation->id)->where('status', 'pending')->update(['status' => 'expired']);
            abort(410, 'This invitation has expired.');
        }

        return $invitation;
    }

    private function invitationEmailMismatch(Invitation $invitation, User $user): bool
    {
        return strtolower($invitation->email) !== strtolower($user->email);
    }
}
// InvitationController Docblock placeholder
