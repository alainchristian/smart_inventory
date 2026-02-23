<?php

namespace App\Livewire\Shop\Returns;

use App\Models\ReturnModel;
use App\Models\Shop;
use App\Services\Returns\ReturnService;
use Livewire\Component;
use Livewire\WithPagination;

class ReturnList extends Component
{
    use WithPagination;

    public $shopId;
    public $shopName;
    public $isOwner = false;

    // Filters
    public $statusFilter = 'all'; // all, pending, approved
    public $typeFilter = 'all'; // all, refund, exchange
    public $shopFilter = 'all'; // all, or specific shop_id (owner only)
    public $dateFrom = '';
    public $dateTo = '';
    public $search = '';

    protected $queryString = [
        'statusFilter' => ['except' => 'all'],
        'typeFilter' => ['except' => 'all'],
        'shopFilter' => ['except' => 'all'],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $user = auth()->user();

        // Check authorization
        if (!$user->isShopManager() && !$user->isOwner()) {
            abort(403, 'Only shop managers and owners can access returns.');
        }

        // Owner sees all shops, shop manager sees only their shop
        $this->isOwner = $user->isOwner();

        if ($this->isOwner) {
            $this->shopId = null; // Owner sees all shops
            $this->shopName = 'All Shops';
        } else {
            $this->shopId = $user->location_id;
            $shop = Shop::find($this->shopId);
            $this->shopName = $shop->name ?? 'Unknown Shop';
        }

        // Default date range to last 30 days
        if (empty($this->dateFrom)) {
            $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        }
        if (empty($this->dateTo)) {
            $this->dateTo = now()->format('Y-m-d');
        }

        // Auto-filter pending approval if coming from alert
        if (request()->has('pending_approval') && request()->get('pending_approval') == '1') {
            $this->statusFilter = 'pending';
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

    public function updatingShopFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->statusFilter = 'all';
        $this->typeFilter = 'all';
        $this->shopFilter = 'all';
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->search = '';
        $this->resetPage();
    }

    public function approveReturn($returnId)
    {
        $return = ReturnModel::findOrFail($returnId);

        // Check authorization
        $user = auth()->user();

        // Shop managers can approve small returns (<50,000 RWF)
        // Only owners can approve large returns (>=50,000 RWF)
        if ($return->refund_amount >= 50000 && !$user->isOwner()) {
            session()->flash('error', 'Only the owner can approve large refunds (>=RWF 500).');
            return;
        }

        // Verify return belongs to this shop (only for shop managers)
        if (!$this->isOwner && $return->shop_id !== $this->shopId) {
            session()->flash('error', 'You can only approve returns from your shop.');
            return;
        }

        try {
            $returnService = app(ReturnService::class);
            $returnService->approveReturn($return, $user);

            session()->flash('success', "Return {$return->return_number} approved successfully.");
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to approve return: ' . $e->getMessage());
        }
    }

    protected function getKpiStats(): array
    {
        $baseQuery = ReturnModel::query();

        // Apply shop filter
        if ($this->isOwner) {
            // Owner view: Apply shop filter if set
            if ($this->shopFilter !== 'all') {
                $baseQuery->where('shop_id', $this->shopFilter);
            }
            // Otherwise show all shops
        } else {
            // Shop manager view: Only their shop
            $baseQuery->where('shop_id', $this->shopId);
        }

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
        $query = ReturnModel::query()
            ->with(['processedBy', 'approvedBy', 'items', 'sale', 'shop'])
            ->latest('processed_at');

        // Apply shop filter
        if ($this->isOwner) {
            // Owner view: Apply shop filter if set
            if ($this->shopFilter !== 'all') {
                $query->where('shop_id', $this->shopFilter);
            }
            // Otherwise show all shops
        } else {
            // Shop manager view: Only their shop
            $query->where('shop_id', $this->shopId);
        }

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

        // Get shops list for owner filter
        $shops = $this->isOwner ? Shop::orderBy('name')->get() : collect();

        return view('livewire.shop.returns.return-list', [
            'returns' => $returns,
            'kpiStats' => $this->getKpiStats(),
            'shops' => $shops,
        ]);
    }
}
