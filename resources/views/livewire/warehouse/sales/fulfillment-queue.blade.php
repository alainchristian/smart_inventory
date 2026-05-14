<div>
<style>
.fq-kpi-strip {
    display: flex;
    gap: 14px;
    margin-bottom: 24px;
}
.fq-kpi {
    flex: 1;
    background: var(--surface-raised);
    border: 1px solid var(--border);
    border-radius: var(--rx);
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.fq-kpi-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-dim);
    text-transform: uppercase;
    letter-spacing: .04em;
}
.fq-kpi-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--text);
    line-height: 1;
}
.fq-kpi-value.pending { color: var(--amber); }
.fq-kpi-value.fulfilled { color: var(--green); }

.fq-card {
    background: var(--surface-raised);
    border: 1px solid var(--border);
    border-radius: var(--rx);
    overflow: hidden;
    margin-bottom: 16px;
}
.fq-sale-row {
    display: grid;
    grid-template-columns: 1fr 1fr 120px 140px 110px 80px;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    cursor: pointer;
    transition: background .15s;
    border-bottom: 1px solid var(--border);
}
.fq-sale-row:hover { background: var(--surface2); }
.fq-sale-row:last-child { border-bottom: none; }
.fq-sale-number {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    font-family: monospace;
}
.fq-sale-shop {
    font-size: 13px;
    color: var(--text-sub);
}
.fq-method-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.chip-transporter {
    background: color-mix(in srgb, var(--accent) 12%, transparent);
    color: var(--accent);
}
.chip-pickup {
    background: color-mix(in srgb, var(--green) 12%, transparent);
    color: var(--green);
}
.fq-time {
    font-size: 12px;
    color: var(--text-dim);
}
.fq-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    justify-content: flex-end;
}
.fq-expand-chevron {
    transition: transform .2s;
    color: var(--text-dim);
    flex-shrink: 0;
}
.fq-expand-chevron.open { transform: rotate(180deg); }

/* Expanded detail */
.fq-detail {
    background: var(--surface);
    border-top: 1px solid var(--border);
    padding: 16px 20px;
}
.fq-detail-header {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-dim);
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 10px;
}
.fq-boxes-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 14px;
}
.fq-box-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 6px 10px;
    font-size: 12px;
    color: var(--text);
}
.fq-box-code {
    font-family: monospace;
    font-weight: 700;
    color: var(--accent);
    font-size: 13px;
}
.fq-box-meta {
    color: var(--text-dim);
    font-size: 11px;
}
.fq-detail-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 12px;
    border-top: 1px solid var(--border);
    margin-top: 8px;
}
.fq-total-label {
    font-size: 13px;
    color: var(--text-sub);
}
.fq-total-value {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
}

/* Confirm row */
.fq-confirm-row {
    background: color-mix(in srgb, var(--amber) 8%, var(--surface));
    border-top: 1px solid color-mix(in srgb, var(--amber) 20%, var(--border));
    padding: 12px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.fq-confirm-text {
    font-size: 13px;
    color: var(--text);
}

/* Buttons */
.btn-fulfill {
    padding: 6px 14px;
    background: var(--green);
    color: #fff;
    border: none;
    border-radius: var(--rx);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: opacity .15s;
}
.btn-fulfill:hover { opacity: .88; }
.btn-cancel-sm {
    padding: 6px 12px;
    background: var(--surface2);
    color: var(--text-sub);
    border: 1px solid var(--border);
    border-radius: var(--rx);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}
.btn-cancel-sm:hover { background: var(--surface3, var(--surface2)); }
.btn-confirm-fulfill {
    padding: 6px 16px;
    background: var(--green);
    color: #fff;
    border: none;
    border-radius: var(--rx);
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity .15s;
}
.btn-confirm-fulfill:hover { opacity: .88; }

.fq-empty {
    padding: 48px 24px;
    text-align: center;
    color: var(--text-dim);
}
.fq-empty-icon {
    width: 48px;
    height: 48px;
    margin: 0 auto 12px;
    color: var(--border);
}
.fq-empty-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-sub);
    margin-bottom: 4px;
}

