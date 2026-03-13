<?php

namespace App\Services\Sales;

use App\Models\CustomerCreditAccount;
use App\Models\CreditRepayment;
use App\Enums\PaymentMethod;
use Illuminate\Support\Facades\DB;

class CreditService
{
    /**
     * Check if a customer phone has any outstanding credit balance.
     * Returns ['has_balance' => bool, 'balance' => int, 'account' => model|null]
     */
    public function checkOutstandingBalance(string $phone): array
    {
        $account = CustomerCreditAccount::where('customer_phone', $phone)->first();

        if (!$account) {
            return ['has_balance' => false, 'balance' => 0, 'account' => null];
        }

        return [
            'has_balance' => $account->outstanding_balance > 0,
            'balance'     => $account->outstanding_balance,
            'account'     => $account,
        ];
    }

    /**
     * Record credit extended to a customer as part of a sale.
     * Updates or creates the credit account and updates outstanding_balance.
     */
    public function extendCredit(
        string $phone,
        ?string $name,
        int $amount,
        int $saleId,
        int $shopId
    ): CustomerCreditAccount {
        return DB::transaction(function () use ($phone, $name, $amount, $saleId, $shopId) {
            $account = CustomerCreditAccount::firstOrCreate(
                ['customer_phone' => $phone],
                ['customer_name' => $name, 'shop_id' => $shopId]
            );

            // Update name if now provided and was missing
            if ($name && !$account->customer_name) {
                $account->customer_name = $name;
            }

            $account->total_credit_given  += $amount;
            $account->outstanding_balance += $amount;
            $account->last_credit_at       = now();
            $account->save();

            return $account;
        });
    }

    /**
     * Record a repayment against a credit account.
     */
    public function recordRepayment(
        CustomerCreditAccount $account,
        int $amount,
        PaymentMethod $method,
        int $recordedBy,
        ?int $saleId = null,
        ?string $reference = null
    ): CreditRepayment {
        return DB::transaction(function () use ($account, $amount, $method, $recordedBy, $saleId, $reference) {
            $repayment = CreditRepayment::create([
                'credit_account_id' => $account->id,
                'payment_method'    => $method,
                'amount'            => $amount,
                'sale_id'           => $saleId,
                'recorded_by'       => $recordedBy,
                'reference'         => $reference,
                'repaid_at'         => now(),
            ]);

            $account->total_repaid        += $amount;
            $account->outstanding_balance  = max(0, $account->outstanding_balance - $amount);
            $account->last_repayment_at    = now();
            $account->save();

            return $repayment;
        });
    }
}
