<x-layouts.app :title="isset($formation) ? 'Edit formation' : 'Create formation'">
    <a href="{{ route('formations.team', $team) }}">Back to formations</a>
    <h1 class="text-3xl font-bold my-6">{{ isset($formation) ? 'Edit formation' : 'New formation' }} — {{ $team->name }}</h1>
    @include('components.feedback')
    <form method="POST" action="{{ isset($formation) ? route('formations.update', $formation) : route('formations.store', $team) }}" id="formation-form">
        @csrf
        @isset($formation) @method('PATCH') @endisset
        <label for="formation-title" class="block font-bold">Formation title</label>
        <input id="formation-title" name="title" required maxlength="255" value="{{ old('title', $formation->title ?? '') }}" class="w-full border rounded-lg p-3 mb-6">
        <p class="mb-4">Drag players onto the pitch, or use “Add player” and edit coordinates below. Maximum 11 players.</p>
        <div id="pitch" class="relative h-[400px] rounded-xl border-4 border-white bg-green-700 mb-4" aria-label="Formation pitch">
            <div class="absolute inset-6 border-2 border-white pointer-events-none"></div>
        </div>
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach($players as $player)
                <button type="button" class="formation-player rounded-lg border p-3 bg-white" draggable="true" data-player-id="{{ $player->id }}">Add {{ $player->name }}</button>
            @endforeach
        </div>
        <div id="position-controls" class="space-y-3"></div>
        <p id="formation-error" role="alert" class="text-red-700"></p>
        <input type="hidden" name="positions" id="positions-input">
        <button class="mt-6 rounded-lg bg-emerald-800 text-white px-8 py-3">Save formation</button>
    </form>
    <script>
        const roster = {{ Illuminate\Support\Js::from($players->map(fn($p) => ['id'=>$p->id,'name'=>$p->name])->values()) }};
        const initial = {{ Illuminate\Support\Js::from(old('positions', $formation->positions ?? [])) }};
        let positions = typeof initial === 'string' ? (() => {try {return JSON.parse(initial);} catch {return [];}})() : initial;
        if (!Array.isArray(positions)) positions = [];
        const pitch = document.getElementById('pitch');
        const controls = document.getElementById('position-controls');
        let draggedId = null;
        function render() {
            controls.replaceChildren();
            pitch.querySelectorAll('.marker').forEach(node => node.remove());
            positions.forEach((position, index) => {
                const player = roster.find(player => Number(player.id) === Number(position.player_id));
                const name = player ? player.name : 'Former player (remove before saving)';
                const marker = document.createElement('span');
                marker.className = 'marker absolute bg-white text-slate-900 rounded p-1 text-xs';
                marker.style.left = Math.max(0, Math.min(100, Number(position.x) || 0)) + '%';
                marker.style.top = Math.max(0, Math.min(100, Number(position.y) || 0)) + '%';
                marker.textContent = name.slice(0, 8);
                pitch.append(marker);
                const row = document.createElement('div');
                row.className = 'flex flex-wrap items-center gap-3 border rounded-lg p-3';
                const title = document.createElement('strong');
                title.textContent = name;
                row.append(title);
                ['x','y'].forEach(axis => {
                    const label = document.createElement('label');
                    label.textContent = axis.toUpperCase() + ' % ';
                    const input = document.createElement('input');
                    input.type = 'number'; input.min = 0; input.max = 100; input.step = 0.1; input.required = true;
                    input.value = position[axis]; input.className = 'w-20 border rounded p-2';
                    input.addEventListener('change', () => {position[axis] = Number(input.value); render();});
                    label.append(input); row.append(label);
                });
                const label = document.createElement('label'); label.textContent = 'Role ';
                const select = document.createElement('select');
                ['GK','DEF','MID','FWD','starter','substitute'].forEach(role => {
                    const option = document.createElement('option'); option.value = role; option.textContent = role; select.append(option);
                });
                select.value = position.role || 'MID';
                select.addEventListener('change', () => {position.role = select.value; sync();});
                label.append(select); row.append(label);
                const remove = document.createElement('button'); remove.type = 'button'; remove.textContent = 'Remove';
                remove.addEventListener('click', () => {positions.splice(index, 1); render();});
                row.append(remove); controls.append(row);
            });
            sync();
        }
        function sync() {document.getElementById('positions-input').value = JSON.stringify(positions);}
        function add(id, x=50, y=50) {
            const existing = positions.find(position => Number(position.player_id) === Number(id));
            if (existing) {existing.x=x; existing.y=y;}
            else if (positions.length < 11) positions.push({player_id:Number(id),x,y,role:'MID'});
            else {document.getElementById('formation-error').textContent='Maximum 11 players.'; return;}
            document.getElementById('formation-error').textContent=''; render();
        }
        document.querySelectorAll('.formation-player').forEach(button => {
            button.addEventListener('click', () => add(button.dataset.playerId));
            button.addEventListener('dragstart', event => {
                draggedId=button.dataset.playerId; event.dataTransfer.setData('text/plain', draggedId);
            });
        });
        pitch.addEventListener('dragover', event => event.preventDefault());
        pitch.addEventListener('drop', event => {
            event.preventDefault();
            if (!draggedId) return;
            const rect=pitch.getBoundingClientRect();
            add(draggedId, Math.round((event.clientX-rect.left)/rect.width*1000)/10, Math.round((event.clientY-rect.top)/rect.height*1000)/10);
            draggedId=null;
        });
        render();
    </script>
</x-layouts.app>
