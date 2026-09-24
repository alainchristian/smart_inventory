<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\DailySession;
use App\Models\ExpenseRequest;
use App\Models\Shop;
use App\Services\DayClose\DailySessionService;
use App\Services\SettingsService;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Cash Register landing page (shop.day-close.index / shop.session.open).
 * Owns today's session state, the open-register modal and the KPI/ledger
 * figures. Activity, pending requests and the record drawer are children.
 */
class Register extends Component
{
    public bool   $showOpenModal    = false;
    public string $openingBalance   = '';
    public ?int   $suggestedBalance = null;
    public string $suggestedFrom    = '';

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user->isShopManager()) {
            abort(403);
        }

        $lastClosed = DailySession::forShop($user->location_id)
            ->whereIn('status', ['closed', 'locked'])
            ->orderByDesc('session_date')
            ->first();

        if ($lastClosed && $lastClosed->cash_retained !== null) {
            $this->suggestedBalance = (int) $lastClosed->cash_retained;
            $this->suggestedFrom    = $lastClosed->session_date->format('d M Y');
            $this->openingBalance   = (string) $this->suggestedBalance;
        }
    }

    public function openRegister(): void
    {
        $this->validate(
            ['openingBalance' => 'required|integer|min:0'],
            [],
            ['openingBalance' => 'opening balance']
        );

        $user = auth()->user();

        try {
            app(DailySessionService::class)->openSession(
                $user,
                $user->location_id,
                (int) $this->openingBalance,
                business_today()->toDateString()
            );

            $this->showOpenModal = false;
            $this->dispatch('session-opened');
            $this->dispatch('notification', ['type' => 'success', 'message' => 'Register opened.']);
        } catch (\Exception $e) {
            $this->addError('openingBalance', $e->getMessage());
        }
    }

    #[On('expense-added')]
    #[On('expense-voided')]
    #[On('expense-updated')]
    #[On('withdrawal-added')]
    #[On('withdrawal-voided')]
    #[On('withdrawal-updated')]
    #[On('deposit-added')]
    #[On('deposit-voided')]
    public function refresh(): void
    {
        // Figures are recomputed in render()
    }

    public function render()
    {
        $shopId = auth()->user()->location_id;
        $today  = business_today()->toDateString();

        $session = DailySession::forShop($shopId)
            ->forDate($today)
            ->with('openedBy', 'closedBy')
            ->first();

        // An older session left open — informational, does not block today
        $blocker = DailySession::forShop($shopId)
            ->open()
            ->where('session_date', '<', $today)
            ->orderByDesc('session_date')
            ->first();

        $summary = $session && $session->isOpen()
            ? app(DailySessionService::class)->computeLiveSummary($session)
            : null;

        $settings = app(SettingsService::class);

        return view('livewire.shop.day-close.register', [
            'session'         => $session,
            'blocker'         => $blocker,
            'summary'         => $summary,
            'pendingCount'    => ExpenseRequest::pending()->forShop($shopId)->count(),
            'allowBank'       => $settings->allowBankTransferPayment(),
            'allowCard'       => $settings->allowCardPayment(),
            'shopName'        => Shop::find($shopId)?->name,
        ]);
    }
}
