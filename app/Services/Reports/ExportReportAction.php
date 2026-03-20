<?php
namespace App\Services\Reports;

use App\Models\SavedReport;

class ExportReportAction
{
    /**
     * Generate CSV from run results.
     */
    public function toCsv(SavedReport $report, array $results): string
    {
        $lines = [];
        $config = $report->resolvedConfig();
        [$dateFrom, $dateTo] = app(ReportRunner::class)->resolveDates($config);

        $lines[] = '"' . $report->name . '"';
        $lines[] = '"Period: ' . $dateFrom . ' to ' . $dateTo . '"';
        $lines[] = '"Generated: ' . now()->format('d M Y H:i') . '"';
        $lines[] = '';

        foreach ($results as $blockResult) {
            if (isset($blockResult['data']['error'])) continue;
            $block = $blockResult['block'];
            $meta  = $blockResult['meta'];
            $data  = $blockResult['data'];
            $viz   = $block['viz'] ?? ($meta['default_viz'] ?? 'kpi_card');
            $title = $block['title'] ?? ($meta['label'] ?? '');

            $lines[] = '"' . strtoupper($title) . '"';

            if ($viz === 'kpi_card') {
                foreach ($data as $key => $value) {
                    if (str_starts_with($key, '_')) continue;
                    if (is_numeric($value) || is_string($value)) {
                        $lines[] = '"' . $key . '","' . $value . '"';
                    }
                }
            } elseif ($viz === 'text') {
                $lines[] = '"' . ($block['content'] ?? '') . '"';
            } else {
                $rows = is_array($data) && isset($data[0]) ? $data : [];
                if (!empty($rows)) {
                    $lines[] = implode(',', array_map(fn($k) => '"' . $k . '"', array_keys($rows[0])));
                    foreach ($rows as $row) {
                        $lines[] = implode(',', array_map(
                            fn($v) => '"' . str_replace('"', '""', (string)($v ?? '')) . '"',
                            $row
                        ));
                    }
                }
            }
            $lines[] = '';
        }

        return implode("\r\n", $lines);
    }

    /**
     * Generate HTML suitable for browser print-to-PDF.
     */
    public function toPrintHtml(SavedReport $report, array $results): string
    {
        $config = $report->resolvedConfig();
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