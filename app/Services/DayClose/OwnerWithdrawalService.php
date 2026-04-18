<?php

namespace App\Services\DayClose;

use App\Models\DailySession;
use App\Models\OwnerWithdrawal;
use App\Models\User;

class OwnerWithdrawalService
{
    /**
     * Record an owner withdrawal from the cash drawer.
     *
     * @param  array{amount: int, reason: string, method?: string, momo_reference?: string|null}  $data
     */
    public function recordWithdrawal(DailySession $session, array $data, User $user): OwnerWithdrawal
    {
        if (! $session->isEditable()) {
            throw new \Exception('Cannot record a withdrawal on a closed session.');
        }

        if (! $user->isShopManager() || $user->location_id !== $session->shop_id) {
            abort(403, 'You can only record withdrawals for your own shop.');
        }

        $amount = (int) ($data['amount'] ?? 0);
        $reason = trim($data['reason'] ?? '');
        $method = $data['method'] ?? 'cash';

        if ($amount <= 0) {
            throw new \Exception('Withdrawal amount must be greater than zero.');
        }

        if (strlen($reason) === 0) {
            throw new \Exception('A reason is required for owner withdrawals.');
        }

        if (! in_array($method, ['cash', 'mobile_money'])) {
            throw new \Exception('Invalid withdrawal method.');
        }

        return OwnerWithdrawal::create([
            'daily_session_id' => $session->id,
            'shop_id'          => $session->shop_id,
            'amount'           => $amount,
            'reason'           => $reason,
            'method'           => $method,
            'momo_reference'   => $method === 'mobile_money' ? ($data['momo_reference'] ?? null) : null,
            'recorded_by'      => $user->id,
            'recorded_at'      => now(),
        ]);
    }

    /**
     * Void an owner withdrawal (soft-delete).
     */
    public function voidWithdrawal(OwnerWithdrawal $withdrawal, User $user): void
    {
        if (! $withdrawal->dailySession->isEditable()) {
            throw new \Exception('Cannot void a withdrawal from a closed session.');
        }

        if (! $user->isShopManager() || $user->location_id !== $withdrawal->shop_id) {
            abort(403, 'You can only void withdrawals for your own shop.');
        }

        $withdrawal->delete();
    }
}
