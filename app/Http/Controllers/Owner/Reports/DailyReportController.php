<?php

namespace App\Http\Controllers\Owner\Reports;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\DayClose\DailySessionService;
use Illuminate\Http\Request;

class DailyReportController extends Controller
{
    public function print(Request $request)
    {
        $user = auth()->user();

        if (! $user->isOwner()) {
            abort(403);
        }

        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
            'shop'      => 'nullable|string',
            'view'      => 'nullable|in:summary,transactions',
        ]);

        $viewMode   = $validated['view'] ?? 'summary';
        $shopFilter = $validated['shop'] ?? 'all';
        $isAllShops = $shopFilter === 'all';
        $shopId     = $isAllShops ? null : (int) str_replace('shop:', '', $shopFilter);

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

        return view('owner.reports.daily-print', [
            'summary'      => $summary,
            'cashRegister' => $cashRegister,
            'position'     => $position,
            'isAllShops'   => $isAllShops,
            'shopName'     => $shopName,
            'dateFrom'     => $validated['date_from'],
            'dateTo'       => $validated['date_to'],
            'viewMode'     => $viewMode,
        ]);
    }
}
