{{-- ┌─────────────────────────────────────────────────────────────────────────┐
    │  Owner · Sales Analytics                                               │
    │  Tabs: Overview · Ledger · Audit · Sellers · Payments · Credit       │
    │  Consistent with .bkpi design system (app.css)                        │
    └─────────────────────────────────────────────────────────────────────────┘ --}}
<div wire:poll.60s>
<style>
/* ── Font size increases for better readability ───────────────────── */

/* Desktop font size increases */
.sa-page-title { font-size:26px !important; }
.sa-page-subtitle { font-size:14px !important; }
.sa-date-btn { font-size:14px !important; padding:6px 16px !important; }
.sa-date-input, .sa-shop-select { font-size:14px !important; }
.sa-tab-btn { font-size:15px !important; }
.sa-tab-btn svg { width:17px !important; height:17px !important; }
.sa-section-title { font-size:16px !important; }
.sa-section-subtitle { font-size:13px !important; }
.sa-table thead th { font-size:12px !important; }
.sa-table tbody td { font-size:14px !important; }
.sa-table tfoot td { font-size:14px !important; }

/* ── Mobile responsive: Sales Analytics ───────────────────────────── */

/* Header: stack controls vertically on mobile */
@media(max-width:640px) {
    .sa-header-controls { flex-direction:column !important; align-items:stretch !important; gap:10px !important; }
    .sa-header-controls > div { flex-wrap:wrap; }
    .sa-header-controls input[type=date] { flex:1; min-width:0; }
    .sa-header-controls select { width:100%; }
    .sa-date-sep { display:none; }

    /* Mobile font sizes */
    .sa-page-title { font-size:24px !important; }
    .sa-page-subtitle { font-size:13px !important; }
    .sa-date-btn { font-size:13px !important; }
}

/* Tab bar: scrollable, equal-width, icons only on very small screens */
@media(max-width:900px) {
    .sa-tab-bar { overflow-x:auto; -webkit-overflow-scrolling:touch; scrollbar-width:none; flex-wrap:nowrap !important; }
    .sa-tab-bar::-webkit-scrollbar { display:none; }
}
@media(max-width:480px) {
    .sa-tab-bar button span.sa-tab-lbl { display:none; }
    .sa-tab-bar button { padding:10px 12px !important; }
}

/* KPI grid: 2-col on mobile */
@media(max-width:640px) {
    .biz-kpi-grid { grid-template-columns:1fr 1fr !important; gap:10px !important; }
    .bkpi { padding:13px 14px !important; }
    .bkpi-value { font-size:22px !important; }
    .bkpi-label { font-size:12px !important; }
}

/* Summary strips: 2-col on mobile, 1-col on very small */
@media(max-width:640px) {
    .sa-strip-wrap { flex-wrap:wrap !important; }
    .sa-strip-wrap > div {
        min-width:calc(50% - 8px) !important;
        flex:none !important;
        padding:12px 14px !important;
    }
    .sa-strip-wrap > div > div:nth-child(1) { font-size:10px !important; }
    .sa-strip-wrap > div > div:nth-child(2) { font-size:20px !important; }
    .sa-strip-wrap > div > div:nth-child(3) { font-size:10px !important; }

    /* Audit header mobile */
    .sa-audit-header { padding:12px 16px !important; }
    .sa-audit-header .sa-section-title { font-size:14px !important; }
    .sa-audit-header .sa-section-subtitle { font-size:10px !important; }
}
@media(max-width:400px) {
    .sa-strip-wrap > div {
        min-width:100% !important;
        padding:14px 16px !important;
    }
    .sa-strip-wrap > div > div:nth-child(2) { font-size:24px !important; }

    /* Customer & Returns stats: single column on very small screens */
    .sa-customer-stats,
    .sa-returns-stats {
        grid-template-columns:1fr !important;
    }
    .sa-customer-stats .sa-stat-item,
    .sa-returns-stats .sa-stat-item {
        border-right:none !important;
    }
    .sa-customer-stats .sa-stat-item > div:first-child,
    .sa-returns-stats .sa-stat-item > div:first-child {
        font-size:20px !important;
    }
    .sa-customer-stats .sa-stat-item > div:last-child,
    .sa-returns-stats .sa-stat-item > div:last-child {
        font-size:10px !important;
    }
}

/* Two-column layout sections: single column on mobile */
@media(max-width:640px) {
    .sa-two-col { grid-template-columns:1fr !important; }
    .sa-two-col-flex { flex-direction:column !important; }

    /* Customer Analysis stats: 2x2 grid on mobile */
    .sa-customer-stats {
        display:grid !important;
        grid-template-columns:1fr 1fr !important;
    }
    .sa-customer-stats .sa-stat-item {
        border-right:1px solid var(--border) !important;
        border-bottom:1px solid var(--border) !important;
        padding:10px 12px !important;
    }
    .sa-customer-stats .sa-stat-item:nth-child(2n) {
        border-right:none !important;
    }
    .sa-customer-stats .sa-stat-item > div:first-child { font-size:16px !important; }
    .sa-customer-stats .sa-stat-item > div:last-child { font-size:9px !important; }

    /* Returns Summary stats: 2x2 grid on mobile */
    .sa-returns-stats {
        display:grid !important;
        grid-template-columns:1fr 1fr !important;
    }
    .sa-returns-stats .sa-stat-item {
        border-right:1px solid var(--border) !important;
        border-bottom:1px solid var(--border) !important;
        padding:10px 12px !important;
        min-width:0 !important;
    }
    .sa-returns-stats .sa-stat-item:nth-child(2n) {
        border-right:none !important;
    }
    .sa-returns-stats .sa-stat-item > div:first-child { font-size:16px !important; }
    .sa-returns-stats .sa-stat-item > div:last-child { font-size:9px !important; }

    /* Customer table: better mobile padding */
    .sa-customer-table th,
    .sa-customer-table td {
        padding:8px 6px !important;
        font-size:11px !important;
    }
    .sa-customer-table th:first-child,
    .sa-customer-table td:first-child {
        padding-left:12px !important;
    }

    /* Returned products: better mobile layout */
    .sa-returned-products {
        padding:12px 14px !important;
    }
    .sa-returned-products .sa-product-row {
        padding:8px 0 !important;
    }
    .sa-returned-products .sa-product-row span:first-child {
        font-size:11px !important;
        word-break:break-word;
    }
    .sa-returned-products .sa-product-row span:last-child {
        font-size:10px !important;
    }
}

/* Tables: horizontal scroll with visual hint */
.sa-table-scroll { overflow-x:auto; -webkit-overflow-scrolling:touch; }

/*
  Mobile card transform for dense tables.
  Tables marked .sa-cards-mob switch to stacked card layout at ≤640px.
  Each <td data-label="…"> renders as a label+value row.
  Columns marked .sa-hide-mob are hidden entirely on mobile.
*/
@media(max-width:640px) {
    .sa-hide-mob { display:none !important; }

    .sa-cards-mob thead { display:none; }
    .sa-cards-mob tfoot { display:none; }
    .sa-cards-mob tbody { display:block; }
    .sa-cards-mob tr    { display:block; padding:11px 14px; border-bottom:1px solid var(--border); }
    .sa-cards-mob tr:last-child { border-bottom:none; }
    .sa-cards-mob td   { display:flex; justify-content:space-between; align-items:center; padding:3px 0; font-size:14px !important; }
    .sa-cards-mob td[data-label]::before {
        content: attr(data-label);
        font-size:11px; font-weight:700; color:var(--text-dim);
        text-transform:uppercase; letter-spacing:.4px; flex-shrink:0; margin-right:8px;
    }
    .sa-cards-mob td.sa-row-title { display:block; padding-bottom:7px; border-bottom:1px solid var(--border); margin-bottom:4px; }
    .sa-cards-mob td.sa-row-title::before { display:none; }

    /* Scorecard mobile - day as header */
    .sa-scorecard-mob td.sa-day-header {
        display:block !important;
        background:var(--bg) !important;
        padding:10px 12px !important;
        border:none !important;
        border-bottom:1px solid var(--border) !important;
        margin:0 -14px 8px -14px !important;
        font-size:13px !important;
        font-weight:700 !important;
    }
    .sa-scorecard-mob td.sa-day-header::before { display:none !important; }

    /* Scorecard mobile - show more data in compact layout */
    .sa-scorecard-mob .sa-day-meta {
        display:flex !important;
        flex-wrap:wrap;
        gap:8px;
        margin-top:6px;
        font-size:11px !important;
        font-weight:500 !important;
        color:var(--text-sub) !important;
    }
    .sa-scorecard-mob .sa-day-meta > span {
        display:inline-flex;
        align-items:center;
        padding:3px 8px;
        background:var(--surface);
        border-radius:6px;
        font-family:var(--mono);
    }

    /* Mobile totals footer (replaces hidden tfoot) */
    .sa-mob-total { display:flex !important; }

    /* Ledger table - keep as horizontal scroll, don't convert to cards */
    .sa-ledger-table { min-width:700px !important; }
    .sa-ledger-table thead { display:table-header-group !important; }
    .sa-ledger-table tbody { display:table-row-group !important; }
    .sa-ledger-table tfoot { display:table-footer-group !important; }
    .sa-ledger-table tr { display:table-row !important; padding:0 !important; }
    .sa-ledger-table th { display:table-cell !important; }
    .sa-ledger-table td { display:table-cell !important; padding:10px 14px !important; }
    .sa-ledger-table td::before { display:none !important; }
    .sa-ledger-table col { display:table-column !important; }

    /* Audit table - keep as horizontal scroll, don't convert to cards */
    .sa-audit-table { min-width:900px !important; }
    .sa-audit-table thead { display:table-header-group !important; }
    .sa-audit-table tbody { display:table-row-group !important; }
    .sa-audit-table tfoot { display:table-footer-group !important; }
    .sa-audit-table tr { display:table-row !important; padding:0 !important; }
    .sa-audit-table th { display:table-cell !important; }
    .sa-audit-table td { display:table-cell !important; padding:10px 14px !important; }
    .sa-audit-table td::before { display:none !important; }
    .sa-audit-table col { display:table-column !important; }
}
@media(min-width:641px) {
    .sa-mob-total { display:none !important; }
}
</style>

