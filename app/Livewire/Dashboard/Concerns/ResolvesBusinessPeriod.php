<?php

namespace App\Livewire\Dashboard\Concerns;

use Illuminate\Support\Carbon;

/**
 * Date range for the dashboard widgets that listen to TimeFilter's
 * 'time-filter-changed' event ($period, $from, $to).
 *
 * The selected days are business-timezone calendar days; sale_date and the
 * other timestamps are stored in UTC.
 *
 * - businessPeriodRange(): UTC start/end — for timestamp columns
 *   (sale_date, repayment_date, processed_at, …).
 * - businessPeriodDates(): the local calendar days — for DATE columns
 *   (daily_sessions.session_date) and for chart buckets / labels.
 * - localDate($utc): the business-timezone date of a UTC moment.
 *
 * Never call ->toDateString() on a UTC bound: 00:00 in Kigali is 22:00 UTC
 * the day before, so it gives the wrong day. And never pass a
 * business-timezone Carbon to a query — Laravel binds its wall-clock time
 * and ignores the zone. Convert with ->utc() first.
 *
 * Returns Illuminate\Support\Carbon (a subclass of Carbon\Carbon) so widgets
 * may type-hint either class.
 */
trait ResolvesBusinessPeriod
{
    /** @return array{0: Carbon, 1: Carbon} first and last local day, at 00:00 business time */
    protected function businessPeriodDates(): array
    {
        $tz    = config('tenant.timezone');
        $today = Carbon::instance(business_today());

        // TimeFilter always sends the resolved dates; the preset match is only
        // the fallback before its first event.
        if ($this->from && $this->to) {
            return [Carbon::parse($this->from, $tz)->startOfDay(), Carbon::parse($this->to, $tz)->startOfDay()];
        }

        return match ($this->period) {
            'yesterday'  => [$today->copy()->subDay(), $today->copy()->subDay()],
            'week'       => [$today->copy()->startOfWeek(), $today->copy()],
            'month'      => [$today->copy()->startOfMonth(), $today->copy()],
            'last_month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()->startOfDay()],
            'last_30'    => [$today->copy()->subDays(29), $today->copy()],
            default      => [$today->copy(), $today->copy()],
        };
    }

    /** @return array{0: Carbon, 1: Carbon} UTC start and end of the selected local days */
    protected function businessPeriodRange(): array
    {
        [$from, $to] = $this->businessPeriodDates();

        return [$from->copy()->startOfDay()->utc(), $to->copy()->endOfDay()->utc()];
    }

    /** Business-timezone calendar date (Y-m-d) of a UTC moment. */
    protected function localDate(\Carbon\Carbon $utc): string
    {
        return $utc->copy()->setTimezone(config('tenant.timezone'))->toDateString();
    }
}
