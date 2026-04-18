<x-layouts.app title="Match">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight">{{ $match->team->name }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $match->opponent ? 'vs '.$match->opponent : 'Match' }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 font-bold text-slate-800 hover:bg-slate-50" href="{{ route('matches.team', $match->team) }}">Team matches</a>
            @if ($isCoach)
                <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 font-bold text-slate-800 hover:bg-slate-50" href="{{ route('matches.edit', $match) }}">Edit</a>
                <a class="rounded-xl bg-[#1d4d2f] px-4 py-2 font-bold text-white hover:brightness-110" href="{{ route('matches.lineup', $match) }}">Lineup</a>
                <form method="POST" action="{{ route('matches.destroy', $match) }}">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-xl bg-slate-900 px-4 py-2 font-bold text-white hover:brightness-110" type="submit">Delete</button>
                </form>
            @endif
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 rounded-3xl border border-black/5 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-extrabold">Details</h2>
            <div class="mt-4 space-y-2 text-sm text-slate-700">
                <p><span class="font-semibold">Date:</span> {{ $match->starts_at?->format('D, M j, Y') }}</p>
                <p><span class="font-semibold">Time:</span> {{ $match->starts_at?->format('H:i') }}</p>
                <p><span class="font-semibold">Location:</span> {{ $match->location ?? 'Not set' }}</p>
            </div>

            <h2 class="mt-8 text-lg font-extrabold">Notes</h2>
            <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $match->notes ?: 'No notes.' }}</p>
        </div>

        <div class="rounded-3xl border border-black/5 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-extrabold">Your Role</h2>
            @php
                $role = $rolesByUserId[$user->id] ?? 'not_selected';
            @endphp
            <p class="mt-2 text-sm text-slate-700">
                @if ($role === 'starter')
                    Starter
                @elseif ($role === 'substitute')
                    Substitute
                @else
                    Not selected
                @endif
            </p>

            <h2 class="mt-8 text-lg font-extrabold">Starters</h2>
            <div class="mt-3 space-y-2 text-sm text-slate-700">
                @forelse ($match->players->filter(fn($p) => $p->pivot->role === 'starter') as $p)
                    <p class="rounded-xl border border-slate-100 px-3 py-2">{{ $p->name }}</p>
                @empty
                    <p class="text-slate-600">No starters set.</p>
                @endforelse
            </div>

            <h2 class="mt-8 text-lg font-extrabold">Substitutes</h2>
            <div class="mt-3 space-y-2 text-sm text-slate-700">
                @forelse ($match->players->filter(fn($p) => $p->pivot->role === 'substitute') as $p)
                    <p class="rounded-xl border border-slate-100 px-3 py-2">{{ $p->name }}</p>
                @empty
                    <p class="text-slate-600">No substitutes set.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>

