{{-- ┌─────────────────────────────────────────────────────────────────────────┐
    │  Owner · Sales Analytics                                               │
    │  Prefix: sa-  |  Tabs: Overview · Ledger · Audit · Sellers · Payments │
    │  Design system: box-shadow:var(--shadow-card), CSS variables only      │
    └─────────────────────────────────────────────────────────────────────────┘ --}}
<div wire:poll.60s>
<style>
/* ── Prefix: sa- (sales-analytics) ──────────────────────────────── */

/* ── Period filter bar ───────────────────────────────────────────── */
.sa-filters { background:var(--surface);border:none;box-shadow:var(--shadow-card);
              border-radius:var(--r);margin-bottom:20px;min-width:0;max-width:100% }
.sa-presets { display:flex;gap:4px;overflow-x:auto;-webkit-overflow-scrolling:touch;
              padding:12px 16px;border-bottom:1px solid var(--border);
              scrollbar-width:none;flex-wrap:nowrap }
.sa-presets::-webkit-scrollbar { display:none }
.sa-preset  { padding:6px 14px;border-radius:7px;font-size:12px;font-weight:600;
              border:1px solid transparent;background:transparent;color:var(--text-dim);
              cursor:pointer;white-space:nowrap;flex-shrink:0;font-family:var(--font);
              transition:all var(--tr) }
.sa-preset:hover  { background:var(--surface2);color:var(--text);border-color:var(--border) }
.sa-preset.active { background:var(--accent);color:#fff;border-color:var(--accent);
                    box-shadow:0 2px 8px var(--accent-glow) }
.sa-controls { display:flex;align-items:center;gap:0;flex-wrap:wrap }
.sa-ctrl-seg { display:flex;align-items:center;gap:8px;padding:10px 16px;
               border-right:1px solid var(--border);flex-shrink:0 }
.sa-ctrl-seg:last-child { border-right:none }
.sa-ctrl-grow { flex:1;min-width:0 }
.sa-date-in  { background:transparent;border:none;outline:none;font-size:14px;
               font-weight:600;font-family:var(--mono);color:var(--text);
               width:110px;cursor:pointer;min-width:0 }
.sa-shop-sel { background:transparent;border:none;outline:none;font-size:14px;
               font-weight:600;font-family:var(--font);color:var(--text);cursor:pointer;
               max-width:160px }

/* ── Tab strip — full-width grid ─────────────────────────────────── */
.sa-tabs { display:grid;grid-template-columns:repeat(6,1fr);
           background:var(--surface);box-shadow:var(--shadow-card);
           border-radius:var(--r);margin-bottom:24px;overflow:hidden }
.sa-tab  { display:flex;align-items:center;justify-content:center;gap:6px;
           padding:12px 10px;border:none;border-radius:0;
           border-bottom:2.5px solid transparent;
           border-right:1px solid var(--border);
           cursor:pointer;font-size:12px;font-weight:600;
           font-family:var(--font);background:transparent;color:var(--text-dim);
           transition:all var(--tr);white-space:nowrap }
.sa-tab:last-child { border-right:none }
.sa-tab:hover  { background:var(--surface2);color:var(--text);border-bottom-color:var(--border-hi) }
.sa-tab.active { background:var(--accent-dim);color:var(--accent);border-bottom-color:var(--accent) }

/* ── KPI cards ───────────────────────────────────────────────────── */
.sa-kpis { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px }
.sa-kpi  { background:var(--surface);border:none;border-radius:var(--r);
           box-shadow:var(--shadow-card);padding:22px 20px;
           display:flex;flex-direction:column;gap:16px;transition:box-shadow var(--tr) }
.sa-kpi:hover { box-shadow:var(--shadow-card-hover) }
.sa-kpi-row  { display:flex;align-items:center;gap:12px }
.sa-kpi-icon { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;
               justify-content:center;flex-shrink:0 }
.sa-kpi-body { flex:1;min-width:0 }
.sa-kpi-label { font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;
                color:var(--text-dim);line-height:1.2 }
.sa-kpi-sub  { font-size:12px;color:var(--text-dim);margin-top:2px }
.sa-kpi-val  { font-size:24px;font-weight:800;font-family:var(--mono);letter-spacing:-1px;
               line-height:1;flex-shrink:0 }
.sa-growth   { font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;
               font-family:var(--mono);white-space:nowrap;flex-shrink:0 }
.sa-growth.up   { background:var(--green-dim);color:var(--green) }
.sa-growth.down { background:var(--red-dim);color:var(--red) }
.sa-growth.neutral { background:var(--surface2);color:var(--text-dim) }
.sa-kpi-divider { height:1px;background:var(--border) }
.sa-kpi-footer  { display:grid;grid-template-columns:repeat(3,1fr);gap:0 }
.sa-kpi-stat    { text-align:center;padding:4px 0 }
.sa-kpi-stat-v  { font-size:12px;font-weight:700;font-family:var(--mono);display:block }
.sa-kpi-stat-l  { font-size:10px;color:var(--text-dim);margin-top:1px;display:block }
.sa-kpi-bar     { height:3px;border-radius:3px;background:var(--surface2) }

/* ── Generic card ────────────────────────────────────────────────── */
.sa-card { background:var(--surface);border:none;box-shadow:var(--shadow-card);
           border-radius:var(--r);overflow:hidden;margin-bottom:20px }
.sa-card-head { padding:16px 20px;border-bottom:1px solid var(--border);
                display:flex;align-items:center;justify-content:space-between;gap:12px }
.sa-card-title { font-size:13px;font-weight:700;color:var(--text) }
.sa-card-sub   { font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px }
.sa-card-badge { font-size:11px;font-family:var(--mono);color:var(--text-dim);flex-shrink:0 }

/* ── Summary stat strip ──────────────────────────────────────────── */
.sa-strip      { display:flex;flex-wrap:wrap;background:var(--surface);
                 box-shadow:var(--shadow-card);border-radius:var(--r);
                 overflow:hidden;margin-bottom:20px }
.sa-strip-item { flex:1;min-width:140px;padding:16px 20px;border-right:1px solid var(--border);
                 border-bottom:1px solid var(--border) }
.sa-strip-item:last-child { border-right:none }
.sa-strip-lbl  { font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.6px;
                 text-transform:uppercase;margin-bottom:6px }
.sa-strip-val  { font-size:22px;font-weight:800;letter-spacing:-0.5px;font-family:var(--mono);line-height:1 }
.sa-strip-sub  { font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:4px }

/* ── Table ───────────────────────────────────────────────────────── */
.sa-tbl-wrap { background:var(--surface);border:none;box-shadow:var(--shadow-card);
               border-radius:var(--r);overflow:hidden;margin-bottom:20px }
.sa-tbl-scroll { overflow-x:auto;-webkit-overflow-scrolling:touch }
.sa-tbl      { width:100%;border-collapse:collapse;font-size:13px }
.sa-tbl thead tr { background:var(--bg);border-bottom:1px solid var(--border) }
.sa-tbl thead th { padding:10px 14px;text-align:left;font-size:11px;font-weight:700;
                   letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);
                   white-space:nowrap }
.sa-tbl tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.sa-tbl tbody tr:last-child { border-bottom:none }
.sa-tbl tbody tr:hover { background:var(--surface2) }
.sa-tbl td { padding:11px 14px;vertical-align:middle }
.sa-tbl tfoot tr { background:var(--bg);border-top:2px solid var(--border) }
.sa-tbl tfoot td { padding:10px 14px;font-weight:700 }

/* ── Alert strip (price overrides) ──────────────────────────────── */
.sa-alert-strip { display:flex;align-items:center;justify-content:space-between;gap:12px;
                  flex-wrap:wrap;padding:13px 18px;margin-bottom:20px;border-radius:var(--r);
                  background:var(--amber-dim);border:1px solid var(--amber) }
.sa-alert-btn  { font-size:11px;font-weight:700;padding:6px 14px;border-radius:20px;
                 border:1px solid var(--amber);background:transparent;
                 color:var(--amber);cursor:pointer;white-space:nowrap;
                 flex-shrink:0;transition:all var(--tr);font-family:var(--font) }
