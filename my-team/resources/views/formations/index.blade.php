<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device=device-width, initial-scale=1.0" name="viewport" />
    <title>Formations | Tactical Sanctum</title>
    @include('components.assets')


    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #121413;
            color: #e2e3e0;
        }
        h1, h2, h3 { font-family: 'Manrope', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="bg-background text-on-surface antialiased min-h-screen flex flex-col">
    @include('components.navigation')

    <main id="main-content" tabindex="-1" class="flex-grow p-6 md:p-12">
        <div class="max-w-6xl mx-auto">
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <a href="{{ route('dashboard') }}" class="text-primary hover:text-primary/80 text-sm font-label">Dashboard</a>
                        <span class="text-secondary text-sm">/</span>
                        <span class="text-secondary text-sm">Formations</span>
                    </div>
                    <h1 class="text-4xl font-black">Formations</h1>
                </div>
            </div>

            @if($formations->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($formations as $formation)
                        <a class="block bg-surface-container-high p-6 rounded-xl border border-outline-variant/10 hover:shadow-lg transition-all cursor-pointer" href="{{ route('formations.show', $formation) }}">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-bold">{{ $formation->title }}</h3>
                                    <p class="text-xs text-secondary">{{ $formation->team->name }}</p>
                                </div>
                                <span class="material-symbols-outlined text-primary text-2xl">stadium</span>
                            </div>
                            <p class="text-xs text-secondary mb-4">{{ $formation->created_at->format('M d, Y') }}</p>
                            <p class="text-xs text-on-secondary-fixed">{{ count($formation->positions ?? []) }} Position{{ count($formation->positions ?? []) !== 1 ? 's' : '' }}</p>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="bg-surface-container-high p-12 rounded-xl border border-outline-variant/10 text-center">
                    <span class="material-symbols-outlined text-4xl text-secondary mb-4 block">stadium</span>
                    <p class="text-secondary mb-4">No formations yet</p>
                    @if($coachTeams->count() > 0)
                        <p class="text-xs text-secondary mb-6">Create your first formation to get started</p>
                        <a href="{{ route('formations.create', $coachTeams->first()) }}" class="bg-primary text-on-primary px-6 py-2 rounded-lg font-bold hover:bg-primary/90 transition-all inline-block">
                            Create Formation
                        </a>
                    @else
                        <p class="text-xs text-secondary">Create a team first to start building formations</p>
                    @endif
                </div>
            @endif
        </div>
    </main>
</body>
</html>
