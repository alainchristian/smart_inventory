<div style="font-family:var(--font)">
<style>
/* ── Fulfillment Queue ── fq- ──────────────────────────────────── */

/* Stat bar */
.fq-stat-bar {
    display:flex;align-items:stretch;
    background:var(--surface);border-radius:var(--r);
    box-shadow:var(--shadow-card);overflow:hidden;margin-bottom:24px;
}
.fq-stat {
    flex:1;padding:18px 24px;border-right:1px solid var(--border);
    display:flex;flex-direction:column;gap:3px;
}
.fq-stat:last-child { border-right:none }
.fq-stat-val {
    font-size:26px;font-weight:800;font-family:var(--mono);
    letter-spacing:-1px;color:var(--text);line-height:1;
}
.fq-stat-lbl {
    font-size:10px;font-weight:700;text-transform:uppercase;
    letter-spacing:.6px;color:var(--text-dim);margin-top:2px;
}
.fq-stat-sub { font-size:11px;color:var(--text-faint) }

/* Tabs */
.fq-tabs {
    display:flex;gap:2px;background:var(--surface2);
    border-radius:8px;padding:3px;margin-bottom:20px;width:fit-content;
}
.fq-tab {
    padding:6px 16px;border:none;border-radius:6px;cursor:pointer;
    font-size:13px;font-weight:600;font-family:var(--font);
    background:transparent;color:var(--text-dim);transition:all var(--tr);
    display:inline-flex;align-items:center;gap:6px;
}
.fq-tab:hover { color:var(--text) }
.fq-tab.active {
    background:var(--surface);color:var(--accent);
    box-shadow:0 1px 4px rgba(0,0,0,.08);
}
.fq-tab-ct {
    font-size:10px;font-weight:800;padding:1px 6px;border-radius:4px;
    background:var(--amber-dim);color:var(--amber);line-height:1.4;
}
.fq-tab.active .fq-tab-ct { background:var(--accent-dim);color:var(--accent) }

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
    font-family:var(--mono);font-size:13px;font-weight:800;
    color:var(--text);flex-shrink:0;
}
.fq-shop {
    font-size:12px;color:var(--text-dim);flex:1;min-width:0;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.fq-age {
    font-size:11px;font-weight:700;padding:2px 8px;border-radius:5px;flex-shrink:0;
}
.fq-age-ok    { background:var(--surface2);color:var(--text-faint) }
.fq-age-amber { background:var(--amber-dim);color:var(--amber) }
.fq-age-red   { background:var(--red-dim);color:var(--red) }

/* Products */
.fq-prods {
    font-size:12px;color:var(--text);margin-bottom:13px;line-height:1.6;
}
.fq-prod-qty { color:var(--text-dim);font-size:11px }
.fq-dot      { color:var(--border);margin:0 5px }

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
    padding:7px 15px;border-radius:8px;border:none;cursor:pointer;
    background:var(--accent);color:#fff;font-size:12px;font-weight:700;
    font-family:var(--font);transition:opacity var(--tr);
    display:inline-flex;align-items:center;gap:5px;flex-shrink:0;
}
.fq-btn-dispatch:hover { opacity:.88 }

/* Confirm strip */
.fq-confirm {
    padding:13px 22px;background:var(--surface2);border-top:1px solid var(--border);
    display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;
}
.fq-confirm-msg { font-size:13px;color:var(--text) }
.fq-confirm-sub { font-size:11px;color:var(--text-dim);margin-top:1px }
.fq-confirm-btns { display:flex;gap:7px;flex-shrink:0 }
.fq-btn-cancel {
    padding:6px 14px;border-radius:7px;border:1.5px solid var(--border);
    background:transparent;color:var(--text-dim);cursor:pointer;
    font-size:12px;font-weight:600;font-family:var(--font);transition:all var(--tr);
}
.fq-btn-cancel:hover { background:var(--surface);color:var(--text) }
.fq-btn-yes {
    padding:6px 14px;border-radius:7px;border:none;
    background:var(--green);color:#fff;cursor:pointer;
    font-size:12px;font-weight:700;font-family:var(--font);
    transition:opacity var(--tr);display:inline-flex;align-items:center;gap:5px;
}
.fq-btn-yes:hover { opacity:.88 }

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
.fq-tbl thead tr { background:var(--surface2);border-bottom:2px solid var(--border) }
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

.fq-tbl-ref  { font-family:var(--mono);font-size:12px;font-weight:800;color:var(--text) }
.fq-tbl-sub  { font-size:11px;color:var(--text-dim);margin-top:1px }

