<?php

namespace App\Livewire\Shop;

use App\Models\Shop;
use App\Models\StockReturn;
use App\Services\Inventory\StockReturnService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Shop → warehouse returns (shop.stock-returns). Stock outside the shop's
 * categories is listed first and pre-filled; anything else can be sent too.
 */
class StockReturns extends Component
{
    #[Url(as: 'shop')]
    public ?int $shopId = null;

    /** product_id => boxes to send */
    public array $send = [];
    public string $reason = '';
    public bool $showReview = false;

    public function mount(): void
    {
        $user = auth()->user();
        if ($user->isShopManager()) {
            $this->shopId = (int) $user->location_id;
        } elseif ($user->isOwner()) {
            $this->shopId ??= (int) (session('selected_shop_id') ?? Shop::orderBy('name')->value('id'));
        } else {
            abort(403);
        }

        $this->prefill();
    }

    public function updatedShopId(): void
    {
        if (! auth()->user()->isOwner()) {
            $this->shopId = (int) auth()->user()->location_id;
        }
        $this->send = [];
        $this->prefill();
    }

    /** Stock the shop no longer sells defaults to "send all". */
    private function prefill(): void
    {
        foreach ($this->stockRows() as $row) {
            if (! $row->sellable) {
                $this->send[$row->product_id] = (int) $row->boxes;
            }
        }
    }

    public function sendAll(int $productId): void
    {
        $row = collect($this->stockRows())->firstWhere('product_id', $productId);
        $this->send[$productId] = (int) ($row->boxes ?? 0);
    }

    public function review(): void
    {
        $rows  = collect($this->stockRows())->keyBy('product_id');
        $clean = [];
        foreach ($this->send as $productId => $n) {
            $n   = max(0, (int) $n);
            $max = (int) ($rows[$productId]->boxes ?? 0);
            if ($n > $max) {
                $this->addError('send.' . $productId, "Only {$max} box(es) available.");
                return;
            }
            if ($n > 0) {
                $clean[$productId] = $n;
            }
        }
        if ($clean === []) {
            $this->addError('send', 'Choose at least one box to send back.');
            return;
        }
        $this->send       = $clean + array_fill_keys(array_keys($this->send), 0);
        $this->showReview = true;
    }

    public function confirmSend(): void
    {
        try {
            $return = app(StockReturnService::class)->send(
                Shop::findOrFail($this->shopId),
                array_filter(array_map('intval', $this->send)),
                trim($this->reason),
                auth()->user()
            );
            $this->dispatch('notification', ['type' => 'success', 'message' => "{$return->return_number} sent — the boxes are off sale until the warehouse receives them."]);
            $this->reset(['send', 'reason', 'showReview']);
            $this->prefill();
        } catch (\Throwable $e) {
            $this->showReview = false;
            $this->dispatch('notification', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function cancelReturn(int $returnId): void
    {
        try {
            $return = StockReturn::where('shop_id', $this->shopId)->findOrFail($returnId);
            app(StockReturnService::class)->cancel($return, null, auth()->user());
            $this->dispatch('notification', ['type' => 'success', 'message' => "{$return->return_number} cancelled — the boxes are back on sale."]);
            $this->prefill();
        } catch (\Throwable $e) {
            $this->dispatch('notification', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /** Shop stock per product: sealed/opened boxes, items, whether this shop sells it. */
    private function stockRows(): array
    {
        if (! $this->shopId) {
            return [];
        }
        $shop = Shop::find($this->shopId);

        return DB::table('boxes')
            ->join('products', 'products.id', '=', 'boxes.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('boxes.location_type', 'shop')
            ->where('boxes.location_id', $this->shopId)
            ->whereIn('boxes.status', ['full', 'partial'])
            ->where('boxes.items_remaining', '>', 0)
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.category_id', 'categories.name')
            ->orderBy('products.name')
            ->selectRaw("products.id as product_id, products.name, products.sku, products.category_id, categories.name as category_name,
                         COUNT(*) as boxes,
                         COUNT(*) FILTER (WHERE boxes.status = 'full') as sealed,
                         SUM(boxes.items_remaining) as items")
            ->get()
            ->map(function ($r) use ($shop) {
                $r->sellable = $shop ? $shop->sellsCategory($r->category_id !== null ? (int) $r->category_id : null) : true;
                return $r;
            })
            ->all();
    }

    public function render()
    {
        $shop = $this->shopId ? Shop::with('defaultWarehouse')->find($this->shopId) : null;
        $rows = collect($this->stockRows());

        return view('livewire.shop.stock-returns', [
            'shop'        => $shop,
            'notSold'     => $rows->where('sellable', false)->values(),
            'sold'        => $rows->where('sellable', true)->values(),
            'rowsById'    => $rows->keyBy('product_id'),
            'returns'     => StockReturn::where('shop_id', $this->shopId)->withCount('boxes')->with(['sentBy:id,name', 'receivedBy:id,name', 'warehouse:id,name'])->latest('sent_at')->limit(30)->get(),
            'shops'       => auth()->user()->isOwner() ? Shop::orderBy('name')->get(['id', 'name']) : collect(),
            'selectedBoxes' => collect($this->send)->sum(fn ($n) => max(0, (int) $n)),
        ]);
    }
}
