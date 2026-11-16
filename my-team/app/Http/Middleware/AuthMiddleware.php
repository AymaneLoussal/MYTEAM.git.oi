<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Check if user is authenticated via session.
     * Validates user_id exists and user record active (handles deleted users).
     * Redirects to login if not.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = session('user_id');
        if (! $userId || ! ($user = User::find($userId))) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            return redirect()->route('login.form');
        }

        // Keep the existing session architecture while making policies/current_password work.
        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