.sa-alert-btn:hover { background:var(--amber);color:#fff }

/* ── Sales mix bars ──────────────────────────────────────────────── */
.sa-mix-grid { display:flex;gap:12px;flex-wrap:wrap }
.sa-mix-item { flex:1;min-width:160px;border-radius:10px;padding:14px 16px;
               border:1px solid var(--border) }
.sa-mix-bar  { height:3px;border-radius:3px;background:var(--surface2);margin-top:8px }

/* ── Peak hours heat grid ────────────────────────────────────────── */
.sa-hours-wrap { padding:16px 20px }
.sa-hours-grid { display:grid;grid-template-columns:repeat(24,1fr);gap:3px;margin-top:10px }
.sa-hours-cell { height:28px;border-radius:4px;display:flex;align-items:center;
                 justify-content:center;font-size:9px;font-weight:700;
                 color:var(--text-dim);font-family:var(--mono);cursor:default }
.sa-hours-labels { display:grid;grid-template-columns:repeat(24,1fr);gap:3px;margin-top:4px }
.sa-hours-lbl { font-size:9px;text-align:center;color:var(--text-dim);font-family:var(--mono) }

/* ── Payment visual bars ─────────────────────────────────────────── */
.sa-pay-row  { display:flex;align-items:center;gap:12px;padding:12px 20px;
               border-bottom:1px solid var(--border) }
.sa-pay-row:last-child { border-bottom:none }
.sa-pay-dot  { width:10px;height:10px;border-radius:50%;flex-shrink:0 }
.sa-pay-name { font-size:13px;font-weight:600;color:var(--text);width:120px;flex-shrink:0 }
.sa-pay-bar-wrap { flex:1;height:8px;background:var(--surface2);border-radius:4px;overflow:hidden }
.sa-pay-bar  { height:100%;border-radius:4px;transition:width .6s ease }
.sa-pay-amt  { font-size:13px;font-weight:700;font-family:var(--mono);color:var(--text);
               width:120px;text-align:right;flex-shrink:0 }
.sa-pay-pct  { font-size:11px;color:var(--text-dim);font-family:var(--mono);
               width:44px;text-align:right;flex-shrink:0 }

/* ── Credit progress ─────────────────────────────────────────────── */
.sa-credit-progress { height:8px;border-radius:4px;background:var(--surface2);overflow:hidden;margin-top:8px }
.sa-credit-fill { height:100%;border-radius:4px;transition:width .6s ease }

/* ── Recon block styles (Revenue Reconciliation in Ledger) ───────── */
.sa-recon    { background:var(--surface);box-shadow:var(--shadow-card);border-radius:var(--r);
               overflow:hidden;margin-top:20px;margin-bottom:20px }
.sa-recon-hd { display:flex;align-items:center;justify-content:space-between;
               padding:16px 22px;border-bottom:1px solid var(--border);gap:12px }
.sa-recon-row { display:flex;align-items:center;justify-content:space-between;
                padding:9px 22px;gap:12px;transition:background var(--tr) }
.sa-recon-row:hover { background:var(--surface2) }
.sa-recon-lbl { display:flex;align-items:center;gap:10px;min-width:0 }
.sa-recon-dot { width:8px;height:8px;border-radius:50%;flex-shrink:0 }
.sa-recon-val { font-size:13px;font-weight:700;font-family:var(--mono);white-space:nowrap }
.sa-recon-sub { display:flex;align-items:center;justify-content:space-between;
                padding:10px 22px;background:var(--surface2) }
.sa-recon-results { display:grid;grid-template-columns:1fr 1fr;border-top:1px solid var(--border) }
.sa-recon-result  { padding:18px 22px }
.sa-recon-result:first-child { border-right:1px solid var(--border) }

/* ── Margin health pill ──────────────────────────────────────────── */
.sa-margin-pill { display:inline-flex;align-items:center;font-size:11px;font-weight:700;
                  padding:2px 8px;border-radius:10px;font-family:var(--mono);white-space:nowrap }

/* ── Empty state ─────────────────────────────────────────────────── */
.sa-empty { padding:56px 20px;text-align:center }
.sa-empty-title { font-size:14px;font-weight:700;color:var(--text-sub);margin-bottom:5px }
.sa-empty-sub   { font-size:12px;color:var(--text-dim) }

/* ── Seller avatar ───────────────────────────────────────────────── */
.sa-avatar { width:30px;height:30px;border-radius:50%;display:grid;place-items:center;
             font-size:12px;font-weight:700;border:1px solid var(--border);flex-shrink:0 }

/* ── Rank badge — neutral, ledger only ───────────────────────────── */
.sa-rank { width:22px;height:22px;border-radius:5px;display:inline-flex;align-items:center;
           justify-content:center;font-size:10px;font-weight:800;font-family:var(--mono);
           flex-shrink:0;background:var(--surface2);color:var(--text-sub);
           border:1px solid var(--border) }

/* ── Card section header left rule ───────────────────────────────── */
.sa-head-accent { border-left:2px solid var(--border-hi);padding-left:12px }

/* ── Ledger column-group header row ──────────────────────────────── */
.sa-colgrp th { padding:5px 14px 4px;font-size:9px;font-weight:700;
                letter-spacing:.8px;text-transform:uppercase;color:var(--text-dim) }
.sa-colgrp th.grp-revenue,.sa-colgrp th.grp-profit,.sa-colgrp th.grp-credit { color:var(--text-dim) }

/* ── Hiding columns on mobile ────────────────────────────────────── */
.sa-hide-mob { }

/* ── Mobile card transform for dense tables ──────────────────────── */
@media(max-width:640px) {
    .sa-hide-mob { display:none !important }
    .sa-cards-mob thead { display:none }
    .sa-cards-mob tfoot { display:none }
    .sa-cards-mob tbody { display:block }
    .sa-cards-mob tr    { display:block;padding:11px 14px;border-bottom:1px solid var(--border) }
    .sa-cards-mob tr:last-child { border-bottom:none }
    .sa-cards-mob td   { display:flex;justify-content:space-between;align-items:center;
                         padding:3px 0;font-size:13px }
    .sa-cards-mob td[data-label]::before {
        content:attr(data-label);font-size:11px;font-weight:700;color:var(--text-dim);
        text-transform:uppercase;letter-spacing:.4px;flex-shrink:0;margin-right:8px }
    .sa-cards-mob td.sa-row-title { display:block;padding-bottom:7px;
                                    border-bottom:1px solid var(--border);margin-bottom:4px }
    .sa-cards-mob td.sa-row-title::before { display:none }

    /* Ledger/audit keep as horizontal scroll */
    .sa-ledger-tbl { min-width:900px !important }
    .sa-ledger-tbl thead,.sa-ledger-tbl tbody,.sa-ledger-tbl tfoot,
    .sa-ledger-tbl tr,.sa-ledger-tbl th,.sa-ledger-tbl td { display:revert !important }

    .sa-audit-tbl { min-width:1360px !important }
    .sa-audit-tbl thead,.sa-audit-tbl tbody,.sa-audit-tbl tfoot,
    .sa-audit-tbl tr,.sa-audit-tbl th,.sa-audit-tbl td { display:revert !important }
}

/* ── Responsive ──────────────────────────────────────────────────── */
@media(max-width:768px) {
    .sa-kpis { grid-template-columns:1fr 1fr;gap:10px }
    .sa-kpi  { padding:14px }
    .sa-kpi-val { font-size:22px }
    .sa-kpi-footer { grid-template-columns:1fr 1fr;gap:0 }
    .sa-two-col { grid-template-columns:1fr !important }
    .sa-controls { flex-direction:column;align-items:stretch }
    .sa-ctrl-seg { border-right:none;border-bottom:1px solid var(--border);flex-wrap:wrap }
    .sa-ctrl-seg:last-child { border-bottom:none }
    .sa-ctrl-grow { flex:1 1 auto }
    .sa-date-in  { flex:1;width:auto;min-width:80px;max-width:none }
    .sa-shop-sel { max-width:none;width:100% }
    .sa-hours-grid,.sa-hours-labels { grid-template-columns:repeat(12,1fr) }
    .sa-recon-results { grid-template-columns:1fr }
    .sa-recon-result:first-child { border-right:none;border-bottom:1px solid var(--border) }
    .sa-pay-name { width:80px }
    .sa-pay-amt  { width:80px }
    .sa-pay-pct  { display:none }
    .sa-strip-item { min-width:calc(50% - 1px) }
    .sa-card-head { flex-wrap:wrap;gap:8px }
    .sa-presets { padding:10px 12px }
    .sa-preset  { padding:5px 10px;font-size:11px }
    .sa-mix-item { min-width:calc(50% - 6px) }
}
@media(max-width:640px) {
    .sa-tabs { display:flex;overflow-x:auto;-webkit-overflow-scrolling:touch;
               scrollbar-width:none;border-radius:var(--r);flex-wrap:nowrap }
    .sa-tabs::-webkit-scrollbar { display:none }
    .sa-tab  { flex-shrink:0;min-width:110px;padding:11px 16px;font-size:12px;border-radius:0 }
    .sa-tab svg { display:none }
}
@media(max-width:480px) {
    .sa-kpis { grid-template-columns:1fr;gap:8px }
    .sa-kpi  { padding:14px 14px;gap:12px }
    .sa-kpi-icon { width:30px;height:30px }
    .sa-kpi-val  { font-size:20px }
    .sa-kpi-footer { grid-template-columns:1fr 1fr }
    .sa-hours-grid,.sa-hours-labels { grid-template-columns:repeat(6,1fr) }
    .sa-strip-item { min-width:calc(50% - 1px);border-right:none;border-bottom:1px solid var(--border) }
    .sa-pay-amt  { display:none }
    .sa-alert-strip { flex-direction:column;align-items:flex-start }
    .sa-mix-item { min-width:100% }
}
</style>

{{-- ══════════════════════════════════════════════════════════════════════════
     PERIOD FILTER BAR
══════════════════════════════════════════════════════════════════════════ --}}
@php
    $periods = [
        'today'   => 'Today',
        'week'    => 'This Week',
        'month'   => 'This Month',
        'quarter' => 'This Quarter',
        'year'    => 'This Year',
    ];
    $currentPeriod = 'custom';
    $periodStarts = [
        'today'   => now()->startOfDay()->toDateString(),
        'week'    => now()->startOfWeek()->toDateString(),
        'month'   => now()->startOfMonth()->toDateString(),
        'quarter' => now()->startOfQuarter()->toDateString(),
        'year'    => now()->startOfYear()->toDateString(),
    ];
    foreach ($periodStarts as $key => $start) {
        if ($dateFrom === $start && $dateTo === now()->toDateString()) {
            $currentPeriod = $key;
            break;
        }
    }
@endphp

<div class="sa-filters">
    {{-- Preset pills --}}
    <div class="sa-presets">
        @foreach($periods as $key => $label)
        <button type="button" wire:key="preset-{{ $key }}" wire:click="setDateRange('{{ $key }}')"
                class="sa-preset {{ $currentPeriod === $key ? 'active' : '' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>
    {{-- Controls row --}}
    <div class="sa-controls">
        <div class="sa-ctrl-seg sa-ctrl-grow">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" style="flex-shrink:0;color:var(--text-dim)">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/>
            </svg>
            <input type="date" wire:model="dateFrom" max="{{ $dateTo }}" class="sa-date-in">
            <span style="font-size:13px;color:var(--text-dim);flex-shrink:0">→</span>
            <input type="date" wire:model="dateTo" min="{{ $dateFrom }}" max="{{ now()->toDateString() }}" class="sa-date-in">
        </div>
        <div class="sa-ctrl-seg">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" style="color:var(--text-dim);flex-shrink:0">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            </svg>
            <select wire:model.live="locationFilter" class="sa-shop-sel">
                <option value="all">All Shops</option>
                @foreach($this->shops as $shop)
                <option value="shop:{{ $shop->id }}">{{ $shop->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="sa-ctrl-seg" style="gap:6px">
            <span style="width:7px;height:7px;border-radius:50%;background:var(--green);flex-shrink:0"></span>
            <span style="font-size:12px;color:var(--text-dim)">Live · 60s</span>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB BAR
══════════════════════════════════════════════════════════════════════════ --}}
@php
    $tabs = [
        'overview' => ['label' => 'Overview',     'icon' => 'M3 3h7v7H3zm11 0h7v7h-7zM3 14h7v7H3zm11 0h7v7h-7z'],
        'ledger'   => ['label' => 'Sales Ledger', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        'audit'    => ['label' => 'Price Audit',  'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        'sellers'  => ['label' => 'Sellers',      'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
        'payments' => ['label' => 'Payments',     'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
        'credit'   => ['label' => 'Credit',       'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
    ];
@endphp
<div class="sa-tabs">
    @foreach($tabs as $key => $tab)
    <button type="button" wire:click="setTab('{{ $key }}')"
            class="sa-tab {{ $activeTab === $key ? 'active' : '' }}">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="{{ $tab['icon'] }}"/>
        </svg>
        {{ $tab['label'] }}
    </button>
    @endforeach
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: OVERVIEW
══════════════════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'overview')
@php
    $rev = $this->revenueKpis;
    $gp  = $this->grossProfitKpis;
    $iss = $this->itemsSoldKpi;
    $ret = $this->returnsImpact;
    $vo  = $this->voidedSalesStats;
    $ov  = $this->priceOverrideStats;
@endphp

{{-- ── 4 KPI Cards ─────────────────────────────────────────────────────── --}}
<div class="sa-kpis">

    {{-- Revenue --}}
    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--pink-dim);color:var(--pink)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                </svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Revenue</div>
                <div class="sa-kpi-sub">{{ number_format($rev['transactions_count']) }} transactions</div>
            </div>
            @php $rg = $rev['growth_percentage'] @endphp
            <span class="sa-growth {{ $rg >= 0 ? 'up' : 'down' }}">{{ $rg >= 0 ? '↑' : '↓' }} {{ abs($rg) }}%</span>
        </div>
        <div class="sa-kpi-val" style="color:var(--text)">{{ number_format($rev['total_revenue']) }}</div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat">
                <span class="sa-kpi-stat-v" style="color:var(--text)">{{ number_format($rev['avg_transaction_value']) }}</span>
                <span class="sa-kpi-stat-l">Avg Order</span>
            </div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="sa-kpi-stat-v" style="color:{{ $rev['total_discount'] > 0 ? 'var(--amber)' : 'var(--text-dim)' }}">{{ number_format($rev['total_discount']) }}</span>
                <span class="sa-kpi-stat-l">Discounts</span>
            </div>
            <div class="sa-kpi-stat">
                <span class="sa-kpi-stat-v" style="color:var(--text-dim)">{{ number_format($rev['previous_revenue']) }}</span>
                <span class="sa-kpi-stat-l">Prev Period</span>
            </div>
        </div>
    </div>

    {{-- Gross Profit --}}
    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--green-dim);color:var(--green)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>
                </svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Gross Profit</div>
                <div class="sa-kpi-sub">After cost of goods</div>
            </div>
            @php $gg = $gp['gross_profit_growth'] @endphp
            <span class="sa-growth {{ $gg >= 0 ? 'up' : 'down' }}">{{ $gg >= 0 ? '↑' : '↓' }} {{ abs($gg) }}%</span>
        </div>
        <div class="sa-kpi-val" style="color:var(--green)">{{ number_format($gp['gross_profit']) }}</div>
        <div class="sa-kpi-bar" style="background:var(--green-dim)">
            <div style="height:100%;border-radius:3px;background:var(--green);width:{{ min($gp['margin_pct'], 100) }}%"></div>
        </div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat">
                @php $mp = $gp['margin_pct'] @endphp
                <span class="sa-kpi-stat-v" style="color:{{ $mp >= 30 ? 'var(--green)' : ($mp >= 15 ? 'var(--amber)' : 'var(--red)') }}">{{ $mp }}%</span>
                <span class="sa-kpi-stat-l">Margin</span>
            </div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="sa-kpi-stat-v" style="color:var(--text-dim)">{{ number_format($gp['total_cost']) }}</span>
                <span class="sa-kpi-stat-l">COGS</span>
            </div>
            <div class="sa-kpi-stat">
                @php $md = $gp['margin_delta'] @endphp
                <span class="sa-kpi-stat-v" style="color:{{ $md >= 0 ? 'var(--green)' : 'var(--red)' }}">{{ $md >= 0 ? '+' : '' }}{{ $md }}pp</span>
                <span class="sa-kpi-stat-l">vs Prev</span>
            </div>
        </div>
    </div>

    {{-- Items Sold --}}
    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--violet-dim);color:var(--violet)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                </svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Items Sold</div>
                <div class="sa-kpi-sub">Units in period</div>
            </div>
            @php $ig = $iss['growth'] @endphp
            <span class="sa-growth {{ $ig >= 0 ? 'up' : 'down' }}">{{ $ig >= 0 ? '↑' : '↓' }} {{ abs($ig) }}%</span>
        </div>
        <div class="sa-kpi-val" style="color:var(--violet)">{{ number_format($iss['items_sold']) }}</div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat">
                <span class="sa-kpi-stat-v" style="color:var(--text-sub)">{{ $ov['override_sales_count'] }}</span>
                <span class="sa-kpi-stat-l">Overrides</span>
            </div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="sa-kpi-stat-v" style="color:var(--text-sub)">{{ $vo['voided_count'] }}</span>
                <span class="sa-kpi-stat-l">Voided</span>
            </div>
            <div class="sa-kpi-stat">
                <span class="sa-kpi-stat-v" style="color:var(--text-dim)">{{ $ov['override_rate'] }}%</span>
                <span class="sa-kpi-stat-l">Override %</span>
            </div>
        </div>
    </div>

    {{-- Net Revenue / Returns --}}
    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--accent-dim);color:var(--accent)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Net Revenue</div>
                <div class="sa-kpi-sub">After {{ $ret['returns_count'] }} returns</div>
            </div>
            @php $rr = $ret['return_rate'] @endphp
            <span class="sa-growth {{ $rr > 5 ? 'down' : 'neutral' }}">{{ $rr }}% ret.</span>
        </div>
        <div class="sa-kpi-val" style="color:var(--accent)">{{ number_format($gp['net_revenue']) }}</div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat">
                <span class="sa-kpi-stat-v" style="color:var(--text-sub)">{{ number_format($ret['returned_revenue']) }}</span>
                <span class="sa-kpi-stat-l">Refunded</span>
            </div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="sa-kpi-stat-v" style="color:var(--text-dim)">{{ $ret['exchange_count'] }}</span>
                <span class="sa-kpi-stat-l">Exchanges</span>
            </div>
            <div class="sa-kpi-stat">
                <span class="sa-kpi-stat-v" style="color:var(--text-dim)">{{ $ret['items_returned'] }}</span>
                <span class="sa-kpi-stat-l">Items Back</span>
            </div>
        </div>
    </div>

