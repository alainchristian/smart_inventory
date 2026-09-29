<?php
namespace App\Services\Reports;

use App\Exports\CustomReport\CustomReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

/**
 * File versions of a custom report (downloads and scheduled emails).
 */
class ReportExporter
{
    public const FORMATS = [
        'pdf'  => 'application/pdf',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'csv'  => 'text/csv; charset=UTF-8',
    ];

    public function render(ReportDocument $doc, string $format): string
    {
        return match ($format) {
            'pdf'   => $this->pdf($doc),
            'xlsx'  => Excel::raw(new CustomReportExport($doc), ExcelFormat::XLSX),
            'csv'   => "\u{FEFF}" . app(ExportReportAction::class)->toCsv($doc->report, $doc->results, $doc->config),  // BOM: Excel reads UTF-8
            default => throw new \InvalidArgumentException("Unknown export format: {$format}"),
        };
    }

    private function pdf(ReportDocument $doc): string
    {
        $pdf = Pdf::loadView('pdf.custom-report', ['doc' => $doc, 'tenant' => config('tenant.name')])
            ->setPaper('a4', 'portrait')
            ->setOption('enable_font_subsetting', true);
        $pdf->render();

        // "Page X of Y" bottom-right on every page (canvas text; dompdf can't do this in CSS)
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $font   = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
        $canvas->page_text($canvas->get_width() - 39.7 - 60, $canvas->get_height() - 30, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 7.5, [0.478, 0.506, 0.627]);

        return $pdf->output();
    }
}
