<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Convert string roles to UserRole enums
        $allowedRoles = array_map(function ($role) {
            return UserRole::from($role);
        }, $roles);

        // Check if user has any of the allowed roles
        if (!in_array($user->role, $allowedRoles)) {
            abort(403, 'Unauthorized access. You do not have the required role.');
        }

        // Check if user is active
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account has been deactivated. Please contact the administrator.');
        }

        return $next($request);
    }
}
