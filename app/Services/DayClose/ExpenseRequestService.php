<?php

namespace App\Services\DayClose;

use App\Models\DailySession;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ExpenseRequestService
{
    /**
     * Create a new expense request (warehouse manager only).
     */
    public function createRequest(array $data, User $user): ExpenseRequest
    {
        if (! $user->isWarehouseManager()) {
            abort(403, 'Only warehouse managers can create expense requests.');
        }

        $amount = (int) ($data['amount'] ?? 0);
        $reason = trim($data['reason'] ?? '');

        if ($amount <= 0) {
            throw new \Exception('Amount must be greater than zero.');
        }

        if (empty($reason)) {
            throw new \Exception('Reason is required.');
        }

        if (empty($data['target_shop_id'])) {
            throw new \Exception('Target shop is required.');
        }

        return ExpenseRequest::create([
            'reference_number' => ExpenseRequest::generateReference(),
            'requested_by'     => $user->id,
            'warehouse_id'     => $user->location_id,
            'target_shop_id'   => $data['target_shop_id'],
            'amount'           => $amount,
            'reason'           => $reason,
            'status'           => 'pending',
        ]);
    }

    /**
     * Approve and pay a pending expense request (shop manager of target shop).
     */
    public function approveAndPay(ExpenseRequest $request, User $user): ExpenseRequest
    {
        if (! $user->isShopManager()) {
            abort(403, 'Only shop managers can approve expense requests.');
        }

        if ($user->location_id !== $request->target_shop_id) {
            abort(403, 'You can only approve requests targeted at your shop.');
        }

        if ($request->status !== 'pending') {
            throw new \Exception('Request is not pending.');
        }

        $session = DailySession::open()
            ->forShop($user->location_id)
            ->forDate(today()->toDateString())
            ->first();

        if (! $session) {
            throw new \Exception('No open session for today. Open the day before paying expense requests.');
        }

        return DB::transaction(function () use ($request, $user, $session) {
            // Find the "Warehouse Support" category
            $category = ExpenseCategory::where('name', 'Warehouse Support')->first();
            $categoryId = $category ? $category->id : ExpenseCategory::where('sort_order', 9)->value('id');

            $expense = Expense::create([
                'daily_session_id'    => $session->id,
                'expense_category_id' => $categoryId,
                'amount'              => $request->amount,
                'description'         => 'Warehouse request: ' . $request->reason . ' (Ref: ' . $request->reference_number . ')',
                'payment_method'      => 'cash',
                'expense_request_id'  => $request->id,
                'recorded_by'         => $user->id,
                'recorded_at'         => now(),
            ]);

            $request->update([
                'status'      => 'paid',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'expense_id'  => $expense->id,
                'paid_at'     => now(),
            ]);

            return $request->fresh();
        });
    }

    /**
     * Reject a pending expense request.
     */
    public function rejectRequest(ExpenseRequest $request, string $reason, User $user): ExpenseRequest
    {
        if (! $user->isShopManager() || $user->location_id !== $request->target_shop_id) {
            abort(403, 'You can only reject requests targeted at your shop.');
        }

        if ($request->status !== 'pending') {
            throw new \Exception('Request is not pending.');
        }

        $request->update([
            'status'           => 'rejected',
            'approved_by'      => $user->id,
            'approved_at'      => now(),
            'rejection_reason' => $reason,
        ]);

        return $request->fresh();
    }
}
