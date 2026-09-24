<?php

namespace App\Livewire\Shop\Reports;

use App\Services\DayClose\DailySessionService;
use App\Services\SettingsService;
use Carbon\Carbon;
use Livewire\Component;

class DailyReport extends Component
{
    public string $preset   = 'today';
    public string $dateFrom = '';
    public string $dateTo   = '';
    /** 'summary' = totals & breakdowns; 'transactions' = line-by-line sales/expenses. */
    public string $viewMode = 'summary';

    public bool $settingAllowCard         = false;
    public bool $settingAllowBankTransfer = false;

    protected $queryString = [
        'dateFrom' => ['except' => ''],
        'dateTo'   => ['except' => ''],
        'viewMode' => ['except' => 'summary'],
    ];

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['summary', 'transactions'], true) ? $mode : 'summary';
    }

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user->isShopManager()) {
            abort(403);
        }

        $svc = app(SettingsService::class);
        $this->settingAllowCard         = $svc->allowCardPayment();
        $this->settingAllowBankTransfer = $svc->allowBankTransferPayment();

        if ($this->dateFrom === '' || $this->dateTo === '') {
            $this->resolveDates();
        } else {
            $this->preset = 'custom';
        }
    }

    public function setPreset(string $key): void
    {
        $this->preset = $key;
        $this->resolveDates();
    }

    public function updatedDateFrom(): void
    {
        $this->preset = 'custom';
    }

    public function updatedDateTo(): void
    {
        $this->preset = 'custom';
    }

    private function resolveDates(): void
    {
        $today = business_today();

        [$this->dateFrom, $this->dateTo] = match ($this->preset) {
            'yesterday'    => [$today->copy()->subDay()->toDateString(), $today->copy()->subDay()->toDateString()],
            'this_week'    => [$today->copy()->startOfWeek()->toDateString(), $today->toDateString()],
            'this_month'   => [$today->copy()->startOfMonth()->toDateString(), $today->toDateString()],
            'last_month'   => [$today->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(), $today->copy()->subMonthNoOverflow()->endOfMonth()->toDateString()],
            'this_quarter' => [$today->copy()->startOfQuarter()->toDateString(), $today->toDateString()],
            'this_year'    => [$today->copy()->startOfYear()->toDateString(), $today->toDateString()],
            default        => [$today->toDateString(), $today->toDateString()],
        };
    }

    public function getActiveDateRangeLabelProperty(): string
    {
        $from = Carbon::parse($this->dateFrom);
        $to   = Carbon::parse($this->dateTo);

        if ($from->isSameDay($to)) {
            return $from->format('d M Y');
        }

        return $from->format('d M') . ' – ' . $to->format('d M Y');
    }

    public function getSummaryProperty(): array
    {
        $shopId = auth()->user()->location_id;

        $summary = app(DailySessionService::class)->computeRangeSummary($shopId, $this->dateFrom, $this->dateTo);

        // Shop managers never see profit/cost figures — strip them at the source.
        unset($summary['total_cogs'], $summary['gross_profit'], $summary['net_for_period']);

        return $summary;
    }

    public function getCashRegisterProperty(): \Illuminate\Support\Collection
    {
        $shopId = auth()->user()->location_id;

        return app(DailySessionService::class)->getCashRegisterByDay($shopId, $this->dateFrom, $this->dateTo);
    }

    /** Real-time snapshot of what the shop currently holds — cash on hand
     *  plus outstanding customer credit — independent of the date filter. */
    public function getPositionProperty(): array
    {
        $shopId = auth()->user()->location_id;

        return app(DailySessionService::class)->getCurrentCashPosition($shopId);
    }

    public function getReconciliationProperty(): \Illuminate\Support\Collection
    {
        return app(DailySessionService::class)->getCashReconciliation(auth()->user()->location_id, $this->dateFrom, $this->dateTo);
    }

    public function getComparisonProperty(): array
    {
        return app(DailySessionService::class)->computeComparisonTotals(auth()->user()->location_id, $this->dateFrom, $this->dateTo);
    }

    public function getChecksProperty(): array
    {
        return app(DailySessionService::class)->getReportChecks(auth()->user()->location_id, $this->dateFrom, $this->dateTo);
    }

    public function render()
    {
        return view('livewire.shop.reports.daily-report', [
            'summary'      => $this->summary,
            'cashRegister' => $this->cashRegister,
            'position'     => $this->position,
            'reconciliation' => $this->reconciliation,
            'comparison'     => $this->comparison,
            'checks'         => $this->checks,
        ]);
    }
}
