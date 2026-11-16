<x-layouts.app title="Matches">
    <div class="flex items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight">Matches</h1>
            <p class="mt-1 text-sm text-secondary">All matches for teams you coach or joined.</p>
        </div>
        <a class="rounded-xl border border-outline-variant bg-surface-container px-4 py-2 font-bold text-on-surface hover:bg-surface-container-high" href="{{ route('dashboard') }}">Back</a>
    </div>

    <div class="mt-8 rounded-3xl border border-black/5 bg-surface-container p-6 shadow-sm">
        <div class="space-y-3">
            @forelse ($matches as $m)
                <a href="{{ route('matches.show', $m) }}" class="block rounded-2xl border border-slate-100 p-4 hover:bg-surface-container-high">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-extrabold">{{ $m->team->name }}</p>
                            <p class="text-sm text-secondary">{{ $m->opponent ? 'vs '.$m->opponent : 'Match' }}{{ $m->location ? ' • '.$m->location : '' }}</p>
                        </div>
                        <div class="text-right text-sm text-slate-700">
                            <p class="font-semibold">{{ $m->starts_at?->format('D, M j, Y') }}</p>
                            <p class="text-secondary">{{ $m->starts_at?->format('H:i') }}</p>
                        </div>
                    </div>
                </a>
            @empty
                <p class="text-sm text-secondary">No matches yet.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>

