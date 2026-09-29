<?php
namespace App\Services\Reports;

use Carbon\Carbon;

/**
 * Formats MetricResult values for people (screen and PDF). Excel and CSV
 * get raw values instead, with the type deciding the number format.
 */
class ReportFormat
{
    /** Value only, no unit ("45,393,000", "12.5%", "1.30×", "13 days") */
    public static function value(mixed $value, string $type): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return match ($type) {
            'money'    => number_format((float) $value),
            'count'    => number_format((float) $value, fmod((float) $value, 1.0) === 0.0 ? 0 : 1),
            'percent'  => number_format((float) $value, 1) . '%',
            'ratio'    => number_format((float) $value, 2) . '×',
            'days'     => number_format((float) $value) . ' ' . ((int) $value === 1 ? 'day' : 'days'),
            'date'     => self::date($value, 'j M Y'),
            'datetime' => self::date($value, 'j M Y H:i'),
            'bool'     => $value ? 'Yes' : 'No',
            default    => (string) $value,
        };
    }

    /** Value with unit for headlines ("45,393,000 RWF") */
    public static function withUnit(mixed $value, string $type): string
    {
        $v = self::value($value, $type);

        return $type === 'money' && $v !== '—' ? $v . ' RWF' : $v;
    }

    /** Numbers right-aligned, text left */
    public static function align(string $type): string
    {
        return in_array($type, ['money', 'count', 'percent', 'ratio', 'days'], true) ? 'right' : 'left';
    }

    /** Column header, with the currency on money columns */
    public static function header(string $label, string $type): string
    {
        return $type === 'money' ? $label . ' (RWF)' : $label;
    }

    private static function date(mixed $value, string $format): string
    {
        try {
            return Carbon::parse($value)->format($format);
        } catch (\Throwable) {
            return (string) $value;
        }
    }
}
