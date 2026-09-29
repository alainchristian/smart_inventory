<?php
namespace App\Services\Reports;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * When a custom report is emailed: daily, weekly on a weekday, or monthly
 * on a day of the month, at a time, in the business timezone.
 *
 * Stored in saved_reports.schedule as
 *   {frequency, day, time: "HH:MM", format: pdf|xlsx, since: ISO-8601 UTC}
 * "since" is when the schedule was set: nothing is sent for occurrences
 * before it, so saving a schedule never sends an email straight away.
 */
final class ReportSchedule
{
    public const FREQUENCIES = ['daily' => 'Every day', 'weekly' => 'Every week', 'monthly' => 'Every month'];
    public const FORMATS     = ['pdf' => 'PDF', 'xlsx' => 'Excel'];
    public const WEEKDAYS    = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
    public const MAX_MONTH_DAY = 28;   // every month has it

    private function __construct(
        public readonly string $frequency,
        public readonly int $day,
        public readonly string $time,
        public readonly string $format,
        public readonly Carbon $since,
    ) {}

    public static function fromArray(?array $a): ?self
    {
        if (! $a || ! isset(self::FREQUENCIES[$a['frequency'] ?? ''])) {
            return null;
        }
        $time = preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', (string) ($a['time'] ?? '')) ? $a['time'] : '08:00';
        $day  = (int) ($a['day'] ?? 1);
        $day  = match ($a['frequency']) {
            'weekly'  => $day >= 1 && $day <= 7 ? $day : 1,
            'monthly' => $day >= 1 && $day <= self::MAX_MONTH_DAY ? $day : 1,
            default   => 0,
        };

        try {
            $since = Carbon::parse($a['since'] ?? 'now')->utc();
        } catch (\Throwable) {
            $since = Carbon::now()->utc();
        }

        return new self($a['frequency'], $day, $time, isset(self::FORMATS[$a['format'] ?? '']) ? $a['format'] : 'pdf', $since);
    }

    public function toArray(): array
    {
        return [
            'frequency' => $this->frequency,
            'day'       => $this->day,
            'time'      => $this->time,
            'format'    => $this->format,
            'since'     => $this->since->toIso8601String(),
        ];
    }

    /** "Every Monday at 08:00 · PDF" */
    public function label(): string
    {
        $when = match ($this->frequency) {
            'daily'   => 'Every day',
            'weekly'  => 'Every ' . self::WEEKDAYS[$this->day],
            'monthly' => 'Monthly on the ' . self::ordinal($this->day),
        };

        return "{$when} at {$this->time} · " . self::FORMATS[$this->format];
    }

    /** The latest scheduled moment at or before $now (business time) */
    public function latestOccurrence(?CarbonInterface $now = null): Carbon
    {
        $now = Carbon::instance($now ?? Carbon::now())->setTimezone(config('tenant.timezone'));
        [$h, $m] = array_map('intval', explode(':', $this->time));

        $at = match ($this->frequency) {
            'daily'   => $now->copy()->setTime($h, $m),
            'weekly'  => $now->copy()->startOfWeek()->addDays($this->day - 1)->setTime($h, $m),
            'monthly' => $now->copy()->startOfMonth()->addDays($this->day - 1)->setTime($h, $m),
        };
        if ($at->gt($now)) {
            $at = match ($this->frequency) {
                'daily'   => $at->subDay(),
                'weekly'  => $at->subWeek(),
                'monthly' => $now->copy()->subMonthNoOverflow()->startOfMonth()->addDays($this->day - 1)->setTime($h, $m),
            };
        }

        return $at;
    }

    /** The next scheduled moment after $now (business time) */
    public function nextOccurrence(?CarbonInterface $now = null): Carbon
    {
        $latest = $this->latestOccurrence($now);
        [$h, $m] = array_map('intval', explode(':', $this->time));

        return match ($this->frequency) {
            'daily'   => $latest->copy()->addDay(),
            'weekly'  => $latest->copy()->addWeek(),
            // startOfMonth() resets the time, so set it again
            'monthly' => $latest->copy()->startOfMonth()->addMonthNoOverflow()->addDays($this->day - 1)->setTime($h, $m),
        };
    }

    /**
     * Due when a scheduled moment has passed that is later than both the
     * last send and the moment the schedule was set. A missed run (server
     * down, scheduler late) is sent once when it catches up, never repeated.
     */
    public function isDue(?CarbonInterface $lastRunAt, ?CarbonInterface $now = null): bool
    {
        $latest = $this->latestOccurrence($now);
        $after  = $lastRunAt && $lastRunAt->gt($this->since) ? $lastRunAt : $this->since;

        return $latest->gt($after);
    }

    /**
     * Best-effort conversion of an old cron value (the builder's free-text
     * field) to a schedule; null when the cron doesn't fit daily / weekly /
     * monthly at a fixed time.
     */
    public static function fromCron(?string $cron): ?array
    {
        $parts = preg_split('/\s+/', trim((string) $cron));
        if (count($parts) !== 5) return null;
        [$min, $hour, $dom, $mon, $dow] = $parts;
        if (! ctype_digit($min) || ! ctype_digit($hour) || (int) $min > 59 || (int) $hour > 23 || $mon !== '*') return null;

        $time = sprintf('%02d:%02d', $hour, $min);
        if ($dom === '*' && $dow === '*') {
            return ['frequency' => 'daily', 'day' => 0, 'time' => $time, 'format' => 'pdf'];
        }
        if ($dom === '*' && ctype_digit($dow) && (int) $dow <= 7) {
            return ['frequency' => 'weekly', 'day' => (int) $dow === 0 ? 7 : (int) $dow, 'time' => $time, 'format' => 'pdf'];
        }
        if ($dow === '*' && ctype_digit($dom) && (int) $dom >= 1 && (int) $dom <= self::MAX_MONTH_DAY) {
            return ['frequency' => 'monthly', 'day' => (int) $dom, 'time' => $time, 'format' => 'pdf'];
        }

        return null;
    }

    public static function ordinal(int $n): string
    {
        $suffix = in_array($n % 100, [11, 12, 13], true) ? 'th' : (['th', 'st', 'nd', 'rd'][$n % 10] ?? 'th');

        return $n . $suffix;
    }
}
