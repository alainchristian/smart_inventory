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

/* Card wrapper — matches POS shadow-card pattern (no overflow:hidden, no border) */
.whs-card {
    background:var(--surface);border:none;
    border-radius:var(--r);box-shadow:var(--shadow-card);
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
.whs-pay-amount-wrap { position:relative;width:130px;flex-shrink:0;min-width:80px }
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
    width:130px;flex-shrink:0;min-width:80px;padding:7px 10px;
    border:1.5px solid var(--green);border-radius:8px;
    background:rgba(16,185,129,.06);color:var(--green);
    font-size:14px;font-family:var(--mono);font-weight:700;
    text-align:right;display:flex;align-items:center;justify-content:space-between;gap:4px;
}
.whs-cash-badge {
    font-size:8px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;
    background:var(--green);color:#fff;padding:2px 5px;border-radius:4px;flex-shrink:0;
}

/* Balance strip — matches POS co-bal-strip */
.whs-bal-strip {
    border:1px solid var(--border);border-radius:11px;
    overflow:hidden;margin-bottom:12px;padding:11px 13px;background:var(--bg);
}
.whs-bal-strip-nums { display:flex;align-items:baseline;justify-content:space-between;margin-bottom:8px }
.whs-bal-total { font-size:24px;font-weight:800;font-family:var(--mono);color:var(--text);line-height:1 }
.whs-bal-unit { font-size:11px;font-weight:600;color:var(--text-dim);margin-left:3px }
.whs-bal-status { font-size:12px;font-weight:700 }
.whs-bal-bar-wrap { height:5px;border-radius:99px;background:var(--border);overflow:hidden }
.whs-bal-bar { height:100%;border-radius:99px;transition:width .2s,background .2s }

/* Order summary — matches POS co-order-card (bg, border) */
.whs-order-card {
    background:var(--bg);border:1px solid var(--border);
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
    width:100%;min-height:50px;padding:10px 16px;
    background:var(--green);color:#fff;border:none;border-radius:13px;
    font-size:15px;font-weight:800;cursor:pointer;font-family:var(--font);
    display:flex;align-items:center;justify-content:center;gap:8px;
    box-shadow:0 5px 18px var(--green-glow);transition:opacity .15s;
    white-space:normal;text-align:center;
}
.whs-complete-btn:hover:not(:disabled) { opacity:.92 }
.whs-complete-btn:disabled { opacity:.5;cursor:not-allowed;box-shadow:none }
.whs-complete-btn-label { display:flex;align-items:center;gap:7px;flex-wrap:wrap;justify-content:center }
.whs-complete-btn-amt   { white-space:nowrap }

