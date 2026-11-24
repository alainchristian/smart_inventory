<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Browser-side hardening headers on every response. Deliberately no
 * script-src policy: the pages use inline scripts and Alpine, so a script
 * policy would need a nonce rollout first. What is set here can't break them.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $h = $response->headers;

        $h->set('X-Content-Type-Options', 'nosniff');
        $h->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        // The phone scanner needs its camera; nothing needs the rest
        $h->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(), payment=(), usb=()');
        // Nobody else may frame the app (clickjacking), embed plugins, or re-point relative URLs
        $h->set('Content-Security-Policy', "frame-ancestors 'self'; object-src 'none'; base-uri 'self'");
        $h->set('X-Frame-Options', 'SAMEORIGIN');

        if ($request->isSecure()) {
            $h->set('Strict-Transport-Security', 'max-age=31536000');
        }

        return $response;
    }
}
