<x-layouts.app title="Edit Match">
    <div class="mx-auto max-w-2xl rounded-3xl border border-black/5 bg-surface-container p-8 shadow-sm">
        <h1 class="text-2xl font-black tracking-tight">Edit match</h1>
        <p class="mt-1 text-sm text-secondary">Team: {{ $match->team->name }}</p>

        <form class="mt-6 space-y-4" method="POST" action="{{ route('matches.update', $match) }}">
            @csrf
            @method('PATCH')

            <div>
                <label for="field-starts_at-1" class="block text-sm font-semibold">Starts at</label>
                <input id="field-starts_at-1" name="starts_at" type="datetime-local" value="{{ old('starts_at', optional($match->starts_at)->format('Y-m-d\\TH:i')) }}" class="mt-1 w-full rounded-xl border-outline-variant" required>
                @error('starts_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="field-opponent-2" class="block text-sm font-semibold">Opponent (optional)</label>
                    <input id="field-opponent-2" name="opponent" value="{{ old('opponent', $match->opponent) }}" class="mt-1 w-full rounded-xl border-outline-variant">
                    @error('opponent')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="field-location-3" class="block text-sm font-semibold">Location (optional)</label>
                    <input id="field-location-3" name="location" value="{{ old('location', $match->location) }}" class="mt-1 w-full rounded-xl border-outline-variant">
                    @error('location')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="field-notes-4" class="block text-sm font-semibold">Notes (optional)</label>
                <textarea id="field-notes-4" name="notes" rows="4" class="mt-1 w-full rounded-xl border-outline-variant">{{ old('notes', $match->notes) }}</textarea>
                @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-between gap-3">
                <a class="rounded-xl border border-outline-variant px-4 py-2 font-bold text-on-surface hover:bg-surface-container-high" href="{{ route('matches.show', $match) }}">Cancel</a>
                <button class="rounded-xl bg-[#1d4d2f] px-5 py-2.5 font-bold text-white hover:brightness-110" type="submit">Update</button>
            </div>
        </form>
    </div>
</x-layouts.app>

