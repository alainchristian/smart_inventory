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
