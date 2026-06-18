<div style="font-family:var(--font)">
<style>
/* ── Receipt Search  rs- ───────────────────────────────────────────────── */
.rs-header        { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:24px;flex-wrap:wrap }
.rs-header-title  { font-size:22px;font-weight:800;color:var(--text);margin:0 0 4px }
.rs-header-sub    { font-size:13px;color:var(--text-dim);margin:0 }

/* Filter card */
.rs-filters       { background:var(--surface);border:none;border-radius:var(--r);box-shadow:var(--shadow-card);margin-bottom:20px;min-width:0;max-width:100% }
.rs-presets-row   { display:flex;gap:4px;overflow-x:auto;-webkit-overflow-scrolling:touch;padding:10px 14px;border-bottom:1px solid var(--border);scrollbar-width:none;flex-wrap:nowrap;min-width:0 }
.rs-presets-row::-webkit-scrollbar { display:none }
.rs-preset-btn    { padding:5px 11px;border-radius:6px;font-size:12px;font-weight:600;border:1px solid transparent;background:transparent;color:var(--text-dim);cursor:pointer;white-space:nowrap;flex-shrink:0;transition:all var(--tr);font-family:var(--font) }
.rs-preset-btn:hover  { background:var(--surface2);color:var(--text);border-color:var(--border) }
.rs-preset-btn.active { background:var(--accent);color:#fff;border-color:var(--accent);box-shadow:0 2px 8px rgba(0,0,0,.12) }
.rs-filter-row    { display:flex;align-items:center;flex-wrap:wrap }
.rs-filter-seg    { display:flex;align-items:center;gap:6px;padding:8px 14px;border-right:1px solid var(--border);flex-shrink:0 }
.rs-filter-seg:last-child { border-right:none }
.rs-filter-grow   { flex:1;min-width:200px }
.rs-search-wrap   { position:relative;width:100% }
.rs-search-icon   { position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-dim);pointer-events:none }
.rs-search        { width:100%;padding:9px 12px 9px 36px;border:1.5px solid var(--border);border-radius:var(--rsm);background:var(--bg);color:var(--text);font-size:13px;font-family:var(--font);outline:none;box-sizing:border-box;transition:border-color var(--tr) }
.rs-search:focus  { border-color:var(--accent) }
.rs-search::placeholder { color:var(--text-dim) }
.rs-date-input    { padding:0;border:none;background:transparent;color:var(--text);font-size:13px;font-weight:600;font-family:var(--font);cursor:pointer;width:110px;outline:none }
.rs-date-input:focus { color:var(--accent) }
.rs-filter-label  { font-size:11px;font-weight:600;color:var(--text-dim);white-space:nowrap }

