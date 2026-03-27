<!DOCTYPE html>

<html class="dark" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Manrope:wght@700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "background": "#121413",
              "tertiary-fixed": "#ffd9e4",
              "on-tertiary-fixed": "#3b0520",
              "tertiary": "#ffb0cc",
              "surface-container-high": "#282a29",
              "on-tertiary": "#561b35",
              "on-secondary": "#24342b",
              "on-error": "#690005",
              "surface-variant": "#333534",
              "surface-container-lowest": "#0d0f0e",
              "inverse-on-surface": "#2f3130",
              "on-primary-fixed-variant": "#23501e",
              "on-tertiary-container": "#ffaac8",
              "surface-dim": "#121413",
              "on-primary": "#0a3909",
              "on-background": "#e2e3e0",
              "on-primary-container": "#9dd090",
              "on-primary-fixed": "#002201",
              "surface": "#121413",
              "on-secondary-container": "#abbdb0",
              "on-surface-variant": "#c2c9bb",
              "on-secondary-fixed-variant": "#3b4a40",
              "error-container": "#93000a",
              "inverse-surface": "#e2e3e0",
              "secondary": "#b9cbbe",
              "tertiary-container": "#7c3a55",
              "secondary-fixed-dim": "#b9cbbe",
              "on-error-container": "#ffdad6",
              "secondary-fixed": "#d5e7d9",
              "primary-fixed-dim": "#a1d494",
              "surface-tint": "#a1d494",
              "primary": "#a1d494",
              "outline": "#8c9387",
              "surface-bright": "#383a38",
              "secondary-container": "#3d4d43",
              "on-secondary-fixed": "#101f16",
              "outline-variant": "#42493e",
              "surface-container": "#1e201f",
              "error": "#ffb4ab",
              "primary-container": "#2d5a27",
              "tertiary-fixed-dim": "#ffb0cc",
              "inverse-primary": "#3b6934",
              "surface-container-highest": "#333534",
              "on-surface": "#e2e3e0",
              "on-tertiary-fixed-variant": "#71314c",
              "primary-fixed": "#bcf0ae",
              "surface-container-low": "#1a1c1b"
            },
            fontFamily: {
              "headline": ["Manrope"],
              "body": ["Inter"],
              "label": ["Inter"]
            },
            borderRadius: {"DEFAULT": "0.75rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
          },
        },
      }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .glass-panel {
            background: rgba(40, 42, 41, 0.7);
            backdrop-filter: blur(24px);
        }
    </style>
