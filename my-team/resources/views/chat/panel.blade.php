<div class="flex h-[75vh] min-h-[32rem] overflow-hidden rounded-2xl border border-white/10 bg-surface-container">
    <aside class="{{ $selected ? 'hidden md:flex' : 'flex' }} w-full md:w-80 shrink-0 flex-col border-r border-white/10">
        <div class="flex items-center justify-between p-5"><h1 class="text-2xl font-bold">Messages</h1><button type="button" data-open-dialog="chat-modal" @disabled(!$team || $contacts->isEmpty()) class="rounded-lg bg-primary px-3 py-2 text-sm font-bold text-on-primary disabled:opacity-40">New Chat</button></div>
        <div class="overflow-y-auto px-3">
            @if(!$team)
                <p class="p-4 text-sm text-secondary">Join or create a team to start chatting.</p>
            @else
                <p class="px-3 py-2 text-xs font-bold uppercase text-secondary">Team chat</p>
                @foreach($conversations->where('type', 'team_group') as $conv)
                    @include('chat.row', ['conv' => $conv])
                @endforeach
                <p class="px-3 py-3 text-xs font-bold uppercase text-secondary">Private conversations</p>
                @forelse($conversations->where('type', '!=', 'team_group') as $conv)
                    @include('chat.row', ['conv' => $conv])
                @empty
                    <p class="px-3 text-sm text-secondary">No private conversations yet.</p>
                @endforelse
            @endif
        </div>
    </aside>
    <main class="{{ $selected ? 'flex' : 'hidden md:flex' }} min-w-0 flex-1 flex-col">
        @if($selected)
            <header class="flex items-center gap-3 border-b border-white/10 p-4"><a href="{{ route('chat.index') }}" class="md:hidden" aria-label="Back to conversations">?</a><span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/20 font-bold text-primary">{{ mb_substr($selected->titleFor($user), 0, 1) }}</span><div><h2 class="font-bold">{{ $selected->titleFor($user) }}</h2><p class="text-xs text-secondary">{{ $selected->type === 'team_group' ? $selected->participants->count().' members' : ($selected->type === 'coach_player_private' ? 'Private conversation' : 'Team announcement') }}</p></div></header>
            <div id="chat-window" class="flex-1 space-y-4 overflow-y-auto p-5" aria-live="polite">
                @if($messages->hasMorePages())<a class="text-sm text-primary" href="{{ $messages->nextPageUrl() }}">Earlier messages</a>@endif
                @foreach($messages->getCollection()->reverse() as $msg)
                    <article data-id="{{ $msg->id }}" class="{{ $msg->sender_id === $user->id ? 'ml-auto bg-primary/20' : 'bg-surface-container-high' }} max-w-[85%] rounded-2xl p-3"><p class="text-xs font-bold">{{ $msg->sender->name }} <span class="font-normal text-secondary">{{ $msg->sender_id === $selected->team->coach_id ? 'Coach' : 'Player' }}</span></p><p class="whitespace-pre-wrap break-words">{{ $msg->message }}</p><time class="text-xs text-secondary">{{ $msg->created_at->format('M j, H:i') }}</time></article>
                @endforeach
            </div>
            <p id="chat-error" role="alert" class="px-4 text-sm text-red-400"></p>
            @can('send', $selected)<form id="chat-form" class="flex gap-2 border-t border-white/10 p-4">@csrf<input type="hidden" name="conversation_id" value="{{ $selected->id }}"><label for="message-input" class="sr-only">Message</label><input id="message-input" name="message" maxlength="5000" required class="min-w-0 flex-1 rounded-xl bg-surface-container-high p-3" placeholder="Write a message"><button class="rounded-xl bg-primary px-5 font-bold text-on-primary">Send</button></form>@endcan
        @else
            <div class="m-auto p-8 text-center text-secondary">Select a conversation to start chatting.</div>
        @endif
    </main>
</div>
<dialog id="chat-modal" aria-labelledby="chat-dialog-title" class="product-dialog"><div class="w-full max-w-md rounded-2xl bg-surface-container p-6"><div class="mb-5 flex justify-between"><h2 id="chat-dialog-title" class="text-xl font-bold">New private chat</h2><button type="button" data-close-dialog aria-label="Close">?</button></div>
@if($team)<form action="{{ route('chat.create') }}" method="POST" class="space-y-4">@csrf<input type="hidden" name="type" value="coach_player_private"><input type="hidden" name="team_id" value="{{ $team->id }}"><label for="chat-player" class="block text-sm">Choose a contact</label><select id="chat-player" name="player_id" required class="w-full rounded-xl bg-surface-container-high p-3">@foreach($contacts as $contact)<option value="{{ $team->coach_id === $user->id ? $contact->id : $user->id }}">{{ $contact->name }} — {{ $contact->id === $team->coach_id ? 'Coach' : 'Player' }}</option>@endforeach</select><button class="w-full rounded-xl bg-primary p-3 font-bold text-on-primary">Open chat</button></form>@endif</div></dialog>
