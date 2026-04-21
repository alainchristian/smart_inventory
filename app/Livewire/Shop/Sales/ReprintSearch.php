<?php

namespace App\Livewire\Shop\Sales;

use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;

class ReprintSearch extends Component
{
    use WithPagination;

    public string $search     = '';
    public string $dateFrom   = '';
    public string $dateTo     = '';

    protected $queryString = [
        'search'   => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo'   => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->dateFrom = today()->toDateString();
        $this->dateTo   = today()->toDateString();
    }

    public function updatingSearch(): void  { $this->resetPage(); }
    public function updatingDateFrom(): void { $this->resetPage(); }
    public function updatingDateTo(): void   { $this->resetPage(); }

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

        $sales = $query->paginate(15);

        return view('livewire.shop.sales.reprint-search', compact('sales'));
    }
}
