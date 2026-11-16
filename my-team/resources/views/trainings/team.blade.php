<x-layouts.app title="Team Trainings">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight">{{ $team->name }}</h1>
            <p class="mt-1 text-sm text-secondary">Training schedule</p>
        </div>
        <div class="flex items-center gap-2">
            <a class="rounded-xl border border-outline-variant bg-surface-container px-4 py-2 font-bold text-on-surface hover:bg-surface-container-high" href="{{ route('trainings.index') }}">All trainings</a>
            @if ($isCoach)
                <a class="rounded-xl bg-[#1d4d2f] px-4 py-2 font-bold text-white hover:brightness-110" href="{{ route('trainings.create', $team) }}">Schedule</a>
            @endif
        </div>
    </div>

    <div class="mt-8 rounded-3xl border border-black/5 bg-surface-container p-6 shadow-sm">
        <div class="space-y-3">
            @forelse ($trainings as $t)
                <a href="{{ route('trainings.show', $t) }}" class="block rounded-2xl border border-slate-100 p-4 hover:bg-surface-container-high">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-extrabold">{{ $t->title }}</p>
                            <p class="text-sm text-secondary">{{ $t->location ?? 'No location' }}</p>
                        </div>
                        <div class="text-right text-sm text-slate-700">
                            <p class="font-semibold">{{ $t->starts_at?->format('D, M j, Y') }}</p>
                            <p class="text-secondary">{{ $t->starts_at?->format('H:i') }}@if($t->ends_at) - {{ $t->ends_at->format('H:i') }}@endif</p>
                        </div>
                    </div>
                </a>
            @empty
                <p class="text-sm text-secondary">No trainings for this team yet.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>