/* Table */
.rs-table-wrap    { background:var(--surface);border:none;border-radius:var(--r);box-shadow:var(--shadow-card) }
.rs-table         { width:100%;border-collapse:collapse }
.rs-table thead tr{ border-bottom:2px solid var(--border) }
.rs-table thead th{ padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.rs-table tbody tr{ border-bottom:1px solid var(--border);transition:background var(--tr) }
.rs-table tbody tr:last-child { border-bottom:none }
.rs-table tbody tr:hover { background:var(--surface2) }
.rs-table td      { padding:12px 16px;font-size:13px;vertical-align:middle }

/* Action buttons */
.rs-action        { display:inline-flex;align-items:center;gap:4px;padding:5px 11px;border-radius:7px;border:1.5px solid var(--border);background:transparent;font-size:12px;font-weight:600;cursor:pointer;font-family:var(--font);color:var(--text-sub);transition:all var(--tr);white-space:nowrap;text-decoration:none }
.rs-action:hover  { border-color:var(--accent);color:var(--accent) }
.rs-action.print:hover { border-color:var(--text-dim);color:var(--text) }

/* Empty */
.rs-empty         { padding:60px 20px;text-align:center }
.rs-empty-title   { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.rs-empty-sub     { font-size:13px;color:var(--text-dim) }

/* Mobile — Strategy B: hide columns + card transform */
@media(max-width:768px) { .rs-hide-mob { display:none !important } }
@media(max-width:640px) {
    .rs-cards-mob thead  { display:none }
    .rs-cards-mob tbody  { display:block }
    .rs-cards-mob tr     { display:block;padding:11px 14px;border-bottom:1px solid var(--border) }
    .rs-cards-mob tr:last-child { border-bottom:none }
    .rs-cards-mob td     { display:flex;justify-content:space-between;align-items:center;padding:3px 0;border:none }
    .rs-cards-mob td[data-label]::before { content:attr(data-label);font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.4px;flex-shrink:0;margin-right:8px }
}

/* Receipt view modal */
.rs-overlay       { position:fixed;inset:0;background:rgba(26,31,54,.5);z-index:400;display:flex;align-items:center;justify-content:center;padding:16px }
.rs-modal         { background:var(--surface);border-radius:var(--r);box-shadow:0 16px 48px rgba(26,31,54,.18);width:100%;max-width:480px;max-height:92vh;display:flex;flex-direction:column }
.rs-modal-banner  { background:var(--green);padding:20px 22px 16px;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-shrink:0;border-radius:var(--r) var(--r) 0 0 }
.rs-modal-close   { width:28px;height:28px;border-radius:50%;border:none;background:rgba(255,255,255,.18);color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background var(--tr) }
.rs-modal-close:hover { background:rgba(255,255,255,.32) }
.rs-modal-body    { flex:1;overflow-y:auto;padding:18px 22px;display:flex;flex-direction:column;gap:14px }
.rs-modal-section { }
.rs-modal-sec-lbl { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-dim);margin-bottom:8px }
.rs-item-row      { display:flex;justify-content:space-between;align-items:baseline;gap:8px;padding:5px 0;border-bottom:1px solid var(--border);font-size:12px }
.rs-item-row:last-child { border-bottom:none }
.rs-pay-row       { display:flex;justify-content:space-between;padding:4px 0;font-size:12px;color:var(--text-dim) }
.rs-pay-total     { border-top:1.5px solid var(--border);margin-top:6px;padding-top:8px;font-size:14px;font-weight:800;color:var(--text) }
.rs-modal-foot    { padding:14px 22px;border-top:1px solid var(--border);display:grid;grid-template-columns:1fr 1fr;gap:10px;flex-shrink:0 }
</style>

{{-- ── Page header ─────────────────────────────────────────────────────────── --}}
<div class="rs-header">
    <div>
        <h1 class="rs-header-title">Receipt History</h1>
        <p class="rs-header-sub">Find and reprint sales receipts</p>
    </div>
    <a href="{{ route('shop.pos') }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:var(--rsm);border:1.5px solid var(--border);background:var(--surface);color:var(--text-dim);font-size:13px;font-weight:600;text-decoration:none;transition:all var(--tr);white-space:nowrap"
       onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'"
       onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-dim)'">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        Back to POS
    </a>
</div>

{{-- ── Filters ──────────────────────────────────────────────────────────────── --}}
<div class="rs-filters">
    {{-- Preset pills --}}
    <div class="rs-presets-row">
        @foreach(['today'=>'Today','yesterday'=>'Yesterday','this_week'=>'This Week','this_month'=>'This Month','last_month'=>'Last Month','last_30'=>'Last 30 Days'] as $key => $label)
            <button class="rs-preset-btn {{ $preset === $key ? 'active' : '' }}" wire:click="setPreset('{{ $key }}')">{{ $label }}</button>
        @endforeach
    </div>
    {{-- Filter row --}}
    <div class="rs-filter-row">
        <div class="rs-filter-seg">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--text-dim);flex-shrink:0"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
            <input class="rs-date-input" type="date" wire:model.live="dateFrom">
            <span style="font-size:12px;color:var(--text-dim)">→</span>
            <input class="rs-date-input" type="date" wire:model.live="dateTo">
        </div>
        <div class="rs-filter-seg rs-filter-grow">
            <div class="rs-search-wrap">
                <svg class="rs-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input class="rs-search" type="text" wire:model.live.debounce.300ms="search" placeholder="Sale #, customer name, phone, product…">
            </div>
        </div>
    </div>
</div>

