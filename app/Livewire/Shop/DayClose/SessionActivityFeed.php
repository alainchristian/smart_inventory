<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\BankDeposit;
use App\Models\CreditRepayment;
use App\Models\DailySession;
use App\Models\Expense;
use App\Models\OwnerWithdrawal;
use App\Models\ReturnModel;
use App\Models\Sale;
use App\Services\DayClose\BankDepositService;
use App\Services\DayClose\ExpenseService;
use App\Services\DayClose\OwnerWithdrawalService;
use Livewire\Attributes\On;
use Livewire\Component;

class SessionActivityFeed extends Component
{
    public int $dailySessionId = 0;

    public function mount(int $dailySessionId): void
    {
        $this->dailySessionId = $dailySessionId;
    }

    #[On('expense-added')]
    #[On('expense-voided')]
    #[On('withdrawal-added')]
    #[On('withdrawal-voided')]
    #[On('deposit-added')]
    #[On('deposit-voided')]
    public function refresh(): void
    {
        // Re-render loads fresh data from render()
    }

    public function voidExpense(int $expenseId): void
    {
        $expense = Expense::where('id', $expenseId)
            ->where('daily_session_id', $this->dailySessionId)
            ->firstOrFail();

        try {
            app(ExpenseService::class)->voidExpense($expense, auth()->user());
            $this->dispatch('expense-voided');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function voidWithdrawal(int $withdrawalId): void
    {
        $withdrawal = OwnerWithdrawal::where('id', $withdrawalId)
            ->where('daily_session_id', $this->dailySessionId)
            ->firstOrFail();

        try {
            app(OwnerWithdrawalService::class)->voidWithdrawal($withdrawal, auth()->user());
            $this->dispatch('withdrawal-voided');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function voidDeposit(int $depositId): void
    {
        $deposit = BankDeposit::where('id', $depositId)
            ->where('daily_session_id', $this->dailySessionId)
            ->firstOrFail();

        try {
            app(BankDepositService::class)->voidDeposit($deposit, auth()->user());
            $this->dispatch('deposit-voided');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $session = DailySession::findOrFail($this->dailySessionId);
        $date    = $session->session_date->toDateString();
        $shopId  = $session->shop_id;
        $isOpen  = $session->isOpen();

        // Build unified activity list
        $activities = collect();

        // Sales
        Sale::notVoided()
            ->where('shop_id', $shopId)
            ->whereDate('sale_date', $date)
            ->with('customer')
            ->get()
            ->each(function ($sale) use (&$activities) {
                $activities->push([
                    'type'      => 'sale',
                    'time'      => $sale->created_at,
                    'label'     => $sale->customer ? 'Sale — ' . $sale->customer->name : 'Sale',
                    'amount'    => $sale->total,
                    'id'        => $sale->id,
                    'voidable'  => false,
                    'system'    => false,
                ]);
            });

        // Returns
        ReturnModel::where('shop_id', $shopId)
            ->whereDate('processed_at', $date)
            ->whereNull('deleted_at')
            ->get()
            ->each(function ($ret) use (&$activities) {
                $activities->push([
                    'type'      => 'return',
                    'time'      => $ret->processed_at,
                    'label'     => 'Return' . ($ret->is_exchange ? ' (exchange)' : ''),
                    'amount'    => $ret->refund_amount,
                    'id'        => $ret->id,
                    'voidable'  => false,
                    'system'    => false,
                ]);
            });

        // Expenses
        Expense::with('category')
            ->where('daily_session_id', $this->dailySessionId)
            ->whereNull('deleted_at')
            ->get()
            ->each(function ($expense) use (&$activities, $isOpen) {
                $activities->push([
                    'type'      => 'expense',
                    'time'      => $expense->recorded_at,
                    'label'     => $expense->is_system_generated ? 'Auto: Cash Shortage' : ($expense->category->name ?? 'Expense'),
                    'amount'    => $expense->amount,
                    'id'        => $expense->id,
                    'voidable'  => $isOpen && ! $expense->is_system_generated,
                    'system'    => $expense->is_system_generated,
                ]);
            });

        // Owner withdrawals
        OwnerWithdrawal::where('daily_session_id', $this->dailySessionId)
            ->whereNull('deleted_at')
            ->get()
            ->each(function ($withdrawal) use (&$activities, $isOpen) {
                $activities->push([
                    'type'      => 'withdrawal',
                    'time'      => $withdrawal->recorded_at,
                    'label'     => 'Owner Withdrawal',
                    'amount'    => $withdrawal->amount,
                    'id'        => $withdrawal->id,
                    'voidable'  => $isOpen,
                    'system'    => false,
                ]);
            });

        // Credit repayments
        CreditRepayment::where('daily_session_id', $this->dailySessionId)
            ->with('customer')
            ->get()
            ->each(function ($repayment) use (&$activities) {
                $activities->push([
                    'type'     => 'repayment',
                    'time'     => $repayment->created_at,
                    'label'    => 'Repayment' . ($repayment->customer ? ' — ' . $repayment->customer->name : ''),
                    'amount'   => $repayment->amount,
                    'id'       => $repayment->id,
                    'voidable' => false,
                    'system'   => false,
                ]);
            });

        // Bank deposits
        BankDeposit::where('daily_session_id', $this->dailySessionId)
            ->whereNull('deleted_at')
            ->get()
            ->each(function ($deposit) use (&$activities, $isOpen) {
                $label = 'Bank Deposit';
                if ($deposit->bank_reference) {
                    $label .= ' — ' . $deposit->bank_reference;
                }
                $activities->push([
                    'type'      => 'bank_deposit',
                    'time'      => $deposit->deposited_at,
                    'label'     => $label,
                    'amount'    => $deposit->amount,
                    'id'        => $deposit->id,
                    'voidable'  => $isOpen,
                    'system'    => false,
                ]);
            });

        $activities = $activities->sortByDesc('time')->values();

        return view('livewire.shop.day-close.session-activity-feed', compact('activities', 'isOpen'));
    }
}
