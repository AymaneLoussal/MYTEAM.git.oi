<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device=device-width, initial-scale=1.0" name="viewport" />
    <title>Teams | Tactical Sanctum</title>
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
        <div class="max-w-6xl mx-auto">
            <div class="mb-8">
                <a href="{{ route('dashboard') }}" class="text-primary hover:text-primary/80 text-sm flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    Back to Dashboard
                </a>
                <h1 class="text-4xl font-black">My Teams</h1>
                <p class="text-secondary text-sm mt-2">Manage your teams as a coach or player</p>
            </div>

            <!-- Teams as Coach -->
            @if(count($coachTeams) > 0)
                <div class="mb-12">
                    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">shield</span>
                        Teams I Coach
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($coachTeams as $team)
                            <a href="{{ route('teams.show', $team) }}" class="group bg-surface-container-high p-6 rounded-lg border border-outline-variant/10 hover:shadow-lg hover:border-primary/30 transition-all cursor-pointer">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h3 class="text-lg font-bold group-hover:text-primary transition-colors">{{ $team->name }}</h3>
                                        <p class="text-xs text-secondary mt-1">Coach</p>
                                    </div>
                                    <span class="material-symbols-outlined text-primary text-2xl">groups</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="material-symbols-outlined text-xs">person</span>
                                    <span class="text-secondary">{{ $team->players->count() }} Players</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Teams as Player -->
            @if(count($playerTeams) > 0)
                <div class="mb-12">
                    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-secondary">person</span>
                        Teams I Play For
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($playerTeams as $team)
                            <a href="{{ route('teams.show', $team) }}" class="group bg-surface-container-high p-6 rounded-lg border border-outline-variant/10 hover:shadow-lg hover:border-secondary/30 transition-all cursor-pointer">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h3 class="text-lg font-bold group-hover:text-secondary transition-colors">{{ $team->name }}</h3>
                                        <p class="text-xs text-secondary mt-1">Coached by <span class="font-bold">{{ $team->coach->name }}</span></p>
                                    </div>
                                    <span class="material-symbols-outlined text-secondary text-2xl">sports_soccer</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="material-symbols-outlined text-xs">person</span>
                                    <span class="text-secondary">{{ $team->players->count() }} Players</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Empty State -->
            @if(count($coachTeams) === 0 && count($playerTeams) === 0)
                <div class="bg-surface-container-high p-12 rounded-lg border border-outline-variant/10 text-center">
                    <span class="material-symbols-outlined text-4xl text-secondary mb-4 block">group_add</span>
                    <p class="text-secondary mb-6">You haven't created or joined any teams yet.</p>
                    <a href="{{ route('dashboard') }}" class="bg-primary text-on-primary px-6 py-3 rounded-lg font-bold hover:bg-primary/90 transition-all inline-block">
                        Go to Dashboard
                    </a>
                </div>
            @endif
        </div>
    </main>
</body>
</html>

