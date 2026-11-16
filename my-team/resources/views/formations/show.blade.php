<x-layouts.app title="Formations">

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black tracking-tight">{{ $formation->title }}</h1>
            <p class="text-secondary">Created {{ $formation->created_at->format('M j, Y') }} by {{ $formation->creator->name }}</p>
        </div>
        <a href="{{ route('formations.team', $formation->team) }}" class="rounded-xl border border-outline-variant bg-surface-container px-6 py-3 font-bold text-on-surface hover:bg-surface-container-high">← Team Formations</a>
    </div>

    <div id="pitch-view" class="relative w-full h-[500px] bg-gradient-to-b from-green-400 to-green-600 rounded-2xl border-4 border-white shadow-2xl overflow-hidden">
        <svg viewBox="0 0 600 400" class="w-full h-full">
            <rect fill="none" stroke="white" stroke-width="4" x="50" y="25" width="500" height="350" rx="10"/>
            <circle cx="300" cy="200" r="40" fill="none" stroke="white" stroke-width="3"/>
            <rect fill="#ff4444" x="10" y="125" width="40" height="150" rx="5"/>
            <rect fill="#4488ff" x="550" y="125" width="40" height="150" rx="5"/>
        </svg>
        @foreach(collect($formation->positions)->filter(fn($p) => ($p['status'] ?? ($p['role'] === 'substitute' ? 'substitute' : 'starter')) === 'starter') as $pos)
            @php
                $player = $players->find($pos['player_id']);
            @endphp
            <div class="absolute text-xs bg-surface-container/90 backdrop-blur-sm rounded-lg p-2 shadow-lg border flex items-center gap-1 font-bold"
                 style="left: {{ $pos['x'] }}%; top: {{ $pos['y'] }}%;">
                {{ \Illuminate\Support\Str::limit($player?->name ?? 'Unknown', 6) ?? 'Unknown' }}
                <span class="text-xs bg-slate-200 px-1 rounded">{{ $pos['role'] ?? 'N/A' }}</span>
            </div>
        @endforeach
    </div>

    <section class="fb-panel"><h2>Substitutes Bench</h2>@foreach(collect($formation->positions)->filter(fn($p) => ($p['status'] ?? ($p['role'] === 'substitute' ? 'substitute' : 'starter')) === 'substitute') as $pos)<p>{{ $players->find($pos['player_id'])?->name ?? 'Former player' }} - {{ in_array($pos['role'], ['GK','DEF','MID','FWD']) ? $pos['role'] : 'MID' }}</p>@endforeach</section>@if($isCoach)
        <div class="flex gap-3">
            <a href="{{ route('formations.edit', $formation) }}" class="flex-1 rounded-xl bg-slate-900 px-6 py-3 text-center font-bold text-white hover:brightness-110">Edit</a>
            <form method="POST" action="{{ route('formations.destroy', $formation) }}" class="flex-1" onsubmit="return confirm('Delete?')">
                @csrf @method('DELETE')
                <button class="w-full rounded-xl border border-red-200 bg-red-50 px-6 py-3 font-bold text-red-700 hover:bg-red-100">Delete</button>
            </form>
        </div>
    @endif
</div>
</x-layouts.app>
