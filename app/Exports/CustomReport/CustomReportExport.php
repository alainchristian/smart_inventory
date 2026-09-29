<?php
namespace App\Exports\CustomReport;

use App\Services\Reports\ReportDocument;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * A custom report as an .xlsx workbook: a Summary sheet (report details,
 * every block's headline and figures, key findings), then one sheet per
 * table block with real numbers, typed number formats and a totals row.
 */
class CustomReportExport implements WithMultipleSheets
{
    public function __construct(private ReportDocument $doc) {}

    public function sheets(): array
    {
        $sheets = [new SummarySheet($this->doc)];
        $used   = ['summary' => true];

        foreach ($this->doc->results as $entry) {
            $r = $entry['result'] ?? null;
            // Summary cards are on the Summary sheet; only table / chart blocks get their own
            if (! $r || $r['error'] || empty($r['columns']) || empty($r['rows']) || ($entry['block']['viz'] ?? '') === 'kpi_card') {
                continue;
            }
            $sheets[] = new TableSheet(
                $this->uniqueTitle($entry['block']['title'] ?? ($entry['meta']['label'] ?? 'Table'), $used),
                $r,
            );
        }

        return $sheets;
    }

    /** Excel sheet names: ≤31 characters, no []:*?/\ and unique (case-insensitive) */
    private function uniqueTitle(string $title, array &$used): string
    {
        $base = trim(preg_replace('/[\[\]:*?\/\\\\]/', ' ', $title)) ?: 'Table';
        $base = mb_substr($base, 0, 31);
        $name = $base;
        for ($n = 2; isset($used[mb_strtolower($name)]); $n++) {
            $suffix = " ({$n})";
            $name = mb_substr($base, 0, 31 - mb_strlen($suffix)) . $suffix;
        }
        $used[mb_strtolower($name)] = true;

        return $name;
    }
}