</div>

{{-- ── Sales Mix ─────────────────────────────────────────────────────────── --}}
@php $saleTypes = $this->saleTypeBreakdown @endphp
@if(count($saleTypes))
<div class="sa-card" style="padding:0;margin-bottom:20px">
    <div class="sa-card-head">
        <div>
            <div class="sa-card-title">Sales Mix</div>
            <div class="sa-card-sub">Box vs item-level breakdown · {{ $this->activeDateRangeLabel }}</div>
        </div>
    </div>
    <div style="padding:16px 20px">
        <div class="sa-mix-grid">
            @foreach($saleTypes as $st)
            @php
                $stColor = match($st['type']) { 'FULL_BOX' => 'var(--accent)', 'INDIVIDUAL_ITEMS' => 'var(--green)', default => 'var(--violet)' };
                $stDim   = match($st['type']) { 'FULL_BOX' => 'var(--accent-dim)', 'INDIVIDUAL_ITEMS' => 'var(--green-dim)', default => 'var(--violet-dim)' };
            @endphp
            <div class="sa-mix-item" style="background:{{ $stDim }}">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                    <span style="font-size:11px;font-weight:700;color:{{ $stColor }};text-transform:uppercase;letter-spacing:.5px">{{ $st['label'] }}</span>
                    <span style="font-size:12px;font-weight:700;color:{{ $stColor }};font-family:var(--mono)">{{ $st['revenue_share'] }}%</span>
                </div>
                <div style="font-size:22px;font-weight:800;font-family:var(--mono);color:{{ $stColor }};letter-spacing:-1px;line-height:1">{{ number_format($st['revenue']) }}</div>
                <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:4px">{{ $st['count'] }} {{ $st['count'] === 1 ? 'sale' : 'sales' }}</div>
                <div class="sa-mix-bar">
                    <div style="height:100%;width:{{ $st['revenue_share'] }}%;background:{{ $stColor }};border-radius:3px;transition:width .6s ease"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ── Price Override Alert ──────────────────────────────────────────────── --}}
@if($ov['override_sales_count'] > 0)
<div class="sa-alert-strip">
    <div style="display:flex;align-items:center;gap:12px;min-width:0">
        <div style="width:32px;height:32px;border-radius:8px;background:var(--amber);display:grid;place-items:center;flex-shrink:0;color:#fff">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>
        <div>
            <div style="font-size:12px;font-weight:700;color:var(--text)">Price Overrides Detected</div>
            <div style="font-size:11px;color:var(--text-sub);margin-top:2px;font-family:var(--mono)">
                <span style="color:var(--amber);font-weight:700">{{ $ov['override_sales_count'] }}</span> sales ·
                <span style="color:var(--amber);font-weight:700">{{ $ov['override_items_count'] }}</span> items ·
                <span style="color:var(--red);font-weight:700">{{ number_format($ov['total_discount_given']) }} RWF</span> discounted ·
                {{ $ov['override_rate'] }}% override rate
            </div>
        </div>
    </div>
    <button type="button" wire:click="setTab('audit')" class="sa-alert-btn">View Audit Trail →</button>
</div>
@endif

{{-- ── Revenue Trend Chart ───────────────────────────────────────────────── --}}
@php $trend = $this->revenueTrend @endphp
@if(count($trend))
<div wire:key="chart-{{ $dateFrom }}-{{ $dateTo }}-{{ $locationFilter }}" class="sa-card" style="margin-bottom:20px">
    <div class="sa-card-head">
        <div>
            <div class="sa-card-title">Revenue Trend</div>
            <div class="sa-card-sub">Daily revenue + transactions · {{ $this->activeDateRangeLabel }}</div>
        </div>
    </div>
    <div style="padding:16px 20px">
        <div id="rev-trend-chart"
             style="min-height:240px"
             data-dates='@json(array_column($trend, "date"))'
             data-revenues='@json(array_column($trend, "revenue"))'
             data-transactions='@json(array_column($trend, "transactions"))'></div>
    </div>
</div>
@endif

{{-- ── Peak Hours ────────────────────────────────────────────────────────── --}}
@php $hourData = $this->salesByHour; $maxHourCount = max(array_column($hourData, 'count') ?: [1]); @endphp
@if(count($hourData) && $maxHourCount > 0)
<div class="sa-card" style="margin-bottom:20px">
    <div class="sa-card-head">
        <div>
            <div class="sa-card-title">Peak Sales Hours</div>
            <div class="sa-card-sub">Transaction intensity by hour · darker = busier</div>
        </div>
        @php
            $busyHour = collect($hourData)->sortByDesc('count')->first();
            $busyLabel = $busyHour ? sprintf('%02d:00', $busyHour['hour']) : '—';
        @endphp
        <span class="sa-card-badge" style="background:var(--accent-dim);color:var(--accent);padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700">
            Peak: {{ $busyLabel }}
        </span>
    </div>
    <div class="sa-hours-wrap">
        <div class="sa-hours-grid">
            @foreach($hourData as $h)
            @php
                $intensity = $maxHourCount > 0 ? ($h['count'] / $maxHourCount) : 0;
                $alpha = max(0.06, $intensity);
            @endphp
            <div class="sa-hours-cell" title="{{ sprintf('%02d:00', $h['hour']) }} — {{ $h['count'] }} txns · {{ number_format($h['revenue']) }} RWF"
                 style="background:rgba(59,111,212,{{ round($alpha, 2) }});color:{{ $intensity > 0.5 ? '#fff' : 'var(--text-dim)' }}">
                @if($h['count'] > 0){{ $h['count'] }}@endif
            </div>
            @endforeach
        </div>
        <div class="sa-hours-labels">
            @foreach($hourData as $h)
            <div class="sa-hours-lbl">{{ $h['hour'] % 6 === 0 ? sprintf('%02d', $h['hour']) : '' }}</div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ── Daily Scorecard ──────────────────────────────────────────────────── --}}
@php
    $scorecard = $this->dailyScorecard;
    $scorecard = array_filter($scorecard, fn($day) => $day['transactions'] > 0);
@endphp
<div class="sa-tbl-wrap" style="margin-bottom:20px">
    <div class="sa-card-head">
        <div>
            <div class="sa-card-title">Daily Scorecard</div>
            <div class="sa-card-sub">Day-by-day breakdown · revenue, profit, returns</div>
        </div>
    </div>
    <div class="sa-tbl-scroll">
        <table class="sa-tbl sa-cards-mob" style="width:100%">
            <thead>
                <tr>
                    <th>Date</th>
                    <th style="text-align:right">Revenue</th>
                    <th class="sa-hide-mob" style="text-align:right">Txns</th>
                    <th class="sa-hide-mob" style="text-align:right">Items</th>
                    <th style="text-align:right;color:var(--green)">Gross Profit</th>
                    <th style="text-align:right;color:var(--green)">Margin</th>
                    <th class="sa-hide-mob" style="text-align:right">Discounts</th>
                    <th class="sa-hide-mob" style="text-align:right;color:var(--red)">Returns</th>
                    <th style="text-align:right">Net Rev.</th>
                </tr>
            </thead>
            <tbody>
                @forelse(array_reverse($scorecard) as $day)
                <tr style="{{ $day['is_today'] ? 'background:var(--accent-dim)' : '' }}">
                    <td class="sa-row-title">
                        <div style="display:flex;align-items:center;gap:8px">
                            <span style="font-weight:600;color:{{ $day['is_today'] ? 'var(--accent)' : 'var(--text)' }}">{{ $day['day_label'] }}</span>
                            @if($day['is_today'])<span style="font-size:10px;font-weight:700;color:var(--accent);background:var(--accent-dim);padding:1px 6px;border-radius:10px">TODAY</span>@endif
                        </div>
                    </td>
                    <td data-label="Revenue" style="text-align:right;font-family:var(--mono);font-weight:600;color:{{ $day['revenue'] > 0 ? 'var(--text)' : 'var(--text-dim)' }}">
                        {{ $day['revenue'] > 0 ? number_format($day['revenue']) : '—' }}
                    </td>
                    <td class="sa-hide-mob" data-label="Txns" style="text-align:right;font-family:var(--mono);color:var(--text-sub)">
                        {{ $day['transactions'] > 0 ? $day['transactions'] : '—' }}
                    </td>
                    <td class="sa-hide-mob" data-label="Items" style="text-align:right;font-family:var(--mono);color:var(--text-sub)">
                        {{ $day['items_sold'] > 0 ? number_format($day['items_sold']) : '—' }}
                    </td>
                    <td data-label="Profit" style="text-align:right;font-family:var(--mono);font-weight:600;color:var(--green)">
                        {{ $day['gross_profit'] > 0 ? number_format($day['gross_profit']) : '—' }}
                    </td>
                    <td data-label="Margin" style="text-align:right">
                        @if($day['margin_pct'] > 0)
                        @php $mp = $day['margin_pct'] @endphp
                        <span class="sa-margin-pill"
                              style="background:{{ $mp >= 30 ? 'var(--green-dim)' : ($mp >= 15 ? 'var(--amber-dim)' : 'var(--red-dim)') }};
                                     color:{{ $mp >= 30 ? 'var(--green)' : ($mp >= 15 ? 'var(--amber)' : 'var(--red)') }}">
                            {{ $mp }}%
                        </span>
                        @else
                        <span style="color:var(--text-dim)">—</span>
                        @endif
                    </td>
                    <td class="sa-hide-mob" data-label="Discounts" style="text-align:right;font-family:var(--mono);color:{{ $day['discounts'] > 0 ? 'var(--amber)' : 'var(--text-dim)' }}">
                        {{ $day['discounts'] > 0 ? number_format($day['discounts']) : '—' }}
                    </td>
                    <td class="sa-hide-mob" data-label="Returns" style="text-align:right;font-family:var(--mono);color:{{ $day['returns_count'] > 0 ? 'var(--red)' : 'var(--text-dim)' }}">
                        {{ $day['returns_count'] > 0 ? $day['returns_count'].' · '.number_format($day['returned_amount']) : '—' }}
                    </td>
                    <td data-label="Net Rev." style="text-align:right;font-family:var(--mono);font-weight:600;color:var(--text)">
                        {{ $day['net_revenue'] > 0 ? number_format($day['net_revenue']) : '—' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="9"><div class="sa-empty"><div class="sa-empty-title">No sales in this period</div></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Bottom row: Top Products | Shop Performance + Payments ──────────── --}}
