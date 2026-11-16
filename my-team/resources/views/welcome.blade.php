<x-layouts.public>
<!-- TopAppBar -->

<main id="main-content" tabindex="-1">
<!-- Hero Section -->
<section id="hero" class="welcome-hero">
<!-- Background Decorative Gradients -->
<div class="absolute top-[-10%] right-[-10%] w-[60%] h-[80%] bg-primary-container/20 rounded-full blur-[120px] -z-10"></div>
<div class="absolute bottom-[-10%] left-[-5%] w-[40%] h-[60%] bg-surface-container-highest/30 rounded-full blur-[100px] -z-10"></div>
<div class="grid grid-cols-1 lg:grid-cols-12 gap-12 w-full items-center">
<!-- Content Column -->
<div class="lg:col-span-6 z-10">
<div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-secondary-container text-primary text-[0.6875rem] font-bold uppercase tracking-widest mb-6">
<span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">stars</span>
<span>YOUR FOOTBALL TEAM, ORGANIZED</span>
</div>
<h1 class="welcome-headline">
                        Less admin.<br><span class="text-primary">More football.</span>
                    </h1>
<p class="text-xl text-secondary max-w-xl mb-12 font-body leading-relaxed opacity-90">
                        Bring your squad, training sessions, fixtures and tactical formations together in one focused team workspace.
                    </p>
<div class="flex flex-wrap gap-4">
<a href="{{ session('user_id') ? route('dashboard') : route('register.form') }}"  class="product-cta">
                            {{ session('user_id') ? 'Open dashboard' : 'Get started' }}
                            <span class="material-symbols-outlined">arrow_forward</span>
</a>
<a href="#features"  class="px-8 py-4 border border-outline-variant/20 text-on-surface font-bold rounded-DEFAULT hover:bg-surface-container-high transition-all flex items-center gap-2">
                            Explore features
                        </a>
</div>
</div>
<!-- Static preview of supported workflows; no private application data. -->
<figure class="lg:col-span-6 welcome-preview"><figcaption>FTMBALL workspace <span>Illustrative preview &middot; demo data</span></figcaption><div class="preview-content"><p class="product-badge">TEAM OVERVIEW</p><h2>Match week, organized.</h2><p>Demo FC &middot; Coach workspace</p><div class="preview-row"><span>Training session</span><strong>Tuesday &middot; 18:00</strong><small>Passing and team shape &middot; Main pitch</small></div><div class="preview-row"><span>Next match</span><strong>Demo FC vs Example United</strong><small>Saturday &middot; 15:00 &middot; Home</small></div><div class="preview-modules"><span>Roster &amp; invitations</span><span>Lineups &amp; formations</span><span>Team conversations</span></div></div></figure>
</div></section>
<!-- Features Grid -->
<section id="features" class="py-24 px-8 md:px-24 bg-surface-container-low">
<div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-8">
<div class="max-w-2xl">
<h2 class="text-4xl md:text-5xl font-headline font-extrabold tracking-tight text-on-surface mb-4">Everything your team needs to prepare</h2>
<p class="text-secondary text-lg">Keep players, plans and conversations together in one team workspace.</p>
</div>
<div class="flex gap-4">
<div class="h-1 w-12 bg-primary rounded-full"></div>
<div class="h-1 w-4 bg-outline-variant rounded-full"></div>
<div class="h-1 w-4 bg-outline-variant rounded-full"></div>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
<!-- Card 1: Team Management -->
<div class="group bg-surface-container-highest p-8 rounded-DEFAULT hover:translate-y-[-4px] transition-all duration-300 relative overflow-hidden">
<div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
<span class="material-symbols-outlined text-8xl">groups</span>
</div>
<div class="h-12 w-12 rounded-lg bg-primary/10 flex items-center justify-center text-primary mb-8">
<span class="material-symbols-outlined">groups</span>
</div>
<h3 class="text-2xl font-headline font-bold text-on-surface mb-4">Team Management</h3>
<p class="text-secondary leading-relaxed mb-6">Organize your roster, invite players and keep private coaching notes.</p>
<ul class="space-y-3">
<li class="flex items-center gap-3 text-sm text-on-surface-variant">
<span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            Secure player invitations
                        </li>
<li class="flex items-center gap-3 text-sm text-on-surface-variant">
<span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            Team roster and coaching notes
                        </li>
