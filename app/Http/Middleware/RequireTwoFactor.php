<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Accounts that must use two-step sign-in (platform support) are held at the
 * code step until they pass it: a password alone opens nothing. Not yet set
 * up → sent to setup; set up → sent to the code page.
 *
 * Only the two-factor pages and sign-out are reachable meanwhile. Those are
 * plain controller routes on purpose: Livewire's update endpoint stays shut.
 */
class RequireTwoFactor
{
    public const SESSION_KEY = 'two_factor_passed_for';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->requiresTwoFactor()) {
            return $next($request);
        }

        if ($request->hasSession() && $request->session()->get(self::SESSION_KEY) === $user->id) {
            return $next($request);
        }

        $route = $request->route()?->getName() ?? '';
        if ($route === 'logout' || str_starts_with($route, 'two-factor.')) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->hasHeader('X-Livewire')) {
            return response()->json(['message' => 'Two-step verification required.'], 401);
        }

        return redirect()->route($user->hasTwoFactor() ? 'two-factor.challenge' : 'two-factor.setup');
    }
}