@php $topProducts = $this->topProducts; $shops = $this->shopPerformance; $methods = $this->paymentMethods; @endphp
<div class="sa-two-col" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">

    {{-- Top Products --}}
    <div class="sa-tbl-wrap" style="margin-bottom:0">
        <div class="sa-card-head">
            <div>
                <div class="sa-card-title">Top Products</div>
                <div class="sa-card-sub">By revenue · with margin</div>
            </div>
        </div>
        <div style="overflow:auto;max-height:360px">
            <table class="sa-tbl" style="width:100%">
                <thead style="position:sticky;top:0">
                    <tr>
                        <th>Product</th>
                        <th style="text-align:right">Revenue</th>
                        <th style="text-align:right;color:var(--green)">Margin</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topProducts as $p)
                    <tr>
                        <td>
                            <div style="font-weight:600;color:var(--text);font-size:12px">{{ $p['product_name'] }}</div>
                            <div style="font-size:10px;color:var(--text-dim);font-family:var(--mono);margin-top:1px">{{ number_format($p['quantity_sold']) }} units · {{ $p['revenue_share'] }}% share</div>
                        </td>
                        <td style="text-align:right;font-family:var(--mono);font-size:12px;font-weight:600;color:var(--text)">{{ number_format($p['revenue']) }}</td>
                        <td style="text-align:right">
                            <span class="sa-margin-pill"
                                  style="background:{{ $p['margin_pct'] >= 30 ? 'var(--green-dim)' : ($p['margin_pct'] >= 15 ? 'var(--amber-dim)' : 'var(--red-dim)') }};
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

    {{-- Shop Performance + Payment Methods --}}
    <div style="display:flex;flex-direction:column;gap:20px">

        {{-- Shop Performance --}}
        <div class="sa-tbl-wrap" style="margin-bottom:0">
            <div class="sa-card-head">
                <div>
                    <div class="sa-card-title">Shop Performance</div>
                    <div class="sa-card-sub">Revenue share and growth</div>
                </div>
            </div>
            @php $maxShopRev = max(array_column($shops, 'revenue') ?: [1]) @endphp
            @foreach($shops as $shop)
            <div style="padding:12px 20px;border-bottom:1px solid var(--border)">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                    <span style="font-size:12px;font-weight:600;color:var(--text)">{{ $shop['shop_name'] }}</span>
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="font-size:12px;font-family:var(--mono);font-weight:600;color:var(--text)">{{ number_format($shop['revenue']) }}</span>
                        @if($shop['growth'] !== null)
                        <span class="sa-margin-pill"
                              style="background:{{ $shop['growth'] >= 0 ? 'var(--green-dim)' : 'var(--red-dim)' }};
                                     color:{{ $shop['growth'] >= 0 ? 'var(--green)' : 'var(--red)' }};font-size:10px">
                            {{ $shop['growth'] >= 0 ? '+' : '' }}{{ $shop['growth'] }}%
                        </span>
                        @endif
                    </div>
                </div>
                <div style="height:5px;background:var(--surface2);border-radius:3px;overflow:hidden">
                    <div style="height:100%;width:{{ $maxShopRev > 0 ? round($shop['revenue'] / $maxShopRev * 100) : 0 }}%;background:var(--accent);border-radius:3px;transition:width .6s ease"></div>
                </div>
                <div style="display:flex;gap:12px;margin-top:5px">
                    <span style="font-size:10px;color:var(--text-dim)">{{ $shop['transactions'] }} txns</span>
                    <span style="font-size:10px;color:var(--text-dim)">{{ $shop['revenue_share'] }}% share</span>
                    @if($shop['override_count'] > 0)
                    <span style="font-size:10px;color:var(--amber)">{{ $shop['override_count'] }} overrides</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Payment Methods --}}
        <div class="sa-tbl-wrap" style="margin-bottom:0">
            <div class="sa-card-head">
                <div class="sa-card-title">Payment Mix</div>
            </div>
            @php
                $payColors = ['cash'=>'var(--green)','card'=>'var(--accent)','mobile_money'=>'var(--pink)','bank_transfer'=>'var(--amber)','credit'=>'var(--red)'];
                $maxMethodRev = max(array_column($methods, 'revenue') ?: [1]);
            @endphp
            @foreach($methods as $m)
            @php $dotColor = $payColors[$m['method'] ?? ''] ?? 'var(--text-dim)'; @endphp
            <div class="sa-pay-row">
                <div class="sa-pay-dot" style="background:{{ $dotColor }}"></div>
                <span class="sa-pay-name">{{ $m['label'] }}</span>
                <div class="sa-pay-bar-wrap">
                    <div class="sa-pay-bar" style="width:{{ $maxMethodRev > 0 ? round($m['revenue'] / $maxMethodRev * 100) : 0 }}%;background:{{ $dotColor }}"></div>
                </div>
                <span class="sa-pay-amt">{{ number_format($m['revenue']) }}</span>
                <span class="sa-pay-pct">{{ $m['revenue_share'] }}%</span>
            </div>
            @endforeach
        </div>

    </div>
</div>

{{-- ── Returns Impact ────────────────────────────────────────────────────── --}}
@if(count($ret['top_returned_products']))
<div class="sa-card" style="margin-bottom:20px">
    <div class="sa-card-head">
        <div style="display:flex;align-items:center;gap:10px">
            <div class="sa-kpi-icon" style="background:var(--red-dim);color:var(--red)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
            <div>
                <div class="sa-card-title">Returns Impact</div>
                <div class="sa-card-sub">{{ $ret['returns_count'] }} returns · {{ number_format($ret['returned_revenue']) }} RWF refunded · {{ $ret['return_rate'] }}% of gross</div>
            </div>
        </div>
    </div>
    <div style="display:flex;flex-wrap:wrap">
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
    $lcShopId = $locationFilter !== 'all' ? (int) str_replace('shop:', '', $locationFilter) : null;

    $periodCreditQ = \App\Models\Sale::whereNull('voided_at')->where('has_credit', true)->whereBetween('sale_date', [$dateFrom, $dateTo]);
    if ($lcShopId) { $periodCreditQ->where('shop_id', $lcShopId); }
    $periodCreditGiven = (int) $periodCreditQ->sum('credit_amount');

    $custQ = \App\Models\Customer::query();
    if ($lcShopId) { $custQ->where('shop_id', $lcShopId); }
    $trueOutstanding   = (int) (clone $custQ)->sum('outstanding_balance');
    $totalCreditRepaid = (int) (clone $custQ)->sum('total_repaid');
    $totalCreditGiven  = (int) (clone $custQ)->sum('total_credit_given');
    $repaymentRate     = $totalCreditGiven > 0 ? round(($totalCreditRepaid / $totalCreditGiven) * 100, 1) : 0;
    $collectedRevenue  = $gp['revenue'] - $trueOutstanding;
    $profitOnPaper     = $gp['gross_profit'];
    $profitInHand      = $collectedRevenue - $gp['total_cost'];
    $profitGap         = $profitOnPaper - $profitInHand;
    $collectedMarginPct = $collectedRevenue > 0 ? round(($profitInHand / $collectedRevenue) * 100, 1) : 0;
    $creditRiskPct     = $gp['revenue'] > 0 ? round(($trueOutstanding / $gp['revenue']) * 100, 1) : 0;
    $gapIsMaterial     = $profitGap > 0 && $gp['gross_profit'] > 0 && ($profitGap / $gp['gross_profit']) > 0.05;
@endphp

<div class="sa-kpis">
    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--accent-dim);color:var(--accent)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Gross Revenue</div>
                <div class="sa-kpi-sub">{{ $rev['transactions_count'] }} transactions</div>
            </div>
        </div>
        <div class="sa-kpi-val" style="color:var(--text)">{{ number_format($gp['revenue']) }}</div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ number_format($gp['total_cost']) }}</span><span class="sa-kpi-stat-l">Cost</span></div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)"><span class="sa-kpi-stat-v">{{ number_format($iss['items_sold']) }}</span><span class="sa-kpi-stat-l">Items</span></div>
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $rev['transactions_count'] }}</span><span class="sa-kpi-stat-l">Txns</span></div>
        </div>
    </div>

    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--green-dim);color:var(--green)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Gross Profit</div>
                <div class="sa-kpi-sub">{{ $gp['margin_pct'] }}% margin</div>
            </div>
        </div>
        <div class="sa-kpi-val" style="color:var(--green)">{{ number_format($gp['gross_profit']) }}</div>
        <div class="sa-kpi-bar" style="background:var(--green-dim)">
            <div style="height:100%;border-radius:3px;background:var(--green);width:{{ min($gp['margin_pct'], 100) }}%"></div>
        </div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $gp['margin_pct'] }}%</span><span class="sa-kpi-stat-l">Margin</span></div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)"><span class="sa-kpi-stat-v">{{ number_format($gp['total_cost']) }}</span><span class="sa-kpi-stat-l">COGS</span></div>
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ number_format($iss['items_sold']) }}</span><span class="sa-kpi-stat-l">Units</span></div>
        </div>
    </div>

    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--violet-dim);color:var(--violet)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Items Sold</div>
                <div class="sa-kpi-sub">Units in period</div>
            </div>
        </div>
        <div class="sa-kpi-val" style="color:var(--text)">{{ number_format($iss['items_sold']) }}</div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $rev['transactions_count'] }}</span><span class="sa-kpi-stat-l">Txns</span></div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)"><span class="sa-kpi-stat-v">{{ $iss['items_sold'] > 0 && $rev['transactions_count'] > 0 ? round($iss['items_sold'] / $rev['transactions_count'], 1) : '—' }}</span><span class="sa-kpi-stat-l">Per Txn</span></div>
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ number_format($rev['avg_transaction_value']) }}</span><span class="sa-kpi-stat-l">Avg Order</span></div>
        </div>
    </div>

    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--amber-dim);color:var(--amber)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Outstanding Credit</div>
                <div class="sa-kpi-sub">{{ $repaymentRate }}% repaid</div>
            </div>
        </div>
        <div class="sa-kpi-val" style="color:var(--text)">{{ number_format($trueOutstanding) }}</div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ number_format($totalCreditGiven) }}</span><span class="sa-kpi-stat-l">Total Given</span></div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)"><span class="sa-kpi-stat-v" style="color:var(--green)">{{ number_format($totalCreditRepaid) }}</span><span class="sa-kpi-stat-l">Repaid</span></div>
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $repaymentRate }}%</span><span class="sa-kpi-stat-l">Rate</span></div>
        </div>
    </div>
</div>

{{-- Credit footnote --}}
@if($trueOutstanding > 0 || $totalCreditGiven > 0)
<div style="padding:9px 14px;border-left:3px solid var(--border-hi);background:var(--surface2);
            border-radius:var(--rsm);margin-bottom:16px">
    <div style="font-size:11px;color:var(--text-dim);line-height:1.6">
        <strong style="color:var(--text-sub)">Outstanding Credit</strong>
        ({{ number_format($trueOutstanding) }} RWF) = current unpaid balance across all customers after
        {{ number_format($totalCreditRepaid) }} RWF repaid ({{ $repaymentRate }}% repayment rate).
        The <strong style="color:var(--text-sub)">Credit Sales</strong> column shows each product's proportional share of credit given in the selected period.
    </div>
</div>
@endif

