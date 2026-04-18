<x-layouts.app title="Chat">
    <div class="flex h-[calc(100vh-120px)] overflow-hidden bg-background">
        <!-- Sidebar: Participants -->
        <div class="w-64 border-r border-outline-variant/10 bg-surface-container-low p-4 hidden md:block">
            <h3 class="text-xs font-bold uppercase tracking-widest text-primary mb-4">Participants</h3>
            <div class="space-y-2">
                @foreach($conversation->participants as $p)
                    <div class="flex items-center gap-2 p-2 rounded-lg bg-surface-container-highest">
                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                        <span class="text-sm font-medium">{{ $p->name }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Chat Area -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <div class="p-4 border-b border-outline-variant/10 flex justify-between items-center bg-surface-container-low">
                <div class="flex items-center gap-3">
                    <span class="font-bold text-lg">{{ $conversation->subject }}</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-primary/20 text-primary uppercase font-bold">
                        {{ str_replace('_', ' ', $conversation->type) }}
                    </span>
                </div>
                <a href="{{ route('chat.index') }}" class="text-sm text-slate-500 hover:text-primary">Back to list</a>
            </div>

            <!-- Messages -->
            <div id="chat-window" class="flex-1 overflow-y-auto p-6 space-y-4 bg-surface-container-lowest">
                @foreach($messages as $msg)
                    <div class="flex {{ $msg->sender_id === $user->id ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-md p-4 rounded-2xl {{ $msg->sender_id === $user->id ? 'bg-primary text-on-primary rounded-tr-none' : 'bg-surface-container-high text-on-surface rounded-tl-none' }}">
                            <p class="text-xs font-bold opacity-70 mb-1">{{ $msg->sender->name }}</p>
                            <p class="text-sm">{{ $msg->message }}</p>
                            <p class="text-[10px] text-right opacity-50 mt-1">{{ $msg->created_at->format('H:i') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Input Area -->
            <div class="p-4 bg-surface-container-low border-t border-outline-variant/10">
                <form id="chat-form" class="flex gap-4">
                    @csrf
                    <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">
                    <input type="text" name="message" id="message-input" class="flex-1 rounded-xl border-slate-200 bg-surface-container-highest text-sm" placeholder="Type your message...">
                    <button type="submit" class="bg-primary text-on-primary px-6 py-2 rounded-xl font-bold text-sm hover:brightness-110 transition-all">
                        Send
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('chat-form');
        const chatWindow = document.getElementById('chat-window');

        form.onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const response = await fetch("{{ route('chat.messages') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });

            if (response.ok) {
                const data = await response.json();
                const msgDiv = document.createElement('div');
                msgDiv.className = 'flex justify-end';
                msgDiv.innerHTML = `
                    <div class="max-w-md p-4 rounded-2xl bg-primary text-on-primary rounded-tr-none">
                        <p class="text-xs font-bold opacity-70 mb-1">{{ $user->name }}</p>
                        <p class="text-sm">${data.message}</p>
                        <p class="text-[10px] text-right opacity-50 mt-1">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</p>
                    </div>
                `;
                chatWindow.appendChild(msgDiv);
                chatWindow.scrollTop = chatWindow.scrollHeight;
                form.reset();
            }
        };

        // Simple Polling for "Real-time" feel
        setInterval(async () => {
            try {
                const response = await fetch("{{ route('chat.show', $conversation) }}");
                if (response.ok) {
                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newMessages = doc.getElementById('chat-window').innerHTML;
                    if (chatWindow.innerHTML !== newMessages) {
                        chatWindow.innerHTML = newMessages;
                        chatWindow.scrollTop = chatWindow.scrollHeight;
                    }
                }
            } catch (e) {
                console.error('Polling error:', e);
            }
        }, 3000);
    </script>
</x-layouts.app>