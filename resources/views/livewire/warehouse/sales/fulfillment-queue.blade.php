<div style="font-family:var(--font)">
<style>
/* ── Fulfillment Queue ── fq- ──────────────────────────────────── */

/* Pending card */
.fq-card {
    background:var(--surface);border-radius:var(--r);
    box-shadow:var(--shadow-card);overflow:hidden;margin-bottom:10px;
    position:relative;transition:box-shadow var(--tr);
}
.fq-card:hover { box-shadow:var(--shadow-card-hover) }

.fq-urgency {
    position:absolute;left:0;top:0;bottom:0;width:3px;
    background:var(--border);
}
.fq-urgency.amber { background:var(--amber) }
.fq-urgency.red   { background:var(--red) }

.fq-body { padding:15px 18px 15px 22px }

/* Top row: ref + shop + age */
.fq-row-top {
    display:flex;align-items:baseline;gap:10px;margin-bottom:6px;flex-wrap:wrap;
}
.fq-ref {
    font-family:var(--mono);font-size:13px;font-weight:700;
    color:var(--text);flex-shrink:0;
}
.fq-shop {
    font-size:12px;color:var(--text-dim);flex:1;min-width:0;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.fq-age {
    font-size:11px;font-weight:600;padding:2px 8px;border-radius:5px;flex-shrink:0;
}
.fq-age-ok    { background:var(--surface2);color:var(--text-dim) }
.fq-age-amber { background:var(--amber-dim);color:var(--amber) }
.fq-age-red   { background:var(--red-dim);color:var(--red) }

/* Products — one per line, not a comma/dot-joined inline run */
.fq-prods {
    font-size:12px;color:var(--text);margin-bottom:13px;
    display:flex;flex-direction:column;gap:3px;
}
.fq-prod-item { line-height:1.4 }
.fq-prod-qty  { color:var(--text-dim);font-size:11px }

/* Action row */
.fq-act {
    display:flex;align-items:center;gap:10px;flex-wrap:wrap;
    padding-top:11px;border-top:1px solid var(--border);
}
.fq-via {
    flex:1;min-width:0;font-size:12px;color:var(--text-dim);
    display:flex;align-items:center;gap:5px;
}
.fq-via b { font-weight:600;color:var(--text) }
.fq-outstanding {
    font-size:11px;font-weight:600;color:var(--amber);
    padding:2px 7px;border-radius:4px;background:var(--amber-dim);
}
.fq-btn-dispatch {
    padding:7px 14px;border-radius:8px;cursor:pointer;
    background:var(--accent-dim);color:var(--accent);border:1.5px solid var(--accent-dim);
    font-size:12px;font-weight:600;font-family:var(--font);
    transition:all var(--tr);display:inline-flex;align-items:center;gap:5px;flex-shrink:0;
}
.fq-btn-dispatch:hover { background:var(--accent);color:#fff;border-color:var(--accent) }

/* Confirm strip — plain white continuation of the card (the card's own
   .fq-urgency stripe already supplies the left accent, so no second grey
   callout box), organized as header → 2-col field grid → footer.
   The name field and signature pad are both fixed-height bordered boxes
   (88px) so the two columns read as equal-weight, equal-height panels
   instead of a short input floating above empty space. */
.fq-confirm { background:var(--surface);border-top:1px solid var(--border);padding:16px 20px 14px }

.fq-confirm-head    { margin-bottom:14px }
.fq-confirm-msg     { font-size:13.5px;font-weight:600;color:var(--text) }
.fq-confirm-sub     { font-size:11.5px;color:var(--text-dim);margin-top:3px }

.fq-confirm-grid    { display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:14px }
.fq-confirm-field   { display:flex;flex-direction:column;gap:6px;min-width:0 }
.fq-confirm-label   { font-size:11px;font-weight:700;color:var(--text-sub);letter-spacing:.2px }
.fq-confirm-input-box {
    height:88px;border:1.5px solid var(--border);border-radius:9px;
    background:var(--surface);display:flex;align-items:center;padding:0 12px;
    box-sizing:border-box;transition:border-color var(--tr);
}
.fq-confirm-input-box:focus-within { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.fq-confirm-input,
.fq-confirm-input:focus {
    flex:1;min-width:0;width:100%;
    border:none !important;background:transparent;outline:none !important;box-shadow:none !important;
    appearance:none;-webkit-appearance:none;
    font-size:14px;color:var(--text);font-family:var(--font);padding:0;
}
.fq-confirm-error   { font-size:11px;color:var(--red);margin-top:5px }

.fq-confirm-foot {
    display:flex;justify-content:flex-end;gap:8px;
    padding-top:12px;border-top:1px solid var(--border);
}

/* Signature pad — same 88px height/border/radius as .fq-confirm-input-box
   so the two columns terminate flush; Clear is an icon overlaid in the
   corner rather than a text link below, so no extra line unbalances them. */
.fq-sig-wrap   { position:relative }
.fq-sig-canvas {
    width:100%;max-width:100%;height:88px;border:1.5px solid var(--border);
    border-radius:9px;background:var(--surface);touch-action:none;cursor:crosshair;display:block;
}
.fq-sig-clear {
    position:absolute;top:7px;right:7px;width:24px;height:24px;border-radius:6px;
    display:flex;align-items:center;justify-content:center;padding:0;
    background:var(--surface2);color:var(--text-dim);border:none;cursor:pointer;
    transition:all var(--tr);
}
.fq-sig-clear:hover { background:var(--red-dim);color:var(--red) }
.fq-btn-cancel {
    padding:8px 16px;border-radius:8px;border:1.5px solid var(--border);
    background:transparent;color:var(--text-dim);cursor:pointer;
    font-size:12.5px;font-weight:600;font-family:var(--font);transition:all var(--tr);
}
.fq-btn-cancel:hover { background:var(--surface2);color:var(--text) }
.fq-btn-yes {
    padding:8px 18px;border-radius:8px;border:none;
    background:var(--green);color:#fff;cursor:pointer;
    font-size:12.5px;font-weight:700;font-family:var(--font);
    transition:opacity var(--tr);display:inline-flex;align-items:center;gap:6px;
}
.fq-btn-yes:hover { opacity:.88 }

/* Consent recap — recipient name + signature shown again when an
   already-fulfilled sale is looked up */
.fq-result-card { margin-bottom:10px }
.fq-result-card .fq-result-fulfilled { margin-bottom:0; border-radius:10px 10px 0 0 }
.fq-consent-block {
    background:var(--green-dim);border-radius:0 0 10px 10px;
    padding:12px 18px 14px;display:flex;flex-wrap:wrap;gap:18px;align-items:flex-start;
    border-top:1px solid rgba(0,0,0,.06);
}
.fq-consent-item { display:flex;flex-direction:column;gap:5px }
.fq-consent-label {
    font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;
    color:var(--green);
}
.fq-consent-name { font-size:13px;font-weight:600;color:var(--text) }
.fq-consent-sig-img { max-width:180px;max-height:70px;border-radius:6px;background:#fff;border:1px solid var(--border) }

/* Empty state */
.fq-empty { padding:60px 24px;text-align:center }
.fq-empty-icon { color:var(--border);margin:0 auto 14px;display:block }
.fq-empty-title { font-size:15px;font-weight:700;color:var(--text);margin-bottom:4px }
.fq-empty-sub   { font-size:13px;color:var(--text-dim);max-width:300px;margin:0 auto }

/* History table */
.fq-tbl-wrap {
    background:var(--surface);border-radius:var(--r);
    box-shadow:var(--shadow-card);overflow:hidden;
}
.fq-tbl-scroll { overflow-x:auto;-webkit-overflow-scrolling:touch }
.fq-tbl {
    width:100%;border-collapse:collapse;min-width:680px;font-size:12px;
}
.fq-tbl thead tr { border-bottom:2px solid var(--border) }
.fq-tbl thead th {
    padding:9px 14px;font-size:10px;font-weight:700;text-transform:uppercase;
    letter-spacing:.6px;color:var(--text-dim);text-align:left;white-space:nowrap;
}
.fq-tbl thead th.r { text-align:right }
.fq-tbl tbody tr.fq-tbl-row {
    border-bottom:1px solid var(--border);background:var(--surface);
    cursor:pointer;transition:background var(--tr);
}
.fq-tbl tbody tr.fq-tbl-row:hover { background:var(--surface2) }
.fq-tbl tbody tr.fq-tbl-row.expanded { background:var(--surface2) }
.fq-tbl td { padding:11px 14px;vertical-align:middle;color:var(--text) }
.fq-tbl td.r { text-align:right }

.fq-tbl-ref  { font-family:var(--mono);font-size:12px;font-weight:700;color:var(--text) }
.fq-tbl-sub  { font-size:11px;color:var(--text-dim);margin-top:1px }

.fq-check {
    width:20px;height:20px;border-radius:50%;background:var(--green-dim);
    display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;
    vertical-align:middle;margin-right:8px;
}

/* Expanded row — white background + left accent border (never grey fill) */
.fq-tbl-exp { background:var(--surface) }
.fq-tbl-exp td { padding:0 }
.fq-exp-inner {
    padding:18px 22px;border-bottom:1px solid var(--border);border-left:3px solid var(--accent);
    display:grid;grid-template-columns:1fr 1fr;gap:24px;
}
.fq-exp-lbl {
    font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;
    color:var(--text-dim);margin-bottom:7px;
}
.fq-exp-row {
    display:flex;justify-content:space-between;align-items:baseline;gap:8px;
    padding:4px 0;border-bottom:1px solid var(--border);font-size:12px;
}
.fq-exp-row:last-child { border-bottom:none }
.fq-exp-key { color:var(--text-dim) }
.fq-exp-val { font-weight:600;color:var(--text);text-align:right }
.fq-box-chips  { margin-top:8px }
.fq-box-row {
    display:flex;justify-content:space-between;align-items:baseline;gap:10px;
    padding:5px 0;border-bottom:1px solid var(--border);font-size:12px;
}
.fq-box-row:last-child { border-bottom:none }
.fq-box-row-name  { color:var(--text) }
.fq-box-row-meta  { display:flex;align-items:baseline;gap:8px;flex-shrink:0 }
.fq-box-row-count { font-family:var(--mono);font-weight:700;color:var(--accent) }
.fq-box-row-sku   { font-size:10px;color:var(--text-dim);font-family:var(--mono) }

/* Section hint */
.fq-count-hint {
    font-size:11px;color:var(--text-dim);margin-bottom:12px;
}

/* Scan panel — primary lookup */
.fq-scan-panel {
    background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);
    padding:18px 22px;margin-bottom:14px;
}
.fq-scan-title {
    font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;
    color:var(--text-dim);margin-bottom:10px;display:flex;align-items:center;gap:7px;
}
.fq-scan-row { display:flex;gap:10px;align-items:center }
.fq-scan-input-wrap { position:relative;flex:1;min-width:0 }
.fq-scan-icon {
    position:absolute;left:13px;top:50%;transform:translateY(-50%);
    color:var(--text-dim);pointer-events:none;
}
.fq-scan-input {
    width:100%;padding:13px 14px 13px 38px;border:1.5px solid var(--border);
    border-radius:10px;font-size:16px;font-family:var(--mono);
    background:var(--surface);color:var(--text);outline:none;box-sizing:border-box;
    transition:border-color var(--tr);
}
.fq-scan-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.fq-scan-btn {
    padding:13px 20px;border-radius:10px;border:none;background:var(--accent);color:#fff;
    font-size:13px;font-weight:700;cursor:pointer;font-family:var(--font);
    box-shadow:0 3px 10px rgba(59,111,212,.25);transition:opacity var(--tr);
    white-space:nowrap;flex-shrink:0;
}
.fq-scan-btn:hover { opacity:.88 }
.fq-scan-toggle {
    margin-top:10px;background:none;border:none;padding:0;cursor:pointer;
    font-size:12px;font-weight:600;color:var(--text-dim);font-family:var(--font);
    text-decoration:underline;text-underline-offset:2px;
}
.fq-scan-toggle:hover { color:var(--accent) }

/* Period filter presets — queue list + history filters */
.fq-presets-row { display:flex;gap:4px;overflow-x:auto;-webkit-overflow-scrolling:touch;
                  padding:0 0 2px;scrollbar-width:none;flex-wrap:nowrap;min-width:0 }
.fq-presets-row::-webkit-scrollbar { display:none }
.fq-preset-btn  { padding:6px 12px;border-radius:7px;font-size:12px;font-weight:600;
                  border:1px solid var(--border);background:transparent;color:var(--text-dim);
                  cursor:pointer;white-space:nowrap;flex-shrink:0;transition:all var(--tr);
                  font-family:var(--font) }
.fq-preset-btn:hover  { background:var(--surface2);color:var(--text) }
.fq-preset-btn.active { background:var(--accent);color:#fff;border-color:var(--accent) }

/* Secondary search-by-customer panel */
.fq-cust-search { margin-top:12px;padding-top:12px;border-top:1px solid var(--border) }
.fq-cust-search-row { position:relative }
.fq-cust-search-input {
    width:100%;padding:10px 12px 10px 36px;border:1.5px solid var(--border);
    border-radius:9px;font-size:14px;background:var(--surface);color:var(--text);
    outline:none;box-sizing:border-box;font-family:var(--font);
    transition:border-color var(--tr);
}
.fq-cust-search-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }

/* Lookup result rows — fulfilled / cancelled / not found */
.fq-result-info {
    display:flex;align-items:center;gap:12px;padding:14px 18px;border-radius:10px;
    margin-bottom:10px;font-size:13px;
}
.fq-result-fulfilled { background:var(--green-dim);color:var(--green) }
.fq-result-cancelled { background:var(--red-dim);color:var(--red) }
.fq-result-notfound {
    background:var(--surface2);color:var(--text-dim);
    display:flex;align-items:center;gap:10px;padding:14px 18px;border-radius:10px;margin-bottom:10px;font-size:13px;
}
.fq-result-ref { font-family:var(--mono);font-weight:700 }
.fq-result-clear {
    margin-left:auto;background:none;border:none;cursor:pointer;
    color:inherit;opacity:.7;font-size:12px;font-family:var(--font);text-decoration:underline;
}
.fq-result-clear:hover { opacity:1 }

/* Responsive */
@media(max-width:900px) {
    .fq-exp-inner { grid-template-columns:1fr }
}
@media(max-width:600px) {
    .fq-row-top { flex-direction:column;align-items:flex-start;gap:4px }
    .fq-act { flex-direction:column;align-items:flex-start }
    .fq-btn-dispatch { width:100%;justify-content:center }
    .fq-confirm-grid { grid-template-columns:1fr }
    .fq-confirm-foot { flex-direction:column-reverse }
    .fq-confirm-foot .fq-btn-cancel,
    .fq-confirm-foot .fq-btn-yes { width:100%;justify-content:center }
    .fq-scan-row { flex-direction:column;align-items:stretch }
    .fq-scan-btn { width:100% }
}

/* ── Section tabs (Pending / History) ── */
.fq-tabs { display:grid;grid-template-columns:repeat(2,1fr);
           background:var(--surface);box-shadow:var(--shadow-card);
           border-radius:var(--r);margin-bottom:18px;overflow:hidden }
.fq-tab  { display:flex;align-items:center;justify-content:center;gap:6px;
           padding:13px 10px;border:none;border-radius:0;
           border-bottom:2.5px solid transparent;border-right:1px solid var(--border);
           cursor:pointer;font-size:13px;font-weight:600;font-family:var(--font);
           background:transparent;color:var(--text-dim);transition:all var(--tr);white-space:nowrap }
.fq-tab:last-child { border-right:none }
.fq-tab:hover  { background:var(--surface2);color:var(--text);border-bottom-color:var(--border-hi) }
.fq-tab.active { background:var(--accent-dim);color:var(--accent);border-bottom-color:var(--accent) }
@media(max-width:480px) {
    .fq-tab svg { display:none }
}
</style>

<div class="fq-tabs">
    <button type="button" class="fq-tab {{ $activeTab === 'pending' ? 'active' : '' }}" wire:click="setTab('pending')">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        {{ $dispatchMethod === 'scan' ? 'Scan & Dispatch' : 'Pending Pickups' }}
    </button>
    <button type="button" class="fq-tab {{ $activeTab === 'history' ? 'active' : '' }}" wire:click="setTab('history')">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Dispatch History
    </button>
</div>

@if($activeTab === 'pending')
@if($dispatchMethod === 'scan')
{{-- ── Receipt lookup (primary scan, secondary name/phone fallback) ──── --}}
<div class="fq-scan-panel">
    <div class="fq-scan-title">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2M7 12h10M7 8h4M7 16h7"/>
        </svg>
        Scan Receipt
    </div>
    <div class="fq-scan-row">
        <div class="fq-scan-input-wrap">
            <svg class="fq-scan-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M2 3h2v18H2zM7 3h1v18H7zM11 3h2v18h-2zM15 3h1v18h-1zM18 3h2v18h-2z"/>
            </svg>
            <input type="text" class="fq-scan-input" wire:model="receiptCode"
                   wire:keydown.enter="scanReceipt"
                   placeholder="Scan or type pickup code (e.g. 7K2-9XP-4QW-1ZM)"
                   autocomplete="off" maxlength="15" data-code-input>
        </div>
        <button class="fq-scan-btn" wire:click="scanReceipt">Look Up</button>
    </div>
    <button class="fq-scan-toggle" wire:click="toggleCustomerSearch">
        {{ $showCustomerSearch ? 'Hide name/phone search' : "Can't scan the receipt? Search by name or phone" }}
    </button>

    @if($showCustomerSearch)
    <div class="fq-cust-search">
        <div class="fq-cust-search-row">
            <svg class="fq-scan-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="left:11px">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" class="fq-cust-search-input" wire:model.live.debounce.300ms="customerSearch"
                   placeholder="Customer name or phone…" autocomplete="off">
        </div>
    </div>
    @endif
</div>

{{-- ── Lookup results ──────────────────────────────────────────────── --}}
@if($scannedCode)
    @if($scannedSale)
        @if($scannedSale->fulfillment_status === 'pending')
            @include('livewire.warehouse.sales.partials.pending-row', ['sale' => $scannedSale])
        @elseif($scannedSale->fulfillment_status === 'fulfilled')
            <div class="fq-result-card">
                <div class="fq-result-info fq-result-fulfilled">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    <span><span class="fq-result-ref">{{ $scannedSale->sale_number }}</span> — already dispatched
                        to {{ $scannedSale->fulfillment_recipient_name ?? $scannedSale->fulfillmentTransporter?->name ?? 'customer' }}
                        at {{ local_time($scannedSale->fulfillment_confirmed_at)?->format('d M, H:i') }}.</span>
                    <button class="fq-result-clear" wire:click="clearScan">Clear</button>
                </div>
                @if($scannedSale->fulfillment_recipient_name || $scannedSale->fulfillment_signature)
                <div class="fq-consent-block">
                    @if($scannedSale->fulfillment_recipient_name)
                    <div class="fq-consent-item">
                        <span class="fq-consent-label">Picked up by</span>
                        <span class="fq-consent-name">{{ $scannedSale->fulfillment_recipient_name }}</span>
                    </div>
                    @endif
                    @if($scannedSale->fulfillment_signature)
                    <div class="fq-consent-item">
                        <span class="fq-consent-label">Signature on file</span>
                        <img src="{{ $scannedSale->fulfillment_signature }}" alt="Customer signature" class="fq-consent-sig-img">
                    </div>
                    @endif
                </div>
                @endif
            </div>
        @else
            <div class="fq-result-info fq-result-cancelled">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                <span><span class="fq-result-ref">{{ $scannedSale->sale_number }}</span> — this sale was cancelled. Do not dispatch.</span>
                <button class="fq-result-clear" wire:click="clearScan">Clear</button>
            </div>
        @endif
    @else
        <div class="fq-result-notfound">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <span>No warehouse order found for <span class="fq-result-ref">{{ $scannedCode }}</span> at {{ $warehouseName }}.</span>
            <button class="fq-result-clear" wire:click="clearScan">Clear</button>
        </div>
    @endif
@elseif($showCustomerSearch && $customerSearch !== '')
    @if($customerSearchResults->isEmpty())
        <div class="fq-result-notfound">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <span>No matching orders for "{{ $customerSearch }}" at {{ $warehouseName }}.</span>
        </div>
    @else
        @foreach($customerSearchResults as $csale)
            @if($csale->fulfillment_status === 'pending')
                @include('livewire.warehouse.sales.partials.pending-row', ['sale' => $csale])
            @elseif($csale->fulfillment_status === 'fulfilled')
                <div class="fq-result-card">
                    <div class="fq-result-info fq-result-fulfilled">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        <span><span class="fq-result-ref">{{ $csale->sale_number }}</span> — already dispatched
                            to {{ $csale->fulfillment_recipient_name ?? $csale->fulfillmentTransporter?->name ?? 'customer' }}
                            at {{ local_time($csale->fulfillment_confirmed_at)?->format('d M, H:i') }}.</span>
                    </div>
                    @if($csale->fulfillment_recipient_name || $csale->fulfillment_signature)
                    <div class="fq-consent-block">
                        @if($csale->fulfillment_recipient_name)
                        <div class="fq-consent-item">
                            <span class="fq-consent-label">Picked up by</span>
                            <span class="fq-consent-name">{{ $csale->fulfillment_recipient_name }}</span>
                        </div>
                        @endif
                        @if($csale->fulfillment_signature)
                        <div class="fq-consent-item">
                            <span class="fq-consent-label">Signature on file</span>
                            <img src="{{ $csale->fulfillment_signature }}" alt="Customer signature" class="fq-consent-sig-img">
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            @else
                <div class="fq-result-info fq-result-cancelled">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    <span><span class="fq-result-ref">{{ $csale->sale_number }}</span> — this sale was cancelled. Do not dispatch.</span>
                </div>
            @endif
        @endforeach
    @endif
@endif
@else
{{-- ── Queue mode: browsable list of orders awaiting pickup ──────────── --}}
<div class="fq-scan-panel">
    <div class="fq-scan-title">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        Filter Pending Pickups
    </div>
    <div class="fq-presets-row">
        @foreach(['all' => 'All', 'today' => 'Today', 'yesterday' => 'Yesterday', 'this_week' => 'This Week', 'this_month' => 'This Month', 'last_30' => 'Last 30 Days'] as $key => $label)
            <button class="fq-preset-btn {{ $queueDateFilter === $key ? 'active' : '' }}"
                    wire:click="$set('queueDateFilter', '{{ $key }}')">{{ $label }}</button>
        @endforeach
    </div>
    <div class="fq-cust-search" style="margin-top:12px">
        <div class="fq-cust-search-row">
            <svg class="fq-scan-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="left:11px">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" class="fq-cust-search-input" wire:model.live.debounce.300ms="queueCustomerSearch"
                   placeholder="Filter by customer name or phone…" autocomplete="off">
        </div>
    </div>
</div>

@if($pendingSales->isEmpty())
<div class="fq-empty">
    <svg class="fq-empty-icon" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
    </svg>
    <div class="fq-empty-title">Nothing pending pickup right now</div>
    <div class="fq-empty-sub">Warehouse-direct orders will appear here as soon as they're sold, and stay until dispatched.</div>
</div>
@else
<div class="fq-count-hint">{{ $pendingSales->count() }} {{ $pendingSales->count() === 1 ? 'order' : 'orders' }} awaiting pickup — newest first</div>
@foreach($pendingSales as $sale)
    @include('livewire.warehouse.sales.partials.pending-row', ['sale' => $sale, 'source' => 'queue'])
@endforeach
@endif
@endif
@else
{{-- ── History — filterable audit trail for both dispatch methods ────── --}}
<div class="fq-scan-panel" style="padding:14px 18px">
    <div class="fq-presets-row" style="padding:0;border-bottom:none">
        @foreach(['all' => 'All', 'today' => 'Today', 'yesterday' => 'Yesterday', 'this_week' => 'This Week', 'this_month' => 'This Month', 'last_30' => 'Last 30 Days'] as $key => $label)
            <button class="fq-preset-btn {{ $historyDateFilter === $key ? 'active' : '' }}"
                    wire:click="$set('historyDateFilter', '{{ $key }}')">{{ $label }}</button>
        @endforeach
    </div>
    <div class="fq-cust-search" style="margin-top:10px;padding-top:10px">
        <div class="fq-cust-search-row">
            <svg class="fq-scan-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="left:11px">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" class="fq-cust-search-input" wire:model.live.debounce.300ms="historyCustomerSearch"
                   placeholder="Filter history by customer name or phone…" autocomplete="off">
        </div>
    </div>
</div>

@if($fulfilledHistory->isEmpty())
<div class="fq-empty">
    <svg class="fq-empty-icon" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10"/><polyline points="20 6 9 17 4 12"/>
    </svg>
    <div class="fq-empty-title">{{ $historyDateFilter !== 'all' || $historyCustomerSearch !== '' ? 'No dispatches match this filter' : 'No dispatches yet' }}</div>
    <div class="fq-empty-sub">
        @if($historyDateFilter !== 'all' || $historyCustomerSearch !== '')
            Try a different period or clear the customer search.
        @else
            Fulfilled orders will appear here with a full audit trail.
        @endif
    </div>
</div>
@else

<div class="fq-count-hint">{{ $fulfilledHistory->count() }} {{ $fulfilledHistory->count() === 1 ? 'dispatch' : 'dispatches' }} — tap a row to view audit detail</div>

<div class="fq-tbl-wrap">
<div class="fq-tbl-scroll">
<table class="fq-tbl">
    <thead>
        <tr>
            <th>Sale ref</th>
            <th>Shop</th>
            <th>Products</th>
            <th>Via</th>
            <th class="r">Boxes</th>
            <th>Dispatched</th>
        </tr>
    </thead>
    <tbody>
    @foreach($fulfilledHistory as $sale)
    @php
        $histWh     = $sale->items->filter(fn($i) => $i->box?->location_type?->value === 'warehouse');
        $histByProd = $histWh->groupBy(fn($i) => $i->product_id)->map(fn($g) => [
            'name'  => $g->first()->product?->name ?? '—',
            'sku'   => $g->first()->product?->sku ?? '',
            'boxes' => $g->count(),
        ]);
        $histPaid = $sale->payments->sum('amount') >= $sale->total;
        $histOpen = $expandedHistoryId === $sale->id;
    @endphp

    <tr class="fq-tbl-row {{ $histOpen ? 'expanded' : '' }}"
        wire:click="toggleHistory({{ $sale->id }})"
        wire:key="hrow-{{ $sale->id }}">
        <td>
            <span class="fq-check">
                <svg width="10" height="10" fill="none" stroke="var(--green)" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </span>
            <span class="fq-tbl-ref">{{ $sale->sale_number }}</span>
        </td>
        <td>
            <div style="font-size:12px;font-weight:600;color:var(--text)">{{ $sale->shop?->name ?? '—' }}</div>
            @if($sale->customer_name)
            <div class="fq-tbl-sub">{{ $sale->customer_name }}</div>
            @endif
        </td>
        <td>
            <div style="font-size:12px;color:var(--text)">
                {{ $histByProd->keys()->map(fn($k) => $histByProd[$k]['name'])->implode(', ') }}
            </div>
            <div class="fq-tbl-sub">{{ $histWh->count() }} {{ $histWh->count() === 1 ? 'box' : 'boxes' }}</div>
        </td>
        <td style="font-size:12px;color:var(--text-dim)">
            @if($sale->fulfillment_method === 'transporter')
                {{ $sale->fulfillmentTransporter?->name ?? 'Transporter' }}
            @else
                Customer Pickup
            @endif
        </td>
        <td class="r" style="font-family:var(--mono);font-weight:700">{{ $histWh->count() }}</td>
        <td>
            @if($sale->fulfillment_confirmed_at)
            <div style="font-size:12px;font-weight:600;color:var(--text)">{{ local_time($sale->fulfillment_confirmed_at)->format('H:i') }}</div>
            <div class="fq-tbl-sub">{{ local_time($sale->fulfillment_confirmed_at)->format('d M Y') }}</div>
            @else
            <span class="fq-tbl-sub">—</span>
            @endif
        </td>
    </tr>

    @if($histOpen)
    <tr class="fq-tbl-exp" wire:key="hexp-{{ $sale->id }}">
        <td colspan="6">
            <div class="fq-exp-inner">

                {{-- Dispatch record --}}
                <div>
                    <div class="fq-exp-lbl">Dispatch record</div>
                    <div class="fq-exp-row">
                        <span class="fq-exp-key">Confirmed by</span>
                        <span class="fq-exp-val">{{ $sale->fulfillmentConfirmedBy?->name ?? '—' }}</span>
                    </div>
                    <div class="fq-exp-row">
                        <span class="fq-exp-key">Dispatched at</span>
                        <span class="fq-exp-val">{{ local_time($sale->fulfillment_confirmed_at)?->format('d M Y, H:i') ?? '—' }}</span>
                    </div>
                    <div class="fq-exp-row">
                        <span class="fq-exp-key">Sold by</span>
                        <span class="fq-exp-val">{{ $sale->soldBy?->name ?? '—' }}</span>
                    </div>
                    @if($sale->customer_name)
                    <div class="fq-exp-row">
                        <span class="fq-exp-key">Customer</span>
                        <span class="fq-exp-val">
                            {{ $sale->customer_name }}
                            @if($sale->customer_phone)
                                <span style="font-family:var(--mono);color:var(--text-dim);font-weight:400"> · {{ $sale->customer_phone }}</span>
                            @endif
                        </span>
                    </div>
                    @endif
                    @if($sale->fulfillment_recipient_name)
                    <div class="fq-exp-row">
                        <span class="fq-exp-key">Picked up by</span>
                        <span class="fq-exp-val">{{ $sale->fulfillment_recipient_name }}</span>
                    </div>
                    @endif
                    @if($sale->fulfillment_signature)
                    <div class="fq-exp-row" style="flex-direction:column;align-items:flex-start;gap:6px">
                        <span class="fq-exp-key">Signature</span>
                        <img src="{{ $sale->fulfillment_signature }}" alt="Customer signature"
                             style="max-width:220px;border:1px solid var(--border);border-radius:6px;background:#fff">
                    </div>
                    @endif
                    <div class="fq-exp-row">
                        <span class="fq-exp-key">Payment</span>
                        <span class="fq-exp-val" style="color:{{ $histPaid ? 'var(--green)' : 'var(--amber)' }}">
                            {{ $histPaid ? 'Fully paid' : 'Partial — balance outstanding' }}
                        </span>
                    </div>
                    @if($sale->fulfillment_notes)
                    <div class="fq-exp-row">
                        <span class="fq-exp-key">Note</span>
                        <span class="fq-exp-val" style="color:var(--text-dim);font-weight:400;font-style:italic">{{ $sale->fulfillment_notes }}</span>
                    </div>
                    @endif
                </div>

                {{-- Boxes dispatched --}}
                <div>
                    <div class="fq-exp-lbl">Boxes dispatched from {{ $warehouseName }}</div>
                    <div class="fq-box-chips">
                        @foreach($histByProd as $row)
                        <div class="fq-box-row">
                            <span class="fq-box-row-name">{{ $row['name'] }}</span>
                            <span class="fq-box-row-meta">
                                @if($row['sku'])
                                    <span class="fq-box-row-sku">{{ $row['sku'] }}</span>
                                @endif
                                <span class="fq-box-row-count">&times;{{ $row['boxes'] }}</span>
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </td>
    </tr>
    @endif

    @endforeach
    </tbody>
</table>
</div>
</div>

@endif
@endif

</div>

{{--
    Signature pad init lives here (the component's root view) rather than in
    partials/pending-row.blade.php. @script inside a Blade partial rendered
    via @include broke Livewire's AJAX response — the update response body
    came back with the whole component's rendered HTML prepended raw before
    the JSON envelope, which the client then failed to JSON.parse. Moving
    @script to the root view file fixed it. _initSignaturePads() queries
    [data-sig-canvas] globally, so it doesn't need to live next to the
    canvas markup itself.
--}}
@script
<script>
    window._initSignaturePads = window._initSignaturePads || function () {
        document.querySelectorAll('[data-sig-canvas]:not([data-sig-ready])').forEach(function (canvas) {
            canvas.dataset.sigReady = '1';

            const ctx = canvas.getContext('2d');
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = (getComputedStyle(document.documentElement).getPropertyValue('--text').trim()) || '#1a1f36';

            let drawing = false;
            let last = null;

            function pos(e) {
                const rect = canvas.getBoundingClientRect();
                return {
                    x: (e.clientX - rect.left) * (canvas.width / rect.width),
                    y: (e.clientY - rect.top) * (canvas.height / rect.height),
                };
            }

            function sync() {
                const w = window.Livewire.find(canvas.closest('[wire\\:id]').getAttribute('wire:id'));
                if (w) w.set('signatureData', canvas.toDataURL('image/png'));
            }

            canvas.addEventListener('pointerdown', function (e) {
                drawing = true;
                last = pos(e);
                canvas.setPointerCapture(e.pointerId);
            });
            canvas.addEventListener('pointermove', function (e) {
                if (!drawing) return;
                const p = pos(e);
                ctx.beginPath();
                ctx.moveTo(last.x, last.y);
                ctx.lineTo(p.x, p.y);
                ctx.stroke();
                last = p;
            });
            canvas.addEventListener('pointerup', function () {
                if (!drawing) return;
                drawing = false;
                sync();
            });
            canvas.addEventListener('pointerleave', function () {
                if (drawing) { drawing = false; sync(); }
            });

            const clearBtn = document.querySelector('[data-sig-clear="' + canvas.id + '"]');
            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    const w = window.Livewire.find(canvas.closest('[wire\\:id]').getAttribute('wire:id'));
                    if (w) w.set('signatureData', '');
                });
            }
        });
    };

    window._initSignaturePads();

    // Cosmetic only: groups the code into 3-char blocks with dashes as the
    // warehouse manager types, matching the receipt's printed format.
    // scanReceipt() already strips non-alphanumeric characters and
    // uppercases before matching, so dashes/case here don't affect lookup.
    window._initCodeInput = window._initCodeInput || function () {
        document.querySelectorAll('[data-code-input]:not([data-code-ready])').forEach(function (input) {
            input.dataset.codeReady = '1';
            input.addEventListener('input', function () {
                const raw = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 12);
                input.value = raw.match(/.{1,3}/g)?.join('-') ?? raw;
            });
        });
    };

    window._initCodeInput();

    if (!window._sigPadsHookRegistered) {
        window._sigPadsHookRegistered = true;
        Livewire.hook('commit', ({ succeed }) => {
            succeed(() => {
                requestAnimationFrame(window._initSignaturePads);
                requestAnimationFrame(window._initCodeInput);
            });
        });
    }
</script>
@endscript
