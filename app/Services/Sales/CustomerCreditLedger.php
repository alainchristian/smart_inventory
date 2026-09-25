<?php

namespace App\Services\Sales;

use App\Models\Customer;
use App\Models\CustomerShopBalance;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The only write path for customer credit.
 *
 * Credit is owned by the shop that extended it (customer_shop_balances). After
 * every change the customer's company-wide totals (customers.total_credit_given,
 * total_repaid, outstanding_balance) are re-derived as the sum across shops.
 *
 * All methods expect to run inside the caller's DB transaction (they open a
 * nested one otherwise) and lock the balance row they change.
 */
class CustomerCreditLedger
{
    /** Credit extended on a sale at $shopId. */
    public function extend(Customer $customer, int $shopId, int $amount, ?CarbonInterface $at = null): CustomerShopBalance
    {
        $this->assertPositive($amount);

        return DB::transaction(function () use ($customer, $shopId, $amount, $at) {
            $row = $this->lockRow($customer->id, $shopId, create: true);
            $row->total_credit_given  += $amount;
            $row->outstanding_balance += $amount;
            $row->last_credit_at       = $at ?? now();
            $row->save();

            $this->syncCustomer($customer, ['last_credit_at' => $row->last_credit_at]);

            return $row;
        });
    }

    /** Repayment collected at $shopId — only reduces that shop's balance. */
    public function repay(Customer $customer, int $shopId, int $amount, ?CarbonInterface $at = null): CustomerShopBalance
    {
        $this->assertPositive($amount);

        return DB::transaction(function () use ($customer, $shopId, $amount, $at) {
            $row = $this->lockRow($customer->id, $shopId);
            if (! $row || $amount > $row->outstanding_balance) {
                throw new \DomainException(
                    'Repayment of ' . number_format($amount) . ' RWF exceeds what this customer owes this shop ('
                    . number_format($row?->outstanding_balance ?? 0) . ' RWF).'
                );
            }

            $row->total_repaid        += $amount;
            $row->outstanding_balance -= $amount;
            $row->last_repayment_at    = $at ?? now();
            $row->save();

            $this->syncCustomer($customer, ['last_repayment_at' => $row->last_repayment_at]);

            return $row;
        });
    }

    /** Owner write-off against one shop's balance. Returns [before, after]. */
    public function writeOff(Customer $customer, int $shopId, int $amount): array
    {
        $this->assertPositive($amount);

        return DB::transaction(function () use ($customer, $shopId, $amount) {
            $row = $this->lockRow($customer->id, $shopId);
            if (! $row || $amount > $row->outstanding_balance) {
                throw new \DomainException(
                    'Write-off of ' . number_format($amount) . ' RWF exceeds the balance owed to this shop ('
                    . number_format($row?->outstanding_balance ?? 0) . ' RWF).'
                );
            }

            $before = $row->outstanding_balance;
            $row->total_written_off   += $amount;
            $row->outstanding_balance -= $amount;
            $row->save();

            $this->syncCustomer($customer);

            return [$before, $row->outstanding_balance];
        });
    }

    /**
     * Undo credit that should never have been owed (voided credit sale, or a
     * return refunded against the debt). Reduces credit given and the balance
     * by the same amount, capped at what is still owed. Returns the amount
     * actually applied.
     */
    public function reverse(Customer $customer, int $shopId, int $amount): int
    {
        if ($amount <= 0) {
            return 0;
        }

        return DB::transaction(function () use ($customer, $shopId, $amount) {
            $row = $this->lockRow($customer->id, $shopId);
            if (! $row || $row->outstanding_balance <= 0) {
                return 0;
            }

            $applied = min($amount, $row->outstanding_balance);
            $row->outstanding_balance -= $applied;
            $row->total_credit_given   = max(0, $row->total_credit_given - $applied);
            $row->save();

            $this->syncCustomer($customer);

            return $applied;
        });
    }

    public function balanceAt(Customer|int $customer, int $shopId): int
    {
        $id = $customer instanceof Customer ? $customer->id : $customer;

        return (int) CustomerShopBalance::where('customer_id', $id)->where('shop_id', $shopId)->value('outstanding_balance');
    }

    /** Balances owed to shops other than $exceptShopId, largest first: [shop name => amount]. */
    public function balancesElsewhere(Customer|int $customer, ?int $exceptShopId): Collection
    {
        $id = $customer instanceof Customer ? $customer->id : $customer;

        return CustomerShopBalance::with('shop:id,name')
            ->where('customer_id', $id)
            ->where('outstanding_balance', '>', 0)
            ->when($exceptShopId, fn ($q) => $q->where('shop_id', '!=', $exceptShopId))
            ->orderByDesc('outstanding_balance')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->shop?->name ?? ('Shop #' . $r->shop_id) => $r->outstanding_balance]);
    }

    /**
     * POS warning text, e.g. "Owes this shop 20,000 RWF · Owes Kimironko 45,000 RWF".
     * Empty string when nothing is owed anywhere.
     */
    public function describeOwed(Customer|int $customer, ?int $currentShopId): string
    {
        $parts = [];

        if ($currentShopId && ($here = $this->balanceAt($customer, $currentShopId)) > 0) {
            $parts[] = 'Owes this shop ' . number_format($here) . ' RWF';
        }
        foreach ($this->balancesElsewhere($customer, $currentShopId) as $shopName => $amount) {
            $short   = trim(\Illuminate\Support\Str::afterLast($shopName, '—')) ?: $shopName;
            $parts[] = 'Owes ' . $short . ' ' . number_format($amount) . ' RWF';
        }

        return implode(' · ', $parts);
    }

    private function lockRow(int $customerId, int $shopId, bool $create = false): ?CustomerShopBalance
    {
        $row = CustomerShopBalance::where('customer_id', $customerId)->where('shop_id', $shopId)->lockForUpdate()->first();

        if (! $row && $create) {
            CustomerShopBalance::insertOrIgnore([
                'customer_id' => $customerId, 'shop_id' => $shopId,
                'created_at'  => now(), 'updated_at' => now(),
            ]);
            $row = CustomerShopBalance::where('customer_id', $customerId)->where('shop_id', $shopId)->lockForUpdate()->first();
        }

        return $row;
    }

    /** Re-derive the customer's company-wide totals from the per-shop rows. */
    private function syncCustomer(Customer $customer, array $extra = []): void
    {
        $sums = CustomerShopBalance::where('customer_id', $customer->id)
            ->selectRaw('COALESCE(SUM(total_credit_given),0) given, COALESCE(SUM(total_repaid),0) repaid, COALESCE(SUM(outstanding_balance),0) outstanding')
            ->first();

        $customer->forceFill($extra + [
            'total_credit_given'  => (int) $sums->given,
            'total_repaid'        => (int) $sums->repaid,
            'outstanding_balance' => (int) $sums->outstanding,
        ])->save();
    }

    private function assertPositive(int $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero.');
        }
    }
}
