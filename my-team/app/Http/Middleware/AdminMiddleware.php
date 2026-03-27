<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class AdminMiddleware
{
    /**
     * Require admin role via is_admin flag.
     * Builds on auth - user must exist and be admin.
     * 403 Forbidden otherwise.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = session('user_id');
        $user = User::find($userId);
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Admin access required');
        }

        return $next($request);
    }
}
