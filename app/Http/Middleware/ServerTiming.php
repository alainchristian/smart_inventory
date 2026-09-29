<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds a Server-Timing header (visible in DevTools → Network → Timing) so a
 * slow page can be split into network vs PHP vs database time without
 * anyone sharing a login. Requests slower than SLOW_MS are also logged.
 */
class ServerTiming
{
    private const SLOW_MS = 1000;

    public function handle(Request $request, Closure $next): Response
    {
        $start   = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);
        $queries = 0;
        $dbMs    = 0.0;

        DB::listen(function ($query) use (&$queries, &$dbMs) {
            $queries++;
            $dbMs += $query->time;
        });

        $response = $next($request);

        $appMs = (microtime(true) - $start) * 1000;

        $response->headers->set('Server-Timing', sprintf(
            'app;dur=%.1f, db;dur=%.1f;desc="%d queries"',
            $appMs, $dbMs, $queries
        ));

        if ($appMs > self::SLOW_MS) {
            Log::warning('Slow request', [
                'method'  => $request->method(),
                'path'    => $request->path(),
                'route'   => $request->route()?->getName(),
                'app_ms'  => (int) $appMs,
                'db_ms'   => (int) $dbMs,
                'queries' => $queries,
                'user_id' => $request->user()?->id,
            ]);
        }

        return $response;
    }
}