{{-- Product Ledger Table --}}
<div class="sa-tbl-wrap">
    <div class="sa-card-head">
        <div class="sa-head-accent">
            <div class="sa-card-title">Product Sales Ledger</div>
            <div class="sa-card-sub">Revenue, cost, and gross profit per product · {{ $this->activeDateRangeLabel }}</div>
        </div>
        <span class="sa-card-badge">{{ count($topP) }} products</span>
    </div>
    <div class="sa-tbl-scroll">
        <table class="sa-tbl sa-ledger-tbl" style="min-width:1150px;table-layout:fixed">
            <colgroup>
                <col style="width:44px">
                <col style="width:220px"><col style="width:70px"><col style="width:70px">
                <col style="width:110px"><col style="width:130px">
                <col style="width:90px"><col style="width:130px">
                <col style="width:90px"><col style="width:110px">
            </colgroup>
            <thead>
                <tr class="sa-colgrp" style="background:var(--bg)">
                    <th colspan="2" style="border-bottom:1px solid var(--border)"></th>
                    <th colspan="3" class="grp-revenue" style="text-align:center;border-bottom:1px solid var(--border-hi)">SALES VOLUME</th>
                    <th colspan="2" class="grp-revenue" style="text-align:center;border-bottom:1px solid var(--border-hi)">REVENUE</th>
                    <th colspan="2" class="grp-profit" style="text-align:center;border-bottom:1px solid var(--border-hi)">PROFITABILITY</th>
                    <th class="grp-credit" style="text-align:center;border-bottom:1px solid var(--border-hi)">CREDIT</th>
                </tr>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th style="text-align:right">Units</th>
                    <th style="text-align:right">Txns</th>
                    <th style="text-align:right">Avg Price</th>
                    <th style="text-align:right">Revenue</th>
                    <th style="text-align:right">Share</th>
                    <th style="text-align:right">Gross Profit</th>
                    <th style="text-align:right">Margin</th>
                    <th style="text-align:right">Credit Sales</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topP as $i => $p)
                <tr>
                    <td><span class="sa-rank">{{ $i + 1 }}</span></td>
                    <td>
                        <div style="font-weight:600;color:var(--text);font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $p['product_name'] }}">{{ $p['product_name'] }}</div>
                    </td>
                    <td style="text-align:right;font-family:var(--mono);color:var(--text-sub)">{{ number_format($p['quantity_sold']) }}</td>
                    <td style="text-align:right;font-family:var(--mono);color:var(--text-sub)">{{ $p['transaction_count'] }}</td>
                    <td class="sa-hide-mob" style="text-align:right;font-family:var(--mono);color:var(--text-sub);font-size:12px">{{ number_format($p['avg_selling_price']) }}</td>
                    <td style="text-align:right;font-family:var(--mono);font-weight:700;color:var(--text)">{{ number_format($p['revenue']) }}</td>
                    <td class="sa-hide-mob" style="text-align:right">
                        <div style="height:4px;background:var(--surface2);border-radius:2px;width:60px;display:inline-block;vertical-align:middle;margin-right:5px">
                            <div style="height:100%;width:{{ $p['revenue_share'] }}%;background:var(--accent);border-radius:2px"></div>
                        </div>
                        <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim)">{{ $p['revenue_share'] }}%</span>
                    </td>
                    <td style="text-align:right;font-family:var(--mono);font-weight:700;color:var(--text)">{{ number_format($p['gross_profit']) }}</td>
                    <td style="text-align:right">
                        <span class="sa-margin-pill"
                              style="background:{{ $p['margin_pct'] >= 30 ? 'var(--green-dim)' : ($p['margin_pct'] >= 15 ? 'var(--amber-dim)' : 'var(--red-dim)') }};
                                     color:{{ $p['margin_pct'] >= 30 ? 'var(--green)' : ($p['margin_pct'] >= 15 ? 'var(--amber)' : 'var(--red)') }}">
                            {{ $p['margin_pct'] }}%
                        </span>
                    </td>
                    <td style="text-align:right;font-family:var(--mono);color:var(--text-sub)">
                        @if($p['credit_revenue'] > 0)
                        {{ number_format($p['credit_revenue']) }} <span style="font-size:10px;color:var(--text-dim)">{{ $p['credit_pct'] }}%</span>
                        @else—@endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="10"><div class="sa-empty"><div class="sa-empty-title">No sales in this period</div></div></td></tr>
                @endforelse
            </tbody>
            @if(count($topP))
            <tfoot>
                <tr>
                    <td></td>
                    <td colspan="4" style="color:var(--text-sub);font-size:11px;letter-spacing:.5px">TOTALS</td>
                    <td style="text-align:right;font-family:var(--mono);color:var(--text)">{{ number_format(array_sum(array_column($topP, 'revenue'))) }}</td>
                    <td style="text-align:right;font-size:11px;font-family:var(--mono);color:var(--text-dim)">100%</td>
                    <td style="text-align:right;font-family:var(--mono);font-weight:700;color:var(--text)">{{ number_format(array_sum(array_column($topP, 'gross_profit'))) }}</td>
                    <td style="text-align:right;font-family:var(--mono);color:var(--text-sub)">{{ $gp['margin_pct'] }}%</td>
                    <td style="text-align:right;font-family:var(--mono);color:var(--text-sub)">
                        @php $totalCredit = collect($topP)->sum('credit_revenue') @endphp
                        {{ $totalCredit > 0 ? number_format($totalCredit) : '—' }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- Revenue Reconciliation --}}
<div class="sa-recon">
    <div class="sa-recon-hd">
        <div class="sa-head-accent">
            <div class="sa-card-title">Revenue Reconciliation</div>
            <div class="sa-card-sub">Paper profit vs cash profit · {{ $this->activeDateRangeLabel }}</div>
        </div>
        @if($creditRiskPct > 0)
        <span class="sa-margin-pill" style="background:var(--surface2);color:var(--text-dim);font-size:11px">
            {{ $creditRiskPct }}% on credit
        </span>
        @endif
    </div>
    <div style="padding:8px 0">
        <div class="sa-recon-row">
            <div class="sa-recon-lbl">
                <div class="sa-recon-dot" style="background:var(--accent)"></div>
                <span style="font-size:13px;color:var(--text-sub)">Gross Revenue</span>
                <span style="font-size:10px;color:var(--text-dim);font-family:var(--mono);margin-left:8px">{{ $rev['transactions_count'] }} transactions</span>
            </div>
            <span class="sa-recon-val" style="color:var(--text);font-weight:700">{{ number_format($gp['revenue']) }}</span>
        </div>
        @if($trueOutstanding > 0)
        <div class="sa-recon-row">
            <div class="sa-recon-lbl">
                <div class="sa-recon-dot" style="background:var(--text-dim)"></div>
                <span style="font-size:13px;color:var(--text-sub)">Less: Outstanding Credit</span>
                <span style="font-size:10px;color:var(--text-dim);font-family:var(--mono);margin-left:8px">{{ $repaymentRate }}% repaid</span>
            </div>
            <span class="sa-recon-val" style="color:var(--text-sub)">({{ number_format($trueOutstanding) }})</span>
        </div>
        @endif
        <hr style="margin:4px 22px;border:none;border-top:1px solid var(--border)">
        <div class="sa-recon-sub">
            <span style="font-size:12px;font-weight:700;color:var(--text);text-transform:uppercase;letter-spacing:.5px">Net Collected Revenue</span>
            <span style="font-size:14px;font-weight:800;font-family:var(--mono);color:var(--text)">{{ number_format($collectedRevenue) }}</span>
        </div>
        <div class="sa-recon-row">
            <div class="sa-recon-lbl">
                <div class="sa-recon-dot" style="background:var(--text-dim)"></div>
                <span style="font-size:13px;color:var(--text-sub)">Less: Cost of Goods Sold</span>
            </div>
            <span class="sa-recon-val" style="color:var(--text-sub)">({{ number_format($gp['total_cost']) }})</span>
        </div>
        <hr style="margin:4px 22px;border:none;border-top:2px solid var(--border)">
    </div>
    <div class="sa-recon-results">
        <div class="sa-recon-result">
            <div style="font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--text-dim);margin-bottom:8px">Gross Profit on Paper</div>
            <div style="font-size:24px;font-weight:800;font-family:var(--mono);color:var(--text);letter-spacing:-1px;margin-bottom:6px">{{ number_format($profitOnPaper) }}</div>
            <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono)">{{ $gp['margin_pct'] }}% margin</div>
            <span class="sa-margin-pill" style="background:var(--surface2);color:var(--text-dim);margin-top:8px">Includes uncollected</span>
        </div>
        @php $inHandIsGreen = $profitInHand >= $profitOnPaper * 0.90 @endphp
        <div class="sa-recon-result" style="background:var(--surface2)">
            <div style="font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--text-dim);margin-bottom:8px">Gross Profit in Hand</div>
            <div style="font-size:24px;font-weight:800;font-family:var(--mono);color:{{ $profitInHand < 0 ? 'var(--red)' : 'var(--text)' }};letter-spacing:-1px;margin-bottom:6px">{{ number_format($profitInHand) }}</div>
            <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono)">
                {{ $collectedMarginPct }}% margin@if($profitGap > 0) · {{ number_format($profitGap) }} uncollected@endif
            </div>
            @if(!$inHandIsGreen)
            <span class="sa-margin-pill" style="background:var(--surface2);color:var(--text-dim);margin-top:8px">Credit gap</span>
            @endif
        </div>
    </div>
    @if($gapIsMaterial)
    <div style="margin:8px 16px 14px;padding:10px 14px;border-radius:var(--rsm);
                border-left:3px solid var(--border-hi);background:var(--surface2);
                font-size:11px;color:var(--text-sub);line-height:1.6">
        {{ number_format($profitGap) }} RWF ({{ round(($profitGap / $gp['gross_profit']) * 100, 1) }}% of gross profit)
        is earned but not yet collected — tied to {{ number_format($trueOutstanding) }} RWF in outstanding customer credit.
    </div>
    @endif
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

@php $avgDiscount = $overStat['override_items_count'] > 0 ? round($overStat['total_discount_given'] / $overStat['override_items_count']) : 0 @endphp
<div class="sa-kpis">
    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--amber-dim);color:var(--amber)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Overridden Sales</div>
                <div class="sa-kpi-sub">Sales with price changes</div>
            </div>
        </div>
        <div class="sa-kpi-val" style="color:var(--text)">{{ $overStat['override_sales_count'] }}</div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $overStat['override_items_count'] }}</span><span class="sa-kpi-stat-l">Items</span></div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)"><span class="sa-kpi-stat-v">{{ $overStat['override_rate'] }}%</span><span class="sa-kpi-stat-l">Rate</span></div>
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ number_format($avgDiscount) }}</span><span class="sa-kpi-stat-l">Avg Disc</span></div>
        </div>
    </div>

    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--accent-dim);color:var(--accent)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Overridden Items</div>
                <div class="sa-kpi-sub">Line items modified</div>
            </div>
        </div>
        <div class="sa-kpi-val" style="color:var(--text)">{{ $overStat['override_items_count'] }}</div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $overStat['override_sales_count'] }}</span><span class="sa-kpi-stat-l">Sales</span></div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)"><span class="sa-kpi-stat-v">{{ $overStat['override_rate'] }}%</span><span class="sa-kpi-stat-l">Rate</span></div>
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ number_format($avgDiscount) }}</span><span class="sa-kpi-stat-l">Avg/Item</span></div>
        </div>
    </div>

    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--red-dim);color:var(--red)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Discount Given</div>
                <div class="sa-kpi-sub">Revenue given away</div>
            </div>
        </div>
        <div class="sa-kpi-val" style="color:var(--text)">{{ number_format($overStat['total_discount_given']) }}</div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ number_format($avgDiscount) }}</span><span class="sa-kpi-stat-l">Avg/Item</span></div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)"><span class="sa-kpi-stat-v">{{ $overStat['override_items_count'] }}</span><span class="sa-kpi-stat-l">Items</span></div>
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $overStat['override_sales_count'] }}</span><span class="sa-kpi-stat-l">Sales</span></div>
        </div>
    </div>

    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--violet-dim);color:var(--violet)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Override Rate</div>
                <div class="sa-kpi-sub">Of all non-voided sales</div>
            </div>
            <span class="sa-growth {{ $overStat['override_rate'] > 20 ? 'down' : ($overStat['override_rate'] > 10 ? 'neutral' : 'up') }}">{{ $overStat['override_rate'] > 20 ? '↑ High' : ($overStat['override_rate'] > 10 ? '→ Moderate' : '↓ Low') }}</span>
        </div>
        <div class="sa-kpi-val" style="color:var(--text)">{{ $overStat['override_rate'] }}%</div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $overStat['override_sales_count'] }}</span><span class="sa-kpi-stat-l">Sales</span></div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)"><span class="sa-kpi-stat-v">{{ $overStat['override_items_count'] }}</span><span class="sa-kpi-stat-l">Items</span></div>
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v" style="color:var(--red)">{{ number_format($overStat['total_discount_given']) }}</span><span class="sa-kpi-stat-l">Discounted</span></div>
        </div>
    </div>
