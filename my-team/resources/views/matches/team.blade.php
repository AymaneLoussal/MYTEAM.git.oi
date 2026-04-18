<x-layouts.app title="Team Matches">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight">{{ $team->name }}</h1>
            <p class="mt-1 text-sm text-slate-600">Match schedule</p>
        </div>
        <div class="flex items-center gap-2">
            <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 font-bold text-slate-800 hover:bg-slate-50" href="{{ route('matches.index') }}">All matches</a>
            @if ($isCoach)
                <a class="rounded-xl bg-[#1d4d2f] px-4 py-2 font-bold text-white hover:brightness-110" href="{{ route('matches.create', $team) }}">Create match</a>
            @endif
        </div>
    </div>

    <div class="mt-8 rounded-3xl border border-black/5 bg-white p-6 shadow-sm">
        <div class="space-y-3">
            @forelse ($matches as $m)
                <a href="{{ route('matches.show', $m) }}" class="block rounded-2xl border border-slate-100 p-4 hover:bg-slate-50">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-extrabold">{{ $m->opponent ? 'vs '.$m->opponent : 'Match' }}</p>
                            <p class="text-sm text-slate-600">{{ $m->location ?? 'No location' }}</p>
                        </div>
                        <div class="text-right text-sm text-slate-700">
                            <p class="font-semibold">{{ $m->starts_at?->format('D, M j, Y') }}</p>
                            <p class="text-slate-500">{{ $m->starts_at?->format('H:i') }}</p>
                        </div>
                    </div>
                </a>
            @empty
                <p class="text-sm text-slate-600">No matches for this team yet.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>

