<?php

namespace App\Http\Middleware;

use App\Models\TrustedDevice;
use App\Services\AuditLogger;
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

        // A device this person chose to trust (passed the code on it before) skips the code
        if ($request->hasSession() && $user->canTrustDevices() && $device = TrustedDevice::forRequest($user, $request)) {
            $device->touchAndRotate($request);
            $request->session()->put(self::SESSION_KEY, $user->id);
            AuditLogger::log([
                'action' => 'two_factor_device_skipped', 'module' => 'auth', 'entity' => $user,
                'entity_identifier' => $user->email, 'details' => ['device_id' => $device->id],
            ]);

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