</div>

<div class="sa-tbl-wrap">
    <div class="sa-card-head">
        <div class="sa-head-accent">
            <div class="sa-card-title">Price Modification Audit Trail</div>
            <div class="sa-card-sub">Every price change in the period · who, what, when, how much</div>
        </div>
        <span class="sa-card-badge" style="{{ count($auditLog) > 0 ? 'color:var(--amber)' : '' }}">
            {{ count($auditLog) }} modifications
        </span>
    </div>
    <div class="sa-tbl-scroll">
        <table class="sa-tbl sa-audit-tbl" style="min-width:1360px;table-layout:fixed">
            <colgroup>
                <col style="width:130px"><col style="width:210px"><col style="width:140px">
                <col style="width:155px"><col style="width:100px"><col style="width:100px">
                <col style="width:105px"><col style="width:90px"><col style="width:165px">
                <col style="width:165px">
            </colgroup>
            <thead>
                <tr>
                    <th>Date & Time</th><th>Sale # / Product</th><th>Shop / Seller</th>
                    <th style="text-align:right">Qty</th><th style="text-align:right">Original</th>
                    <th style="text-align:right">Actual</th><th style="text-align:right;color:var(--red)">Discount</th>
                    <th style="text-align:right;color:var(--green)">Margin</th>
                    <th>Reason</th><th>Approved</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditLog as $entry)
                @php $isLargeDiscount = ($entry['discount_pct'] ?? 0) >= 20; @endphp
                <tr style="{{ $isLargeDiscount ? 'background:var(--amber-dim);border-left:3px solid var(--amber)' : '' }}">
                    <td>
                        <div style="font-family:var(--mono);font-size:12px;font-weight:600;color:var(--text)">{{ \Carbon\Carbon::parse($entry['sale_date'])->format('M d, Y') }}</div>
                        <div style="font-family:var(--mono);font-size:11px;color:var(--text-dim);margin-top:2px">{{ \Carbon\Carbon::parse($entry['sale_date'])->format('H:i') }}</div>
                    </td>
                    <td>
                        <div style="font-size:11px;font-family:var(--mono);font-weight:600;color:var(--accent);margin-bottom:3px">{{ $entry['sale_number'] }}</div>
                        <div style="font-size:12px;color:var(--text);word-break:break-word">
                            {{ $entry['product_name'] }}
                            @if($entry['line_count'] > 1)
                            <span style="margin-left:4px;padding:1px 6px;background:var(--accent-dim);color:var(--accent);border-radius:10px;font-size:10px;font-weight:700;font-family:var(--mono)">×{{ $entry['line_count'] }}</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div style="font-size:11px;color:var(--text-sub);margin-bottom:2px">{{ $entry['shop_name'] }}</div>
                        <div style="font-size:12px;font-weight:600;color:var(--text)">{{ $entry['seller_name'] }}</div>
                    </td>
                    <td style="text-align:right;font-family:var(--mono);font-size:12px;color:var(--text-sub)">{{ $entry['quantity_display'] }}</td>
                    <td style="text-align:right;font-family:var(--mono);font-size:12px;color:var(--text-sub)">{{ number_format($entry['original_unit_price']) }}</td>
                    <td style="text-align:right;font-family:var(--mono);font-size:12px;font-weight:700;color:var(--text)">{{ number_format($entry['actual_unit_price']) }}</td>
                    <td style="text-align:right">
                        <div style="font-family:var(--mono);font-size:12px;font-weight:700;color:var(--red)">{{ number_format($entry['total_discount']) }}</div>
                        <div style="font-size:10px;color:var(--text-dim);font-family:var(--mono)">{{ $entry['discount_pct'] }}% off</div>
                    </td>
                    <td style="text-align:right">
                        @php $mas = $entry['margin_at_sale'] @endphp
                        <span style="font-size:12px;font-weight:700;font-family:var(--mono);
                            color:{{ $mas >= 20 ? 'var(--green)' : ($mas >= 5 ? 'var(--amber)' : 'var(--red)') }}">
                            {{ $mas }}%
                        </span>
                    </td>
                    <td style="font-size:11px;color:var(--text-sub)">
                        <div style="word-break:break-word">{{ $entry['reason'] ?? '—' }}</div>
                        @if($entry['reference'])
                        <div style="font-size:10px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">Ref: {{ $entry['reference'] }}</div>
                        @endif
                    </td>
                    <td>
                        @if(($entry['seller_role'] ?? '') === 'owner')
                        <span class="sa-margin-pill" style="background:var(--green-dim);color:var(--green)">✓ Owner</span>
                        @elseif($entry['is_approved'])
                        <span class="sa-margin-pill" style="background:var(--green-dim);color:var(--green)">✓ {{ $entry['approved_by'] }}</span>
                        @else
                        <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-start">
                            <span class="sa-margin-pill" style="background:var(--amber-dim);color:var(--amber)">Pending</span>
                            @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                            <button wire:click="approvePriceOverride({{ $entry['sale_id'] }})"
                                    wire:confirm="Approve price override on sale {{ $entry['sale_number'] }}?"
                                    wire:loading.attr="disabled"
                                    style="font-size:11px;font-weight:700;padding:4px 10px;border-radius:8px;
                                           border:1px solid var(--green);background:var(--green-dim);
                                           color:var(--green);cursor:pointer;white-space:nowrap;
                                           font-family:var(--font);transition:all var(--tr)"
                                    onmouseover="this.style.background='var(--green)';this.style.color='#fff'"
                                    onmouseout="this.style.background='var(--green-dim)';this.style.color='var(--green)'">
                                Approve ✓
                            </button>
                            @endif
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="10"><div class="sa-empty"><div class="sa-empty-title">No price modifications in this period</div></div></td></tr>
                @endforelse
            </tbody>
            @if(count($auditLog))
            <tfoot>
                <tr>
                    <td colspan="6" style="color:var(--text-sub);font-size:11px;letter-spacing:.5px">TOTAL DISCOUNT GIVEN</td>
                    <td style="text-align:right;font-family:var(--mono);color:var(--red)">{{ number_format($totalDisc) }}</td>
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

{{-- Seller Performance Table --}}
<div class="sa-tbl-wrap">
    <div class="sa-card-head">
        <div class="sa-head-accent">
            <div class="sa-card-title">Seller Performance</div>
            <div class="sa-card-sub">{{ $this->activeDateRangeLabel }} · ranked by revenue</div>
        </div>
        <span class="sa-card-badge">{{ count($sellers) }} sellers</span>
    </div>
    <div class="sa-tbl-scroll">
        <table class="sa-tbl sa-cards-mob" style="width:100%">
            <thead>
                <tr>
                    <th class="sa-hide-mob">#</th>
                    <th>Seller</th>
                    <th class="sa-hide-mob">Shop</th>
                    <th style="text-align:right">Txns</th>
                    <th style="text-align:right">Revenue</th>
                    <th class="sa-hide-mob" style="text-align:right">Share</th>
                    <th class="sa-hide-mob" style="text-align:right">Avg Order</th>
                    <th class="sa-hide-mob" style="text-align:right">Items</th>
                    <th style="text-align:right;color:var(--green)">GP</th>
                    <th style="text-align:right;color:var(--green)">Margin</th>
                    <th class="sa-hide-mob" style="text-align:right;color:var(--amber)">Discounts</th>
                    <th style="text-align:right;color:var(--amber)">Overrides</th>
                    <th style="text-align:right;color:var(--red)">Voided</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sellers as $i => $s)
                @php $isTop = $i === 0; @endphp
                <tr style="{{ $isTop ? 'background:var(--green-dim)' : '' }}">
                    <td class="sa-hide-mob" style="font-size:11px;font-family:var(--mono);color:var(--text-dim)">{{ $i + 1 }}</td>
                    <td class="sa-row-title">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div class="sa-avatar"
                                 style="background:{{ $isTop ? 'var(--green-dim)' : 'var(--surface2)' }};
                                        color:{{ $isTop ? 'var(--green)' : 'var(--text-sub)' }}">
                                {{ strtoupper(substr($s['seller_name'], 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:600;color:var(--text);font-size:12px">{{ $s['seller_name'] }}</div>
                                @if($isTop)<div style="font-size:10px;color:var(--green);font-weight:600">Top seller</div>@endif
                                <div style="font-size:10px;color:var(--text-dim)">{{ $s['shop_name'] }} · {{ $s['revenue_share'] }}% share</div>
                            </div>
                        </div>
                    </td>
                    <td class="sa-hide-mob" style="font-size:12px;color:var(--text-sub)">{{ $s['shop_name'] }}</td>
                    <td data-label="Txns" style="text-align:right;font-family:var(--mono);color:var(--text-sub)">{{ $s['transactions'] }}</td>
                    <td data-label="Revenue" style="text-align:right;font-family:var(--mono);font-weight:700;color:var(--text)">{{ number_format($s['revenue']) }}</td>
                    <td class="sa-hide-mob" style="text-align:right">
                        <div style="height:4px;background:var(--surface2);border-radius:2px;width:50px;display:inline-block;vertical-align:middle;margin-right:5px">
                            <div style="height:100%;width:{{ min($s['revenue_share'], 100) }}%;background:var(--accent);border-radius:2px"></div>
                        </div>
                        <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim)">{{ $s['revenue_share'] }}%</span>
                    </td>
                    <td class="sa-hide-mob" data-label="Avg Order" style="text-align:right;font-family:var(--mono);color:var(--text-sub);font-size:11px">{{ number_format($s['avg_order']) }}</td>
                    <td class="sa-hide-mob" data-label="Items" style="text-align:right;font-family:var(--mono);color:var(--text-sub)">{{ number_format($s['items_sold']) }}</td>
                    <td data-label="GP" style="text-align:right;font-family:var(--mono);font-weight:700;color:var(--green)">{{ number_format($s['gross_profit']) }}</td>
                    <td data-label="Margin" style="text-align:right">
                        @php $mp = $s['margin_pct'] @endphp
                        <span class="sa-margin-pill"
                              style="background:{{ $mp >= 30 ? 'var(--green-dim)' : ($mp >= 15 ? 'var(--amber-dim)' : 'var(--red-dim)') }};
                                     color:{{ $mp >= 30 ? 'var(--green)' : ($mp >= 15 ? 'var(--amber)' : 'var(--red)') }}">
                            {{ $mp }}%
                        </span>
                    </td>
                    <td class="sa-hide-mob" data-label="Discounts" style="text-align:right;font-family:var(--mono);color:{{ $s['total_discount'] > 0 ? 'var(--amber)' : 'var(--text-dim)' }};font-size:11px">
                        {{ $s['total_discount'] > 0 ? number_format($s['total_discount']) : '—' }}
                    </td>
                    <td data-label="Overrides" style="text-align:right">
                        @if($s['override_count'] > 0)
                        <span class="sa-margin-pill"
                              style="background:{{ $s['override_count'] > 3 ? 'var(--amber-dim)' : 'transparent' }};
                                     color:{{ $s['override_count'] > 3 ? 'var(--amber)' : 'var(--text-sub)' }}">
                            {{ $s['override_count'] }}
                        </span>
                        @else<span style="color:var(--text-dim)">—</span>@endif
                    </td>
                    <td data-label="Voided" style="text-align:right">
                        @if($s['void_count'] > 0)
                        <span class="sa-margin-pill" style="background:var(--red-dim);color:var(--red)">{{ $s['void_count'] }}</span>
                        @else<span style="color:var(--text-dim)">—</span>@endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="13"><div class="sa-empty"><div class="sa-empty-title">No sales data for this period</div></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Sellers KPI cards --}}
<div class="sa-kpis">
    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--accent-dim);color:var(--accent)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Known Customers</div>
                <div class="sa-kpi-sub">Identified by name/phone</div>
            </div>
        </div>
        <div class="sa-kpi-val" style="color:var(--text)">{{ $customers['total_customers'] }}</div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $customers['repeat_customers'] }}</span><span class="sa-kpi-stat-l">Repeat</span></div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)"><span class="sa-kpi-stat-v">{{ $customers['walkin_count'] }}</span><span class="sa-kpi-stat-l">Walk-ins</span></div>
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $customers['repeat_rate'] }}%</span><span class="sa-kpi-stat-l">Repeat Rate</span></div>
        </div>
    </div>

    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--green-dim);color:var(--green)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Repeat Rate</div>
                <div class="sa-kpi-sub">{{ $customers['repeat_customers'] }} returning customers</div>
            </div>
            <span class="sa-growth {{ $customers['repeat_rate'] >= 30 ? 'up' : 'neutral' }}">{{ $customers['repeat_rate'] >= 30 ? '↑ Strong' : '→ Building' }}</span>
        </div>
        <div class="sa-kpi-val" style="color:var(--green)">{{ $customers['repeat_rate'] }}%</div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $customers['repeat_customers'] }}</span><span class="sa-kpi-stat-l">Returning</span></div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)"><span class="sa-kpi-stat-v">{{ $customers['walkin_count'] }}</span><span class="sa-kpi-stat-l">Walk-ins</span></div>
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $customers['total_customers'] }}</span><span class="sa-kpi-stat-l">Identified</span></div>
        </div>
    </div>

    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--amber-dim);color:var(--amber)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Returns</div>
                <div class="sa-kpi-sub">{{ $ret['items_returned'] }} items returned</div>
            </div>
        </div>
        <div class="sa-kpi-val" style="color:var(--text)">{{ $ret['returns_count'] }}</div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ number_format($ret['returned_revenue']) }}</span><span class="sa-kpi-stat-l">Refunded</span></div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)"><span class="sa-kpi-stat-v">{{ $ret['exchange_count'] }}</span><span class="sa-kpi-stat-l">Exchanges</span></div>
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $ret['return_rate'] }}%</span><span class="sa-kpi-stat-l">Rate</span></div>
        </div>
    </div>

    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--pink-dim);color:var(--pink)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Refunded Amount</div>
                <div class="sa-kpi-sub">{{ $ret['return_rate'] }}% return rate</div>
            </div>
        </div>
        <div class="sa-kpi-val" style="color:var(--text)">{{ number_format($ret['returned_revenue']) }}</div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $ret['returns_count'] }}</span><span class="sa-kpi-stat-l">Returns</span></div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)"><span class="sa-kpi-stat-v">{{ $ret['exchange_count'] }}</span><span class="sa-kpi-stat-l">Exchanges</span></div>
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $ret['items_returned'] }}</span><span class="sa-kpi-stat-l">Items</span></div>
        </div>
    </div>
