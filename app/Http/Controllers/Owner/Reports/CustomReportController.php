<?php
namespace App\Http\Controllers\Owner\Reports;

use App\Http\Controllers\Controller;
use App\Models\SavedReport;
use App\Services\AuditLogger;
use App\Services\Reports\ReportDocument;
use App\Services\Reports\ReportExporter;
use Illuminate\Http\Request;

class CustomReportController extends Controller
{
    public function library()
    {
        return view('owner.reports.custom.library');
    }

    public function builder(Request $request)
    {
        // Old edit links were builder?reportId=X, which Livewire never read
        if ($request->filled('reportId')) {
            return redirect()->route('owner.reports.custom.edit', (int) $request->query('reportId'));
        }

        return view('owner.reports.custom.builder');
    }

    public function edit(SavedReport $report)
    {
        abort_unless($report->created_by === auth()->id(), 403);

        return view('owner.reports.custom.builder', ['reportId' => $report->id]);
    }

    public function view(SavedReport $report)
    {
        abort_unless($report->isVisibleTo(auth()->user()), 403);

        return view('owner.reports.custom.view', compact('report'));
    }

    /**
     * PDF / Excel / CSV of the report for the viewer's filters (period,
     * location, comparison in the query string; the saved defaults
     * otherwise).
     */
    public function export(Request $request, SavedReport $report, string $format)
    {
        abort_unless($report->isVisibleTo($request->user()), 403);
        abort_unless(array_key_exists($format, ReportExporter::FORMATS), 404);

        $doc  = new ReportDocument($report, $request->only(['date_range', 'date_from', 'date_to', 'location_filter', 'comparison_mode']), $request->user());
        $body = app(ReportExporter::class)->render($doc, $format);

        AuditLogger::log([
            'action'            => "report_{$format}_downloaded",
            'module'            => 'reports',
            'entity_type'       => 'SavedReport',
            'entity_id'         => $report->id,
            'entity_identifier' => $report->name,
            'details'           => [
                'date_from'       => $doc->from,
                'date_to'         => $doc->to,
                'location'        => $doc->config['location_filter'] ?? 'all',
                'comparison_mode' => $doc->config['comparison_mode'] ?? 'none',
            ],
        ]);

        return response($body, 200, [
            'Content-Type'        => ReportExporter::FORMATS[$format],
            'Content-Disposition' => 'attachment; filename="' . $doc->fileName($format) . '"',
        ]);
    }

    /** Old "Print" links: the PDF replaced the browser print page */
    public function print(Request $request, SavedReport $report)
    {
        abort_unless($report->isVisibleTo($request->user()), 403);

        return redirect()->route('owner.reports.custom.export', [$report, 'pdf'] + $request->query());
    }
}
