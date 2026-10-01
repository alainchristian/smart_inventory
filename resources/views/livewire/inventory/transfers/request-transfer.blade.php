{{--
    New stock request (Inventory\Transfers\RequestTransfer). Prefix rf-;
    shared transfer parts from <x-transfers.*> (tf-).
--}}
<div class="rf-page" style="font-family:var(--font)">
<x-transfers.styles />
<style>
.rf-page { padding:0 0 80px }
.rf-layout { display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:16px;align-items:start }
.rf-card { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);min-width:0;margin-bottom:16px }
.rf-card-head { padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap }
.rf-card-title { font-size:13px;font-weight:700;color:var(--text);margin:0 }
.rf-card-sub { font-size:12px;color:var(--text-dim);margin-top:2px }
.rf-card-body { padding:16px 18px }

.rf-search-wrap { position:relative;flex:1;min-width:220px;max-width:360px }
.rf-search-ico { position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--text-dim);pointer-events:none }
.rf-search { width:100%;padding:9px 11px 9px 34px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;background:var(--surface);
             color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font) }
.rf-search:focus, .rf-select:focus, .rf-textarea:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }

.rf-table { width:100%;border-collapse:collapse }
.rf-table thead tr { border-bottom:2px solid var(--border) }
.rf-table th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.rf-table td { padding:11px 16px;font-size:13px;color:var(--text-sub);vertical-align:middle;white-space:nowrap }
.rf-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.rf-table tbody tr:last-child { border-bottom:none }
.rf-table tbody tr:hover { background:var(--surface2) }
.rf-table tr.out td { color:var(--text-dim) }
.rf-table tr.added { background:var(--accent-dim) }
.rf-r { text-align:right !important }
.rf-prod { font-weight:600;color:var(--text);max-width:280px;overflow:hidden;text-overflow:ellipsis }
.rf-sub { font-size:12px;color:var(--text-dim);margin-top:2px }
.rf-n { font:700 13px var(--mono);color:var(--text) }
.rf-n.low { color:var(--amber) }
.rf-n.none { color:var(--red) }
.rf-add { padding:5px 12px;border-radius:7px;border:1.5px solid var(--accent);background:var(--surface);color:var(--accent);
          font:600 12px var(--font);cursor:pointer;white-space:nowrap;transition:all var(--tr) }
.rf-add:hover { background:var(--accent);color:#fff }
.rf-add:disabled { border-color:var(--border);color:var(--text-dim);background:transparent;cursor:default }
.rf-empty { padding:40px 18px;text-align:center;font-size:13px;color:var(--text-dim) }

/* Request panel */
.rf-panel { position:sticky;top:calc(var(--topbar-height) + 16px) }
.rf-field { margin-bottom:14px }
.rf-label { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.rf-select, .rf-textarea { width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--surface);
                           color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font) }
.rf-textarea { resize:vertical;min-height:64px }
.rf-static { font-size:14px;font-weight:600;color:var(--text) }
.rf-error { font-size:11px;color:var(--red);margin-top:4px }
.rf-basket { border-top:1px solid var(--border);border-bottom:1px solid var(--border);margin:0 -18px;padding:4px 18px }
.rf-line { padding:10px 0;border-bottom:1px solid var(--border) }
.rf-line:last-child { border-bottom:none }
.rf-line-top { display:flex;justify-content:space-between;gap:10px;align-items:flex-start }
.rf-line-name { font-size:13px;font-weight:600;color:var(--text);min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap }
.rf-rm { width:24px;height:24px;flex-shrink:0;border:none;border-radius:6px;background:transparent;color:var(--text-dim);cursor:pointer;font-size:16px;line-height:1 }
.rf-rm:hover { background:var(--red-dim);color:var(--red) }
.rf-line-qty { display:flex;align-items:center;gap:8px;margin-top:6px }
.rf-step { display:inline-flex;align-items:center;border-radius:8px;box-shadow:0 0 0 1.5px var(--border) }
.rf-step button { width:30px;height:30px;border:none;background:transparent;color:var(--text-sub);font-size:16px;cursor:pointer }
.rf-step button:hover { color:var(--accent) }
.rf-qty { width:48px;height:30px;border:none;border-left:1.5px solid var(--border);border-right:1.5px solid var(--border);
          font:700 14px var(--mono);color:var(--text);background:var(--surface);outline:none;padding:0 4px }
.rf-qty.over { color:var(--red) }
.rf-line-meta { font-size:12px;color:var(--text-dim) }
.rf-warn { font-size:11px;color:var(--red);margin-top:5px }
.rf-totals { display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin:14px 0 }
.rf-totals > div { text-align:center;padding:8px 4px;border-radius:9px;box-shadow:0 0 0 1px var(--border) }
.rf-total-v { font:800 17px var(--mono);color:var(--text);line-height:1.1 }
.rf-total-l { font-size:11px;color:var(--text-dim);margin-top:2px }
.rf-submit { width:100%;justify-content:center;padding:11px 16px;font-size:14px }
.rf-flash { font-size:12px;margin-top:10px;line-height:1.5 }
.rf-basket-empty { padding:18px 0;text-align:center;font-size:13px;color:var(--text-dim) }

