<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Per-shop customer credit ledger.
 *
 * Credit belongs to the shop that gave it: one row per (customer, shop).
 * customers.total_credit_given / total_repaid / outstanding_balance remain as
 * the SUM across shops (kept in sync by CustomerCreditLedger) so company-wide
 * views keep working unchanged.
 *
 * Backfill: per-shop figures are rebuilt from history — credit sale payments
 * (by sales.shop_id), repayments and write-offs (by their shop_id). Anything
 * that doesn't reconcile (repayments taken at another shop, rows with no
 * shop, balances with no history) is settled so that each customer's rows
 * always sum to their existing customers.outstanding_balance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_shop_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->unsignedBigInteger('total_credit_given')->default(0);
            $table->unsignedBigInteger('total_repaid')->default(0);
            $table->unsignedBigInteger('total_written_off')->default(0);
            $table->unsignedBigInteger('outstanding_balance')->default(0);
            $table->timestamp('last_credit_at')->nullable();
            $table->timestamp('last_repayment_at')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'shop_id']);
            $table->index(['shop_id', 'outstanding_balance']);
        });

        $this->backfill();
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_shop_balances');
    }

    private function backfill(): void
    {
        $rows = []; // [customer_id][shop_id] => figures

        $touch = function (int $customerId, int $shopId) use (&$rows) {
            $rows[$customerId][$shopId] ??= [
                'given' => 0, 'repaid' => 0, 'written_off' => 0,
                'last_credit_at' => null, 'last_repayment_at' => null,
            ];
        };

        // Credit extended, by the shop that made the sale
        $given = DB::table('sale_payments')
            ->join('sales', 'sales.id', '=', 'sale_payments.sale_id')
            ->where('sale_payments.payment_method', 'credit')
            ->whereNotNull('sales.customer_id')
            ->whereNotNull('sales.shop_id')
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->groupBy('sales.customer_id', 'sales.shop_id')
            ->select('sales.customer_id', 'sales.shop_id',
                DB::raw('SUM(sale_payments.amount) as amount'),
                DB::raw('MAX(sales.sale_date) as last_at'))
            ->get();
        foreach ($given as $g) {
            $touch($g->customer_id, $g->shop_id);
            $rows[$g->customer_id][$g->shop_id]['given'] = (int) $g->amount;
            $rows[$g->customer_id][$g->shop_id]['last_credit_at'] = $g->last_at;
        }

        // Repayments / write-offs with a shop are applied to that shop; those
        // without one go into a per-customer pool settled below.
        $pool = [];

        $repaid = DB::table('credit_repayments')
            ->groupBy('customer_id', 'shop_id')
            ->select('customer_id', 'shop_id', DB::raw('SUM(amount) as amount'), DB::raw('MAX(repayment_date) as last_at'))
            ->get();
        foreach ($repaid as $r) {
            if ($r->shop_id === null) {
                $pool[$r->customer_id] = ($pool[$r->customer_id] ?? 0) + (int) $r->amount;
                continue;
            }
            $touch($r->customer_id, $r->shop_id);
            $rows[$r->customer_id][$r->shop_id]['repaid'] = (int) $r->amount;
            $rows[$r->customer_id][$r->shop_id]['last_repayment_at'] = $r->last_at;
        }

        if (Schema::hasTable('credit_writeoffs')) {
            $writeoffs = DB::table('credit_writeoffs')
                ->groupBy('customer_id', 'shop_id')
                ->select('customer_id', 'shop_id', DB::raw('SUM(amount) as amount'))
                ->get();
            foreach ($writeoffs as $w) {
                if ($w->shop_id === null) {
                    $pool[$w->customer_id] = ($pool[$w->customer_id] ?? 0) + (int) $w->amount;
                    continue;
                }
                $touch($w->customer_id, $w->shop_id);
                $rows[$w->customer_id][$w->shop_id]['written_off'] = (int) $w->amount;
            }
        }

        $customers = DB::table('customers')
            ->whereNull('deleted_at')
            ->where(fn ($q) => $q->where('outstanding_balance', '>', 0)->orWhereIn('id', array_keys($rows)))
            ->get(['id', 'shop_id', 'outstanding_balance']);

        $now = now();
        $insert = [];

        foreach ($customers as $c) {
            $shops = $rows[$c->id] ?? [];

            // Net per shop; money repaid at a different shop than it was owed
            // shows up as a negative net — move it into the pool.
            $net = [];
            foreach ($shops as $shopId => $f) {
                $n = $f['given'] - $f['repaid'] - $f['written_off'];
                if ($n < 0) {
                    $pool[$c->id] = ($pool[$c->id] ?? 0) + abs($n);
                    $n = 0;
                }
                $net[$shopId] = $n;
            }

            // Settle the pool against the largest balances first
            $left = $pool[$c->id] ?? 0;
            arsort($net);
            foreach ($net as $shopId => $n) {
                if ($left <= 0) {
                    break;
                }
                $take = min($n, $left);
                $net[$shopId] -= $take;
                $left -= $take;
            }

            // Reconcile to the balance the business already recorded
            $target = (int) $c->outstanding_balance;
            $diff   = $target - array_sum($net);
            if ($diff > 0) {
                $home = $c->shop_id
                    ?? (count($net) ? array_key_first($net) : null)
                    ?? DB::table('sales')->where('customer_id', $c->id)->whereNotNull('shop_id')->orderByDesc('sale_date')->value('shop_id');
                if ($home === null) {
                    // No way to tell which shop gave this credit — leave it on
                    // the customer total only; it surfaces as "unassigned".
                    continue;
                }
                $shops[$home] ??= ['given' => 0, 'repaid' => 0, 'written_off' => 0, 'last_credit_at' => null, 'last_repayment_at' => null];
                $net[$home] = ($net[$home] ?? 0) + $diff;
            } elseif ($diff < 0) {
                $excess = -$diff;
                arsort($net);
                foreach ($net as $shopId => $n) {
                    $take = min($n, $excess);
                    $net[$shopId] -= $take;
                    $excess -= $take;
                    if ($excess <= 0) {
                        break;
                    }
                }
            }

            foreach ($shops as $shopId => $f) {
                $insert[] = [
                    'customer_id'         => $c->id,
                    'shop_id'             => $shopId,
                    'total_credit_given'  => max($f['given'], $net[$shopId] ?? 0),
                    'total_repaid'        => $f['repaid'],
                    'total_written_off'   => $f['written_off'],
                    'outstanding_balance' => max(0, $net[$shopId] ?? 0),
                    'last_credit_at'      => $f['last_credit_at'],
                    'last_repayment_at'   => $f['last_repayment_at'],
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];
            }
        }

        foreach (array_chunk($insert, 500) as $chunk) {
            DB::table('customer_shop_balances')->insert($chunk);
        }
    }
};
