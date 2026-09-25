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
    public function create(array $data, ?int $shopId = null): Customer
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
    public function recordSalePurchase(Customer $customer, int $creditAmount = 0, ?int $shopId = null): void
    {
        $customer->update(['last_purchase_at' => now()]);

        if ($creditAmount > 0) {
            if (! $shopId) {
                throw new \InvalidArgumentException('Credit must be attached to the shop that gave it.');
            }
            // Credit belongs to the shop that gave it (customer_shop_balances)
            app(CustomerCreditLedger::class)->extend($customer, $shopId, $creditAmount);
        }
    }
}