/* Customer section — mirrors POS co-customer-* / co-search-* / co-new-cust-* */
.whs-customer-selected {
    display:flex;align-items:center;justify-content:space-between;
    padding:11px 14px;background:var(--surface2);border:1.5px solid var(--accent);
    border-radius:11px;gap:10px;
}
.whs-customer-name  { font-size:13px;font-weight:700;color:var(--text);margin-bottom:3px }
.whs-customer-phone { font-size:11px;color:var(--text-sub);font-family:var(--mono) }
.whs-customer-credit {
    display:inline-block;margin-left:8px;padding:1px 7px;
    background:rgba(245,158,11,.12);color:var(--amber);border-radius:5px;font-weight:700;font-size:10px;
}
.whs-customer-clear {
    width:26px;height:26px;border-radius:50%;border:none;
    background:rgba(239,68,68,.1);color:var(--red);
    cursor:pointer;display:grid;place-items:center;font-size:16px;
    flex-shrink:0;line-height:1;padding:0;transition:background .15s;
}
.whs-customer-clear:hover { background:rgba(239,68,68,.2) }
.whs-search-wrap { position:relative }
.whs-search-input {
    width:100%;box-sizing:border-box;padding:9px 11px;
    border:1.5px solid var(--border);border-radius:10px;
    font-size:13px;background:var(--surface);color:var(--text);
    outline:none;transition:border-color .15s;font-family:var(--font);
}
.whs-search-input:focus { border-color:var(--accent) }
.whs-search-dropdown {
    position:absolute;top:calc(100% + 5px);left:0;right:0;z-index:20;
    background:var(--surface);border:1.5px solid var(--border);border-radius:11px;
    box-shadow:0 10px 30px rgba(0,0,0,.18);max-height:200px;overflow-y:auto;
}
.whs-search-result {
    width:100%;padding:10px 13px;text-align:left;border:none;background:transparent;
    cursor:pointer;border-bottom:1px solid var(--border);transition:background .15s;
    font-family:var(--font);display:block;
}
.whs-search-result:last-child { border-bottom:none }
.whs-search-result:hover { background:var(--surface2) }
.whs-search-result-name  { font-size:13px;font-weight:700;color:var(--text);margin-bottom:2px }
.whs-search-result-phone { font-size:11px;color:var(--text-dim);font-family:var(--mono) }
.whs-search-result-credit { margin-left:6px;color:var(--amber);font-weight:700 }
.whs-new-cust-btn {
    width:100%;margin-top:8px;padding:8px;border-radius:10px;
    border:1.5px dashed var(--border);background:transparent;
    font-size:12px;font-weight:700;cursor:pointer;color:var(--text-dim);
    transition:all .15s;font-family:var(--font);
}
.whs-new-cust-btn:hover { border-color:var(--accent);color:var(--accent) }
.whs-new-cust-form {
    background:var(--bg);border:1px solid var(--border);border-radius:11px;padding:14px;
}
.whs-new-cust-title { font-size:13px;font-weight:800;color:var(--text);margin-bottom:12px }
.whs-new-cust-btns  { display:flex;gap:8px;margin-top:10px }
.whs-btn-secondary {
    flex:1;padding:9px;border-radius:9px;border:1.5px solid var(--border);
    background:transparent;font-size:12px;font-weight:600;cursor:pointer;
    color:var(--text-sub);font-family:var(--font);transition:all .15s;
}
.whs-btn-secondary:hover { border-color:var(--accent);color:var(--accent) }
.whs-btn-primary {
    flex:1;padding:9px;border-radius:9px;border:none;
    background:var(--accent);color:#fff;font-size:12px;font-weight:700;
    cursor:pointer;font-family:var(--font);transition:opacity .15s;
}
.whs-btn-primary:hover { opacity:.88 }

