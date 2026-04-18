<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Team;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.custom');
    }

    /**
     * List all conversations the user is part of.
     */
    public function index()
    {
        $user = User::findOrFail(session('user_id'));
        
        $conversations = Conversation::whereHas('participants', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['team', 'participants'])->latest()->get();

        // Get the user's team to allow creating chats easily
        $team = Team::where('coach_id', $user->id)->first() 
                ?? Team::whereHas('players', fn($q) => $q->where('user_id', $user->id))->first();

        return view('chat.index', compact('user', 'conversations', 'team'));
    }

    /**
     * Show a specific conversation and its messages.
     */
    public function show(Conversation $conversation)
    {
        $user = User::findOrFail(session('user_id'));

        // Authorization: Check if user is a participant
        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            abort(403, 'You do not have access to this conversation.');
        }

        $messages = $conversation->messages()->with('sender')->orderBy('created_at', 'asc')->get();

        return view('chat.show', compact('conversation', 'messages', 'user'));
    }

    /**
     * Send a message.
     */
    public function store(Request $request)
    {
        $user = User::findOrFail(session('user_id'));
        $conversationId = $request->conversation_id;

        // Authorization: Check if user is a participant
        $conversation = Conversation::findOrFail($conversationId);
        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => $user->id,
            'message' => $validated['message'],
        ]);

        return response()->json($message);
    }

    /**
     * Create a new conversation based on type.
     */
    public function create(Request $request)
    {
        $user = User::findOrFail(session('user_id'));
        $type = $request->type; // 'team_group', 'coach_player_private', 'team_announcement'
        $teamId = $request->team_id;

        if (!$teamId) abort(400, 'Team ID is required');
        $team = Team::findOrFail($teamId);

        // Only coach can create team-wide chats or announcements
        if ($type === 'team_group' || $type === 'team_announcement') {
            if ($team->coach_id !== $user->id) abort(403);
        }

        $conversation = Conversation::create([
            'type' => $type,
            'team_id' => $teamId,
            'created_by' => $user->id,
            'subject' => $request->subject ?? 'New Conversation',
        ]);

        if ($type === 'team_group' || $type === 'team_announcement') {
            // Add all team members + coach
            $userIds = $team->players()->pluck('users.id')->push($team->coach_id)->unique();
            $conversation->participants()->attach($userIds);
        } elseif ($type === 'coach_player_private') {
            $playerId = $request->player_id;
            if (!$playerId) abort(400, 'Player ID is required');
            
            // Ensure the player is actually in the team
            if (!$team->players()->where('users.id', $playerId)->exists()) {
                abort(403, 'Player is not in this team');
            }

            $conversation->participants()->attach([$user->id, $playerId]);
        }

        return redirect()->route('chat.show', $conversation);
    }
}

// ChatController Docblock placeholder 
