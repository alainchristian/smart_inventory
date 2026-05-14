<div>

@if($sessionBlocked)
    <x-session-gate-blocked
        :reason="$sessionBlockReason"
        :session-date="$blockedSessionDate"
        :session-id="$blockedSessionId"
    />
@else

{{-- ═══════════ SHARED CSS ═══════════ --}}
<style>
/* Section label — matches POS co-section-label */
.whs-section-label {
    font-size:10px;font-weight:800;color:var(--text-dim);
    text-transform:uppercase;letter-spacing:.7px;margin-bottom:10px;
}

/* Card wrapper */
.whs-card {
    background:var(--surface);border:1px solid var(--border);
    border-radius:16px;overflow:hidden;
}

/* Input — matches POS co-input */
.whs-input {
    width:100%;box-sizing:border-box;padding:9px 11px;
    border:1.5px solid var(--border);border-radius:9px;
    font-size:13px;background:var(--surface);color:var(--text);
    outline:none;font-family:var(--font);transition:border-color .15s;
}
.whs-input:focus { border-color:var(--accent) }
.whs-input.mono { font-family:var(--mono) }

/* ── Payment rows (from POS co-pay-*) ───────────────────── */
.whs-pay-list {
    border:1px solid var(--border);border-radius:13px;overflow:hidden;margin-bottom:12px;
}
.whs-pay-row {
    display:flex;align-items:center;gap:11px;padding:9px 13px;
    background:var(--surface);border-bottom:1px solid var(--border);
    transition:background .12s;
}
.whs-pay-row:last-child { border-bottom:none }
.whs-pay-row.is-active { background:var(--surface2) }
.whs-pay-icon {
    width:30px;height:30px;border-radius:8px;display:grid;place-items:center;
    font-size:14px;flex-shrink:0;
}
.whs-pay-meta { flex:1;min-width:0 }
.whs-pay-label {
    font-size:10px;font-weight:700;color:var(--text-dim);
    text-transform:uppercase;letter-spacing:.5px;line-height:1;
}
.whs-pay-amount-wrap { position:relative;width:130px;flex-shrink:0 }
.whs-pay-amount {
    width:100%;box-sizing:border-box;padding:7px 36px 7px 10px;
    border:1.5px solid var(--border);border-radius:8px;
    background:var(--surface);color:var(--text);font-size:14px;
    font-family:var(--mono);outline:none;transition:border-color .15s;text-align:right;
}
.whs-pay-amount:focus { border-color:var(--accent);background:var(--surface2) }
.whs-pay-amount-unit {
    position:absolute;right:9px;top:50%;transform:translateY(-50%);
    font-size:10px;font-weight:700;color:var(--text-dim);pointer-events:none;
}
/* Cash row — auto-computed */
.whs-cash-row {
    display:flex;align-items:center;gap:11px;padding:9px 13px;
    background:var(--surface2);border:1px solid var(--border);
    border-radius:13px;margin-bottom:12px;
}
.whs-cash-display {
    width:130px;flex-shrink:0;padding:7px 10px;
    border:1.5px solid var(--green);border-radius:8px;
    background:rgba(16,185,129,.06);color:var(--green);
    font-size:14px;font-family:var(--mono);font-weight:700;
    text-align:right;display:flex;align-items:center;justify-content:space-between;gap:4px;
}
.whs-cash-badge {
    font-size:8px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;
    background:var(--green);color:#fff;padding:2px 5px;border-radius:4px;flex-shrink:0;
}

/* Balance strip */
.whs-bal-strip {
    border:1px solid var(--border);border-radius:11px;
    overflow:hidden;margin-bottom:12px;padding:11px 13px;background:var(--surface2);
}
.whs-bal-strip-nums { display:flex;align-items:baseline;justify-content:space-between;margin-bottom:8px }
.whs-bal-total { font-size:24px;font-weight:800;font-family:var(--mono);color:var(--text);line-height:1 }
.whs-bal-unit { font-size:11px;font-weight:600;color:var(--text-dim);margin-left:3px }
.whs-bal-status { font-size:12px;font-weight:700 }
.whs-bal-bar-wrap { height:5px;border-radius:99px;background:var(--border);overflow:hidden }
.whs-bal-bar { height:100%;border-radius:99px;transition:width .2s,background .2s }

/* Order summary */
.whs-order-card {
    background:var(--surface2);border:1px solid var(--border);
    border-radius:12px;padding:14px 16px;margin-bottom:16px;
}
.whs-order-row {
    display:flex;justify-content:space-between;align-items:baseline;
    gap:8px;padding:4px 0;border-bottom:1px solid var(--border);
}
.whs-order-row:last-of-type { border-bottom:none }
.whs-order-name { font-size:12px;color:var(--text-sub);min-width:0;flex:1 }
.whs-order-amt  { font-size:12px;font-weight:700;font-family:var(--mono);color:var(--text);flex-shrink:0 }
.whs-order-total {
    display:flex;justify-content:space-between;align-items:center;
    padding-top:10px;border-top:2px solid var(--border);margin-top:6px;
}
.whs-order-total-label { font-size:13px;font-weight:700;color:var(--text) }
.whs-order-total-amt   { font-size:26px;font-weight:800;font-family:var(--mono);color:var(--accent);line-height:1 }
.whs-order-total-rwf   { font-size:12px;font-weight:600;color:var(--text-dim);margin-left:4px }

/* Complete button — matches POS co-complete-btn */
.whs-complete-btn {
    width:100%;height:50px;
    background:var(--green);color:#fff;border:none;border-radius:13px;
    font-size:16px;font-weight:800;cursor:pointer;font-family:var(--font);
    display:flex;align-items:center;justify-content:center;gap:8px;
    box-shadow:0 5px 18px rgba(34,197,94,.28);transition:opacity .15s;
}
.whs-complete-btn:hover:not(:disabled) { opacity:.92 }
.whs-complete-btn:disabled { opacity:.5;cursor:not-allowed;box-shadow:none }