/* Tabs */
.fq-tabs {
    display: flex;
    gap: 4px;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 4px;
    margin-bottom: 22px;
}
.fq-tab-btn {
    flex: 1;
    padding: 8px 16px;
    border: none;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    font-family: var(--font);
    background: transparent;
    color: var(--text-dim);
    transition: all .15s;
}
.fq-tab-btn.active {
    background: var(--surface-raised);
    color: var(--text);
    box-shadow: 0 1px 4px rgba(0,0,0,.1);
}

/* History table */
.fq-hist-table { width: 100%; border-collapse: collapse; }
.fq-hist-th {
    padding: 10px 14px;
    text-align: left;
    font-size: 10px;
    font-weight: 800;
    color: var(--text-dim);
    text-transform: uppercase;
    letter-spacing: .05em;
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
.fq-hist-td {
    padding: 11px 14px;
    font-size: 13px;
    color: var(--text-sub);
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}
.fq-hist-td:last-child, .fq-hist-th:last-child { text-align: right; }
.fq-hist-row:last-child .fq-hist-td { border-bottom: none; }
.fq-hist-row:hover .fq-hist-td { background: var(--surface2); }

@media (max-width: 900px) {
    .fq-kpi-strip { flex-direction: column; }
    .fq-sale-row { grid-template-columns: 1fr 1fr; gap: 8px; }
}
</style>

{{-- Flash --}}
@if(session('error'))
    <div style="background:color-mix(in srgb,var(--red) 10%,var(--surface));border:1px solid color-mix(in srgb,var(--red) 25%,var(--border));border-radius:var(--rx);padding:10px 16px;margin-bottom:16px;color:var(--red);font-size:13px;">
        {{ session('error') }}
    </div>
@endif

{{-- Tabs --}}
<div class="fq-tabs">
    <button class="fq-tab-btn {{ $tab === 'pending' ? 'active' : '' }}" wire:click="setTab('pending')">
        Pending Fulfillment
        @if($pendingSales->count() > 0)
            <span style="display:inline-block;background:var(--amber);color:#fff;
                         font-size:9px;font-weight:800;padding:1px 6px;border-radius:10px;
                         margin-left:5px;vertical-align:1px">{{ $pendingSales->count() }}</span>
        @endif
    </button>
    <button class="fq-tab-btn {{ $tab === 'history' ? 'active' : '' }}" wire:click="setTab('history')">
        Fulfilled History
    </button>
</div>

{{-- KPI strip --}}
<div class="fq-kpi-strip">
    <div class="fq-kpi">
        <span class="fq-kpi-label">Pending Fulfillment</span>
        <span class="fq-kpi-value pending">{{ $pendingSales->count() }}</span>
        <span style="font-size:12px;color:var(--text-dim);">awaiting handover</span>
    </div>
    <div class="fq-kpi">
        <span class="fq-kpi-label">Fulfilled Today</span>
        <span class="fq-kpi-value fulfilled">{{ $fulfilledToday }}</span>
        <span style="font-size:12px;color:var(--text-dim);">handed over today</span>
    </div>
    <div class="fq-kpi">
        <span class="fq-kpi-label">Warehouse</span>
        <span style="font-size:17px;font-weight:700;color:var(--text);line-height:1.2">{{ $warehouseName }}</span>
        <span style="font-size:12px;color:var(--text-dim);">your location</span>
    </div>
</div>

{{-- ══════ PENDING TAB ══════ --}}
@if($tab === 'pending')

{{-- Queue --}}
@if($pendingSales->isEmpty())
    <div class="fq-card">
        <div class="fq-empty">
            <svg class="fq-empty-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="fq-empty-title">All clear</div>
            <div style="font-size:13px;">No pending fulfillments for this warehouse.</div>
        </div>
    </div>
@else
    <div style="font-size:12px;color:var(--text-dim);margin-bottom:10px;">
        Click a row to see the exact boxes to hand over.
    </div>

    <div class="fq-card">
        {{-- Table header --}}
        <div style="display:grid;grid-template-columns:1fr 1fr 120px 140px 110px 80px;gap:12px;
                    padding:10px 20px;background:var(--surface);border-bottom:1px solid var(--border);">
            <span style="font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.05em">Sale #</span>
            <span style="font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.05em">Shop</span>
            <span style="font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.05em">Items</span>
            <span style="font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.05em">Method</span>
            <span style="font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.05em">Time</span>
            <span></span>
        </div>

        @foreach($pendingSales as $sale)
            {{-- Main row --}}
            <div class="fq-sale-row" wire:click="toggleExpand({{ $sale->id }})" wire:key="sale-{{ $sale->id }}">
                <span class="fq-sale-number">{{ $sale->sale_number }}</span>
                <span class="fq-sale-shop">{{ $sale->shop?->name ?? '—' }}</span>
                <span style="font-size:13px;color:var(--text-sub);">
                    {{ $sale->items->count() }} box{{ $sale->items->count() !== 1 ? 'es' : '' }}
                </span>
                <span>
                    @if($sale->fulfillment_method === 'transporter')
                        <span class="fq-method-chip chip-transporter">
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
                            </svg>
                            {{ $sale->fulfillmentTransporter?->name ?? 'Transporter' }}
                        </span>
                    @else
                        <span class="fq-method-chip chip-pickup">
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Customer Pickup
                        </span>
                    @endif
                </span>
                <span class="fq-time">{{ $sale->sale_date->diffForHumans() }}</span>
                <span class="fq-actions">
                    <svg class="fq-expand-chevron {{ $expandedSaleId === $sale->id ? 'open' : '' }}" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </span>
            </div>

            {{-- Expanded detail --}}
            @if($expandedSaleId === $sale->id)
                <div class="fq-detail" wire:key="detail-{{ $sale->id }}">
                    <div class="fq-detail-header">Boxes to hand over</div>

                    @php
                        $grouped = $sale->items->groupBy('product_id');
                    @endphp

                    @foreach($grouped as $productId => $items)
                        <div style="margin-bottom:12px;">
                            <div style="font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:6px;">
                                {{ $items->first()->product?->name ?? 'Unknown Product' }}
                                <span style="font-weight:400;color:var(--text-dim);">({{ $items->count() }} box{{ $items->count() !== 1 ? 'es' : '' }})</span>
                            </div>
                            <div class="fq-boxes-grid">
                                @foreach($items as $item)
                                    <div class="fq-box-chip">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--accent)">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                        <span class="fq-box-code">{{ $item->box?->box_code ?? 'N/A' }}</span>
                                        <span class="fq-box-meta">
                                            @if($item->box)
                                                {{ $item->quantity_sold }} items
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    {{-- Notes --}}
                    @if($sale->fulfillment_notes)
                        <div style="background:color-mix(in srgb,var(--amber) 8%,var(--surface));border:1px solid color-mix(in srgb,var(--amber) 20%,var(--border));border-radius:6px;padding:8px 12px;margin-bottom:14px;">
                            <span style="font-size:11px;font-weight:700;color:var(--amber);text-transform:uppercase;letter-spacing:.04em;">Note: </span>
                            <span style="font-size:13px;color:var(--text-sub);">{{ $sale->fulfillment_notes }}</span>
                        </div>
                    @endif

                    <div class="fq-detail-footer">
                        <div>
                            <div class="fq-total-label">Sale Total</div>
                            <div class="fq-total-value">{{ number_format($sale->total) }} RWF</div>
                        </div>
                        <div>
                            @if($sale->customer_name)
                                <div style="font-size:12px;color:var(--text-dim);text-align:right;">
                                    Customer: <span style="color:var(--text-sub);font-weight:600;">{{ $sale->customer_name }}</span>
                                    @if($sale->customer_phone)
                                        · {{ $sale->customer_phone }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Confirm row --}}
            @if($confirmingFulfillmentId === $sale->id)
                <div class="fq-confirm-row" wire:key="confirm-{{ $sale->id }}">
                    <span class="fq-confirm-text">
                        <strong>Confirm handover</strong> — mark all {{ $sale->items->count() }} box{{ $sale->items->count() !== 1 ? 'es' : '' }} as given to {{ $sale->fulfillment_method === 'transporter' ? ($sale->fulfillmentTransporter?->name ?? 'transporter') : 'customer' }}?
                    </span>
                    <div style="display:flex;gap:8px;">
                        <button class="btn-cancel-sm" wire:click="cancelFulfillment">Cancel</button>
                        <button class="btn-confirm-fulfill" wire:click="markFulfilled({{ $sale->id }})">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:-.1em;">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Confirm
                        </button>
                    </div>
                </div>
            @else
                <div style="padding:10px 20px;border-top:1px solid var(--border);background:var(--surface);display:flex;justify-content:flex-end;"
                     wire:key="action-row-{{ $sale->id }}">
                    <button class="btn-fulfill" wire:click="requestFulfillment({{ $sale->id }})">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        Mark Fulfilled
                    </button>
                </div>
            @endif
        @endforeach
    </div>
