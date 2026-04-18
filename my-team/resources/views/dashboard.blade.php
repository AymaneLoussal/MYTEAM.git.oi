<!DOCTYPE html>

<html class="dark" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device=device-width, initial-scale=1.0" name="viewport" />
    <title>Tactical Sanctum | Dashboard</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&amp;family=Inter:wght@100..900&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "secondary-container": "#3d4d43",
                        "secondary": "#b9cbbe",
                        "on-error": "#690005",
                        "inverse-on-surface": "#2f3130",
                        "surface": "#121413",
                        "on-tertiary-fixed-variant": "#71314c",
                        "surface-container-low": "#1a1c1b",
                        "surface-tint": "#a1d494",
                        "surface-bright": "#383a38",
                        "surface-container-lowest": "#0d0f0e",
                        "on-primary-fixed": "#002201",
                        "background": "#121413",
                        "error-container": "#93000a",
                        "tertiary-container": "#7c3a55",
                        "surface-variant": "#333534",
                        "primary": "#a1d494",
                        "on-secondary-fixed": "#101f16",
                        "tertiary": "#ffb0cc",
                        "surface-container": "#1e201f",
                        "error": "#ffb4ab",
                        "inverse-surface": "#e2e3e0",
                        "on-secondary-fixed-variant": "#3b4a40",
                        "on-background": "#e2e3e0",
                        "tertiary-fixed-dim": "#ffb0cc",
                        "outline": "#8c9387",
                        "on-tertiary-container": "#ffaac8",
                        "on-secondary": "#24342b",
                        "primary-container": "#2d5a27",
                        "on-primary-container": "#9dd090",
                        "on-tertiary": "#561b35",
                        "secondary-fixed": "#d5e7d9",
                        "surface-container-highest": "#333534",
                        "on-secondary-container": "#abbdb0",
                        "surface-container-high": "#282a29",
                        "on-primary-fixed-variant": "#23501e",
                        "on-error-container": "#ffdad6",
                        "on-surface-variant": "#c2c9bb",
                        "primary-fixed": "#bcf0ae",
                        "surface-dim": "#121413",
                        "primary-fixed-dim": "#a1d494",
                        "on-tertiary-fixed": "#3b0520",
                        "secondary-fixed-dim": "#b9cbbe",
                        "on-surface": "#e2e3e0",
                        "outline-variant": "#42493e",
                        "tertiary-fixed": "#ffd9e4",
                        "inverse-primary": "#3b6934",
                        "on-primary": "#0a3909"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.75rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "fontFamily": {
                        "headline": ["Manrope"],
                        "body": ["Inter"],
                        "label": ["Inter"]
                    }
                },
            },
        }
    </script>
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
    <header class="bg-[#121413] dark:bg-[#121413] sticky top-0 z-50">
        <div class="flex justify-between items-center px-8 py-4 w-full max-w-screen-2xl mx-auto">
            <div class="text-2xl font-black tracking-tighter text-[#a1d494]">Tactical Sanctum</div>
            <nav class="hidden md:flex items-center gap-8">
                <a href="{{ route('dashboard') }}" class="font-label text-sm hover:text-primary transition-colors">Dashboard</a>
                <a href="{{ route('formations.index') }}" class="font-label text-sm hover:text-primary transition-colors">Formations</a>
                <a href="{{ route('trainings.index') }}" class="font-label text-sm hover:text-primary transition-colors">Trainings</a>
                <a href="{{ route('matches.index') }}" class="font-label text-sm hover:text-primary transition-colors">Matches</a>
            </nav>
            <div class="flex items-center gap-4">
                <a href="{{ route('profile.show') }}" class="text-[#b9cbbe] hover:text-[#a1d494] transition-colors">
                    <span class="material-symbols-outlined">person</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-[#b9cbbe] hover:text-[#a1d494] transition-colors">
                        <span class="material-symbols-outlined">logout</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="flex-grow flex flex-col md:flex-row relative lg:ml-0">
        <aside class="fixed left-0 top-0 h-full flex flex-col z-40 h-screen w-64 glass-sidebar shadow-[0_20px_40px_rgba(0,0,0,0.4)] hidden lg:flex">
            <div class="p-8 flex flex-col h-full">
                <div class="mb-12">
                    <div class="w-16 h-16 bg-primary-container/30 rounded-2xl flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-primary text-4xl">dashboard</span>
                    </div>
                    <h2 class="text-primary font-headline text-xl font-bold tracking-tight">Dashboard</h2>
                    <p class="text-secondary text-xs font-label">Coach Hub</p>
                </div>
                <nav class="space-y-2 flex-grow">
                    <div class="flex items-center gap-3 bg-[#2d5a27] text-[#a1d494] rounded-xl px-4 py-3">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span class="font-label text-sm">Dashboard</span>
                    </div>
                    <div class="flex items-center gap-3 hover:bg-surface-container-high text-[#b9cbbe] px-4 py-3 rounded-xl transition-all cursor-pointer" onclick="location.href='{{ route('teams.index') }}'">
                        <span class="material-symbols-outlined">groups</span>
                        <span class="font-label text-sm">Teams</span>
                    </div>
                    <div class="flex items-center gap-3 hover:bg-surface-container-high text-[#b9cbbe] px-4 py-3 rounded-xl transition-all cursor-pointer" onclick="location.href='{{ route('formations.index') }}'">
                        <span class="material-symbols-outlined">stadium</span>
                        <span class="font-label text-sm">Formations</span>
                    </div>
                    <div class="flex items-center gap-3 hover:bg-surface-container-high text-[#b9cbbe] px-4 py-3 rounded-xl transition-all cursor-pointer" onclick="location.href='{{ route('trainings.index') }}'">
                        <span class="material-symbols-outlined">fitness_center</span>
                        <span class="font-label text-sm">Trainings</span>
                    </div>
                    <div class="flex items-center gap-3 hover:bg-surface-container-high text-[#b9cbbe] px-4 py-3 rounded-xl transition-all cursor-pointer" onclick="location.href='{{ route('matches.index') }}'">
                        <span class="material-symbols-outlined">sports_soccer</span>
                        <span class="font-label text-sm">Matches</span>
                    </div>
                </nav>
            </div>
        </aside>
        <section class="flex-grow flex items-center justify-center p-6 md:p-12 lg:ml-64 bg-surface-container-low">
            <div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <div class="lg:col-span-4 space-y-6">
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-primary-container/20 text-primary text-[10px] font-bold tracking-widest uppercase">
                        Welcome Coach {{ $user->name }}
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
                        <div class="bg-surface-container-lowest p-6 rounded-xl border hover:shadow-md transition-all cursor-pointer" onclick="location.href='{{ route('teams.index') }}'">
                            <span class="material-symbols-outlined text-primary text-2xl mb-3 block">stadium</span>
                            <h3 class="text-lg font-bold mb-2">My Teams</h3>
                            <p class="text-xs text-secondary">Manage rosters and notes</p>
                        </div>
                        <div class="bg-surface-container-lowest p-6 rounded-xl border hover:shadow-md transition-all cursor-pointer" onclick="location.href='{{ route('formations.index') }}'">
                            <span class="material-symbols-outlined text-primary text-2xl mb-3 block">draw</span>
                            <h3 class="text-lg font-bold mb-2">Tactics</h3>
                            <p class="text-xs text-secondary">Formation builder</p>
                        </div>
                    </div>
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-surface-container-lowest p-6 rounded-xl">
                            <h4 class="text-sm font-bold uppercase tracking-widest text-secondary mb-4">Pending Invites</h4>
                            @forelse ($pendingInvitations as $inv)
                            <div class="flex items-center gap-3 p-3 bg-surface-container-high rounded-lg mb-3">
                                <img class="w-8 h-8 rounded-full" src="{{ $inv->coach->avatar_url ?? asset('images/default-avatar.svg') }}" alt="">
                                <div>
                                    <p class="font-bold text-sm">{{ $inv->team->name }}</p>
                                    <p class="text-xs text-secondary">{{ $inv->coach->name ?? 'Coach' }}</p>
                                </div>
                                <a class="ml-auto text-primary text-xs font-bold" href="{{ route('invitations.show', $inv->token) }}">Review</a>
                            </div>
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