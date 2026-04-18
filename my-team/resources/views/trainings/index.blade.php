<x-layouts.app title="Trainings">
    <div class="flex items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight">Trainings</h1>
            <p class="mt-1 text-sm text-slate-600">All trainings for teams you coach or joined.</p>
        </div>
        <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 font-bold text-slate-800 hover:bg-slate-50" href="{{ route('dashboard') }}">Back</a>
    </div>

    <div class="mt-8 rounded-3xl border border-black/5 bg-white p-6 shadow-sm">
        <div class="space-y-3">
            @forelse ($trainings as $t)
                <a href="{{ route('trainings.show', $t) }}" class="block rounded-2xl border border-slate-100 p-4 hover:bg-slate-50">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-extrabold">{{ $t->title }}</p>
                            <p class="text-sm text-slate-600">{{ $t->team->name }}</p>
                        </div>
                        <div class="text-right text-sm text-slate-700">
                            <p class="font-semibold">{{ $t->starts_at?->format('D, M j, Y') }}</p>
                            <p class="text-slate-500">{{ $t->starts_at?->format('H:i') }}@if($t->ends_at) - {{ $t->ends_at->format('H:i') }}@endif</p>
                        </div>
                    </div>
                </a>
            @empty
                <p class="text-sm text-slate-600">No trainings scheduled yet.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>

