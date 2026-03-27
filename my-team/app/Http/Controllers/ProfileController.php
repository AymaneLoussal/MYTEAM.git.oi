<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
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
     * Update authenticated user profile.
     * - Optional: name, email (unique), avatar upload (avatars/public), password (hashed if provided).
     * - Avatar stored as path, accessor provides URL.
     * - Returns updated user with avatar_url.
     */
    public function update(Request $request)
    {
        $userId = session('user_id');
        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'nullable|min:6|confirmed'
        ]);

        // Upload avatar if provided
        if ($request->hasFile('avatar')) {
            // Delete old if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Hash password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
            unset($validated['password_confirmation']); // Client sends
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh(['avatar_url'])
        ]);
    }
}

