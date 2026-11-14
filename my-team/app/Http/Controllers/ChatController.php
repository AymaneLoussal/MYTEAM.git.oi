<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $conversations = Conversation::whereHas('participants', fn ($q) => $q->where('users.id', $user->id))
            ->whereHas('team', fn ($q) => $q->where('coach_id', $user->id)
                ->orWhereHas('players', fn ($q) => $q->where('users.id', $user->id)))
            ->with(['team', 'participants'])->latest()->paginate(20);
        $team = $user->coachTeams()->first() ?? $user->playerTeams()->first();

        return view('chat.index', compact('user', 'conversations', 'team'));
    }

    public function show(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $user = $request->user();
        $messages = $conversation->messages()->with('sender')->orderByDesc('id')->paginate(50);

        return view('chat.show', compact('conversation', 'messages', 'user'));
    }

    public function messages(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $validated = $request->validate(['after' => 'required|integer|min:0']);

        return response()->json($conversation->messages()->with('sender:id,name')
            ->where('id', '>', $validated['after'])->orderBy('id')->limit(100)->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|integer',
            'message' => 'required|string|max:5000',
        ]);
        $conversation = Conversation::findOrFail($validated['conversation_id']);
        $this->authorize('send', $conversation);
        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        return response()->json($message->load('sender:id,name'), 201);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['team_group', 'coach_player_private', 'team_announcement'])],
            'team_id' => 'required|integer',
            'player_id' => 'required_if:type,coach_player_private|nullable|integer',
            'subject' => 'nullable|string|max:255',
        ]);
        $team = Team::findOrFail($validated['team_id']);
        $user = $request->user();
        abort_unless($team->hasMember($user), 403);
        $isCoach = $team->coach_id === $user->id;

        if ($validated['type'] === 'coach_player_private') {
            $playerId = (int) $validated['player_id'];
            abort_unless($team->players()->where('users.id', $playerId)->exists(), 403);
            abort_unless($isCoach || $playerId === $user->id, 403);
            $participants = [$team->coach_id, $playerId];
        } else {
            abort_unless($isCoach, 403);
            $participants = $team->players()->pluck('users.id')->push($team->coach_id)->unique()->all();
        }

        $conversation = DB::transaction(function () use ($validated, $team, $user, $participants) {
            $conversation = Conversation::create([
                'type' => $validated['type'], 'team_id' => $team->id,
                'created_by' => $user->id, 'subject' => $validated['subject'] ?? 'New Conversation',
            ]);
            $conversation->participants()->attach($participants);

            return $conversation;
        });

        return redirect()->route('chat.show', $conversation);
    }
}
