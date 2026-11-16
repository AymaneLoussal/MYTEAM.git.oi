<x-layouts.app title="Team Matches">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight">{{ $team->name }}</h1>
            <p class="mt-1 text-sm text-secondary">Match schedule</p>
        </div>
        <div class="flex items-center gap-2">
            <a class="rounded-xl border border-outline-variant bg-surface-container px-4 py-2 font-bold text-on-surface hover:bg-surface-container-high" href="{{ route('matches.index') }}">All matches</a>
            @if ($isCoach)
                <a class="rounded-xl bg-[#1d4d2f] px-4 py-2 font-bold text-white hover:brightness-110" href="{{ route('matches.create', $team) }}">Create match</a>
            @endif
        </div>
    </div>

    <div class="mt-8 rounded-3xl border border-black/5 bg-surface-container p-6 shadow-sm">
        <div class="space-y-3">
            @forelse ($matches as $m)
                <a href="{{ route('matches.show', $m) }}" class="block rounded-2xl border border-slate-100 p-4 hover:bg-surface-container-high">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-extrabold">{{ $m->opponent ? 'vs '.$m->opponent : 'Match' }}</p>
                            <p class="text-sm text-secondary">{{ $m->location ?? 'No location' }}</p>
                        </div>
                        <div class="text-right text-sm text-slate-700">
                            <p class="font-semibold">{{ $m->starts_at?->format('D, M j, Y') }}</p>
                            <p class="text-secondary">{{ $m->starts_at?->format('H:i') }}</p>
                        </div>
                    </div>
                </a>
            @empty
                <p class="text-sm text-secondary">No matches for this team yet.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>

