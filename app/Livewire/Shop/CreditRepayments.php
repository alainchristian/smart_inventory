<?php

namespace App\Livewire\Shop;

use App\Enums\PaymentMethod;
use App\Livewire\Concerns\RequiresOpenSession;
use App\Models\CreditRepayment;
use App\Models\CreditWriteoff;
use App\Models\Customer;
use App\Services\SettingsService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CreditRepayments extends Component
{
    use WithPagination, RequiresOpenSession;

    // Search and selection
    public string $searchQuery = '';
    public ?int $selectedCustomerId = null;

    // Repayment form — one input per channel (mirrors the checkout's
    // multi-channel payment pattern); the total repayment amount is simply
    // the sum of whatever channels are filled in, no separate total field.
    public int $payAmt_cash          = 0;
    public int $payAmt_card          = 0;
    public int $payAmt_mobile_money  = 0;
    public int $payAmt_bank_transfer = 0;
    public string $payRef_card          = '';
    public string $payRef_bank_transfer = '';
    public string $notes = '';

    // UI state
    public bool $showRepaymentForm = false;
    public bool $settingAllowCardPayment  = false;
    public bool $settingAllowBankTransfer = false;

    public function mount(): void
    {
        $user = auth()->user();
        if ($user->isShopManager()) {
            $this->checkSession($user->location_id);
        }

        $settings = app(SettingsService::class);
        $this->settingAllowCardPayment  = $settings->allowCardPayment();
        $this->settingAllowBankTransfer = $settings->allowBankTransferPayment();
    }

    protected $rules = [
        'payRef_card' => 'nullable|string|max:255',
        'payRef_bank_transfer' => 'nullable|string|max:255',
        'notes' => 'nullable|string|max:500',
    ];

    public function updatedSearchQuery()
    {
        $this->resetPage();
    }

    private function resetRepaymentForm(): void
    {
        $this->reset([
            'payAmt_cash', 'payAmt_card', 'payAmt_mobile_money', 'payAmt_bank_transfer',
            'payRef_card', 'payRef_bank_transfer',
            'notes',
        ]);
    }

    public function selectCustomer(int $customerId)
    {
        $this->selectedCustomerId = $customerId;
        $this->showRepaymentForm = true;
        $this->resetRepaymentForm();
    }

    public function cancelRepayment()
    {
        $this->showRepaymentForm = false;
        $this->selectedCustomerId = null;
        $this->resetRepaymentForm();
    }

    public function recordRepayment()
    {
        $this->validate();

        $customer = Customer::findOrFail($this->selectedCustomerId);

        // Channel breakdown — mirrors the checkout's payment-channel pattern,
        // except there's no fixed target to allocate: the repayment total is
        // simply the sum of whichever channels the user filled in.
        $breakdown = [
            'cash'          => ['amount' => (int) $this->payAmt_cash,          'reference' => null],
            'card'          => ['amount' => (int) $this->payAmt_card,          'reference' => $this->payRef_card ?: null],
            'mobile_money'  => ['amount' => (int) $this->payAmt_mobile_money,  'reference' => null],
            'bank_transfer' => ['amount' => (int) $this->payAmt_bank_transfer, 'reference' => $this->payRef_bank_transfer ?: null],
        ];

        $totalAllocated = collect($breakdown)->sum('amount');

        if ($totalAllocated <= 0) {
            $this->addError('total', __('Enter a repayment amount in at least one payment channel.'));
            return;
        }

        // Validate amount doesn't exceed outstanding balance
        if ($totalAllocated > $customer->outstanding_balance) {
            $this->addError('total', __('Repayment amount cannot exceed outstanding balance of :amount RWF', ['amount' => number_format($customer->outstanding_balance, 0)]));
            return;
        }

        DB::transaction(function () use ($customer, $breakdown, $totalAllocated) {
            // 1. Create one repayment row per channel used — all sharing the
            // same timestamp so SessionActivityFeed can group them back into
            // a single feed entry (customer_id + repayment_date).
            $repaymentDate = now();
            foreach ($breakdown as $method => $data) {
                if ($data['amount'] <= 0) {
                    continue;
                }

                CreditRepayment::create([
                    'customer_id'      => $customer->id,
                    'shop_id'          => auth()->user()->location_id,
                    'daily_session_id' => $this->activeSession?->id,
                    'amount'           => $data['amount'],
                    'payment_method'   => $method,
                    'reference'        => $data['reference'],
                    'notes'            => $this->notes ?: null,
                    'recorded_by'      => auth()->id(),
                    'repayment_date'   => $repaymentDate,
                ]);
            }

            // 2. Update customer balances
            $newBalance = max(0, $customer->outstanding_balance - $totalAllocated);
            $customer->update([
                'total_repaid'        => $customer->total_repaid + $totalAllocated,
                'outstanding_balance' => $newBalance,
                'last_repayment_at'   => $repaymentDate,
            ]);

            // 3. Write activity log
            \App\Models\ActivityLog::create([
                'user_id'           => auth()->id(),
                'user_name'         => auth()->user()?->name,
                'action'            => 'credit_repayment_recorded',
                'entity_type'       => 'Customer',
                'entity_id'         => $customer->id,
                'entity_identifier' => $customer->name . ' (' . $customer->phone . ')',
                'details'           => [
                    'amount'           => $totalAllocated,
                    'breakdown'        => collect($breakdown)->filter(fn ($d) => $d['amount'] > 0)->map(fn ($d) => $d['amount'])->toArray(),
                    'new_balance'      => $newBalance,
                    'previous_balance' => $customer->outstanding_balance,
                    'fully_paid'       => $newBalance === 0,
                    'shop_id'          => auth()->user()->location_id,
                ],
                'ip_address'        => request()->ip(),
                'user_agent'        => request()->header('User-Agent'),
            ]);

            // 4. Resolve open credit alerts for this customer if fully paid
            if ($newBalance === 0) {
                \App\Models\Alert::where('entity_type', 'Customer')
                    ->where('entity_id', $customer->id)
                    ->whereNull('resolved_at')
                    ->update([
                        'resolved_at'      => now(),
                        'resolution_notes' => 'Outstanding balance cleared by repayment on ' . now()->format('d M Y'),
                    ]);
            }

            // 5. Bust analytics cache so dashboards show fresh numbers
            try {
                // Tag-based flush (Redis/Memcached)
                if (method_exists(\Illuminate\Support\Facades\Cache::getStore(), 'tags')) {
                    \Illuminate\Support\Facades\Cache::tags(['analytics'])->flush();
                }
                // Key-based flush for common cache keys
                foreach ([
                    'shop_dashboard_payment_breakdown_' . auth()->user()->location_id,
                    'shop_dashboard_payment_breakdown_' . auth()->user()->location_id . '_' . now()->toDateString(),
                ] as $key) {
                    \Illuminate\Support\Facades\Cache::forget($key);
                }
            } catch (\Exception $e) {
                // Cache flush failure must never break the repayment transaction
                \Illuminate\Support\Facades\Log::warning('Cache flush failed after repayment: ' . $e->getMessage());
            }
        });

        $this->dispatch('notification', [
            'type'    => 'success',
            'message' => __('Credit repayment of :amount RWF recorded for :name.', [
                'amount' => number_format($totalAllocated, 0),
                'name'   => $customer->name,
            ]),
        ]);

        $this->cancelRepayment();
    }

    public function getStatsProperty()
    {
        $isShopManager = auth()->user()->isShopManager();
        $shopId        = auth()->user()->location_id;

        $scopeToShop = function ($query) use ($isShopManager, $shopId) {
            if ($isShopManager) {
                $query->where(function ($q) use ($shopId) {
                    $q->where('shop_id', $shopId)->orWhereNull('shop_id');
                });
            }
            return $query;
        };

        $outstandingBase = $scopeToShop(Customer::query()->where('outstanding_balance', '>', 0));

        $totalOutstanding = (clone $outstandingBase)->sum('outstanding_balance');
        $customerCount    = (clone $outstandingBase)->count();
        $highestBalance   = (clone $outstandingBase)->max('outstanding_balance') ?? 0;
        $avgBalance       = $customerCount > 0 ? intdiv($totalOutstanding, $customerCount) : 0;

        // All-time totals across every customer in scope (not just those still owing)
        $allBase        = $scopeToShop(Customer::query());
        $allCreditGiven = (clone $allBase)->sum('total_credit_given');
        $allRepaid      = (clone $allBase)->sum('total_repaid');
        $repaymentRate  = $allCreditGiven > 0 ? round(($allRepaid / $allCreditGiven) * 100, 1) : 0;

        $writtenOffQuery = CreditWriteoff::query();
        if ($isShopManager) {
            $writtenOffQuery->where('shop_id', $shopId);
        }
        $totalWrittenOff = $writtenOffQuery->sum('amount');

        $repaymentsTodayQuery = CreditRepayment::query()->whereDate('repayment_date', today());
        if ($isShopManager) {
            $repaymentsTodayQuery->where('shop_id', $shopId);
        }
        $repaymentsToday       = $repaymentsTodayQuery->get(['amount', 'customer_id']);
        $collectedToday        = $repaymentsToday->sum('amount');
        $repaymentsTodayCount  = $repaymentsToday->count();
        $customersPaidToday    = $repaymentsToday->pluck('customer_id')->unique()->count();
        $avgPaymentToday       = $repaymentsTodayCount > 0 ? intdiv($collectedToday, $repaymentsTodayCount) : 0;

        // Same overdue definition as GenerateSystemAlerts::generateOverdueCreditAlerts()
        $overdueDays = app(SettingsService::class)->overdueCreditDays();
        $cutoff      = now()->subDays($overdueDays);
        $overdueCount = (clone $outstandingBase)
            ->where(function ($q) use ($cutoff) {
                $q->where(function ($qq) use ($cutoff) {
                    $qq->whereNull('last_repayment_at')->where('last_credit_at', '<', $cutoff);
                })->orWhere('last_repayment_at', '<', $cutoff);
            })
            ->count();

        return [
            'total_outstanding'      => $totalOutstanding,
            'customer_count'         => $customerCount,
            'highest_balance'        => $highestBalance,
            'avg_balance'            => $avgBalance,
            'all_credit_given'       => $allCreditGiven,
            'all_repaid'             => $allRepaid,
            'repayment_rate'         => $repaymentRate,
            'total_written_off'      => $totalWrittenOff,
            'collected_today'        => $collectedToday,
            'repayments_today_count' => $repaymentsTodayCount,
            'customers_paid_today'   => $customersPaidToday,
            'avg_payment_today'      => $avgPaymentToday,
            'overdue_count'          => $overdueCount,
            'overdue_days'           => $overdueDays,
        ];
    }

    public function getCustomersProperty()
    {
        $query = Customer::query()
            ->where('outstanding_balance', '>', 0);

        // Filter by shop if user is shop manager — but never hide a
        // customer with no shop assigned (shop_id nullable; some existing
        // customers were registered without one via the owner's Customers
        // page). An unassigned customer should still be repayable from any
        // shop rather than becoming invisible everywhere.
        if (auth()->user()->isShopManager()) {
            $shopId = auth()->user()->location_id;
            $query->where(function ($q) use ($shopId) {
                $q->where('shop_id', $shopId)->orWhereNull('shop_id');
            });
        }

        // Search filter
        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', '%' . $this->searchQuery . '%')
                    ->orWhere('phone', 'like', '%' . $this->searchQuery . '%');
            });
        }

        return $query->orderBy('outstanding_balance', 'desc')
            ->paginate(20);
    }

    public function getSelectedCustomerProperty()
    {
        if (!$this->selectedCustomerId) {
            return null;
        }
        return Customer::with('shop')->find($this->selectedCustomerId);
    }

    /**
     * Recent repayments, grouped back into one entry per submission — a
     * multi-channel repayment writes one CreditRepayment row per channel
     * (all sharing the same repayment_date), so group by that timestamp to
     * show them as a single history entry with multiple method pills.
     */
    public function getRepaymentHistoryProperty()
    {
        if (!$this->selectedCustomerId) {
            return collect();
        }

        return CreditRepayment::where('customer_id', $this->selectedCustomerId)
            ->orderBy('repayment_date', 'desc')
            ->limit(40)
            ->get()
            ->groupBy(fn ($r) => $r->repayment_date->toDateTimeString())
            ->map(fn ($rows) => [
                'repayment_date' => $rows->first()->repayment_date,
                'amount'         => $rows->sum('amount'),
                'methods'        => $rows->map(fn ($r) => [
                    'method'    => $r->payment_method,
                    'amount'    => $r->amount,
                    'reference' => $r->reference,
                ])->values(),
            ])
            ->sortByDesc('repayment_date')
            ->take(10)
            ->values();
    }

    public function render()
    {
        return view('livewire.shop.credit-repayments');
    }
}
