<div class="sli-root">

{{-- ══════════════════════════════════════════════
     KPI CARDS — react to active date filter
══════════════════════════════════════════════ --}}
<div class="sli-kpi-row">

    {{-- Revenue --}}
    <div class="sli-kpi sli-kpi--blue">
        <div class="sli-kpi-head">
            <div class="sli-kpi-icon" style="background:rgba(59,107,212,.12);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b6bd4" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="sli-kpi-period">{{ $activePeriodLabel }}</span>
        </div>
        <div class="sli-kpi-body">
            <div class="sli-kpi-label">Total Revenue</div>
            <div class="sli-kpi-value">{{ number_format($summaryTotal) }}<span class="sli-kpi-unit">RWF</span></div>
        </div>
        <div class="sli-kpi-bar" style="background:rgba(59,107,212,.15);"><div class="sli-kpi-bar-fill" style="width:100%;background:#3b6bd4;"></div></div>
    </div>

    {{-- Transactions --}}
    <div class="sli-kpi sli-kpi--green">
        <div class="sli-kpi-head">
            <div class="sli-kpi-icon" style="background:rgba(16,185,129,.12);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <span class="sli-kpi-period">{{ $activePeriodLabel }}</span>
        </div>
        <div class="sli-kpi-body">
            <div class="sli-kpi-label">Transactions</div>
            <div class="sli-kpi-value">{{ number_format($summaryCount) }}</div>
        </div>
        <div class="sli-kpi-bar" style="background:rgba(16,185,129,.15);"><div class="sli-kpi-bar-fill" style="width:100%;background:#10b981;"></div></div>
    </div>

    {{-- Avg. Sale --}}
    <div class="sli-kpi sli-kpi--purple">
        <div class="sli-kpi-head">
            <div class="sli-kpi-icon" style="background:rgba(139,92,246,.12);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <span class="sli-kpi-period">{{ $activePeriodLabel }}</span>
        </div>
        <div class="sli-kpi-body">
            <div class="sli-kpi-label">Avg. Transaction</div>
            <div class="sli-kpi-value">{{ number_format($summaryAvg) }}<span class="sli-kpi-unit">RWF</span></div>
        </div>
        <div class="sli-kpi-bar" style="background:rgba(139,92,246,.15);"><div class="sli-kpi-bar-fill" style="width:100%;background:#8b5cf6;"></div></div>
    </div>

    {{-- Cash Collected --}}
    <div class="sli-kpi sli-kpi--teal">
        <div class="sli-kpi-head">
            <div class="sli-kpi-icon" style="background:rgba(29,158,117,.12);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1d9e75" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <span class="sli-kpi-period">{{ $activePeriodLabel }}</span>
        </div>
        <div class="sli-kpi-body">
            <div class="sli-kpi-label">Cash Collected</div>
            <div class="sli-kpi-value">{{ number_format($summaryCash) }}<span class="sli-kpi-unit">RWF</span></div>
        </div>
        @php $cashPct = $summaryTotal > 0 ? min(100, round(($summaryCash / $summaryTotal) * 100)) : 0; @endphp
        <div class="sli-kpi-bar" style="background:rgba(29,158,117,.15);"><div class="sli-kpi-bar-fill" style="width:{{ $cashPct }}%;background:#1d9e75;"></div></div>
    </div>

    {{-- Credit --}}
    <div class="sli-kpi {{ $summaryCredit > 0 ? 'sli-kpi--amber' : 'sli-kpi--neutral' }}">
        <div class="sli-kpi-head">
            <div class="sli-kpi-icon" style="background:{{ $summaryCredit > 0 ? 'rgba(245,158,11,.12)' : 'rgba(0,0,0,.05)' }};">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $summaryCredit > 0 ? '#f59e0b' : 'var(--text-dim)' }}" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="sli-kpi-period">{{ $activePeriodLabel }}</span>
        </div>
        <div class="sli-kpi-body">
            <div class="sli-kpi-label">Credit Issued</div>
            <div class="sli-kpi-value {{ $summaryCredit > 0 ? 'sli-kpi-value--warn' : '' }}">{{ number_format($summaryCredit) }}<span class="sli-kpi-unit">RWF</span></div>
        </div>
        <div class="sli-kpi-bar" style="background:{{ $summaryCredit > 0 ? 'rgba(245,158,11,.15)' : 'rgba(0,0,0,.06)' }};"><div class="sli-kpi-bar-fill" style="width:100%;background:{{ $summaryCredit > 0 ? '#f59e0b' : 'var(--border)' }};"></div></div>
    </div>

