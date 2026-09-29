<?php
namespace App\Exports\CustomReport;

use App\Services\Reports\ReportDocument;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SummarySheet implements FromArray, WithTitle, WithStyles, WithColumnFormatting, ShouldAutoSize, WithStrictNullComparison
{
    private const UNITS = ['money' => 'RWF', 'percent' => '%', 'ratio' => '×', 'days' => 'days'];

    private int $headerRow = 0;
    private array $sectionRows = [];

    public function __construct(private ReportDocument $doc) {}

    public function title(): string
    {
        return 'Summary';
    }

    public function array(): array
    {
        $d = $this->doc;
        $rows = [
            [$d->report->name],
            ['Period', $d->periodLabel, $d->from, $d->to],
            ['Location', $d->locationLabel],
        ];
        if ($d->comparisonLabel) {
            $rows[] = ['Compared with', $d->comparisonLabel];
        }
        $rows[] = ['Generated', $d->generatedAt . ($d->generatedBy ? ' by ' . $d->generatedBy->name : '')];
        $rows[] = [];

        $rows[] = ['Block', 'Figure', 'Value', 'Unit', 'Previous period', 'Change %'];
        $this->headerRow = count($rows);

        foreach ($d->results as $entry) {
            $r = $entry['result'] ?? null;
            if (! $r) continue;   // text blocks
            $title = $entry['block']['title'] ?? ($entry['meta']['label'] ?? '');

            if ($r['error']) {
                $rows[] = [$title, "Couldn't load this block"];
                continue;
            }
            if ($h = $r['headline']) {
                $c = $r['comparison'];
                $rows[] = [$title, $h['label'], $this->cell($h['value'], $h['type']), self::UNITS[$h['type']] ?? '',
                           $c ? $this->cell($c['value'], $h['type']) : null, $c['pct'] ?? null];
            }
            foreach ($r['stats'] as $s) {
                $rows[] = ['', $s['label'], $this->cell($s['value'], $s['type']), self::UNITS[$s['type']] ?? ''];
            }
            foreach ($r['notes'] as $note) {
                $rows[] = ['', 'Note: ' . $note];
            }
        }

        if ($findings = ReportDocument::findings($d->results)) {
            $rows[] = [];
            $rows[] = ['Key findings'];
            $this->sectionRows[] = count($rows);
            foreach ($findings as $f) {
                $rows[] = [$f['title'], $f['text']];
            }
        }

        return $rows;
    }

    /** Numbers stay numbers; anything else as text */
    private function cell(mixed $v, string $type): mixed
    {
        return is_numeric($v) && ! in_array($type, ['date', 'datetime', 'text'], true) ? $v + 0 : $v;
    }

    public function columnFormats(): array
    {
        return ['C' => '#,##0.##', 'E' => '#,##0.##', 'F' => '0.0'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A' . ($this->headerRow + 1));
        $styles = [
            1                => ['font' => ['bold' => true, 'size' => 14]],
            'A2:A5'          => ['font' => ['bold' => true]],
            $this->headerRow => ['font' => ['bold' => true], 'borders' => ['bottom' => ['borderStyle' => 'thin']]],
        ];
        foreach ($this->sectionRows as $row) {
            $styles[$row] = ['font' => ['bold' => true]];
        }

        return $styles;
    }
}
