<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('auth.login');
        }

        $user = Auth::user();

        // Check if user has one of the allowed roles
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // If not allowed, return 403 Forbidden
        \Illuminate\Support\Facades\Log::warning('403 Forbidden', ['user_role' => $user->role, 'allowed_roles' => $roles, 'url' => $request->fullUrl()]);
        abort(403, 'Unauthorized action. Anda tidak memiliki akses ke halaman ini.');
    }
}
