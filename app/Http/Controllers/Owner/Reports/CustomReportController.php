<?php
namespace App\Http\Controllers\Owner\Reports;

use App\Http\Controllers\Controller;
use App\Models\SavedReport;
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
}