</head>
<body class="bg-background text-on-background font-body selection:bg-primary selection:text-on-primary">
<!-- TopAppBar -->
<header class="bg-[#121413]/70 backdrop-blur-xl docked full-width top-0 sticky z-40 shadow-[0_20px_40px_rgba(0,0,0,0.4)] bg-gradient-to-b from-[#1a1c1b] to-transparent">
<nav class="flex justify-between items-center w-full px-8 py-4">
<div class="flex items-center gap-12">
<span class="text-2xl font-black tracking-tighter text-[#a1d494] font-headline">FTMBALL</span>
<div class="hidden md:flex items-center gap-8">
<a class="text-[#a1d494] font-bold text-sm font-label uppercase tracking-wider" href="#">Dashboard</a>
<a class="text-[#b9cbbe] hover:bg-[#282a29] transition-colors duration-200 px-3 py-1 rounded text-sm font-label uppercase tracking-wider" href="#">Squad</a>
<a class="text-[#b9cbbe] hover:bg-[#282a29] transition-colors duration-200 px-3 py-1 rounded text-sm font-label uppercase tracking-wider" href="#">Tactics</a>
<a class="text-[#b9cbbe] hover:bg-[#282a29] transition-colors duration-200 px-3 py-1 rounded text-sm font-label uppercase tracking-wider" href="#">Fixtures</a>
</div>
</div>
<div class="flex items-center gap-6">
<div class="flex items-center gap-4">
<button class="text-[#b9cbbe] hover:bg-[#282a29] p-2 rounded-full transition-all scale-95 active:scale-90">
<span class="material-symbols-outlined">notifications</span>
</button>
<button class="text-[#b9cbbe] hover:bg-[#282a29] p-2 rounded-full transition-all scale-95 active:scale-90">
<span class="material-symbols-outlined">settings</span>
</button>
</div>
<div class="h-10 w-10 rounded-full overflow-hidden border border-outline-variant/20">
<img alt="Coach Profile Avatar" data-alt="professional portrait of a focused football manager in a dark suit with a stadium background at night" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBrbIcMu9jZbyUKAHVK_xRxp_hgHaxvYE2pTgJgcCDQAqzG1eoJrhv__TMDPqOaupwEbyl4BHp-LdnwHP6LV4D7zsGX8se3N31EEEDC33zr4s24wuX4nLdUSrcYMvtLOBhrTWBbegN_cZC7uBWisqf1xYvhXzVIrBgA_D2B7ka0XyXX7Ruuz_RVzy0qckKH8DFmlo6sAcBgE0bHANPMWIh_rfVFDhQTknlzIG2sqIUKqnBEvS7ABJd2s2oB2NaWw9PfFkKAY6EHHrg"/>
</div>
</div>
</nav>
</header>
<main>
<!-- Hero Section -->
<section class="relative min-h-[921px] flex items-center overflow-hidden px-8 md:px-24">
<!-- Background Decorative Gradients -->
<div class="absolute top-[-10%] right-[-10%] w-[60%] h-[80%] bg-primary-container/20 rounded-full blur-[120px] -z-10"></div>
<div class="absolute bottom-[-10%] left-[-5%] w-[40%] h-[60%] bg-surface-container-highest/30 rounded-full blur-[100px] -z-10"></div>
<div class="grid grid-cols-1 lg:grid-cols-12 gap-12 w-full items-center">
<!-- Content Column -->
<div class="lg:col-span-6 z-10">
<div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-secondary-container text-primary text-[0.6875rem] font-bold uppercase tracking-widest mb-6">
<span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">stars</span>
<span>Elite Division Access</span>
</div>
<h1 class="text-6xl md:text-8xl font-headline font-extrabold tracking-tighter text-on-surface mb-8 leading-[0.9]">
                        The Elite <span class="text-primary">Tactical Suite</span> for Your Team
                    </h1>
<p class="text-xl text-secondary max-w-xl mb-12 font-body leading-relaxed opacity-90">
                        Harness data-driven scouting, precision tactics boards, and elite performance analytics in a single, immersive war room environment.
                    </p>
<div class="flex flex-wrap gap-4">
<button class="px-8 py-4 bg-gradient-to-br from-primary to-primary-container text-on-primary font-bold rounded-DEFAULT shadow-xl transition-all scale-95 hover:scale-100 active:scale-90 flex items-center gap-2">
                            Initialize Dashboard
                            <span class="material-symbols-outlined">arrow_forward</span>
</button>
<button class="px-8 py-4 border border-outline-variant/20 text-on-surface font-bold rounded-DEFAULT hover:bg-surface-container-high transition-all flex items-center gap-2">
                            Create Roster
                        </button>
