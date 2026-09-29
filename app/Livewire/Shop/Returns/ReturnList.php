<?php

namespace App\Livewire\Shop\Returns;

use App\Models\ReturnModel;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ReturnList extends Component
{
    use WithPagination;
    use \App\Livewire\Concerns\RequiresOpenSession;

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
        $shopId = $this->shopId ?? auth()->user()->location_id;
        if (!$this->checkSession($shopId)) {
            return;
        }

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

    public function approveReturn(int $returnId): void
    {
        $user = auth()->user();

        if (! $user->isOwner()) {
            abort(403);
        }

        $return = ReturnModel::findOrFail($returnId);

        try {
            app(\App\Services\Returns\ReturnService::class)
                ->approveReturn($return, $user);

            session()->flash('success', 'Return ' . $return->return_number . ' approved.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
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

        // Every card and footer figure in one pass over the filtered returns
        $pending = 'approved_at IS NULL AND approved_by IS NULL';
        $c = (clone $baseQuery)->toBase()->selectRaw("
                COUNT(*)                                                               AS total_returns,
                COUNT(*) FILTER (WHERE approved_at IS NULL)                            AS pending_count,
                COUNT(*) FILTER (WHERE {$pending})                                     AS pending_approval,
                COALESCE(SUM(refund_amount) FILTER (WHERE NOT is_exchange), 0)         AS total_refunds,
                COUNT(*) FILTER (WHERE NOT is_exchange)                                AS refund_count,
                COALESCE(MAX(refund_amount) FILTER (WHERE NOT is_exchange), 0)         AS largest_refund,
                COUNT(*) FILTER (WHERE refund_method = 'credit_balance' AND NOT is_exchange) AS reduced_debt_count,
                COUNT(*) FILTER (WHERE is_exchange)                                    AS exchange_count,
                COUNT(*) FILTER (WHERE is_exchange AND {$pending})                     AS exchange_pending,
                COALESCE(SUM(refund_amount) FILTER (WHERE NOT is_exchange AND {$pending}), 0) AS pending_refund_value,
                MIN(processed_at) FILTER (WHERE {$pending})                            AS oldest_pending_at
            ")->first();

        $stats = collect((array) $c)->except('oldest_pending_at')->map(fn ($v) => (int) $v)->all();
        $stats['approved_count']     = $stats['total_returns'] - $stats['pending_approval'];
        $stats['exchange_approved']  = $stats['exchange_count'] - $stats['exchange_pending'];
        $stats['avg_refund']         = $stats['refund_count'] > 0 ? intdiv($stats['total_refunds'], $stats['refund_count']) : 0;
        $stats['oldest_pending']     = $c->oldest_pending_at ? local_time($c->oldest_pending_at)->diffForHumans(short: true) : null;
        $stats['items_exchanged']    = (int) DB::table('return_items')
            ->whereIn('return_id', (clone $baseQuery)->exchanges()->select('id'))
            ->sum('quantity_returned');

        return $stats;
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
        if ($this->statusFilter === 'pending_approval' || $this->statusFilter === 'pending') {
            $query->whereNull('approved_at')->whereNull('approved_by');
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
