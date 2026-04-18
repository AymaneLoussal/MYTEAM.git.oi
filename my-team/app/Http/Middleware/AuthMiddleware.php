<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

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
        if (!$userId || !($user = User::find($userId))) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            return redirect()->route('login.form');
        }

        return $next($request);
    }
}
