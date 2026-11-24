<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * The phone-scanner endpoints are public and the session code is only 6
 * characters, so guessing codes is the thing to slow down. Every "session not
 * found" answer counts against the caller; past the limit it gets 429 until
 * the window ends. Normal use (valid code) never counts.
 */
class ThrottleScannerGuessing
{
    public const MAX_MISSES = 15;
    public const WINDOW     = 600;   // seconds

    public function handle(Request $request, Closure $next): Response
    {
        $key = 'scanner-miss|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_MISSES)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many wrong codes. Try again in a few minutes.',
            ], 429, ['Retry-After' => RateLimiter::availableIn($key)]);
        }

        $response = $next($request);

        if ($response->getStatusCode() === 404) {
            RateLimiter::hit($key, self::WINDOW);
        }

        return $response;
    }
}
