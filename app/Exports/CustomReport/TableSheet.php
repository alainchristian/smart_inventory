<?php
namespace App\Exports\CustomReport;

use App\Services\Reports\ReportFormat;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** One table block: typed header, rows as real numbers / dates, totals row */
class TableSheet implements FromArray, WithTitle, WithStyles, WithColumnFormatting, ShouldAutoSize, WithStrictNullComparison
{
    public function __construct(private string $title, private array $result) {}

    public function title(): string
    {
        return $this->title;
    }

    public function array(): array
    {
        $cols = $this->result['columns'];
        $rows = [array_map(fn ($c) => ReportFormat::header($c['label'], $c['type']), $cols)];

        foreach ($this->result['rows'] as $row) {
            $rows[] = array_map(fn ($c) => $this->cell($row[$c['key']] ?? null, $c['type']), $cols);
        }

        if ($this->hasTotals()) {
            $rows[] = array_map(fn ($c, $i) => $i === 0 ? 'Total' : $this->cell($this->result['totals'][$c['key']] ?? null, $c['type']),
                $cols, array_keys($cols));
        }

        return $rows;
    }

    private function hasTotals(): bool
    {
        return ! empty($this->result['totals']) && count($this->result['rows']) > 1;
    }

    private function cell(mixed $v, string $type): mixed
    {
        if ($v === null || $v === '') return null;

        return match ($type) {
            'date', 'datetime' => $this->excelDate($v),
            'bool'             => $v ? 'Yes' : 'No',
            'text'             => (string) $v,
            default            => is_numeric($v) ? $v + 0 : $v,
        };
    }

    private function excelDate(mixed $v): mixed
    {
        try {
            return ExcelDate::PHPToExcel(Carbon::parse($v));
        } catch (\Throwable) {
            return (string) $v;
        }
    }

    public function columnFormats(): array
    {
        $formats = [];
        foreach ($this->result['columns'] as $i => $c) {
            $values = array_column($this->result['rows'], $c['key']);
            $whole  = collect($values)->every(fn ($v) => ! is_numeric($v) || floor((float) $v) == (float) $v);

            $format = match ($c['type']) {
                'money'    => '#,##0',
                'count'    => $whole ? '#,##0' : '#,##0.00',
                'percent'  => '0.0"%"',
                'ratio'    => '0.00"×"',
                'days'     => '#,##0',
                'date'     => 'd mmm yyyy',
                'datetime' => 'd mmm yyyy hh:mm',
                default    => null,
            };
            if ($format) {
                $formats[Coordinate::stringFromColumnIndex($i + 1)] = $format;
            }
        }

        return $formats;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $styles = [1 => ['font' => ['bold' => true], 'borders' => ['bottom' => ['borderStyle' => 'thin']]]];
        if ($this->hasTotals()) {
            $styles[count($this->result['rows']) + 2] = ['font' => ['bold' => true], 'borders' => ['top' => ['borderStyle' => 'thin']]];
        }

        return $styles;
    }
}
