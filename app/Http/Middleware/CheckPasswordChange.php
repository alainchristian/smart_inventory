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
            !$request->routeIs('password.change') &&
            !$request->routeIs('logout') &&
            !$request->routeIs('livewire.*')
        ) {
            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
