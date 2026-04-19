<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\DailySession;
use App\Services\DayClose\DailySessionService;
use Livewire\Attributes\On;
use Livewire\Component;

class CloseWizard extends Component
{
    public int $dailySessionId = 0;
    public int $currentStep = 1;
    public array $summary = [];

    // Step 3 inputs
    public int $actualCashCounted = 0;

    // Step 4 inputs — disposition
    public int    $cashToOwnerMomo   = 0;
    public string $ownerMomoReference = '';
    public int    $cashRetained       = 0;
    public string $notes              = '';

    // Step 4 — non-cash channel settlements
    public int    $momoSettled              = 0;
    public string $momoSettledRef           = '';
    public int    $cardSettled              = 0;
    public string $cardSettledRef           = '';
    public int    $otherSettled             = 0;
    public string $otherSettledRef          = '';
    public int    $bankTransferSettled      = 0;
    public string $bankTransferSettledRef   = '';

    // Computed
    public int $cashVariance = 0;

    public function mount(?int $dailySessionId = null): void
    {
        $user = auth()->user();

        if ($dailySessionId) {
            $session = DailySession::findOrFail($dailySessionId);
        } else {
            if (! $user->isShopManager()) {
                session()->flash('error', 'Please select a specific session to close.');
                $this->redirect(route('shop.day-close.index'));
                return;
            }

            $session = DailySession::forShop($user->location_id)
                ->forDate(today())
                ->first();

            if (! $session) {
                session()->flash('error', 'No open session found for today. Please open the day first.');
                $this->redirect(route('shop.day-close.index'));
                return;
            }
        }

        if (! $session->isEditable()) {
            session()->flash('error', 'This session is already closed or locked.');
            $this->redirect(route('shop.day-close.index'));
            return;
        }

        if (! $user->isOwner() && $user->location_id !== $session->shop_id) {
            abort(403);
        }

        $this->dailySessionId = $session->id;
        $this->summary        = app(DailySessionService::class)->computeLiveSummary($session);

        // Pre-fill non-cash settlements from live summary
        $this->momoSettled          = $this->summary['total_sales_momo']          ?? 0;
        $this->cardSettled          = $this->summary['total_sales_card']          ?? 0;
        $this->otherSettled         = $this->summary['total_sales_other']         ?? 0;
        $this->bankTransferSettled  = $this->summary['total_sales_bank_transfer'] ?? 0;
    }

    public function nextStep(): void
    {
        if ($this->currentStep === 3) {
            $this->validate(['actualCashCounted' => 'required|integer|min:0']);
            $this->cashVariance = $this->actualCashCounted - ($this->summary['expected_cash'] ?? 0);
        }

        if ($this->currentStep === 4) {
            if ($this->cashToOwnerMomo > $this->actualCashCounted) {
                $this->addError('cashToOwnerMomo', 'MoMo transfer cannot exceed actual cash counted.');
                return;
            }
        }

        if ($this->currentStep < 4) {
            $this->currentStep++;
        }
    }

    public function prevStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function updatedActualCashCounted(): void
    {
        $this->cashVariance  = $this->actualCashCounted - ($this->summary['expected_cash'] ?? 0);
        $this->cashRetained  = max(0, $this->actualCashCounted - $this->cashToOwnerMomo);
    }

    public function updatedCashToOwnerMomo(): void
    {
        $this->cashRetained = max(0, $this->actualCashCounted - $this->cashToOwnerMomo);
    }

    public function submitClose(): void
    {
        if ($this->currentStep !== 4) {
            return;
        }

        $this->validate([
            'actualCashCounted' => 'required|integer|min:0',
            'cashToOwnerMomo'   => 'required|integer|min:0',
        ]);

        if ($this->cashToOwnerMomo > $this->actualCashCounted) {
            $this->addError('cashToOwnerMomo', 'MoMo transfer cannot exceed actual cash counted.');
            return;
        }

        try {
            $session = DailySession::findOrFail($this->dailySessionId);

            app(DailySessionService::class)->closeSession($session, [
                'actual_cash_counted'       => $this->actualCashCounted,
                'cash_to_owner_momo'        => $this->cashToOwnerMomo,
                'owner_momo_reference'      => $this->ownerMomoReference,
                'momo_settled'              => $this->momoSettled,
                'momo_settled_ref'          => $this->momoSettledRef,
                'card_settled'              => $this->cardSettled,
                'card_settled_ref'          => $this->cardSettledRef,
                'other_settled'             => $this->otherSettled,
                'other_settled_ref'         => $this->otherSettledRef,
                'bank_transfer_settled'     => $this->bankTransferSettled,
                'bank_transfer_settled_ref' => $this->bankTransferSettledRef,
                'notes'                     => $this->notes,
            ], auth()->user());

            session()->flash('success', 'Day closed successfully.');
            $this->redirect(route('shop.dashboard'));
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
    public function reloadSummary(): void
    {
        $session = DailySession::find($this->dailySessionId);
        if ($session) {
            $this->summary      = app(DailySessionService::class)->computeLiveSummary($session);
            $this->cashVariance = $this->actualCashCounted - ($this->summary['expected_cash'] ?? 0);
            $this->cashRetained = max(0, $this->actualCashCounted - $this->cashToOwnerMomo);
        }
    }

    public function render()
    {
        $session = $this->dailySessionId
            ? DailySession::find($this->dailySessionId)
            : null;

        return view('livewire.shop.day-close.close-wizard', compact('session'));
    }
}
