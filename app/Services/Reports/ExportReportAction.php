<?php
namespace App\Services\Reports;

use App\Models\SavedReport;

class ExportReportAction
{
    /**
     * Generate CSV from run results.
     */
    public function toCsv(SavedReport $report, array $results, ?array $config = null): string
    {
        $config ??= $report->resolvedConfig();
        [$dateFrom, $dateTo] = app(ReportRunner::class)->resolveDates($config);

        $lines   = [];
        $lines[] = self::csvRow([$report->name]);
        $lines[] = self::csvRow(['Period', ReportPeriod::label($dateFrom, $dateTo), $dateFrom, $dateTo]);
        $lines[] = self::csvRow(['Location', ReportContext::locationLabel($config['location_filter'] ?? 'all')]);
        $lines[] = self::csvRow(['Generated', business_now()->format('Y-m-d H:i')]);
        $lines[] = '';

        foreach ($results as $entry) {
            $block = $entry['block'];
            $title = $block['title'] ?? ($entry['meta']['label'] ?? '');

            if (($block['metric_id'] ?? '') === 'text_block') {
                if (trim($block['content'] ?? '') !== '') {
                    $lines[] = self::csvRow([mb_strtoupper($title)]);
                    $lines[] = self::csvRow([$block['content']]);
                    $lines[] = '';
                }
                continue;
            }

            $r = $entry['result'] ?? null;
            $lines[] = self::csvRow([mb_strtoupper($title)]);
            if (! $r || $r['error']) {
                $lines[] = self::csvRow(["Couldn't load this block"]);
                $lines[] = '';
                continue;
            }

            if ($r['headline']) {
                $lines[] = self::csvRow([ReportFormat::header($r['headline']['label'], $r['headline']['type']), $r['headline']['value']]);
                if ($r['comparison'] && $r['comparison']['value'] !== null) {
                    $lines[] = self::csvRow(['Compared with ' . $r['comparison']['period'], $r['comparison']['value']]);
                }
            }
            foreach ($r['stats'] as $s) {
                $lines[] = self::csvRow([ReportFormat::header($s['label'], $s['type']), $s['value']]);
            }

            if ($r['columns'] && $r['rows']) {
                $lines[] = '';
                $lines[] = self::csvRow(array_map(fn ($c) => ReportFormat::header($c['label'], $c['type']), $r['columns']));
                foreach ($r['rows'] as $row) {
                    $lines[] = self::csvRow(array_map(fn ($c) => $row[$c['key']] ?? null, $r['columns']));
                }
                if ($r['totals'] && count($r['rows']) > 1) {
                    $lines[] = self::csvRow(array_map(
                        fn ($c, $i) => $i === 0 ? 'Total' : ($r['totals'][$c['key']] ?? null),
                        $r['columns'], array_keys($r['columns'])
                    ));
                }
            }
            foreach ($r['notes'] as $note) {
                $lines[] = self::csvRow(['Note: ' . $note]);
            }
            $lines[] = '';
        }

        return implode("\r\n", $lines);
    }

    /**
     * One CSV line. Every cell is quoted with doubled inner quotes, and
     * text cells go through csv_safe() so product/customer names or report
     * text starting with = + - @ can't run as spreadsheet formulas.
     * Numbers, including numeric strings, are left alone (a negative
     * amount is data, not a formula).
     */
    private static function csvRow(array $cells): string
    {
        return implode(',', array_map(function ($v) {
            $v = match (true) {
                $v === null            => '',
                is_bool($v)            => $v ? 'Yes' : 'No',
                is_int($v), is_float($v) => (string) $v,
                is_numeric($v)         => (string) $v,  // Postgres returns decimals as strings
                default                => csv_safe((string) $v),
            };

            return '"' . str_replace('"', '""', $v) . '"';
        }, $cells));
    }
}