{{-- ══════════════════════════════════════════════════════════════════════════
     PAGE HEADER
══════════════════════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:24px;flex-wrap:wrap">
    <div>
        <h1 class="sa-page-title" style="font-size:22px;font-weight:700;color:var(--text);letter-spacing:-0.5px;margin:0 0 4px">
            Sales Analytics
        </h1>
        <div class="sa-page-subtitle" style="font-size:13px;color:var(--text-dim);font-family:var(--mono)">
            {{ $this->activeDateRangeLabel }}
            @if($locationFilter !== 'all')
                · {{ $this->selectedShopName }}
            @endif
            · auto-refreshes every 60s
        </div>
    </div>

    {{-- Date range quick-select --}}
    <div class="sa-header-controls" style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
        @php
            $currentPeriod = 'custom';
            $periods = [
                'today'   => ['label' => 'Today',        'start' => now()->startOfDay()->toDateString()],
                'week'    => ['label' => 'This Week',    'start' => now()->startOfWeek()->toDateString()],
                'month'   => ['label' => 'This Month',   'start' => now()->startOfMonth()->toDateString()],
                'quarter' => ['label' => 'This Quarter', 'start' => now()->startOfQuarter()->toDateString()],
                'year'    => ['label' => 'This Year',    'start' => now()->startOfYear()->toDateString()],
            ];
            foreach ($periods as $key => $period) {
                if ($dateFrom === $period['start'] && $dateTo === now()->toDateString()) {
                    $currentPeriod = $key;
                    break;
                }
            }
        @endphp
        <select wire:change="setDateRange($event.target.value)" class="sa-date-btn"
            style="padding:6px 16px;border-radius:8px;font-size:14px;font-weight:600;border:1px solid var(--border);background:var(--surface);color:var(--text);cursor:pointer">
            <option value="custom" {{ $currentPeriod === 'custom' ? 'selected' : '' }}>Custom Range</option>
            @foreach($periods as $key => $period)
                <option value="{{ $key }}" {{ $currentPeriod === $key ? 'selected' : '' }}>{{ $period['label'] }}</option>
            @endforeach
        </select>

        {{-- Custom date range --}}
        <div style="display:flex;gap:6px;align-items:center">
            <input type="date" wire:model.live="dateFrom" max="{{ $dateTo }}" class="sa-date-input"
                style="padding:5px 10px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:12px;font-family:var(--mono)">
            <span class="sa-date-sep" style="color:var(--text-dim);font-size:12px">→</span>
            <input type="date" wire:model.live="dateTo" min="{{ $dateFrom }}" max="{{ now()->toDateString() }}" class="sa-date-input"
                style="padding:5px 10px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:12px;font-family:var(--mono)">
        </div>

        {{-- Shop filter --}}
        <select wire:model.live="locationFilter" class="sa-shop-select"
            style="padding:5px 10px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:12px">
            <option value="all">All Shops</option>
            @foreach($this->shops as $shop)
                <option value="shop:{{ $shop->id }}">{{ $shop->name }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB BAR
══════════════════════════════════════════════════════════════════════════ --}}
<div class="sa-tab-bar" style="display:flex;gap:2px;border-bottom:2px solid var(--border);margin-bottom:28px">
    @php
        $tabs = [
            'overview' => ['label' => 'Overview',  'icon' => 'M3 3h7v7H3zm11 0h7v7h-7zM3 14h7v7H3zm11 0h7v7h-7z'],
            'ledger'   => ['label' => 'Sales Ledger','icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            'audit'    => ['label' => 'Price Audit', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
            'sellers'  => ['label' => 'Sellers',    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            'payments' => ['label' => 'Payments',   'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
            'credit'   => ['label' => 'Credit',     'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
        ];
    @endphp
    @foreach($tabs as $key => $tab)
        <button type="button" wire:click="setTab('{{ $key }}')" class="sa-tab-btn"
            style="display:flex;align-items:center;gap:7px;padding:10px 18px;border:none;background:none;cursor:pointer;font-size:13px;font-weight:600;
                   color:{{ $activeTab === $key ? 'var(--accent)' : 'var(--text-sub)' }};
                   border-bottom:2px solid {{ $activeTab === $key ? 'var(--accent)' : 'transparent' }};
                   margin-bottom:-2px;transition:color .15s">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="{{ $tab['icon'] }}"/>
            </svg>
            <span class="sa-tab-lbl">{{ $tab['label'] }}</span>
        </button>
    @endforeach
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: OVERVIEW
══════════════════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'overview')

    {{-- ── KPI Row 1: Revenue · Gross Profit · Margin · Items ─────────────── --}}
    @php
        $rev  = $this->revenueKpis;
        $gp   = $this->grossProfitKpis;
        $iss  = $this->itemsSoldKpi;
        $ret  = $this->returnsImpact;
        $vo   = $this->voidedSalesStats;
        $ov   = $this->priceOverrideStats;
    @endphp

    <div class="biz-kpi-grid" style="margin-bottom:24px">

        {{-- Revenue --}}
        <div class="bkpi pink" style="animation:fadeUp .35s ease .05s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon pink">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <span class="bkpi-name">Revenue</span>
                </div>
                @php $rg = $rev['growth_percentage'] @endphp
                <span class="bkpi-pct {{ $rg >= 0 ? 'up' : 'down' }}">
                    {{ $rg >= 0 ? '↑' : '↓' }} {{ abs($rg) }}%
                </span>
            </div>
            <div class="bkpi-value">{{ number_format($rev['total_revenue']) }}</div>
            <div class="bkpi-meta">{{ number_format($rev['transactions_count']) }} transactions · RWF</div>
            <div style="display:flex;gap:0;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--pink);font-family:var(--mono)">{{ number_format($rev['avg_transaction_value']) }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Avg Order</div>
                </div>
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:{{ $rev['total_discount'] > 0 ? 'var(--amber)' : 'var(--text-dim)' }};font-family:var(--mono)">{{ number_format($rev['total_discount']) }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Discounts</div>
                </div>
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--text-dim);font-family:var(--mono)">{{ number_format($rev['previous_revenue']) }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Prev Period</div>
                </div>
            </div>
        </div>

        {{-- Gross Profit --}}
        <div class="bkpi green" style="animation:fadeUp .35s ease .10s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                    </div>
                    <span class="bkpi-name">Gross Profit</span>
                </div>
                @php $gg = $gp['gross_profit_growth'] @endphp
                <span class="bkpi-pct {{ $gg >= 0 ? 'up' : 'down' }}">
                    {{ $gg >= 0 ? '↑' : '↓' }} {{ abs($gg) }}%
                </span>
            </div>
            <div class="bkpi-value" style="color:var(--green)">{{ number_format($gp['gross_profit']) }}</div>
            <div class="bkpi-meta">After cost of goods · RWF</div>
            <div style="display:flex;gap:0;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--green);font-family:var(--mono)">{{ $gp['margin_pct'] }}%</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Margin</div>
                </div>
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--text-dim);font-family:var(--mono)">{{ number_format($gp['total_cost']) }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Total Cost</div>
                </div>
                <div style="flex:1;text-align:center">
                    @php $md = $gp['margin_delta'] @endphp
                    <div style="font-size:11px;font-weight:700;color:{{ $md >= 0 ? 'var(--green)' : 'var(--red)' }};font-family:var(--mono)">
                        {{ $md >= 0 ? '+' : '' }}{{ $md }}pp
                    </div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">vs Prev</div>
                </div>
            </div>
        </div>

        {{-- Net Revenue (after returns) --}}
        <div class="bkpi blue" style="animation:fadeUp .35s ease .15s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <span class="bkpi-name">Net Revenue</span>
                </div>
                @php $rr = $ret['return_rate'] @endphp
                <span class="bkpi-pct {{ $rr > 5 ? 'down' : 'up' }}">
                    {{ $rr }}% returned
                </span>
            </div>
            <div class="bkpi-value">{{ number_format($gp['net_revenue']) }}</div>
            <div class="bkpi-meta">After {{ $ret['returns_count'] }} returns · RWF</div>
            <div style="display:flex;gap:0;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:{{ $ret['returned_revenue'] > 0 ? 'var(--red)' : 'var(--text-dim)' }};font-family:var(--mono)">{{ number_format($ret['returned_revenue']) }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Refunded</div>
                </div>
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--text-dim);font-family:var(--mono)">{{ $ret['exchange_count'] }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Exchanges</div>
                </div>
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--text-dim);font-family:var(--mono)">{{ $ret['items_returned'] }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Items Back</div>
                </div>
            </div>
        </div>

        {{-- Items sold + overrides --}}
        <div class="bkpi violet" style="animation:fadeUp .35s ease .20s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon violet">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    </div>
                    <span class="bkpi-name">Items Sold</span>
                </div>
                @php $ig = $iss['growth'] @endphp
                <span class="bkpi-pct {{ $ig >= 0 ? 'up' : 'down' }}">
                    {{ $ig >= 0 ? '↑' : '↓' }} {{ abs($ig) }}%
                </span>
            </div>
            <div class="bkpi-value">{{ number_format($iss['items_sold']) }}</div>
            <div class="bkpi-meta">Units in period</div>
            <div style="display:flex;gap:0;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:{{ $ov['override_sales_count'] > 0 ? 'var(--amber)' : 'var(--text-dim)' }};font-family:var(--mono)">{{ $ov['override_sales_count'] }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Overrides</div>
                </div>
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:{{ $vo['voided_count'] > 0 ? 'var(--red)' : 'var(--text-dim)' }};font-family:var(--mono)">{{ $vo['voided_count'] }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Voided</div>
                </div>
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--text-dim);font-family:var(--mono)">{{ $ov['override_rate'] }}%</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Override %</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Revenue Trend Chart ──────────────────────────────────────────────── --}}
    @php $trend = $this->revenueTrend @endphp
    @if(count($trend))
    <div wire:key="chart-{{ $dateFrom }}-{{ $dateTo }}-{{ $locationFilter }}" style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:22px 24px;margin-bottom:24px;animation:fadeUp .4s ease .25s both">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <div>
                <div class="sa-section-title" style="font-size:13px;font-weight:700;color:var(--text)">Revenue Trend</div>
                <div class="sa-section-subtitle" style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">Daily revenue · {{ $this->activeDateRangeLabel }}</div>
            </div>
        </div>
        <div id="rev-trend-chart-{{ md5($dateFrom.$dateTo.$locationFilter) }}" style="min-height:240px"></div>
    </div>
    @endif

    {{-- ── Daily Scorecard ──────────────────────────────────────────────────── --}}
    @php
        $scorecard = $this->dailyScorecard;
        // Filter out days with no transactions
        $scorecard = array_filter($scorecard, fn($day) => $day['transactions'] > 0);
    @endphp
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);margin-bottom:24px;animation:fadeUp .4s ease .30s both;overflow:hidden">
        <div style="padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
            <div>
                <div class="sa-section-title" style="font-size:13px;font-weight:700;color:var(--text)">Daily Scorecard</div>
                <div class="sa-section-subtitle" style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">Day-by-day breakdown · revenue, profit, returns</div>
            </div>
        </div>
        <div class="sa-table-scroll">
            <table class="sa-cards-mob sa-scorecard-mob sa-table" style="width:100%;border-collapse:collapse;font-size:12px">
                <thead>
                    <tr style="border-bottom:1px solid var(--border);background:var(--bg)">
                        <th style="padding:9px 16px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Date</th>
                        <th style="padding:9px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Revenue</th>
                        <th class="sa-hide-mob" style="padding:9px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Transactions</th>
                        <th class="sa-hide-mob" style="padding:9px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Items</th>
                        <th style="padding:9px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--green);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Gross Profit</th>
                        <th style="padding:9px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--green);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Margin</th>
                        <th class="sa-hide-mob" style="padding:9px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Discounts</th>
                        <th class="sa-hide-mob" style="padding:9px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--red);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Returns</th>
                        <th style="padding:9px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Net Rev.</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(array_reverse($scorecard) as $day)
                    <tr style="border-bottom:1px solid var(--border);{{ $day['is_today'] ? 'background:rgba(59,111,212,.04)' : '' }};transition:background .1s" onmouseover="this.style.background='rgba(59,111,212,.03)'" onmouseout="this.style.background='{{ $day['is_today'] ? 'rgba(59,111,212,.04)' : 'transparent' }}'">
                        <td class="sa-row-title sa-day-header" style="padding:9px 16px;white-space:nowrap">
                            <div>
                                <span style="font-weight:600;color:{{ $day['is_today'] ? 'var(--accent)' : 'var(--text)' }}">{{ $day['day_label'] }}</span>
                                @if($day['is_today'])<span style="margin-left:6px;font-size:10px;font-weight:700;color:var(--accent);background:var(--accent-dim);padding:1px 6px;border-radius:10px">TODAY</span>@endif
                            </div>
                            <div class="sa-day-meta">
                                @if($day['transactions'] > 0)
                                    <span>{{ $day['transactions'] }} txns</span>
                                @endif
                                @if($day['items_sold'] > 0)
                                    <span>{{ number_format($day['items_sold']) }} items</span>
                                @endif
                                @if($day['gross_profit'] > 0)
                                    <span style="color:var(--green)">{{ number_format($day['gross_profit']) }} GP</span>
                                @endif
                                @if($day['discounts'] > 0)
                                    <span style="color:var(--amber)">{{ number_format($day['discounts']) }} disc</span>
                                @endif
                                @if($day['returns_count'] > 0)
                                    <span style="color:var(--red)">{{ $day['returns_count'] }} returns</span>
                                @endif
                            </div>
                        </td>
                        <td data-label="Revenue" style="padding:9px 14px;text-align:right;font-family:var(--mono);font-weight:600;color:{{ $day['revenue'] > 0 ? 'var(--text)' : 'var(--text-dim)' }}">
                            {{ $day['revenue'] > 0 ? number_format($day['revenue']) : '—' }}
                        </td>
                        <td class="sa-hide-mob" data-label="Txns" style="padding:9px 14px;text-align:right;font-family:var(--mono);color:var(--text-sub)">
                            {{ $day['transactions'] > 0 ? $day['transactions'] : '—' }}
                        </td>
                        <td class="sa-hide-mob" data-label="Items" style="padding:9px 14px;text-align:right;font-family:var(--mono);color:var(--text-sub)">
                            {{ $day['items_sold'] > 0 ? number_format($day['items_sold']) : '—' }}
                        </td>
                        <td data-label="Profit" style="padding:9px 14px;text-align:right;font-family:var(--mono);font-weight:600;color:var(--green)">
                            {{ $day['gross_profit'] > 0 ? number_format($day['gross_profit']) : '—' }}
                        </td>
                        <td data-label="Margin" style="padding:9px 14px;text-align:right">
                            @if($day['margin_pct'] > 0)
                                <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:10px;font-family:var(--mono);
                                    background:{{ $day['margin_pct'] >= 30 ? 'var(--green-glow)' : ($day['margin_pct'] >= 15 ? 'rgba(251,191,36,.12)' : 'rgba(225,29,72,.08)') }};
                                    color:{{ $day['margin_pct'] >= 30 ? 'var(--green)' : ($day['margin_pct'] >= 15 ? 'var(--amber)' : 'var(--red)') }}">
                                    {{ $day['margin_pct'] }}%
                                </span>
                            @else
                                <span style="color:var(--text-dim)">—</span>
                            @endif
                        </td>
                        <td class="sa-hide-mob" data-label="Discounts" style="padding:9px 14px;text-align:right;font-family:var(--mono);color:{{ $day['discounts'] > 0 ? 'var(--amber)' : 'var(--text-dim)' }}">
                            {{ $day['discounts'] > 0 ? number_format($day['discounts']) : '—' }}
                        </td>
                        <td class="sa-hide-mob" data-label="Returns" style="padding:9px 14px;text-align:right;font-family:var(--mono);color:{{ $day['returns_count'] > 0 ? 'var(--red)' : 'var(--text-dim)' }}">
                            {{ $day['returns_count'] > 0 ? $day['returns_count'] . ' · ' . number_format($day['returned_amount']) : '—' }}
                        </td>
                        <td data-label="Net Rev." style="padding:9px 14px;text-align:right;font-family:var(--mono);font-weight:600;color:var(--text)">
                            {{ $day['net_revenue'] > 0 ? number_format($day['net_revenue']) : '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" style="padding:32px;text-align:center;color:var(--text-dim)">No sales in this period</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Bottom Row: Top Products + Shop Breakdown + Payment Methods ─────── --}}
    <div class="sa-two-col" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">

        {{-- Top products --}}
        @php $topProducts = $this->topProducts @endphp
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;animation:fadeUp .4s ease .35s both">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
                <div class="sa-section-title" style="font-size:13px;font-weight:700;color:var(--text)">Top Products</div>
                <div class="sa-section-subtitle" style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">By revenue · with margin</div>
            </div>
            <div style="overflow:auto;max-height:340px">
                <table style="width:100%;border-collapse:collapse;font-size:12px">
                    <thead style="position:sticky;top:0;background:var(--bg);z-index:1">
                        <tr style="border-bottom:1px solid var(--border)">
                            <th style="padding:8px 16px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Product</th>
                            <th style="padding:8px 10px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Revenue</th>
                            <th style="padding:8px 10px;text-align:right;font-size:10px;font-weight:700;color:var(--green);letter-spacing:.5px;text-transform:uppercase">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topProducts as $i => $p)
                        <tr style="border-bottom:1px solid var(--border)" onmouseover="this.style.background='rgba(59,111,212,.03)'" onmouseout="this.style.background='transparent'">
                            <td style="padding:8px 16px">
                                <div style="font-weight:600;color:var(--text)">{{ $p['product_name'] }}</div>
                                <div style="font-size:10px;color:var(--text-dim);font-family:var(--mono);margin-top:1px">{{ number_format($p['quantity_sold']) }} units · {{ $p['revenue_share'] }}% share</div>
                            </td>
                            <td style="padding:8px 10px;text-align:right;font-family:var(--mono);font-size:11px;font-weight:600;color:var(--text)">{{ number_format($p['revenue']) }}</td>
                            <td style="padding:8px 10px;text-align:right">
                                <span style="font-size:11px;font-weight:700;font-family:var(--mono);
                                    color:{{ $p['margin_pct'] >= 30 ? 'var(--green)' : ($p['margin_pct'] >= 15 ? 'var(--amber)' : 'var(--red)') }}">
                                    {{ $p['margin_pct'] }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Shop performance + payment methods stacked --}}
        <div style="display:flex;flex-direction:column;gap:20px">

            {{-- Shop performance --}}
            @php $shops = $this->shopPerformance @endphp
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;animation:fadeUp .4s ease .40s both">
                <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
                    <div class="sa-section-title" style="font-size:13px;font-weight:700;color:var(--text)">Shop Performance</div>
                    <div class="sa-section-subtitle" style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">Revenue share and growth per shop</div>
                </div>
                @php $maxShopRev = max(array_column($shops, 'revenue') ?: [1]) @endphp
                @foreach($shops as $shop)
                <div style="padding:12px 20px;border-bottom:1px solid var(--border)">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                        <span style="font-size:12px;font-weight:600;color:var(--text)">{{ $shop['shop_name'] }}</span>
                        <div style="display:flex;align-items:center;gap:8px">
                            <span style="font-size:11px;font-family:var(--mono);font-weight:600;color:var(--text)">{{ number_format($shop['revenue']) }}</span>
                            @if($shop['growth'] !== null)
                                <span style="font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;font-family:var(--mono);
                                    background:{{ $shop['growth'] >= 0 ? 'var(--green-glow)' : 'rgba(225,29,72,.08)' }};
                                    color:{{ $shop['growth'] >= 0 ? 'var(--green)' : 'var(--red)' }}">
                                    {{ $shop['growth'] >= 0 ? '+' : '' }}{{ $shop['growth'] }}%
                                </span>
                            @endif
                        </div>
                    </div>
                    <div style="height:5px;background:var(--bg);border-radius:3px;overflow:hidden">
                        <div style="height:100%;width:{{ $maxShopRev > 0 ? round($shop['revenue'] / $maxShopRev * 100) : 0 }}%;background:linear-gradient(90deg,var(--accent),var(--violet));border-radius:3px;transition:width .6s ease"></div>
                    </div>
                    <div style="display:flex;gap:12px;margin-top:5px">
                        <span style="font-size:10px;color:var(--text-dim)">{{ $shop['transactions'] }} txn</span>
                        <span style="font-size:10px;color:var(--text-dim)">{{ $shop['revenue_share'] }}% share</span>
                        @if($shop['override_count'] > 0)
                        <span style="font-size:10px;color:var(--amber)">{{ $shop['override_count'] }} overrides</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Payment methods --}}
            @php $methods = $this->paymentMethods @endphp
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;animation:fadeUp .4s ease .45s both">
                <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
                    <div class="sa-section-title" style="font-size:13px;font-weight:700;color:var(--text)">Payment Methods</div>
                </div>
                <div style="padding:12px 20px">
                    @foreach($methods as $m)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border);' : '' }}">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="width:8px;height:8px;border-radius:50%;background:{{ match($loop->index % 4) { 0=>'var(--accent)', 1=>'var(--green)', 2=>'var(--pink)', default=>'var(--violet)' } }}"></div>
                            <span style="font-size:12px;font-weight:600;color:var(--text)">{{ $m['label'] }}</span>
                        </div>
                        <div style="text-align:right">
                            <div style="font-size:12px;font-family:var(--mono);font-weight:600;color:var(--text)">{{ number_format($m['revenue']) }}</div>
                            <div style="font-size:10px;color:var(--text-dim)">{{ $m['count'] }} · {{ $m['revenue_share'] }}%</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    {{-- ── Returns Impact detail ────────────────────────────────────────────── --}}
    @if(count($ret['top_returned_products']))
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;margin-bottom:24px;animation:fadeUp .4s ease .50s both">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px">
            <div style="width:32px;height:32px;border-radius:8px;background:rgba(225,29,72,.10);display:grid;place-items:center;color:var(--red)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </div>
            <div>
                <div class="sa-section-title" style="font-size:13px;font-weight:700;color:var(--text)">Returns Impact</div>
                <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono)">{{ $ret['returns_count'] }} returns · {{ number_format($ret['returned_revenue']) }} RWF refunded · {{ $ret['return_rate'] }}% of gross revenue</div>
            </div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:0">
            @foreach($ret['top_returned_products'] as $rp)
            <div style="padding:10px 20px;border-right:1px solid var(--border);border-bottom:1px solid var(--border);min-width:160px">
                <div style="font-size:12px;font-weight:600;color:var(--text)">{{ $rp['product_name'] }}</div>
                <div style="font-size:11px;color:var(--red);font-family:var(--mono);margin-top:2px">{{ $rp['qty_returned'] }} units returned</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: SALES LEDGER
