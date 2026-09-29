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

    /**
     * Generate HTML suitable for browser print-to-PDF.
     */
    public function toPrintHtml(SavedReport $report, array $results, ?array $config = null): string
    {
        $config ??= $report->resolvedConfig();
        [$dateFrom, $dateTo] = app(ReportRunner::class)->resolveDates($config);

        ob_start();
        ?><!DOCTYPE html><html><head><meta charset="UTF-8">
<title><?= htmlspecialchars($report->name) ?></title>
<style>
body{font-family:sans-serif;font-size:13px;color:#111;padding:24px;max-width:960px;margin:0 auto}
h1{font-size:20px;margin:0 0 4px}.meta{font-size:12px;color:#666;margin-bottom:24px}
.block{margin-bottom:24px;page-break-inside:avoid}
.block-title{font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#333;border-bottom:2px solid #111;padding-bottom:4px;margin-bottom:12px}
table{width:100%;border-collapse:collapse;font-size:12px}
th{background:#f0f0f0;text-align:left;padding:6px 10px;font-weight:700;border:1px solid #ddd}
td{padding:5px 10px;border:1px solid #ddd}tr:nth-child(even) td{background:#fafafa}
.kpi{font-size:28px;font-weight:800;color:#111}.kpi-label{font-size:11px;text-transform:uppercase;color:#666;letter-spacing:.6px}
.text-block{background:#f9f9f9;border-left:3px solid #ccc;padding:12px 16px;font-size:13px;line-height:1.6;white-space:pre-wrap}
</style></head><body>
<h1><?= htmlspecialchars($report->name) ?></h1>
<div class="meta">Period: <?= $dateFrom ?> – <?= $dateTo ?> &nbsp;·&nbsp; Generated: <?= now()->format('d M Y H:i') ?><?= $report->description ? ' &nbsp;·&nbsp; ' . htmlspecialchars($report->description) : '' ?></div>
<?php
        foreach ($results as $blockResult) {
            $block = $blockResult['block'];
            $meta  = $blockResult['meta'];
            $data  = $blockResult['data'];
            $viz   = $block['viz'] ?? ($meta['default_viz'] ?? 'kpi_card');
            $title = $block['title'] ?? ($meta['label'] ?? '');

            if (isset($data['error'])) {
                echo '<div class="block"><div class="block-title">' . htmlspecialchars($title) . '</div>';
                echo '<p style="color:red">Error: ' . htmlspecialchars($data['error']) . '</p></div>';
                continue;
            }

            if (!empty($block['show_if_nonzero'])) {
                $firstNum = collect($data)->filter(fn($v, $k) => !str_starts_with($k, '_') && is_numeric($v))->first();
                if ($firstNum == 0 || $firstNum === null) continue;
            }

            if ($viz === 'text') {
                echo '<div class="block">';
                if ($title) echo '<div class="block-title">' . htmlspecialchars($title) . '</div>';
                echo '<div class="text-block">' . htmlspecialchars($block['content'] ?? '') . '</div>';
                echo '</div>';
                continue;
            }

            echo '<div class="block"><div class="block-title">' . htmlspecialchars($title) . '</div>';

            if ($viz === 'kpi_card') {
                $mainValue = null; $mainKey = null;
                foreach ($data as $k => $v) {
                    if (str_starts_with($k, '_')) continue;
                    if (is_numeric($v)) { $mainKey = $k; $mainValue = $v; break; }
                }
                if ($mainValue !== null) {
                    echo '<div class="kpi">' . number_format($mainValue) . '</div>';
                    echo '<div class="kpi-label">' . htmlspecialchars(str_replace('_', ' ', $mainKey)) . '</div>';
                }
                $others = array_filter($data, fn($k) => !str_starts_with($k, '_') && $k !== $mainKey, ARRAY_FILTER_USE_KEY);
                if (!empty($others)) {
                    echo '<table style="margin-top:8px"><tbody>';
                    foreach ($others as $k => $v) {
                        if (is_scalar($v)) echo '<tr><td>' . htmlspecialchars(str_replace('_', ' ', $k)) . '</td><td>' . htmlspecialchars((string)$v) . '</td></tr>';
                    }
                    echo '</tbody></table>';
                }
            } elseif (is_array($data) && isset($data[0]) && is_array($data[0])) {
                echo '<table><thead><tr>';
                foreach (array_keys($data[0]) as $col) {
                    if (str_starts_with($col, '_')) continue;
                    echo '<th>' . htmlspecialchars(str_replace('_', ' ', $col)) . '</th>';
                }
                echo '</tr></thead><tbody>';
                foreach ($data as $row) {
                    echo '<tr>';
                    foreach ($row as $col => $val) {
                        if (str_starts_with($col, '_')) continue;
                        echo '<td>' . htmlspecialchars(is_array($val) ? json_encode($val) : (string)($val ?? '')) . '</td>';
                    }
                    echo '</tr>';
                }
                echo '</tbody></table>';
            }
            echo '</div>';
        }
        echo '</body></html>';
        return ob_get_clean();
    }
}