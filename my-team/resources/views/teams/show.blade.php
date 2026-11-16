<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device=device-width, initial-scale=1.0" name="viewport" />
    <title>{{ $team->name }} | Tactical Sanctum</title>
    @include('components.assets')


    <style>
        body { font-family: 'Inter', sans-serif; background-color: #121413; color: #e2e3e0; }
        h1, h2, h3 { font-family: 'Manrope', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="bg-background text-on-surface antialiased min-h-screen flex flex-col">
    @include('components.navigation')

    <main id="main-content" tabindex="-1" class="flex-grow p-6 md:p-12">
        @include('components.feedback')
        @if($isCoach)
        <section class="max-w-6xl mx-auto mb-6 rounded-xl bg-surface-container p-5">
            <h2 class="text-xl font-bold">Pending invitations</h2>
            <p class="text-sm text-secondary">Pending means not yet accepted. It does not confirm email delivery.</p>
            @foreach($team->invitations()->pending()->get() as $pending)
                <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                    <span>{{ $pending->email }}</span>
                    @if(!$pending->isExpired())
                    <form method="POST" action="{{ route('invitations.resend', $pending) }}">@csrf<button class="rounded-lg bg-primary px-4 py-2 text-on-primary">Resend Invitation</button></form>
                    @else <span>Expired — send a new invitation.</span> @endif
                </div>
            @endforeach
        </section>
        @endif
        <div class="max-w-6xl mx-auto">
            <div class="mb-8">
                <a href="{{ route('teams.index') }}" class="text-primary hover:text-primary/80 text-sm flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    Back to Teams
                </a>
                <div class="flex items-center gap-4 mb-4">
                    <div>
                        <h1 class="text-4xl font-black">{{ $team->name }}</h1>
                        <p class="text-secondary text-sm mt-1">Coached by <span class="text-primary font-bold">{{ $team->coach->name }}</span></p>
                    </div>
                    <div class="bg-primary-container/30 rounded-full px-4 py-2">
                        <span class="text-primary font-bold text-sm">{{ $team->players->count() }} Players</span>
                    </div>
                </div>
            </div>

            <nav aria-label="Team resources" class="flex flex-wrap gap-3 mb-6">
                <a class="product-cta" href="{{ route('matches.team', $team) }}">Team matches</a>
                <a class="product-cta" href="{{ route('trainings.team', $team) }}">Training schedule</a>
                <a class="product-cta" href="{{ route('formations.team', $team) }}">Team formations</a>
            </nav>
            <!-- Coach Controls -->
            @if($isCoach)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <a href="{{ route('formations.create', $team) }}" class="flex items-center gap-3 bg-primary text-on-primary px-6 py-3 rounded-lg font-bold hover:bg-primary/90 transition-all">
                        <span class="material-symbols-outlined">draw</span>
                        Create Formation
                    </a>
                    <a href="{{ route('trainings.create', $team) }}" class="flex items-center gap-3 bg-surface-container-high px-6 py-3 rounded-lg font-bold hover:bg-surface-container text-[#b9cbbe] transition-all border border-outline-variant/20">
                        <span class="material-symbols-outlined">fitness_center</span>
                        Schedule Training
                    </a>
                    <a href="{{ route('matches.create', $team) }}" class="flex items-center gap-3 bg-surface-container-high px-6 py-3 rounded-lg font-bold hover:bg-surface-container text-[#b9cbbe] transition-all border border-outline-variant/20">
                        <span class="material-symbols-outlined">sports_soccer</span>
                        Plan Match
                    </a>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Players List -->
                <div class="lg:col-span-2">
                    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined">groups</span>
                        Team Members
                    </h2>
                    @if($team->players->count() > 0)
                        <div class="space-y-3">
                            @foreach($team->players as $player)
                                <div class="bg-surface-container-high p-4 rounded-lg border border-outline-variant/10 hover:shadow-lg transition-all">
                                    <div class="flex items-center gap-4 justify-between">
                                        <div class="flex items-center gap-4 flex-1">
                                            <img src="{{ $player->avatar_url ?? asset('images/default-avatar.svg') }}" alt="{{ $player->name }}" class="w-12 h-12 rounded-full object-cover border-2 border-primary/30">
                                            <div>
                                                <h3 class="font-bold">{{ $player->name }}</h3>
                                                <p class="text-xs text-secondary">{{ $player->email }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    @if($isCoach)
                                        <form method="POST" action="{{ route('teams.update-notes', [$team, $player]) }}" class="mt-4 space-y-2">
                                            @csrf @method('PATCH')
                                            <textarea 
                                                name="notes" 
                                                rows="2" 
                                                class="w-full bg-surface-container border border-outline-variant/30 rounded-lg p-3 text-xs focus:ring-2 focus:ring-primary focus:border-transparent @error('notes') border-error @enderror"
                                                placeholder="Add coach notes..."
                                            >{{ old('notes', $player->pivot->notes ?? '') }}</textarea>
                                            @error('notes')
                                                <p class="text-error text-xs">{{ $message }}</p>
                                            @enderror
                                            <button type="submit" class="w-full bg-primary/20 text-primary px-3 py-2 rounded-lg text-xs font-bold hover:bg-primary/30 transition-all">
                                                Save Notes
                                            </button>
                                        </form>
                                    @elseif($player->pivot->notes && $isCoach)
                                        <p class="text-xs text-secondary mt-3 italic p-3 bg-surface-container-low rounded-lg">{{ $player->pivot->notes }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-surface-container-high p-8 rounded-lg border border-outline-variant/10 text-center">
                            <span class="material-symbols-outlined text-4xl text-secondary mb-4 block">person_add</span>
                            <p class="text-secondary">No players in team yet</p>
                        </div>
                    @endif
                </div>

                <!-- Invite Section -->
                @if($isCoach)
                    <div class="lg:col-span-1">
                        <div class="bg-surface-container-high p-6 rounded-lg border border-outline-variant/10 sticky top-24">
                            <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined">mail</span>
                                Invite Players
                            </h3>
                            <form method="POST" action="{{ route('dashboard.teams.invite', $team) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <input 
                                        type="email" 
                                        name="email" 
                                        placeholder="player@example.com" 
                                        class="w-full bg-surface-container border border-outline-variant/30 rounded-lg p-3 text-sm focus:ring-2 focus:ring-primary focus:border-transparent @error('email') border-error @enderror"
                                        required
                                    >
                                    @error('email')
                                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit" class="w-full bg-primary text-on-primary px-4 py-3 rounded-lg font-bold hover:bg-primary/90 transition-all text-sm">
                                    Send Invite
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="lg:col-span-1">
                        <div class="bg-surface-container-high p-6 rounded-lg border border-outline-variant/10 sticky top-24">
                            <h3 class="font-bold text-lg mb-4">Team Actions</h3>
                            <form method="POST" action="{{ route('teams.leave', $team) }}" onsubmit="return confirm('Are you sure you want to leave this team?');">
                                @csrf
                                <button type="submit" class="w-full bg-error/20 text-error px-4 py-3 rounded-lg font-bold hover:bg-error/30 transition-all text-sm flex items-center gap-2 justify-center">
                                    <span class="material-symbols-outlined">exit_to_app</span>
                                    Leave Team
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </main>
</body>
</html>

