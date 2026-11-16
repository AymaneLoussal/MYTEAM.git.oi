<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device=device-width, initial-scale=1.0" name="viewport" />
    <title>Profile | Tactical Sanctum</title>
    @include('components.assets')


    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #121413;
            color: #e2e3e0;
        }
        h1, h2, h3 {
            font-family: 'Manrope', sans-serif;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-background text-on-surface antialiased min-h-screen">
    @include('components.navigation')

    <main id="main-content" tabindex="-1" class="flex-grow p-6 md:p-12">
        @include('components.feedback')
        <div class="max-w-2xl mx-auto">
            <div class="mb-8">
                <a href="{{ route('dashboard') }}" class="text-primary hover:text-primary/80 text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    Back to Dashboard
                </a>
            </div>

            <div class="bg-surface-container-high p-8 rounded-2xl shadow-xl border border-outline-variant/10">
                <h1 class="text-3xl font-black text-on-surface mb-8">Edit Profile</h1>

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    <!-- Avatar Section -->
                    <div class="flex flex-col items-center mb-8">
                        <div class="w-32 h-32 rounded-full bg-primary-container/30 flex items-center justify-center mb-4 overflow-hidden border-2 border-primary/30">
                            @if($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                            @else
                                <span class="material-symbols-outlined text-4xl text-primary">person</span>
                            @endif
                        </div>
                        <label for="avatar" class="text-sm font-bold mb-2">Change avatar</label>
<input id="avatar" type="file" name="avatar" accept="image/jpeg,image/png,image/gif" class="w-full max-w-sm" aria-describedby="avatar-help">
<p id="avatar-help" class="text-xs text-secondary mt-2">Choose a JPEG, PNG, or GIF up to 2 MB, then save your profile below.</p>
@error('avatar')<p role="alert" class="text-error text-xs mt-2">{{ $message }}</p>@enderror
                    </div>

                    <!-- Name Field -->
                    <div>
                        <label for="name" class="block text-sm font-bold mb-2">Name</label>
                        <input 
                            type="text" 
                            name="name" 
                            id="name" 
                            value="{{ old('name', $user->name) }}"
                            class="w-full bg-surface-container border border-outline-variant/30 rounded-lg p-3 text-sm focus:ring-2 focus:ring-primary focus:border-transparent @error('name') border-error @enderror"
                        >
                        @error('name')
                            <p class="text-error text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-bold mb-2">Email</label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            value="{{ old('email', $user->email) }}"
                            class="w-full bg-surface-container border border-outline-variant/30 rounded-lg p-3 text-sm focus:ring-2 focus:ring-primary focus:border-transparent @error('email') border-error @enderror"
                        >
                        @error('email')
                            <p class="text-error text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="current_password" class="block text-sm font-bold mb-2">Current password (required to change email or password)</label>
                        <input type="password" name="current_password" id="current_password" autocomplete="current-password" class="w-full rounded-lg p-3 bg-surface-container">
                        @error('current_password')<p role="alert" class="text-error">{{ $message }}</p>@enderror
                    </div>
                    <!-- Password Section -->
                    <div class="border-t border-outline-variant/20 pt-6 mt-6">
                        <h3 class="text-lg font-bold mb-4">Change Password (Optional)</h3>

                        <div class="space-y-4">
                            <div>
                                <label for="password" class="block text-sm font-bold mb-2">New Password</label>
                                <input 
                                    type="password" 
                                    name="password" 
                                    id="password" 
                                    class="w-full bg-surface-container border border-outline-variant/30 rounded-lg p-3 text-sm focus:ring-2 focus:ring-primary focus:border-transparent @error('password') border-error @enderror"
                                    placeholder="Leave blank to keep current password"
                                >
                                @error('password')
                                    <p class="text-error text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-bold mb-2">Confirm Password</label>
                                <input 
                                    type="password" 
                                    name="password_confirmation" 
                                    id="password_confirmation" 
                                    class="w-full bg-surface-container border border-outline-variant/30 rounded-lg p-3 text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Confirm new password"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-4 mt-8 pt-6 border-t border-outline-variant/20">
                        <a href="{{ route('dashboard') }}" class="flex-1 bg-surface-container text-on-surface px-4 py-3 rounded-lg font-bold hover:bg-surface-container-high transition-all text-center">
                            Cancel
                        </a>
                        <button type="submit" class="flex-1 bg-primary text-on-primary px-4 py-3 rounded-lg font-bold hover:bg-primary/90 transition-all active:scale-95">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
