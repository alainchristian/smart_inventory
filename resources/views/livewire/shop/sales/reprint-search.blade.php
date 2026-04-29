<div>
<style>
/* ── Receipt List Responsive ── */
.rl-table       { display:block; }
.rl-mobile-list { display:none; }
.rl-header-row,
.rl-data-row    {
    display:grid;
    grid-template-columns:130px 1fr 1fr 90px 100px 120px;
    gap:8px;
    padding:10px 16px;
    align-items:center;
}
.rl-header-row {
    padding:9px 16px;
    background:var(--surface2);
    border-bottom:1px solid var(--border);
}
.rl-col-label {
    font-size:10px;font-weight:700;text-transform:uppercase;
    letter-spacing:.5px;color:var(--text-dim);
}
.rl-data-row   { border-bottom:1px solid var(--border);background:var(--surface); }
.rl-data-row:last-child { border-bottom:none; }
.rl-act-btn {
    display:inline-flex;align-items:center;gap:4px;
    padding:5px 10px;border-radius:8px;font-size:11px;font-weight:700;
    text-decoration:none;cursor:pointer;border:1.5px solid var(--border);
    background:var(--surface2);color:var(--text-dim);
    transition:all .15s;
}
.rl-act-btn:hover  { border-color:var(--accent);color:var(--accent); }
.rl-act-btn.print:hover { border-color:var(--text-dim);color:var(--text); }

/* Mobile card layout */
@media(max-width:680px){
    .rl-table       { display:none; }
    .rl-mobile-list { display:flex;flex-direction:column;gap:0; }
    .rl-mobile-card {
        padding:12px 14px;border-bottom:1px solid var(--border);
        background:var(--surface);
    }
    .rl-mobile-card:last-child { border-bottom:none; }
    .rl-mc-top  { display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:6px; }
    .rl-mc-num  { font-size:13px;font-weight:700;font-family:monospace;color:var(--text); }
    .rl-mc-amt  { font-size:14px;font-weight:800;font-family:monospace;color:var(--accent);white-space:nowrap; }
    .rl-mc-mid  { font-size:11px;color:var(--text-dim);margin-bottom:8px; }
    .rl-mc-bot  { display:flex;gap:8px;align-items:center;justify-content:space-between; }
    .rl-mc-date { font-size:10px;color:var(--text-dim); }
    .rl-mc-actions { display:flex;gap:6px; }
}