/* Cart item card */
.whs-cart-item {
    background:var(--surface2);border:1.5px solid var(--border);
    border-radius:12px;padding:11px 12px;margin-bottom:8px;
}

/* Fulfillment toggle */
.whs-fulfill-btn {
    flex:1;padding:12px 10px;border-radius:11px;text-align:center;
    border:2px solid var(--border);background:var(--surface);
    cursor:pointer;transition:all .12s;
}
.whs-fulfill-btn.active { border-color:var(--accent);background:color-mix(in srgb,var(--accent) 8%,var(--surface)) }
.whs-fulfill-btn-title { font-size:13px;font-weight:700;color:var(--text-sub) }
.whs-fulfill-btn.active .whs-fulfill-btn-title { color:var(--accent) }
.whs-fulfill-btn-sub { font-size:11px;color:var(--text-dim);margin-top:3px }

/* Add-to-cart modal — matches POS sm-* */
.whs-sm-overlay {
    position:fixed;inset:0;z-index:600;display:flex;align-items:center;
    justify-content:center;background:rgba(10,14,35,.6);backdrop-filter:blur(6px);padding:16px;
}
.whs-sm-card {
    background:var(--surface);border:1px solid var(--border);border-radius:18px;
    width:420px;max-width:100%;display:flex;flex-direction:column;
    box-shadow:0 24px 64px rgba(0,0,0,.32);overflow:hidden;
}
.whs-sm-head {
    display:flex;align-items:center;justify-content:space-between;
    padding:18px 20px 14px;border-bottom:1px solid var(--border);flex-shrink:0;
}
.whs-sm-title   { font-size:16px;font-weight:800;color:var(--text);line-height:1.2 }
.whs-sm-subtitle{ font-size:12px;color:var(--text-sub);margin-top:3px;
                  white-space:nowrap;overflow:hidden;text-overflow:ellipsis }
.whs-sm-close {
    width:30px;height:30px;border-radius:8px;background:var(--surface2);
    border:1px solid var(--border);cursor:pointer;display:grid;place-items:center;
    color:var(--text-dim);flex-shrink:0;transition:all .15s;
}
.whs-sm-close:hover { background:var(--border);color:var(--text) }
.whs-sm-body    { padding:18px 20px }
.whs-sm-info {
    display:flex;align-items:center;justify-content:space-between;
    background:var(--surface2);border:1px solid var(--border);border-radius:11px;
    padding:11px 14px;margin-bottom:16px;gap:12px;
}
.whs-sm-stepper {
    display:flex;align-items:center;border:2px solid var(--accent);
    border-radius:10px;overflow:hidden;background:var(--surface);
}
.whs-sm-step-btn {
    width:40px;height:48px;background:transparent;border:none;cursor:pointer;
    font-size:20px;color:var(--accent);display:grid;place-items:center;transition:background .12s;
}
.whs-sm-step-btn:hover { background:var(--surface2) }
.whs-sm-qty-input {
    flex:1;border:none;padding:0;text-align:center;font-size:22px;font-weight:800;
    font-family:var(--mono);color:var(--text);background:transparent;outline:none;
    min-width:0;height:48px;width:80px;
}
.whs-sm-total {
    display:flex;align-items:center;justify-content:space-between;
    border:1.5px solid var(--border);border-radius:11px;
    padding:13px 16px;margin:16px 0 4px;background:var(--surface2);
}
.whs-sm-footer {
    display:grid;grid-template-columns:auto 1fr;gap:9px;
    padding:14px 20px;border-top:1px solid var(--border);background:var(--surface);
}
.whs-sm-cancel {
    padding:0 22px;height:46px;background:transparent;color:var(--text-sub);
    border:1.5px solid var(--border);border-radius:11px;font-size:14px;
    font-weight:700;cursor:pointer;font-family:var(--font);transition:all .15s;white-space:nowrap;
}
.whs-sm-cancel:hover { border-color:var(--accent);color:var(--accent) }
.whs-sm-confirm {
    height:46px;background:var(--accent);color:#fff;border:none;border-radius:11px;
    font-size:14px;font-weight:800;cursor:pointer;font-family:var(--font);
    display:flex;align-items:center;justify-content:center;gap:7px;
    box-shadow:0 4px 14px rgba(59,111,212,.28);transition:opacity .15s;
}
.whs-sm-confirm:hover { opacity:.9 }

/* Tabs */
.whs-tabs {
    display:flex;gap:4px;background:var(--surface2);border:1px solid var(--border);
    border-radius:12px;padding:4px;margin-bottom:22px;
}
.whs-tab-btn {
    flex:1;padding:8px 16px;border:none;border-radius:9px;font-size:13px;font-weight:700;
    cursor:pointer;font-family:var(--font);background:transparent;color:var(--text-dim);transition:all .15s;
}
.whs-tab-btn.active {
    background:var(--surface-raised);color:var(--text);box-shadow:0 1px 4px rgba(0,0,0,.1);
}

/* History */
.whs-hist-table { width:100%;border-collapse:collapse }
.whs-hist-th {
    padding:10px 14px;text-align:left;font-size:10px;font-weight:800;
    color:var(--text-dim);text-transform:uppercase;letter-spacing:.05em;
    background:var(--surface);border-bottom:1px solid var(--border);white-space:nowrap;
}
.whs-hist-td {
    padding:11px 14px;font-size:13px;color:var(--text-sub);
    border-bottom:1px solid var(--border);vertical-align:middle;
}
.whs-hist-row:last-child .whs-hist-td { border-bottom:none }
.whs-hist-row:hover .whs-hist-td { background:var(--surface2) }

