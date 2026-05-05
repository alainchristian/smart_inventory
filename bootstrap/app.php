<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register middleware aliases
        $middleware->alias([
            'role'                  => \App\Http\Middleware\CheckRole::class,
            'location'              => \App\Http\Middleware\CheckLocation::class,
            'check.password.change' => \App\Http\Middleware\CheckPasswordChange::class,
        ]);

        // Force password change wall for new users
        $middleware->appendToGroup('web', \App\Http\Middleware\CheckPasswordChange::class);

        // Trust all proxies (ngrok, load balancers, etc.)
        // This fixes HTTPS detection when behind proxy
        $middleware->trustProxies(at: '*', headers: Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
            Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
            Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
            Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO |
            Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB);
    })
    ->withSchedule(function (Schedule $schedule) {
        // Generate system alerts every 5 minutes
        $schedule->command('alerts:generate')->everyFiveMinutes();
        // Run scheduled custom reports every hour
        $schedule->command('reports:run-scheduled')->hourly();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Redirect cleanly to login on session/CSRF expiry instead of showing 419
        $exceptions->render(function (
            \Illuminate\Session\TokenMismatchException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->expectsJson() || $request->hasHeader('X-Livewire')) {
                return response()->json(['message' => 'Session expired.'], 419);
            }
            return redirect()->route('login')->with('status', 'Your session has expired. Please sign in again.');
        });

        // Redirect authenticated users to their own dashboard on 403 instead of error page
        $exceptions->render(function (
            \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->expectsJson() || $request->hasHeader('X-Livewire')) {
                return null; // Let Livewire/API handle it normally
            }

            if (auth()->check()) {
                $user = auth()->user();
                $route = match (true) {
                    $user->isOwner()            => 'owner.dashboard',
                    $user->isWarehouseManager() => 'warehouse.dashboard',
                    default                     => 'shop.dashboard',
                };
                return redirect()->route($route)
                    ->with('error', 'You don\'t have permission to access that page.');
            }
        });
    })->create();