{{-- ── Results table ────────────────────────────────────────────────────────── --}}
<div class="rs-table-wrap">
    <div style="overflow-x:auto;-webkit-overflow-scrolling:touch">
        <table class="rs-table rs-cards-mob">
            <thead>
                <tr>
                    <th>Receipt #</th>
                    <th>Customer</th>
                    <th class="rs-hide-mob">Items</th>
                    <th style="text-align:right">Total</th>
                    <th class="rs-hide-mob">Date &amp; Time</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                @php
                    $grouped = $sale->items->groupBy('product_id');
                    $names   = $grouped->map(fn($g) => $g->first()->product?->name ?? '?')->take(2)->values();
                    $extra   = $grouped->count() - 2;
                    $itemStr = $names->implode(', ') . ($extra > 0 ? ' +' . $extra . ' more' : '');
                @endphp
                <tr wire:key="sale-{{ $sale->id }}">
                    <td data-label="Receipt #">
                        <div style="font-size:12px;font-weight:700;font-family:var(--mono);color:var(--text)">{{ $sale->sale_number }}</div>
                        @if($sale->shop)
                            <div style="font-size:11px;color:var(--text-dim)">{{ $sale->shop->name }}</div>
                        @endif
                    </td>
                    <td data-label="Customer">
                        @if($sale->customer_name)
                            <div style="font-size:13px;color:var(--text)">{{ $sale->customer_name }}</div>
                            @if($sale->customer_phone)
                                <div style="font-size:11px;font-family:var(--mono);color:var(--text-dim)">{{ $sale->customer_phone }}</div>
                            @endif
                        @else
                            <span style="font-size:12px;color:var(--text-dim)">Walk-in</span>
                        @endif
                    </td>
                    <td class="rs-hide-mob" data-label="Items" style="font-size:12px;color:var(--text-dim);max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $itemStr }}">
                        {{ $itemStr }}
                    </td>
                    <td data-label="Total" style="text-align:right;white-space:nowrap">
                        <span style="font-family:var(--mono);font-weight:700;color:var(--accent)">{{ number_format($sale->total) }}</span>
                        <span style="font-size:10px;color:var(--text-dim);margin-left:2px">RWF</span>
                    </td>
                    <td class="rs-hide-mob" data-label="Date" style="white-space:nowrap">
                        <div style="font-size:13px;color:var(--text-sub)">{{ ($sale->sale_date ?? $sale->created_at)->format('d M Y') }}</div>
                        <div style="font-size:11px;color:var(--text-dim)">{{ ($sale->sale_date ?? $sale->created_at)->format('H:i') }}</div>
                    </td>
                    <td data-label="Actions">
                        <div style="display:flex;gap:6px;justify-content:flex-end">
                            <button wire:click="viewSale({{ $sale->id }})" class="rs-action">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                View
                            </button>
                            <a href="{{ route('shop.receipt.print', $sale->id) }}" target="_blank" class="rs-action print">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                Print
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="rs-empty">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--border)" stroke-width="1.5" style="margin:0 auto 12px;display:block"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            <div class="rs-empty-title">No receipts found</div>
                            <div class="rs-empty-sub">Try adjusting the date range or search query</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Infinite scroll sentinel --}}
    @if($hasMore)
    <div wire:key="scroll-sentinel" x-data
         x-init="
             const obs = new IntersectionObserver(entries => {
                 if (entries[0].isIntersecting) $wire.loadMore()
             }, { rootMargin: '300px' });
             obs.observe($el);
         "
         style="height:1px;margin-top:4px"></div>
    @else
    <div style="padding:14px 0;text-align:center;font-size:12px;color:var(--text-dim);border-top:1px solid var(--border)">
        @if($total > $perPage)
            All {{ number_format($total) }} receipts loaded
        @elseif($total > 0)
            {{ number_format($total) }} {{ $total === 1 ? 'receipt' : 'receipts' }}
        @endif
    </div>
    @endif
</div>