/* Responsive */
@media (max-width: 860px) {
    .whs-main-grid     { grid-template-columns:1fr !important; }
    .whs-checkout-grid { grid-template-columns:1fr !important; }
}
</style>

{{-- Tab switcher — only show when not mid-checkout/done --}}
@if($step === 'cart' || $tab === 'history')
<div class="whs-tabs">
    <button class="whs-tab-btn {{ $tab === 'sale' ? 'active' : '' }}" wire:click="setTab('sale')">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
             style="display:inline;vertical-align:-.1em;margin-right:5px">
            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        New Sale
    </button>
    <button class="whs-tab-btn {{ $tab === 'history' ? 'active' : '' }}" wire:click="setTab('history')">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
             style="display:inline;vertical-align:-.1em;margin-right:5px">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Sale History
    </button>
</div>
@endif

{{-- Flash --}}
@if(session('error'))
<div style="background:color-mix(in srgb,var(--red) 10%,var(--surface));border:1px solid color-mix(in srgb,var(--red) 25%,var(--border));
            border-radius:10px;padding:10px 16px;margin-bottom:16px;color:var(--red);font-size:13px;font-weight:600;
            display:flex;align-items:center;gap:8px">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    {{ session('error') }}
</div>
@endif

@if($tab === 'sale')

{{-- ═══════════════════════════════════════════════════════════
     STEP: CART
═══════════════════════════════════════════════════════════ --}}
@if($step === 'cart')

<div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:flex-start" class="whs-main-grid">

  {{-- LEFT: Warehouse stock table --}}
  <div class="whs-card">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <div>
        <div style="font-size:14px;font-weight:800;color:var(--text)">Warehouse Stock</div>
        <div style="font-size:11px;color:var(--text-sub);margin-top:2px">{{ $warehouseName }}</div>
      </div>
      <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono)">
        {{ $warehouseStock->count() }} products available
      </div>
    </div>

    @if($warehouseStock->isEmpty())
      <div style="padding:48px 24px;text-align:center;color:var(--text-dim);font-size:13px">
        No stock available at {{ $warehouseName }}
      </div>
    @else
      <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
          <thead>
            <tr style="background:var(--surface2)">
              <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:800;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Product</th>
              <th style="padding:10px 12px;text-align:center;font-size:10px;font-weight:800;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Boxes</th>
              <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:800;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Box Price</th>
              <th style="padding:10px 12px;text-align:center;font-size:10px;font-weight:800;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Add</th>
            </tr>
          </thead>
          <tbody>
            @foreach($warehouseStock as $product)
              @php
                $boxPrice  = $product->box_selling_price ?? ($product->selling_price * $product->items_per_box);
                $inCart    = collect($cart)->firstWhere('product_id', $product->id);
                $cartBoxes = $inCart ? $inCart['boxes'] : 0;
                $available = $product->box_count - $cartBoxes;
              @endphp
              <tr style="border-top:1px solid var(--border);transition:background .1s"
                  onmouseover="this.style.background='var(--surface2)'"
                  onmouseout="this.style.background=''">
                <td style="padding:12px 16px">
                  <div style="font-size:13px;font-weight:700;color:var(--text)">{{ $product->name }}</div>
                  <div style="font-size:10px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">
                    {{ $product->sku }}
                    @if($product->category_name) · {{ $product->category_name }}@endif
                    · {{ $product->items_per_box }} items/box
                  </div>
                </td>
                <td style="padding:12px;text-align:center">
                  <span style="font-family:var(--mono);font-size:15px;font-weight:800;
                               color:{{ $available > 0 ? 'var(--green)' : 'var(--text-dim)' }}">
                    {{ $available }}
                  </span>
                  @if($cartBoxes > 0)
                    <div style="font-size:10px;color:var(--text-dim)">{{ $cartBoxes }} in cart</div>
                  @endif
                </td>
                <td style="padding:12px;text-align:right;font-family:var(--mono);font-size:13px;font-weight:700;color:var(--accent)">
                  {{ number_format($boxPrice) }}
                  <span style="font-size:10px;font-weight:600;color:var(--text-dim)"> RWF</span>
                </td>
                <td style="padding:12px;text-align:center">
                  @if($available > 0)
                    <button wire:click="openAddModal({{ $product->id }})"
                            style="padding:6px 16px;background:var(--accent);color:#fff;border:none;
                                   border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;
                                   font-family:var(--font);box-shadow:0 2px 8px rgba(59,111,212,.22);
                                   transition:opacity .15s"
                            onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                      Add
                    </button>
                  @else
                    <span style="font-size:11px;font-weight:600;color:var(--text-dim);
                                 background:var(--surface2);padding:4px 10px;border-radius:6px">
                      In cart
                    </span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

  {{-- RIGHT: Cart panel (POS-style) --}}
  <div style="position:sticky;top:84px">
    <div class="whs-card" style="display:flex;flex-direction:column">

      {{-- Cart header --}}
      <div style="padding:14px 16px 12px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:9px">
          <div style="width:32px;height:32px;border-radius:9px;background:var(--surface2);display:grid;place-items:center">
            <svg width="15" height="15" fill="none" stroke="var(--text-dim)" stroke-width="2.5" viewBox="0 0 24 24">
              <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
          </div>
          <span style="font-size:15px;font-weight:800;color:var(--text)">Cart</span>
          @if(!empty($cart))
            <span style="background:var(--accent);color:#fff;font-size:10px;font-weight:800;
                         padding:2px 8px;border-radius:10px;letter-spacing:.2px">
              {{ count($cart) }}
            </span>
          @endif
        </div>
        @if(!empty($cart))
          <button wire:click="$set('cart', [])"
                  wire:confirm="Clear all items from cart?"
                  style="font-size:11px;font-weight:700;color:var(--red);
                         background:color-mix(in srgb,var(--red) 8%,transparent);
                         border:1px solid color-mix(in srgb,var(--red) 20%,transparent);
                         padding:4px 10px;border-radius:7px;cursor:pointer">
            Clear all
          </button>
        @endif
      </div>

      {{-- Cart items --}}
      <div style="padding:10px 12px 4px;max-height:340px;overflow-y:auto">
        @forelse($cart as $i => $item)
          <div class="whs-cart-item" wire:key="ci-{{ $i }}">
            <div style="display:flex;align-items:flex-start;gap:7px;margin-bottom:7px">
              <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:700;color:var(--text);
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                  {{ $item['product_name'] }}
                </div>
                <div style="margin-top:4px">
                  <span style="font-size:9px;font-weight:700;padding:2px 6px;border-radius:5px;
                               background:color-mix(in srgb,var(--accent) 12%,transparent);color:var(--accent)">
                    BOX
                  </span>
                </div>
              </div>
              <button wire:click="removeFromCart({{ $i }})"
                      style="width:28px;height:28px;border-radius:7px;
                             background:color-mix(in srgb,var(--red) 10%,transparent);
                             border:none;cursor:pointer;display:grid;place-items:center;
                             color:var(--red);flex-shrink:0">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <polyline points="3 6 5 6 21 6"/>
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                </svg>
              </button>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between">
              <span style="font-size:11px;color:var(--text-dim)">
                {{ $item['boxes'] }}&nbsp;{{ $item['boxes'] === 1 ? 'box' : 'boxes' }}
                &nbsp;·&nbsp;{{ number_format($item['box_price']) }}&nbsp;RWF/box
              </span>
              <span style="font-size:15px;font-weight:800;color:var(--text);font-family:var(--mono)">
                {{ number_format($item['line_total']) }}
                <span style="font-size:9px;font-weight:600;color:var(--text-dim)">RWF</span>
              </span>
            </div>
          </div>
        @empty
          <div style="min-height:160px;display:flex;flex-direction:column;align-items:center;
                      justify-content:center;text-align:center;padding:24px;color:var(--text-dim)">
            <svg width="40" height="40" fill="none" stroke="var(--border)" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:12px">
              <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            <div style="font-size:13px;font-weight:700;color:var(--text-sub)">Cart is empty</div>
            <div style="font-size:12px;margin-top:4px">Click Add on a product to begin</div>
          </div>
        @endforelse
      </div>

      {{-- Cart footer --}}
      <div style="border-top:1.5px solid var(--border);padding:14px 16px 16px">
        @if(!empty($cart))
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
            <span style="font-size:15px;font-weight:700;color:var(--text)">Total</span>
            <span style="font-size:28px;font-weight:800;color:var(--accent);font-family:var(--mono);line-height:1">
              {{ number_format($cartTotal) }}<span style="font-size:12px;font-weight:600;color:var(--text-dim)"> RWF</span>
            </span>
          </div>
        @endif
        <button wire:click="goToCheckout"
                @if(empty($cart)) disabled @endif
                style="width:100%;padding:14px 16px;
                       background:{{ empty($cart) ? 'var(--surface2)' : 'linear-gradient(135deg,#3b6fd4,#6b8dff)' }};
                       color:{{ empty($cart) ? 'var(--text-dim)' : '#fff' }};
                       border:none;border-radius:12px;font-size:15px;font-weight:800;
                       cursor:{{ empty($cart) ? 'not-allowed' : 'pointer' }};font-family:var(--font);
                       box-shadow:{{ empty($cart) ? 'none' : '0 4px 20px rgba(59,111,212,.38)' }};
                       display:flex;align-items:center;justify-content:center;gap:9px;transition:.15s">
          @if(empty($cart))
            Add items to cart first
          @else
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
            Checkout — {{ number_format($cartTotal) }} RWF
          @endif
        </button>
      </div>

    </div>
  </div>

