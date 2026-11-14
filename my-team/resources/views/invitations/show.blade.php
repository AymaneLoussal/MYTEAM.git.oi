<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device=device-width, initial-scale=1.0" name="viewport" />
    <title>Team Invitation | Tactical Sanctum</title>
    @include('components.assets')


    <style>
        body { font-family: 'Inter', sans-serif; background-color: #121413; color: #e2e3e0; }
        h1, h2, h3 { font-family: 'Manrope', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="bg-background text-on-surface antialiased min-h-screen flex flex-col">
    <header class="bg-[#121413] sticky top-0 z-50">
        <div class="flex justify-between items-center px-8 py-4">
            <a href="{{ route('dashboard') }}" class="text-2xl font-black tracking-tighter text-[#a1d494]">Tactical Sanctum</a>
        </div>
    </header>

    <main class="flex-grow flex items-center justify-center p-6 md:p-12">
        <div class="w-full max-w-2xl">
            @include('components.feedback')
            @if($invitation->status === 'expired' || ($invitation->status === 'pending' && $invitation->isExpired()))
                <div class="bg-surface-container-high p-8 rounded-lg border border-error/30 text-center">
                    <span class="material-symbols-outlined text-4xl text-error mb-4 block">error</span>
                    <h1 class="text-2xl font-bold mb-2">Invitation Expired</h1>
                    <p class="text-secondary mb-6">This invitation is no longer valid.</p>
                    <a href="{{ route('login.form') }}" class="bg-primary text-on-primary px-6 py-3 rounded-lg font-bold hover:bg-primary/90 transition-all inline-block">
                        Sign In
                    </a>
                </div>
            @elseif($invitation->status === 'accepted')
                <div class="bg-surface-container-high p-8 rounded-lg border border-primary/30 text-center">
                    <span class="material-symbols-outlined text-4xl text-primary mb-4 block">check_circle</span>
                    <h1 class="text-2xl font-bold mb-2">Already Accepted</h1>
                    <p class="text-secondary mb-6">You have already accepted this invitation and joined the team.</p>
                    <a href="{{ route('teams.show', $invitation->team) }}" class="bg-primary text-on-primary px-6 py-3 rounded-lg font-bold hover:bg-primary/90 transition-all inline-block">
                        View Team
                    </a>
                </div>
            @elseif($invitation->status === 'rejected')
                <h1 class="text-2xl font-bold">Invitation Declined</h1>
            @else
                <div class="bg-surface-container-high p-8 md:p-10 rounded-lg border border-outline-variant/10">
                    <div class="text-center mb-8">
                        <span class="material-symbols-outlined text-4xl text-primary mb-4 block">mail</span>
                        <h1 class="text-3xl font-black mb-2">You're Invited!</h1>
                        <p class="text-secondary">Join <span class="text-primary font-bold">{{ $invitation->team->name }}</span></p>
                    </div>

                    <div class="bg-surface-container-lowest p-6 rounded-lg mb-8">
                        <div class="mb-6">
                            <label class="text-xs text-secondary font-bold uppercase tracking-wider">Team</label>
                            <p class="text-lg font-bold mt-1">{{ $invitation->team->name }}</p>
                        </div>
                        <div class="mb-6">
                            <label class="text-xs text-secondary font-bold uppercase tracking-wider">Invited By</label>
                            <p class="text-lg font-bold mt-1">{{ $invitation->coach?->name ?? 'Team coach' }}</p>
                        </div>
                        <div>
                            <label class="text-xs text-secondary font-bold uppercase tracking-wider">Invitation Expires</label>
                            <p class="text-lg font-bold mt-1">{{ $invitation->expires_at?->format('M d, Y') ?? 'No expiration' }}</p>
                        </div>
                    </div>

                    @if($currentUser)
                        <!-- User is logged in -->
                        <div class="mb-6 p-4 bg-primary/10 rounded-lg">
                            <p class="text-sm text-secondary">Logged in as: <span class="text-primary font-bold">{{ $currentUser->email }}</span></p>
                        </div>

                        <div class="flex gap-4">
                            <form method="POST" action="{{ route('invitations.accept', $invitation->token) }}" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full bg-primary text-on-primary px-6 py-3 rounded-lg font-bold hover:bg-primary/90 transition-all">
                                    Accept Invitation
                                </button>
                            </form>
                            <form method="POST" action="{{ route('invitations.reject', $invitation->token) }}" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full bg-surface-container text-on-surface px-6 py-3 rounded-lg font-bold hover:bg-surface-container-high transition-all border border-outline-variant/30">
                                    Decline
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- User not logged in -->
                        <div class="space-y-4">
                            <p class="text-secondary text-sm">To join this team, please log in or create an account.</p>

                            <div class="flex gap-4">
                                <form method="GET" action="{{ route('login.form') }}" class="flex-1">
                                    <button type="submit" class="w-full bg-primary text-on-primary px-6 py-3 rounded-lg font-bold hover:bg-primary/90 transition-all">
                                        Sign In
                                    </button>
                                </form>
                                <form method="GET" action="{{ route('register.form') }}" class="flex-1">
                                    <button type="submit" class="w-full bg-surface-container text-on-surface px-6 py-3 rounded-lg font-bold hover:bg-surface-container-high transition-all border border-outline-variant/30">
                                        Create Account
                                    </button>
                                </form>
                            </div>

                            <div class="relative my-6">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-outline-variant/20"></div>
                                </div>
                                <div class="relative flex justify-center text-sm">
                                    <span class="px-2 bg-surface-container-high text-secondary">Or register to accept</span>
                                </div>
                            </div>

                            <button type="button" onclick="document.getElementById('registerForm').classList.remove('hidden')" class="w-full bg-surface-container-high px-6 py-3 rounded-lg font-bold hover:bg-surface-container transition-all border border-outline-variant/30">
                                Create Account & Accept Invitation
                            </button>
                        </div>

                        <!-- Registration Form (hidden by default) -->
                        <div id="registerForm" class="{{ ($showRegistration ?? false) || $errors->any() ? '' : 'hidden' }} mt-8 pt-8 border-t border-outline-variant/20">
                            <h2 class="text-xl font-bold mb-6">Create Account</h2>
                            <form method="POST" action="{{ route('invitations.register', $invitation->token) }}" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-sm font-bold mb-2">Full Name</label>
                                    <input 
                                        type="text" 
                                        name="name" 
                                        required
                                        class="w-full bg-surface-container border border-outline-variant/30 rounded-lg p-3 text-sm focus:ring-2 focus:ring-primary focus:border-transparent @error('name') border-error @enderror"
                                        placeholder="Your name"
                                    >
                                    @error('name')
                                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-bold mb-2">Email ({{ $invitation->email }})</label>
                                    <input 
                                        type="email" 
                                        value="{{ $invitation->email }}"
                                        disabled
                                        class="w-full bg-surface-container border border-outline-variant/30 rounded-lg p-3 text-sm text-secondary"
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-bold mb-2">Password</label>
                                    <input 
                                        type="password" 
                                        name="password" 
                                        required
                                        class="w-full bg-surface-container border border-outline-variant/30 rounded-lg p-3 text-sm focus:ring-2 focus:ring-primary focus:border-transparent @error('password') border-error @enderror"
                                        placeholder="At least 6 characters"
                                    >
                                    @error('password')
                                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-bold mb-2">Confirm Password</label>
                                    <input 
                                        type="password" 
                                        name="password_confirmation" 
                                        required
                                        class="w-full bg-surface-container border border-outline-variant/30 rounded-lg p-3 text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
                                        placeholder="Confirm your password"
                                    >
                                </div>

                                <div class="flex gap-4 pt-4">
                                    <button type="button" onclick="document.getElementById('registerForm').classList.add('hidden')" class="flex-1 bg-surface-container text-on-surface px-6 py-3 rounded-lg font-bold hover:bg-surface-container-high transition-all border border-outline-variant/30">
                                        Cancel
                                    </button>
                                    <button type="submit" class="flex-1 bg-primary text-on-primary px-6 py-3 rounded-lg font-bold hover:bg-primary/90 transition-all">
                                        Create Account & Join
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </main>
</body>
</html>
