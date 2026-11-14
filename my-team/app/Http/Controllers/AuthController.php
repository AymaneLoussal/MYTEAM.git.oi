<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }

    /**
     * Register new user - validates input, hashes password, creates user record.
     * No login automatic - client must call login next.
     */
    public function register(Request $request)
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim((string) $request->email))]);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed', // assumes password_confirmation
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($this->wantsJson($request)) {
            return response()->json(['message' => 'User created successfully', 'user_id' => $user->id]);
        }

        return redirect()->route('login.form')->with('status', 'Account created. Please sign in.');
    }

    /**
     * Login user - validates credentials, sets session user_id, regenerates session ID for security.
     * Returns success - session cookie sent for subsequent requests.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::whereRaw('LOWER(email) = ?', [strtolower(trim((string) $request->email))])->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            if ($this->wantsJson($request)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            return back()
                ->withErrors(['email' => 'Invalid credentials'])
                ->onlyInput('email');
        }

        session(['user_id' => $user->id]);
        session()->regenerate(); // Prevent session fixation

        if ($this->wantsJson($request)) {
            return response()->json(['message' => 'Login successful', 'user' => $user->only('id', 'name', 'email')]);
        }

        if ($token = session()->pull('invitation_token')) {
            return redirect()->route('invitations.show', $token);
        }

        return redirect()->route('dashboard');
    }

    /**
     * Logout current user - clears user_id from session.
     * Requires auth middleware.
     */
    public function logout(Request $request)
    {
        Auth::forgetUser();
        session()->invalidate();
        session()->regenerateToken();

        if ($this->wantsJson($request)) {
            return response()->json(['message' => 'Logout successful']);
        }

        return redirect('/');
    }
}
// AuthController Docblock placeholder
// Validation rules extracted
// Validation rules extracted for login
