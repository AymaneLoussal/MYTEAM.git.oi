<x-layouts.app title="Match Lineup">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight">Lineup</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $match->team->name }} • {{ $match->starts_at?->format('D, M j, Y H:i') }}</p>
        </div>
        <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 font-bold text-slate-800 hover:bg-slate-50" href="{{ route('matches.show', $match) }}">Back</a>
    </div>

    @error('roles')
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
            {{ $message }}
        </div>
    @enderror

    <form class="mt-8 rounded-3xl border border-black/5 bg-white p-6 shadow-sm" method="POST" action="{{ route('matches.lineup.update', $match) }}">
        @csrf
        @method('PATCH')

        <div class="flex items-center justify-between">
            <h2 class="text-lg font-extrabold">Team Players</h2>
            <div class="text-sm text-slate-600">Max 11 starters</div>
        </div>

        <div class="mt-4 grid gap-3">
            @foreach ($teamPlayers as $p)
                @php
                    $role = old('roles.'.$p->id, $existingRoles[$p->id] ?? 'not_selected');
                @endphp
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-100 p-4">
                    <div class="font-bold">{{ $p->name }}</div>
                    <select name="roles[{{ $p->id }}]" class="w-56 rounded-xl border-slate-200">
                        <option value="not_selected" @selected($role === 'not_selected')>Not selected</option>
                        <option value="starter" @selected($role === 'starter')>Starter</option>
                        <option value="substitute" @selected($role === 'substitute')>Substitute</option>
                    </select>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <button class="rounded-xl bg-[#1d4d2f] px-5 py-2.5 font-bold text-white hover:brightness-110" type="submit">Save lineup</button>
        </div>
    </form>
</x-layouts.app>

