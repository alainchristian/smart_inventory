<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\ViewAs;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Read-only "view as": for a signed-in platform-support account whose session
 * carries a target user id, the request runs AS that user (nothing is logged
 * in as them) inside a transaction that is always rolled back. Mail and queues
 * are switched off, and a statement guard (AppServiceProvider) refuses any
 * INSERT / UPDATE / DELETE, so a save shows "view only" instead of silently
 * pretending to work.
 *
 * Support routes are never impersonated, so the console and the Exit button
 * always act as the real support account.
 */
class ViewAsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasSession()) {
            return $next($request);
        }

        $session = $request->session();
        $id      = $session->get(ViewAs::SESSION_KEY);

        if (! $id) {
            return $next($request);
        }

        $real = Auth::user();

        // The flag only means something for a signed-in support account
        if (! $real || ! $real->isSuperAdmin()) {
            $session->forget(ViewAs::SESSION_KEY);
            return $next($request);
        }

        $route = $request->route()?->getName() ?? '';

        if (str_starts_with($route, 'support.')) {
            // Start / stop manage the flag themselves; opening any other
            // support page leaves view-as mode.
            if (! in_array($route, ['support.view-as.start', 'support.view-as.stop'], true)) {
                $session->forget(ViewAs::SESSION_KEY);
            }
            return $next($request);
        }

        $target = User::visibleToBusiness()->where('is_active', true)->find($id);

        if (! $target) {
            $session->forget(ViewAs::SESSION_KEY);
            return redirect()->route('support.accounts')
                ->with('error', 'That account is no longer available to view.');
        }

        $viewAs = app(ViewAs::class);
        $viewAs->start($real, $target);
        Auth::setUser($target);

        $previous = [config('mail.default'), config('queue.default')];
        config(['mail.default' => 'array', 'queue.default' => 'null']);

        // Roll back to where we started, never past it
        $level = DB::transactionLevel();
        DB::beginTransaction();

        try {
            return $next($request);
        } finally {
            while (DB::transactionLevel() > $level) {
                DB::rollBack();
            }
            config(['mail.default' => $previous[0], 'queue.default' => $previous[1]]);
            $viewAs->stop();
        }
    }
}
