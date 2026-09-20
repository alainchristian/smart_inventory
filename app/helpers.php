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
