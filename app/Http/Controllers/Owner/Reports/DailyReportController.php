<?php

namespace App\Http\Controllers\Owner\Reports;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\AuditLogger;
use App\Services\DayClose\DailySessionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DailyReportController extends Controller
{
    public function print(Request $request)
    {
        return view('owner.reports.daily-print', $this->buildReportData($request));
    }

    private function buildReportData(Request $request): array
    {
        $user = auth()->user();

        if (! $user->isOwner() && ! $user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
            'shop'      => ['nullable', 'regex:/^(all|shop:\d+)$/'],
            'view'      => 'nullable|in:summary,transactions',
            'profit'    => 'nullable|boolean',
        ]);

        $viewMode   = $validated['view'] ?? 'summary';
        $shopFilter = $validated['shop'] ?? 'all';
        $isAllShops = $shopFilter === 'all';
        $shopId     = $isAllShops ? null : (int) str_replace('shop:', '', $shopFilter);

        if (! $isAllShops && ! Shop::whereKey($shopId)->exists()) {
            abort(404);
        }

        $service = app(DailySessionService::class);

        $summary = $service->computeRangeSummary(
            $shopId,
            $validated['date_from'],
            $validated['date_to']
        );

        $shops = Shop::orderBy('name')->get(['id', 'name']);

        $cashRegister = $isAllShops
            ? $service->getCashRegisterByShop($shops, $validated['date_from'], $validated['date_to'])
            : $service->getCashRegisterByDay($shopId, $validated['date_from'], $validated['date_to']);

        $position = $isAllShops
            ? $service->getCurrentCashPosition(null, $shops)
            : $service->getCurrentCashPosition($shopId);

        $shopName = $isAllShops ? 'All Shops' : (Shop::find($shopId)->name ?? 'Unknown Shop');

        return [
            'summary'      => $summary,
            'cashRegister' => $cashRegister,
            'position'     => $position,
            'isAllShops'   => $isAllShops,
            'shopName'     => $shopName,
            'dateFrom'     => $validated['date_from'],
            'dateTo'       => $validated['date_to'],
            'viewMode'     => $viewMode,
            'reconciliation' => $service->getCashReconciliation($shopId, $validated['date_from'], $validated['date_to']),
            'comparison'     => $service->computeComparisonTotals($shopId, $validated['date_from'], $validated['date_to']),
            'checks'         => $service->getReportChecks($shopId, $validated['date_from'], $validated['date_to']),
            'generatedBy'    => $user->name,
            'showProfit'     => $request->boolean('profit'),
            'profitByProduct' => $request->boolean('profit')
                ? $service->getProfitByProduct($shopId, $validated['date_from'], $validated['date_to'])
                : collect(),
        ];
    }

    public function pdf(Request $request)
    {
        $data = $this->buildReportData($request);

        $days = Carbon::parse($data['dateFrom'])->diffInDays(Carbon::parse($data['dateTo'])) + 1;
        if ($data['viewMode'] === 'transactions' && $days > 31) {
            throw ValidationException::withMessages([
                'view' => 'Transactions PDF is limited to 31 days; choose Summary or a shorter period.',
            ]);
        }

        $shopSlug = $data['isAllShops'] ? 'all-shops' : Str::slug($data['shopName']);
        $period   = $data['dateFrom'] === $data['dateTo']
            ? $data['dateFrom']
            : $data['dateFrom'] . '_to_' . $data['dateTo'];
        $filename = "daily-report-{$shopSlug}-{$period}.pdf";

        $pdf = Pdf::loadView('pdf.daily-report', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('enable_font_subsetting', true);
        $pdf->render();
        $this->addPageNumbers($pdf);

        AuditLogger::log([
            'action'            => 'report_pdf_downloaded',
            'module'            => 'reports',
            'entity_type'       => 'DailyReport',
            'entity_identifier' => $period,
            'details'           => [
                'date_from'   => $data['dateFrom'],
                'date_to'     => $data['dateTo'],
                'shop_id'     => $data['isAllShops'] ? null : (int) str_replace('shop:', '', $request->input('shop')),
                'view'        => $data['viewMode'],
                'provisional' => collect($data['checks'])->contains('code', 'session_open'),
            ],
        ]);

        return $pdf->download($filename);
    }

    /** "Page X of Y" bottom-right on every page (canvas text; dompdf cannot do this in CSS). */
    private function addPageNumbers($pdf): void
    {
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $font   = $dompdf->getFontMetrics()->getFont('DejaVu Sans');

        // RGB floats of the --text-dim token (canvas API takes numbers, not CSS).
        $canvas->page_text($canvas->get_width() - 39.7 - 60, $canvas->get_height() - 30, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 7.5, [0.478, 0.506, 0.627]);
    }
}
