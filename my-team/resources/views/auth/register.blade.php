<!DOCTYPE html>
<html class="dark" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>FTMBALL | Join Tactical Staff</title>
@include('components.assets')



<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .tactical-gradient {
            background: linear-gradient(135deg, #9ed3aa 0%, #003016 100%);
        }
        .pitch-mesh {
            background-image: radial-gradient(rgba(158, 211, 170, 0.05) 1px, transparent 1px);
            background-size: 32px 32px;
        }
    </style>
</head>
<body class="bg-surface text-on-surface font-body selection:bg-primary/30 antialiased overflow-x-hidden">
<main class="min-h-screen flex items-center justify-center relative p-4 lg:p-0 overflow-hidden">
<!-- Background Decor -->
<div class="absolute inset-0 pitch-mesh pointer-events-none opacity-40"></div>
<div class="absolute -top-24 -right-24 w-96 h-96 bg-primary/10 rounded-full blur-[120px]"></div>
<div class="absolute -bottom-24 -left-24 w-96 h-96 bg-primary-container/20 rounded-full blur-[120px]"></div>
<div class="w-full max-w-5xl grid grid-cols-1 lg:grid-cols-2 bg-surface-container-low rounded-xl shadow-[0px_24px_48px_rgba(0,0,0,0.4)] overflow-hidden relative z-10">
<!-- Left Side: Branding -->
<div class="hidden lg:flex flex-col justify-between p-12 relative overflow-hidden bg-surface-container">
<div class="relative z-10">
<div class="flex items-center gap-2 mb-12">
<span class="material-symbols-outlined text-primary text-3xl" data-icon="sports_soccer">sports_soccer</span>
<span class="font-headline font-black italic tracking-tighter text-2xl text-primary">FTMBALL</span>
</div>
<h1 class="font-headline text-5xl font-extrabold tracking-tight leading-[1.1] mb-6">
Join the <br/>
<span class="text-primary">Technical Staff</span>
</h1>
<p class="text-on-surface-variant max-w-sm font-medium">
Register to access your team's tactical dashboard, formations, and match planning.
</p>
</div>
<div class="relative z-10 space-y-8">
<div class="flex items-center gap-4">
<div class="w-12 h-12 rounded-full bg-surface-container-highest flex items-center justify-center text-primary">
<span class="material-symbols-outlined" data-icon="group_add">group_add</span>
</div>
<div>
<p class="text-sm font-bold text-on-surface">Team Management</p>
<p class="text-xs text-on-surface-variant">Invite and organize players</p>
</div>
</div>
<div class="flex items-center gap-4">
<div class="w-12 h-12 rounded-full bg-surface-container-highest flex items-center justify-center text-primary">
<span class="material-symbols-outlined" data-icon="person_add">person_add</span>
</div>
<div>
<p class="text-sm font-bold text-on-surface">Profile Setup</p>
<p class="text-xs text-on-surface-variant">Avatar and preferences</p>
</div>
</div>
</div>
</div>
<!-- Right Side: Register Form -->
<div class="p-8 lg:p-16 flex flex-col justify-center bg-surface">
<div class="flex lg:hidden items-center gap-2 mb-10">
<span class="material-symbols-outlined text-primary text-2xl" data-icon="sports_soccer">sports_soccer</span>
<span class="font-headline font-black italic tracking-tighter text-xl text-primary">FTMBALL</span>
</div>
<div class="w-full max-w-sm mx-auto">
<header class="mb-10 text-center lg:text-left">
<h2 class="font-headline text-3xl font-bold mb-2">Join Tactical Staff</h2>
<p class="text-on-surface-variant text-sm font-medium">Create your manager profile</p>
</header>
<form method="POST" action="{{ route('register') }}" class="space-y-6">
@csrf
<div class="space-y-2">
<label class="font-label text-xs uppercase tracking-widest text-on-surface-variant font-semibold" for="name">Manager Name</label>
<input name="name" class="w-full bg-surface-container-high border-none rounded-lg pl-12 pr-4 py-4 text-sm focus:ring-0 focus:border-b-2 focus:border-primary transition-all text-on-surface placeholder:text-outline/50" id="name" placeholder="John Coachman" type="text" value="{{ old('name') }}" required>
@if ($errors->has('name'))
<p class="text-[10px] text-error flex items-center gap-1 mt-1">
<span class="material-symbols-outlined text-xs" data-icon="error">error</span>
{{ $errors->first('name') }}
</p>
@endif
</div>
<div class="space-y-2">
<label class="font-label text-xs uppercase tracking-widest text-on-surface-variant font-semibold" for="email">Manager Email</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm" data-icon="alternate_email">alternate_email</span>
<input name="email" class="w-full bg-surface-container-high border-none rounded-lg pl-12 pr-4 py-4 text-sm focus:ring-0 focus:border-b-2 focus:border-primary transition-all text-on-surface placeholder:text-outline/50" id="email" placeholder="coach@ftmball.com" type="email" value="{{ old('email') }}" required>
</div>
@if ($errors->has('email'))
<p class="text-[10px] text-error flex items-center gap-1 mt-1">
<span class="material-symbols-outlined text-xs" data-icon="error">error</span>
{{ $errors->first('email') }}
</p>
@endif
</div>
<div class="space-y-2">
<label class="font-label text-xs uppercase tracking-widest text-on-surface-variant font-semibold" for="password">Create Access Key</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm" data-icon="lock">lock</span>
<input name="password" class="w-full bg-surface-container-high border-none rounded-lg pl-12 pr-12 py-4 text-sm focus:ring-0 focus:border-b-2 focus:border-primary transition-all text-on-surface placeholder:text-outline/50" id="password" placeholder="••••••••" type="password" required minlength="6">
</div>
@if ($errors->has('password'))
<p class="text-[10px] text-error flex items-center gap-1 mt-1">
<span class="material-symbols-outlined text-xs" data-icon="error">error</span>
{{ $errors->first('password') }}
</p>
@endif
</div>
<div class="space-y-2">
<label class="font-label text-xs uppercase tracking-widest text-on-surface-variant font-semibold" for="password_confirmation">Confirm Access Key</label>
<input name="password_confirmation" class="w-full bg-surface-container-high border-none rounded-lg pl-12 pr-4 py-4 text-sm focus:ring-0 focus:border-b-2 focus:border-primary transition-all text-on-surface placeholder:text-outline/50" id="password_confirmation" placeholder="••••••••" type="password" required>
</div>
<button class="w-full tactical-gradient py-4 rounded-xl font-headline font-bold text-background tracking-wide hover:scale-[1.02] active:scale-95 transition-all shadow-lg shadow-primary/10" type="submit">
JOIN TACTICAL STAFF
</button>
</form>
<div class="mt-10 pt-10 border-t border-outline-variant/15">
<p class="text-center text-sm text-on-surface-variant">
Already on the technical staff? 
<a class="text-primary font-bold hover:underline ml-1" href="{{ route('login.form') }}">Sign In</a>
</p>
</div>
</div>
</div>
</div>
<!-- System Footer -->
<footer class="absolute bottom-6 left-0 w-full flex justify-center lg:justify-end lg:pr-12 pointer-events-none">
<div class="flex items-center gap-6 text-[10px] uppercase tracking-[0.2em] font-bold text-on-surface-variant/40">
<span>v2.4.0 Elite Tactical Suite</span>
<span class="hidden lg:block">•</span>
<span class="hidden lg:block">System Status: Optimal</span>
</div>
</footer>
</main>
</body>
</html>