</div>

@endif

{{-- ═══════════════════════════════════════════════════════════
     STEP: CHECKOUT
═══════════════════════════════════════════════════════════ --}}
@if($step === 'checkout')

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:flex-start" class="whs-checkout-grid">

  {{-- LEFT: Order summary + Customer + Fulfillment --}}
  <div style="display:flex;flex-direction:column;gap:16px">

    {{-- Order summary --}}
    <div class="whs-card" style="padding:18px 20px">
      <div class="whs-section-label">Order Summary</div>
      <div class="whs-order-card">
        @foreach($cart as $item)
          <div class="whs-order-row">
            <span class="whs-order-name">
              {{ $item['product_name'] }}
              <span style="color:var(--text-dim)"> × {{ $item['boxes'] }} {{ $item['boxes'] === 1 ? 'box' : 'boxes' }}</span>
            </span>
            <span class="whs-order-amt">{{ number_format($item['line_total']) }}</span>
          </div>
        @endforeach
        <div class="whs-order-total">
          <span class="whs-order-total-label">Total</span>
          <div>
            <span class="whs-order-total-amt">{{ number_format($cartTotal) }}</span>
            <span class="whs-order-total-rwf">RWF</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Customer (optional) --}}
    <div class="whs-card" style="padding:18px 20px">
      <div class="whs-section-label">
        Customer <span style="font-weight:400;text-transform:none;letter-spacing:0">(optional)</span>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div>
          <label style="display:block;font-size:10px;font-weight:700;color:var(--text-dim);
                        text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px">Name</label>
          <input wire:model="customerName" type="text" placeholder="Customer name" class="whs-input">
        </div>
        <div>
          <label style="display:block;font-size:10px;font-weight:700;color:var(--text-dim);
                        text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px">Phone</label>
          <input wire:model="customerPhone" type="text" placeholder="07X XXX XXXX" class="whs-input mono">
        </div>
      </div>
    </div>

    {{-- Fulfillment Method --}}
    <div class="whs-card" style="padding:18px 20px">
      <div class="whs-section-label">
        Delivery Method
        <span style="color:var(--red);letter-spacing:0;text-transform:none;font-weight:400"> required</span>
      </div>

      <div style="display:flex;gap:10px;margin-bottom:14px">
        <button type="button" wire:click="$set('fulfillmentMethod','transporter')"
                class="whs-fulfill-btn {{ $fulfillmentMethod === 'transporter' ? 'active' : '' }}">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
               style="margin:0 auto 5px;display:block;color:{{ $fulfillmentMethod === 'transporter' ? 'var(--accent)' : 'var(--text-dim)' }}">
            <rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
            <circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
          </svg>
          <div class="whs-fulfill-btn-title">Via Transporter</div>
          <div class="whs-fulfill-btn-sub">Delivered to customer</div>
        </button>
        <button type="button" wire:click="$set('fulfillmentMethod','customer_pickup')"
                class="whs-fulfill-btn {{ $fulfillmentMethod === 'customer_pickup' ? 'active' : '' }}">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
               style="margin:0 auto 5px;display:block;color:{{ $fulfillmentMethod === 'customer_pickup' ? 'var(--accent)' : 'var(--text-dim)' }}">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          <div class="whs-fulfill-btn-title">Customer Pickup</div>
          <div class="whs-fulfill-btn-sub">Goes to {{ $warehouseName }}</div>
        </button>
      </div>

      @if($fulfillmentMethod === 'transporter')
        <div style="margin-bottom:12px">
          <label style="display:block;font-size:10px;font-weight:700;color:var(--text-dim);
                        text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px">
            Transporter <span style="color:var(--red)">*</span>
          </label>
          <select wire:model="fulfillmentTransporterId" class="whs-input" style="cursor:pointer">
            <option value="">Select transporter…</option>
            @foreach($transporters as $t)
              <option value="{{ $t->id }}">{{ $t->name }}{{ $t->phone ? ' · '.$t->phone : '' }}</option>
            @endforeach
          </select>
        </div>
      @endif

      <div>
        <label style="display:block;font-size:10px;font-weight:700;color:var(--text-dim);
                      text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px">
          Notes <span style="font-weight:400;text-transform:none;letter-spacing:0">(optional)</span>
        </label>
        <input wire:model="fulfillmentNotes" type="text"
               placeholder="Delivery address, timing, instructions…"
               class="whs-input">
      </div>
    </div>

  </div>{{-- /left --}}

  {{-- RIGHT: Payment (Alpine-powered, POS-style) --}}
  <div style="position:sticky;top:84px"
       x-data="{
           total:  {{ (int) $cartTotal }},
           momo:   null,
           card:   null,
           bank:   null,
           credit: null,
           get m()       { return Number(this.momo)   || 0 },
           get c()       { return Number(this.card)   || 0 },
           get b()       { return Number(this.bank)   || 0 },
           get cr()      { return Number(this.credit) || 0 },
           get nonCash() { return this.m + this.c + this.b + this.cr },
           get cash()    { return Math.max(0, this.total - this.nonCash) },
           get fillPct() { return this.total > 0 ? Math.min(100, Math.round(this.nonCash / this.total * 100)) : 0 },
           get isOver()  { return this.nonCash > this.total },
           get isOk()    { return !this.isOver },
           submit() {
               $wire.processPayment(this.cash, this.m, this.c, this.b, this.cr);
           }
       }">

    <div class="whs-card" style="padding:18px 20px">

      <div class="whs-section-label">Payment</div>

      {{-- Balance strip --}}
      <div class="whs-bal-strip">
        <div class="whs-bal-strip-nums">
          <div>
            <span class="whs-bal-total">{{ number_format($cartTotal) }}</span>
            <span class="whs-bal-unit">RWF</span>
          </div>
          <div class="whs-bal-status" :style="isOver ? 'color:var(--red)' : 'color:var(--green)'">
            <span x-show="isOk">✓ Balanced</span>
            <span x-show="isOver" x-cloak>⚠ Over-allocated</span>
          </div>
        </div>
        <div class="whs-bal-bar-wrap">
          <div class="whs-bal-bar"
               :style="`width:${fillPct}%;background:${isOver ? 'var(--red)' : (fillPct >= 100 ? 'var(--green)' : 'var(--accent)')}`">
          </div>
        </div>
      </div>

      {{-- Non-cash channels --}}
      <div class="whs-pay-list">

        {{-- Mobile Money --}}
        <div class="whs-pay-row" :class="m > 0 ? 'is-active' : ''">
          <div class="whs-pay-icon" style="background:rgba(16,185,129,.12)">
            <svg width="14" height="14" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24">
              <rect x="5" y="2" width="14" height="20" rx="2"/>
              <line x1="12" y1="18" x2="12.01" y2="18" stroke-linecap="round" stroke-width="2.5"/>
            </svg>
          </div>
          <div class="whs-pay-meta">
            <div class="whs-pay-label">Mobile Money</div>
          </div>
          <div class="whs-pay-amount-wrap">
            <input x-model.number="momo" type="number" min="0" placeholder="0" class="whs-pay-amount">
            <span class="whs-pay-amount-unit">RWF</span>
          </div>
        </div>

        {{-- Bank Transfer --}}
        <div class="whs-pay-row" :class="b > 0 ? 'is-active' : ''">
          <div class="whs-pay-icon" style="background:rgba(99,102,241,.12)">
            <svg width="14" height="14" fill="none" stroke="#6366f1" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 10h16v11H4zM2 7l10-4 10 4M8 14v3m4-3v3m4-3v3"/>
            </svg>
          </div>
          <div class="whs-pay-meta">
            <div class="whs-pay-label">Bank Transfer</div>
          </div>
          <div class="whs-pay-amount-wrap">
            <input x-model.number="bank" type="number" min="0" placeholder="0" class="whs-pay-amount">
            <span class="whs-pay-amount-unit">RWF</span>
          </div>
        </div>

        {{-- Card --}}
        <div class="whs-pay-row" :class="c > 0 ? 'is-active' : ''">
          <div class="whs-pay-icon" style="background:rgba(59,130,246,.12)">
            <svg width="14" height="14" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24">
              <rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
          </div>
          <div class="whs-pay-meta">
            <div class="whs-pay-label">Card</div>
          </div>
          <div class="whs-pay-amount-wrap">
            <input x-model.number="card" type="number" min="0" placeholder="0" class="whs-pay-amount">
            <span class="whs-pay-amount-unit">RWF</span>
          </div>
        </div>

        {{-- Credit --}}
        <div class="whs-pay-row" :class="cr > 0 ? 'is-active' : ''">
          <div class="whs-pay-icon" style="background:rgba(245,158,11,.12)">
            <svg width="14" height="14" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                       M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
          <div class="whs-pay-meta">
            <div class="whs-pay-label">Credit</div>
          </div>
          <div class="whs-pay-amount-wrap">
            <input x-model.number="credit" type="number" min="0" placeholder="0"
                   class="whs-pay-amount"
                   :style="cr > 0 ? 'border-color:var(--amber)' : ''">
            <span class="whs-pay-amount-unit">RWF</span>
          </div>
        </div>

      </div>{{-- /pay-list --}}

      {{-- Cash: auto-computed remainder --}}
      <div class="whs-cash-row">
        <div class="whs-pay-icon" style="background:rgba(16,185,129,.12)">
          <svg width="14" height="14" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24">
            <rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/>
            <path stroke-linecap="round" d="M6 10h.01M18 14h.01"/>
          </svg>
        </div>
        <div class="whs-pay-meta">
          <div class="whs-pay-label" style="color:var(--green)">Cash</div>
          <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Remainder — auto-calculated</div>
        </div>
        <div class="whs-cash-display"
             :style="cash < 0 ? 'border-color:var(--red);color:var(--red);background:rgba(239,68,68,.06)' : ''">
          <span x-text="new Intl.NumberFormat().format(cash)" style="font-size:14px"></span>
          <span class="whs-cash-badge" :style="cash > 0 ? '' : 'background:var(--text-dim)'">AUTO</span>
        </div>
      </div>

      {{-- Over-allocation error --}}
      <div x-show="isOver" x-cloak
           style="margin-bottom:12px;padding:9px 12px;
                  background:color-mix(in srgb,var(--red) 7%,transparent);
                  border:1.5px solid var(--red);border-radius:10px;font-size:12px;color:var(--red)">
        <strong>Over-allocated:</strong> non-cash total exceeds the order by
        <span x-text="new Intl.NumberFormat().format(nonCash - total)"></span> RWF — reduce an amount above.
      </div>

      {{-- Complete sale button --}}
      <button @click="submit()"
              wire:loading.attr="disabled"
              :disabled="isOver"
              class="whs-complete-btn"
              :style="isOver ? 'opacity:.4;cursor:not-allowed;box-shadow:none' : ''">
        <span wire:loading.remove style="display:flex;align-items:center;gap:7px">
          <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          Complete Sale — {{ number_format($cartTotal) }} RWF
        </span>
        <span wire:loading style="display:none;font-size:14px">Processing…</span>
      </button>

      <button wire:click="backToCart"
              style="width:100%;margin-top:10px;padding:10px;background:transparent;color:var(--text-sub);
                     border:1.5px solid var(--border);border-radius:11px;font-size:13px;font-weight:700;
                     cursor:pointer;font-family:var(--font);transition:all .15s"
              onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'"
              onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-sub)'">
        ← Back to Cart
      </button>

    </div>

  </div>{{-- /right alpine --}}

