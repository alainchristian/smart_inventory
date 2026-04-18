<?php

namespace App\Services\DayClose;

use App\Models\BankDeposit;
use App\Models\DailySession;
use App\Models\User;

class BankDepositService
{
    /**
     * Record a bank deposit mid-session.
     *
     * @param  array{amount: int, bank_reference?: string|null, notes?: string|null}  $data
     */
    public function recordDeposit(DailySession $session, array $data, User $user): BankDeposit
    {
        if (! $session->isEditable()) {
            throw new \Exception('Cannot record a bank deposit on a closed session.');
        }

        if (! $user->isShopManager() || $user->location_id !== $session->shop_id) {
            abort(403, 'You can only record deposits for your own shop.');
        }

        $amount = (int) ($data['amount'] ?? 0);

        if ($amount <= 0) {
            throw new \Exception('Deposit amount must be greater than zero.');
        }

        return BankDeposit::create([
            'daily_session_id' => $session->id,
            'shop_id'          => $session->shop_id,
            'amount'           => $amount,
            'bank_reference'   => $data['bank_reference'] ?? null,
            'notes'            => $data['notes'] ?? null,
            'deposited_by'     => $user->id,
            'deposited_at'     => now(),
        ]);
    }

    /**
     * Void a bank deposit (soft-delete).
     */
    public function voidDeposit(BankDeposit $deposit, User $user): void
    {
        if (! $deposit->dailySession->isEditable()) {
            throw new \Exception('Cannot void a bank deposit from a closed session.');
        }

        if (! $user->isShopManager() || $user->location_id !== $deposit->shop_id) {
            abort(403, 'You can only void deposits for your own shop.');
        }

        $deposit->delete();
    }
}