@endif

@endif {{-- /pending tab --}}

{{-- ══════ HISTORY TAB ══════ --}}
@if($tab === 'history')

@if($fulfilledHistory->isEmpty())
    <div class="fq-card">
        <div class="fq-empty">
            <svg class="fq-empty-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="fq-empty-title">No fulfilled sales yet</div>
            <div style="font-size:13px;">Fulfilled warehouse sales will appear here.</div>
        </div>
    </div>
@else
    <div class="fq-card" style="overflow-x:auto">
        <table class="fq-hist-table">
            <thead>
                <tr>
                    <th class="fq-hist-th">Sale #</th>
                    <th class="fq-hist-th">Shop</th>
                    <th class="fq-hist-th">Items</th>
                    <th class="fq-hist-th">Method</th>
                    <th class="fq-hist-th">Confirmed By</th>
                    <th class="fq-hist-th">Confirmed At</th>
                    <th class="fq-hist-th">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fulfilledHistory as $sale)
                    <tr class="fq-hist-row" wire:key="hist-{{ $sale->id }}">
                        <td class="fq-hist-td">
                            <span style="font-family:var(--mono);font-weight:700;color:var(--text);font-size:12px">
                                {{ $sale->sale_number }}
                            </span>
                        </td>
                        <td class="fq-hist-td">{{ $sale->shop?->name ?? '—' }}</td>
                        <td class="fq-hist-td">
                            {{ $sale->items->count() }} box{{ $sale->items->count() !== 1 ? 'es' : '' }}
                        </td>
                        <td class="fq-hist-td">
                            @if($sale->fulfillment_method === 'transporter')
                                <span class="fq-method-chip chip-transporter">
                                    {{ $sale->fulfillmentTransporter?->name ?? 'Transporter' }}
                                </span>
                            @else
                                <span class="fq-method-chip chip-pickup">Customer Pickup</span>
                            @endif
                        </td>
                        <td class="fq-hist-td">
                            {{ $sale->fulfillmentConfirmedBy?->name ?? '—' }}
                        </td>
                        <td class="fq-hist-td" style="white-space:nowrap">
                            @if($sale->fulfillment_confirmed_at)
                                <span style="font-size:12px;color:var(--text-dim)">
                                    {{ $sale->fulfillment_confirmed_at->format('d M Y, H:i') }}
                                </span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="fq-hist-td" style="font-family:var(--mono);font-weight:700;color:var(--text);white-space:nowrap">
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

</div>
