<?php

namespace App\Livewire\Owner\Reports;

use App\Models\Shop;
use App\Services\DayClose\DailySessionService;
use App\Services\SettingsService;
use Carbon\Carbon;
use Livewire\Component;

class DailyReport extends Component
{
    public string $preset         = 'today';
    public string $dateFrom       = '';
    public string $dateTo         = '';
    public string $locationFilter = 'all';
    /** 'summary' = totals & breakdowns; 'transactions' = line-by-line sales/expenses. */
    public string $viewMode = 'summary';

    /** Profit figures (COGS, gross/net profit) are hidden until an owner/admin switches them on. */
    public bool $showProfit = false;

    public bool $settingAllowCard         = false;
    public bool $settingAllowBankTransfer = false;

    protected $queryString = [
        'dateFrom'       => ['except' => ''],
        'dateTo'         => ['except' => ''],
        'locationFilter' => ['except' => 'all'],
        'viewMode'       => ['except' => 'summary'],
    ];

    public function toggleProfit(): void
    {
        $user = auth()->user();
        abort_unless($user->isOwner() || $user->isAdmin(), 403);

        $this->showProfit = ! $this->showProfit;
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['summary', 'transactions'], true) ? $mode : 'summary';
    }

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user->isOwner() && ! $user->isAdmin()) {
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

    /** null = every shop combined ("All Shops"); otherwise the selected shop's id. */
    private function resolveShopId(): ?int
    {
        if ($this->locationFilter === 'all') {
            return null;
        }

        return (int) str_replace('shop:', '', $this->locationFilter);
    }

    public function getShopsProperty()
    {
        return Shop::orderBy('name')->get(['id', 'name']);
    }

    public function getSelectedShopNameProperty(): string
    {
        if ($this->locationFilter === 'all') {
            return 'All Shops';
        }

        $shop = Shop::find($this->resolveShopId());

        return $shop ? $shop->name : 'Unknown Shop';
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
        return app(DailySessionService::class)->computeRangeSummary($this->resolveShopId(), $this->dateFrom, $this->dateTo);
    }

    public function getCashRegisterProperty(): \Illuminate\Support\Collection
    {
        $svc = app(DailySessionService::class);

        if ($this->locationFilter === 'all') {
            return $svc->getCashRegisterByShop($this->shops, $this->dateFrom, $this->dateTo);
        }

        return $svc->getCashRegisterByDay($this->resolveShopId(), $this->dateFrom, $this->dateTo);
    }

    /** Real-time snapshot of what the business currently holds — cash on
     *  hand plus outstanding customer credit — independent of the date filter. */
    public function getPositionProperty(): array
    {
        $svc = app(DailySessionService::class);

        if ($this->locationFilter === 'all') {
            return $svc->getCurrentCashPosition(null, $this->shops);
        }

        return $svc->getCurrentCashPosition($this->resolveShopId());
    }

    /** Per-product cost/profit — only computed when an owner/admin has switched profit on. */
    public function getProfitByProductProperty(): \Illuminate\Support\Collection
    {
        if (! $this->showProfit) {
            return collect();
        }

        return app(DailySessionService::class)->getProfitByProduct($this->resolveShopId(), $this->dateFrom, $this->dateTo);
    }

    public function getReconciliationProperty(): \Illuminate\Support\Collection
    {
        return app(DailySessionService::class)->getCashReconciliation($this->resolveShopId(), $this->dateFrom, $this->dateTo);
    }

    public function getComparisonProperty(): array
    {
        return app(DailySessionService::class)->computeComparisonTotals($this->resolveShopId(), $this->dateFrom, $this->dateTo);
    }

    public function getChecksProperty(): array
    {
        return app(DailySessionService::class)->getReportChecks($this->resolveShopId(), $this->dateFrom, $this->dateTo);
    }

    public function render()
    {
        return view('livewire.owner.reports.daily-report', [
            'summary'      => $this->summary,
            'cashRegister' => $this->cashRegister,
            'position'     => $this->position,
            'reconciliation' => $this->reconciliation,
            'comparison'     => $this->comparison,
            'checks'         => $this->checks,
            'profitByProduct' => $this->profitByProduct,
        ]);
    }
}