/* ── Receipt View Modal ── */
.rm-overlay {
    position:fixed;inset:0;background:rgba(0,0,0,.55);
    z-index:9000;display:flex;align-items:center;justify-content:center;
    padding:16px;
}
.rm-panel {
    background:var(--surface);border-radius:18px;
    width:100%;max-width:480px;max-height:90vh;
    display:flex;flex-direction:column;
    box-shadow:0 24px 80px rgba(0,0,0,.45);overflow:hidden;
}
.rm-header {
    background:linear-gradient(135deg,#16a34a,#15803d);
    padding:20px 22px 18px;color:#fff;
    display:flex;align-items:flex-start;justify-content:space-between;gap:12px;
    flex-shrink:0;
}
.rm-close-btn {
    background:rgba(255,255,255,.15);border:none;color:#fff;
    width:28px;height:28px;border-radius:50%;cursor:pointer;
    display:flex;align-items:center;justify-content:center;
    font-size:16px;flex-shrink:0;transition:background .15s;
}
.rm-close-btn:hover { background:rgba(255,255,255,.28); }
.rm-body { overflow-y:auto;flex:1;padding:18px 22px; }
.rm-section { margin-bottom:16px; }
.rm-section-title {
    font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;
    color:var(--text-dim);margin-bottom:8px;
}
.rm-item-row {
    display:flex;justify-content:space-between;align-items:baseline;
    gap:8px;padding:5px 0;border-bottom:1px solid var(--border);
    font-size:12px;
}
.rm-item-row:last-child { border-bottom:none; }
.rm-item-name  { color:var(--text);flex:1; }
.rm-item-qty   { color:var(--text-dim);font-size:11px;white-space:nowrap; }
.rm-item-price { font-weight:700;font-family:monospace;color:var(--text);white-space:nowrap; }
.rm-pay-row {
    display:flex;justify-content:space-between;padding:4px 0;
    font-size:12px;color:var(--text-dim);
}
.rm-pay-row.total {
    border-top:1.5px solid var(--border);margin-top:6px;padding-top:8px;
    font-size:14px;font-weight:800;color:var(--text);
}
.rm-footer {
    padding:14px 22px;border-top:1px solid var(--border);
    display:grid;grid-template-columns:1fr 1fr;gap:10px;flex-shrink:0;
    background:var(--surface2);
}
.rm-footer-btn {
    display:flex;align-items:center;justify-content:center;gap:6px;
    padding:10px;border-radius:10px;font-size:12px;font-weight:700;
    cursor:pointer;text-decoration:none;border:none;transition:all .15s;
}
.rm-footer-btn.print {
    background:var(--surface);border:1.5px solid var(--border);color:var(--text);
}
.rm-footer-btn.print:hover { border-color:var(--accent);color:var(--accent); }
.rm-footer-btn.close-btn {
    background:var(--surface);border:1.5px solid var(--border);color:var(--text-dim);
}
.rm-footer-btn.close-btn:hover { border-color:var(--red);color:var(--red); }

/* Filter bar */
.rl-filters {
    display:grid;
    grid-template-columns:1fr auto auto;
    align-items:flex-end;gap:10px;margin-bottom:18px;
}
.rl-filter-group { display:flex;flex-direction:column;gap:5px; }
.rl-filter-label {
    font-size:10px;font-weight:700;text-transform:uppercase;
    letter-spacing:.5px;color:var(--text-dim);
}
.rl-filter-input {
    padding:8px 10px;border:1.5px solid var(--border);border-radius:10px;
    background:var(--surface);color:var(--text);font-size:13px;outline:none;
    transition:border-color .15s;width:100%;box-sizing:border-box;
}
.rl-filter-input:focus { border-color:var(--accent); }
.rl-search-wrap { position:relative; }
.rl-search-icon { position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--text-dim); }
.rl-search-input { padding-left:30px !important; }
@media(max-width:600px){
    .rl-filters {
        grid-template-columns:1fr 1fr;
        grid-template-rows:auto auto;
    }
    .rl-filter-group:first-child {
        grid-column:1 / -1;
    }
}
</style>

{{-- Filters --}}
<div class="rl-filters">
    <div class="rl-filter-group">
        <label class="rl-filter-label">Search</label>
        <div class="rl-search-wrap">
            <svg class="rl-search-icon" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Sale #, customer name, phone, product…"
                   class="rl-filter-input rl-search-input">
        </div>
    </div>
    <div class="rl-filter-group">
        <label class="rl-filter-label">From</label>
        <input wire:model.live="dateFrom" type="date" class="rl-filter-input">
    </div>
    <div class="rl-filter-group">
        <label class="rl-filter-label">To</label>
        <input wire:model.live="dateTo" type="date" class="rl-filter-input">
    </div>
</div>

