<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Team;
use App\Services\TeamChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ChatController extends Controller
{
    private function pageData(Request $request, ?Conversation $selected = null): array
    {
        $user = $request->user();
        $team = $user->coachTeams()->first() ?? $user->playerTeams()->first();
        if ($team) app(TeamChat::class)->ensureGroup($team);
        $conversations = $team ? Conversation::where('team_id', $team->id)
            ->whereHas('participants', fn ($q) => $q->where('users.id', $user->id))
            ->with(['team', 'participants', 'latestMessage.sender'])->get()
            ->filter(fn ($c) => $user->can('view', $c))
            ->sortByDesc(fn ($c) => ($c->type === 'team_group' ? '1' : '0').($c->latestMessage?->created_at ?? $c->created_at)->format('YmdHis')) : collect();
        $contacts = ! $team ? collect() : ($team->coach_id === $user->id ? $team->players()->get() : collect([$team->coach]));
        return compact('user', 'team', 'conversations', 'contacts', 'selected');
    }

    public function index(Request $request)
    {
        return view('chat.index', $this->pageData($request));
    }

    public function show(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $messages = $conversation->messages()->with('sender')->orderByDesc('id')->paginate(50);
        return view('chat.show', $this->pageData($request, $conversation) + compact('conversation', 'messages'));
    }

    public function messages(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $validated = $request->validate(['after' => 'required|integer|min:0']);
        return response()->json($conversation->messages()->with('sender:id,name')->where('id', '>', $validated['after'])->orderBy('id')->limit(100)->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['conversation_id' => 'required|integer', 'message' => 'required|string|max:5000']);
        $conversation = Conversation::findOrFail($validated['conversation_id']);
        $this->authorize('send', $conversation);
        return response()->json($conversation->messages()->create(['sender_id' => $request->user()->id, 'message' => $validated['message']])->load('sender:id,name'), 201);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['coach_player_private'])],
            'team_id' => 'required|integer', 'player_id' => 'required|integer',
        ]);
        $user = $request->user();
        $conversation = DB::transaction(function () use ($validated, $user) {
            $team = Team::whereKey($validated['team_id'])->lockForUpdate()->firstOrFail();
            $playerId = (int) $validated['player_id'];
            abort_unless($team->players()->where('users.id', $playerId)->exists(), 403);
            abort_unless($team->coach_id === $user->id || $playerId === $user->id, 403);
            $chat = Conversation::where('team_id', $team->id)->where('type', 'coach_player_private')
                ->whereHas('participants', fn ($q) => $q->where('users.id', $team->coach_id))
                ->whereHas('participants', fn ($q) => $q->where('users.id', $playerId))
                ->has('participants', '=', 2)->first();
            if (! $chat) {
                $chat = Conversation::create(['type' => 'coach_player_private', 'team_id' => $team->id, 'created_by' => $user->id, 'private_player_id' => $playerId]);
                $chat->participants()->attach([$team->coach_id, $playerId]);
            }
            return $chat;
        });
        return redirect()->route('chat.show', $conversation);
    }
}
