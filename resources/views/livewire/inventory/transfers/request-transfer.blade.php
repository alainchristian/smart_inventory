<div>
<style>
/* ── Layout ─────────────────────────────────────────────── */
.rf-layout {
    display: grid;
    grid-template-columns: 1fr 290px;
    gap: 16px;
    align-items: start;
}

/* ── Card ───────────────────────────────────────────────── */
.rf-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 14px;
}
.rf-card-head {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 10px 14px;
    border-bottom: 1px solid var(--border);
    background: var(--surface2);
}
.rf-card-icon {
    width: 26px; height: 26px; border-radius: 7px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: var(--icon-bg, rgba(99,102,241,.12));
    color: var(--icon-c, var(--accent));
}
.rf-card-head h3 { font-size: 12px; font-weight: 700; color: var(--text); margin: 0; }
.rf-card-body    { padding: 14px; }

/* ── Form fields ────────────────────────────────────────── */
.rf-row2  { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.rf-field { display: flex; flex-direction: column; gap: 4px; }
.rf-label { font-size: 10px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; color: var(--text-dim); }
.rf-select, .rf-input, .rf-textarea {
    padding: 8px 10px; border-radius: 8px;
    border: 1px solid var(--border); background: #fff;
    color: var(--text); font-size: 13px; outline: none;
    transition: border-color .15s; font-family: inherit;
    width: 100%; box-sizing: border-box;
}
.rf-select:focus, .rf-input:focus, .rf-textarea:focus { border-color: var(--accent); }
.rf-textarea { min-height: 64px; resize: vertical; }
.rf-error    { font-size: 11px; color: var(--red); margin-top: 2px; }

/* ── Search ─────────────────────────────────────────────── */
.rf-search-wrap { position: relative; margin-bottom: 8px; }
.rf-search-wrap input {
    width: 100%; padding: 9px 10px 9px 32px; border-radius: 8px;
    border: 1px solid var(--border); background: #fff;
    color: var(--text); font-size: 13px; outline: none;
    transition: border-color .15s; box-sizing: border-box;
}
.rf-search-wrap input:focus { border-color: var(--accent); }
.rf-search-ico { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-dim); pointer-events: none; }

/* ── Product list ───────────────────────────────────────── */
.rf-products { max-height: 440px; overflow-y: auto; }
.rf-prod-row {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 2px;
    border-bottom: 1px solid var(--border);
    transition: background .12s;
    min-height: 48px;
}
.rf-prod-row:last-child { border-bottom: none; }
.rf-prod-row:hover      { background: var(--surface2); border-radius: 6px; margin: 0 -4px; padding: 10px 6px; }
.rf-prod-row.is-added   { background: rgba(16,185,129,.04); border-radius: 6px; margin: 0 -4px; padding: 10px 6px; border-bottom-color: transparent; }
.rf-prod-row.is-added + .rf-prod-row { border-top: 1px solid var(--border); }
.rf-prod-row.no-stock   { opacity: .4; }

