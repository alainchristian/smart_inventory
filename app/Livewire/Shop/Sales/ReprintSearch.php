<?php

namespace App\Livewire\Shop\Sales;

use App\Models\Sale;
use Livewire\Component;
use Carbon\Carbon;

class ReprintSearch extends Component
{
    public string $search    = '';
    public string $dateFrom  = '';
    public string $dateTo    = '';
    public string $preset    = 'today';
    public int    $perPage   = 25;

    public bool  $showReceiptModal = false;
    public ?int  $selectedSaleId   = null;
    public ?Sale $selectedSale     = null;

    protected $queryString = [
        'search'   => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo'   => ['except' => ''],
        'preset'   => ['except' => 'today'],
    ];

    public function mount(): void
    {
        $this->resolveDates();
    }

    public function setPreset(string $preset): void
    {
        $this->preset  = $preset;
        $this->perPage = 25;
        $this->resolveDates();
    }

    public function resolveDates(): void
    {
        $today = today();

        match ($this->preset) {
            'today'      => [$this->dateFrom, $this->dateTo] = [$today->toDateString(), $today->toDateString()],
            'yesterday'  => [$this->dateFrom, $this->dateTo] = [$today->copy()->subDay()->toDateString(), $today->copy()->subDay()->toDateString()],
            'this_week'  => [$this->dateFrom, $this->dateTo] = [$today->copy()->startOfWeek()->toDateString(), $today->toDateString()],
            'this_month' => [$this->dateFrom, $this->dateTo] = [$today->copy()->startOfMonth()->toDateString(), $today->toDateString()],
            'last_month' => [$this->dateFrom, $this->dateTo] = [
                $today->copy()->subMonth()->startOfMonth()->toDateString(),
                $today->copy()->subMonth()->endOfMonth()->toDateString(),
            ],
            'last_30'    => [$this->dateFrom, $this->dateTo] = [$today->copy()->subDays(29)->toDateString(), $today->toDateString()],
            default      => null, // 'custom' — keep whatever dateFrom/dateTo are
        };
    }

    public function updatingSearch(): void
    {
        $this->perPage = 25;
    }

    public function updatingDateFrom(): void
    {
        $this->preset  = 'custom';
        $this->perPage = 25;
    }

    public function updatingDateTo(): void
    {
        $this->preset  = 'custom';
        $this->perPage = 25;
    }

    public function loadMore(): void
    {
        $this->perPage += 25;
    }

    public function viewSale(int $id): void
    {
        $this->selectedSale     = Sale::with(['shop', 'soldBy', 'payments', 'items.product'])->find($id);
        $this->selectedSaleId   = $id;
        $this->showReceiptModal = true;
    }

    public function closeReceiptModal(): void
    {
        $this->showReceiptModal = false;
        $this->selectedSale     = null;
        $this->selectedSaleId   = null;
    }

    public function render()
    {
        $user   = auth()->user();
        $shopId = $user->isOwner() ? null : $user->location_id;

        $query = Sale::with(['shop', 'soldBy', 'payments', 'items.product'])
            ->whereNull('voided_at')
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('sale_date', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn ($q) => $q->whereDate('sale_date', '<=', $this->dateTo))
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('sale_number', 'ilike', $term)
                          ->orWhere('customer_name', 'ilike', $term)
                          ->orWhere('customer_phone', 'ilike', $term)
                          ->orWhereHas('items.product', fn ($p) => $p->where('name', 'ilike', $term));
                });
            })
            ->orderByDesc('sale_date');

        $total   = $query->count();
        $sales   = $query->take($this->perPage)->get();
        $hasMore = $total > $this->perPage;

        return view('livewire.shop.sales.reprint-search', compact('sales', 'total', 'hasMore'));
    }
}
