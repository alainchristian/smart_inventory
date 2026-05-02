<div>
<style>
/* ─── Request Transfer Form ───────────────────────── */
.rf-breadcrumb { font-size:17px; color:var(--text-sub); margin-bottom:16px; }
.rf-breadcrumb a { color:var(--accent); text-decoration:none; }
.rf-page-head h1 { font-size:31px; font-weight:800; letter-spacing:-.4px; color:var(--text); margin:0 0 3px; }
.rf-page-head p  { font-size:19px; color:var(--text-sub); margin:0 0 24px; }

/* Two-column layout */
.rf-layout { display:grid; grid-template-columns:1fr 320px; gap:20px; align-items:start; }
@media(max-width:860px){ .rf-layout { grid-template-columns:1fr; } }

/* Cards */
.rf-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--r); overflow:hidden; margin-bottom:16px; }
.rf-card-head {
    display:flex; align-items:center; gap:10px;
    padding:12px 18px; border-bottom:1px solid var(--border); background:var(--surface2);
}
.rf-card-icon {
    width:30px; height:30px; border-radius:8px;
    display:flex; align-items:center; justify-content:center;
    background:var(--icon-bg,rgba(99,102,241,.12)); color:var(--icon-c,var(--accent));
    flex-shrink:0;
}
.rf-card-head-text h3 { font-size:19px; font-weight:700; color:var(--text); margin:0; }
.rf-card-head-text p  { font-size:16px; color:var(--text-sub); margin:0; }
.rf-card-body { padding:18px; }