{{-- Results container --}}
<div style="border:1px solid var(--border);border-radius:14px;overflow:hidden;">

    {{-- Desktop table --}}
    <div class="rl-table">
        <div class="rl-header-row">
            <span class="rl-col-label">Receipt #</span>
            <span class="rl-col-label">Customer</span>
            <span class="rl-col-label">Items</span>
            <span class="rl-col-label" style="text-align:right">Total</span>
            <span class="rl-col-label">Date</span>
            <span></span>
        </div>

        @forelse($sales as $sale)
        <div class="rl-data-row" wire:key="sale-{{ $sale->id }}">

            <div>
                <div style="font-size:12px;font-weight:700;font-family:monospace;color:var(--text);">{{ $sale->sale_number }}</div>
                @if($sale->shop)
                    <div style="font-size:10px;color:var(--text-dim);">{{ $sale->shop->name }}</div>
                @endif
            </div>

            <div>
                @if($sale->customer_name)
                    <div style="font-size:12px;color:var(--text);">{{ $sale->customer_name }}</div>
                    @if($sale->customer_phone)
                        <div style="font-size:10px;font-family:monospace;color:var(--text-dim);">{{ $sale->customer_phone }}</div>
                    @endif
                @else
                    <span style="font-size:11px;color:var(--text-dim);">Walk-in</span>
                @endif
            </div>

            <div style="font-size:11px;color:var(--text-dim);">
                @php
                    $grouped = $sale->items->groupBy('product_id');
                    $names   = $grouped->map(fn($g) => $g->first()->product->name ?? '?')->take(2)->values();
                    $extra   = $grouped->count() - 2;
                @endphp
                {{ $names->implode(', ') }}{{ $extra > 0 ? ' +' . $extra . ' more' : '' }}
            </div>

            <div style="font-size:12px;font-weight:700;font-family:monospace;color:var(--accent);text-align:right;">
                {{ number_format($sale->total) }}
            </div>

            <div style="font-size:11px;color:var(--text-dim);">
                {{ ($sale->sale_date ?? $sale->created_at)->format('d M Y') }}<br>
                <span style="color:var(--text-dim);font-size:10px;">{{ ($sale->sale_date ?? $sale->created_at)->format('H:i') }}</span>
            </div>

            <div style="display:flex;gap:6px;align-items:center;">
                <button wire:click="viewSale({{ $sale->id }})" class="rl-act-btn" title="View receipt">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                    View
                </button>
                <a href="{{ route('shop.receipt.print', $sale->id) }}" target="_blank" class="rl-act-btn print" title="Print receipt">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>
                    </svg>
                    Print
                </a>
            </div>

        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-dim);font-size:13px;">
            No receipts found. Try adjusting the filters.
        </div>
        @endforelse
    </div>

    {{-- Mobile card list --}}
    <div class="rl-mobile-list">
        @forelse($sales as $sale)
        <div class="rl-mobile-card" wire:key="m-sale-{{ $sale->id }}">
            <div class="rl-mc-top">
                <div>
                    <div class="rl-mc-num">{{ $sale->sale_number }}</div>
                    @if($sale->shop)
                        <div style="font-size:10px;color:var(--text-dim);margin-top:1px;">{{ $sale->shop->name }}</div>
                    @endif
                </div>
                <div class="rl-mc-amt">{{ number_format($sale->total) }} <span style="font-size:10px;font-weight:500;color:var(--text-dim);">RWF</span></div>
            </div>
            <div class="rl-mc-mid">
                @if($sale->customer_name)
                    {{ $sale->customer_name }}@if($sale->customer_phone) · {{ $sale->customer_phone }}@endif
                @else
                    Walk-in customer
                @endif
                —
                @php
                    $grouped = $sale->items->groupBy('product_id');
                    $names   = $grouped->map(fn($g) => $g->first()->product->name ?? '?')->take(2)->values();
                    $extra   = $grouped->count() - 2;
                @endphp
                {{ $names->implode(', ') }}{{ $extra > 0 ? ' +' . $extra . ' more' : '' }}
            </div>
            <div class="rl-mc-bot">
                <div class="rl-mc-date">
                    {{ ($sale->sale_date ?? $sale->created_at)->format('d M Y, H:i') }}
                </div>
                <div class="rl-mc-actions">
                    <button wire:click="viewSale({{ $sale->id }})" class="rl-act-btn">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                        View
                    </button>
                    <a href="{{ route('shop.receipt.print', $sale->id) }}" target="_blank" class="rl-act-btn print">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>
                        </svg>
                        Print
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-dim);font-size:13px;">
            No receipts found. Try adjusting the filters.
        </div>
        @endforelse
    </div>

</div>

{{-- Pagination --}}
@if($sales->hasPages())
    <div class="mt-4">{{ $sales->links() }}</div>
@endif