══════════════════════════════════════════════════════════════════════════ --}}
@elseif($activeTab === 'ledger')

    @php
        $gp   = $this->grossProfitKpis;
        $iss  = $this->itemsSoldKpi;
        $rev  = $this->revenueKpis;
        $topP = $this->topProducts;
        // ── Credit figures for the Ledger strip ──────────────────────────────────
        $lcShopId = $locationFilter !== 'all'
            ? (int) str_replace('shop:', '', $locationFilter)
            : null;

        // Period credit: what was sold on credit in the selected date range
        // (matches the sum of credit_revenue column across all product rows)
        $periodCreditQ = \App\Models\Sale::whereNull('voided_at')
            ->where('has_credit', true)
            ->whereBetween('sale_date', [$dateFrom, $dateTo]);
        if ($lcShopId) {
            $periodCreditQ->where('shop_id', $lcShopId);
        }
        $periodCreditGiven = (int) $periodCreditQ->sum('credit_amount');

        // All-time outstanding: current unpaid balance across all customers
        $custQ = \App\Models\Customer::query();
        if ($lcShopId) {
            $custQ->where('shop_id', $lcShopId);
        }
        $trueOutstanding   = (int) (clone $custQ)->sum('outstanding_balance');
        $totalCreditRepaid = (int) (clone $custQ)->sum('total_repaid');
        $totalCreditGiven  = (int) (clone $custQ)->sum('total_credit_given');
        $repaymentRate     = $totalCreditGiven > 0
            ? round(($totalCreditRepaid / $totalCreditGiven) * 100, 1)
            : 0;
    @endphp

    {{-- Summary strip --}}
    <div class="sa-strip-wrap" style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:20px">
        @foreach([
            ['label'=>'Gross Revenue',  'value'=>number_format($gp['revenue']),      'color'=>'var(--accent)',  'sub'=>$rev['transactions_count'].' transactions'],
            ['label'=>'Total Cost',     'value'=>number_format($gp['total_cost']),   'color'=>'var(--text-sub)','sub'=>'Cost of goods'],
            ['label'=>'Gross Profit',   'value'=>number_format($gp['gross_profit']), 'color'=>'var(--green)',   'sub'=>$gp['margin_pct'].'% margin'],
            ['label'=>'Items Sold',     'value'=>number_format($iss['items_sold']),  'color'=>'var(--violet)',  'sub'=>'Units'],
            ['label' => 'Outstanding Credit',
             'value' => number_format($trueOutstanding),
             'color' => 'var(--amber)',
             'sub'   => number_format($totalCreditGiven) . ' given · '
                      . number_format($totalCreditRepaid) . ' repaid · '
                      . $repaymentRate . '%'],
        ] as $strip)
        <div style="flex:1;min-width:140px;background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:14px 18px">
            <div style="font-size:11px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;margin-bottom:6px">{{ $strip['label'] }}</div>
            <div style="font-size:22px;font-weight:700;letter-spacing:-0.5px;color:{{ $strip['color'] }}">{{ $strip['value'] }}</div>
            <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:3px">{{ $strip['sub'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Credit footnote: explains gross vs net distinction --}}
    @if($trueOutstanding > 0 || $totalCreditGiven > 0)
    <div style="display:flex;align-items:center;gap:8px;padding:8px 14px;
                background:rgba(217,119,6,.06);border:1px solid rgba(217,119,6,.2);
                border-radius:8px;margin-bottom:16px;margin-top:-4px">
        <span style="font-size:14px;flex-shrink:0">ℹ️</span>
        <div style="font-size:11px;color:var(--text-sub);line-height:1.5">
            <strong style="color:#d97706">Outstanding Credit</strong>
            ({{ number_format($trueOutstanding) }} RWF) = current unpaid balance
            across all customers after {{ number_format($totalCreditRepaid) }} RWF
            repaid ({{ $repaymentRate }}% repayment rate).
            &nbsp;·&nbsp;
            The <strong style="color:#d97706">Credit Sales</strong> column shows
            each product's proportional share of credit given in the selected period.
        </div>
    </div>
    @endif

    {{-- Full product ledger table --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
            <div>
                <div class="sa-section-title" style="font-size:13px;font-weight:700;color:var(--text)">Product Sales Ledger</div>
                <div class="sa-section-subtitle" style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">Revenue, cost, and gross profit per product · {{ $this->activeDateRangeLabel }}</div>
            </div>
            <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim)">{{ count($topP) }} products</span>
        </div>
        <div class="sa-table-scroll">
            <table class="sa-ledger-table sa-table" style="min-width:900px;width:100%;border-collapse:collapse;font-size:12px;table-layout:fixed">
                <colgroup>
                    <col class="sa-hide-mob" style="width:60px">
                    <col style="width:280px">
                    <col style="width:100px">
                    <col style="width:100px">
                    <col class="sa-hide-mob" style="width:120px">
                    <col style="width:120px">
                    <col class="sa-hide-mob" style="width:120px">
                    <col style="width:140px">
                    <col style="width:120px">
                    <col style="width:120px">
                </colgroup>
                <thead>
                    <tr style="background:var(--bg);border-bottom:1px solid var(--border)">
                        <th class="sa-hide-mob" style="padding:10px 12px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">#</th>
                        <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Product</th>
                        <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Units</th>
                        <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Txns</th>
                        <th class="sa-hide-mob" style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Avg Price</th>
                        <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Revenue</th>
                        <th class="sa-hide-mob" style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Share</th>
                        <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--green);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Gross Profit</th>
                        <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--green);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Margin %</th>
                        <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;
                                   color:#d97706;letter-spacing:.5px;text-transform:uppercase;
                                   white-space:nowrap"
                            title="Gross credit sales per product. Repayments are tracked at customer level and cannot be attributed to specific products.">
                            Credit Sales
                            <span style="display:block;font-size:9px;font-weight:400;color:var(--text-dim);
                                         text-transform:none;letter-spacing:0">gross · pre-repayment</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topP as $i => $p)
                    <tr style="border-bottom:1px solid var(--border);transition:background .1s" onmouseover="this.style.background='rgba(59,111,212,.03)'" onmouseout="this.style.background='transparent'">
                        <td class="sa-hide-mob" style="padding:10px 12px;font-size:11px;color:var(--text-dim);font-family:var(--mono)">{{ $i + 1 }}</td>
                        <td style="padding:10px 14px;overflow:hidden;text-overflow:ellipsis">
                            <div style="font-weight:600;color:var(--text);white-space:normal;word-break:break-word">{{ $p['product_name'] }}</div>
                        </td>
                        <td style="padding:10px 14px;text-align:right;font-family:var(--mono);color:var(--text-sub);white-space:nowrap">{{ number_format($p['quantity_sold']) }}</td>
                        <td style="padding:10px 14px;text-align:right;font-family:var(--mono);color:var(--text-sub);white-space:nowrap">{{ $p['transaction_count'] }}</td>
                        <td class="sa-hide-mob" style="padding:10px 14px;text-align:right;font-family:var(--mono);color:var(--text-sub);white-space:nowrap">{{ number_format($p['avg_selling_price']) }}</td>
                        <td style="padding:10px 14px;text-align:right;font-family:var(--mono);font-weight:700;color:var(--text);white-space:nowrap">{{ number_format($p['revenue']) }}</td>
                        <td class="sa-hide-mob" style="padding:10px 14px;text-align:right;white-space:nowrap">
                            <div style="height:4px;background:var(--bg);border-radius:2px;width:60px;display:inline-block;vertical-align:middle;margin-right:6px">
                                <div style="height:100%;width:{{ $p['revenue_share'] }}%;background:var(--accent);border-radius:2px"></div>
                            </div>
                            <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim)">{{ $p['revenue_share'] }}%</span>
                        </td>
                        <td style="padding:10px 14px;text-align:right;font-family:var(--mono);font-weight:700;color:var(--green);white-space:nowrap">{{ number_format($p['gross_profit']) }}</td>
                        <td style="padding:10px 14px;text-align:right;white-space:nowrap">
                            <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:10px;font-family:var(--mono);
                                background:{{ $p['margin_pct'] >= 30 ? 'var(--green-glow)' : ($p['margin_pct'] >= 15 ? 'rgba(251,191,36,.12)' : 'rgba(225,29,72,.08)') }};
                                color:{{ $p['margin_pct'] >= 30 ? 'var(--green)' : ($p['margin_pct'] >= 15 ? 'var(--amber)' : 'var(--red)') }}">
                                {{ $p['margin_pct'] }}%
                            </span>
                        </td>
                        <td style="padding:10px 14px;text-align:right;font-family:var(--mono);
                                   font-size:12px;white-space:nowrap;
                                   color:{{ $p['credit_revenue'] > 0 ? '#d97706' : 'var(--text-dim)' }}">
                            @if($p['credit_revenue'] > 0)
                                {{ number_format($p['credit_revenue']) }}
                                <span style="font-size:10px;color:#d97706;margin-left:2px">
                                    {{ $p['credit_pct'] }}%
                                </span>
                            @else
                                <span style="color:var(--text-dim)">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" style="padding:40px;text-align:center;color:var(--text-dim)">No sales in this period</td></tr>
                    @endforelse
                </tbody>
                @if(count($topP))
                <tfoot>
                    <tr style="background:var(--bg);border-top:2px solid var(--border)">
                        <td class="sa-hide-mob" style="padding:10px 12px"></td>
                        <td colspan="4" style="padding:10px 14px;font-size:12px;font-weight:700;color:var(--text-sub)">TOTALS</td>
                        <td style="padding:10px 14px;text-align:right;font-family:var(--mono);font-weight:700;color:var(--text);white-space:nowrap">{{ number_format(array_sum(array_column($topP, 'revenue'))) }}</td>
                        <td class="sa-hide-mob" style="padding:10px 14px;text-align:right;font-size:11px;font-family:var(--mono);color:var(--text-dim)">100%</td>
                        <td style="padding:10px 14px;text-align:right;font-family:var(--mono);font-weight:700;color:var(--green);white-space:nowrap">{{ number_format(array_sum(array_column($topP, 'gross_profit'))) }}</td>
                        <td style="padding:10px 14px;text-align:right;font-size:12px;font-weight:700;font-family:var(--mono);color:var(--green);white-space:nowrap">{{ $gp['margin_pct'] }}%</td>
                        <td style="padding:10px 14px;text-align:right;font-size:12px;font-weight:700;
                                   font-family:var(--mono);white-space:nowrap;color:#d97706">
                            @php $totalCredit = collect($topP)->sum('credit_revenue'); @endphp
                            {{ $totalCredit > 0 ? number_format($totalCredit) : '—' }}
                            @if($totalCredit > 0)
                                <span style="display:block;font-size:10px;font-weight:400;
                                             color:var(--text-dim);font-family:var(--font)">
                                    gross sales
                                </span>
                            @endif
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: PRICE AUDIT
══════════════════════════════════════════════════════════════════════════ --}}
@elseif($activeTab === 'audit')

    @php
        $auditLog  = $this->priceAuditLog;
        $overStat  = $this->priceOverrideStats;
        $totalDisc = array_sum(array_column($auditLog, 'total_discount'));
    @endphp

    {{-- Summary strip --}}
    <div class="sa-strip-wrap" style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:20px">
        @foreach([
            ['label'=>'Overridden Sales',    'value'=>$overStat['override_sales_count'],  'color'=>'var(--amber)',   'sub'=>'Sales with price changes'],
            ['label'=>'Overridden Items',    'value'=>$overStat['override_items_count'],  'color'=>'var(--text)',    'sub'=>'Line items modified'],
            ['label'=>'Total Discount Given','value'=>number_format($overStat['total_discount_given']),'color'=>'var(--red)','sub'=>'Revenue given away · RWF'],
            ['label'=>'Override Rate',       'value'=>$overStat['override_rate'].'%',     'color'=>'var(--text-sub)','sub'=>'Of all non-voided sales'],
        ] as $strip)
        <div style="flex:1;min-width:140px;background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:14px 18px">
            <div style="font-size:11px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;margin-bottom:6px">{{ $strip['label'] }}</div>
            <div style="font-size:22px;font-weight:700;letter-spacing:-0.5px;color:{{ $strip['color'] }}">{{ $strip['value'] }}</div>
            <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:3px">{{ $strip['sub'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Audit table --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden">
        <div class="sa-audit-header" style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
            <div>
                <div class="sa-section-title" style="font-size:13px;font-weight:700;color:var(--text)">Price Modification Audit Trail</div>
                <div class="sa-section-subtitle" style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">Every price change in the period · who, what, when, how much</div>
            </div>
            <span style="font-size:11px;font-family:var(--mono);color:{{ count($auditLog) > 0 ? 'var(--amber)' : 'var(--text-dim)' }}">
                {{ count($auditLog) }} modifications
            </span>
        </div>
        <div class="sa-table-scroll">
            <table class="sa-audit-table sa-table" style="min-width:1000px;width:100%;border-collapse:collapse;font-size:12px;table-layout:fixed">
                <colgroup>
                    <col style="width:140px">
                    <col style="width:240px">
                    <col style="width:160px">
                    <col style="width:90px">
                    <col style="width:110px">
                    <col style="width:110px">
                    <col style="width:110px">
                    <col style="width:100px">
                    <col style="width:180px">
                    <col style="width:120px">
                </colgroup>
                <thead>
                    <tr style="background:var(--bg);border-bottom:1px solid var(--border)">
                        <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Date & Time</th>
                        <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Sale # / Product</th>
                        <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Shop / Seller</th>
                        <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Qty</th>
                        <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Original</th>
                        <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Actual</th>
                        <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--red);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Discount</th>
                        <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:var(--green);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Margin</th>
                        <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Reason</th>
                        <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Approved</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLog as $entry)
                    <tr style="border-bottom:1px solid var(--border);transition:background .1s" onmouseover="this.style.background='rgba(59,111,212,.03)'" onmouseout="this.style.background='transparent'">
                        <td style="padding:10px 14px;white-space:nowrap">
                            <div style="font-family:var(--mono);font-size:12px;color:var(--text);font-weight:600">{{ \Carbon\Carbon::parse($entry['sale_date'])->format('M d, Y') }}</div>
                            <div style="font-family:var(--mono);font-size:11px;color:var(--text-dim);margin-top:2px">{{ \Carbon\Carbon::parse($entry['sale_date'])->format('H:i') }}</div>
                        </td>
                        <td style="padding:10px 14px">
                            <div style="font-size:11px;font-family:var(--mono);font-weight:600;color:var(--accent);margin-bottom:3px">{{ $entry['sale_number'] }}</div>
                            <div style="font-size:12px;color:var(--text);white-space:normal;word-break:break-word">
                                {{ $entry['product_name'] }}
                                @if($entry['line_count'] > 1)
                                    <span style="margin-left:4px;padding:1px 6px;background:var(--accent-glow);color:var(--accent);border-radius:10px;font-size:10px;font-weight:700;font-family:var(--mono)">×{{ $entry['line_count'] }}</span>
                                @endif
                            </div>
                        </td>
                        <td style="padding:10px 14px">
                            <div style="font-size:11px;color:var(--text-sub);margin-bottom:2px">{{ $entry['shop_name'] }}</div>
                            <div style="font-size:12px;font-weight:600;color:var(--text)">{{ $entry['seller_name'] }}</div>
                        </td>
                        <td style="padding:10px 14px;text-align:right;font-family:var(--mono);font-size:12px;color:var(--text-sub);white-space:nowrap">{{ $entry['quantity_display'] }}</td>
                        <td style="padding:10px 14px;text-align:right;font-family:var(--mono);font-size:12px;color:var(--text-sub);white-space:nowrap">{{ number_format($entry['original_unit_price']) }}</td>
                        <td style="padding:10px 14px;text-align:right;font-family:var(--mono);font-size:12px;font-weight:700;color:var(--text);white-space:nowrap">{{ number_format($entry['actual_unit_price']) }}</td>
                        <td style="padding:10px 14px;text-align:right;white-space:nowrap">
                            <div style="font-family:var(--mono);font-size:12px;font-weight:700;color:var(--red)">{{ number_format($entry['total_discount']) }}</div>
                            <div style="font-size:10px;color:var(--text-dim);font-family:var(--mono)">{{ $entry['discount_pct'] }}% off</div>
                        </td>
                        <td style="padding:10px 14px;text-align:right;white-space:nowrap">
                            @if($entry['margin_at_sale'] > 0)
                                <span style="font-size:12px;font-weight:700;font-family:var(--mono);
                                    color:{{ $entry['margin_at_sale'] >= 20 ? 'var(--green)' : ($entry['margin_at_sale'] >= 5 ? 'var(--amber)' : 'var(--red)') }}">
                                    {{ $entry['margin_at_sale'] }}%
                                </span>
                            @else
                                <span style="color:var(--red);font-size:12px;font-family:var(--mono);font-weight:700">{{ $entry['margin_at_sale'] }}%</span>
                            @endif
                        </td>
                        <td style="padding:10px 14px;font-size:11px;color:var(--text-sub)">
                            <div style="white-space:normal;word-break:break-word">{{ $entry['reason'] ?? '—' }}</div>
                            @if($entry['reference'])
                                <div style="font-size:10px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">Ref: {{ $entry['reference'] }}</div>
                            @endif
                        </td>
                        <td style="padding:10px 14px;white-space:nowrap">
                            @if($entry['is_approved'])
                                <span style="font-size:11px;font-weight:700;padding:3px 8px;border-radius:10px;background:var(--green-glow);color:var(--green)">✓ {{ $entry['approved_by'] }}</span>
                            @else
                                <span style="font-size:11px;font-weight:700;padding:3px 8px;border-radius:10px;background:rgba(251,191,36,.12);color:var(--amber)">Pending</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" style="padding:48px;text-align:center;color:var(--text-dim)">No price modifications in this period</td></tr>
                    @endforelse
                </tbody>
                @if(count($auditLog))
                <tfoot>
                    <tr style="background:var(--bg);border-top:2px solid var(--border)">
                        <td colspan="6" style="padding:10px 14px;font-size:12px;font-weight:700;color:var(--text-sub)">TOTAL DISCOUNT GIVEN</td>
                        <td style="padding:10px 14px;text-align:right;font-family:var(--mono);font-weight:700;color:var(--red);white-space:nowrap">{{ number_format($totalDisc) }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: SELLERS
══════════════════════════════════════════════════════════════════════════ --}}
@elseif($activeTab === 'sellers')

    @php
        $sellers   = $this->sellerPerformance;
        $customers = $this->customerRepeatAnalysis;
        $ret       = $this->returnsImpact;
    @endphp

    {{-- Seller performance table --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;margin-bottom:24px">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
            <div>
                <div class="sa-section-title" style="font-size:13px;font-weight:700;color:var(--text)">Seller Performance</div>
                <div class="sa-section-subtitle" style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">{{ $this->activeDateRangeLabel }} · ranked by revenue</div>
            </div>
            <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim)">{{ count($sellers) }} sellers</span>
        </div>
        <div class="sa-table-scroll">
            <table class="sa-cards-mob sa-table" style="width:100%;border-collapse:collapse;font-size:12px">
                <thead>
                    <tr style="background:var(--bg);border-bottom:1px solid var(--border)">
                        <th class="sa-hide-mob" style="padding:10px 16px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">#</th>
                        <th style="padding:10px 12px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Seller</th>
                        <th class="sa-hide-mob" style="padding:10px 12px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Shop</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Txns</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Revenue</th>
                        <th class="sa-hide-mob" style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Share</th>
                        <th class="sa-hide-mob" style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Avg Order</th>
                        <th class="sa-hide-mob" style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Items</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--green);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">GP</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--green);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Margin</th>
                        <th class="sa-hide-mob" style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--amber);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Discounts</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--amber);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Overrides</th>
                        <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:var(--red);letter-spacing:.5px;text-transform:uppercase;white-space:nowrap">Voided</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sellers as $i => $s)
                    @php
                        $isTop = $i === 0;
                        $hasWarnings = $s['override_count'] > 3 || $s['void_count'] > 2;
                    @endphp
                    <tr style="border-bottom:1px solid var(--border);{{ $isTop ? 'background:rgba(14,158,134,.03)' : '' }};transition:background .1s"
                        onmouseover="this.style.background='rgba(59,111,212,.03)'"
                        onmouseout="this.style.background='{{ $isTop ? 'rgba(14,158,134,.03)' : 'transparent' }}'">
                        <td class="sa-hide-mob" style="padding:10px 16px;font-size:11px;font-family:var(--mono);color:var(--text-dim)">{{ $i + 1 }}</td>
                        <td class="sa-row-title" style="padding:10px 12px">
                            <div style="display:flex;align-items:center;gap:7px">
                                <div style="width:28px;height:28px;border-radius:50%;display:grid;place-items:center;font-size:11px;font-weight:700;
                                    background:{{ $isTop ? 'var(--green-glow)' : 'var(--bg)' }};
                                    color:{{ $isTop ? 'var(--green)' : 'var(--text-sub)' }};border:1px solid var(--border)">
                                    {{ strtoupper(substr($s['seller_name'], 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:600;color:var(--text);font-size:12px">{{ $s['seller_name'] }}</div>
                                    @if($isTop)<div style="font-size:10px;color:var(--green)">Top seller</div>@endif
                                    <div style="font-size:10px;color:var(--text-dim);margin-top:2px">{{ $s['shop_name'] }} · {{ $s['transactions'] }} txns · {{ $s['revenue_share'] }}% share</div>
                                </div>
                            </div>
                        </td>
                        <td class="sa-hide-mob" style="padding:10px 12px;font-size:11px;color:var(--text-sub)">{{ $s['shop_name'] }}</td>
                        <td data-label="Txns" style="padding:10px 12px;text-align:right;font-family:var(--mono);color:var(--text-sub)">{{ $s['transactions'] }}</td>
                        <td data-label="Revenue" style="padding:10px 12px;text-align:right;font-family:var(--mono);font-weight:700;color:var(--text)">{{ number_format($s['revenue']) }}</td>
                        <td class="sa-hide-mob" style="padding:10px 12px;text-align:right">
                            <div style="height:4px;background:var(--bg);border-radius:2px;width:50px;display:inline-block;vertical-align:middle;margin-right:5px">
                                <div style="height:100%;width:{{ min($s['revenue_share'], 100) }}%;background:var(--accent);border-radius:2px"></div>
                            </div>
                            <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim)">{{ $s['revenue_share'] }}%</span>
                        </td>
                        <td class="sa-hide-mob" data-label="Avg Order" style="padding:10px 12px;text-align:right;font-family:var(--mono);color:var(--text-sub);font-size:11px">{{ number_format($s['avg_order']) }}</td>
                        <td class="sa-hide-mob" data-label="Items" style="padding:10px 12px;text-align:right;font-family:var(--mono);color:var(--text-sub)">{{ number_format($s['items_sold']) }}</td>
                        <td data-label="GP" style="padding:10px 12px;text-align:right;font-family:var(--mono);font-weight:700;color:var(--green)">{{ number_format($s['gross_profit']) }}</td>
                        <td data-label="Margin" style="padding:10px 12px;text-align:right">
                            <span style="font-size:11px;font-weight:700;padding:2px 7px;border-radius:10px;font-family:var(--mono);
                                background:{{ $s['margin_pct'] >= 30 ? 'var(--green-glow)' : ($s['margin_pct'] >= 15 ? 'rgba(251,191,36,.12)' : 'rgba(225,29,72,.08)') }};
                                color:{{ $s['margin_pct'] >= 30 ? 'var(--green)' : ($s['margin_pct'] >= 15 ? 'var(--amber)' : 'var(--red)') }}">
                                {{ $s['margin_pct'] }}%
                            </span>
                        </td>
                        <td class="sa-hide-mob" data-label="Discounts" style="padding:10px 12px;text-align:right;font-family:var(--mono);color:{{ $s['total_discount'] > 0 ? 'var(--amber)' : 'var(--text-dim)' }};font-size:11px">
                            {{ $s['total_discount'] > 0 ? number_format($s['total_discount']) : '—' }}
                        </td>
                        <td data-label="Overrides" style="padding:10px 12px;text-align:right">
                            @if($s['override_count'] > 0)
                                <span style="font-size:11px;font-weight:700;font-family:var(--mono);padding:2px 7px;border-radius:10px;
                                    background:{{ $s['override_count'] > 3 ? 'rgba(217,119,6,.12)' : 'transparent' }};
                                    color:{{ $s['override_count'] > 3 ? 'var(--amber)' : 'var(--text-sub)' }}">
                                    {{ $s['override_count'] }}
                                </span>
                            @else
                                <span style="color:var(--text-dim)">—</span>
                            @endif
                        </td>
                        <td data-label="Voided" style="padding:10px 12px;text-align:right">
                            @if($s['void_count'] > 0)
                                <span style="font-size:11px;font-weight:700;font-family:var(--mono);padding:2px 7px;border-radius:10px;
                                    background:rgba(225,29,72,.08);color:var(--red)">
                                    {{ $s['void_count'] }}
                                </span>
                            @else
                                <span style="color:var(--text-dim)">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="13" style="padding:48px;text-align:center;color:var(--text-dim)">No sales data for this period</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Customer Repeat Analysis + Returns side-by-side --}}
    <div class="sa-two-col" style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

        {{-- Customer analysis --}}
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
                <div class="sa-section-title" style="font-size:13px;font-weight:700;color:var(--text)">Customer Analysis</div>
                <div class="sa-section-subtitle" style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">Repeat rate and top spenders</div>
            </div>

            {{-- Stats strip --}}
            <div class="sa-customer-stats" style="display:flex;border-bottom:1px solid var(--border)">
                @foreach([
                    ['label' => 'Known Customers', 'value' => $customers['total_customers'],                      'color' => 'var(--accent)'],
                    ['label' => 'Repeat',           'value' => $customers['repeat_customers'],                     'color' => 'var(--green)'],
                    ['label' => 'Repeat Rate',      'value' => $customers['repeat_rate'].'%',                     'color' => $customers['repeat_rate'] >= 30 ? 'var(--green)' : 'var(--text-sub)'],
                    ['label' => 'Walk-ins',         'value' => $customers['walkin_count'],                        'color' => 'var(--text-dim)'],
                ] as $stat)
                <div class="sa-stat-item" style="flex:1;padding:12px 14px;text-align:center;border-right:1px solid var(--border)">
                    <div style="font-size:18px;font-weight:700;color:{{ $stat['color'] }}">{{ $stat['value'] }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:2px">{{ $stat['label'] }}</div>
                </div>
                @endforeach
            </div>

            {{-- Top customers --}}
            <div class="sa-customer-table-wrap" style="overflow:auto;max-height:360px">
                <table class="sa-customer-table" style="width:100%;border-collapse:collapse;font-size:12px">
                    <thead style="position:sticky;top:0;background:var(--bg);z-index:1">
                        <tr style="border-bottom:1px solid var(--border)">
                            <th style="padding:8px 16px;text-align:left;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Customer</th>
                            <th style="padding:8px 10px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Visits</th>
                            <th style="padding:8px 10px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Total Spent</th>
                            <th style="padding:8px 10px;text-align:right;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase">Avg</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers['top_customers'] as $c)
                        <tr style="border-bottom:1px solid var(--border)" onmouseover="this.style.background='rgba(59,111,212,.03)'" onmouseout="this.style.background='transparent'">
                            <td style="padding:8px 16px">
                                <div style="display:flex;align-items:center;gap:7px">
                                    @if($c['is_repeat'])
                                    <span style="font-size:9px;font-weight:700;padding:1px 5px;border-radius:8px;background:var(--green-glow);color:var(--green)">↩</span>
                                    @endif
                                    <div>
                                        <div style="font-weight:600;color:var(--text)">{{ $c['name'] }}</div>
                                        @if($c['phone'])<div style="font-size:10px;color:var(--text-dim);font-family:var(--mono)">{{ $c['phone'] }}</div>@endif
                                    </div>
                                </div>
                            </td>
                            <td style="padding:8px 10px;text-align:right;font-family:var(--mono);color:{{ $c['purchase_count'] > 1 ? 'var(--green)' : 'var(--text-sub)' }};font-weight:{{ $c['purchase_count'] > 1 ? '700' : '400' }}">
                                {{ $c['purchase_count'] }}
                            </td>
                            <td style="padding:8px 10px;text-align:right;font-family:var(--mono);font-weight:600;color:var(--text);font-size:11px">{{ number_format($c['total_spent']) }}</td>
                            <td style="padding:8px 10px;text-align:right;font-family:var(--mono);font-size:11px;color:var(--text-sub)">{{ number_format($c['avg_order']) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="padding:32px;text-align:center;color:var(--text-dim)">No named customers in this period</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Returns detail for sellers tab --}}
        <div style="display:flex;flex-direction:column;gap:20px">

            <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden">
                <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
                    <div class="sa-section-title" style="font-size:13px;font-weight:700;color:var(--text)">Returns Summary</div>
                    <div class="sa-section-subtitle" style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">Impact on net revenue</div>
                </div>
                <div class="sa-returns-stats" style="display:flex;flex-wrap:wrap;border-bottom:1px solid var(--border)">
                    @foreach([
                        ['label' => 'Returns',      'value' => $ret['returns_count'],              'color' => 'var(--text)'],
                        ['label' => 'Refunded',     'value' => number_format($ret['returned_revenue']), 'color' => 'var(--red)'],
                        ['label' => 'Exchanges',    'value' => $ret['exchange_count'],              'color' => 'var(--amber)'],
                        ['label' => 'Return Rate',  'value' => $ret['return_rate'].'%',             'color' => $ret['return_rate'] > 5 ? 'var(--red)' : 'var(--green)'],
                    ] as $stat)
                    <div class="sa-stat-item" style="flex:1;min-width:80px;padding:12px 14px;text-align:center;border-right:1px solid var(--border)">
                        <div style="font-size:18px;font-weight:700;color:{{ $stat['color'] }}">{{ $stat['value'] }}</div>
                        <div style="font-size:10px;color:var(--text-dim);margin-top:2px">{{ $stat['label'] }}</div>
                    </div>
                    @endforeach
                </div>

                @if(count($ret['top_returned_products']))
                <div class="sa-returned-products" style="padding:14px 20px">
                    <div style="font-size:11px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;margin-bottom:10px">Most Returned Products</div>
                    @foreach($ret['top_returned_products'] as $rp)
                    <div class="sa-product-row" style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid var(--border);gap:10px">
                        <span style="font-size:12px;color:var(--text);flex:1;min-width:0">{{ $rp['product_name'] }}</span>
                        <span style="font-size:11px;font-family:var(--mono);font-weight:700;color:var(--red);white-space:nowrap">{{ $rp['qty_returned'] }} units</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>
    </div>

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: PAYMENTS
══════════════════════════════════════════════════════════════════════════ --}}
@elseif($activeTab === 'payments')

    @php
        // Get payment summary data - ensure we include the full day
        $startDate = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $endDate = \Carbon\Carbon::parse($dateTo)->endOfDay();

        $paymentQuery = \App\Models\SalePayment::query()
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->whereNull('sales.voided_at')
            ->where('sales.sale_date', '>=', $startDate)
            ->where('sales.sale_date', '<=', $endDate);

        if ($locationFilter !== 'all') {
            $shopId = (int) str_replace('shop:', '', $locationFilter);
            $paymentQuery->where('sales.shop_id', $shopId);
        }

        $paymentSummary = $paymentQuery
            ->select('sale_payments.payment_method', \Illuminate\Support\Facades\DB::raw('SUM(sale_payments.amount) as total_amount'), \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT sale_payments.sale_id) as transaction_count'))
            ->groupBy('sale_payments.payment_method')
            ->get();

        $paymentMethods = [];
        foreach (\App\Enums\PaymentMethod::cases() as $method) {
            $data = $paymentSummary->firstWhere('payment_method', $method->value);
            $paymentMethods[$method->value] = [
                'label' => $method->label(),
                'total' => $data ? $data->total_amount : 0,
                'count' => $data ? $data->transaction_count : 0,
            ];
        }

        $totalRevenue = array_sum(array_column($paymentMethods, 'total'));

        // Split payment stats
        $salesQuery = \App\Models\Sale::query()
            ->whereNull('voided_at')
            ->where('sale_date', '>=', $startDate)
            ->where('sale_date', '<=', $endDate);

        if ($locationFilter !== 'all') {
            $shopId = (int) str_replace('shop:', '', $locationFilter);
            $salesQuery->where('shop_id', $shopId);
        }

        $totalSales = (clone $salesQuery)->count();
        $splitPaymentSales = (clone $salesQuery)->where('is_split_payment', true)->count();
        $splitPercentage = $totalSales > 0 ? round(($splitPaymentSales / $totalSales) * 100, 1) : 0;

        // Credit stats
        $creditSales = (clone $salesQuery)->where('has_credit', true);
        $creditCount = $creditSales->count();
        $creditTotal = $creditSales->sum('credit_amount');

        // Average transaction value (no division needed, amounts are already in RWF)
        $avgTransactionValue = $totalSales > 0 ? round($totalRevenue / $totalSales) : 0;
    @endphp

    {{-- Payment KPI Cards - Modern Design --}}
    <div class="biz-kpi-grid" style="margin-bottom:24px">

        {{-- Total Revenue --}}
        <div class="bkpi violet" style="animation:fadeUp .35s ease .05s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon violet">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                            <line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                    </div>
                    <span class="bkpi-name">Total Revenue</span>
                </div>
                <span class="bkpi-pct up">{{ $totalSales }}</span>
            </div>
            <div class="bkpi-value">{{ number_format($totalRevenue , 0) }}</div>
            <div class="bkpi-meta">{{ $totalSales }} transactions · RWF</div>
            <div style="display:flex;gap:0;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--violet);font-family:var(--mono)">{{ number_format($avgTransactionValue , 0) }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Avg Order</div>
                </div>
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--text-sub);font-family:var(--mono)">{{ $splitPaymentSales }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Split</div>
                </div>
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--amber);font-family:var(--mono)">{{ $creditCount }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Credit</div>
                </div>
            </div>
        </div>

        {{-- Cash Payments --}}
        <div class="bkpi green" style="animation:fadeUp .35s ease .10s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"/>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <span class="bkpi-name">Cash</span>
                </div>
                @php
                    $cashPct = $totalRevenue > 0 ? round(($paymentMethods['cash']['total'] / $totalRevenue) * 100, 1) : 0;
                @endphp
                <span class="bkpi-pct {{ $cashPct > 0 ? 'up' : '' }}">{{ $cashPct }}%</span>
            </div>
            <div class="bkpi-value" style="color:var(--green)">{{ number_format($paymentMethods['cash']['total'] , 0) }}</div>
            <div class="bkpi-meta">{{ $paymentMethods['cash']['count'] }} transactions · RWF</div>
        </div>

        {{-- Card Payments --}}
        <div class="bkpi blue" style="animation:fadeUp .35s ease .15s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                            <line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                    </div>
                    <span class="bkpi-name">Card</span>
                </div>
                @php
                    $cardPct = $totalRevenue > 0 ? round(($paymentMethods['card']['total'] / $totalRevenue) * 100, 1) : 0;
                @endphp
                <span class="bkpi-pct {{ $cardPct > 0 ? 'up' : '' }}">{{ $cardPct }}%</span>
            </div>
            <div class="bkpi-value" style="color:var(--blue)">{{ number_format($paymentMethods['card']['total'] , 0) }}</div>
            <div class="bkpi-meta">{{ $paymentMethods['card']['count'] }} transactions · RWF</div>
        </div>

        {{-- Mobile Money --}}
        <div class="bkpi pink" style="animation:fadeUp .35s ease .20s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon pink">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                            <line x1="12" y1="18" x2="12.01" y2="18"/>
                        </svg>
                    </div>
                    <span class="bkpi-name">Mobile Money</span>
                </div>
                @php
                    $mmPct = $totalRevenue > 0 ? round(($paymentMethods['mobile_money']['total'] / $totalRevenue) * 100, 1) : 0;
                @endphp
                <span class="bkpi-pct {{ $mmPct > 0 ? 'up' : '' }}">{{ $mmPct }}%</span>
            </div>
            <div class="bkpi-value" style="color:var(--pink)">{{ number_format($paymentMethods['mobile_money']['total'] , 0) }}</div>
            <div class="bkpi-meta">{{ $paymentMethods['mobile_money']['count'] }} transactions · RWF</div>
        </div>

        {{-- Bank Transfer --}}
        <div class="bkpi amber" style="animation:fadeUp .35s ease .25s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon amber">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                    </div>
                    <span class="bkpi-name">Bank Transfer</span>
                </div>
                @php
                    $btPct = $totalRevenue > 0 ? round(($paymentMethods['bank_transfer']['total'] / $totalRevenue) * 100, 1) : 0;
                @endphp
                <span class="bkpi-pct {{ $btPct > 0 ? 'up' : '' }}">{{ $btPct }}%</span>
            </div>
            <div class="bkpi-value" style="color:var(--amber)">{{ number_format($paymentMethods['bank_transfer']['total'] , 0) }}</div>
            <div class="bkpi-meta">{{ $paymentMethods['bank_transfer']['count'] }} transactions · RWF</div>
        </div>

        {{-- Credit --}}
        <div class="bkpi red" style="animation:fadeUp .35s ease .30s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon red">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </div>
                    <span class="bkpi-name">Credit</span>
                </div>
                @php
                    $crPct = $totalRevenue > 0 ? round(($paymentMethods['credit']['total'] / $totalRevenue) * 100, 1) : 0;
                @endphp
                <span class="bkpi-pct {{ $crPct > 0 ? 'down' : '' }}">{{ $crPct }}%</span>
            </div>
            <div class="bkpi-value" style="color:var(--red)">{{ number_format($paymentMethods['credit']['total'] , 0) }}</div>
            <div class="bkpi-meta">{{ $creditCount }} transactions · RWF</div>
            <div style="display:flex;gap:0;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--red);font-family:var(--mono)">{{ number_format($creditTotal , 0) }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Outstanding</div>
                </div>
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--text-sub);font-family:var(--mono)">{{ $creditCount }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Sales</div>
                </div>
            </div>
        </div>

    </div>

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: CREDIT
══════════════════════════════════════════════════════════════════════════ --}}
@elseif($activeTab === 'credit')

    @php
        // Credit summary stats
        $customerQuery = \App\Models\Customer::query();

        if ($locationFilter !== 'all') {
            $shopId = (int) str_replace('shop:', '', $locationFilter);
            $customerQuery->where('shop_id', $shopId);
        }

        $customersWithCredit = (clone $customerQuery)->where('outstanding_balance', '>', 0)->count();
        $totalOutstanding = (clone $customerQuery)->sum('outstanding_balance');
        $totalCreditGiven = (clone $customerQuery)->sum('total_credit_given');
        $totalRepaid = (clone $customerQuery)->sum('total_repaid');

        // Calculate repayment rate
        $repaymentRate = $totalCreditGiven > 0 ? round(($totalRepaid / $totalCreditGiven) * 100, 1) : 0;

        // Top customers by outstanding balance
        $topCustomers = (clone $customerQuery)
            ->where('outstanding_balance', '>', 0)
            ->with('shop')
            ->orderBy('outstanding_balance', 'desc')
            ->limit(10)
            ->get();

        // Get credit sales from date range - ensure we include the full day
        $startDate = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $endDate = \Carbon\Carbon::parse($dateTo)->endOfDay();

        $creditSalesInPeriod = \App\Models\Sale::query()
            ->whereNull('voided_at')
            ->where('has_credit', true)
            ->where('sale_date', '>=', $startDate)
            ->where('sale_date', '<=', $endDate);

        if ($locationFilter !== 'all') {
            $shopId = (int) str_replace('shop:', '', $locationFilter);
            $creditSalesInPeriod->where('shop_id', $shopId);
        }

        $creditSalesCount = $creditSalesInPeriod->count();
        $creditGivenInPeriod = $creditSalesInPeriod->sum('credit_amount');
    @endphp

    {{-- Credit KPI Cards - Modern Design --}}
    <div class="biz-kpi-grid" style="margin-bottom:24px">

        {{-- Total Outstanding --}}
        <div class="bkpi red" style="animation:fadeUp .35s ease .05s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon red">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </div>
                    <span class="bkpi-name">Outstanding Balance</span>
                </div>
                <span class="bkpi-pct down">{{ $customersWithCredit }}</span>
            </div>
            <div class="bkpi-value" style="color:var(--red)">{{ number_format($totalOutstanding , 0) }}</div>
            <div class="bkpi-meta">{{ $customersWithCredit }} customers · RWF</div>
            <div style="display:flex;gap:0;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--red);font-family:var(--mono)">{{ number_format($totalCreditGiven , 0) }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Total Given</div>
                </div>
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--green);font-family:var(--mono)">{{ number_format($totalRepaid , 0) }}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Repaid</div>
                </div>
                <div style="flex:1;text-align:center">
                    <div style="font-size:11px;font-weight:700;color:var(--text-sub);font-family:var(--mono)">{{ $repaymentRate }}%</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Rate</div>
                </div>
            </div>
        </div>

        {{-- Credit Given (Period) --}}
        <div class="bkpi amber" style="animation:fadeUp .35s ease .10s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon amber">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                        </svg>
                    </div>
                    <span class="bkpi-name">Credit Given (Period)</span>
                </div>
                <span class="bkpi-pct {{ $creditSalesCount > 0 ? 'down' : '' }}">{{ $creditSalesCount }}</span>
            </div>
            <div class="bkpi-value" style="color:var(--amber)">{{ number_format($creditGivenInPeriod , 0) }}</div>
            <div class="bkpi-meta">{{ $creditSalesCount }} sales · RWF</div>
        </div>

        {{-- Customers with Credit --}}
        <div class="bkpi blue" style="animation:fadeUp .35s ease .15s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <span class="bkpi-name">Customers</span>
                </div>
                @php
                    $avgDebt = $customersWithCredit > 0 ? round($totalOutstanding / $customersWithCredit , 0) : 0;
                @endphp
                <span class="bkpi-pct">{{ number_format($avgDebt) }}</span>
            </div>
            <div class="bkpi-value" style="color:var(--blue)">{{ number_format($customersWithCredit) }}</div>
            <div class="bkpi-meta">Avg: {{ number_format($avgDebt, 0) }} RWF per customer</div>
        </div>

        {{-- Repayment Rate --}}
        <div class="bkpi green" style="animation:fadeUp .35s ease .20s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                    </div>
                    <span class="bkpi-name">Repayment Rate</span>
                </div>
                <span class="bkpi-pct {{ $repaymentRate >= 80 ? 'up' : 'down' }}">{{ $repaymentRate >= 80 ? '↑' : '↓' }}</span>
            </div>
            <div class="bkpi-value" style="color:var(--green)">{{ $repaymentRate }}%</div>
            <div class="bkpi-meta">{{ number_format($totalRepaid , 0) }} RWF repaid</div>
        </div>

    </div>

    {{-- Top Customers by Outstanding Balance --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;animation:fadeUp .35s ease .25s both">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
            <div>
                <div class="sa-section-title" style="font-size:13px;font-weight:700;color:var(--text)">Top Customers by Outstanding Balance</div>
                <div class="sa-section-subtitle" style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">Highest credit balances · All time</div>
            </div>
            <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim)">Top {{ $topCustomers->count() }}</span>
        </div>

        @if($topCustomers->count() > 0)
            <div style="overflow-x:auto">
                <table class="sa-table" style="width:100%;border-collapse:collapse;font-size:13px">
                    <colgroup>
                        <col style="width:50px">
                        <col style="width:auto">
                        <col style="width:140px">
                        <col style="width:140px">
                        <col style="width:120px">
                        <col style="width:120px">
                        <col style="width:120px">
                    </colgroup>
                    <thead>
                        <tr style="border-bottom:1px solid var(--border);background:var(--surface2)">
                            <th style="text-align:center;padding:10px 14px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.8px;font-size:10px">#</th>
                            <th style="text-align:left;padding:10px 14px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.8px;font-size:10px">Customer</th>
                            <th style="text-align:left;padding:10px 14px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.8px;font-size:10px">Phone</th>
                            <th style="text-align:left;padding:10px 14px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.8px;font-size:10px">Shop</th>
                            <th style="text-align:right;padding:10px 14px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.8px;font-size:10px">Outstanding</th>
                            <th style="text-align:right;padding:10px 14px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.8px;font-size:10px">Given</th>
                            <th style="text-align:right;padding:10px 14px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.8px;font-size:10px">Repaid</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topCustomers as $index => $customer)
                            <tr style="border-bottom:1px solid var(--border)">
                                <td style="text-align:center;padding:12px 14px">
                                    <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:6px;background:var(--surface2);color:var(--text-sub);font-size:11px;font-weight:700">{{ $index + 1 }}</span>
                                </td>
                                <td style="padding:12px 14px;font-weight:600;color:var(--text)">{{ $customer->name }}</td>
                                <td style="padding:12px 14px;font-family:var(--mono);color:var(--text-sub);font-size:12px">{{ $customer->phone }}</td>
                                <td style="padding:12px 14px;color:var(--text-sub);font-size:12px">{{ $customer->shop?->name ?? '—' }}</td>
                                <td style="text-align:right;padding:12px 14px">
                                    <span style="font-family:var(--mono);font-weight:700;color:var(--red);font-size:13px">{{ number_format($customer->outstanding_balance , 0) }}</span>
                                </td>
                                <td style="text-align:right;padding:12px 14px">
                                    <span style="font-family:var(--mono);color:var(--text-sub);font-size:12px">{{ number_format($customer->total_credit_given , 0) }}</span>
                                </td>
                                <td style="text-align:right;padding:12px 14px">
                                    <span style="font-family:var(--mono);color:var(--green);font-size:12px;font-weight:600">{{ number_format($customer->total_repaid , 0) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align:center;padding:60px 20px;color:var(--text-dim)">
                <svg style="width:48px;height:48px;margin:0 auto 16px;opacity:0.3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <div style="font-size:14px;font-weight:600;color:var(--text-sub);margin-bottom:4px">No Credit Customers Found</div>
                <div style="font-size:12px;font-style:italic">All customers have cleared their balances</div>
            </div>
        @endif
    </div>

@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     APEXCHARTS — Revenue Trend (Overview tab only)
══════════════════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'overview' && count($this->revenueTrend))
@php $trend = $this->revenueTrend @endphp
<script>
    (function() {
        const chartId = 'rev-trend-chart-{{ md5($dateFrom.$dateTo.$locationFilter) }}';
        const rawDates         = @json(array_column($trend, 'date'));
        const rawRevenues      = @json(array_column($trend, 'revenue'));
        const rawTransactions  = @json(array_column($trend, 'transactions'));

        // Sanitize data - ensure no null/undefined/NaN values
        const dates = rawDates.filter(d => d != null);
        const revenues = rawRevenues.map(v => (v == null || isNaN(v)) ? 0 : Number(v));
        const transactions = rawTransactions.map(v => (v == null || isNaN(v)) ? 0 : Number(v));

        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initChart);
        } else {
            initChart();
        }

        function initChart() {
            const el = document.getElementById(chartId);
            if (!el) {
                console.warn('Chart element not found:', chartId);
                return;
            }

            if (typeof ApexCharts === 'undefined') {
                console.warn('ApexCharts library not loaded');
                return;
            }

            // Validate we have data
            if (!dates || dates.length === 0 || revenues.length === 0) {
                console.warn('No valid chart data available');
                el.innerHTML = '<div style="padding:40px;text-align:center;color:var(--text-dim)">No data available for chart</div>';
                return;
            }

            // Clear any existing chart
            el.innerHTML = '';

            const chart = new ApexCharts(el, {
                series: [{
                    name: 'Revenue',
                    type: 'column',
                    data: revenues,
                }, {
                    name: 'Transactions',
                    type: 'line',
                    data: transactions,
                }],
                chart: {
                    height: 240,
                    type: 'line',
                    sparkline: { enabled: false },
                    toolbar: { show: false },
                    animations: { enabled: true, easing: 'easeinout', speed: 600 },
                    fontFamily: "'DM Mono', monospace",
                },
                stroke: {
                    width: [0, 3],
                    curve: 'smooth'
                },
                plotOptions: {
                    bar: {
                        columnWidth: '60%',
                        borderRadius: 4,
                    }
                },
                fill: {
                    opacity: [0.85, 1],
                    type: ['solid', 'solid']
                },
                colors: ['#3b6fd4', '#10b981'],
                labels: dates,
                markers: {
                    size: [0, 4],
                    strokeWidth: 2,
                    hover: { size: 6 }
                },
                xaxis: {
                    type: 'datetime',
                    labels: {
                        style: { fontSize: '11px', colors: '#7a81a0', fontFamily: "'DM Mono', monospace" },
                        rotate: -30,
                        formatter: (val) => {
                            try {
                                if (!val) return '';
                                const d = new Date(val);
                                if (isNaN(d.getTime())) return '';
                                return d.toLocaleDateString('en-GB', { month: 'short', day: 'numeric' });
                            } catch (e) {
                                return '';
                            }
                        }
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: [{
                    title: {
                        text: 'Revenue',
                        style: { fontSize: '11px', color: '#7a81a0', fontFamily: "'DM Mono', monospace" }
                    },
                    labels: {
                        style: { fontSize: '11px', colors: '#7a81a0', fontFamily: "'DM Mono', monospace" },
                        formatter: (v) => {
                            if (v == null || isNaN(v)) return '0';
                            return v >= 1000000 ? (v/1000000).toFixed(1)+'M' : v >= 1000 ? (v/1000).toFixed(0)+'K' : v.toFixed(0);
                        },
                    }
                }, {
                    opposite: true,
                    title: {
                        text: 'Transactions',
                        style: { fontSize: '11px', color: '#10b981', fontFamily: "'DM Mono', monospace" }
                    },
                    labels: {
                        style: { fontSize: '11px', colors: '#10b981', fontFamily: "'DM Mono', monospace" },
                        formatter: (v) => {
                            if (v == null || isNaN(v)) return '0';
                            return v.toFixed(0);
                        },
                    }
                }],
                grid: {
                    borderColor: '#e2e6f3',
                    strokeDashArray: 4,
                    xaxis: { lines: { show: false } },
                    padding: {
                        top: 0,
                        right: 10,
                        bottom: 0,
                        left: 10
                    }
                },
                legend: {
                    show: true,
                    position: 'top',
                    horizontalAlign: 'right',
                    fontSize: '11px',
                    fontFamily: "'DM Mono', monospace",
                    markers: {
                        width: 10,
                        height: 10,
                        radius: 2
                    },
                    itemMargin: {
                        horizontal: 10
                    }
                },
                dataLabels: { enabled: false },
                tooltip: {
                    shared: true,
                    intersect: false,
                    theme: 'light',
                    y: [{
                        formatter: (v) => {
                            if (v == null || isNaN(v)) return '0 RWF';
                            return new Intl.NumberFormat().format(v) + ' RWF';
                        }
                    }, {
                        formatter: (v) => {
                            if (v == null || isNaN(v)) return '0 txns';
                            return v + ' txns';
                        }
                    }]
                }
            });

            chart.render();
        }
    })();
</script>
@endif

</div>