.fq-check {
    width:20px;height:20px;border-radius:50%;background:var(--green-dim);
    display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;
    vertical-align:middle;margin-right:8px;
}

/* Expanded row */
.fq-tbl-exp { background:var(--surface2) }
.fq-tbl-exp td { padding:0 }
.fq-exp-inner {
    padding:16px 20px;border-bottom:1px solid var(--border);
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
.fq-box-chips { display:flex;flex-wrap:wrap;gap:5px;margin-top:8px }
.fq-box-chip {
    display:inline-flex;align-items:center;gap:5px;padding:3px 9px;
    border-radius:5px;font-size:11px;background:var(--surface);border:1px solid var(--border);
}
.fq-box-chip b { font-family:var(--mono);font-weight:700;color:var(--accent) }

/* Section hint */
.fq-count-hint {
    font-size:11px;color:var(--text-dim);margin-bottom:12px;
}

/* Responsive */
@media(max-width:900px) {
    .fq-stat { flex:1 1 140px }
    .fq-exp-inner { grid-template-columns:1fr }
}
@media(max-width:600px) {
    .fq-stat-bar { flex-direction:column }
    .fq-stat { border-right:none;border-bottom:1px solid var(--border) }
    .fq-stat:last-child { border-bottom:none }
    .fq-row-top { flex-direction:column;align-items:flex-start;gap:4px }
    .fq-act { flex-direction:column;align-items:flex-start }
    .fq-btn-dispatch { width:100%;justify-content:center }
    .fq-confirm { flex-direction:column;align-items:flex-start }
    .fq-tabs { width:100% }
}
</style>

{{-- ── Stat bar ────────────────────────────────────────────────────── --}}
@php
    $pendingBoxTotal = $pendingSales->sum(
        fn($s) => $s->items->filter(fn($i) => $i->box?->location_type?->value === 'warehouse')->count()
    );
@endphp
<div class="fq-stat-bar">
    <div class="fq-stat">
        <div class="fq-stat-val">{{ $pendingSales->count() }}</div>
        <div class="fq-stat-lbl">Awaiting Dispatch</div>
        <div class="fq-stat-sub">{{ $pendingBoxTotal }} {{ $pendingBoxTotal === 1 ? 'box' : 'boxes' }} to hand over</div>
    </div>
    <div class="fq-stat">
        <div class="fq-stat-val" style="color:var(--green)">{{ $fulfilledToday }}</div>
        <div class="fq-stat-lbl">Fulfilled Today</div>
        <div class="fq-stat-sub">dispatched this session</div>
    </div>
    <div class="fq-stat">
        <div class="fq-stat-val">{{ $boxesDispatchedToday }}</div>
        <div class="fq-stat-lbl">Boxes Out Today</div>
        <div class="fq-stat-sub">from {{ $warehouseName }}</div>
    </div>
</div>

{{-- ── Tabs ────────────────────────────────────────────────────────── --}}
<div class="fq-tabs">
    <button class="fq-tab {{ $tab === 'pending' ? 'active' : '' }}" wire:click="setTab('pending')">
        Pending
        @if($pendingSales->count() > 0)
            <span class="fq-tab-ct">{{ $pendingSales->count() }}</span>
        @endif
    </button>
    <button class="fq-tab {{ $tab === 'history' ? 'active' : '' }}" wire:click="setTab('history')">
        History
    </button>
</div>

{{-- ══════════ PENDING ══════════ --}}
@if($tab === 'pending')

@if($pendingSales->isEmpty())
<div class="fq-empty">
    <svg class="fq-empty-icon" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div class="fq-empty-title">Queue is clear</div>
    <div class="fq-empty-sub">All warehouse-sourced orders have been dispatched.</div>
</div>
@else

@foreach($pendingSales as $sale)
@php
    $whItems    = $sale->items->filter(fn($i) => $i->box?->location_type?->value === 'warehouse');
    $byProduct  = $whItems->groupBy(fn($i) => $i->product_id)->map(fn($g) => [
        'name'  => $g->first()->product?->name ?? '—',
        'boxes' => $g->count(),
    ]);
    $paidFull   = $sale->total > 0 && $sale->payments->sum('amount') >= $sale->total;
    $ageMin     = (int) $sale->sale_date->diffInMinutes(now());
    $urgency    = $ageMin >= 120 ? 'red' : ($ageMin >= 30 ? 'amber' : '');
    $ageBadge   = $ageMin >= 120 ? 'fq-age-red' : ($ageMin >= 30 ? 'fq-age-amber' : 'fq-age-ok');
    $ageLabel   = $ageMin < 60
        ? "{$ageMin}m ago"
        : floor($ageMin / 60).'h '.str_pad($ageMin % 60, 2, '0').'m';
    $confirming = $confirmingFulfillmentId === $sale->id;
@endphp

<div class="fq-card" wire:key="card-{{ $sale->id }}">
    <div class="fq-urgency {{ $urgency }}"></div>

    {{-- Main body (always visible) --}}
    <div class="fq-body">
        <div class="fq-row-top">
            <span class="fq-ref">{{ $sale->sale_number }}</span>
            <span class="fq-shop">
                {{ $sale->shop?->name ?? '—' }}
                @if($sale->customer_name)
                    &middot; {{ $sale->customer_name }}
                @endif
                &middot; {{ $sale->sale_date->format('d M, H:i') }}
            </span>
            <span class="fq-age {{ $ageBadge }}">{{ $ageLabel }}</span>
        </div>

        <div class="fq-prods">
            @foreach($byProduct as $prod)
                <span style="font-weight:600">{{ $prod['name'] }}</span>@if($prod['boxes'] > 1)<span class="fq-prod-qty"> &times;{{ $prod['boxes'] }}</span>@endif@if(!$loop->last)<span class="fq-dot">&middot;</span>@endif
            @endforeach
        </div>

        @if(!$confirming)
        <div class="fq-act">
            <div class="fq-via">
                @if($sale->fulfillment_method === 'transporter')
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    Via&nbsp;<b>{{ $sale->fulfillmentTransporter?->name ?? 'Transporter' }}</b>
                @else
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <b>Customer Pickup</b>
                @endif
                @if(!$paidFull)
                    <span class="fq-outstanding" style="margin-left:6px">Balance outstanding</span>
                @endif
            </div>
            <button class="fq-btn-dispatch" wire:click="requestFulfillment({{ $sale->id }})">
                Confirm Dispatch
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
        @endif
    </div>

    {{-- Confirm strip --}}
    @if($confirming)
    <div class="fq-confirm">
        <div>
            <div class="fq-confirm-msg">
                Hand over <strong>{{ $whItems->count() }} {{ $whItems->count() === 1 ? 'box' : 'boxes' }}</strong>
                to {{ $sale->fulfillment_method === 'transporter'
                    ? ($sale->fulfillmentTransporter?->name ?? 'transporter')
                    : 'customer' }}?
            </div>
            <div class="fq-confirm-sub">This action is permanent and cannot be undone.</div>
        </div>
        <div class="fq-confirm-btns">
            <button class="fq-btn-cancel" wire:click="cancelFulfillment">Cancel</button>
            <button class="fq-btn-yes" wire:click="markFulfilled({{ $sale->id }})">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Yes, Dispatched
            </button>
        </div>
    </div>
    @endif

</div>
@endforeach
@endif
@endif {{-- /pending --}}

{{-- ══════════ HISTORY ══════════ --}}
@if($tab === 'history')

@if($fulfilledHistory->isEmpty())
<div class="fq-empty">
    <svg class="fq-empty-icon" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10"/><polyline points="20 6 9 17 4 12"/>
    </svg>
    <div class="fq-empty-title">No dispatches yet</div>
    <div class="fq-empty-sub">Fulfilled orders will appear here with a full audit trail.</div>
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
            <div style="font-size:12px;font-weight:600;color:var(--text)">{{ $sale->fulfillment_confirmed_at->format('H:i') }}</div>
            <div class="fq-tbl-sub">{{ $sale->fulfillment_confirmed_at->format('d M Y') }}</div>
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
                        <span class="fq-exp-val">{{ $sale->fulfillment_confirmed_at?->format('d M Y, H:i') ?? '—' }}</span>
                    </div>
                    <div class="fq-exp-row">
                        <span class="fq-exp-key">Sold by</span>
                        <span class="fq-exp-val">{{ $sale->soldBy?->name ?? '—' }}</span>
                    </div>
                    @if($sale->customer_name)
                    <div class="fq-exp-row">
                        <span class="fq-exp-key">Recipient</span>
                        <span class="fq-exp-val">
                            {{ $sale->customer_name }}
                            @if($sale->customer_phone)
                                <span style="font-family:var(--mono);color:var(--text-dim);font-weight:400"> · {{ $sale->customer_phone }}</span>
                            @endif
                        </span>
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
                        <span class="fq-box-chip">
                            {{ $row['name'] }}
                            <b>&times;{{ $row['boxes'] }}</b>
                            @if($row['sku'])
                                <span style="font-size:10px;color:var(--text-faint);font-family:var(--mono)">{{ $row['sku'] }}</span>
                            @endif
                        </span>
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
@endif {{-- /history --}}

</div>
