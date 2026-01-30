<?php

namespace App\Livewire\Inventory\Transfers;

use App\Enums\TransferStatus;
use App\Models\Shop;
use App\Models\Transfer;
use App\Models\Warehouse;
use Livewire\Component;
use Livewire\WithPagination;

class TransferList extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $status = null;
    public ?int $warehouseId = null;
    public ?int $shopId = null;
    public ?string $startDate = null;
    public ?string $endDate = null;
    public bool $discrepanciesOnly = false;
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => null],
        'warehouseId' => ['except' => null],
        'shopId' => ['except' => null],
        'startDate' => ['except' => null],
        'endDate' => ['except' => null],
    ];

    public function mount()
    {
        $user = auth()->user();

        // Auto-filter based on user role
        if ($user->isWarehouseManager()) {
            $this->warehouseId = $user->location_id;
        } elseif ($user->isShopManager()) {
            $this->shopId = $user->location_id;
        }

        // Default date range: last 30 days
        if (!$this->startDate) {
            $this->startDate = now()->subDays(30)->format('Y-m-d');
        }
        if (!$this->endDate) {
            $this->endDate = now()->format('Y-m-d');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset([
            'search',
            'status',
            'warehouseId',
            'shopId',
            'startDate',
            'endDate',
            'discrepanciesOnly'
        ]);
        $this->mount(); // Reapply defaults
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $query = Transfer::query()
            ->with([
                'fromWarehouse',
                'toShop',
                'requestedBy',
                'items.product',
                'transporter'
            ])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('transfer_number', 'ILIKE', "%{$this->search}%")
                          ->orWhereHas('fromWarehouse', fn($q) => $q->where('name', 'ILIKE', "%{$this->search}%"))
                          ->orWhereHas('toShop', fn($q) => $q->where('name', 'ILIKE', "%{$this->search}%"));
                });
            })
            ->when($this->status, function ($q) {
                $q->where('status', $this->status);
            })
            ->when($this->warehouseId, function ($q) {
                $q->where('from_warehouse_id', $this->warehouseId);
            })
            ->when($this->shopId, function ($q) {
                $q->where('to_shop_id', $this->shopId);
            })
            ->when($this->startDate, function ($q) {
                $q->whereDate('created_at', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($q) {
                $q->whereDate('created_at', '<=', $this->endDate);
            })
            ->when($this->discrepanciesOnly, function ($q) {
                $q->where('has_discrepancy', true);
            })
            ->orderBy($this->sortBy, $this->sortDirection);

        // Get summary statistics
        $stats = [
            'pending' => (clone $query)->where('status', TransferStatus::PENDING)->count(),
            'approved' => (clone $query)->where('status', TransferStatus::APPROVED)->count(),
            'in_transit' => (clone $query)->where('status', TransferStatus::IN_TRANSIT)->count(),
            'delivered' => (clone $query)->where('status', TransferStatus::DELIVERED)->count(),
            'received' => (clone $query)->where('status', TransferStatus::RECEIVED)->count(),
            'with_discrepancies' => (clone $query)->where('has_discrepancy', true)->count(),
        ];

        return view('livewire.inventory.transfers.transfer-list', [
            'transfers' => $query->paginate(25),
            'stats' => $stats,
            'warehouses' => Warehouse::active()->orderBy('name')->get(),
            'shops' => Shop::active()->orderBy('name')->get(),
            'statuses' => TransferStatus::cases(),
        ]);
    }
}
