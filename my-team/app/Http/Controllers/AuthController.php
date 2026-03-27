<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register new user - validates input, hashes password, creates user record.
     * No login automatic - client must call login next.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed' // assumes password_confirmation
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'User created successfully', 'user_id' => $user->id]);
    }

    /**
     * Login user - validates credentials, sets session user_id, regenerates session ID for security.
     * Returns success - session cookie sent for subsequent requests.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        session(['user_id' => $user->id]);
        session()->regenerate(); // Prevent session fixation

        return response()->json(['message' => 'Login successful', 'user' => $user->only('id', 'name', 'email')]);
    }

    /**
     * Logout current user - clears user_id from session.
     * Requires auth middleware.
     */
    public function logout(Request $request)
    {
        session()->forget('user_id');

        return response()->json(['message' => 'Logout successful']);
    }
}
