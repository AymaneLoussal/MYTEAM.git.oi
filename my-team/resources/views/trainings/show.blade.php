<x-layouts.app title="Training">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight">{{ $training->title }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $training->team->name }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 font-bold text-slate-800 hover:bg-slate-50" href="{{ route('trainings.team', $training->team) }}">Team schedule</a>
            @if ($isCoach)
                <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 font-bold text-slate-800 hover:bg-slate-50" href="{{ route('trainings.edit', $training) }}">Edit</a>
                <form method="POST" action="{{ route('trainings.destroy', $training) }}">
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
                <p><span class="font-semibold">Date:</span> {{ $training->starts_at?->format('D, M j, Y') }}</p>
                <p><span class="font-semibold">Time:</span> {{ $training->starts_at?->format('H:i') }}@if($training->ends_at) - {{ $training->ends_at->format('H:i') }}@endif</p>
                <p><span class="font-semibold">Location:</span> {{ $training->location ?? 'Not set' }}</p>
            </div>

            <h2 class="mt-8 text-lg font-extrabold">Notes</h2>
            <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $training->notes ?: 'No notes.' }}</p>
        </div>

        <div class="rounded-3xl border border-black/5 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-extrabold">Rules</h2>
            <p class="mt-2 text-sm text-slate-600">Players can view trainings. Only the coach can create, edit, or delete.</p>
        </div>
    </div>
</x-layouts.app>

