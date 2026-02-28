<?php

namespace App\Livewire\Dashboard;

use App\Enums\BoxStatus;
use App\Enums\TransferStatus;
use App\Models\Alert;
use App\Models\Box;
use App\Models\Sale;
use App\Models\Transfer;
use Carbon\Carbon;
use Livewire\Component;

class KpiRow extends Component
{
    /**
     * Get total active boxes count with delta
     */
    public function getTotalBoxesProperty(): array
    {
        $currentCount = Box::whereIn('status', [
            BoxStatus::FULL,
            BoxStatus::PARTIAL,
            BoxStatus::DAMAGED
        ])->count();

        // Get last week's count
        $lastWeekCount = Box::whereIn('status', [
            BoxStatus::FULL,
            BoxStatus::PARTIAL,
            BoxStatus::DAMAGED
        ])
            ->where('created_at', '<=', Carbon::now()->subWeek())
            ->count();

        $delta = $currentCount - $lastWeekCount;
        $deltaPercentage = $lastWeekCount > 0 ? (($delta / $lastWeekCount) * 100) : 0;

        return [
            'value' => $currentCount,
            'delta' => $delta,
            'deltaPercentage' => round($deltaPercentage, 1),
        ];
    }

    /**
     * Get today's sales revenue with delta
     */
    public function getTodaysSalesProperty(): array
    {
        $todayRevenue = Sale::notVoided()
            ->whereDate('sale_date', Carbon::today())
            ->sum('total');

        // Get yesterday's revenue for comparison
        $yesterdayRevenue = Sale::notVoided()
            ->whereDate('sale_date', Carbon::yesterday())
            ->sum('total');

        $delta = $todayRevenue - $yesterdayRevenue;
        $deltaPercentage = $yesterdayRevenue > 0 ? (($delta / $yesterdayRevenue) * 100) : 0;

        return [
            'value' => $todayRevenue / 100, // Convert cents to dollars
            'delta' => $delta / 100,
            'deltaPercentage' => round($deltaPercentage, 1),
        ];
    }

    /**
     * Get active transfers count
     */
    public function getActiveTransfersProperty(): array
    {
        $activeStatuses = [
            TransferStatus::PENDING,
            TransferStatus::APPROVED,
            TransferStatus::IN_TRANSIT,
            TransferStatus::DELIVERED,
        ];

        $currentCount = Transfer::whereIn('status', $activeStatuses)->count();

        // Get last week's count
        $lastWeekCount = Transfer::whereIn('status', $activeStatuses)
            ->where('created_at', '<=', Carbon::now()->subWeek())
            ->count();

        $delta = $currentCount - $lastWeekCount;
        $deltaPercentage = $lastWeekCount > 0 ? (($delta / $lastWeekCount) * 100) : 0;

        return [
            'value' => $currentCount,
            'delta' => $delta,
            'deltaPercentage' => round($deltaPercentage, 1),
        ];
    }

    /**
     * Get critical alerts count
     */
    public function getCriticalAlertsProperty(): array
    {
        $currentCount = Alert::unresolved()
            ->notDismissed()
            ->critical()
            ->count();

        // Get last week's count
        $lastWeekCount = Alert::critical()
            ->where('created_at', '<=', Carbon::now()->subWeek())
            ->where('is_resolved', false)
            ->where('is_dismissed', false)
            ->count();

        $delta = $currentCount - $lastWeekCount;
        $deltaPercentage = $lastWeekCount > 0 ? (($delta / $lastWeekCount) * 100) : 0;

        return [
            'value' => $currentCount,
            'delta' => $delta,
            'deltaPercentage' => round($deltaPercentage, 1),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.kpi-row');
    }
}