/* Form fields */
.rf-row2   { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.rf-field  { display:flex; flex-direction:column; gap:5px; }
.rf-label  { font-size:14px; font-weight:700; letter-spacing:.9px; text-transform:uppercase; color:var(--text-sub); }
.rf-select, .rf-input, .rf-textarea {
    padding:9px 11px; border-radius:var(--rsm,6px);
    border:1px solid var(--border); background:var(--surface2); color:var(--text);
    font-size:19px; outline:none; transition:border-color .15s; font-family:inherit; width:100%; box-sizing:border-box;
}
.rf-select:focus,.rf-input:focus,.rf-textarea:focus { border-color:var(--accent); }
.rf-textarea { min-height:72px; resize:vertical; }
.rf-error { font-size:16px; color:#ef4444; margin-top:2px; }

/* Product search */
.rf-search-wrap { position:relative; margin-bottom:14px; }
.rf-search-wrap input {
    width:100%; padding:9px 11px 9px 36px; border-radius:var(--rsm,6px);
    border:1px solid var(--border); background:var(--surface2); color:var(--text);
    font-size:19px; outline:none; transition:border-color .15s; box-sizing:border-box;
}
.rf-search-wrap input:focus { border-color:var(--accent); }
.rf-search-ico { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:var(--text-sub); pointer-events:none; }

/* Product rows */
.rf-products { display:flex; flex-direction:column; gap:7px; max-height:460px; overflow-y:auto; padding-right:2px; }
.rf-prod-row {
    display:flex; align-items:center; gap:10px;
    padding:10px 12px; border-radius:var(--rsm,6px);
    border:1px solid var(--border); background:var(--surface2);
    transition:border-color .15s;
}
.rf-prod-row:hover       { border-color:var(--accent); }
.rf-prod-row.is-added    { border-color:var(--green); background:rgba(16,185,129,.04); }
.rf-prod-row.no-stock    { opacity:.55; }
.rf-prod-info            { flex:1; min-width:0; }
.rf-prod-name            { font-size:17px; font-weight:600; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.rf-prod-meta            { display:flex; align-items:center; gap:6px; margin-top:2px; }
.rf-prod-sku             { font-size:14px; color:var(--text-sub); }
.rf-prod-cat             { font-size:14px; color:var(--text-sub); background:var(--surface); padding:1px 5px; border-radius:3px; border:1px solid var(--border); }

/* Stock info */
.rf-stock { display:flex; flex-direction:column; align-items:flex-end; gap:2px; flex-shrink:0; }
.rf-stock-pill {
    font-size:14px; font-weight:700; padding:2px 7px; border-radius:4px;
    background:var(--sp-bg); color:var(--sp-c);
}
.rf-stock-detail { font-size:13px; color:var(--text-sub); text-align:right; }

/* Add button */
.rf-add-btn {
    padding:6px 12px; border-radius:var(--rsm,6px); font-size:16px; font-weight:700;
    border:1.5px solid var(--accent); color:var(--accent); background:transparent;
    cursor:pointer; flex-shrink:0; display:inline-flex; align-items:center; gap:4px;
    transition:all .15s; white-space:nowrap;
}
.rf-add-btn:hover   { background:var(--accent); color:#fff; }
.rf-add-btn.added   { background:var(--green); border-color:var(--green); color:#fff; cursor:default; }
.rf-add-btn.blocked { border-color:var(--border); color:var(--text-sub); cursor:not-allowed; }

/* ── Summary panel */
.rf-summary { position:sticky; top:16px; }

.rf-items-list { display:flex; flex-direction:column; gap:6px; margin-bottom:14px; }
.rf-item-row {
    padding:10px 12px; border-radius:var(--rsm,6px);
    background:var(--surface2); border:1px solid var(--border);
}
.rf-item-top  { display:flex; align-items:flex-start; justify-content:space-between; gap:8px; margin-bottom:8px; }
.rf-item-name { font-size:17px; font-weight:600; color:var(--text); }
.rf-item-sku  { font-size:14px; color:var(--text-sub); }
.rf-item-rm   { background:none; border:none; color:var(--text-sub); cursor:pointer; font-size:22px; line-height:1; padding:0; transition:color .12s; }
.rf-item-rm:hover { color:#ef4444; }
.rf-qty-ctrl  { display:flex; align-items:center; gap:6px; }
.rf-qty-input {
    width:60px; text-align:center; padding:5px; border-radius:5px;
    border:1px solid var(--border); background:var(--surface); color:var(--text);
    font-size:19px; font-weight:700; outline:none;
}
.rf-qty-input:focus { border-color:var(--accent); }
.rf-qty-input.over { border-color:#ef4444; background:rgba(239,68,68,.06); color:#ef4444; }
.rf-qty-label { font-size:14px; color:var(--text-sub); }
.rf-qty-avail { font-size:13px; color:var(--text-sub); margin-top:3px; }
.rf-overstock-warn {
    font-size:14px; font-weight:600; color:#ef4444;
    display:flex; align-items:center; gap:4px; margin-top:4px;
}

/* Totals */
.rf-totals { border-top:1px solid var(--border); padding:12px 0; margin-bottom:12px; display:flex; flex-direction:column; gap:5px; }
.rf-total-row { display:flex; justify-content:space-between; align-items:center; }
.rf-total-row .l { font-size:16px; color:var(--text-sub); }
.rf-total-row .v { font-size:19px; font-weight:700; color:var(--text); }
.rf-total-row.big .v { font-size:26px; font-weight:800; color:var(--accent); }

/* Route preview */
.rf-route-preview {
    display:flex; align-items:center; gap:8px; font-size:16px;
    padding:8px 10px; background:var(--surface2); border-radius:var(--rsm,6px);
    margin-bottom:12px; color:var(--text-sub);
}
.rf-route-preview strong { color:var(--text); font-weight:700; }
.rf-route-preview-line { flex:1; border-top:1.5px dashed var(--border); height:0; }

/* Submit */
.rf-submit {
    width:100%; padding:12px; border-radius:var(--r); border:none; cursor:pointer;
    background:var(--accent); color:#fff; font-size:20px; font-weight:800;
    letter-spacing:.2px; transition:opacity .15s; display:flex; align-items:center;
    justify-content:center; gap:8px;
}
.rf-submit:hover    { opacity:.88; }
.rf-submit:disabled { opacity:.4; cursor:not-allowed; }

.rf-empty-cart { text-align:center; padding:28px 12px; color:var(--text-sub); }
.rf-empty-cart svg { display:block; margin:0 auto 10px; opacity:.3; }
.rf-empty-cart p { font-size:17px; margin:0; }

.rf-flash-ok  { padding:9px 12px; border-radius:var(--rsm,6px); font-size:17px; font-weight:600; background:rgba(16,185,129,.1); border:1px solid var(--green); color:var(--green); margin-top:10px; }
.rf-flash-err { padding:9px 12px; border-radius:var(--rsm,6px); font-size:17px; font-weight:600; background:rgba(239,68,68,.08); border:1px solid #ef4444; color:#ef4444; margin-top:10px; }
.rf-no-wh-notice {
    text-align:center; padding:28px; border:1px dashed var(--border);
    border-radius:var(--rsm,6px); color:var(--text-sub); font-size:17px;
}

/* Livewire loading safety */
[wire\:loading] { display: none !important; }
@keyframes rf-spin { to { transform: rotate(360deg); } }
.rf-spin { animation: rf-spin .8s linear infinite; }

/* Responsive base — applied to all transfer pages */
@media(max-width:600px) {
    /* Cards */
    .tl-card, .rf-card {
        border-radius:var(--rsm, 8px);
    }
    /* Tables inside cards — make them scroll horizontally */
    table {
        display:block;
        overflow-x:auto;
        -webkit-overflow-scrolling:touch;
        white-space:nowrap;
    }
    /* Prevent text overflow on narrow screens */
    .tl-num, .rf-prod-name, .tl-route-node {
        max-width:140px;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
    }
    /* Badges wrap instead of overflow */
    .tl-card-meta, .tl-dates {
        flex-wrap:wrap;
        gap:4px;
    }
}
\n
/* 2A - Transfer List Fixes */
@media(max-width:900px) {
    .tl-pipeline { grid-template-columns: repeat(3, 1fr); }
}
@media(max-width:600px) {
    .tl-pipeline { grid-template-columns: repeat(2, 1fr); gap:0; }
    .tl-pipeline-step { padding:10px 12px; }
    .tl-step-num  { font-size:24px; }
    .tl-step-sub  { display:none; }
    .tl-card-top    { flex-direction:column; padding:0 14px; }
    .tl-card-stats  { border-left:none; border-top:1px solid var(--border); margin:0 0 8px; flex-wrap:wrap; }
    .tl-stat        { padding:8px 14px; flex:1; min-width:80px; }
    .tl-bar         { gap:4px; padding:8px 10px; }
    .tl-chip        { padding:4px 10px; font-size:13px; }
    .tl-search      { width:100%; margin-left:0; margin-top:6px; }
    .tl-search input{ width:100%; }
    .tl-route-dash-line { width:20px; }
    .tl-card-foot   { flex-wrap:wrap; gap:6px; }
    .tl-action      { flex:1; justify-content:center; }
    .tl-foot-time   { width:100%; text-align:center; margin-left:0; }
    .tl-page-header         { flex-direction:column; align-items:flex-start; }
    .tl-page-header-left h1 { font-size:24px; }
    .tl-new-btn             { width:100%; justify-content:center; }
}
\n
/* 2B - Request Form Fixes */
@media(max-width:860px) {
    .rf-layout { grid-template-columns:1fr; }
    .rf-summary { position:static; }
}
@media(max-width:600px) {
    .rf-row2 { grid-template-columns:1fr; }
    .rf-prod-row    { flex-wrap:wrap; gap:8px; }
    .rf-prod-info   { width:100%; }
    .rf-stock       { align-items:flex-start; }
    .rf-add-btn     { width:100%; justify-content:center; }
    .rf-item-top    { flex-wrap:wrap; }
    .rf-qty-ctrl    { width:100%; justify-content:space-between; }
}
\n</style>

<div class="rf-breadcrumb">
  <a href="{{ route('shop.transfers.index') }}">← Transfer Requests</a>
  &nbsp;/&nbsp; New Request
</div>
<div class="rf-page-head">
  <h1>New Stock Request</h1>
  <p>Select a warehouse, choose products and submit your request</p>
</div>

<form wire:submit.prevent="submit">
<div class="rf-layout">

  {{-- ── LEFT: Form + Products ─────────────────── --}}
  <div>

    {{-- Details card --}}
    <div class="rf-card">
      <div class="rf-card-head">
        <div class="rf-card-icon">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div class="rf-card-head-text">
          <h3>Request Details</h3>
          <p>Set the source warehouse and destination shop</p>
        </div>
      </div>
      <div class="rf-card-body">
        <div class="rf-row2">
          <div class="rf-field">
            <label class="rf-label">From Warehouse</label>
            <select wire:model.live="fromWarehouseId" class="rf-select">
              <option value="">Select warehouse…</option>
              @foreach($this->warehouses as $wh)
                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
              @endforeach
            </select>
            @error('fromWarehouseId')<span class="rf-error">{{ $message }}</span>@enderror
          </div>
          <div class="rf-field">
            <label class="rf-label">To Shop</label>
            <select wire:model.live="toShopId" class="rf-select">
              <option value="">Select shop…</option>
              @foreach($this->shops as $sh)
                <option value="{{ $sh->id }}">{{ $sh->name }}</option>
              @endforeach
            </select>
            @error('toShopId')<span class="rf-error">{{ $message }}</span>@enderror
          </div>
        </div>
        <div class="rf-field" style="margin-top:12px">
          <label class="rf-label">Notes <span style="text-transform:none;letter-spacing:0;font-weight:400">(optional)</span></label>
          <textarea wire:model="notes" class="rf-textarea" placeholder="Add special instructions, urgency reason, or context…"></textarea>
        </div>
      </div>
    </div>

    {{-- Product selection card --}}
    <div class="rf-card">
      <div class="rf-card-head">
        <div class="rf-card-icon" style="--icon-bg:rgba(16,185,129,.12);--icon-c:var(--green)">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
        </div>
        <div class="rf-card-head-text">
          <h3>Select Products</h3>
          <p>Search the warehouse catalogue and add to your request</p>
        </div>
      </div>
      <div class="rf-card-body">

        <div class="rf-search-wrap">
          <svg class="rf-search-ico" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, SKU or category…">
        </div>

        @if(!$fromWarehouseId)
          <div class="rf-no-wh-notice">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 8px;display:block;opacity:.4"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Select a warehouse above to browse available stock
          </div>
        @else
          <div class="rf-products">
            @forelse($products as $product)
            @php
              $stock    = $stockLevels[$product->id] ?? ['full_boxes'=>0,'partial_boxes'=>0,'total_boxes'=>0,'total_items'=>0];
              $hasStock = $stock['total_boxes'] > 0;
              $isAdded  = false;
              foreach($items as $itm){ if($itm['product_id'] == $product->id){ $isAdded=true; break; } }
              if($stock['total_boxes'] > 5)     { $spBg='rgba(16,185,129,.1)';  $spC='var(--green)'; }
              elseif($stock['total_boxes'] > 0) { $spBg='rgba(217,119,6,.1)';  $spC='#d97706'; }
              else                              { $spBg='rgba(239,68,68,.08)';  $spC='#ef4444'; }
            @endphp
            <div class="rf-prod-row {{ $isAdded?'is-added':'' }} {{ !$hasStock?'no-stock':'' }}">
              <div class="rf-prod-info">
                <div class="rf-prod-name" title="{{ $product->name }}">{{ $product->name }}</div>
                <div class="rf-prod-meta">
                  @if($product->sku ?? null)
                    <span class="rf-prod-sku">{{ $product->sku }}</span>
                  @endif
                  @if(isset($product->category) && $product->category)
                    <span class="rf-prod-cat">{{ $product->category->name }}</span>
                  @endif
                </div>
              </div>

              <div class="rf-stock">
                <span class="rf-stock-pill" style="--sp-bg:{{ $spBg }};--sp-c:{{ $spC }}">
                  @if($hasStock){{ $stock['total_boxes'] }} {{ Str::plural('box', $stock['total_boxes']) }}@else Out of stock @endif
                </span>
                @if($hasStock)
                  <span class="rf-stock-detail">{{ $stock['full_boxes'] }}F · {{ $stock['partial_boxes'] }}P</span>
                  <span class="rf-stock-detail">{{ $product->items_per_box ?? 0 }}/box</span>
                @endif
              </div>

              @if($isAdded)
                <button class="rf-add-btn added" type="button" disabled>✓ Added</button>
              @elseif(!$hasStock)
                <button class="rf-add-btn blocked" type="button" disabled>No Stock</button>
              @else
                <button class="rf-add-btn" type="button"
                        wire:click="addProductToCart({{ $product->id }})"
                        wire:loading.attr="disabled"
                        wire:target="addProductToCart({{ $product->id }})">
                  <span wire:loading.remove wire:target="addProductToCart({{ $product->id }})">+ Add</span>
                  <span wire:loading wire:target="addProductToCart({{ $product->id }})" style="display:none">…</span>
                </button>
              @endif
            </div>
            @empty
            <div style="text-align:center;padding:28px;font-size:17px;color:var(--text-sub)">No products match your search.</div>
            @endforelse
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- ── RIGHT: Order Summary ───────────────────── --}}
  <div class="rf-summary">
    <div class="rf-card">
      <div class="rf-card-head">
        <div class="rf-card-icon" style="--icon-bg:rgba(139,92,246,.12);--icon-c:var(--violet)">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        </div>
        <div class="rf-card-head-text">
          <h3>Request Summary</h3>
          <p>{{ count($items) }} product(s) selected</p>
        </div>
      </div>
      <div class="rf-card-body">

        @if(empty($items))
          <div class="rf-empty-cart">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            <p>Add products from the list to start building your request</p>
          </div>
        @else
          <div class="rf-items-list">
            @foreach($items as $index => $item)
            @php
              $p = $products->firstWhere('id', $item['product_id']);
              if(!$p) $p = \App\Models\Product::find($item['product_id']);
              $boxes   = (int)($item['boxes_requested'] ?? 0);
              $avail   = isset($stockLevels[$item['product_id']]) ? $stockLevels[$item['product_id']]['total_boxes'] : 0;
              $overMax = $boxes > $avail && $boxes > 0;
            @endphp
            @if($p)
            <div wire:key="summary-{{ $item['product_id'] }}" class="rf-item-row">
              <div class="rf-item-top">
                <div>
                  <div class="rf-item-name">{{ Str::limit($p->name, 30) }}</div>
                  <div class="rf-item-sku">{{ $p->items_per_box ?? 0 }} items/box</div>
                </div>
                <button class="rf-item-rm" type="button" wire:click="removeItem({{ $index }})" title="Remove">×</button>
              </div>
              <div class="rf-qty-ctrl">
                <input class="rf-qty-input {{ $overMax ? 'over' : '' }}" type="number" min="0"
                       wire:model.live="items.{{ $index }}.boxes_requested">
                <span class="rf-qty-label">boxes</span>
              </div>
              <div class="rf-qty-avail">Avail: {{ $avail }} boxes</div>
              @if($overMax)
                <div class="rf-overstock-warn">
                  <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2zm0 3.5L20.5 19h-17L12 5.5zM11 10v5h2v-5h-2zm0 6v2h2v-2h-2z"/></svg>
                  Exceeds stock by {{ $boxes - $avail }}
                </div>
              @endif
              @error("items.{$index}.boxes_requested")<div class="rf-overstock-warn">{{ $message }}</div>@enderror
            </div>
            @endif
            @endforeach
          </div>

          @php
            $totalBoxes = 0; $estItems = 0;
            foreach($items as $i){
              $bx = (int)($i['boxes_requested'] ?? 0);
              $totalBoxes += $bx;
              $pr = $products->firstWhere('id',$i['product_id']) ?? \App\Models\Product::find($i['product_id']);
              if($pr) $estItems += $bx * ($pr->items_per_box ?? 0);
            }
          @endphp
          <div class="rf-totals">
            <div class="rf-total-row">
              <span class="l">Products</span>
              <span class="v">{{ count($items) }}</span>
            </div>
            <div class="rf-total-row">
              <span class="l">Boxes requested</span>
              <span class="v">{{ $totalBoxes }}</span>
            </div>
            <div class="rf-total-row big">
              <span class="l" style="font-size:17px;color:var(--text);font-weight:600">Est. items</span>
              <span class="v">~{{ number_format($estItems) }}</span>
            </div>
          </div>
        @endif

        {{-- Route preview --}}
        @php
          $selWh = $fromWarehouseId ? $this->warehouses->firstWhere('id', $fromWarehouseId) : null;
          $selSh = $toShopId ? $this->shops->firstWhere('id', $toShopId) : null;
        @endphp
        @if($selWh && $selSh)
        <div class="rf-route-preview">
          <strong>{{ $selWh->name }}</strong>
          <div class="rf-route-preview-line"></div>
          <svg width="11" height="11" viewBox="0 0 24 24" fill="var(--accent)"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          <strong>{{ $selSh->name }}</strong>
        </div>
        @endif

        <button class="rf-submit" type="submit"
                wire:loading.attr="disabled"
                @if(empty($items) || !$fromWarehouseId || !$toShopId) disabled @endif>
          <span wire:loading.remove wire:target="submit" style="display:inline-flex;align-items:center;gap:8px">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Submit Request
          </span>
          <span wire:loading wire:target="submit" style="display:none;align-items:center;gap:8px">
            <svg class="rf-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2a10 10 0 0110 10"/></svg>
            Submitting…
          </span>
        </button>

        @if(session('success'))
          <div class="rf-flash-ok">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
          <div class="rf-flash-err">{{ session('error') }}</div>
        @endif
      </div>
    </div>
  </div>

</div>
</form>
</div>