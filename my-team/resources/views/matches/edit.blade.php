<x-layouts.app title="Edit Match">
    <div class="mx-auto max-w-2xl rounded-3xl border border-black/5 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-black tracking-tight">Edit match</h1>
        <p class="mt-1 text-sm text-slate-600">Team: {{ $match->team->name }}</p>

        <form class="mt-6 space-y-4" method="POST" action="{{ route('matches.update', $match) }}">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-sm font-semibold">Starts at</label>
                <input name="starts_at" type="datetime-local" value="{{ old('starts_at', optional($match->starts_at)->format('Y-m-d\\TH:i')) }}" class="mt-1 w-full rounded-xl border-slate-200" required>
                @error('starts_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold">Opponent (optional)</label>
                    <input name="opponent" value="{{ old('opponent', $match->opponent) }}" class="mt-1 w-full rounded-xl border-slate-200">
                    @error('opponent')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold">Location (optional)</label>
                    <input name="location" value="{{ old('location', $match->location) }}" class="mt-1 w-full rounded-xl border-slate-200">
                    @error('location')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold">Notes (optional)</label>
                <textarea name="notes" rows="4" class="mt-1 w-full rounded-xl border-slate-200">{{ old('notes', $match->notes) }}</textarea>
                @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-between gap-3">
                <a class="rounded-xl border border-slate-200 px-4 py-2 font-bold text-slate-800 hover:bg-slate-50" href="{{ route('matches.show', $match) }}">Cancel</a>
                <button class="rounded-xl bg-[#1d4d2f] px-5 py-2.5 font-bold text-white hover:brightness-110" type="submit">Update</button>
            </div>
        </form>
    </div>
</x-layouts.app>

