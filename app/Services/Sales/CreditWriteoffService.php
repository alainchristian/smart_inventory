<?php

namespace App\Services\Sales;

use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\CreditWriteoff;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreditWriteoffService
{
    public function writeoff(Customer $customer, int $amount, string $reason, User $user): CreditWriteoff
    {
        if (! $user->isOwner()) {
            abort(403, 'Only the owner can perform credit write-offs.');
        }

        if ($amount <= 0) {
            throw new \Exception('Write-off amount must be greater than zero.');
        }

        if ($amount > $customer->outstanding_balance) {
            throw new \Exception(
                'Write-off amount (' . number_format($amount) . ' RWF) exceeds ' .
                'outstanding balance (' . number_format($customer->outstanding_balance) . ' RWF).'
            );
        }

        if (strlen(trim($reason)) === 0) {
            throw new \Exception('A reason is required for credit write-offs.');
        }

        return DB::transaction(function () use ($customer, $amount, $reason, $user) {
            $balanceBefore = $customer->outstanding_balance;
            $balanceAfter  = $balanceBefore - $amount;

            $writeoff = CreditWriteoff::create([
                'customer_id'    => $customer->id,
                'shop_id'        => $customer->shop_id,
                'amount'         => $amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
                'reason'         => trim($reason),
                'written_off_by' => $user->id,
                'written_off_at' => now(),
            ]);

            $customer->outstanding_balance = $balanceAfter;
            $customer->save();

            // Resolve open overdue/credit-limit alerts for this customer
            Alert::where('entity_type', 'Customer')
                ->where('entity_id', $customer->id)
                ->where('is_resolved', false)
                ->get()
                ->each(fn ($a) => $a->markAsResolved($user->id));

            ActivityLog::create([
                'user_id'           => $user->id,
                'user_name'         => $user->name,
                'action'            => 'credit_writeoff',
                'entity_type'       => 'Customer',
                'entity_id'         => $customer->id,
                'entity_identifier' => $customer->name . ' (' . $customer->phone . ')',
                'details'           => [
                    'amount'         => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after'  => $balanceAfter,
                    'reason'         => $reason,
                    'full_writeoff'  => $balanceAfter === 0,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            return $writeoff;
        });
    }
}
