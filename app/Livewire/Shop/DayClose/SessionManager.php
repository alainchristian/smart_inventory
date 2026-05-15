<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\DailySession;
use App\Models\ExpenseRequest;
use App\Models\Sale;
use App\Services\DayClose\DailySessionService;
use App\Services\SettingsService;
use Livewire\Attributes\On;
use Livewire\Component;

class SessionManager extends Component
{
    public ?DailySession $todaySession           = null;
    public ?DailySession $unclosedPrevious       = null;
    public array         $liveSummary            = [];
    public int           $pendingRequestsCount   = 0;
    public int           $openingBalance         = 0;
    public string        $openingBalanceHint     = '';
    public bool          $showOpenForm           = false;
    public int           $warehouseDirectCount   = 0;
    public bool          $settingAllowCard        = false;
    public bool          $settingAllowBank        = false;

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user->isShopManager()) {
            abort(403);
        }

        $this->loadData();
    }

    private function loadData(): void
    {
        $user   = auth()->user();
        $shopId = $user->location_id;

        $this->unclosedPrevious = DailySession::forShop($shopId)
            ->open()
            ->where('session_date', '<', today())
            ->first();

        $this->todaySession = DailySession::forShop($shopId)
            ->forDate(today()->toDateString())
            ->first();

        if ($this->todaySession && $this->todaySession->isOpen()) {
            $this->liveSummary = app(DailySessionService::class)
                ->computeLiveSummary($this->todaySession);

            $this->warehouseDirectCount = Sale::where('shop_id', $shopId)
                ->whereDate('sale_date', today())
                ->whereNull('voided_at')
                ->whereNull('deleted_at')
                ->where('fulfillment_type', 'warehouse_direct')
                ->count();
        }

        // Pre-fill opening balance from last closed session's retained cash
        if (! $this->todaySession) {
            $lastClosed = DailySession::forShop($shopId)
                ->whereIn('status', ['closed', 'locked'])
                ->orderByDesc('session_date')
                ->first();

            if ($lastClosed && $lastClosed->cash_retained !== null) {
                $this->openingBalance    = $lastClosed->cash_retained;
                $this->openingBalanceHint = 'Carried forward from ' . $lastClosed->session_date->format('d M Y');
            }
        }

        $this->pendingRequestsCount = ExpenseRequest::pending()->forShop($shopId)->count();

        $settings = app(SettingsService::class);
        $this->settingAllowCard = $settings->allowCardPayment();
        $this->settingAllowBank = $settings->allowBankTransferPayment();
    }

    public function openDay(): void
    {
        $this->validate([
            'openingBalance' => 'required|integer|min:0',
        ]);

        $user = auth()->user();

        try {
            app(DailySessionService::class)->openSession(
                $user,
                $user->location_id,
                $this->openingBalance,
                today()->toDateString()
            );

            $this->openingBalance = 0;
            $this->showOpenForm   = false;
            $this->loadData();
            session()->flash('success', 'Day opened successfully.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    #[On('expense-added')]
    #[On('expense-voided')]
    #[On('withdrawal-added')]
    #[On('withdrawal-voided')]
    #[On('deposit-added')]
    #[On('deposit-voided')]
    public function refreshSummary(): void
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.shop.day-close.session-manager');
    }
}
