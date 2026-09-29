<?php
namespace App\Services\Reports;

/**
 * The one shape every metric returns, and the only thing the screen,
 * PDF, Excel and CSV renderers read. Serialises to a plain array
 * (toArray / fromArray) for the results cache and Livewire.
 *
 * Value types (for headline, stats and columns):
 *   money | count | percent | ratio | days | date | datetime | text | bool
 */
final class MetricResult
{
    /** @var array{value:int|float|null,type:string,label:string}|null */
    public ?array $headline = null;

    /** @var list<array{label:string,value:mixed,type:string}> */
    public array $stats = [];

    /** @var list<array{key:string,label:string,type:string}> */
    public array $columns = [];

    /** @var list<array<string,mixed>> */
    public array $rows = [];

    /** Sums for columns where a total makes sense, keyed like a row */
    public ?array $totals = null;

    /** @var array{labels:list<string>,datasets:list<array{label:string,data:list<int|float>,type:string}>}|null */
    public ?array $series = null;

    /** Plain-language caveats shown under the block */
    public array $notes = [];

    /** ['text' => …, 'tone' => good|warn|bad|neutral] */
    public ?array $insight = null;

    /** Set by the runner when comparing: ['value', 'period', 'pct', 'good' => bool|null] */
    public ?array $comparison = null;

    /** Set by the runner from block thresholds: ok | warn | crit */
    public ?string $status = null;

    public ?string $error = null;

    /** [labelKey, valueKeys] from chart(); lets sortAndLimit() rebuild the series */
    private ?array $chartSpec = null;

    public static function make(): self
    {
        return new self();
    }

    public static function failed(string $message): self
    {
        $r = new self();
        $r->error = $message;
        return $r;
    }

    public function headline(int|float|null $value, string $type, string $label): self
    {
        $this->headline = ['value' => $value, 'type' => $type, 'label' => $label];
        return $this;
    }

    public function stat(string $label, mixed $value, string $type = 'count'): self
    {
        $this->stats[] = ['label' => $label, 'value' => $value, 'type' => $type];
        return $this;
    }

    /** @param array<string, array{0:string,1:string}> $columns key => [label, type] */
    public function table(array $columns, array $rows, array $totalKeys = []): self
    {
        $this->columns = [];
        foreach ($columns as $key => [$label, $type]) {
            $this->columns[] = ['key' => $key, 'label' => $label, 'type' => $type];
        }

        $keys = array_keys($columns);
        $this->rows = array_values(array_map(function ($row) use ($keys) {
            $row = (array) $row;
            $out = [];
            foreach ($keys as $k) {
                $out[$k] = $row[$k] ?? null;
            }
            return $out;
        }, $rows));

        $this->totals = null;
        if ($totalKeys && $this->rows) {
            $this->totals = [];
            foreach ($keys as $k) {
                $this->totals[$k] = in_array($k, $totalKeys, true)
                    ? array_sum(array_map(fn ($r) => (float) ($r[$k] ?? 0), $this->rows))
                    : null;
            }
        }

        return $this;
    }

    /**
     * Chart data from the table rows: one label column, one or more value
     * columns. Call after table().
     */
    public function chart(string $labelKey, array $valueKeys): self
    {
        $this->chartSpec = [$labelKey, $valueKeys];
        $types  = array_column($this->columns, 'type', 'key');
        $labels = array_column($this->columns, 'label', 'key');

        $this->series = [
            'labels'   => array_map(fn ($r) => (string) ($r[$labelKey] ?? ''), $this->rows),
            'datasets' => array_map(fn ($k) => [
                'label' => $labels[$k] ?? $k,
                'type'  => $types[$k] ?? 'count',
                'data'  => array_map(fn ($r) => (float) ($r[$k] ?? 0), $this->rows),
            ], $valueKeys),
        ];

        return $this;
    }

    public function note(string $text): self
    {
        if (! in_array($text, $this->notes, true)) {
            $this->notes[] = $text;
        }
        return $this;
    }

    public function insight(?array $insight): self
    {
        $this->insight = $insight;
        return $this;
    }

    public function hasTable(): bool
    {
        return $this->columns !== [];
    }

    public function isEmpty(): bool
    {
        return $this->error === null
            && ($this->headline['value'] ?? null) === null
            && $this->rows === []
            && $this->stats === [];
    }

    /** Headline value, or null */
    public function value(): int|float|null
    {
        return $this->headline['value'] ?? null;
    }

    /**
     * Sort rows by a column key and/or keep the first N. Totals are
     * recomputed only when rows are dropped.
     */
    public function sortAndLimit(?string $sortBy, string $direction = 'desc', ?int $limit = null): self
    {
        $keys = array_column($this->columns, 'key');

        if ($sortBy && in_array($sortBy, $keys, true)) {
            usort($this->rows, function ($a, $b) use ($sortBy, $direction) {
                $cmp = is_numeric($a[$sortBy] ?? null) && is_numeric($b[$sortBy] ?? null)
                    ? ($a[$sortBy] <=> $b[$sortBy])
                    : strnatcasecmp((string) ($a[$sortBy] ?? ''), (string) ($b[$sortBy] ?? ''));
                return $direction === 'asc' ? $cmp : -$cmp;
            });
        }

        if ($limit && $limit > 0 && count($this->rows) > $limit) {
            $this->rows = array_slice($this->rows, 0, $limit);
            if ($this->totals !== null) {
                foreach ($this->totals as $k => $v) {
                    if ($v !== null) {
                        $this->totals[$k] = array_sum(array_map(fn ($r) => (float) ($r[$k] ?? 0), $this->rows));
                    }
                }
            }
        }

        if ($this->chartSpec !== null) {
            $this->chart(...$this->chartSpec);  // keep the chart in step with the table
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'headline'   => $this->headline,
            'stats'      => $this->stats,
            'columns'    => $this->columns,
            'rows'       => $this->rows,
            'totals'     => $this->totals,
            'series'     => $this->series,
            'notes'      => $this->notes,
            'insight'    => $this->insight,
            'comparison' => $this->comparison,
            'status'     => $this->status,
            'error'      => $this->error,
        ];
    }

    public static function fromArray(array $a): self
    {
        $r = new self();
        foreach (['headline', 'totals', 'series', 'insight', 'comparison', 'status', 'error'] as $k) {
            $r->$k = $a[$k] ?? null;
        }
        foreach (['stats', 'columns', 'rows', 'notes'] as $k) {
            $r->$k = $a[$k] ?? [];
        }
        return $r;
    }
}