@media (max-width:1000px) {
    .rf-layout { grid-template-columns:1fr }
    .rf-panel { position:static }
}
@media (max-width:640px) {
    .rf-card-head, .rf-card-body { padding-left:12px;padding-right:12px }
    .rf-basket { margin:0 -12px;padding:4px 12px }
    .rf-search-wrap { max-width:none }
    .rf-search, .rf-select, .rf-textarea { font-size:16px }
}
@keyframes rf-spin { to { transform:rotate(360deg) } }
</style>

<x-transfers.header title="New stock request" sub="Ask the warehouse for boxes. It checks stock and may adjust the quantities." :back="route('shop.transfers.index')" dup-title />

@php
    $isShopManager = auth()->user()->isShopManager();
    $inBasket = collect($items)->pluck('product_id')->map(fn ($id) => (int) $id)->all();
    $productsById = $products->keyBy('id');
    $lineProduct = fn ($id) => $productsById[$id] ?? \App\Models\Product::find($id);
    $totalBoxes = 0; $totalItems = 0;
    foreach ($items as $i) {
        $bx = (int) ($i['boxes_requested'] ?? 0);
        $totalBoxes += $bx;
        $totalItems += $bx * (int) ($lineProduct($i['product_id'])?->items_per_box ?? 0);
    }
@endphp

