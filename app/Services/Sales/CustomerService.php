<?php

namespace App\Services\Sales;

use App\Models\Customer;

class CustomerService
{
    /**
     * Find customer by phone. Returns null if not found.
     */
    public function findByPhone(string $phone): ?Customer
    {
        return Customer::where('phone', $phone)->first();
    }

    /**
     * Create a new customer record.
     */
    public function create(array $data, int $shopId): Customer
    {
        return Customer::create([
            'name'          => $data['name'],
            'phone'         => $data['phone'],
            'email'         => $data['email'] ?? null,
            'notes'         => $data['notes'] ?? null,
            'registered_by' => auth()->id(),
            'shop_id'       => $shopId,
        ]);
    }

    /**
     * Mark a sale as made to this customer and optionally extend credit.
     * Called inside the sale transaction.
     */
    public function recordSalePurchase(Customer $customer, int $creditAmount = 0): void
    {
        $updates = ['last_purchase_at' => now()];

        if ($creditAmount > 0) {
            $updates['total_credit_given']  = $customer->total_credit_given + $creditAmount;
            $updates['outstanding_balance'] = $customer->outstanding_balance + $creditAmount;
            $updates['last_credit_at']      = now();
        }

        $customer->update($updates);
    }
}
