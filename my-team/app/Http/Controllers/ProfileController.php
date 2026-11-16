<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Profile management - update name/email/avatar/password for authenticated user.
 */
class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.custom');
    }

    /**
     * Show profile edit form.
     */
    public function show()
    {
        $userId = session('user_id');
        $user = User::findOrFail($userId);

        return view('profile.show', ['user' => $user]);
    }

    /**
     * Update authenticated user profile.
     * - Optional: name, email (unique), avatar upload (avatars/public), password (hashed if provided).
     * - Avatar stored as path, accessor provides URL.
     * - Returns updated user with avatar_url.
     */
    public function update(Request $request)
    {
        $userId = session('user_id');
        $user = User::findOrFail($userId);

        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->email))]);
        }
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,'.$user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'nullable|string|min:6|confirmed',
            'current_password' => 'nullable|string',
        ], [
            'avatar.uploaded' => 'The avatar could not be processed. Choose a JPEG, PNG, or GIF up to 2 MB.',
            'avatar.max' => 'The avatar may not be greater than 2 MB.',
        ]);

        if (! empty($validated['password']) || (isset($validated['email']) && $validated['email'] !== $user->email)) {
            $request->validate(['current_password' => 'required|current_password']);
        }
        unset($validated['current_password']);
        if (empty($validated['password'])) {
            unset($validated['password']);
        }
        unset($validated['avatar']);
        $oldAvatar = $user->avatar;

        // Upload avatar if provided
        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
            abort_unless($validated['avatar'], 500, 'Unable to store avatar.');
        }

        // Hash password if provided
        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
            unset($validated['password_confirmation']); // Client sends
        }

        $user->update($validated);

        if (isset($validated['avatar']) && $oldAvatar) {
            Storage::disk('public')->delete($oldAvatar);
        }
        if (! $request->expectsJson()) {
            return redirect()->route('profile.show')->with('status', 'Profile updated successfully');
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh(),
        ]);
    }
}

// ProfileController Docblock placeholder