</ul>
</div>
<!-- Card 2: Tactics -->
<div class="group bg-surface-container-highest p-8 rounded-DEFAULT border-t-2 border-primary hover:translate-y-[-4px] transition-all duration-300 relative overflow-hidden">
<div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
<span class="material-symbols-outlined text-8xl">strategy</span>
</div>
<div class="h-12 w-12 rounded-lg bg-primary flex items-center justify-center text-on-primary mb-8 shadow-lg shadow-primary/20">
<span class="material-symbols-outlined">strategy</span>
</div>
<h3 class="text-2xl font-headline font-bold text-on-surface mb-4">Tactics</h3>
<p class="text-secondary leading-relaxed mb-6">Arrange your players on a formation board and prepare lineups for each match.</p>
<ul class="space-y-3">
<li class="flex items-center gap-3 text-sm text-on-surface-variant">
<span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            Formation builder
                        </li>
<li class="flex items-center gap-3 text-sm text-on-surface-variant">
<span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            Match lineups
                        </li>
</ul>
</div>
<!-- Card 3: Training -->
<div class="group bg-surface-container-highest p-8 rounded-DEFAULT hover:translate-y-[-4px] transition-all duration-300 relative overflow-hidden">
<div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
<span class="material-symbols-outlined text-8xl">fitness_center</span>
</div>
<div class="h-12 w-12 rounded-lg bg-primary/10 flex items-center justify-center text-primary mb-8">
<span class="material-symbols-outlined">fitness_center</span>
</div>
<h3 class="text-2xl font-headline font-bold text-on-surface mb-4">Training</h3>
<p class="text-secondary leading-relaxed mb-6">Schedule sessions with start times, locations and notes so your team knows the plan.</p>
<ul class="space-y-3">
<li class="flex items-center gap-3 text-sm text-on-surface-variant">
<span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            Session Scheduling
                        </li>
<li class="flex items-center gap-3 text-sm text-on-surface-variant">
<span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            Session locations and notes
                        </li>
</ul>
</div>
</div>
</section>
<!-- Call to Action -->
<section id="tactics" class="py-32 px-8 text-center relative overflow-hidden">
<div class="absolute inset-0 bg-primary/5 -z-10 blur-[120px]"></div>
<div class="max-w-4xl mx-auto">
<h2 class="text-5xl md:text-7xl font-headline font-black tracking-tighter text-on-surface mb-8">Ready to command the pitch?</h2>
<p class="text-xl text-secondary mb-12 max-w-2xl mx-auto">Create your account, organize your team and prepare for your next match.</p>
<div class="flex justify-center gap-6">
<a href="{{ session('user_id') ? route('dashboard') : route('register.form') }}"  class="px-12 py-5 bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold rounded-DEFAULT shadow-2xl hover:brightness-110 transition-all flex items-center gap-3">
                        {{ session('user_id') ? 'Open dashboard' : 'Get started' }}
                        <span class="material-symbols-outlined">bolt</span>
</a>
</div>
</div>
</section>
<section id="how-it-works" class="product-steps"><p class="product-badge">YOUR NEXT MATCH STARTS HERE</p><h2>One team. A clearer plan.</h2><div><x-card><h3>01 &middot; Build your squad</h3><p>Create your team and invite players through secure email links.</p></x-card><x-card><h3>02 &middot; Plan the week</h3><p>Schedule training, prepare matches and set your lineup.</p></x-card><x-card><h3>03 &middot; Stay connected</h3><p>Share tactics and keep conversations with your team in one place.</p></x-card></div></section></main>
<!-- Simple Editorial Footer -->
<footer class="py-12 px-8 border-t border-outline-variant/10 bg-surface-container-low">
<div class="flex flex-col md:flex-row justify-between items-center gap-8">
<div class="flex flex-col items-center md:items-start">
<span class="text-xl font-black tracking-tighter text-primary font-headline mb-2">FTMBALL</span>
<p class="text-xs text-secondary">&copy; {{ date("Y") }} FTMBALL ? Football team management</p>
</div>
<div class="flex flex-wrap justify-center gap-6">
<a class="text-xs font-bold text-on-surface-variant hover:text-primary transition-colors uppercase tracking-widest" href="#features">Features</a>
<a class="text-xs font-bold text-on-surface-variant hover:text-primary transition-colors uppercase tracking-widest" href="#how-it-works">How it works</a>
<a class="text-xs font-bold text-on-surface-variant hover:text-primary transition-colors uppercase tracking-widest" href="{{ session('user_id') ? route('dashboard') : route('register.form') }}">Get started</a>
</div>
</div>
</footer>
</x-layouts.public>