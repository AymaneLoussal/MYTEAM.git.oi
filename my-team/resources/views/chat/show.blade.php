<x-layouts.app title="Messages"><div class="mx-auto max-w-6xl p-3 md:p-6">@include('chat.panel')</div>
<script>
const chatWindow = document.getElementById('chat-window'), form = document.getElementById('chat-form'), error = document.getElementById('chat-error');
let lastId = {{ $messages->getCollection()->max('id') ?? 0 }};
const newestPage = {{ $messages->currentPage() === 1 ? 'true' : 'false' }};
chatWindow.scrollTop = chatWindow.scrollHeight;
function appendMessage(data) {
    if (chatWindow.querySelector('[data-id="' + Number(data.id) + '"]')) return;
    const article = document.createElement('article'); article.dataset.id = data.id;
    article.className = (data.sender_id === {{ $user->id }} ? 'ml-auto bg-primary/20' : 'bg-surface-container-high') + ' max-w-[85%] rounded-2xl p-3';
    const author = document.createElement('p'); author.className = 'text-xs font-bold'; author.textContent = data.sender.name + (data.sender_id === {{ $selected->team->coach_id }} ? ' · Coach' : ' · Player');
    const content = document.createElement('p'); content.className = 'whitespace-pre-wrap break-words'; content.textContent = data.message;
    const time = document.createElement('time'); time.className = 'text-xs text-secondary'; time.textContent = new Date(data.created_at).toLocaleString();
    article.append(author, content, time); chatWindow.appendChild(article); chatWindow.scrollTop = chatWindow.scrollHeight;
}
if (form) form.addEventListener('submit', async event => {
    event.preventDefault(); const button = form.querySelector('button'); button.disabled = true; button.textContent = 'Sending…';
    try { const response = await fetch({{ Illuminate\Support\Js::from(route('chat.messages')) }}, {method:'POST', headers:{Accept:'application/json'}, body:new FormData(form)}); const data = await response.json(); if (!response.ok) throw new Error(data.message || 'Unable to send message.'); if (newestPage) appendMessage(data); else location.href = {{ Illuminate\Support\Js::from(route('chat.show', $selected)) }}; form.elements.message.value = ''; error.textContent = ''; }
    catch (failure) { error.textContent = failure.message; } finally { button.disabled = false; button.textContent = 'Send'; }
});
async function poll() { try { const response = await fetch({{ Illuminate\Support\Js::from(route('chat.poll', $selected)) }} + '?after=' + lastId, {headers:{Accept:'application/json'}}); if ([401,403].includes(response.status)) { error.textContent = 'You no longer have access to this conversation.'; if (form) form.hidden = true; return; } if (!response.ok) throw new Error('Unable to refresh messages.'); (await response.json()).forEach(data => { appendMessage(data); lastId = Math.max(lastId, data.id); }); error.textContent = ''; } catch (failure) { error.textContent = failure.message; } setTimeout(poll, 3000); }
if (newestPage) setTimeout(poll, 3000);
</script></x-layouts.app>
