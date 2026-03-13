<?php

namespace App\Livewire\Owner\Reports;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerCreditReport extends Component
{
    use WithPagination;

    // ─── Filters ──────────────────────────────────────────────────────────────
    public string $search = '';
    public string $locationFilter = 'all';
    public string $balanceFilter = 'with_balance'; // all | with_balance | no_balance
    public string $sortBy = 'balance_desc'; // balance_desc | balance_asc | name | recent

    protected $queryString = [
        'search' => ['except' => ''],
        'locationFilter' => ['except' => 'all'],
        'balanceFilter' => ['except' => 'with_balance'],
        'sortBy' => ['except' => 'balance_desc'],
    ];

    // ─── Actions ──────────────────────────────────────────────────────────────
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingLocationFilter()
    {
        $this->resetPage();
    }

    public function updatingBalanceFilter()
    {
        $this->resetPage();
    }

    public function updatingSortBy()
    {
        $this->resetPage();
    }

    // ─── Computed Properties ──────────────────────────────────────────────────
    public function getShopsProperty()
    {
        return Shop::orderBy('name')->get(['id', 'name']);
    }

    public function getSelectedShopNameProperty(): string
    {
        if ($this->locationFilter === 'all') {
            return 'All Shops';
        }
        $shopId = (int) str_replace('shop:', '', $this->locationFilter);
        $shop = Shop::find($shopId);
        return $shop ? $shop->name : 'Unknown Shop';
    }

    // ─── Credit Summary Stats ─────────────────────────────────────────────────
    public function getCreditSummaryProperty(): array
    {
        $query = Customer::query();

        if ($this->locationFilter !== 'all') {
            $shopId = (int) str_replace('shop:', '', $this->locationFilter);
            $query->where('shop_id', $shopId);
        }

        return [
            'total_customers_with_credit' => (clone $query)->where('outstanding_balance', '>', 0)->count(),
            'total_outstanding' => (clone $query)->sum('outstanding_balance'),
            'total_credit_given' => (clone $query)->sum('total_credit_given'),
            'total_repaid' => (clone $query)->sum('total_repaid'),
        ];
    }

    // ─── Customers List ───────────────────────────────────────────────────────
    public function getCustomersProperty()
    {
        $query = Customer::query()
            ->with(['shop', 'registeredBy']);

        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        // Location filter
        if ($this->locationFilter !== 'all') {
            $shopId = (int) str_replace('shop:', '', $this->locationFilter);
            $query->where('shop_id', $shopId);
        }

        // Balance filter
        if ($this->balanceFilter === 'with_balance') {
            $query->where('outstanding_balance', '>', 0);
        } elseif ($this->balanceFilter === 'no_balance') {
            $query->where('outstanding_balance', '=', 0);
        }

        // Sorting
        match ($this->sortBy) {
            'balance_desc' => $query->orderBy('outstanding_balance', 'desc'),
            'balance_asc' => $query->orderBy('outstanding_balance', 'asc'),
            'name' => $query->orderBy('name', 'asc'),
            'recent' => $query->orderBy('last_credit_at', 'desc'),
            default => $query->orderBy('outstanding_balance', 'desc'),
        };

        return $query->paginate(50);
    }

    // ─── Credit History for Customer ──────────────────────────────────────────
    public ?int $selectedCustomerId = null;
    public bool $showCreditHistory = false;

    public function showCustomerHistory(int $customerId)
    {
        $this->selectedCustomerId = $customerId;
        $this->showCreditHistory = true;
    }

    public function closeCreditHistory()
    {
        $this->selectedCustomerId = null;
        $this->showCreditHistory = false;
    }

    public function getSelectedCustomerProperty()
    {
        if (!$this->selectedCustomerId) {
            return null;
        }
        return Customer::with('shop')->find($this->selectedCustomerId);
    }

    public function getCustomerCreditSalesProperty()
    {
        if (!$this->selectedCustomerId) {
            return collect();
        }

        return Sale::query()
            ->with(['shop', 'soldBy'])
            ->where('customer_id', $this->selectedCustomerId)
            ->where('has_credit', true)
            ->orderBy('sale_date', 'desc')
            ->limit(100)
            ->get();
    }

    public function render()
    {
        return view('livewire.owner.reports.customer-credit-report');
    }
}