</div>

{{-- ══════════════════════════════════════════════
     FILTER PANEL
══════════════════════════════════════════════ --}}
@php
    $payLabels = ['all'=>'All payments','cash'=>'Cash','mobile_money'=>'MoMo','bank_transfer'=>'Bank','credit'=>'Credit','voided'=>'Voided'];
@endphp
<div class="sli-filter-panel" x-data="{ open: false }">

    {{-- ── Search (always visible) ── --}}
    <div class="sli-search-row">
        <div class="sli-search-wrap">
            <svg class="sli-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35" stroke-linecap="round"/></svg>
            <input wire:model.live.debounce.300ms="search"
                   type="text"
                   placeholder="Search sale #, customer name or phone…"
                   class="sli-search-input">
            @if($search)
            <button wire:click="$set('search','')" class="sli-search-clear" title="Clear">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/></svg>
            </button>
            @endif
        </div>
        <div class="sli-result-count" wire:loading.class="sli-loading">
            <span wire:loading.remove>{{ number_format($totalFiltered) }} result{{ $totalFiltered !== 1 ? 's' : '' }}</span>
            <span wire:loading style="display:none">Loading…</span>
        </div>
    </div>

    {{-- ── Mobile: active-chip summary + toggle (hidden on desktop) ── --}}
    <div class="sli-mobile-bar">
        <div class="sli-active-chips">
            <span class="sli-ac sli-ac--blue">{{ $activePeriodLabel }}</span>
            @if($paymentFilter !== 'all')
            <span class="sli-ac sli-ac--dark">{{ $payLabels[$paymentFilter] ?? $paymentFilter }}</span>
            @endif
            @if($search)
            <span class="sli-ac sli-ac--grey">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35" stroke-linecap="round"/></svg>
                Search active
            </span>
            @endif
        </div>
        <button @click="open = !open" class="sli-filter-toggle" :class="{ 'is-open': open }">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="4" y1="6" x2="20" y2="6" stroke-linecap="round"/><line x1="8" y1="12" x2="20" y2="12" stroke-linecap="round"/><line x1="12" y1="18" x2="20" y2="18" stroke-linecap="round"/></svg>
            Filters
            <svg class="sli-chevron" :class="{ 'sli-chevron--up': open }" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </div>

    {{-- ── Filter rows: always visible desktop, dropdown on mobile ── --}}
    <div class="sli-filter-rows" :class="{ 'is-open': open }">
        <div class="sli-filter-divider"></div>

        {{-- Period --}}
        <div class="sli-filter-row">
            <span class="sli-filter-label">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round"/></svg>
                Period
            </span>
            <div class="sli-pills sli-pills--scroll">
                @foreach(['today'=>'Today','yesterday'=>'Yesterday','this_week'=>'This Week','this_month'=>'This Month','last_30'=>'Last 30 Days','all'=>'All Time'] as $key=>$lbl)
                <button wire:click="$set('dateFilter','{{ $key }}')"
                        class="sli-pill sli-pill--date {{ $dateFilter===$key ? 'active' : '' }}">
                    {{ $lbl }}
                </button>
                @endforeach
            </div>
        </div>

        {{-- Payment --}}
        <div class="sli-filter-row">
            <span class="sli-filter-label">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22" stroke-linecap="round"/></svg>
                Payment
            </span>
            <div class="sli-pills sli-pills--wrap">
                @foreach([
                    'all'           => ['All',   ''],
                    'cash'          => ['Cash',  '#0f6e56'],
                    'mobile_money'  => ['MoMo',  '#2a55b8'],
                    'bank_transfer' => ['Bank',  '#6d28d9'],
                    'credit'        => ['Credit','#b45309'],
                    'voided'        => ['Voided','#a32d2d'],
                ] as $key => [$lbl, $col])
                <button wire:click="$set('paymentFilter','{{ $key }}')"
                        class="sli-pill sli-pill--pay {{ $paymentFilter===$key ? 'active' : '' }}"
                        style="{{ ($paymentFilter===$key && $col) ? '--pill-active:'.$col.';' : '' }}">
                    {{ $lbl }}
                </button>
                @endforeach
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════
     TABLE
