@props(['title' => 'FTMBALL'])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    @include('components.assets')
</head>
<body class="min-h-screen bg-gradient-to-b from-[#eef5ec] via-white to-[#eef5ec] text-slate-900">
    <header class="sticky top-0 z-40 border-b border-black/5 bg-white/75 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
            <a href="{{ route('dashboard') }}" class="font-black tracking-tight text-[#1d4d2f]">FTMBALL</a>
            <nav class="flex flex-wrap items-center gap-3">
                @if (session('user_id'))
                    <a class="rounded-full px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100" href="{{ route('dashboard') }}">Dashboard</a>
                    <a class="rounded-full px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100" href="{{ route('trainings.index') }}">Trainings</a>
                    <a class="rounded-full px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100" href="{{ route('matches.index') }}">Matches</a>
                    <a href="{{ route('formations.index') }}">Formations</a>
                    <a href="{{ route('chat.index') }}">Chat</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-full bg-[#1d4d2f] px-4 py-2 text-sm font-bold text-white hover:brightness-110">Logout</button>
                    </form>
                @else
                    <a class="rounded-full px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100" href="{{ route('login.form') }}">Login</a>
                    <a class="rounded-full bg-[#1d4d2f] px-4 py-2 text-sm font-bold text-white hover:brightness-110" href="{{ route('register.form') }}">Register</a>
                @endif
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-10">
        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <ul role="alert" class="mb-4 text-red-700">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        @endif
        {{ $slot }}
    </main>
</body>
</html>
