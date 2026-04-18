<?php

namespace App\Services\DayClose;

use App\Enums\AlertSeverity;
use App\Models\Alert;
use App\Models\DailySession;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;

class ExpenseService
{
    /**
     * Add an expense to an open session.
     */
    public function addExpense(DailySession $session, array $data, User $user): Expense
    {
        if (! $session->isEditable()) {
            throw new \Exception('Session is closed — no new expenses can be added.');
        }

        if (! $user->isShopManager() || $user->location_id !== $session->shop_id) {
            abort(403, 'You can only add expenses to your own shop session.');
        }

        $categoryId = (int) ($data['expense_category_id'] ?? 0);
        $amount     = (int) ($data['amount'] ?? 0);
        $description = trim($data['description'] ?? '');
        $paymentMethod = $data['payment_method'] ?? 'cash';

        if (! ExpenseCategory::find($categoryId)) {
            throw new \Exception('Invalid expense category.');
        }

        if ($amount <= 0) {
            throw new \Exception('Expense amount must be greater than zero.');
        }

        if (empty($description)) {
            throw new \Exception('Expense description is required.');
        }

        $validMethods = ['cash', 'mobile_money', 'bank_transfer', 'other'];
        if (! in_array($paymentMethod, $validMethods)) {
            throw new \Exception('Invalid payment method.');
        }

        $expense = Expense::create([
            'daily_session_id'    => $session->id,
            'expense_category_id' => $categoryId,
            'amount'              => $amount,
            'description'         => $description,
            'payment_method'      => $paymentMethod,
            'receipt_reference'   => $data['receipt_reference'] ?? null,
            'recorded_by'         => $user->id,
            'recorded_at'         => now(),
            'expense_request_id'  => $data['expense_request_id'] ?? null,
        ]);

        // Alert owner for large expenses
        if ($amount > 50000) {
            $category = ExpenseCategory::find($categoryId);
            $shopName = $session->shop->name ?? "Shop #{$session->shop_id}";

            $owner = \App\Models\User::where('role', 'owner')->first();
            if ($owner) {
                Alert::create([
                    'title'        => 'Large Expense Recorded',
                    'message'      => 'Large expense recorded: ' . ($category->name ?? 'Unknown') . ' — ' . number_format($amount) . ' RWF at ' . $shopName,
                    'severity'     => AlertSeverity::WARNING,
                    'entity_type'  => 'expense',
                    'entity_id'    => $expense->id,
                    'user_id'      => $owner->id,
                    'action_url'   => route('owner.finance.daily'),
                    'action_label' => 'View Session',
                ]);
            }
        }

        return $expense;
    }

    /**
     * Void (soft-delete) an expense from an open session.
     */
    public function voidExpense(Expense $expense, User $user): void
    {
        if (! $expense->dailySession->isEditable()) {
            throw new \Exception('Cannot void expense from a closed session.');
        }

        if (! $user->isShopManager() || $user->location_id !== $expense->dailySession->shop_id) {
            abort(403, 'You can only void expenses from your own shop session.');
        }

        $expense->delete();
    }
}
