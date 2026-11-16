<!DOCTYPE html>

<html class="dark" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device=device-width, initial-scale=1.0" name="viewport" />
    <title>Tactical Sanctum | Dashboard</title>
    @include('components.assets')



    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #121413;
            color: #e2e3e0;
        }

        h1,
        h2,
        h3 {
            font-family: 'Manrope', sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .glass-sidebar {
            background: rgba(40, 42, 41, 0.7);
            backdrop-filter: blur(24px);
        }

        .primary-gradient {
            background: linear-gradient(135deg, #a1d494 0%, #2d5a27 100%);
        }
    </style>
</head>

<body class="bg-background text-on-surface antialiased min-h-screen flex flex-col">
    <!-- TopNavBar -->
    @include('components.navigation')

    <main id="main-content" tabindex="-1" class="flex-grow flex flex-col md:flex-row relative lg:ml-0">
        @include('components.feedback')

        <section class="flex-grow flex items-center justify-center p-6 md:p-12  bg-surface-container-low">
            <div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <div class="lg:col-span-4 space-y-6">
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-primary-container/20 text-primary text-[10px] font-bold tracking-widest uppercase">
                        Welcome {{ $user->name }}
                    </div>
                    <h1 class="text-4xl lg:text-5xl font-black text-on-surface leading-tight tracking-tighter">
                        <span class="text-primary">Your </span> Dynasty Awaits
                    </h1>
                    <p class="text-secondary leading-relaxed">
                        Quick access to tactical tools and team management. Build your empire.
                    </p>
                </div>
                <div class="lg:col-span-8 bg-surface-container-high p-8 md:p-10 rounded-[2rem] shadow-2xl border border-outline-variant/10">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @if($coachTeams->isEmpty() && $playerTeams->isEmpty())
                            <div class="bg-surface-container-lowest p-6 rounded-xl border hover:shadow-md transition-all cursor-pointer">
                                <span class="material-symbols-outlined text-primary text-2xl mb-3 block">groups</span>
                                <h3 class="text-lg font-bold mb-2">Create Team</h3>
                                <p class="text-xs text-secondary mb-4">Invite players and set foundation</p>
                                <form method="POST" action="{{ route('dashboard.teams.create') }}" class="space-y-3">
                                    @csrf
                                    <div>
                                        <input
                                            name="name"
                                            placeholder="e.g. FC Legacy"
                                            class="w-full bg-surface-container border border-outline-variant/30 rounded-lg p-3 text-sm focus:ring-2 focus:ring-primary focus:border-transparent placeholder-secondary/50 @error('name') border-error @enderror"
                                            required
                                            value="{{ old('name') }}"
                                        >
                                        @error('name')
                                            <p class="text-error text-xs mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <button type="submit" class="w-full bg-primary text-on-primary px-4 py-3 rounded-lg font-bold hover:bg-primary/90 transition-all active:scale-95">
                                        Create Team
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="bg-surface-container-lowest p-6 rounded-xl border border-error/30">
                                <span class="material-symbols-outlined text-error text-2xl mb-3 block">block</span>
                                <h3 class="text-lg font-bold mb-2">Cannot Create Team</h3>
                                <p class="text-xs text-secondary mb-4">
                                    @if($coachTeams->isNotEmpty())
                                        You are already coaching a team. Leave or delete your current team first.
                                    @else
                                        You are already a member of another team. Leave that team first.
                                    @endif
                                </p>
                                <a href="{{ route('teams.index') }}" class="w-full block text-center bg-error/20 text-error px-4 py-3 rounded-lg font-bold hover:bg-error/30 transition-all text-sm">
                                    Manage Teams
                                </a>
                            </div>
                        @endif
                        <a class="block bg-surface-container-lowest p-6 rounded-xl border hover:shadow-md transition-all cursor-pointer" href="{{ route('teams.index') }}">
                            <span class="material-symbols-outlined text-primary text-2xl mb-3 block">stadium</span>
                            <h3 class="text-lg font-bold mb-2">My Teams</h3>
                            <p class="text-xs text-secondary">Manage rosters and notes</p>
                        </a>
                        <a class="block bg-surface-container-lowest p-6 rounded-xl border hover:shadow-md transition-all cursor-pointer" href="{{ route('formations.index') }}">
                            <span class="material-symbols-outlined text-primary text-2xl mb-3 block">draw</span>
                            <h3 class="text-lg font-bold mb-2">Tactics</h3>
                            <p class="text-xs text-secondary">Formation builder</p>
                        </a>
                    </div>
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-surface-container-lowest p-6 rounded-xl">
                            <h4 class="text-sm font-bold uppercase tracking-widest text-secondary mb-4">Pending Invites</h4>
                            @forelse ($pendingInvitations as $inv)
                            <article class="p-4 bg-surface-container-high rounded-xl mb-3 space-y-2">
                                <div class="flex flex-wrap justify-between gap-2"><h5 class="font-bold">{{ $inv->team->name }}</h5><span class="text-primary text-xs font-bold">Pending</span></div>
                                <p class="text-sm text-secondary">Invited by {{ $inv->coach->name ?? 'Team coach' }}</p>
                                <p class="text-sm text-secondary">{{ $inv->expires_at ? 'Expires '.$inv->expires_at->format('M d, Y H:i') : 'No expiration date' }}</p>
                                <p class="text-sm">To review, accept or decline, open the <strong>Review Invitation</strong> link sent to your email. Check spam, or ask your coach to resend it after configuring email delivery.</p>
                            </article>
                            @empty
                            <p class="text-xs text-secondary italic">No invites</p>
                            @endforelse
                        </div>
                        <div class="bg-surface-container-lowest p-6 rounded-xl">
                            <h4 class="text-sm font-bold uppercase tracking-widest text-secondary mb-4">Quick Actions</h4>
                            <div class="space-y-3">
                                <a href="{{ route('trainings.index') }}" class="flex items-center gap-3 p-3 bg-surface-container-high rounded-lg hover:bg-surface-container text-sm">
                                    <span class="material-symbols-outlined">fitness_center</span>
                                    Schedule Training
                                </a>
                                <a href="{{ route('matches.index') }}" class="flex items-center gap-3 p-3 bg-surface-container-high rounded-lg hover:bg-surface-container text-sm">
                                    <span class="material-symbols-outlined">sports_soccer</span>
                                    Plan Match
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
        </section>
        </main>
        <!-- Glow Effects -->
        <div class="fixed bottom-0 right-0 w-1/3 h-1/3 bg-primary-container/10 blur-[120px] rounded-full pointer-events-none -z-10"></div>
        <div class="fixed top-0 left-0 w-1/4 h-1/4 bg-primary/5 blur-[100px] rounded-full pointer-events-none -z-10"></div>
</body>

</html>
