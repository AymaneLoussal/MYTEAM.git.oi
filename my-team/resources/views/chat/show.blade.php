<x-layouts.app title="Chat">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">{{ $conversation->subject }}</h1>
        <a href="{{ route('chat.index') }}">Back to conversations</a>
    </div>
    <p class="mb-4 text-sm">Participants: {{ $conversation->participants->pluck('name')->join(', ') }}</p>
    {{ $messages->links() }}
    <div id="chat-window" class="space-y-4 my-6 max-h-[60vh] overflow-y-auto" aria-live="polite">
        @foreach($messages->getCollection()->reverse() as $msg)
            <article data-id="{{ $msg->id }}" class="rounded-xl border p-4 bg-white">
                <p class="font-bold">{{ $msg->sender->name }}</p>
                <p class="whitespace-pre-wrap break-words">{{ $msg->message }}</p>
                <time class="text-xs text-slate-500">{{ $msg->created_at->format('H:i') }}</time>
            </article>
        @endforeach
    </div>
    <p id="chat-error" role="alert" class="text-red-700"></p>
    @can('send', $conversation)
        <form id="chat-form" class="flex gap-4">
            @csrf
            <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">
            <label for="message-input" class="sr-only">Message</label>
            <input type="text" name="message" id="message-input" maxlength="5000" required class="flex-1 rounded-xl border p-3" placeholder="Type your message">
            <button class="bg-emerald-800 text-white px-6 rounded-xl">Send</button>
        </form>
    @endcan
    <script>
        const chatWindow = document.getElementById('chat-window');
        const form = document.getElementById('chat-form');
        const error = document.getElementById('chat-error');
        let lastId = {{ $messages->getCollection()->max('id') ?? 0 }};
        const newestPage = {{ $messages->currentPage() === 1 ? 'true' : 'false' }};
        function appendMessage(data) {
            if (chatWindow.querySelector('[data-id="' + Number(data.id) + '"]')) return;
            const article = document.createElement('article');
            article.dataset.id = data.id;
            article.className = 'rounded-xl border p-4 bg-white';
            const author = document.createElement('p');
            author.className = 'font-bold';
            author.textContent = data.sender.name;
            const text = document.createElement('p');
            text.className = 'whitespace-pre-wrap break-words';
            text.textContent = data.message;
            article.append(author, text);
            chatWindow.appendChild(article);
            chatWindow.scrollTop = chatWindow.scrollHeight;
        }
        if (form) form.addEventListener('submit', async event => {
            event.preventDefault();
            const button = form.querySelector('button');
            button.disabled = true;
            try {
                const response = await fetch({{ Illuminate\Support\Js::from(route('chat.messages')) }}, {
                    method: 'POST', headers: {'Accept': 'application/json'}, body: new FormData(form)
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Unable to send message.');
                if (newestPage) appendMessage(data);
                else location.href = {{ Illuminate\Support\Js::from(route('chat.show', $conversation)) }};
                form.elements.message.value = '';
                error.textContent = '';
            } catch (failure) { error.textContent = failure.message; }
            finally { button.disabled = false; }
        });
        async function poll() {
            try {
                const response = await fetch({{ Illuminate\Support\Js::from(route('chat.poll', $conversation)) }} + '?after=' + lastId, {headers: {'Accept':'application/json'}});
                if ([401,403].includes(response.status)) {
                    error.textContent = 'You no longer have access to this conversation.';
                    if (form) form.hidden = true;
                    return;
                }
                if (!response.ok) throw new Error('Unable to refresh messages.');
                const messages = await response.json();
                messages.forEach(data => { appendMessage(data); lastId = Math.max(lastId, data.id); });
                error.textContent = '';
            } catch (failure) { error.textContent = failure.message; }
            setTimeout(poll, 3000);
        }
        if (newestPage) setTimeout(poll, 3000);
    </script>
</x-layouts.app>
