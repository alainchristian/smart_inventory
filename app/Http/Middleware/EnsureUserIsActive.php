<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * A deactivated account is signed out on its very next request, wherever it
 * is. CheckRole only guarded role-gated pages, and Livewire's update calls
 * don't re-run route middleware, so a user deactivated while signed in could
 * keep working on pages they already had open. This sits in the web group, so
 * it covers Livewire updates too.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->is_active) {
            return $next($request);
        }

        // Let sign-out itself through.
        if ($request->routeIs('logout')) {
            return $next($request);
        }

        Auth::guard()->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $message = 'Your account has been deactivated. Please contact the administrator.';

        // 419 makes Livewire reload the page, which then lands on the sign-in screen.
        if ($request->expectsJson() || $request->hasHeader('X-Livewire')) {
            return response()->json(['message' => $message], 419);
        }

        return redirect()->route('login')->with('error', $message);
    }
}
