<?php

namespace App\Http\Middleware;

use App\Services\AuditLogger;
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

        // Check if user is active
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account has been deactivated. Please contact the administrator.');
        }

        // Owner and Admin both pass the 'owner' role check
        // (Admin uses all owner routes — no separate route prefix needed)
        if ($user->isOwner() || $user->isAdmin()) {
            if (in_array('owner', $roles)) {
                return $next($request);
            }
        }

        // Check exact role match
        foreach ($roles as $role) {
            if ($user->role->value === $role) {
                return $next($request);
            }
        }

        AuditLogger::log([
            'actor'             => $user,
            'action'            => 'permission_denied',
            'module'            => 'auth',
            'entity_type'       => 'Route',
            'entity_identifier' => $request->path(),
            'details'           => ['required_roles' => $roles, 'user_role' => $user->role->value],
            'status'            => 'failed',
            'severity'          => 'warning',
        ]);

        abort(403, 'Unauthorized.');
    }
}