</div>

@endif

{{-- ═══════════════════════════════════════════════════════════
     STEP: DONE
═══════════════════════════════════════════════════════════ --}}
@if($step === 'done' && $completedSale)

<div style="max-width:640px;margin:0 auto">

  {{-- Success header --}}
  <div style="background:color-mix(in srgb,var(--green) 10%,var(--surface));
              border:1px solid color-mix(in srgb,var(--green) 30%,var(--border));
              border-radius:16px;padding:20px 24px;margin-bottom:20px;
              display:flex;align-items:center;gap:16px">
    <div style="width:48px;height:48px;border-radius:14px;background:var(--green);
                display:grid;place-items:center;flex-shrink:0">
      <svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
    </div>
    <div>
      <div style="font-size:17px;font-weight:800;color:var(--text)">Sale Recorded</div>
      <div style="font-size:13px;color:var(--text-sub);margin-top:3px">
        Pending fulfillment at {{ $warehouseName }} ·
        <span style="font-family:var(--mono);font-weight:700;color:var(--green)">
          {{ $completedSale->sale_number }}
        </span>
      </div>
    </div>
  </div>

  {{-- Boxes to hand over --}}
  <div class="whs-card" style="margin-bottom:16px;padding:18px 20px">
    <div class="whs-section-label">Boxes to Hand Over at {{ $warehouseName }}</div>
    @foreach($completedSale->items->groupBy('product_id') as $productId => $items)
      @php $product = $items->first()->product; @endphp
      <div style="margin-bottom:14px">
        <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:7px">
          {{ $product?->name }}
          <span style="font-size:11px;font-weight:400;color:var(--text-dim)">
            ({{ $items->count() }} box{{ $items->count() !== 1 ? 'es' : '' }})
          </span>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:7px">
          @foreach($items as $item)
            <div style="display:inline-flex;align-items:center;gap:6px;
                        background:var(--surface2);border:1px solid var(--border);
                        border-radius:8px;padding:6px 12px">
              <svg width="11" height="11" fill="none" stroke="var(--accent)" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
              </svg>
              <span style="font-family:var(--mono);font-size:13px;font-weight:700;color:var(--accent)">
                {{ $item->box?->box_code ?? '—' }}
              </span>
              <span style="font-size:11px;color:var(--text-dim)">{{ $item->quantity_sold }} items</span>
            </div>
          @endforeach
        </div>
      </div>
    @endforeach
  </div>

  {{-- Fulfillment info --}}
  <div class="whs-card" style="margin-bottom:16px;padding:18px 20px">
    <div class="whs-section-label">Fulfillment</div>
    <div style="display:flex;align-items:center;gap:12px">
      @if($completedSale->fulfillment_method === 'transporter')
        <div style="width:40px;height:40px;border-radius:11px;
                    background:color-mix(in srgb,var(--accent) 12%,transparent);
                    display:grid;place-items:center;flex-shrink:0">
          <svg width="18" height="18" fill="none" stroke="var(--accent)" stroke-width="2" viewBox="0 0 24 24">
            <rect x="1" y="3" width="15" height="13"/>
            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
            <circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
          </svg>
        </div>
        <div>
          <div style="font-size:13px;font-weight:700;color:var(--text)">
            Via Transporter: {{ $completedSale->fulfillmentTransporter?->name ?? '—' }}
          </div>
          @if($completedSale->fulfillment_notes)
            <div style="font-size:12px;color:var(--text-sub);margin-top:2px">
              {{ $completedSale->fulfillment_notes }}
            </div>
          @endif
        </div>
      @else
        <div style="width:40px;height:40px;border-radius:11px;
                    background:color-mix(in srgb,var(--green) 12%,transparent);
                    display:grid;place-items:center;flex-shrink:0">
          <svg width="18" height="18" fill="none" stroke="var(--green)" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
        <div>
          <div style="font-size:13px;font-weight:700;color:var(--text)">
            Customer Pickup at {{ $warehouseName }}
          </div>
          @if($completedSale->fulfillment_notes)
            <div style="font-size:12px;color:var(--text-sub);margin-top:2px">
              {{ $completedSale->fulfillment_notes }}
            </div>
          @endif
        </div>
      @endif
    </div>
  </div>

  {{-- Total collected --}}
  <div class="whs-card" style="padding:16px 24px;margin-bottom:20px">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <span style="font-size:14px;font-weight:600;color:var(--text-sub)">Total Collected</span>
      <span style="font-size:26px;font-weight:800;color:var(--accent);font-family:var(--mono)">
        {{ number_format($completedSale->total) }}
        <span style="font-size:13px;font-weight:600;color:var(--text-dim)"> RWF</span>
      </span>
    </div>
  </div>

  <button wire:click="newSale"
          style="width:100%;height:50px;background:linear-gradient(135deg,#3b6fd4,#6b8dff);
                 color:#fff;border:none;border-radius:13px;font-size:15px;font-weight:800;
                 cursor:pointer;font-family:var(--font);box-shadow:0 4px 18px rgba(59,111,212,.35);
                 display:flex;align-items:center;justify-content:center;gap:8px">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
      <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
    </svg>
    New Warehouse Sale
  </button>