</div>

{{-- Customer Analysis + Returns --}}
<div class="sa-two-col" style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

    {{-- Customer Analysis --}}
    <div class="sa-tbl-wrap" style="margin-bottom:0">
        <div class="sa-card-head">
            <div class="sa-head-accent">
                <div class="sa-card-title">Customer Analysis</div>
                <div class="sa-card-sub">Repeat rate and top spenders</div>
            </div>
        </div>
        <div style="overflow:auto;max-height:360px">
            <table class="sa-tbl" style="width:100%">
                <thead style="position:sticky;top:0">
                    <tr>
                        <th>Customer</th>
                        <th style="text-align:right">Visits</th>
                        <th style="text-align:right">Total Spent</th>
                        <th style="text-align:right">Avg</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers['top_customers'] as $c)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:6px">
                                @if($c['is_repeat'])<span class="sa-margin-pill" style="background:var(--green-dim);color:var(--green);font-size:9px">↩ repeat</span>@endif
                                <div>
                                    <div style="font-weight:600;color:var(--text);font-size:12px">{{ $c['name'] }}</div>
                                    @if($c['phone'])<div style="font-size:10px;color:var(--text-dim);font-family:var(--mono)">{{ $c['phone'] }}</div>@endif
                                </div>
                            </div>
                        </td>
                        <td style="text-align:right;font-family:var(--mono);color:{{ $c['purchase_count'] > 1 ? 'var(--green)' : 'var(--text-sub)' }};font-weight:{{ $c['purchase_count'] > 1 ? '700' : '400' }}">{{ $c['purchase_count'] }}</td>
                        <td style="text-align:right;font-family:var(--mono);font-weight:600;color:var(--text);font-size:12px">{{ number_format($c['total_spent']) }}</td>
                        <td style="text-align:right;font-family:var(--mono);font-size:11px;color:var(--text-sub)">{{ number_format($c['avg_order']) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4"><div class="sa-empty" style="padding:24px"><div class="sa-empty-sub">No named customers in this period</div></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Returns Summary --}}
    <div class="sa-tbl-wrap" style="margin-bottom:0">
        <div class="sa-card-head">
            <div class="sa-head-accent">
                <div class="sa-card-title">Returns Summary</div>
                <div class="sa-card-sub">Impact on net revenue</div>
            </div>
        </div>
        @if(count($ret['top_returned_products']))
        <div style="padding:14px 20px">
            <div style="font-size:11px;font-weight:700;color:var(--text-dim);letter-spacing:.5px;text-transform:uppercase;margin-bottom:10px">Most Returned Products</div>
            @foreach($ret['top_returned_products'] as $rp)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid var(--border);gap:10px">
                <span style="font-size:12px;color:var(--text);flex:1;min-width:0">{{ $rp['product_name'] }}</span>
                <span style="font-size:11px;font-family:var(--mono);font-weight:700;color:var(--red);white-space:nowrap">{{ $rp['qty_returned'] }} units</span>
            </div>
            @endforeach
        </div>
        @else
        <div class="sa-empty"><div class="sa-empty-sub">No returns in this period</div></div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: PAYMENTS
══════════════════════════════════════════════════════════════════════════ --}}
@elseif($activeTab === 'payments')
@php
    $startDate = \Carbon\Carbon::parse($dateFrom)->startOfDay();
    $endDate   = \Carbon\Carbon::parse($dateTo)->endOfDay();

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
        $paymentMethods[$method->value] = ['label' => $method->label(), 'total' => $data ? $data->total_amount : 0, 'count' => $data ? $data->transaction_count : 0];
    }

    $totalRevenue      = array_sum(array_column($paymentMethods, 'total'));
    $salesQuery        = \App\Models\Sale::query()->whereNull('voided_at')->where('sale_date', '>=', $startDate)->where('sale_date', '<=', $endDate);
    if ($locationFilter !== 'all') { $shopId = (int) str_replace('shop:', '', $locationFilter); $salesQuery->where('shop_id', $shopId); }
    $totalSales        = (clone $salesQuery)->count();
    $splitPaymentSales = (clone $salesQuery)->where('is_split_payment', true)->count();
    $splitPercentage   = $totalSales > 0 ? round(($splitPaymentSales / $totalSales) * 100, 1) : 0;
    $creditSalesQ      = (clone $salesQuery)->where('has_credit', true);
    $creditCount       = $creditSalesQ->count();
    $creditTotal       = $creditSalesQ->sum('credit_amount');
    $avgTransactionValue = $totalSales > 0 ? round($totalRevenue / $totalSales) : 0;
@endphp

{{-- 4 headline KPIs --}}
<div class="sa-kpis" style="margin-bottom:20px">
    @php $cashPct = $totalRevenue > 0 ? round(($paymentMethods['cash']['total'] / $totalRevenue) * 100, 1) : 0; @endphp
    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--violet-dim);color:var(--violet)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Total Revenue</div>
                <div class="sa-kpi-sub">{{ $totalSales }} transactions</div>
            </div>
        </div>
        <div class="sa-kpi-val" style="color:var(--text)">{{ number_format($totalRevenue) }}</div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ number_format($avgTransactionValue) }}</span><span class="sa-kpi-stat-l">Avg Order</span></div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)"><span class="sa-kpi-stat-v">{{ $splitPaymentSales }}</span><span class="sa-kpi-stat-l">Split</span></div>
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $creditCount }}</span><span class="sa-kpi-stat-l">Credit Sales</span></div>
        </div>
    </div>

    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--green-dim);color:var(--green)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Cash</div>
                <div class="sa-kpi-sub">{{ $paymentMethods['cash']['count'] }} transactions</div>
            </div>
            <span class="sa-growth neutral">{{ $cashPct }}% of total</span>
        </div>
        <div class="sa-kpi-val" style="color:var(--green)">{{ number_format($paymentMethods['cash']['total']) }}</div>
        <div class="sa-kpi-bar" style="background:var(--green-dim)"><div style="height:100%;border-radius:3px;background:var(--green);width:{{ $cashPct }}%"></div></div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $paymentMethods['cash']['count'] }}</span><span class="sa-kpi-stat-l">Txns</span></div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)"><span class="sa-kpi-stat-v">{{ $paymentMethods['cash']['count'] > 0 ? number_format(round($paymentMethods['cash']['total'] / $paymentMethods['cash']['count'])) : '—' }}</span><span class="sa-kpi-stat-l">Avg/Txn</span></div>
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $cashPct }}%</span><span class="sa-kpi-stat-l">of Total</span></div>
        </div>
    </div>

    @php $mmPct = $totalRevenue > 0 ? round(($paymentMethods['mobile_money']['total'] / $totalRevenue) * 100, 1) : 0; @endphp
    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--pink-dim);color:var(--pink)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Mobile Money</div>
                <div class="sa-kpi-sub">{{ $paymentMethods['mobile_money']['count'] }} transactions</div>
            </div>
            <span class="sa-growth neutral">{{ $mmPct }}% of total</span>
        </div>
        <div class="sa-kpi-val" style="color:var(--pink)">{{ number_format($paymentMethods['mobile_money']['total']) }}</div>
        <div class="sa-kpi-bar" style="background:var(--pink-dim)"><div style="height:100%;border-radius:3px;background:var(--pink);width:{{ $mmPct }}%"></div></div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $paymentMethods['mobile_money']['count'] }}</span><span class="sa-kpi-stat-l">Txns</span></div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)"><span class="sa-kpi-stat-v">{{ $paymentMethods['mobile_money']['count'] > 0 ? number_format(round($paymentMethods['mobile_money']['total'] / $paymentMethods['mobile_money']['count'])) : '—' }}</span><span class="sa-kpi-stat-l">Avg/Txn</span></div>
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $mmPct }}%</span><span class="sa-kpi-stat-l">of Total</span></div>
        </div>
    </div>

    @php $crPct = $totalRevenue > 0 ? round(($paymentMethods['credit']['total'] / $totalRevenue) * 100, 1) : 0; @endphp
    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--amber-dim);color:var(--amber)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Credit</div>
                <div class="sa-kpi-sub">{{ $creditCount }} credit sales</div>
            </div>
            <span class="sa-growth {{ $crPct > 20 ? 'down' : 'neutral' }}">{{ $crPct }}% of total</span>
        </div>
        <div class="sa-kpi-val" style="color:var(--amber)">{{ number_format($paymentMethods['credit']['total']) }}</div>
        <div class="sa-kpi-bar" style="background:var(--amber-dim)"><div style="height:100%;border-radius:3px;background:var(--amber);width:{{ $crPct }}%"></div></div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $creditCount }}</span><span class="sa-kpi-stat-l">Sales</span></div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)"><span class="sa-kpi-stat-v">{{ $creditCount > 0 ? number_format(round($paymentMethods['credit']['total'] / $creditCount)) : '—' }}</span><span class="sa-kpi-stat-l">Avg/Sale</span></div>
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v" style="color:{{ $crPct > 20 ? 'var(--amber)' : 'var(--text-sub)' }}">{{ $crPct }}%</span><span class="sa-kpi-stat-l">of Total</span></div>
        </div>
    </div>
</div>

