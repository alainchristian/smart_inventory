<?php
namespace App\Services\Reports;

use Carbon\Carbon;

/**
 * Report date presets, always in the business timezone. The analytics
 * services take business-tz Y-m-d strings and convert to UTC themselves.
 */
class ReportPeriod
{
    public const PRESETS = [
        'today'      => 'Today',
        'yesterday'  => 'Yesterday',
        'week'       => 'This week',
        'last_week'  => 'Last week',
        'month'      => 'This month',
        'last_month' => 'Last month',
        'last_30'    => 'Last 30 days',
        'quarter'    => 'This quarter',
        'year'       => 'This year',
        'custom'     => 'Custom range',
    ];

    public const COMPARISONS = [
        'none'         => 'No comparison',
        'prior_period' => 'Previous period',
        'prior_year'   => 'Same period last year',
    ];

    /** @return array{0:string,1:string} [from, to] as business-tz Y-m-d */
    public static function resolve(?string $preset, ?string $from = null, ?string $to = null): array
    {
        if ($preset === 'custom' && $from && $to) {
            return $from <= $to ? [$from, $to] : [$to, $from];
        }

        $today = business_today();

        [$start, $end] = match ($preset) {
            'today'      => [$today, $today],
            'yesterday'  => [$today->copy()->subDay(), $today->copy()->subDay()],
            'week'       => [$today->copy()->startOfWeek(), $today],
            'last_week'  => [$today->copy()->subWeek()->startOfWeek(), $today->copy()->subWeek()->endOfWeek()],
            'last_month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
            'last_30'    => [$today->copy()->subDays(29), $today],
            'quarter'    => [$today->copy()->startOfQuarter(), $today],
            'year'       => [$today->copy()->startOfYear(), $today],
            default      => [$today->copy()->startOfMonth(), $today],  // 'month'
        };

        return [$start->toDateString(), $end->toDateString()];
    }

    /**
     * The period to compare against. 'prior_period' is the same number of
     * days immediately before (inclusive length, so the two never overlap).
     *
     * @return array{0:string,1:string}|null
     */
    public static function prior(?string $mode, string $from, string $to): ?array
    {
        $f = Carbon::parse($from);
        $t = Carbon::parse($to);

        return match ($mode) {
            'prior_period' => (function () use ($f, $t) {
                $days = (int) $f->diffInDays($t) + 1;
                return [$f->copy()->subDays($days)->toDateString(), $t->copy()->subDays($days)->toDateString()];
            })(),
            'prior_year' => [$f->copy()->subYearNoOverflow()->toDateString(), $t->copy()->subYearNoOverflow()->toDateString()],
            default      => null,
        };
    }

    /** "1 – 29 Sep 2026", "29 Sep 2026", "28 Aug – 3 Sep 2026" */
    public static function label(string $from, string $to): string
    {
        $f = Carbon::parse($from);
        $t = Carbon::parse($to);

        if ($f->isSameDay($t)) {
            return $t->format('j M Y');
        }
        if ($f->isSameMonth($t)) {
            return $f->format('j') . ' – ' . $t->format('j M Y');
        }
        if ($f->isSameYear($t)) {
            return $f->format('j M') . ' – ' . $t->format('j M Y');
        }

        return $f->format('j M Y') . ' – ' . $t->format('j M Y');
    }
}