══════════════════════════════════════════════ --}}
<div class="sli-table-card">

    <div class="sli-table-wrap">
        <table class="sli-table">
            <thead>
                <tr>
                    <th wire:click="sort('sale_number')" class="sli-th sli-th--sortable">
                        <span>Sale #</span>
                        <span class="sli-sort {{ $sortBy==='sale_number' ? ($sortDir==='asc' ? 'asc' : 'desc') : '' }}"></span>
                    </th>
                    <th wire:click="sort('sale_date')" class="sli-th sli-th--sortable">
                        <span>Date &amp; Time</span>
                        <span class="sli-sort {{ $sortBy==='sale_date' ? ($sortDir==='asc' ? 'asc' : 'desc') : '' }}"></span>
                    </th>
                    <th class="sli-th">Customer</th>
                    <th class="sli-th sli-th--center">Items</th>
                    <th class="sli-th">Payment</th>
                    <th wire:click="sort('total')" class="sli-th sli-th--sortable sli-th--right">
                        <span>Amount</span>
                        <span class="sli-sort {{ $sortBy==='total' ? ($sortDir==='asc' ? 'asc' : 'desc') : '' }}"></span>
                    </th>
                    <th class="sli-th sli-th--center">Status</th>
                    <th class="sli-th sli-th--center" style="width:50px;"></th>
                </tr>
            </thead>
            <tbody>
            @forelse($sales as $sale)
                <tr class="sli-row {{ $sale->voided_at ? 'sli-row--voided' : '' }} {{ $expandedId===$sale->id ? 'sli-row--open' : '' }}"
                    wire:key="sale-{{ $sale->id }}">

                    <td class="sli-td sli-td--mono sli-td--num">
                        {{ $sale->sale_number }}
                    </td>

                    <td class="sli-td">
                        <span class="sli-date-d">{{ $sale->sale_date->format('M j, Y') }}</span>
                        <span class="sli-date-t">{{ $sale->sale_date->format('g:i A') }}</span>
                    </td>

                    <td class="sli-td">
                        @if($sale->customer_name || $sale->customer_phone)
                            <span class="sli-cust-name">{{ $sale->customer_name ?: '—' }}</span>
                            @if($sale->customer_phone)<span class="sli-cust-phone">{{ $sale->customer_phone }}</span>@endif
                        @else
                            <span class="sli-walkin">Walk-in</span>
                        @endif
                    </td>

                    <td class="sli-td sli-td--center">
                        <span class="sli-qty-chip">{{ $sale->items->count() }}</span>
                    </td>

                    <td class="sli-td">
                        @php
                            $pm = $sale->payment_method instanceof \App\Enums\PaymentMethod
                                ? $sale->payment_method->value : (string) $sale->payment_method;
                            $pmMeta = [
                                'cash'          => ['Cash',  '#0f6e56', 'rgba(29,158,117,.1)'],
                                'mobile_money'  => ['MoMo',  '#2a55b8', 'rgba(59,107,212,.1)'],
                                'bank_transfer' => ['Bank',  '#6d28d9', 'rgba(124,58,237,.1)'],
                                'credit'        => ['Credit','#b45309', 'rgba(245,158,11,.1)'],
                                'card'          => ['Card',  '#047857', 'rgba(16,185,129,.1)'],
                            ];
                            [$pmLabel, $pmColor, $pmBg] = $pmMeta[$pm] ?? [ucwords(str_replace('_',' ',$pm)), 'var(--text-dim)', 'rgba(0,0,0,.06)'];
                            if ($sale->has_credit && $pm !== 'credit') $pmLabel .= ' + Credit';
                        @endphp
                        <span class="sli-pm-chip" style="color:{{ $pmColor }};background:{{ $pmBg }};">{{ $pmLabel }}</span>
                    </td>

                    <td class="sli-td sli-td--right sli-td--mono sli-td--amount">
                        {{ number_format($sale->total) }}<span class="sli-rwf">RWF</span>
                    </td>

                    <td class="sli-td sli-td--center">
                        @if($sale->voided_at)
                            <span class="sli-status sli-status--voided">Voided</span>
                        @else
                            <span class="sli-status sli-status--ok">Completed</span>
                        @endif
                    </td>

                    <td class="sli-td sli-td--center">
                        <button wire:click="toggleExpand({{ $sale->id }})"
                                class="sli-expand-btn {{ $expandedId===$sale->id ? 'open' : '' }}"
                                title="{{ $expandedId===$sale->id ? 'Collapse' : 'View details' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </td>
                </tr>

                {{-- ── Expanded detail ── --}}
                @if($expandedId === $sale->id && $expandedSale)
                <tr wire:key="detail-{{ $sale->id }}" class="sli-detail-row">
                    <td colspan="8" class="sli-detail-td">
                        <div class="sli-detail-body">
                            <div class="sli-detail-grid">

                                {{-- Items sold --}}
                                <div class="sli-detail-section">
                                    <div class="sli-detail-sec-head">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                        Items Sold
                                    </div>
                                    <table class="sli-inner-tbl">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th class="r">Qty</th>
                                                <th class="r">Unit Price</th>
                                                <th class="r">Line Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($expandedSale->items as $item)
                                        <tr>
                                            <td>{{ $item->product?->name ?? '—' }}</td>
                                            <td class="r mono">{{ number_format($item->quantity_sold) }}</td>
                                            <td class="r mono">{{ number_format($item->actual_unit_price) }} RWF</td>
                                            <td class="r mono bold">{{ number_format($item->line_total) }} RWF</td>
                                        </tr>
                                        @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="r" style="color:var(--text-dim);font-size:11px;">Total</td>
                                                <td class="r mono bold">{{ number_format($expandedSale->total) }} RWF</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                {{-- Payment breakdown --}}
                                <div class="sli-detail-section">
                                    <div class="sli-detail-sec-head">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22" stroke-linecap="round"/></svg>
                                        Payment Breakdown
                                    </div>
                                    @foreach($expandedSale->payments as $pmt)
                                    @php
                                        $pv = $pmt->payment_method instanceof \App\Enums\PaymentMethod ? $pmt->payment_method->value : (string)$pmt->payment_method;
                                        $plabels = ['cash'=>'Cash','mobile_money'=>'Mobile Money','bank_transfer'=>'Bank Transfer','credit'=>'Credit','card'=>'Card'];
                                    @endphp
                                    <div class="sli-pmt-line">
                                        <span class="sli-pmt-name">{{ $plabels[$pv] ?? ucwords(str_replace('_',' ',$pv)) }}</span>
                                        <span class="sli-pmt-val">{{ number_format($pmt->amount) }} RWF</span>
                                    </div>
                                    @endforeach

                                    @if($expandedSale->discount > 0)
                                    <div class="sli-pmt-line sli-pmt-line--discount">
                                        <span class="sli-pmt-name">Discount</span>
                                        <span class="sli-pmt-val" style="color:#e24b4a;">−{{ number_format($expandedSale->discount) }} RWF</span>
                                    </div>
                                    @endif

                                    @if($expandedSale->has_credit && $expandedSale->credit_amount > 0)
                                    <div class="sli-credit-alert">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01" stroke-linecap="round"/></svg>
                                        {{ number_format($expandedSale->credit_amount) }} RWF recorded as credit
                                    </div>
                                    @endif
                                </div>

                                {{-- Sale metadata --}}
                                <div class="sli-detail-section">
                                    <div class="sli-detail-sec-head">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Sale Details
                                    </div>
                                    <div class="sli-meta">
                                        <div class="sli-meta-row">
                                            <span class="sli-meta-k">Sold by</span>
                                            <span class="sli-meta-v">{{ $expandedSale->soldBy?->name ?? '—' }}</span>
                                        </div>
                                        <div class="sli-meta-row">
                                            <span class="sli-meta-k">Sale type</span>
                                            <span class="sli-meta-v">{{ $expandedSale->type instanceof \App\Enums\SaleType ? $expandedSale->type->label() : $expandedSale->type }}</span>
                                        </div>
                                        @if($expandedSale->customer)
                                        <div class="sli-meta-row">
                                            <span class="sli-meta-k">Customer</span>
                                            <span class="sli-meta-v">{{ $expandedSale->customer->name }}</span>
                                        </div>
                                        @endif
                                        @if($expandedSale->has_price_override)
                                        <div class="sli-meta-row">
                                            <span class="sli-meta-k">Price override</span>
                                            <span class="sli-meta-v" style="color:#f59e0b;">{{ $expandedSale->price_override_reason ?? 'No reason given' }}</span>
                                        </div>
                                        @endif
                                        @if($expandedSale->notes)
                                        <div class="sli-meta-row">
                                            <span class="sli-meta-k">Notes</span>
                                            <span class="sli-meta-v">{{ $expandedSale->notes }}</span>
                                        </div>
                                        @endif
                                        @if($expandedSale->voided_at)
                                        <div class="sli-meta-row">
                                            <span class="sli-meta-k">Voided at</span>
                                            <span class="sli-meta-v" style="color:#e24b4a;">{{ $expandedSale->voided_at->format('M j, Y g:i A') }}</span>
                                        </div>
                                        @if($expandedSale->void_reason)
                                        <div class="sli-meta-row">
                                            <span class="sli-meta-k">Reason</span>
                                            <span class="sli-meta-v" style="color:#e24b4a;">{{ $expandedSale->void_reason }}</span>
                                        </div>
                                        @endif
                                        @endif
                                    </div>

                                    <a href="{{ route('shop.sales.receipt', $expandedSale) }}" target="_blank" class="sli-print-btn">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8" rx="1"/></svg>
                                        Print Receipt
                                    </a>
                                </div>

                            </div>
                        </div>
                    </td>
                </tr>
                @endif

            @empty
                <tr>
                    <td colspan="8" class="sli-empty-state">
                        <div class="sli-empty-inner">
                            <div class="sli-empty-icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div class="sli-empty-title">No sales found</div>
                            <div class="sli-empty-sub">Try changing the period or clearing the search filter</div>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Infinite scroll sentinel --}}
    <div wire:ignore
         x-data
         x-ref="sentinel"
         x-init="new IntersectionObserver(([e]) => {
             if (e.isIntersecting && $wire.hasMore) $wire.loadMore();
         }, { rootMargin: '300px' }).observe($refs.sentinel)"
         style="height:2px;"></div>

    {{-- Loading indicator --}}
    <div wire:loading wire:target="loadMore" class="sli-loading-more" style="display:none">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="sli-spin"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
        Loading more…
    </div>

    @if(!$hasMore && $totalFiltered > 20)
    <div class="sli-all-loaded">All {{ number_format($totalFiltered) }} results shown</div>
    @endif

</div>

</div>{{-- /sli-root --}}