</div>
</div>
<!-- Visual Column -->
<div class="lg:col-span-6 relative">
<div class="relative rounded-2xl overflow-hidden shadow-2xl aspect-[4/5] md:aspect-square group">
<img alt="Professional football coach visual" class="object-cover w-full h-full scale-105 group-hover:scale-110 transition-transform duration-700" data-alt="high-angle shot of a professional football coach intensely analyzing a digital tactical board in a high-tech dark studio" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBZP6f47UQY39oO2XssNmkoFWQdHYydh6zkiC1iFjgacxNLSjr8dsJd28TZHPJy3eZcUHXuJra4XlYQVeutT0C1c9NKJoLYZ8oRFMgRRgYopNBgpqz3xzMs24myTZkxaGDqRrUAr_I6a8TZ3W9KHEVHUBfYB5LXS-2FSFtQMXwSGG8OOnz1ME_9RXtdrzNv6trbSDcEwJOf18X2ZPitQs5fzS4qZwa9SJoNZ_M8Mef4Armqpi6A6Fqn08IVRH6xE8QDboaYIMMaYkE"/>
<!-- Glass Overlay Stats -->
<div class="absolute bottom-6 left-6 right-6 glass-panel p-6 rounded-xl border border-outline-variant/10">
<div class="flex justify-between items-end">
<div>
<p class="text-[0.6875rem] font-bold text-primary uppercase tracking-widest mb-1">Current Win Rate</p>
<h3 class="text-4xl font-headline font-black text-on-surface">84.2%</h3>
</div>
<div class="text-right">
<p class="text-[0.6875rem] font-bold text-secondary uppercase tracking-widest mb-1">Squad Morale</p>
<div class="flex gap-1">
<div class="h-1 w-8 bg-primary rounded-full"></div>
<div class="h-1 w-8 bg-primary rounded-full"></div>
<div class="h-1 w-8 bg-primary rounded-full"></div>
<div class="h-1 w-4 bg-outline-variant rounded-full"></div>
</div>
</div>
</div>
</div>
</div>
<!-- Decorative Elements -->
<div class="absolute -top-6 -right-6 w-32 h-32 bg-primary/20 rounded-full blur-2xl animate-pulse"></div>
</div>
</div>
</section>
<!-- Features Grid -->
<section class="py-24 px-8 md:px-24 bg-surface-container-low">
<div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-8">
<div class="max-w-2xl">
<h2 class="text-4xl md:text-5xl font-headline font-extrabold tracking-tight text-on-surface mb-4">Command every aspect of the pitch</h2>
<p class="text-secondary text-lg">Integrated tools designed for the modern manager who demands surgical precision.</p>
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
<p class="text-secondary leading-relaxed mb-6">Complete player profiles with deep-dive performance history and contract management modules.</p>
<ul class="space-y-3">
<li class="flex items-center gap-3 text-sm text-on-surface-variant">
<span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            Injury tracking &amp; Recovery
                        </li>
<li class="flex items-center gap-3 text-sm text-on-surface-variant">
<span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            Scouting Network
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
<p class="text-secondary leading-relaxed mb-6">Advanced 2D/3D board engine for designing set pieces and dynamic phase transitions.</p>
<ul class="space-y-3">
<li class="flex items-center gap-3 text-sm text-on-surface-variant">
<span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            Dynamic Formation Builder
                        </li>
<li class="flex items-center gap-3 text-sm text-on-surface-variant">
<span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            Opponent Analysis
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
<p class="text-secondary leading-relaxed mb-6">Custom drill libraries and AI-generated session plans tailored to your tactical identity.</p>
<ul class="space-y-3">
<li class="flex items-center gap-3 text-sm text-on-surface-variant">
<span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            Session Scheduling
                        </li>
<li class="flex items-center gap-3 text-sm text-on-surface-variant">
<span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            Intensity Monitoring
                        </li>
