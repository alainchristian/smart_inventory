<?php

namespace App\Services\DayClose;

use App\Enums\AlertSeverity;
use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\CreditRepayment;
use App\Models\DailySession;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ReturnModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DailySessionService
{
    /**
     * Open a new daily session for a shop.
     */
    public function openSession(User $user, int $shopId, int $openingBalance, string $date): DailySession
    {
        if (! $user->isShopManager()) {
            abort(403, 'Only shop managers can open sessions.');
        }

        if ($user->location_id !== $shopId) {
            abort(403, 'You can only open a session for your own shop.');
        }

        // Check for any open session across any date (not just today)
        $anyOpen = DailySession::forShop($shopId)->open()->first();
        if ($anyOpen) {
            throw new \Exception(
                'Session for ' . $anyOpen->session_date->format('d M Y') . ' is still open. Close it first.'
            );
        }

        if (DailySession::forShop($shopId)->forDate($date)->exists()) {
            throw new \Exception('A session already exists for today.');
        }

        return DB::transaction(function () use ($user, $shopId, $openingBalance, $date) {
            $session = DailySession::create([
                'session_date'    => $date,
                'shop_id'         => $shopId,
                'opening_balance' => $openingBalance,
                'opened_by'       => $user->id,
                'opened_at'       => now(),
                'status'          => 'open',
            ]);

            ActivityLog::create([
                'user_id'           => $user->id,
                'user_name'         => $user->name,
                'action'            => 'daily_session_opened',
                'entity_type'       => 'daily_session',
                'entity_id'         => $session->id,
                'entity_identifier' => $session->session_date->format('Y-m-d'),
            ]);

            return $session;
        });
    }

    /**
     * Compute live summary figures for the close wizard (not stored yet).
     * Uses sale_payments for accurate split-payment support.
     */
    public function computeLiveSummary(DailySession $session): array
    {
        $date   = $session->session_date->toDateString();
        $shopId = $session->shop_id;

        // Sales breakdown via sale_payments (handles split payments correctly)
        $saleTotals = DB::table('sale_payments')
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->where('sales.shop_id', $shopId)
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->whereDate('sales.sale_date', $date)
            ->selectRaw("
                SUM(CASE WHEN sale_payments.payment_method = 'cash'          THEN sale_payments.amount ELSE 0 END) as cash,
                SUM(CASE WHEN sale_payments.payment_method = 'mobile_money'  THEN sale_payments.amount ELSE 0 END) as momo,
                SUM(CASE WHEN sale_payments.payment_method = 'card'          THEN sale_payments.amount ELSE 0 END) as card,
                SUM(CASE WHEN sale_payments.payment_method = 'bank_transfer' THEN sale_payments.amount ELSE 0 END) as bank_transfer,
                SUM(CASE WHEN sale_payments.payment_method = 'credit'        THEN sale_payments.amount ELSE 0 END) as credit_sales,
                SUM(CASE WHEN sale_payments.payment_method NOT IN ('cash','mobile_money','card','bank_transfer','credit') THEN sale_payments.amount ELSE 0 END) as other,
                SUM(sale_payments.amount) as total,
                COUNT(DISTINCT sales.id) as transaction_count
            ")->first();

        // Cash refunds (cash returns only, no exchanges)
        $cashRefunds = ReturnModel::where('shop_id', $shopId)
            ->whereDate('processed_at', $date)
            ->whereNull('deleted_at')
            ->where('refund_method', 'cash')
            ->where('is_exchange', false)
            ->sum('refund_amount');

        // Expenses in this session
        $expensesQuery = $session->expenses()->whereNull('deleted_at');
        $totalExpenses = (int) (clone $expensesQuery)->sum('amount');
        $cashExpenses  = (int) (clone $expensesQuery)->where('payment_method', 'cash')->sum('amount');
        $momoExpenses  = (int) (clone $expensesQuery)->where('payment_method', 'mobile_money')->sum('amount');
        $expenseCount  = (int) (clone $expensesQuery)->count();

        // Owner withdrawals
        $withdrawalQuery    = $session->ownerWithdrawals()->whereNull('deleted_at');
        $totalWithdrawals   = (int) $withdrawalQuery->sum('amount');
        $cashWithdrawals    = (int) (clone $withdrawalQuery)->where('method', 'cash')->sum('amount');
        $momoWithdrawals    = (int) (clone $withdrawalQuery)->where('method', 'mobile_money')->sum('amount');
        $withdrawalCount    = (int) $withdrawalQuery->count();

        // Bank deposits split by source (cash drawer vs MoMo wallet)
        $depositsQuery = $session->bankDeposits()->whereNull('deleted_at');
        $cashDeposits  = (int) (clone $depositsQuery)->where('source', 'cash')->sum('amount');
        $momoDeposits  = (int) (clone $depositsQuery)->where('source', 'mobile_money')->sum('amount');
        $totalDeposits = $cashDeposits + $momoDeposits;
        $depositCount  = (int) $depositsQuery->count();

        // Credit repayments received today (split by payment method)
        $repaymentQuery  = CreditRepayment::where('shop_id', $shopId)
            ->whereDate('repayment_date', $date);
        $cashRepayments  = (int) (clone $repaymentQuery)->where('payment_method', 'cash')->sum('amount');
        $momoRepayments  = (int) (clone $repaymentQuery)->where('payment_method', 'mobile_money')->sum('amount');
        $totalRepayments = (int) $repaymentQuery->sum('amount');

        // expected_cash: cash sales + cash repayments in; refunds/expenses/withdrawals/deposits out
        $expectedCash = $session->opening_balance
            + (int) ($saleTotals->cash ?? 0)
            + $cashRepayments
            - (int) $cashRefunds
            - (int) $cashExpenses
            - (int) $cashWithdrawals
            - (int) $cashDeposits;

        // MoMo available balance (for deposit validation)
        $momoAvailable = (int) ($saleTotals->momo ?? 0)
            + $momoRepayments
            - $momoExpenses
            - $momoWithdrawals
            - $momoDeposits;

        return [
            'opening_balance'              => (int) $session->opening_balance,
            'total_sales_cash'             => (int) ($saleTotals->cash          ?? 0),
            'total_sales_momo'             => (int) ($saleTotals->momo          ?? 0),
            'total_sales_card'             => (int) ($saleTotals->card          ?? 0),
            'total_sales_bank_transfer'    => (int) ($saleTotals->bank_transfer ?? 0),
            'total_sales_credit'           => (int) ($saleTotals->credit_sales  ?? 0),
            'total_sales_other'            => (int) ($saleTotals->other         ?? 0),
            'total_sales'                  => (int) ($saleTotals->total         ?? 0),
            'transaction_count'     => (int) ($saleTotals->transaction_count ?? 0),
            'total_refunds_cash'    => (int) $cashRefunds,
            'total_expenses'        => $totalExpenses,
            'total_expenses_cash'   => $cashExpenses,
            'total_expenses_momo'   => $momoExpenses,
            'expense_count'         => $expenseCount,
            'total_withdrawals'     => $totalWithdrawals,
            'total_withdrawals_cash'=> $cashWithdrawals,
            'total_withdrawals_momo'=> $momoWithdrawals,
            'withdrawal_count'      => $withdrawalCount,
            'total_bank_deposits'   => $totalDeposits,
            'cash_deposits'         => $cashDeposits,
            'momo_deposits'         => $momoDeposits,
            'bank_deposit_count'    => $depositCount,
            'total_repayments'      => $totalRepayments,
            'total_repayments_cash' => $cashRepayments,
            'total_repayments_momo' => $momoRepayments,
            'expected_cash'         => (int) $expectedCash,
            'momo_available'        => (int) $momoAvailable,
        ];
    }

    /**
     * Close a session with the provided closing data.
     */
    public function closeSession(DailySession $session, array $data, User $user): DailySession
    {
        if (! $session->isOpen()) {
            throw new \Exception('Session is not open.');
        }

        if (! $user->isShopManager() || $user->location_id !== $session->shop_id) {
            abort(403, 'You can only close sessions for your own shop.');
        }

        $actualCash      = (int) ($data['actual_cash_counted'] ?? 0);
        $cashToOwnerMomo = (int) ($data['cash_to_owner_momo'] ?? 0);

        if ($actualCash < 0) {
            throw new \Exception('Actual cash counted cannot be negative.');
        }

        if ($cashToOwnerMomo < 0 || $cashToOwnerMomo > $actualCash) {
            throw new \Exception(
                'MoMo transfer to owner (' . number_format($cashToOwnerMomo) . ') cannot exceed actual cash counted (' .
                number_format($actualCash) . ')'
            );
        }

        $cashRetained = $actualCash - $cashToOwnerMomo;

        return DB::transaction(function () use ($session, $data, $user, $actualCash, $cashToOwnerMomo, $cashRetained) {
            $summary  = $this->computeLiveSummary($session);
            $variance = $actualCash - $summary['expected_cash'];

            $session->update([
                'transaction_count'           => $summary['transaction_count'],
                'total_sales_cash'            => $summary['total_sales_cash'],
                'total_sales_momo'            => $summary['total_sales_momo'],
                'total_sales_card'            => $summary['total_sales_card'],
                'total_sales_bank_transfer'   => $summary['total_sales_bank_transfer'],
                'total_sales_credit'          => $summary['total_sales_credit'],
                'total_sales_other'           => $summary['total_sales_other'],
                'total_sales'                 => $summary['total_sales'],
                'total_refunds_cash'     => $summary['total_refunds_cash'],
                'total_expenses'         => $summary['total_expenses'],
                'total_expenses_cash'    => $summary['total_expenses_cash'],
                'total_expenses_momo'    => $summary['total_expenses_momo'],
                'total_withdrawals'      => $summary['total_withdrawals'],
                'total_withdrawals_cash' => $summary['total_withdrawals_cash'],
                'total_withdrawals_momo' => $summary['total_withdrawals_momo'],
                'total_bank_deposits'    => $summary['total_bank_deposits'],
                'bank_deposit_count'     => $summary['bank_deposit_count'],
                'cash_deposits'          => $summary['cash_deposits'],
                'momo_deposits'          => $summary['momo_deposits'],
                'total_repayments'       => $summary['total_repayments'],
                'total_repayments_cash'  => $summary['total_repayments_cash'],
                'total_repayments_momo'  => $summary['total_repayments_momo'],
                'expected_cash'          => $summary['expected_cash'],
                'actual_cash_counted'    => $actualCash,
                'cash_variance'          => $variance,
                'cash_to_owner_momo'     => $cashToOwnerMomo,
                'owner_momo_reference'   => $data['owner_momo_reference'] ?? null,
                'momo_settled'           => (int) ($data['momo_settled'] ?? 0),
                'momo_settled_ref'       => $data['momo_settled_ref'] ?? null,
                'card_settled'           => (int) ($data['card_settled'] ?? 0),
                'card_settled_ref'       => $data['card_settled_ref'] ?? null,
                'other_settled'              => (int) ($data['other_settled'] ?? 0),
                'other_settled_ref'          => $data['other_settled_ref'] ?? null,
                'bank_transfer_settled'      => (int) ($data['bank_transfer_settled'] ?? 0),
                'bank_transfer_settled_ref'  => $data['bank_transfer_settled_ref'] ?? null,
                'cash_retained'              => $cashRetained,
                'notes'                  => $data['notes'] ?? null,
                'status'                 => 'closed',
                'closed_by'              => $user->id,
                'closed_at'              => now(),
            ]);

            $shopName = $session->shop->name ?? "Shop #{$session->shop_id}";
            $owner    = User::where('role', 'owner')->first();

            // Shortage: auto-create locked Expense + alert
            if ($variance < 0) {
                $shortageCategory = ExpenseCategory::firstOrCreate(
                    ['name' => 'Cash Shortage'],
                    [
                        'description' => 'System-generated category for cash shortages recorded on day close.',
                        'applies_to'  => 'shop',
                        'is_active'   => true,
                        'sort_order'  => 9999,
                    ]
                );
                Expense::create([
                    'daily_session_id'    => $session->id,
                    'expense_category_id' => $shortageCategory->id,
                    'amount'              => abs($variance),
                    'description'         => 'Cash shortage recorded on day close — auto-generated by system',
                    'payment_method'      => 'cash',
                    'is_system_generated' => true,
                    'recorded_by'         => $user->id,
                    'recorded_at'         => now(),
                ]);

                Alert::create([
                    'title'        => 'Cash Shortage — ' . $shopName,
                    'message'      => 'Shortage of ' . number_format(abs($variance)) . ' RWF on ' . $session->session_date->format('d M Y'),
                    'severity'     => abs($variance) >= 20000 ? AlertSeverity::CRITICAL : AlertSeverity::WARNING,
                    'entity_type'  => 'daily_session',
                    'entity_id'    => $session->id,
                    'user_id'      => $owner?->id,
                    'action_url'   => route('owner.finance.daily'),
                    'action_label' => 'View Session',
                ]);
            }

            // Surplus: alert only — no accounting entry
            if ($variance > 0) {
                Alert::create([
                    'title'        => 'Cash Surplus — ' . $shopName,
                    'message'      => 'Surplus of ' . number_format($variance) . ' RWF on ' . $session->session_date->format('d M Y') . '. Please review.',
                    'severity'     => AlertSeverity::WARNING,
                    'entity_type'  => 'daily_session',
                    'entity_id'    => $session->id,
                    'user_id'      => $owner?->id,
                    'action_url'   => route('owner.finance.daily'),
                    'action_label' => 'View Session',
                ]);
            }

            ActivityLog::create([
                'user_id'           => $user->id,
                'user_name'         => $user->name,
                'action'            => 'daily_session_closed',
                'entity_type'       => 'daily_session',
                'entity_id'         => $session->id,
                'entity_identifier' => $session->session_date->format('Y-m-d'),
                'details'           => [
                    'variance'          => $variance,
                    'cash_to_owner_momo'=> $cashToOwnerMomo,
                    'retained'          => $cashRetained,
                ],
            ]);

            return $session->fresh();
        });
    }

    /**
     * Lock a closed session (owner only).
     */
    public function lockSession(DailySession $session, User $user): DailySession
    {
        if (! $user->isOwner()) {
            abort(403, 'Only the owner can lock sessions.');
        }

        if (! $session->isClosed()) {
            throw new \Exception('Only closed sessions can be locked.');
        }

        $session->update([
            'status'    => 'locked',
            'locked_by' => $user->id,
            'locked_at' => now(),
        ]);

        ActivityLog::create([
            'user_id'           => $user->id,
            'user_name'         => $user->name,
            'action'            => 'daily_session_locked',
            'entity_type'       => 'daily_session',
            'entity_id'         => $session->id,
            'entity_identifier' => $session->session_date->format('Y-m-d'),
        ]);

        return $session->fresh();
    }
}
