<x-layouts.app title="Conversations">
    <div class="mx-auto max-w-6xl p-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-black tracking-tight">Messages</h1>
            <button @disabled(!$team) onclick="document.getElementById('chat-modal').classList.remove('hidden')" class="bg-primary text-on-primary px-4 py-2 rounded-xl font-bold text-sm hover:brightness-110 transition-all">
                New Chat
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-1 space-y-4">
                @foreach($conversations as $conv)
                    <a href="{{ route('chat.show', $conv) }}" class="block p-4 rounded-2xl border border-slate-100 bg-white hover:bg-slate-50 transition-colors">
                        <div class="flex justify-between items-center">
                            <span class="font-bold">{{ $conv->subject }}</span>
                            <span class="text-xs text-slate-400">{{ $conv->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-slate-500 truncate">{{ $conv->type }}</p>
                    </a>
                @endforeach
                {{ $conversations->links() }}
            </div>
            <div class="md:col-span-2">
                <div class="rounded-3xl border border-black/5 bg-white p-12 text-center text-slate-500">
                    Select a conversation to start chatting
                </div>
            </div>
        </div>
    </div>

    <!-- New Chat Modal -->
    <div id="chat-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl p-8 w-full max-w-md shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Start New Chat</h2>
                <button onclick="document.getElementById('chat-modal').classList.add('hidden')" class="text-slate-400 hover:text-red-500">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="{{ route('chat.create') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-sm font-semibold mb-2">Conversation Type</label>
                    <select name="type" id="chat-type" onchange="togglePlayerSelect()" class="w-full rounded-xl border-slate-200 text-sm">
                        @if($team?->coach_id === $user->id)<option value="team_group">Team Group Chat</option>@endif
                        <option value="coach_player_private">Private Chat (Coach & Player)</option>
                        @if($team?->coach_id === $user->id)<option value="team_announcement">Team Announcement</option>@endif
                    </select>
                </div>

                <div id="player-select-container" class="hidden">
                    <label class="block text-sm font-semibold mb-2">Select Player</label>
                    <select name="player_id" class="w-full rounded-xl border-slate-200 text-sm">
                        @if($team)
                            @foreach($team->players as $player)
                                @if($team->coach_id === $user->id || $player->id === $user->id)
                                <option value="{{ $player->id }}">{{ $player->name }}</option>
                                @endif
                            @endforeach
                        @endif
                    </select>
                </div>

                <input type="hidden" name="team_id" value="{{ $team?->id }}">

                <div>
                    <label class="block text-sm font-semibold mb-2">Subject (Optional)</label>
                    <input type="text" name="subject" class="w-full rounded-xl border-slate-200 text-sm" placeholder="e.g. Match Strategy">
                </div>

                <button class="w-full py-3 bg-primary text-on-primary font-bold rounded-xl hover:brightness-110 transition-all">
                    Create Conversation
                </button>
            </form>
        </div>
    </div>

    <script>
        function togglePlayerSelect() {
            const type = document.getElementById('chat-type').value;
            const container = document.getElementById('player-select-container');
            container.classList.toggle('hidden', type !== 'coach_player_private');
        }
        togglePlayerSelect();
    </script>
</x-layouts.app>