{{-- ── Receipt view modal ───────────────────────────────────────────────────── --}}
@if($showReceiptModal && $selectedSale)
<div class="rs-overlay" wire:click.self="closeReceiptModal">
    <div class="rs-modal">

        {{-- Banner --}}
        <div class="rs-modal-banner">
            <div>
                <div style="font-size:11px;color:rgba(255,255,255,.75);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">Receipt</div>
                <div style="font-size:19px;font-weight:800;font-family:var(--mono);color:#fff">{{ $selectedSale->sale_number }}</div>
                @if($selectedSale->shop)
                    <div style="font-size:12px;color:rgba(255,255,255,.75);margin-top:3px">{{ $selectedSale->shop->name }}</div>
                @endif
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px">
                <button class="rs-modal-close" wire:click="closeReceiptModal">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
                <div style="font-size:11px;color:rgba(255,255,255,.75)">{{ ($selectedSale->sale_date ?? $selectedSale->created_at)->format('d M Y, H:i') }}</div>
            </div>
        </div>

        {{-- Body --}}
        <div class="rs-modal-body">

            {{-- Customer --}}
            @if($selectedSale->customer_name || $selectedSale->customer_phone)
            <div class="rs-modal-section">
                <div class="rs-modal-sec-lbl">Customer</div>
                <div style="font-size:13px;color:var(--text);font-weight:600">{{ $selectedSale->customer_name ?? '—' }}</div>
                @if($selectedSale->customer_phone)
                    <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono)">{{ $selectedSale->customer_phone }}</div>
                @endif
            </div>
            @endif

            {{-- Served by --}}
            @if($selectedSale->soldBy)
            <div style="font-size:11px;color:var(--text-dim)">
                Served by <strong style="color:var(--text-sub)">{{ $selectedSale->soldBy->name }}</strong>
            </div>
            @endif

            {{-- Items --}}
            <div class="rs-modal-section">
                <div class="rs-modal-sec-lbl">Items</div>
                <div style="background:var(--bg);border-radius:var(--rsm);padding:8px 10px">
                    @foreach($selectedSale->items->groupBy(fn($i) => $i->product_id.'_'.($i->is_full_box?'b':'i')) as $lines)
                    @php
                        $line      = $lines->first();
                        $isBox     = $line->is_full_box;
                        $ipb       = max(1, $line->product?->items_per_box ?? 1);
                        $totalQty  = $isBox ? (int) round($lines->sum('quantity_sold') / $ipb) : $lines->sum('quantity_sold');
                        $unitLabel = $isBox ? ($totalQty === 1 ? 'box' : 'boxes') : 'pcs';
                        $lineTotal = $lines->sum('line_total');
                    @endphp
                    <div class="rs-item-row">
                        <span style="color:var(--text);flex:1">{{ $line->product?->name ?? '?' }}</span>
                        <span style="color:var(--text-dim);font-size:11px;white-space:nowrap">{{ $totalQty }} {{ $unitLabel }}</span>
                        <span style="font-weight:700;font-family:var(--mono);color:var(--text);white-space:nowrap">{{ number_format($lineTotal) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Payment --}}
            <div class="rs-modal-section">
                <div class="rs-modal-sec-lbl">Payment</div>
                <div style="background:var(--bg);border-radius:var(--rsm);padding:8px 10px">
                    @php $payments = $selectedSale->payments; @endphp
                    @if($payments && $payments->count() > 0)
                        @foreach($payments as $pay)
                        <div class="rs-pay-row">
                            <span>{{ ucfirst(str_replace('_', ' ', $pay->payment_method->value ?? $pay->payment_method)) }}</span>
                            <span style="font-family:var(--mono);font-weight:600">{{ number_format($pay->amount) }} RWF</span>
                        </div>
                        @endforeach
                    @else
                        <div class="rs-pay-row">
                            <span>{{ ucfirst(str_replace('_', ' ', $selectedSale->payment_method->value ?? $selectedSale->payment_method ?? 'Cash')) }}</span>
                            <span style="font-family:var(--mono);font-weight:600">{{ number_format($selectedSale->total) }} RWF</span>
                        </div>
                    @endif
                    <div class="rs-pay-row rs-pay-total">
                        <span>Total</span>
                        <span style="font-family:var(--mono)">{{ number_format($selectedSale->total) }} RWF</span>
                    </div>
                </div>
                @if($selectedSale->has_credit && $selectedSale->credit_amount > 0)
                <div style="margin-top:8px;padding:8px 12px;background:var(--amber-dim);border-radius:var(--rsm)">
                    <span style="font-size:12px;color:var(--amber);font-weight:600">
                        Credit portion: {{ number_format($selectedSale->credit_amount) }} RWF
                    </span>
                </div>
                @endif
            </div>

            {{-- Notes --}}
            @if($selectedSale->notes)
            <div style="font-size:12px;color:var(--text-dim);background:var(--bg);border-radius:var(--rsm);padding:8px 12px">
                <span style="font-weight:600;color:var(--text-sub)">Note:</span> {{ $selectedSale->notes }}
            </div>
            @endif

        </div>

        {{-- Footer --}}
        <div class="rs-modal-foot">
            <a href="{{ route('shop.receipt.print', $selectedSale->id) }}" target="_blank"
               style="display:flex;align-items:center;justify-content:center;gap:6px;padding:10px;border-radius:var(--rsm);border:1.5px solid var(--border);background:transparent;color:var(--text-sub);font-size:13px;font-weight:600;text-decoration:none;cursor:pointer;transition:all var(--tr);font-family:var(--font)"
               onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'"
               onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-sub)'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print Receipt
            </a>
            <button wire:click="closeReceiptModal"
                    style="display:flex;align-items:center;justify-content:center;gap:6px;padding:10px;border-radius:var(--rsm);border:1.5px solid var(--border);background:transparent;color:var(--text-dim);font-size:13px;font-weight:600;cursor:pointer;transition:all var(--tr);font-family:var(--font)"
                    onmouseover="this.style.borderColor='var(--red)';this.style.color='var(--red)'"
                    onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-dim)'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Close
            </button>
        </div>

    </div>
</div>
@endif

</div>
