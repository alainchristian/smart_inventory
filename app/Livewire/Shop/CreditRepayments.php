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

    // Repayment form
    public $amount = '';
    public string $paymentMethod = 'cash';
    public string $reference = '';
    public string $notes = '';

    // UI state
    public bool $showRepaymentForm = false;

    public function mount(): void
    {
        $user = auth()->user();
        if ($user->isShopManager()) {
            $this->checkSession($user->location_id);
        }
    }

    protected $rules = [
        'amount' => 'required|numeric|min:1',
        'paymentMethod' => 'required|in:cash,card,mobile_money,bank_transfer',
        'reference' => 'nullable|string|max:255',
        'notes' => 'nullable|string|max:500',
    ];

    public function updatedSearchQuery()
    {
        $this->resetPage();
    }

    public function selectCustomer(int $customerId)
    {
        $this->selectedCustomerId = $customerId;
        $this->showRepaymentForm = true;
        $this->reset(['amount', 'paymentMethod', 'reference', 'notes']);
        $this->paymentMethod = 'cash';
    }

    public function cancelRepayment()
    {
        $this->showRepaymentForm = false;
        $this->selectedCustomerId = null;
        $this->reset(['amount', 'paymentMethod', 'reference', 'notes']);
    }

    public function recordRepayment()
    {
        $this->validate();

        $customer = Customer::findOrFail($this->selectedCustomerId);
        $amount = (int) ($this->amount);

        // Validate amount doesn't exceed outstanding balance
        if ($amount > $customer->outstanding_balance) {
            $this->addError('amount', 'Repayment amount cannot exceed outstanding balance of ' . number_format($customer->outstanding_balance, 0) . ' RWF');
            return;
        }

        DB::transaction(function () use ($customer, $amount) {
            // 1. Create repayment record — link to active session when available
            CreditRepayment::create([
                'customer_id'      => $customer->id,
                'shop_id'          => auth()->user()->location_id,
                'daily_session_id' => $this->activeSession?->id,
                'amount'           => $amount,
                'payment_method'   => $this->paymentMethod,
                'reference'        => $this->reference ?: null,
                'notes'            => $this->notes ?: null,
                'recorded_by'      => auth()->id(),
                'repayment_date'   => now(),
            ]);

            // 2. Update customer balances
            $newBalance = max(0, $customer->outstanding_balance - $amount);
            $customer->update([
                'total_repaid'        => $customer->total_repaid + $amount,
                'outstanding_balance' => $newBalance,
                'last_repayment_at'   => now(),
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
                    'amount'           => $amount,
                    'payment_method'   => $this->paymentMethod,
                    'reference'        => $this->reference ?: null,
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
            'message' => 'Credit repayment of ' . number_format($amount, 0) . ' RWF recorded for ' . $customer->name . '.',
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
        return Customer::with(['shop', 'creditRepayments' => function ($query) {
            $query->orderBy('repayment_date', 'desc')->limit(10);
        }])->find($this->selectedCustomerId);
    }

    public function getPaymentMethodsProperty()
    {
        return [
            'cash' => 'Cash',
            'card' => 'Card',
            'mobile_money' => 'Mobile Money',
            'bank_transfer' => 'Bank Transfer',
        ];
    }

    public function render()
    {
        return view('livewire.shop.credit-repayments');
    }
}