{{-- Receipt View Modal --}}
@if($showReceiptModal && $selectedSale)
<div class="rm-overlay" wire:click.self="closeReceiptModal">
    <div class="rm-panel">

        {{-- Green header --}}
        <div class="rm-header">
            <div>
                <div style="font-size:11px;opacity:.8;margin-bottom:2px;text-transform:uppercase;letter-spacing:.5px;">Receipt</div>
                <div style="font-size:18px;font-weight:800;font-family:monospace;">{{ $selectedSale->sale_number }}</div>
                @if($selectedSale->shop)
                    <div style="font-size:12px;opacity:.75;margin-top:3px;">{{ $selectedSale->shop->name }}</div>
                @endif
            </div>
            <div style="text-align:right;display:flex;flex-direction:column;align-items:flex-end;gap:8px;">
                <button class="rm-close-btn" wire:click="closeReceiptModal">✕</button>
                <div style="font-size:11px;opacity:.75;">{{ ($selectedSale->sale_date ?? $selectedSale->created_at)->format('d M Y, H:i') }}</div>
            </div>
        </div>

        {{-- Body --}}
        <div class="rm-body">

            {{-- Customer --}}
            @if($selectedSale->customer_name || $selectedSale->customer_phone)
            <div class="rm-section">
                <div class="rm-section-title">Customer</div>
                <div style="font-size:13px;color:var(--text);font-weight:600;">{{ $selectedSale->customer_name ?? '—' }}</div>
                @if($selectedSale->customer_phone)
                    <div style="font-size:11px;color:var(--text-dim);font-family:monospace;">{{ $selectedSale->customer_phone }}</div>
                @endif
            </div>
            @endif

            {{-- Served by --}}
            @if($selectedSale->soldBy)
            <div style="font-size:11px;color:var(--text-dim);margin-bottom:14px;">
                Served by <strong style="color:var(--text-dim);">{{ $selectedSale->soldBy->name }}</strong>
            </div>
            @endif

            {{-- Items --}}
            <div class="rm-section">
                <div class="rm-section-title">Items</div>
                @foreach($selectedSale->items->groupBy('product_id') as $productId => $lines)
                    @php
                        $line      = $lines->first();
                        $totalQty  = $lines->sum('quantity_sold');
                        $lineTotal = $lines->sum('line_total');
                        $unitPrice = $totalQty > 0 ? round($lineTotal / $totalQty) : 0;
                    @endphp
                    <div class="rm-item-row">
                        <div class="rm-item-name">{{ $line->product->name ?? '?' }}</div>
                        <div class="rm-item-qty">× {{ $totalQty }}</div>
                        <div class="rm-item-price">{{ number_format($lineTotal) }}</div>
                    </div>
                @endforeach
            </div>

            {{-- Payment breakdown --}}
            <div class="rm-section">
                <div class="rm-section-title">Payment</div>
                @php $payments = $selectedSale->payments; @endphp
                @if($payments && $payments->count() > 0)
                    @foreach($payments as $pay)
                    <div class="rm-pay-row">
                        <span>{{ ucfirst(str_replace('_', ' ', $pay->payment_method->value ?? $pay->payment_method)) }}</span>
                        <span style="font-family:monospace;font-weight:600;">{{ number_format($pay->amount) }} RWF</span>
                    </div>
                    @endforeach
                @else
                    <div class="rm-pay-row">
                        <span>{{ ucfirst(str_replace('_', ' ', $selectedSale->payment_method->value ?? $selectedSale->payment_method ?? 'Cash')) }}</span>
                        <span style="font-family:monospace;font-weight:600;">{{ number_format($selectedSale->total) }} RWF</span>
                    </div>
                @endif
                <div class="rm-pay-row total">
                    <span>Total</span>
                    <span style="font-family:monospace;">{{ number_format($selectedSale->total) }} RWF</span>
                </div>
                @if($selectedSale->has_credit && $selectedSale->credit_amount > 0)
                <div style="margin-top:8px;padding:8px 10px;background:rgba(245,158,11,.1);border-radius:8px;border:1px solid rgba(245,158,11,.3);">
                    <div style="font-size:11px;color:var(--amber);font-weight:600;">
                        Credit balance: {{ number_format($selectedSale->credit_amount) }} RWF
                    </div>
                </div>
                @endif
            </div>

        </div>

        {{-- Footer actions --}}
        <div class="rm-footer">
            <a href="{{ route('shop.receipt.print', $selectedSale->id) }}" target="_blank" class="rm-footer-btn print">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>
                </svg>
                Print Receipt
            </a>
            <button class="rm-footer-btn close-btn" wire:click="closeReceiptModal">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                Close
            </button>
        </div>

    </div>
</div>
@endif
</div>
