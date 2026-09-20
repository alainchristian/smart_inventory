<?php

namespace App\Http\Middleware;

use App\Services\SettingsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const AVAILABLE = ['en', 'rw'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolve($request);

        App::setLocale($locale);
        \Carbon\Carbon::setLocale($locale);

        return $next($request);
    }

    /**
     * Priority when multilingual is enabled: signed-in user's own preference
     * → guest's session choice → business-wide default → app config fallback.
     *
     * When multilingual is disabled, per-user/session choices are ignored
     * entirely — everyone gets the business-wide default language.
     */
    private function resolve(Request $request): string
    {
        $settings = app(SettingsService::class);
        $default  = $settings->defaultLocale();
        $default  = in_array($default, self::AVAILABLE, true) ? $default : config('app.locale', 'en');

        if (!$settings->multilingualEnabled()) {
            return $default;
        }

        $userLocale = auth()->check() ? auth()->user()->locale : null;
        if ($userLocale && in_array($userLocale, self::AVAILABLE, true)) {
            return $userLocale;
        }

        $sessionLocale = $request->session()->get('locale');
        if ($sessionLocale && in_array($sessionLocale, self::AVAILABLE, true)) {
            return $sessionLocale;
        }

        return $default;
    }
}
