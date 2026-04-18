<x-layouts.app title="Matches">
    <div class="flex items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight">Matches</h1>
            <p class="mt-1 text-sm text-slate-600">All matches for teams you coach or joined.</p>
        </div>
        <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 font-bold text-slate-800 hover:bg-slate-50" href="{{ route('dashboard') }}">Back</a>
    </div>

    <div class="mt-8 rounded-3xl border border-black/5 bg-white p-6 shadow-sm">
        <div class="space-y-3">
            @forelse ($matches as $m)
                <a href="{{ route('matches.show', $m) }}" class="block rounded-2xl border border-slate-100 p-4 hover:bg-slate-50">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-extrabold">{{ $m->team->name }}</p>
                            <p class="text-sm text-slate-600">{{ $m->opponent ? 'vs '.$m->opponent : 'Match' }}{{ $m->location ? ' • '.$m->location : '' }}</p>
                        </div>
                        <div class="text-right text-sm text-slate-700">
                            <p class="font-semibold">{{ $m->starts_at?->format('D, M j, Y') }}</p>
                            <p class="text-slate-500">{{ $m->starts_at?->format('H:i') }}</p>
                        </div>
                    </div>
                </a>
            @empty
                <p class="text-sm text-slate-600">No matches yet.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>