/* Cart item card — matches POS cart item style */
.whs-cart-item {
    background:var(--surface);border:1.5px solid var(--border);
    border-radius:var(--r);padding:11px 12px;margin-bottom:8px;
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
    background:var(--bg);border:1px solid var(--border);border-radius:11px;
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
/* Line total — matches POS sm-total exactly */
.whs-sm-total {
    display:flex;align-items:center;justify-content:space-between;
    border-radius:11px;padding:13px 16px;margin-bottom:4px;
    border:1.5px solid var(--border);background:var(--bg);
}
.whs-sm-total.modified { border-color:var(--amber);background:rgba(245,158,11,.06) }
.whs-sm-total-label { font-size:12px;color:var(--text-sub) }
.whs-sm-total-amount {
    font-size:24px;font-weight:800;font-family:var(--mono);color:var(--accent);line-height:1;
}
.whs-sm-total.modified .whs-sm-total-amount { color:var(--amber) }
.whs-sm-total-unit { font-size:11px;font-weight:600;color:var(--text-dim);margin-left:4px }
/* Price override section — identical to POS sm-price-* */
.whs-sm-price-label-row {
    display:flex;align-items:center;justify-content:space-between;min-height:18px;margin-bottom:8px;
}
.whs-sm-price-section-label {
    font-size:10px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.6px;
}
.whs-sm-modified-badge {
    font-size:10px;font-weight:700;color:var(--amber);
    background:rgba(245,158,11,.12);padding:2px 7px;border-radius:5px;transition:opacity .15s;
}
.whs-sm-price-wrap { position:relative }
.whs-sm-price-prefix {
    position:absolute;left:11px;top:50%;transform:translateY(-50%);
    font-size:10px;font-weight:700;color:var(--text-dim);pointer-events:none;
    font-family:var(--mono);
}
.whs-sm-price-input {
    width:100%;box-sizing:border-box;padding:10px 12px 10px 38px;
    border:2px solid var(--border);border-radius:10px;
    background:var(--surface);color:var(--text);font-size:14px;
    font-family:var(--mono);outline:none;transition:border-color .15s;height:48px;
}
.whs-sm-price-input:focus { border-color:var(--accent) }
.whs-sm-price-input.modified { border-color:var(--amber) }
.whs-sm-price-locked {
    height:48px;padding:0 14px;background:var(--surface2);border:2px solid var(--border);
    border-radius:10px;font-family:var(--mono);font-size:14px;font-weight:700;
    color:var(--text-sub);display:flex;align-items:center;justify-content:space-between;
}
.whs-sm-price-locked-hint { font-size:10px;color:var(--text-dim) }
.whs-sm-reason-wrap {
    margin-top:7px;overflow:hidden;transition:max-height .2s,opacity .2s;max-height:0;opacity:0;
}
.whs-sm-reason-wrap.visible { max-height:60px;opacity:1 }
.whs-sm-reason-input {
    width:100%;box-sizing:border-box;padding:8px 11px;
    border:1.5px solid var(--amber);border-radius:8px;
    font-size:13px;background:color-mix(in srgb,var(--amber) 5%,var(--surface));
    color:var(--text);outline:none;font-family:var(--font);
}
.whs-sm-reason-input::placeholder { color:var(--amber);opacity:.6 }
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

/* Tabs — full-width grid tabs matching ui-design.md pattern */
.whs-tabs {
    display:grid;grid-template-columns:1fr 1fr;
    background:var(--surface);box-shadow:var(--shadow-card);
    border-radius:var(--r);margin-bottom:22px;overflow:hidden;
}
.whs-tab-btn {
    display:flex;align-items:center;justify-content:center;gap:6px;
    padding:12px 10px;border:none;border-radius:0;
    border-bottom:2.5px solid transparent;border-right:1px solid var(--border);
    cursor:pointer;font-size:12px;font-weight:600;font-family:var(--font);
    background:transparent;color:var(--text-dim);transition:all var(--tr);white-space:nowrap;
}
.whs-tab-btn:last-child { border-right:none }
.whs-tab-btn:hover { background:var(--surface2);color:var(--text);border-bottom-color:var(--border-hi) }
.whs-tab-btn.active { background:var(--accent-dim);color:var(--accent);border-bottom-color:var(--accent) }

/* History — matches ui-design.md table rules */
.whs-hist-table { width:100%;border-collapse:collapse }
.whs-hist-thead-row { border-bottom:2px solid var(--border) }
.whs-hist-th {
    padding:10px 14px;text-align:left;font-size:11px;font-weight:700;
    color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;
}
.whs-hist-td {
    padding:11px 14px;font-size:13px;color:var(--text-sub);
    border-bottom:1px solid var(--border);vertical-align:middle;transition:background var(--tr);
}
.whs-hist-row:last-child .whs-hist-td { border-bottom:none }
.whs-hist-row:hover .whs-hist-td { background:var(--surface2) }
/* Stock table */
.whs-stock-table { width:100%;border-collapse:collapse;min-width:480px }
.whs-stock-thead-row { border-bottom:2px solid var(--border) }
.whs-stock-th {
    padding:10px 16px;text-align:left;font-size:11px;font-weight:700;
    letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap;
}
.whs-stock-th.center { text-align:center }
.whs-stock-th.right  { text-align:right }
.whs-stock-row { border-top:1px solid var(--border);transition:background var(--tr) }
.whs-stock-row:hover { background:var(--surface2) }
.whs-stock-td { padding:12px 16px;vertical-align:middle }
.whs-stock-td.center { text-align:center }
.whs-stock-td.right  { text-align:right }

/* Back button */
.whs-back-btn:hover { border-color:var(--accent);color:var(--accent) }

/* Responsive */
@media (max-width: 860px) {
    .whs-main-grid     { grid-template-columns:1fr !important; }
    .whs-checkout-grid { grid-template-columns:1fr !important; }
}
@media (max-width: 640px) {
    .whs-tabs { overflow-x:auto;scrollbar-width:none;display:flex }
    .whs-tabs::-webkit-scrollbar { display:none }
    .whs-tab-btn { flex-shrink:0;min-width:110px }
}
@media (max-width: 480px) {
    .whs-complete-btn    { font-size:13px }
    .whs-pay-amount-wrap { width:100px }
    .whs-pay-amount      { font-size:13px }
    .whs-cash-display    { width:100px;font-size:13px }
    .whs-order-total-amt { font-size:20px }
    .whs-bal-total       { font-size:22px }
    .whs-pay-row         { gap:7px;padding:8px 10px }
    .whs-pay-icon        { width:26px;height:26px;border-radius:6px }
}
</style>

{{-- Page header — matches POS header bar style --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:22px;flex-wrap:wrap">
    <div style="display:flex;align-items:center;gap:12px">
        <div style="width:40px;height:40px;border-radius:11px;background:var(--accent-dim);
                    display:grid;place-items:center;flex-shrink:0">
            <svg width="20" height="20" fill="none" stroke="var(--accent)" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <div>
            <h1 style="font-size:20px;font-weight:800;color:var(--text);margin:0 0 3px;letter-spacing:-.3px">
                Warehouse Sale
            </h1>
            <p style="font-size:13px;color:var(--text-dim);margin:0">
                {{ $warehouseName }}
                @if($step === 'checkout') · <span style="color:var(--accent);font-weight:600">Checkout</span>@endif
                @if($step === 'done') · <span style="color:var(--green);font-weight:600">Complete</span>@endif
            </p>
        </div>
    </div>
    @if($step === 'cart' && !empty($cart))
    <div style="display:flex;align-items:center;gap:8px;background:var(--accent);color:#fff;
                padding:6px 14px;border-radius:22px">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        <span style="font-size:12px;font-weight:700;font-family:var(--mono)">
            {{ count($cart) }} · {{ number_format($cartTotal) }} RWF
        </span>
    </div>
    @endif
</div>

{{-- Tab switcher — only show when not mid-checkout/done --}}
@if($step === 'cart' || $tab === 'history')
<div class="whs-tabs">
    <button class="whs-tab-btn {{ $tab === 'sale' ? 'active' : '' }}" wire:click="setTab('sale')">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        New Sale
    </button>
    <button class="whs-tab-btn {{ $tab === 'history' ? 'active' : '' }}" wire:click="setTab('history')">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
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
      <div style="padding:60px 24px;text-align:center">
        <svg width="48" height="48" fill="none" stroke="var(--border)" stroke-width="1.5" viewBox="0 0 24 24"
             style="margin:0 auto 14px;display:block">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        <div style="font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px">No stock available</div>
        <div style="font-size:13px;color:var(--text-dim)">{{ $warehouseName }} has no boxes in stock</div>
      </div>
    @else
      <div style="overflow-x:auto;-webkit-overflow-scrolling:touch">
        <table class="whs-stock-table">
          <thead>
            <tr class="whs-stock-thead-row">
              <th class="whs-stock-th">Product</th>
              <th class="whs-stock-th center">Boxes</th>
              <th class="whs-stock-th right">Box Price</th>
              <th class="whs-stock-th center">Add</th>
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
              <tr class="whs-stock-row" wire:key="stock-{{ $product->id }}">
                <td class="whs-stock-td">
                  <div style="font-size:13px;font-weight:700;color:var(--text)">{{ $product->name }}</div>
                  <div style="font-size:10px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">
                    {{ $product->sku }}
                    @if($product->category_name) · {{ $product->category_name }}@endif
                    · {{ $product->items_per_box }} items/box
                  </div>
                </td>
                <td class="whs-stock-td center">
                  <span style="font-family:var(--mono);font-size:16px;font-weight:800;
                               color:{{ $available > 0 ? 'var(--green)' : 'var(--text-dim)' }}">
                    {{ $available }}
                  </span>
                  @if($cartBoxes > 0)
                    <div style="font-size:10px;color:var(--text-dim);margin-top:2px">{{ $cartBoxes }} in cart</div>
                  @endif
                </td>
                <td class="whs-stock-td right" style="white-space:nowrap">
                  <span style="font-family:var(--mono);font-size:13px;font-weight:700;color:var(--accent)">{{ number_format($boxPrice) }}</span>
                  <span style="font-size:10px;font-weight:600;color:var(--text-dim)"> RWF</span>
                </td>
                <td class="whs-stock-td center">
                  @if($available > 0)
                    <button wire:click="openAddModal({{ $product->id }})"
                            style="padding:6px 16px;background:var(--accent);color:#fff;border:none;
                                   border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;
                                   font-family:var(--font);box-shadow:0 2px 8px var(--accent-glow);
                                   transition:opacity var(--tr)"
                            onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                      Add
                    </button>
                  @else
                    <span class="xx-stat" style="background:var(--surface2);color:var(--text-dim)">In cart</span>
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
  <div style="position:sticky;top:16px">
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
                <div style="margin-top:4px;display:flex;align-items:center;gap:4px">
                  <span style="font-size:9px;font-weight:700;padding:2px 6px;border-radius:5px;
                               background:color-mix(in srgb,var(--accent) 12%,transparent);color:var(--accent)">
                    BOX
                  </span>
                  @if(!empty($item['price_modified']))
                  <span style="font-size:9px;font-weight:700;padding:2px 6px;border-radius:5px;
                               background:color-mix(in srgb,var(--amber) 15%,transparent);color:var(--amber)">
                    MODIFIED
                  </span>
                  @endif
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
                       background:{{ empty($cart) ? 'var(--surface2)' : 'var(--accent)' }};
                       color:{{ empty($cart) ? 'var(--text-dim)' : '#fff' }};
                       border:none;border-radius:12px;font-size:15px;font-weight:800;
                       cursor:{{ empty($cart) ? 'not-allowed' : 'pointer' }};font-family:var(--font);
                       box-shadow:{{ empty($cart) ? 'none' : '0 4px 18px var(--accent-glow)' }};
                       display:flex;align-items:center;justify-content:center;gap:9px;transition:opacity var(--tr)">
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

      @if($customerId)
      {{-- Selected customer chip --}}
      <div class="whs-customer-selected">
        <div style="min-width:0">
          <div class="whs-customer-name">{{ $customerName }}</div>
          <div class="whs-customer-phone">
            {{ $customerPhone }}
            @if($customerOutstandingBalance > 0)
            <span class="whs-customer-credit">
              Credit: {{ number_format($customerOutstandingBalance) }} RWF
            </span>
            @endif
          </div>
        </div>
        <button wire:click="clearCustomer" class="whs-customer-clear" title="Remove customer">×</button>
      </div>

      @elseif($showNewCustomerForm)
      {{-- Create new customer inline form --}}
      <div class="whs-new-cust-form">
        <div class="whs-new-cust-title">Register New Customer</div>
        <div style="display:grid;gap:9px">
          <input wire:model="newCustomerName"  type="text"  placeholder="Full name *"               class="whs-input">
          <input wire:model="newCustomerPhone" type="text"  placeholder="+250… (phone) *"           class="whs-input mono">
          <input wire:model="newCustomerEmail" type="email" placeholder="email@example.com (optional)" class="whs-input">
        </div>
        <div class="whs-new-cust-btns">
          <button wire:click="cancelNewCustomer" class="whs-btn-secondary">Cancel</button>
          <button wire:click="saveNewCustomer"   class="whs-btn-primary">Save &amp; Select</button>
        </div>
      </div>

      @else
      {{-- Search input + dropdown --}}
      <div class="whs-search-wrap">
        <input wire:model.live="customerSearch" type="text"
               wire:focus="openCustomerSearch"
               placeholder="Search by name or phone…"
               class="whs-search-input"
               autocomplete="off">
        @if($showCustomerSearch && count($customerResults) > 0)
        <div class="whs-search-dropdown">
          @foreach($customerResults as $c)
          <button wire:click="selectCustomer({{ $c['id'] }})" class="whs-search-result" type="button">
            <div class="whs-search-result-name">{{ $c['name'] }}</div>
            <div class="whs-search-result-phone">
              {{ $c['phone'] }}
              @if($c['outstanding_balance'] > 0)
              <span class="whs-search-result-credit">Credit: {{ number_format($c['outstanding_balance']) }}</span>
              @endif
            </div>
          </button>
          @endforeach
        </div>
        @endif
      </div>
      <button wire:click="showCreateCustomerForm" type="button" class="whs-new-cust-btn">
        + Register New Customer
      </button>
      @endif

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
  <div style="position:sticky;top:16px"
       x-data="{
           total:       {{ (int) $cartTotal }},
           momo:        null,
           card:        null,
           bank:        null,
           credit:      null,
           allowCard:   {{ $settingAllowCardPayment  ? 'true' : 'false' }},
           allowBank:   {{ $settingAllowBankTransfer ? 'true' : 'false' }},
           get m()       { return Number(this.momo)   || 0 },
           get c()       { return this.allowCard ? (Number(this.card)   || 0) : 0 },
           get b()       { return this.allowBank ? (Number(this.bank)   || 0) : 0 },
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

      {{-- Credit warning --}}
      <div style="{{ $creditWarningVisible ? '' : 'display:none' }}">
        <div style="margin-bottom:12px;padding:10px 13px;
                    background:color-mix(in srgb,var(--amber) 8%,var(--surface));
                    border:1.5px solid var(--amber);border-radius:10px">
          <div style="font-size:11px;font-weight:800;color:var(--amber);margin-bottom:2px;text-transform:uppercase;letter-spacing:.4px">
            Outstanding Balance
          </div>
          <div style="font-size:12px;color:var(--text)">{{ $creditWarningMessage }}</div>
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
        @if($settingAllowBankTransfer)
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
        @endif

        {{-- Card --}}
        @if($settingAllowCardPayment)
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
        @endif

        {{-- Credit --}}
        @if($settingAllowCreditSales)
        <div class="whs-pay-row" :class="cr > 0 ? 'is-active' : ''">
          <div class="whs-pay-icon" style="background:rgba(245,158,11,.12)">
            <svg width="14" height="14" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                       M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
          <div class="whs-pay-meta">
            <div class="whs-pay-label">
              Credit
              @if(!$customerId && $settingCreditRequiresCustomer)
                <span style="font-size:10px;font-weight:400;color:var(--text-dim);
                             text-transform:none;letter-spacing:0;margin-left:4px">
                  — select customer first
                </span>
              @endif
            </div>
          </div>
          <div class="whs-pay-amount-wrap">
            <input x-model.number="credit" type="number" min="0" placeholder="0"
                   class="whs-pay-amount"
                   :disabled="{{ $settingCreditRequiresCustomer ? 'true' : 'false' }} && !$wire.customerId"
                   :style="cr > 0 ? 'border-color:var(--amber)' : ''">
            <span class="whs-pay-amount-unit">RWF</span>
          </div>
        </div>
        @endif

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
        <span wire:loading.remove class="whs-complete-btn-label">
          <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          <span>Complete Sale</span>
          <span class="whs-complete-btn-amt">— {{ number_format($cartTotal) }} RWF</span>
        </span>
        <span wire:loading style="display:none;font-size:14px">Processing…</span>
      </button>

      <button wire:click="backToCart"
              style="width:100%;margin-top:10px;padding:10px;background:transparent;color:var(--text-sub);
                     border:1.5px solid var(--border);border-radius:11px;font-size:13px;font-weight:700;
                     cursor:pointer;font-family:var(--font);transition:all var(--tr)"
              class="whs-back-btn">
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
          style="width:100%;height:50px;background:var(--accent);
                 color:#fff;border:none;border-radius:13px;font-size:15px;font-weight:800;
                 cursor:pointer;font-family:var(--font);box-shadow:0 4px 18px var(--accent-glow);
                 display:flex;align-items:center;justify-content:center;gap:8px;transition:opacity var(--tr)"
          onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
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
    <div class="whs-card" style="padding:60px 24px;text-align:center">
        <svg width="48" height="48" fill="none" stroke="var(--border)" stroke-width="1.5" viewBox="0 0 24 24"
             style="margin:0 auto 12px;display:block">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div style="font-size:16px;font-weight:600;color:var(--text-sub);margin-bottom:4px">No warehouse sales yet</div>
        <div style="font-size:13px;color:var(--text-dim)">Completed warehouse direct sales from this shop will appear here.</div>
    </div>
@else
    <div class="whs-card">
      <div style="overflow-x:auto;-webkit-overflow-scrolling:touch">
        <table class="whs-hist-table" style="min-width:540px">
            <thead>
                <tr class="whs-hist-thead-row">
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
      $defaultBoxPrice = (int)($stagingProduct->box_selling_price ?? ($stagingProduct->selling_price * $stagingProduct->items_per_box));
      $effectivePrice  = (int)$stagingBoxPrice > 0 ? (int)$stagingBoxPrice : $defaultBoxPrice;
      $stagingTotal    = max(0, (int)$stagingBoxes) * $effectivePrice;
    @endphp
    <div class="whs-sm-overlay">
      <div class="whs-sm-card">

        {{-- Header --}}
        <div class="whs-sm-head">
          <div style="min-width:0">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px">
              <div class="whs-sm-title">Add to Cart</div>
              <span style="font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;
                           background:color-mix(in srgb,var(--accent) 12%,transparent);color:var(--accent);
                           padding:2px 7px;border-radius:5px;flex-shrink:0">Warehouse</span>
            </div>
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
                          text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px">
                {{ $stagingProduct->sku }}
              </div>
              <div style="font-size:12px;color:var(--text-sub)">
                @if($stagingProduct->category_name){{ $stagingProduct->category_name }}@endif
              </div>
            </div>
            <div style="text-align:right;flex-shrink:0">
              <div style="font-size:10px;color:var(--text-dim);margin-bottom:3px;white-space:nowrap">
                At {{ $warehouseName }}
              </div>
              <div style="font-size:20px;font-weight:800;font-family:var(--mono);line-height:1;
                           color:{{ $stagingProduct->box_count > 0 ? 'var(--green)' : 'var(--red)' }}">
                {{ $stagingProduct->box_count }}
                <span style="font-size:11px;font-weight:600;color:var(--text-dim)">boxes</span>
              </div>
              <div style="font-size:11px;color:var(--text-dim);margin-top:2px">
                {{ $stagingProduct->items_per_box }} items / box
              </div>
            </div>
          </div>

          {{-- Full Box indicator --}}
          <div style="border:2px solid var(--accent);border-radius:11px;padding:12px 14px;
                      background:color-mix(in srgb,var(--accent) 6%,var(--surface));margin-bottom:14px">
            <div style="display:flex;align-items:center;justify-content:space-between">
              <div>
                <div style="font-size:13px;font-weight:800;color:var(--accent)">Full Box</div>
                <div style="font-size:11px;color:var(--text-dim);margin-top:2px">
                  {{ $stagingProduct->items_per_box }} items / box
                </div>
              </div>
              <div style="font-size:13px;font-weight:700;font-family:var(--mono);color:var(--text-dim)">
                Default: {{ number_format($defaultBoxPrice) }} RWF
              </div>
            </div>
          </div>

          {{-- Unit Price section --}}
          <div class="whs-sm-price-section" style="margin-bottom:14px">
            <div class="whs-sm-price-label-row">
              <span class="whs-sm-price-section-label">Unit Price (per box)</span>
              <span class="whs-sm-modified-badge"
                    style="opacity:{{ $stagingPriceModified ? '1' : '0' }}">MODIFIED</span>
            </div>

            @if($settingAllowPriceOverride)
            <div class="whs-sm-price-wrap">
              <span class="whs-sm-price-prefix">RWF</span>
              {{-- wire:model.lazy: syncs on blur only, prevents per-keystroke re-renders --}}
              <input wire:model.lazy="stagingBoxPrice" type="number" min="1"
                     class="whs-sm-price-input {{ $stagingPriceModified ? 'modified' : '' }}">
            </div>
            {{-- Reason field: always in DOM, CSS transitions in/out --}}
            <div class="whs-sm-reason-wrap {{ $stagingPriceModified ? 'visible' : '' }}">
              <input wire:model.live="stagingPriceReason" type="text"
                     class="whs-sm-reason-input"
                     placeholder="Reason for price change (required)…">
            </div>
            @else
            <div class="whs-sm-price-locked">
              <span>{{ number_format($defaultBoxPrice) }} RWF</span>
              <span class="whs-sm-price-locked-hint">locked by owner</span>
            </div>
            @endif
          </div>

          {{-- Qty row --}}
          <div style="margin-bottom:16px">
            <div style="font-size:10px;font-weight:700;color:var(--text-dim);
                        text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px">
              Qty (boxes)
            </div>
            <div class="whs-sm-stepper">
              {{-- wire:click calls dedicated methods — $set with expressions does NOT work in Livewire 3 --}}
              <button type="button" class="whs-sm-step-btn"
                      wire:click="decrementStagingBoxes">&minus;</button>
              {{-- wire:model.lazy syncs only on blur — prevents per-keystroke re-renders --}}
              <input wire:model.lazy="stagingBoxes" type="number"
                     min="1" max="{{ $stagingProduct->box_count }}"
                     class="whs-sm-qty-input">
              <button type="button" class="whs-sm-step-btn"
                      wire:click="incrementStagingBoxes">+</button>
            </div>
          </div>

          {{-- Line total — identical structure to POS sm-total --}}
          <div class="whs-sm-total {{ $stagingPriceModified ? 'modified' : '' }}">
            <div>
              <div class="whs-sm-total-label">Line total</div>
              <div style="font-size:11px;color:var(--text-dim);margin-top:2px">
                {{ (int)$stagingBoxes }} × {{ number_format($effectivePrice) }} RWF
              </div>
            </div>
            <div>
              <span class="whs-sm-total-amount">{{ number_format($stagingTotal) }}</span>
              <span class="whs-sm-total-unit">RWF</span>
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