</ul>
</div>
</div>
</section>
<!-- Stats Banner (Asymmetric Layout) -->
<section class="py-24 px-8 md:px-24">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
<div class="lg:col-span-4">
<div class="relative w-full aspect-square rounded-2xl overflow-hidden shadow-2xl">
<img alt="Tactics screen" class="w-full h-full object-cover" data-alt="close up of a high-resolution tablet screen displaying complex football tactical movements and heatmaps in a dark room" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAHlJdmCHER8kV84iGGDHBK1ALjUZ2EEwmUloXW2HLtopQwaJemMMAQmAyQCXot0sJoMgNdw6zI9kbBn6LUXSqhuFGX6kUFs9y-LMMY4doopE-lis83dDfPB4QzQCnZARBYqjkjcaGwguzotZVYyyu4RjRSvTrnESw42zl9f22H_FM9YJOSi1st25M0ko-8tVRzl9l_RyaJTM5elaqDKpH_gBMh4r06HxNvmmhQqQgbtGYZBfd4aqjcONidOhDDk5p40ipslaY6vXE"/>
<div class="absolute inset-0 bg-gradient-to-t from-background via-transparent to-transparent"></div>
</div>
</div>
<div class="lg:col-span-8 grid grid-cols-2 md:grid-cols-4 gap-8">
<div class="p-6 bg-surface-container-low rounded-xl">
<h4 class="text-primary text-[0.6875rem] font-bold uppercase tracking-widest mb-2">Clubs</h4>
<p class="text-4xl font-headline font-black text-on-surface">500+</p>
<p class="text-xs text-secondary mt-2">Active pro teams</p>
</div>
<div class="p-6 bg-surface-container-low rounded-xl">
<h4 class="text-primary text-[0.6875rem] font-bold uppercase tracking-widest mb-2">Metrics</h4>
<p class="text-4xl font-headline font-black text-on-surface">2.4k</p>
<p class="text-xs text-secondary mt-2">Data points / sec</p>
</div>
<div class="p-6 bg-surface-container-low rounded-xl">
<h4 class="text-primary text-[0.6875rem] font-bold uppercase tracking-widest mb-2">Accuracy</h4>
<p class="text-4xl font-headline font-black text-on-surface">99%</p>
<p class="text-xs text-secondary mt-2">XG prediction</p>
</div>
<div class="p-6 bg-surface-container-low rounded-xl">
<h4 class="text-primary text-[0.6875rem] font-bold uppercase tracking-widest mb-2">Reports</h4>
<p class="text-4xl font-headline font-black text-on-surface">15M</p>
<p class="text-xs text-secondary mt-2">Scouting entries</p>
</div>
</div>
</div>
</section>
<!-- Call to Action -->
<section class="py-32 px-8 text-center relative overflow-hidden">
<div class="absolute inset-0 bg-primary/5 -z-10 blur-[120px]"></div>
<div class="max-w-4xl mx-auto">
<h2 class="text-5xl md:text-7xl font-headline font-black tracking-tighter text-on-surface mb-8">Ready to command the pitch?</h2>
<p class="text-xl text-secondary mb-12 max-w-2xl mx-auto">Join the world's most innovative coaching staffs and elevate your tactical game to the elite level.</p>
<div class="flex justify-center gap-6">
<button class="px-12 py-5 bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold rounded-DEFAULT shadow-2xl hover:brightness-110 transition-all flex items-center gap-3">
                        Get Started Now
                        <span class="material-symbols-outlined">bolt</span>
</button>
</div>
</div>
</section>
</main>
<!-- Simple Editorial Footer -->
<footer class="py-12 px-8 border-t border-outline-variant/10 bg-surface-container-low">
<div class="flex flex-col md:flex-row justify-between items-center gap-8">
<div class="flex flex-col items-center md:items-start">
<span class="text-xl font-black tracking-tighter text-primary font-headline mb-2">FTMBALL</span>
<p class="text-xs text-secondary uppercase tracking-widest">© 2024 Tactical Sanctum Systems Inc.</p>
</div>
<div class="flex gap-12">
<a class="text-xs font-bold text-on-surface-variant hover:text-primary transition-colors uppercase tracking-widest" href="#">Privacy</a>
<a class="text-xs font-bold text-on-surface-variant hover:text-primary transition-colors uppercase tracking-widest" href="#">Security</a>
<a class="text-xs font-bold text-on-surface-variant hover:text-primary transition-colors uppercase tracking-widest" href="#">Legal</a>
</div>
</div>
</footer>
</body></html>
