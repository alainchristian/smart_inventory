<?php

use Carbon\Carbon;

if (! function_exists('business_now')) {
    function business_now(): Carbon
    {
        return Carbon::now(config('tenant.timezone'));
    }
}

if (! function_exists('business_today')) {
    function business_today(): Carbon
    {
        return business_now()->startOfDay();
    }
}

if (! function_exists('local_time')) {
    function local_time(Carbon|DateTimeInterface|string|null $dt): ?Carbon
    {
        if (! $dt) {
            return null;
        }

        return Carbon::parse($dt)->copy()->setTimezone(config('tenant.timezone'));
    }
}

if (! function_exists('multilingual_enabled')) {
    /**
     * Whether the owner has turned on language switching (Business Settings
     * → Localization). When false, the language switcher UI must stay
     * hidden — SetLocale already forces everyone to the default language
     * regardless of what's in this check.
     */
    function multilingual_enabled(): bool
    {
        return app(\App\Services\SettingsService::class)->multilingualEnabled();
    }
}

if (! function_exists('csv_safe')) {
    /**
     * Prefix a leading =, +, -, or @ with an apostrophe so spreadsheet apps
     * (Excel, Sheets) treat the cell as text instead of a formula.
     */
    function csv_safe(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return preg_match('/^[=+\-@]/', $value) ? "'" . $value : $value;
    }
}

if (! function_exists('safe_return_path')) {
    /**
     * A "go back here afterwards" address, or null. Only a path inside this
     * app is accepted ("/shop/pos?x=1"), never another site: no scheme, no
     * "//host", no backslashes, and never Livewire's own request endpoint.
     */
    function safe_return_path(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '' || strlen($value) > 500
            || ! str_starts_with($value, '/') || str_starts_with($value, '//')
            || str_contains($value, "\\") || preg_match('/[\x00-\x1F]/', $value)
            || str_starts_with($value, '/livewire')) {
            return null;
        }

        return $value;
    }
}

if (! function_exists('current_return_path')) {
    /**
     * The page the user is looking at, as a safe return path. During a
     * Livewire update the request is /livewire/update, so the page comes
     * from the Referer instead.
     */
    function current_return_path(): ?string
    {
        $request = request();
        if (! $request->is('livewire/*')) {
            return safe_return_path($request->getRequestUri());
        }
        $referer = (string) $request->headers->get('referer');
        if ($referer === '' || parse_url($referer, PHP_URL_HOST) !== $request->getHost()) {
            return null;
        }
        $path  = parse_url($referer, PHP_URL_PATH) ?: '';
        $query = parse_url($referer, PHP_URL_QUERY);

        return safe_return_path($path . ($query ? '?' . $query : ''));
    }
}
