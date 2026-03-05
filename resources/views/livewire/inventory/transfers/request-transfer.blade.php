<div>
<style>
/* ─── Request Transfer Form ─────────────────────────── */
.rt-wrap {
    display:grid; grid-template-columns:320px 1fr; gap:20px;
    align-items:start;
}
@media(max-width:900px){ .rt-wrap{ grid-template-columns:1fr; } }

/* ── Page header */
.rt-page-header h1 { font-size:24px; font-weight:800; letter-spacing:-.5px; color:var(--text); margin:0 0 3px; }
.rt-page-header p  { font-size:13px; color:var(--text-sub); margin:0 0 20px; }

/* ── Card shell */
.rt-card {
    background:var(--surface); border:1px solid var(--border); border-radius:var(--r);
    overflow:hidden;
}
.rt-card-head {
    display:flex; align-items:center; gap:10px;
    padding:14px 18px; border-bottom:1px solid var(--border); background:var(--surface2);
}
.rt-card-head-ico {
    width:32px; height:32px; border-radius:var(--rsm,6px);
    background:rgba(99,102,241,.1); color:var(--accent);
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.rt-card-head h2 { font-size:14px; font-weight:700; color:var(--text); margin:0; }
.rt-card-body { padding:18px; display:flex; flex-direction:column; gap:14px; }

/* ── Form controls */
.rt-field { display:flex; flex-direction:column; gap:4px; }
.rt-label {
    font-size:10px; font-weight:700; letter-spacing:.8px;
    text-transform:uppercase; color:var(--text-sub);
}
.rt-select,.rt-textarea,.rt-input {
    width:100%; padding:9px 12px;
    background:var(--surface2); border:1px solid var(--border);
    border-radius:var(--rsm,6px); color:var(--text); font-size:13px;
    outline:none; transition:border-color .15s; box-sizing:border-box;
}
.rt-select:focus,.rt-textarea:focus,.rt-input:focus { border-color:var(--accent); }
.rt-error { font-size:11px; color:#ef4444; margin-top:2px; }
.rt-textarea { resize:vertical; min-height:80px; }

/* Route arrow between selects */
.rt-route-arrow {
    display:flex; align-items:center; justify-content:center;
    color:var(--text-sub); padding:4px 0;
}

/* ── Sticky sidebar */
.rt-sidebar { position:sticky; top:20px; display:flex; flex-direction:column; gap:16px; }

/* ── Product picker trigger */
.rt-picker-trigger {
    width:100%; display:flex; align-items:center; justify-content:space-between;
    padding:12px 14px; background:var(--surface2); border:1.5px solid var(--border);
    border-radius:var(--rsm,6px); cursor:pointer; transition:all .15s; text-align:left;
}
.rt-picker-trigger:not([disabled]):hover { border-color:var(--accent); }
.rt-picker-trigger[disabled] { opacity:.5; cursor:not-allowed; }
.rt-picker-trigger-left { display:flex; align-items:center; gap:10px; }
.rt-picker-trigger-ico {
    width:34px; height:34px; border-radius:var(--rsm,6px);
    background:rgba(99,102,241,.1); color:var(--accent);
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.rt-picker-trigger-title { font-size:13px; font-weight:700; color:var(--text); }
.rt-picker-trigger-sub   { font-size:11px; color:var(--text-sub); }
.rt-picker-chevron { color:var(--text-sub); transition:transform .2s; }
.rt-picker-chevron.open { transform:rotate(180deg); }

/* ── Product dropdown */
.rt-dropdown {
    position:absolute; top:calc(100% + 6px); left:0; right:0; z-index:50;
    background:var(--surface); border:1px solid var(--border); border-radius:var(--r);
    box-shadow:0 12px 40px rgba(0,0,0,.15); overflow:hidden;
}
.rt-dropdown-search {
    padding:12px 14px; border-bottom:1px solid var(--border);
    background:var(--surface2); position:relative;
}
.rt-dropdown-search input {
    width:100%; padding:8px 10px 8px 34px;
    background:var(--surface); border:1px solid var(--border);
    border-radius:var(--rsm,6px); color:var(--text); font-size:13px;
    outline:none; transition:border-color .15s; box-sizing:border-box;
}
.rt-dropdown-search input:focus { border-color:var(--accent); }
.rt-search-ico {
    position:absolute; left:22px; top:50%; transform:translateY(-50%);
    color:var(--text-sub); pointer-events:none;
}
.rt-dropdown-meta {
    display:flex; justify-content:space-between; align-items:center;
    padding:6px 14px; border-bottom:1px solid var(--border);
    font-size:10px; font-weight:700; letter-spacing:.7px;
    text-transform:uppercase; color:var(--text-sub); background:var(--surface2);
}
.rt-dropdown-list { max-height:320px; overflow-y:auto; }

/* Product row */
.rt-product-row {
    display:flex; align-items:center; justify-content:space-between;
    padding:10px 14px; border-bottom:1px solid var(--border);
    cursor:pointer; transition:background .12s; background:var(--surface);
    text-align:left; width:100%;
}
.rt-product-row:last-child { border-bottom:none; }
.rt-product-row:not([disabled]):hover { background:var(--surface2); }
.rt-product-row[disabled] { opacity:.55; cursor:not-allowed; }
.rt-product-row.in-cart   { background:rgba(16,185,129,.04); }
.rt-product-row-left { display:flex; align-items:center; gap:10px; flex:1; min-width:0; }
.rt-product-dot {
    width:28px; height:28px; border-radius:50%; flex-shrink:0;
    display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700;
    background:rgba(99,102,241,.1); color:var(--accent);
}
.rt-product-dot.added  { background:rgba(16,185,129,.15); color:var(--green); }
.rt-product-dot.empty  { background:rgba(128,128,128,.1); color:var(--text-sub); }
.rt-product-name { font-size:13px; font-weight:600; color:var(--text); }
.rt-product-stock { display:flex; gap:4px; flex-wrap:wrap; margin-top:2px; }
.rt-stock-chip {
    font-size:9px; font-weight:700; letter-spacing:.4px; text-transform:uppercase;
    padding:1px 6px; border-radius:4px;
}
.rt-stock-chip.sealed   { background:rgba(16,185,129,.12); color:var(--green); }
.rt-stock-chip.opened   { background:rgba(217,119,6,.12); color:#d97706; }
.rt-stock-chip.total    { background:rgba(99,102,241,.1); color:var(--accent); }
.rt-stock-chip.out      { background:rgba(239,68,68,.1); color:#ef4444; }
.rt-stock-chip.added-lbl{ background:rgba(16,185,129,.15); color:var(--green); }
.rt-product-add-ico { color:var(--text-sub); flex-shrink:0; }

/* ── Cart panel */
.rt-cart { display:flex; flex-direction:column; }
.rt-cart-empty {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    padding:48px 24px; color:var(--text-sub); text-align:center; gap:8px;
}
.rt-cart-empty-ico {
    width:52px; height:52px; border-radius:50%; border:2px dashed var(--border);
    display:flex; align-items:center; justify-content:center; color:var(--text-sub);
    margin-bottom:4px;
}
.rt-cart-empty h3 { font-size:15px; font-weight:700; color:var(--text); margin:0; }
.rt-cart-empty p  { font-size:12px; color:var(--text-sub); margin:0; }

/* Cart item row */
.rt-cart-item {
    padding:14px 18px; border-bottom:1px solid var(--border);
    display:flex; flex-direction:column; gap:10px;
    transition:background .12s;
}
.rt-cart-item:last-child { border-bottom:none; }
.rt-cart-item.over-stock { background:rgba(239,68,68,.03); }

.rt-cart-item-top { display:flex; align-items:center; gap:10px; }
.rt-cart-item-num {
    width:24px; height:24px; border-radius:var(--rsm,6px);
    background:rgba(99,102,241,.1); color:var(--accent);
    font-size:10px; font-weight:800; display:flex; align-items:center; justify-content:center;
    flex-shrink:0;
}
.rt-cart-item-name { font-size:13px; font-weight:700; color:var(--text); flex:1; min-width:0; }
.rt-cart-item-remove {
    background:none; border:none; cursor:pointer; padding:4px;
    color:var(--text-sub); transition:color .12s; border-radius:4px;
}
.rt-cart-item-remove:hover { color:#ef4444; background:rgba(239,68,68,.08); }

.rt-cart-item-fields { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.rt-cart-num-input {
    width:100%; padding:8px 10px;
    background:var(--surface2); border:1px solid var(--border);
    border-radius:var(--rsm,6px); color:var(--text); font-size:13px; font-weight:700;
    outline:none; transition:border-color .15s; box-sizing:border-box;
}
.rt-cart-num-input:focus { border-color:var(--accent); }
.rt-cart-num-input.over  { border-color:#ef4444; background:rgba(239,68,68,.06); color:#ef4444; }
.rt-est-units {
    display:flex; align-items:center; justify-content:space-between;
    padding:8px 10px; background:var(--surface2); border:1px solid var(--border);
    border-radius:var(--rsm,6px); height:38px; box-sizing:border-box;
}
.rt-est-units-per { font-size:10px; color:var(--text-sub); }
.rt-est-units-val  { font-size:14px; font-weight:800; color:var(--text); }
.rt-cart-overstock {
    display:flex; align-items:center; gap:6px;
    font-size:11px; font-weight:600; color:#ef4444;
    padding:6px 10px; background:rgba(239,68,68,.07); border-radius:var(--rsm,6px);
}

/* ── Cart summary footer */
.rt-cart-summary {
    padding:14px 18px; border-top:1px solid var(--border);
    background:var(--surface2);
}
.rt-summary-row {
    display:flex; align-items:center; justify-content:space-between;
    margin-bottom:12px; gap:8px; flex-wrap:wrap;
}
.rt-summary-label { font-size:11px; color:var(--text-sub); }
.rt-summary-stats { display:flex; gap:16px; }
.rt-summary-stat  { text-align:right; }
.rt-summary-stat-v { font-size:18px; font-weight:800; color:var(--text); line-height:1; }
.rt-summary-stat-l { font-size:9px; font-weight:700; letter-spacing:.8px; text-transform:uppercase; color:var(--text-sub); }
.rt-summary-divider { width:1px; background:var(--border); align-self:stretch; }

/* ── Form actions */
.rt-actions {
    display:flex; align-items:center; justify-content:flex-end; gap:10px;
    padding:14px 18px; border-top:1px solid var(--border); background:var(--surface2);
}
.rt-cancel {
    padding:9px 18px; border-radius:var(--rsm,6px);
    border:1px solid var(--border); background:var(--surface);
    color:var(--text-sub); font-size:13px; font-weight:600;
    text-decoration:none; transition:all .15s;
}
.rt-cancel:hover { border-color:var(--accent); color:var(--accent); }
.rt-submit-btn {
    padding:9px 24px; border-radius:var(--rsm,6px);
    border:none; background:var(--accent); color:#fff;
    font-size:13px; font-weight:700; cursor:pointer; transition:opacity .15s;
    display:inline-flex; align-items:center; gap:6px; min-width:160px; justify-content:center;
}
.rt-submit-btn:hover:not([disabled]) { opacity:.88; }
.rt-submit-btn[disabled] { opacity:.45; cursor:not-allowed; }

/* Dropdown empty */
.rt-dropdown-empty {
    padding:40px 16px; text-align:center; color:var(--text-sub);
}
.rt-dropdown-empty h4 { font-size:14px; font-weight:700; color:var(--text); margin:0 0 4px; }
.rt-dropdown-empty p  { font-size:12px; margin:0; }
</style>

{{-- ── Page header ────────────────────────────────── --}}
<div class="rt-page-header">
  <h1>New Transfer Request</h1>
  <p>Build a stock transfer request from warehouse to shop</p>
</div>

<form wire:submit.prevent="submit">
<div class="rt-wrap">

  {{-- ══ LEFT SIDEBAR — Transfer Config ═════════════════ --}}
  <div class="rt-sidebar">
    <div class="rt-card">
      <div class="rt-card-head">
        <div class="rt-card-head-ico">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <h2>Transfer Config</h2>
      </div>
      <div class="rt-card-body">

        {{-- From Warehouse --}}
        <div class="rt-field">
          <label class="rt-label">From Warehouse</label>
          <select wire:model.live="fromWarehouseId" class="rt-select">
            <option value="">Select Warehouse</option>
            @foreach($this->warehouses as $warehouse)
              <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
            @endforeach
          </select>
          @error('fromWarehouseId')<span class="rt-error">{{ $message }}</span>@enderror
        </div>

        {{-- Arrow divider --}}
        <div class="rt-route-arrow">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/><polyline points="18 13 12 19 6 13"/>
          </svg>
        </div>

        {{-- To Shop --}}
        <div class="rt-field">
          <label class="rt-label">To Shop</label>
          <select wire:model.live="toShopId" class="rt-select">
            <option value="">Select Shop</option>
            @foreach($this->shops as $shop)
              <option value="{{ $shop->id }}">{{ $shop->name }}</option>
            @endforeach
          </select>
          @error('toShopId')<span class="rt-error">{{ $message }}</span>@enderror
        </div>

        {{-- Notes --}}
        <div class="rt-field">
          <label class="rt-label">Internal Notes</label>
          <textarea wire:model="notes" class="rt-textarea" placeholder="Purpose of this transfer…" rows="3"></textarea>
        </div>

      </div>
    </div>
  </div>

  {{-- ══ RIGHT COLUMN — Product Picker + Cart ═══════════ --}}
  <div style="display:flex;flex-direction:column;gap:16px;">

    {{-- ── Product Picker ─────────────────────────────── --}}
    <div class="rt-card" style="position:relative;z-index:30"
         x-data="{ open: @entangle('dropdownOpen') }"
         @click.away="open = false">

      <div class="rt-card-head">
        <div class="rt-card-head-ico">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        <h2>Add Products</h2>
        @if(count($items) > 0)
          <span style="margin-left:auto;font-size:11px;color:var(--accent);font-weight:700;">{{ count($items) }} selected</span>
        @endif
      </div>

      <div class="rt-card-body" style="padding-bottom:14px;">
        {{-- Trigger button --}}
        <button
          type="button"
          @click="open = !open"
          @if(!$fromWarehouseId) disabled @endif
          class="rt-picker-trigger"
        >
          <div class="rt-picker-trigger-left">
            <div class="rt-picker-trigger-ico">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </div>
            <div>
              <div class="rt-picker-trigger-title">
                @if($fromWarehouseId) Browse warehouse inventory @else Select a warehouse first @endif
              </div>
              <div class="rt-picker-trigger-sub">
                @if(count($items) > 0) {{ count($items) }} {{ Str::plural('product', count($items)) }} in request @else Click to search and add products @endif
              </div>
            </div>
          </div>
          <svg class="rt-picker-chevron" :class="{'open': open}" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
        </button>

        {{-- Dropdown --}}
        <div
          x-show="open"
          x-transition:enter="transition ease-out duration-150"
          x-transition:enter-start="opacity-0 translate-y-1"
          x-transition:enter-end="opacity-100 translate-y-0"
          x-transition:leave="transition ease-in duration-100"
          x-transition:leave-start="opacity-100 translate-y-0"
          x-transition:leave-end="opacity-0 translate-y-1"
          class="rt-dropdown"
          style="display:none"
        >
          {{-- Search --}}
          <div class="rt-dropdown-search" style="position:relative">
            <svg class="rt-search-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <svg wire:loading wire:target="search" style="position:absolute;left:22px;top:50%;transform:translateY(-50%);animation:spin 1s linear infinite;color:var(--accent)" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a10 10 0 0110 10"/></svg>
            <input
              type="text"
              wire:model.live.debounce.300ms="search"
              placeholder="Search by name or SKU…"
              autofocus
              class="rt-input"
              style="padding-left:34px"
            >
            @if(strlen($search) > 0)
              <button type="button" wire:click="$set('search','')"
                style="position:absolute;right:22px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-sub);padding:2px">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            @endif
          </div>
          <div class="rt-dropdown-meta">
            <span>{{ strlen($search) > 0 ? count($products).' results' : 'Recent inventory' }}</span>
            <span>Stock Status</span>
          </div>

          {{-- Product list --}}
          <div class="rt-dropdown-list">
            @forelse($products as $product)
            @php
              $stock = $stockLevels[$product->id] ?? ['total_boxes'=>0,'full_boxes'=>0,'partial_boxes'=>0];
              $isOutOfStock = $stock['total_boxes'] == 0;
              $isInCart = false;
              foreach ($items as $item) {
                if ($item['product_id'] == $product->id) { $isInCart = true; break; }
              }
            @endphp
            <button
              type="button"
              wire:click="addProductToCart({{ $product->id }})"
              wire:loading.attr="disabled"
              wire:target="addProductToCart({{ $product->id }})"
              @if($isOutOfStock || $isInCart) disabled @endif
              class="rt-product-row {{ $isInCart ? 'in-cart' : '' }}"
            >
              <div class="rt-product-row-left">
                <div class="rt-product-dot {{ $isInCart ? 'added' : ($isOutOfStock ? 'empty' : '') }}">
                  @if($isInCart)
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  @else
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                  @endif
                </div>
                <div>
                  <div class="rt-product-name">{{ $product->name }}</div>
                  <div class="rt-product-stock">
                    @if($isInCart)
                      <span class="rt-stock-chip added-lbl">Added</span>
                    @elseif($isOutOfStock)
                      <span class="rt-stock-chip out">Out of Stock</span>
                    @else
                      <span class="rt-stock-chip sealed">{{ $stock['full_boxes'] }} sealed</span>
                      <span class="rt-stock-chip opened">{{ $stock['partial_boxes'] }} opened</span>
                      <span class="rt-stock-chip total">{{ $stock['total_boxes'] }} total</span>
                    @endif
                  </div>
                </div>
              </div>
              @if(!$isOutOfStock && !$isInCart)
                <div class="rt-product-add-ico">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </div>
              @endif
            </button>
            @empty
            <div class="rt-dropdown-empty">
              @if(strlen($search) > 0)
                <h4>No products found</h4>
                <p>Try different keywords or SKU</p>
              @else
                <h4>Start searching</h4>
                <p>Type to find products in warehouse stock</p>
              @endif
            </div>
            @endforelse
          </div>
        </div>
      </div>
    </div>

    {{-- ── Transfer Cart ─────────────────────────────── --}}
    <div class="rt-card rt-cart">
      <div class="rt-card-head">
        <div class="rt-card-head-ico">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        </div>
        <h2>Transfer Cart</h2>
        <span style="margin-left:4px;font-size:11px;background:var(--surface2);color:var(--text-sub);padding:1px 7px;border-radius:10px;border:1px solid var(--border);">{{ count($items) }}</span>
        @if(count($items) > 0)
          <button type="button" wire:click="$set('items',[])"
                  style="margin-left:auto;font-size:11px;color:#ef4444;font-weight:600;background:none;border:none;cursor:pointer;padding:2px 8px;border-radius:4px">
            Clear All
          </button>
        @endif
      </div>

      @if(count($items) === 0)
        <div class="rt-cart-empty">
          <div class="rt-cart-empty-ico">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
          </div>
          <h3>Cart is empty</h3>
          <p>Add products via the picker above</p>
        </div>
      @else
        @foreach($items as $index => $item)
        @php
          $product = $products->firstWhere('id', $item['product_id']);
          if (!$product) { $product = \App\Models\Product::find($item['product_id']); }
          $stock = $stockLevels[$product->id] ?? ['total_boxes'=>0,'full_boxes'=>0,'partial_boxes'=>0];
          $availableBoxes = $stock['total_boxes'];
          $requestedBoxes = $item['boxes_requested'] ?? 0;
          $totalItems = $requestedBoxes * ($product->items_per_box ?? 0);
          $exceedsStock = $requestedBoxes > $availableBoxes;
        @endphp
        @if($product)
        <div wire:key="item-{{ $item['product_id'] }}" class="rt-cart-item {{ $exceedsStock && $requestedBoxes > 0 ? 'over-stock' : '' }}">
          <div class="rt-cart-item-top">
            <div class="rt-cart-item-num">{{ $index + 1 }}</div>
            <div class="rt-cart-item-name">{{ $product->name }}</div>
            <button type="button" wire:click="removeItem({{ $index }})" class="rt-cart-item-remove" title="Remove">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <div class="rt-cart-item-fields">
            <div class="rt-field">
              <label class="rt-label">Boxes (avail. {{ $availableBoxes }})</label>
              <input
                type="number"
                wire:model.live="items.{{ $index }}.boxes_requested"
                min="0"
                max="{{ $availableBoxes }}"
                class="rt-cart-num-input {{ $exceedsStock && $requestedBoxes > 0 ? 'over' : '' }}"
                placeholder="0"
              >
              @error("items.{$index}.boxes_requested")<span class="rt-error">{{ $message }}</span>@enderror
            </div>
            <div class="rt-field">
              <label class="rt-label">Est. Units</label>
              <div class="rt-est-units">
                <span class="rt-est-units-per">{{ $product->items_per_box ?? 0 }}/box</span>
                <span class="rt-est-units-val">{{ number_format($totalItems) }}</span>
              </div>
            </div>
          </div>
          @if($exceedsStock && $requestedBoxes > 0)
            <div class="rt-cart-overstock">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2zm0 3.5L20.5 19h-17L12 5.5zM11 10v5h2v-5h-2zm0 6v2h2v-2h-2z"/></svg>
              Exceeds stock by {{ $requestedBoxes - $availableBoxes }} boxes
            </div>
          @endif
        </div>
        @endif
        @endforeach

        {{-- ── Summary footer ── --}}
        @php
          $grandTotalBoxes = 0; $grandTotalItems = 0;
          foreach($items as $itm){
            $bx = (int)($itm['boxes_requested'] ?? 0);
            $grandTotalBoxes += $bx;
            $prd = $products->firstWhere('id',$itm['product_id']) ?? \App\Models\Product::find($itm['product_id']);
            if($prd) $grandTotalItems += $bx * ($prd->items_per_box ?? 0);
          }
        @endphp
        <div class="rt-cart-summary">
          <div class="rt-summary-row">
            <span class="rt-summary-label">{{ count($items) }} {{ Str::plural('product', count($items)) }} in request</span>
            <div class="rt-summary-stats">
              <div class="rt-summary-stat">
                <div class="rt-summary-stat-v">{{ number_format($grandTotalBoxes) }}</div>
                <div class="rt-summary-stat-l">Total Boxes</div>
              </div>
              <div class="rt-summary-divider"></div>
              <div class="rt-summary-stat" style="color:var(--accent)">
                <div class="rt-summary-stat-v" style="color:var(--accent)">{{ number_format($grandTotalItems) }}</div>
                <div class="rt-summary-stat-l">Est. Units</div>
              </div>
            </div>
          </div>
        </div>
      @endif

      {{-- ── Form actions ── --}}
      <div class="rt-actions">
        <a href="{{ route('shop.transfers.index') }}" class="rt-cancel">Cancel</a>
        <button
          type="submit"
          class="rt-submit-btn"
          @if(count($items) == 0) disabled @endif
          wire:loading.attr="disabled"
        >
          <span wire:loading.remove>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
            Submit Request
          </span>
          <span wire:loading style="display:flex;align-items:center;gap:6px">
            <svg style="animation:spin 1s linear infinite" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2a10 10 0 0110 10"/></svg>
            Submitting…
          </span>
        </button>
      </div>
    </div>

  </div>
</div>
</form>
</div>