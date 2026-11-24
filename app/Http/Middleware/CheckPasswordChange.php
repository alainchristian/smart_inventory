<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            auth()->check() &&
            auth()->user()->must_change_password &&
            ! app(\App\Support\ViewAs::class)->active() &&
            !$request->routeIs('password.change') &&
            !$request->routeIs('logout') &&
            // Two-step comes first: RequireTwoFactor holds an account at these pages until it
            // passes, and it blocks /change-password, so redirecting from here made a loop
            !$request->routeIs('two-factor.*') &&
            !$request->routeIs('livewire.*')
        ) {
            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
