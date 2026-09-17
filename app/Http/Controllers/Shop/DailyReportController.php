<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\DayClose\DailySessionService;
use Illuminate\Http\Request;

class DailyReportController extends Controller
{
    public function print(Request $request)
    {
        $user = auth()->user();

        if (! $user->isShopManager()) {
            abort(403);
        }

        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
            'view'      => 'nullable|in:summary,transactions',
        ]);

        $viewMode = $validated['view'] ?? 'summary';

        $shopId = $user->location_id;

        $service = app(DailySessionService::class);

        $summary = $service->computeRangeSummary(
            $shopId,
            $validated['date_from'],
            $validated['date_to']
        );

        $cashRegister = $service->getCashRegisterByDay(
            $shopId,
            $validated['date_from'],
            $validated['date_to']
        );

        $position = $service->getCurrentCashPosition($shopId);

        return view('shop.reports.daily-print', [
            'summary'      => $summary,
            'cashRegister' => $cashRegister,
            'position'     => $position,
            'shop'         => Shop::find($shopId),
            'dateFrom'     => $validated['date_from'],
            'dateTo'       => $validated['date_to'],
            'viewMode'     => $viewMode,
        ]);
    }
}