</div>

@endif {{-- /step === 'done' --}}

@endif {{-- /tab === 'sale' --}}

{{-- ═══════════════════════════════════════════════════════════
     HISTORY TAB
═══════════════════════════════════════════════════════════ --}}
@if($tab === 'history')

@if($saleHistory->isEmpty())
    <div class="whs-card" style="padding:48px 24px;text-align:center">
        <svg width="48" height="48" fill="none" stroke="var(--border)" stroke-width="1.5" viewBox="0 0 24 24"
             style="margin:0 auto 12px;display:block">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div style="font-size:16px;font-weight:600;color:var(--text-sub);margin-bottom:4px">No warehouse sales yet</div>
        <div style="font-size:13px;color:var(--text-dim)">Completed warehouse direct sales from this shop will appear here.</div>
    </div>
@else
    <div class="whs-card" style="overflow-x:auto">
        <table class="whs-hist-table">
            <thead>
                <tr>
                    <th class="whs-hist-th">Sale #</th>
                    <th class="whs-hist-th">Date</th>
                    <th class="whs-hist-th">Items</th>
                    <th class="whs-hist-th">Status</th>
                    <th class="whs-hist-th">Method</th>
                    <th class="whs-hist-th" style="text-align:right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($saleHistory as $sale)
                    <tr class="whs-hist-row" wire:key="shist-{{ $sale->id }}">
                        <td class="whs-hist-td">
                            <span style="font-family:var(--mono);font-weight:700;color:var(--text);font-size:12px">
                                {{ $sale->sale_number }}
                            </span>
                        </td>
                        <td class="whs-hist-td" style="white-space:nowrap;font-size:12px">
                            {{ $sale->sale_date->format('d M Y') }}
                            <span style="color:var(--text-dim)">{{ $sale->sale_date->format('H:i') }}</span>
                        </td>
                        <td class="whs-hist-td">
                            {{ $sale->items->count() }} box{{ $sale->items->count() !== 1 ? 'es' : '' }}
                            @if($sale->items->count())
                                <div style="font-size:11px;color:var(--text-dim);margin-top:2px">
                                    {{ $sale->items->pluck('product.name')->filter()->unique()->implode(', ') }}
                                </div>
                            @endif
                        </td>
                        <td class="whs-hist-td">
                            @if($sale->fulfillment_status === 'fulfilled')
                                <span style="display:inline-flex;align-items:center;gap:4px;
                                             background:color-mix(in srgb,var(--green) 12%,transparent);
                                             color:var(--green);font-size:11px;font-weight:700;
                                             padding:3px 9px;border-radius:20px;text-transform:uppercase;letter-spacing:.04em">
                                    <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    Fulfilled
                                </span>
                            @else
                                <span style="display:inline-flex;align-items:center;gap:4px;
                                             background:color-mix(in srgb,var(--amber) 12%,transparent);
                                             color:var(--amber);font-size:11px;font-weight:700;
                                             padding:3px 9px;border-radius:20px;text-transform:uppercase;letter-spacing:.04em">
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td class="whs-hist-td">
                            @if($sale->fulfillment_method === 'transporter')
                                <span style="font-size:12px;color:var(--text-sub)">
                                    {{ $sale->fulfillmentTransporter?->name ?? 'Transporter' }}
                                </span>
                            @else
                                <span style="font-size:12px;color:var(--text-sub)">Customer Pickup</span>
                            @endif
                        </td>
                        <td class="whs-hist-td" style="text-align:right;font-family:var(--mono);font-weight:700;color:var(--text);white-space:nowrap">
                            {{ number_format($sale->total) }}
                            <span style="font-size:10px;font-weight:600;color:var(--text-dim)">RWF</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@endif {{-- /history tab --}}

