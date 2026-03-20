<?php
namespace App\Http\Controllers\Owner\Reports;

use App\Http\Controllers\Controller;
use App\Models\SavedReport;

class CustomReportController extends Controller
{
    public function library()
    {
        return view('owner.reports.custom.library');
    }

    public function builder()
    {
        return view('owner.reports.custom.builder');
    }

    public function view(SavedReport $report)
    {
        $user = auth()->user();
        if ($report->created_by !== $user->id && ! $report->is_shared) {
            abort(403);
        }
        return view('owner.reports.custom.view', compact('report'));
    }
}
