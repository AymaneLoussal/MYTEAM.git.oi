@extends('components.layouts.app')

@section('title', $team->name . ' - New Formation')

<x-header title="New Tactical Formation" />

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('formations.team', $team) }}" class="text-slate-500 hover:text-slate-700">
            ← Back to formations
        </a>
        <h1 class="text-3xl font-black tracking-tight flex-1">{{ $team->name }}</h1>
    </div>

    <form method="POST" action="{{ route('formations.store', $team) }}" class="space-y-6">
        @csrf
        <div>
            <label class="block text-sm font-bold mb-2">Formation Title</label>
            <input name="title" type="text" class="w-full rounded-xl border-slate-200 p-4 text-lg" placeholder="4-3-3 Attack" required>
        </div>

        <div>
            <label class="block text-sm font-bold mb-4">Pitch Setup (Drag players to positions)</label>
            <div id="pitch" class="relative w-full h-[500px] bg-gradient-to-b from-green-400 to-green-600 rounded-2xl border-4 border-white shadow-2xl overflow-hidden">
                <!-- Pitch markings SVG -->
                <svg viewBox="0 0 600 400" class="w-full h-full">
                    <rect fill="none" stroke="white" stroke-width="4" x="50" y="25" width="500" height="350" rx="10"/>
                    <!-- Center circle -->
                    <circle cx="300" cy="200" r="40" fill="none" stroke="white" stroke-width="3"/>
                    <!-- Goals -->
                    <rect fill="#ff4444" x="10" y="125" width="40" height="150" rx="5"/>
                    <rect fill="#4488ff" x="550" y="125" width="40" height="150" rx="5"/>
                </svg>
            </div>
            <p class="mt-4 text-sm text-slate-600">Drag players below to the pitch. Save positions.</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 max-h-48 overflow-y-auto p-4 border rounded-xl bg-slate-50">
            @foreach($players as $player)
                <div class="group formation-player bg-white rounded-xl p-4 border shadow-sm hover:shadow-md cursor-grab active:cursor-grabbing transition-all" draggable="true" data-player-id="{{ $player->id }}" data-name="{{ $player->name }}" data-role="">
                    <img src="{{ $player->avatar_url }}" alt="{{ $player->name }}" class="w-12 h-12 rounded-full mx-auto mb-2 object-cover">
                    <div class="text-center">
                        <div class="font-bold text-sm">{{ $player->name }}</div>
                        <div class="text-xs text-slate-500">Select role</div>
                    </div>
                </div>
            @endforeach
        </div>

        <input type="hidden" name="positions" id="positions-input" value="[]">
        <button type="submit" class="w-full rounded-xl bg-[#1d4d2f] px-8 py-4 text-xl font-bold text-white hover:brightness-110">Save Formation</button>
    </form>
</div>

<script>
const players = document.querySelectorAll('.formation-player');
const pitch = document.getElementById('pitch');
let draggedPlayer = null;
let positions = [];

players.forEach(player => {
    player.addEventListener('dragstart', (e) => {
        draggedPlayer = player;
        e.dataTransfer.effectAllowed = 'move';
    });
});

pitch.addEventListener('dragover', (e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
});

pitch.addEventListener('drop', (e) => {
    e.preventDefault();
    if (draggedPlayer) {
        const rect = pitch.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width * 100).toFixed(1);
        const y = ((e.clientY - rect.top) / rect.height * 100).toFixed(1);
        
        const pos = {
            player_id: draggedPlayer.dataset.playerId,
            x: parseFloat(x),
            y: parseFloat(y),
            role: 'starter' // TODO: select
        };
        
        // Update or add
        const index = positions.findIndex(p => p.player_id == pos.player_id);
        if (index > -1) {
            positions[index] = pos;
        } else {
            positions.push(pos);
        }
        
        document.getElementById('positions-input').value = JSON.stringify(positions);
        
        // Visual feedback: clone to pitch
        let marker = document.getElementById(`marker-${pos.player_id}`);
        if (marker) marker.remove();
        marker = document.createElement('div');
        marker.id = `marker-${pos.player_id}`;
        marker.className = 'absolute text-xs bg-white rounded-full p-1 shadow-lg flex items-center gap-1 cursor-pointer';
        marker.style.left = x + '%';
        marker.style.top = y + '%';
        marker.innerHTML = `<span>${draggedPlayer.dataset.name.slice(0,8)}</span>`;
        pitch.appendChild(marker);
        
        draggedPlayer = null;
    }
});
</script>

<style>
#pitch svg { pointer-events: none; }
</style>

