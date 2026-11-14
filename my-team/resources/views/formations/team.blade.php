<x-layouts.app title="Formations">

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-black tracking-tight">{{ $team->name }} Formations</h1>
        @if($isCoach)
            <a class="rounded-xl bg-[#1d4d2f] px-6 py-3 font-bold text-white hover:brightness-110" href="{{ route('formations.create', $team) }}">New Formation</a>
        @endif
    </div>

    @if($formations->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-200 p-12 text-center">
            <p class="text-lg font-bold text-slate-500">No formations yet.</p>
            @if($isCoach)
                <p class="mt-2 text-sm text-slate-500">Create your first tactical setup!</p>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($formations as $formation)
                <div class="group rounded-2xl border border-slate-200 bg-white p-6 hover:shadow-lg transition-all">
                    <h3 class="font-bold text-lg mb-2">{{ $formation->title }}</h3>
                    <p class="text-sm text-slate-500 mb-4">{{ $formation->created_at->format('M j, Y') }}</p>
                    <div class="flex gap-2">
                        <a href="{{ route('formations.show', $formation) }}" class="flex-1 rounded-xl bg-slate-900 px-4 py-2 text-center text-sm font-bold text-white hover:brightness-110">View</a>
                        @if($isCoach)
                            <a href="{{ route('formations.edit', $formation) }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-800 hover:bg-slate-50">Edit</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <a class="rounded-xl border border-slate-200 bg-white px-6 py-3 font-bold text-slate-800 hover:bg-slate-50 inline-block" href="{{ route('dashboard') }}">← Back</a>
</div>
</x-layouts.app>