{{-- ═══════════════════════════════════════════════════════════
     ADD-TO-CART MODAL (POS staging modal style)
═══════════════════════════════════════════════════════════ --}}
@if($showAddModal && $stagingProductId)
  @php $stagingProduct = $warehouseStock->firstWhere('id', $stagingProductId); @endphp
  @if($stagingProduct)
    @php
      $bp           = $stagingProduct->box_selling_price ?? ($stagingProduct->selling_price * $stagingProduct->items_per_box);
      $stagingTotal = max(0, (int)$stagingBoxes) * $bp;
    @endphp
    <div class="whs-sm-overlay">
      <div class="whs-sm-card">

        {{-- Header --}}
        <div class="whs-sm-head">
          <div style="min-width:0">
            <div class="whs-sm-title">Add to Cart</div>
            <div class="whs-sm-subtitle">{{ $stagingProduct->name }}</div>
          </div>
          <button wire:click="closeAddModal" class="whs-sm-close">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        {{-- Body --}}
        <div class="whs-sm-body">

          {{-- Info strip --}}
          <div class="whs-sm-info">
            <div style="min-width:0">
              <div style="font-size:10px;font-family:var(--mono);color:var(--text-dim);
                          text-transform:uppercase;letter-spacing:.4px;margin-bottom:2px">
                {{ $stagingProduct->sku }}
              </div>
              <div style="font-size:12px;color:var(--text-sub)">
                {{ $stagingProduct->items_per_box }} items/box
                @if($stagingProduct->category_name) · {{ $stagingProduct->category_name }}@endif
              </div>
            </div>
            <div style="text-align:right;flex-shrink:0">
              <div style="font-size:10px;color:var(--text-dim);margin-bottom:3px">Available</div>
              <div style="font-size:20px;font-weight:800;font-family:var(--mono);line-height:1;color:var(--green)">
                {{ $stagingProduct->box_count }}
                <span style="font-size:11px;font-weight:600;color:var(--text-dim)">boxes</span>
              </div>
            </div>
          </div>

          {{-- Quantity stepper --}}
          <div>
            <div style="font-size:10px;font-weight:700;color:var(--text-dim);
                        text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px">
              Number of Boxes
            </div>
            <div class="whs-sm-stepper">
              <button type="button" class="whs-sm-step-btn"
                      wire:click="$set('stagingBoxes', max(1, $stagingBoxes - 1))">&minus;</button>
              <input wire:model.live="stagingBoxes" type="number"
                     min="1" max="{{ $stagingProduct->box_count }}"
                     class="whs-sm-qty-input">
              <button type="button" class="whs-sm-step-btn"
                      wire:click="$set('stagingBoxes', min($stagingProduct->box_count, $stagingBoxes + 1))">+</button>
            </div>
          </div>

          {{-- Line total --}}
          <div class="whs-sm-total">
            <div>
              <div style="font-size:12px;color:var(--text-sub)">Line total</div>
              <div style="font-size:11px;color:var(--text-dim);margin-top:2px">
                {{ (int)$stagingBoxes }} × {{ number_format($bp) }} RWF
              </div>
            </div>
            <div>
              <span style="font-size:26px;font-weight:800;font-family:var(--mono);color:var(--accent);line-height:1">
                {{ number_format($stagingTotal) }}
              </span>
              <span style="font-size:12px;font-weight:600;color:var(--text-dim);margin-left:3px">RWF</span>
            </div>
          </div>

        </div>

        {{-- Footer --}}
        <div class="whs-sm-footer">
          <button wire:click="closeAddModal" class="whs-sm-cancel">Cancel</button>
          <button wire:click="confirmAddToCart"
                  wire:loading.attr="disabled"
                  class="whs-sm-confirm">
            <span wire:loading.remove style="display:flex;align-items:center;gap:6px">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
              </svg>
              Add to Cart
            </span>
            <span wire:loading style="display:none;font-size:13px">Adding…</span>
          </button>
        </div>

      </div>
    </div>
  @endif
@endif

@endif {{-- /sessionBlocked --}}
</div>