{{-- Visual payment breakdown --}}
<div class="sa-tbl-wrap">
    <div class="sa-card-head">
        <div class="sa-head-accent">
            <div class="sa-card-title">Payment Method Breakdown</div>
        </div>
    </div>
    @php
        $pmConfig = [
            'cash'          => ['color' => 'var(--green)',  'label' => 'Cash'],
            'mobile_money'  => ['color' => 'var(--pink)',   'label' => 'Mobile Money'],
            'card'          => ['color' => 'var(--accent)', 'label' => 'Card'],
            'bank_transfer' => ['color' => 'var(--amber)',  'label' => 'Bank Transfer'],
            'credit'        => ['color' => 'var(--red)',    'label' => 'Credit'],
        ];
        $maxPay = max(array_column($paymentMethods, 'total') ?: [1]);
    @endphp
    @foreach($paymentMethods as $key => $pm)
    @php $cfg = $pmConfig[$key] ?? ['color' => 'var(--text-dim)', 'label' => $pm['label']]; @endphp
    <div class="sa-pay-row">
        <div class="sa-pay-dot" style="background:{{ $cfg['color'] }}"></div>
        <span class="sa-pay-name">{{ $pm['label'] }}</span>
        <div class="sa-pay-bar-wrap">
            <div class="sa-pay-bar" style="width:{{ $maxPay > 0 ? round($pm['total'] / $maxPay * 100) : 0 }}%;background:{{ $cfg['color'] }}"></div>
        </div>
        <span class="sa-pay-amt">{{ number_format($pm['total']) }}</span>
        <span class="sa-pay-pct">{{ $totalRevenue > 0 ? round($pm['total'] / $totalRevenue * 100, 1) : 0 }}%</span>
    </div>
    @endforeach
    <div style="padding:10px 20px;border-top:2px solid var(--border);display:flex;justify-content:space-between;align-items:center">
        <span style="font-size:11px;font-weight:700;color:var(--text-sub);letter-spacing:.5px;text-transform:uppercase">Total</span>
        <span style="font-size:14px;font-weight:800;font-family:var(--mono);color:var(--text)">{{ number_format($totalRevenue) }}</span>
    </div>
</div>

{{-- Split payments info --}}
@if($splitPaymentSales > 0)
<div style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:var(--accent-dim);
            border:1px solid var(--accent);border-radius:var(--rsm)">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" style="flex-shrink:0">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    <div style="font-size:12px;color:var(--accent)">
        <strong>{{ $splitPaymentSales }}</strong> sales ({{ $splitPercentage }}%) used split payment —
        amounts above are sum of each method across all sales.
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: CREDIT
══════════════════════════════════════════════════════════════════════════ --}}
@elseif($activeTab === 'credit')
@php
    $customerQuery = \App\Models\Customer::query();
    if ($locationFilter !== 'all') { $shopId = (int) str_replace('shop:', '', $locationFilter); $customerQuery->where('shop_id', $shopId); }
    $customersWithCredit  = (clone $customerQuery)->where('outstanding_balance', '>', 0)->count();
    $totalOutstanding     = (clone $customerQuery)->sum('outstanding_balance');
    $totalCreditGiven     = (clone $customerQuery)->sum('total_credit_given');
    $totalRepaid          = (clone $customerQuery)->sum('total_repaid');
    $repaymentRate        = $totalCreditGiven > 0 ? round(($totalRepaid / $totalCreditGiven) * 100, 1) : 0;
    $topCustomers         = (clone $customerQuery)->where('outstanding_balance', '>', 0)->with('shop')->orderBy('outstanding_balance', 'desc')->limit(10)->get();
    $startDate            = \Carbon\Carbon::parse($dateFrom)->startOfDay();
    $endDate              = \Carbon\Carbon::parse($dateTo)->endOfDay();
    $creditSalesInPeriod  = \App\Models\Sale::query()->whereNull('voided_at')->where('has_credit', true)->where('sale_date', '>=', $startDate)->where('sale_date', '<=', $endDate);
    if ($locationFilter !== 'all') { $shopId = (int) str_replace('shop:', '', $locationFilter); $creditSalesInPeriod->where('shop_id', $shopId); }
    $creditSalesCount     = $creditSalesInPeriod->count();
    $creditGivenInPeriod  = $creditSalesInPeriod->sum('credit_amount');
    $avgDebt              = $customersWithCredit > 0 ? round($totalOutstanding / $customersWithCredit) : 0;
@endphp

<div class="sa-kpis" style="margin-bottom:20px">

    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--red-dim);color:var(--red)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Outstanding Balance</div>
                <div class="sa-kpi-sub">{{ $customersWithCredit }} customers · all time</div>
            </div>
        </div>
        <div class="sa-kpi-val" style="color:var(--red)">{{ number_format($totalOutstanding) }}</div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ number_format($totalCreditGiven) }}</span><span class="sa-kpi-stat-l">Total Given</span></div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)"><span class="sa-kpi-stat-v" style="color:var(--green)">{{ number_format($totalRepaid) }}</span><span class="sa-kpi-stat-l">Repaid</span></div>
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ number_format($avgDebt) }}</span><span class="sa-kpi-stat-l">Avg Debt</span></div>
        </div>
    </div>

    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--amber-dim);color:var(--amber)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Credit Given (Period)</div>
                <div class="sa-kpi-sub">{{ $creditSalesCount }} credit sales</div>
            </div>
        </div>
        <div class="sa-kpi-val" style="color:var(--amber)">{{ number_format($creditGivenInPeriod) }}</div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $creditSalesCount }}</span><span class="sa-kpi-stat-l">Sales</span></div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)"><span class="sa-kpi-stat-v">{{ $creditSalesCount > 0 ? number_format(round($creditGivenInPeriod / $creditSalesCount)) : '—' }}</span><span class="sa-kpi-stat-l">Avg/Sale</span></div>
            @php $periodRevenue = $this->grossProfitKpis['revenue'] @endphp
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ $periodRevenue > 0 ? round(($creditGivenInPeriod / $periodRevenue) * 100, 1) : 0 }}%</span><span class="sa-kpi-stat-l">of Revenue</span></div>
        </div>
    </div>

    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--accent-dim);color:var(--accent)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Customers with Credit</div>
                <div class="sa-kpi-sub">Currently carrying a balance</div>
            </div>
        </div>
        <div class="sa-kpi-val" style="color:var(--accent)">{{ number_format($customersWithCredit) }}</div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ number_format($avgDebt) }}</span><span class="sa-kpi-stat-l">Avg Debt</span></div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)"><span class="sa-kpi-stat-v">{{ number_format($totalOutstanding) }}</span><span class="sa-kpi-stat-l">Total Owed</span></div>
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v" style="color:{{ $repaymentRate >= 80 ? 'var(--green)' : ($repaymentRate >= 50 ? 'var(--amber)' : 'var(--red)') }}">{{ $repaymentRate }}%</span><span class="sa-kpi-stat-l">Repaid</span></div>
        </div>
    </div>

    @php $rpColor = $repaymentRate >= 80 ? 'var(--green)' : ($repaymentRate >= 50 ? 'var(--amber)' : 'var(--red)') @endphp
    <div class="sa-kpi">
        <div class="sa-kpi-row">
            <div class="sa-kpi-icon" style="background:var(--green-dim);color:var(--green)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            </div>
            <div class="sa-kpi-body">
                <div class="sa-kpi-label">Repayment Rate</div>
                <div class="sa-kpi-sub">{{ number_format($totalRepaid) }} RWF repaid</div>
            </div>
            <span class="sa-growth {{ $repaymentRate >= 80 ? 'up' : ($repaymentRate >= 50 ? 'neutral' : 'down') }}">{{ $repaymentRate >= 80 ? '↑ Good' : ($repaymentRate >= 50 ? '→ Fair' : '↓ Low') }}</span>
        </div>
        <div class="sa-kpi-val" style="color:{{ $rpColor }}">{{ $repaymentRate }}%</div>
        <div class="sa-kpi-divider"></div>
        <div class="sa-kpi-footer">
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v" style="color:var(--green)">{{ number_format($totalRepaid) }}</span><span class="sa-kpi-stat-l">Repaid</span></div>
            <div class="sa-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)"><span class="sa-kpi-stat-v" style="color:var(--red)">{{ number_format($totalOutstanding) }}</span><span class="sa-kpi-stat-l">Still Owed</span></div>
            <div class="sa-kpi-stat"><span class="sa-kpi-stat-v">{{ number_format($totalCreditGiven) }}</span><span class="sa-kpi-stat-l">Total Given</span></div>
        </div>
    </div>

</div>

{{-- Top Customers by Outstanding Balance --}}
<div class="sa-tbl-wrap">
    <div class="sa-card-head">
        <div class="sa-head-accent">
            <div class="sa-card-title">Top Customers by Outstanding Balance</div>
            <div class="sa-card-sub">Highest credit balances · All time</div>
        </div>
        <span class="sa-card-badge">Top {{ $topCustomers->count() }}</span>
    </div>
    @if($topCustomers->count() > 0)
    <div class="sa-tbl-scroll">
        <table class="sa-tbl" style="width:100%;min-width:600px">
            <thead>
                <tr>
                    <th style="text-align:center;width:44px">#</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Shop</th>
                    <th style="text-align:right;color:var(--red)">Outstanding</th>
                    <th style="text-align:right">Given</th>
                    <th style="text-align:right;color:var(--green)">Repaid</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topCustomers as $index => $customer)
                <tr>
                    <td style="text-align:center">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:6px;background:var(--surface2);color:var(--text-sub);font-size:11px;font-weight:700">{{ $index + 1 }}</span>
                    </td>
                    <td style="font-weight:600;color:var(--text)">{{ $customer->name }}</td>
                    <td style="font-family:var(--mono);color:var(--text-sub);font-size:12px">{{ $customer->phone }}</td>
                    <td style="color:var(--text-sub);font-size:12px">{{ $customer->shop?->name ?? '—' }}</td>
                    <td style="text-align:right;font-family:var(--mono);font-weight:700;color:var(--red)">{{ number_format($customer->outstanding_balance) }}</td>
                    <td style="text-align:right;font-family:var(--mono);color:var(--text-sub);font-size:12px">{{ number_format($customer->total_credit_given) }}</td>
                    <td style="text-align:right;font-family:var(--mono);font-weight:600;color:var(--green);font-size:12px">{{ number_format($customer->total_repaid) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="sa-empty">
        <div class="sa-empty-title">No Credit Customers</div>
        <div class="sa-empty-sub">All customers have cleared their balances</div>
    </div>
    @endif
</div>

@endif


</div>

@script
<script>
    let _revChart = null;

    function _initRevChart() {
        const el = document.getElementById('rev-trend-chart');
        if (!el) return;

        const dates        = JSON.parse(el.dataset.dates        || '[]');
        const revenues     = JSON.parse(el.dataset.revenues     || '[]');
        const transactions = JSON.parse(el.dataset.transactions || '[]');

        if (_revChart) { _revChart.destroy(); _revChart = null; }

        if (!dates.length) {
            el.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:240px;color:var(--text-sub);font-size:13px">No data available for this period</div>';
            return;
        }

        _revChart = new ApexCharts(el, {
            chart: {
                type: 'area',
                height: 240,
                toolbar: { show: false },
                animations: { enabled: false },
                background: 'transparent',
                fontFamily: 'inherit',
            },
            series: [
                { name: 'Revenue (RWF)', type: 'area', data: revenues },
                { name: 'Transactions',  type: 'line', data: transactions },
            ],
            xaxis: {
                categories: dates,
                labels: { style: { colors: 'var(--text-sub)', fontSize: '11px' }, rotate: -30, rotateAlways: false },
                axisBorder: { show: false },
                axisTicks:  { show: false },
            },
            yaxis: [
                {
                    seriesName: 'Revenue (RWF)',
                    labels: {
                        formatter: v => (v >= 1000000 ? (v/1000000).toFixed(1)+'M' : v >= 1000 ? (v/1000).toFixed(0)+'K' : v),
                        style: { colors: ['var(--text-sub)'], fontSize: '11px' },
                    },
                },
                {
                    seriesName: 'Transactions',
                    opposite: true,
                    labels: {
                        formatter: v => Math.round(v),
                        style: { colors: ['var(--text-sub)'], fontSize: '11px' },
                    },
                },
            ],
            colors: ['#3b82f6', '#10b981'],
            fill: {
                type: ['gradient', 'solid'],
                gradient: { shade: 'dark', opacityFrom: 0.35, opacityTo: 0.03, stops: [0, 100] },
            },
            stroke: { curve: 'smooth', width: [2, 2], dashArray: [0, 4] },
            grid: { borderColor: 'rgba(255,255,255,0.07)', strokeDashArray: 3, padding: { left: 8, right: 8 } },
            markers: { size: [3, 3], hover: { size: 5 } },
            tooltip: {
                shared: true,
                intersect: false,
                theme: 'dark',
                y: [
                    { formatter: v => new Intl.NumberFormat().format(v) + ' RWF' },
                    { formatter: v => v + ' txns' },
                ],
            },
            legend: { show: false },
        });

        _revChart.render();
    }

    _initRevChart();

    Livewire.hook('commit', ({ succeed }) => {
        succeed(() => {
            if (document.getElementById('rev-trend-chart')) {
                requestAnimationFrame(_initRevChart);
            }
        });
    });
</script>
@endscript
