<?php

namespace App\Livewire\Shop\Returns;

use App\Models\ReturnModel;
use App\Models\Shop;
use Livewire\Component;
use Livewire\WithPagination;

class ReturnList extends Component
{
    use WithPagination;

    public $shopId;
    public $shopName;

    // Filters
    public $statusFilter = 'all'; // all, pending, approved
    public $typeFilter = 'all'; // all, refund, exchange
    public $dateFrom = '';
    public $dateTo = '';
    public $search = '';

    protected $queryString = [
        'statusFilter' => ['except' => 'all'],
        'typeFilter' => ['except' => 'all'],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $user = auth()->user();

        if (!$user->isShopManager()) {
            abort(403, 'Only shop managers can access returns.');
        }

        $this->shopId = $user->location_id;
        $shop = Shop::find($this->shopId);
        $this->shopName = $shop->name ?? 'Unknown Shop';

        // Default date range to last 30 days
        if (empty($this->dateFrom)) {
            $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        }
        if (empty($this->dateTo)) {
            $this->dateTo = now()->format('Y-m-d');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->statusFilter = 'all';
        $this->typeFilter = 'all';
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->search = '';
        $this->resetPage();
    }

    protected function getKpiStats(): array
    {
        $baseQuery = ReturnModel::where('shop_id', $this->shopId);

        // Apply date range to KPIs
        if ($this->dateFrom) {
            $baseQuery->whereDate('processed_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $baseQuery->whereDate('processed_at', '<=', $this->dateTo);
        }

        return [
            'total_returns' => (clone $baseQuery)->count(),
            'pending_count' => (clone $baseQuery)->pendingApproval()->count(),
            'total_refunds' => (clone $baseQuery)->refunds()->sum('refund_amount'),
            'exchange_count' => (clone $baseQuery)->exchanges()->count(),
        ];
    }

    public function render()
    {
        $query = ReturnModel::where('shop_id', $this->shopId)
            ->with(['processedBy', 'approvedBy', 'items', 'sale'])
            ->latest('processed_at');

        // Apply status filter
        if ($this->statusFilter === 'pending') {
            $query->pendingApproval();
        } elseif ($this->statusFilter === 'approved') {
            $query->approved();
        }

        // Apply type filter
        if ($this->typeFilter === 'refund') {
            $query->refunds();
        } elseif ($this->typeFilter === 'exchange') {
            $query->exchanges();
        }

        // Apply date range filter
        if ($this->dateFrom) {
            $query->whereDate('processed_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('processed_at', '<=', $this->dateTo);
        }

        // Apply search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('return_number', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $this->search . '%');
            });
        }

        $returns = $query->paginate(20);

        return view('livewire.shop.returns.return-list', [
            'returns' => $returns,
            'kpiStats' => $this->getKpiStats(),
        ]);
    }
}