<form wire:submit.prevent="submit" class="rf-layout">
    {{-- ═══ Products ═════════════════════════════════════════════ --}}
    <div class="rf-card">
        <div class="rf-card-head">
            <div>
                <h2 class="rf-card-title">Products</h2>
                <div class="rf-card-sub">
                    @if($isSpecialised)
                        Only what this shop sells: {{ $sellsLabel }}
                    @else
                        Boxes available in the warehouse, and what your shop already holds
                    @endif
                </div>
            </div>
            <div class="rf-search-wrap">
                <svg class="rf-search-ico" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                <input class="rf-search" type="text" wire:model.live.debounce.300ms="search" placeholder="Search name, SKU or category…" aria-label="Search products">
            </div>
        </div>

        @if(!$fromWarehouseId)
            <div class="rf-empty">Choose the warehouse on the right to see its stock.</div>
        @elseif($products->isEmpty())
            <div class="rf-empty">No products match "{{ $search }}".</div>
        @else
            <div class="m-scroll">
                <table class="rf-table m-sticky-first" style="min-width:640px">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="rf-r">In warehouse</th>
                            <th class="rf-r">In your shop</th>
                            <th class="rf-r"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            @php
                                $wh = $stockLevels[$product->id] ?? ['full_boxes' => 0, 'partial_boxes' => 0, 'total_boxes' => 0];
                                $here = $shopStock[$product->id] ?? null;
                                $added = in_array($product->id, $inBasket, true);
                                $has = $wh['total_boxes'] > 0;
                            @endphp
                            <tr class="{{ $added ? 'added' : '' }} {{ $has ? '' : 'out' }}" wire:key="rp-{{ $product->id }}">
                                <td>
                                    <div class="rf-prod" title="{{ $product->name }}">{{ $product->name }}</div>
                                    <div class="rf-sub">{{ collect([$product->sku, $product->category?->name, $product->items_per_box . '/box'])->filter()->implode(' · ') }}</div>
                                </td>
                                <td class="rf-r">
                                    @if($has)
                                        <span class="rf-n {{ $wh['total_boxes'] <= 5 ? 'low' : '' }}">{{ $wh['total_boxes'] }}</span> <span class="rf-sub">{{ Str::plural('box', $wh['total_boxes']) }}</span>
                                        <div class="rf-sub">{{ $wh['full_boxes'] }} sealed{{ $wh['partial_boxes'] ? ' · ' . $wh['partial_boxes'] . ' opened' : '' }}</div>
                                    @else
                                        <span class="rf-n none">Out of stock</span>
                                    @endif
                                </td>
                                <td class="rf-r">
                                    @if($here)
                                        <span class="rf-n">{{ $here['boxes'] }}</span> <span class="rf-sub">{{ Str::plural('box', $here['boxes']) }}</span>
                                        <div class="rf-sub">{{ number_format($here['items']) }} items</div>
                                    @else
                                        <span class="rf-n none">None</span>
                                    @endif
                                </td>
                                <td class="rf-r">
                                    <button type="button" class="rf-add" wire:click="addProductToCart({{ $product->id }})"
                                            @disabled($added || ! $has) wire:loading.attr="disabled" wire:target="addProductToCart({{ $product->id }})">
                                        {{ $added ? 'Added' : ($has ? 'Add' : '—') }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ═══ The request ═════════════════════════════════════════ --}}
    <div class="rf-panel">
        <div class="rf-card">
            <div class="rf-card-head"><h2 class="rf-card-title">Your request</h2></div>
            <div class="rf-card-body">
                <div class="rf-field">
                    <label class="rf-label" for="rf-from">From</label>
                    <select id="rf-from" wire:model.live="fromWarehouseId" class="rf-select">
                        <option value="">Choose a warehouse…</option>
                        @foreach($this->warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @error('fromWarehouseId')<div class="rf-error">{{ $message }}</div>@enderror
                </div>
                <div class="rf-field">
                    <label class="rf-label" for="rf-to">To</label>
                    @if($isShopManager)
                        <div class="rf-static">{{ $this->shops->firstWhere('id', $toShopId)?->name ?? '—' }}</div>
                    @else
                        <select id="rf-to" wire:model.live="toShopId" class="rf-select">
                            <option value="">Choose a shop…</option>
                            @foreach($this->shops as $sh)
                                <option value="{{ $sh->id }}">{{ $sh->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    @error('toShopId')<div class="rf-error">{{ $message }}</div>@enderror
                </div>

                <div class="rf-basket">
                    @forelse($items as $index => $item)
                        @php
                            $p = $lineProduct($item['product_id']);
                            $boxes = (int) ($item['boxes_requested'] ?? 0);
                            $avail = $stockLevels[$item['product_id']]['total_boxes'] ?? 0;
                            $over = $boxes > $avail && $boxes > 0;
                        @endphp
                        @if($p)
                            <div class="rf-line" wire:key="rl-{{ $item['product_id'] }}">
                                <div class="rf-line-top">
                                    <div class="rf-line-name" title="{{ $p->name }}">{{ $p->name }}</div>
                                    <button type="button" class="rf-rm m-tap" wire:click="removeItem({{ $index }})" aria-label="Remove {{ $p->name }}">×</button>
                                </div>
                                <div class="rf-line-qty">
                                    <div class="rf-step">
                                        <button type="button" wire:click="decrementItem({{ $index }})" aria-label="One box less">−</button>
                                        <x-number-input class="rf-qty {{ $over ? 'over' : '' }}" align="center" wire:model.live.debounce.300ms="items.{{ $index }}.boxes_requested" aria-label="Boxes of {{ $p->name }}" />
                                        <button type="button" wire:click="incrementItem({{ $index }})" aria-label="One box more">+</button>
                                    </div>
                                    <span class="rf-line-meta">{{ Str::plural('box', $boxes) }} · {{ number_format($boxes * (int) $p->items_per_box) }} items</span>
                                </div>
                                @if($over)
                                    <div class="rf-warn">Only {{ $avail }} in the warehouse.</div>
                                @endif
                                @error("items.{$index}.boxes_requested")<div class="rf-warn">{{ $message }}</div>@enderror
                            </div>
                        @endif
                    @empty
                        <div class="rf-basket-empty">Add products from the list.</div>
                    @endforelse
                </div>

                <div class="rf-totals">
                    <div><div class="rf-total-v">{{ count($items) }}</div><div class="rf-total-l">Products</div></div>
                    <div><div class="rf-total-v">{{ number_format($totalBoxes) }}</div><div class="rf-total-l">Boxes</div></div>
                    <div><div class="rf-total-v">{{ number_format($totalItems) }}</div><div class="rf-total-l">Items</div></div>
                </div>

                <div class="rf-field">
                    <label class="rf-label" for="rf-needed">Needed by <span style="font-weight:500;color:var(--text-dim)">(optional)</span></label>
                    <input id="rf-needed" type="date" class="rf-select" wire:model="neededBy" min="{{ business_today()->toDateString() }}">
                    @error('neededBy')<div class="rf-error">{{ $message }}</div>@enderror
                </div>

                <div class="rf-field">
                    <label class="rf-label" for="rf-notes">Note for the warehouse <span style="font-weight:500;color:var(--text-dim)">(optional)</span></label>
                    <textarea id="rf-notes" wire:model="notes" class="rf-textarea" maxlength="1000" placeholder="e.g. For the weekend promotion"></textarea>
                </div>

                <button type="submit" class="tf-btn tf-btn-primary rf-submit" wire:loading.attr="disabled" wire:target="submit"
                        @disabled(empty($items) || ! $fromWarehouseId || ! $toShopId)>
                    <span wire:loading.remove wire:target="submit">Send request</span>
                    <span wire:loading wire:target="submit" style="display:none;align-items:center;gap:6px">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="animation:rf-spin 1s linear infinite"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                        Sending…
                    </span>
                </button>
                @if(session('error'))<div class="rf-flash" style="color:var(--red)">{{ session('error') }}</div>@endif
            </div>
        </div>
    </div>
</form>
</div>