.rf-prod-info { flex: 1; min-width: 0; }
.rf-prod-name { font-size: 13px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rf-prod-meta { display: flex; align-items: center; gap: 5px; margin-top: 2px; flex-wrap: wrap; }
.rf-prod-sku  { font-size: 10px; color: var(--text-dim); font-family: var(--mono); }
.rf-prod-cat  { font-size: 10px; color: var(--text-dim); background: var(--surface2); padding: 1px 5px; border-radius: 4px; }

/* ── Stock pill ─────────────────────────────────────────── */
.rf-stock      { display: flex; flex-direction: column; align-items: flex-end; gap: 1px; flex-shrink: 0; }
.rf-stock-pill { font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 5px; white-space: nowrap; }
.rf-stock-sub  { font-size: 10px; color: var(--text-dim); }

/* ── Add button ─────────────────────────────────────────── */
.rf-add-btn {
    min-width: 32px; min-height: 32px;
    padding: 5px 11px; border-radius: 7px;
    font-size: 11px; font-weight: 700;
    border: 1.5px solid var(--accent); color: var(--accent); background: transparent;
    cursor: pointer; flex-shrink: 0;
    display: inline-flex; align-items: center; justify-content: center; gap: 4px;
    transition: all .15s;
}
.rf-add-btn:hover   { background: var(--accent); color: #fff; }
.rf-add-btn.added   { border-color: var(--green); background: rgba(16,185,129,.08); color: var(--green); cursor: default; }
.rf-add-btn.blocked { border-color: var(--border); color: var(--text-dim); cursor: not-allowed; }
/* Hide text label on narrow screens — show only icon */
.rf-add-text { display: inline; }

/* ── Summary items ──────────────────────────────────────── */
.rf-summary  { position: sticky; top: 16px; }
.rf-items-list { display: flex; flex-direction: column; gap: 0; margin-bottom: 12px; }
.rf-item-row {
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
}
.rf-item-row:last-child { border-bottom: none; }
.rf-item-top  { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-bottom: 6px; }
.rf-item-name { font-size: 12px; font-weight: 600; color: var(--text); }
.rf-item-sub  { font-size: 10px; color: var(--text-dim); margin-top: 1px; }
.rf-item-rm   { background: none; border: none; color: var(--text-dim); cursor: pointer; font-size: 17px; line-height: 1; padding: 2px 4px; transition: color .12s; flex-shrink: 0; }
.rf-item-rm:hover { color: var(--red); }
.rf-qty-row   { display: flex; align-items: center; justify-content: space-between; }
.rf-qty-ctrl  { display: flex; align-items: center; gap: 6px; }
.rf-qty-input {
    width: 54px; text-align: center; padding: 6px; border-radius: 6px;
    border: 1px solid var(--border); background: #fff;
    color: var(--text); font-size: 13px; font-weight: 700;
    font-family: var(--mono); outline: none;
}
.rf-qty-input:focus { border-color: var(--accent); }
.rf-qty-input.over  { border-color: var(--red); color: var(--red); }
.rf-qty-label { font-size: 11px; color: var(--text-dim); }
.rf-qty-avail { font-size: 10px; color: var(--text-dim); }
.rf-overstock-warn { font-size: 11px; font-weight: 600; color: var(--red); display: flex; align-items: center; gap: 4px; margin-top: 4px; }

/* ── Totals ─────────────────────────────────────────────── */
.rf-totals { border-top: 1px solid var(--border); padding: 10px 0; margin-bottom: 10px; display: flex; flex-direction: column; gap: 4px; }
.rf-total-row   { display: flex; justify-content: space-between; align-items: center; }
.rf-total-row .l { font-size: 12px; color: var(--text-dim); }
.rf-total-row .v { font-size: 12px; font-weight: 700; color: var(--text); }
.rf-total-row.big .v { font-size: 16px; font-weight: 800; color: var(--accent); }

/* ── Route ──────────────────────────────────────────────── */
.rf-route { display: flex; align-items: center; gap: 7px; padding: 8px 0; margin-bottom: 10px; }
.rf-route-name { font-size: 12px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90px; }
.rf-route-line { flex: 1; border-top: 1.5px dashed var(--border); min-width: 8px; }

/* ── Submit ─────────────────────────────────────────────── */
.rf-submit {
    width: 100%; padding: 11px 14px; border-radius: 10px; border: none; cursor: pointer;
    background: var(--accent); color: #fff; font-size: 13px; font-weight: 700;
    transition: opacity .15s; display: flex; align-items: center; justify-content: center; gap: 7px;
    min-height: 44px;
}
.rf-submit:hover    { opacity: .88; }
.rf-submit:disabled { opacity: .4; cursor: not-allowed; }

/* ── Notices ────────────────────────────────────────────── */
.rf-no-wh { text-align: center; padding: 28px 16px; color: var(--text-dim); font-size: 12px; }
.rf-empty-cart { text-align: center; padding: 22px 12px; color: var(--text-dim); }
.rf-empty-cart p { font-size: 12px; margin: 8px 0 0; }
.rf-flash-ok  { padding: 8px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; background: var(--green-dim); color: var(--green); margin-top: 10px; }
.rf-flash-err { padding: 8px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; background: var(--red-dim);   color: var(--red);   margin-top: 10px; }

/* ── Loading ────────────────────────────────────────────── */
[wire\:loading] { display: none !important; }
@keyframes rf-spin { to { transform: rotate(360deg); } }
.rf-spin { animation: rf-spin .8s linear infinite; }

/* ── Responsive ─────────────────────────────────────────── */
@media (max-width: 860px) {
    .rf-layout  { grid-template-columns: 1fr; }
    .rf-summary { position: static; order: 3; }
}
@media (max-width: 640px) {
    .rf-row2     { grid-template-columns: 1fr; }
    .rf-products { max-height: 360px; }
}
/* On narrow phones: stack name above, stock+button below */
@media (max-width: 520px) {
    .rf-prod-row  { flex-wrap: wrap; row-gap: 6px; }
    .rf-prod-info { width: 100%; }
    .rf-stock     { flex-direction: row; align-items: center; gap: 8px; }
    .rf-add-btn   { margin-left: auto; }
    .rf-add-text  { display: none; }   /* icon only */
}
</style>

<form wire:submit.prevent="submit">
<div class="rf-layout">

  {{-- ── LEFT ───────────────────────────────────── --}}
  <div>

    {{-- Details card --}}
    <div class="rf-card">
      <div class="rf-card-head">
        <div class="rf-card-icon">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
        </div>
        <h3>Transfer Details</h3>
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
        <div class="rf-field" style="margin-top:10px;">
          <label class="rf-label">Notes <span style="text-transform:none;letter-spacing:0;font-weight:400;">(optional)</span></label>
          <textarea wire:model="notes" class="rf-textarea" placeholder="Special instructions…"></textarea>
        </div>
      </div>
    </div>

    {{-- Products card --}}
    <div class="rf-card">
      <div class="rf-card-head" style="--icon-bg:rgba(16,185,129,.12);--icon-c:var(--green);">
        <div class="rf-card-icon">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
          </svg>
        </div>
        <h3>Products</h3>
      </div>
      <div class="rf-card-body">

        <div class="rf-search-wrap">
          <svg class="rf-search-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search name, SKU or category…">
        </div>

        @if(!$fromWarehouseId)
          <div class="rf-no-wh">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                 style="display:block;margin:0 auto 8px;opacity:.3;">
              <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Select a warehouse to see stock
          </div>
        @else
          <div class="rf-products">
            @forelse($products as $product)
              @php
                $stock    = $stockLevels[$product->id] ?? ['full_boxes'=>0,'partial_boxes'=>0,'total_boxes'=>0];
                $hasStock = $stock['total_boxes'] > 0;
                $isAdded  = false;
                foreach($items as $itm){ if($itm['product_id'] == $product->id){ $isAdded = true; break; } }
                if($stock['total_boxes'] > 5)     { $spBg='var(--green-dim)'; $spC='var(--green)'; }
                elseif($stock['total_boxes'] > 0) { $spBg='var(--amber-dim)'; $spC='var(--amber)'; }
                else                              { $spBg='var(--red-dim)';   $spC='var(--red)'; }
              @endphp
              <div class="rf-prod-row {{ $isAdded ? 'is-added' : '' }} {{ !$hasStock ? 'no-stock' : '' }}">

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
                  <span class="rf-stock-pill" style="background:{{ $spBg }};color:{{ $spC }};">
                    {{ $hasStock ? $stock['total_boxes'].' '.Str::plural('box',$stock['total_boxes']) : 'Out of stock' }}
                  </span>
                  @if($hasStock)
                    <span class="rf-stock-sub">{{ $stock['full_boxes'] }}F · {{ $stock['partial_boxes'] }}P</span>
                  @endif
                </div>

                @if($isAdded)
                  <button class="rf-add-btn added" type="button" disabled>
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                    <span class="rf-add-text">Added</span>
                  </button>
                @elseif(!$hasStock)
                  <button class="rf-add-btn blocked" type="button" disabled>—</button>
                @else
                  <button class="rf-add-btn" type="button"
                          wire:click="addProductToCart({{ $product->id }})"
                          wire:loading.attr="disabled"
                          wire:target="addProductToCart({{ $product->id }})">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span wire:loading.remove wire:target="addProductToCart({{ $product->id }})" class="rf-add-text">Add</span>
                    <span wire:loading        wire:target="addProductToCart({{ $product->id }})" style="display:none;">…</span>
                  </button>
                @endif

              </div>
            @empty
              <div style="text-align:center;padding:24px;font-size:12px;color:var(--text-dim);">
                No products match your search.
              </div>
            @endforelse
          </div>
        @endif

      </div>
    </div>

  </div>

  {{-- ── RIGHT: Summary ──────────────────────── --}}
  <div class="rf-summary">
    <div class="rf-card">
      <div class="rf-card-head" style="--icon-bg:rgba(139,92,246,.12);--icon-c:#8b5cf6;">
        <div class="rf-card-icon">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 01-8 0"/>
          </svg>
        </div>
        <h3>Summary&ensp;<span style="font-weight:400;color:var(--text-dim);font-size:11px;">{{ count($items) > 0 ? count($items).' item'.(count($items)===1?'':'s') : '' }}</span></h3>
      </div>
      <div class="rf-card-body">

        @if(empty($items))
          <div class="rf-empty-cart">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                 style="display:block;margin:0 auto;opacity:.2;">
              <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
              <line x1="3" y1="6" x2="21" y2="6"/>
              <path d="M16 10a4 4 0 01-8 0"/>
            </svg>
            <p>Add products from the list</p>
          </div>
        @else

          <div class="rf-items-list">
            @foreach($items as $index => $item)
              @php
                $p     = $products->firstWhere('id', $item['product_id']) ?? \App\Models\Product::find($item['product_id']);
                $boxes = (int)($item['boxes_requested'] ?? 0);
                $avail = $stockLevels[$item['product_id']]['total_boxes'] ?? 0;
                $over  = $boxes > $avail && $boxes > 0;
              @endphp
              @if($p)
              <div wire:key="si-{{ $item['product_id'] }}" class="rf-item-row">
                <div class="rf-item-top">
                  <div style="min-width:0;">
                    <div class="rf-item-name">{{ Str::limit($p->name, 28) }}</div>
                    <div class="rf-item-sub">{{ $p->items_per_box ?? 0 }} items/box</div>
                  </div>
                  <button class="rf-item-rm" type="button" wire:click="removeItem({{ $index }})" title="Remove">×</button>
                </div>
                <div class="rf-qty-row">
                  <div class="rf-qty-ctrl">
                    <input class="rf-qty-input {{ $over ? 'over' : '' }}" type="number" min="0"
                           wire:model.live="items.{{ $index }}.boxes_requested">
                    <span class="rf-qty-label">boxes</span>
                  </div>
                  <span class="rf-qty-avail">{{ $avail }} avail.</span>
                </div>
                @if($over)
                  <div class="rf-overstock-warn">
                    <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2zm1 13h-2v-5h2v5zm0 3h-2v-2h2v2z"/></svg>
                    Exceeds stock by {{ $boxes - $avail }}
                  </div>
                @endif
                @error("items.{$index}.boxes_requested")
                  <div class="rf-overstock-warn">{{ $message }}</div>
                @enderror
              </div>
              @endif
            @endforeach
          </div>

          @php
            $totalBoxes = 0; $estItems = 0;
            foreach($items as $i){
              $bx = (int)($i['boxes_requested'] ?? 0);
              $totalBoxes += $bx;
              $pr = $products->firstWhere('id', $i['product_id']) ?? \App\Models\Product::find($i['product_id']);
              if($pr) $estItems += $bx * ($pr->items_per_box ?? 0);
            }
          @endphp
          <div class="rf-totals">
            <div class="rf-total-row">
              <span class="l">Products</span><span class="v">{{ count($items) }}</span>
            </div>
            <div class="rf-total-row">
              <span class="l">Boxes requested</span><span class="v">{{ $totalBoxes }}</span>
            </div>
            <div class="rf-total-row big">
              <span class="l" style="font-size:12px;color:var(--text);font-weight:600;">Est. items</span>
              <span class="v">~{{ number_format($estItems) }}</span>
            </div>
          </div>
        @endif

        {{-- Route preview --}}
        @php
          $selWh = $fromWarehouseId ? $this->warehouses->firstWhere('id', $fromWarehouseId) : null;
          $selSh = $toShopId        ? $this->shops->firstWhere('id', $toShopId)             : null;
        @endphp
        @if($selWh && $selSh)
          <div class="rf-route">
            <span class="rf-route-name">{{ $selWh->name }}</span>
            <div class="rf-route-line"></div>
            <svg width="10" height="10" viewBox="0 0 24 24" fill="var(--accent)" style="flex-shrink:0;">
              <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
            <span class="rf-route-name">{{ $selSh->name }}</span>
          </div>
        @endif

        <button class="rf-submit" type="submit"
                wire:loading.attr="disabled"
                @if(empty($items) || !$fromWarehouseId || !$toShopId) disabled @endif>
          <span wire:loading.remove wire:target="submit" style="display:inline-flex;align-items:center;gap:7px;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
            </svg>
            Submit Request
          </span>
          <span wire:loading wire:target="submit" style="display:none;align-items:center;gap:7px;">
            <svg class="rf-spin" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M12 2a10 10 0 0110 10"/>
            </svg>
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
