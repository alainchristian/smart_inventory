<div class="fq-page" style="font-family:var(--font)">
<style>
/* ── Fulfillment ── fq- ─────────────────────────────────────────── */
.fq-page { padding:0 0 80px; }

/* Header */
.fq-header { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px;flex-wrap:wrap; }
.fq-head-l { display:flex;align-items:flex-start;gap:12px;min-width:0; }
.fq-back   { width:36px;height:36px;border-radius:var(--rsm);display:flex;align-items:center;justify-content:center;flex-shrink:0;
             background:var(--surface);color:var(--text-sub);box-shadow:var(--shadow-card);text-decoration:none;transition:all var(--tr); }
.fq-back:hover { box-shadow:var(--shadow-card-hover);color:var(--text); }
.fq-title  { font-size:22px;font-weight:800;color:var(--text);margin:0 0 4px; }
.fq-subtitle { font-size:13px;color:var(--text-dim);margin:0; }
.fq-subtitle b { color:var(--text-sub);font-weight:600; }

/* Pill tabs (Pending / History) */
.fq-tabs { display:flex;gap:4px; }
.fq-tab  { display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:9px;border:1.5px solid var(--border);cursor:pointer;font-size:13px;
           font-weight:600;font-family:var(--font);background:var(--surface);color:var(--text-dim);transition:all var(--tr);white-space:nowrap; }
.fq-tab:hover  { border-color:var(--accent);color:var(--accent); }
.fq-tab.active { background:var(--accent);border-color:var(--accent);color:#fff; }
.fq-tab-count  { font-size:11px;font-weight:700;padding:1px 7px;border-radius:20px;background:rgba(255,255,255,.22);font-family:var(--mono); }
.fq-tab:not(.active) .fq-tab-count { background:var(--surface2);color:var(--text-dim); }

/* KPIs — .iv-kpi anatomy */
.fq-kpis      { display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px; }
.fq-kpi       { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);padding:20px;display:flex;flex-direction:column;gap:14px;min-width:0;transition:box-shadow var(--tr); }
.fq-kpi:hover { box-shadow:var(--shadow-card-hover); }
.fq-kpi-row   { display:flex;align-items:center;gap:12px; }
.fq-kpi-icon  { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.fq-kpi-label { font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);line-height:1.2; }
.fq-kpi-sub   { font-size:12px;color:var(--text-dim);margin-top:2px; }
.fq-kpi-val   { font-size:24px;font-weight:800;font-family:var(--mono);letter-spacing:-1px;line-height:1;white-space:nowrap; }
.fq-kpi-unit  { font-size:12px;font-weight:500;color:var(--text-dim);margin-left:4px;letter-spacing:0; }
.fq-kpi-divider { height:1px;background:var(--border); }
.fq-kpi-stat  { display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid var(--border); }
.fq-kpi-stat:last-child { border-bottom:none; }
.fq-kpi-stat-l { font-size:11px;color:var(--text-dim);margin-right:8px; }
.fq-kpi-stat-v { font-size:13px;font-weight:700;font-family:var(--mono);color:var(--text-sub);white-space:nowrap; }

/* Cards */
.fq-card      { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);min-width:0;margin-bottom:16px; }
.fq-card-head { padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap; }
.fq-card-title { font-size:13px;font-weight:700;color:var(--text); }
.fq-card-sub  { font-size:12px;color:var(--text-dim);margin-top:2px; }

/* Search + presets */
.fq-search-wrap { position:relative;width:280px;max-width:100%; }
.fq-search-icon { position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--text-dim);pointer-events:none; }
.fq-search   { width:100%;padding:8px 11px 8px 34px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;background:var(--surface);color:var(--text);
               outline:none;box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr); }
.fq-search:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim); }
.fq-presets  { display:flex;gap:4px;overflow-x:auto;-webkit-overflow-scrolling:touch;padding:10px 14px;border-bottom:1px solid var(--border);scrollbar-width:none;flex-wrap:nowrap;min-width:0; }
.fq-presets::-webkit-scrollbar { display:none; }
.fq-preset   { padding:5px 11px;border-radius:6px;font-size:12px;font-weight:600;border:1px solid transparent;background:transparent;color:var(--text-dim);
               cursor:pointer;white-space:nowrap;flex-shrink:0;transition:all var(--tr);font-family:var(--font); }
.fq-preset:hover  { background:var(--surface2);color:var(--text);border-color:var(--border); }
.fq-preset.active { background:var(--accent);color:#fff;border-color:var(--accent);box-shadow:0 2px 8px rgba(0,0,0,.12); }

/* Pending rows */
.fq-row   { display:grid;grid-template-columns:190px minmax(0,1fr) minmax(0,1.3fr) 190px auto;gap:16px;align-items:center;
            padding:13px 20px;border-bottom:1px solid var(--border);transition:background var(--tr); }
.fq-row:last-child { border-bottom:none; }
.fq-row:hover { background:var(--surface2); }
.fq-ref   { font-family:var(--mono);font-size:13px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px;white-space:nowrap; }
.fq-dot   { width:7px;height:7px;border-radius:50%;flex-shrink:0; }
.fq-main  { font-size:13px;font-weight:600;color:var(--text); }
.fq-sub   { font-size:12px;color:var(--text-dim);margin-top:2px; }
.fq-c-ref .fq-sub { padding-left:15px;white-space:nowrap; }
.fq-ellip { overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.fq-x     { color:var(--text-dim);font-weight:500;font-family:var(--mono);font-size:12px; }
.fq-note  { font-style:italic; }
.fq-c-via { display:flex;flex-direction:column;align-items:flex-start;gap:4px;min-width:0; }
.fq-c-act { display:flex;align-items:center;gap:6px;justify-content:flex-end; }
.fq-badge { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;white-space:nowrap;max-width:100%;overflow:hidden;text-overflow:ellipsis; }

/* Buttons */
.fq-btn         { padding:9px 16px;border-radius:var(--rsm);font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font);transition:all var(--tr);
                  display:inline-flex;align-items:center;justify-content:center;gap:6px;white-space:nowrap;text-decoration:none;line-height:1.2; }
.fq-btn-primary { background:var(--accent);color:#fff;border:1px solid var(--accent);box-shadow:0 3px 10px rgba(59,111,212,.25); }
.fq-btn-primary:hover { opacity:.88; }
.fq-btn-primary:disabled { opacity:.5;cursor:not-allowed; }
.fq-btn-ghost   { background:var(--surface);color:var(--text-sub);border:1px solid var(--border); }
.fq-btn-ghost:hover { background:var(--surface2);color:var(--text); }
.fq-btn-sm      { padding:6px 14px;font-size:12px; }
.fq-icon-btn    { width:32px;height:32px;border-radius:8px;border:1.5px solid var(--border);background:transparent;color:var(--text-sub);cursor:pointer;
                  display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;text-decoration:none;transition:all var(--tr); }
.fq-icon-btn:hover { border-color:var(--accent);color:var(--accent); }
.fq-link   { background:none;border:none;padding:0;cursor:pointer;font-size:12px;font-weight:600;color:var(--accent);font-family:var(--font);white-space:nowrap; }
.fq-link:hover { text-decoration:underline; }

/* Scan card */
.fq-scan-body { padding:18px 20px; }
.fq-scan-row  { display:flex;gap:10px;align-items:stretch; }
.fq-scan-wrap { position:relative;flex:1;min-width:0; }
.fq-scan-input { width:100%;padding:12px 14px 12px 40px;border:1.5px solid var(--border);border-radius:10px;font-size:18px;font-weight:700;letter-spacing:1px;
                 font-family:var(--mono);background:var(--surface);color:var(--text);outline:none;box-sizing:border-box;transition:border-color var(--tr);text-transform:uppercase; }
.fq-scan-input::placeholder { font-weight:500;letter-spacing:0;text-transform:none;font-size:14px;color:var(--text-dim);font-family:var(--font); }
.fq-scan-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim); }
.fq-scan-icon { position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-dim);pointer-events:none; }
.fq-scan-foot { display:flex;align-items:center;gap:12px;margin-top:12px;flex-wrap:wrap; }
.fq-hint      { font-size:12px;color:var(--text-dim); }

/* Notices (lookup results that aren't pending) */
.fq-notice    { display:flex;align-items:center;gap:16px;padding:14px 18px;margin-bottom:12px;background:var(--surface);border-radius:var(--rsm);
                box-shadow:var(--shadow-card);border-left:3px solid var(--border); }
.fq-notice-main { flex:1;min-width:0; }
.fq-notice-t  { font-size:13px;font-weight:700;color:var(--text); }
.fq-notice-s  { font-size:12px;color:var(--text-dim);margin-top:3px;line-height:1.5; }
.fq-notice-sig { max-width:140px;max-height:52px;border-radius:6px;border:1px solid var(--border);background:var(--surface);flex-shrink:0; }

/* History table */
.fq-scroll { overflow-x:auto;-webkit-overflow-scrolling:touch; }
.fq-table  { width:100%;border-collapse:collapse;table-layout:fixed; }
.fq-table thead tr { border-bottom:2px solid var(--border); }
.fq-table th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap; }
.fq-table tbody tr { border-bottom:1px solid var(--border);cursor:pointer;transition:background var(--tr); }
.fq-table tbody tr:last-child { border-bottom:none; }
.fq-table tbody tr:hover, .fq-table tbody tr.active { background:var(--surface2); }
.fq-table td { padding:12px 16px;font-size:13px;vertical-align:middle;color:var(--text-sub); }
.fq-table td.num, .fq-table th.num { text-align:right; }

/* Empty */
.fq-empty       { padding:52px 20px;text-align:center; }
.fq-empty-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px; }
.fq-empty-sub   { font-size:13px;color:var(--text-dim);max-width:380px;margin:0 auto;line-height:1.5; }

/* Modal */
.fq-overlay { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;padding:16px; }
.fq-modal   { background:var(--surface);border-radius:var(--r);box-shadow:0 24px 60px rgba(26,31,54,.25);width:100%;max-width:480px;max-height:calc(100vh - 32px);display:flex;flex-direction:column; }
.fq-modal-head  { padding:20px 22px 0;flex-shrink:0; }
.fq-modal-title { font-size:16px;font-weight:800;color:var(--text);margin:0; }
.fq-modal-sub   { font-size:13px;color:var(--text-dim);margin:4px 0 0;line-height:1.5; }
.fq-modal-body  { padding:16px 22px;overflow-y:auto; }
.fq-modal-foot  { padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px;flex-shrink:0; }
.fq-pack      { border:1px solid var(--border);border-radius:var(--rsm);margin-bottom:16px; }
.fq-pack-row  { display:flex;justify-content:space-between;gap:12px;padding:8px 12px;border-bottom:1px solid var(--border);font-size:13px; }
.fq-pack-row:last-child { border-bottom:none; }
.fq-field     { margin-bottom:16px; }
.fq-label     { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:6px;letter-spacing:.3px; }
.fq-label span { color:var(--red); }
.fq-input     { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--surface);color:var(--text);
                outline:none;box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr); }
.fq-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim); }
.fq-error     { font-size:12px;color:var(--red);margin-top:5px; }
.fq-sig-wrap  { position:relative; }
.fq-sig-canvas { width:100%;height:140px;border:1.5px dashed var(--border-hi);border-radius:9px;background:var(--surface);touch-action:none;cursor:crosshair;display:block; }
.fq-sig-hint  { position:absolute;left:12px;bottom:8px;font-size:11px;color:var(--text-dim);pointer-events:none; }
.fq-sig-clear { position:absolute;top:8px;right:8px;padding:3px 9px;border-radius:6px;border:1px solid var(--border);background:var(--surface);color:var(--text-sub);
                cursor:pointer;font-size:11px;font-weight:600;font-family:var(--font);transition:all var(--tr); }
.fq-sig-clear:hover { border-color:var(--red);color:var(--red); }

/* Drawer (history detail) */
.fq-d-overlay { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);backdrop-filter:blur(2px); }
.fq-drawer  { position:fixed;top:0;right:0;bottom:0;z-index:401;width:480px;max-width:100vw;background:var(--surface);border-left:1px solid var(--border);
              box-shadow:-8px 0 40px rgba(26,31,54,.14);display:flex;flex-direction:column;animation:fq-in .22s cubic-bezier(.4,0,.2,1); }
@keyframes fq-in { from { transform:translateX(100%) } to { transform:translateX(0) } }
.fq-d-head  { display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:18px 22px;border-bottom:1px solid var(--border);flex-shrink:0; }
.fq-d-body  { flex:1;overflow-y:auto;overscroll-behavior:contain;padding-bottom:16px; }
.fq-d-close { width:32px;height:32px;border-radius:8px;border:none;background:var(--surface2);color:var(--text-sub);cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.fq-d-close:hover { background:var(--surface3); }
.fq-sec     { padding:14px 22px 6px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--accent); }
.fq-drow    { display:flex;justify-content:space-between;align-items:center;gap:12px;padding:8px 22px;border-bottom:1px solid var(--border);font-size:13px; }
.fq-drow-l  { color:var(--text-dim);flex-shrink:0; }
.fq-drow-v  { color:var(--text);font-weight:600;text-align:right;min-width:0; }

@keyframes fq-spin { to { transform:rotate(360deg) } }

@media (max-width:1100px) {
    .fq-row { grid-template-columns:170px minmax(0,1fr) minmax(0,1fr) auto;gap:12px; }
    .fq-c-via { grid-column:2 / 4;flex-direction:row;flex-wrap:wrap; }
    .fq-c-act { grid-column:4;grid-row:1 / span 2; }
}
@media (max-width:768px) {
    .fq-kpis { grid-template-columns:1fr 1fr; }
    .fq-kpi { padding:14px;gap:10px; }
    .fq-kpi-val { font-size:20px; }
    .fq-title { font-size:19px; }
    .fq-row { grid-template-columns:minmax(0,1fr) auto;gap:6px 12px;padding:12px 16px; }
    .fq-c-ref { grid-column:1; }
    .fq-c-act { grid-column:2;grid-row:1; }
    .fq-c-cust, .fq-c-items, .fq-c-via { grid-column:1 / -1; }
    .fq-c-via { flex-direction:row;flex-wrap:wrap; }
    .fq-search-wrap { width:100%; }
    .fq-drawer { left:0;width:auto; }
}
@media (max-width:640px) {
    /* The work comes first on a phone — summary cards move below the list */
    .fq-page { display:flex;flex-direction:column; }
    .fq-kpis { order:10;margin:4px 0 0; }
    .fq-tab, .fq-preset, .fq-link, .fq-sig-clear { min-height:32px !important;min-width:0 !important;padding:5px 11px !important; }
    .fq-back, .fq-d-close, .fq-icon-btn { min-height:0 !important;min-width:0 !important;padding:0 !important; }
    .fq-btn-sm { min-height:32px !important;padding:6px 14px !important; }
    .fq-table td { padding-left:12px !important;padding-right:12px !important; }
    .fq-scan-row { flex-direction:column; }
    .fq-modal-foot { flex-direction:column-reverse; }
    .fq-modal-foot .fq-btn { width:100%; }
    .fq-notice { flex-wrap:wrap; }
}
@media (max-width:480px) { .fq-kpis { grid-template-columns:1fr; } }
</style>

@php
    $fmtAge = fn (?int $m) => $m === null ? '—' : ($m < 60 ? "{$m}m" : floor($m / 60) . 'h ' . str_pad($m % 60, 2, '0', STR_PAD_LEFT) . 'm');
    $oldTone = $stats['oldest_minutes'] === null ? 'text-dim' : ($stats['oldest_minutes'] >= 120 ? 'red' : ($stats['oldest_minutes'] >= 30 ? 'amber' : 'green'));
    $presets = ['all' => 'All', 'today' => 'Today', 'yesterday' => 'Yesterday', 'this_week' => 'This week', 'this_month' => 'This month', 'last_30' => 'Last 30 days'];
@endphp

{{-- ── Header ── --}}
<div class="fq-header">
    <div class="fq-head-l">
        <a href="{{ route('warehouse.dashboard') }}" wire:navigate class="fq-back" aria-label="Back to dashboard">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div style="min-width:0">
            <h1 class="fq-title">Fulfillment</h1>
            <p class="fq-subtitle"><b>{{ $warehouseName }}</b> · {{ $dispatchMethod === 'scan' ? 'Scan the customer’s pickup code to dispatch' : 'Pack and hand over warehouse-direct orders' }}</p>
        </div>
    </div>
    <div class="fq-tabs" role="tablist">
        <button type="button" role="tab" class="fq-tab {{ $activeTab === 'pending' ? 'active' : '' }}" wire:click="setTab('pending')">
            {{ $dispatchMethod === 'scan' ? 'Scan & dispatch' : 'Pending' }} <span class="fq-tab-count">{{ $stats['pending'] }}</span>
        </button>
        <button type="button" role="tab" class="fq-tab {{ $activeTab === 'history' ? 'active' : '' }}" wire:click="setTab('history')">
            History
        </button>
    </div>
</div>

{{-- ── KPIs (always all pending orders, never the filtered list) ── --}}
<div class="fq-kpis">
    <div class="fq-kpi">
        <div class="fq-kpi-row">
            <div class="fq-kpi-icon" style="background:var(--accent-dim);color:var(--accent)"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></div>
            <div><div class="fq-kpi-label">Awaiting dispatch</div><div class="fq-kpi-sub">Sold, not yet handed over</div></div>
        </div>
        <div class="fq-kpi-val" style="color:var(--text)">{{ $stats['pending'] }}<span class="fq-kpi-unit">{{ $stats['pending'] === 1 ? 'order' : 'orders' }}</span></div>
        <div class="fq-kpi-divider"></div>
        <div>
            <div class="fq-kpi-stat"><span class="fq-kpi-stat-l">To pick</span><span class="fq-kpi-stat-v">{{ \App\Livewire\Warehouse\Sales\FulfillmentQueue::packLabel($stats['pending_boxes'], $stats['pending_items']) }}</span></div>
            <div class="fq-kpi-stat"><span class="fq-kpi-stat-l">Via transporter · pickup</span><span class="fq-kpi-stat-v">{{ $stats['pending_transport'] }} · {{ $stats['pending'] - $stats['pending_transport'] }}</span></div>
        </div>
    </div>

    <div class="fq-kpi">
        <div class="fq-kpi-row">
            <div class="fq-kpi-icon" style="background:var(--{{ $oldTone === 'text-dim' ? 'surface2' : $oldTone . '-dim' }});color:var(--{{ $oldTone }})"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
            <div><div class="fq-kpi-label">Oldest waiting</div><div class="fq-kpi-sub">Time since the sale</div></div>
        </div>
        <div class="fq-kpi-val" style="color:var(--{{ $oldTone === 'green' ? 'text' : $oldTone }})">{{ $fmtAge($stats['oldest_minutes']) }}</div>
        <div class="fq-kpi-divider"></div>
        <div>
            <div class="fq-kpi-stat"><span class="fq-kpi-stat-l">Waiting over 30 min</span><span class="fq-kpi-stat-v" style="{{ $stats['over_30m'] ? 'color:var(--amber)' : '' }}">{{ $stats['over_30m'] }}</span></div>
            <div class="fq-kpi-stat"><span class="fq-kpi-stat-l">Waiting over 2 h</span><span class="fq-kpi-stat-v" style="{{ $stats['over_2h'] ? 'color:var(--red)' : '' }}">{{ $stats['over_2h'] }}</span></div>
        </div>
    </div>

    <div class="fq-kpi">
        <div class="fq-kpi-row">
            <div class="fq-kpi-icon" style="background:var(--green-dim);color:var(--green)"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
            <div><div class="fq-kpi-label">Dispatched today</div><div class="fq-kpi-sub">Handed over and signed for</div></div>
        </div>
        <div class="fq-kpi-val" style="color:var(--{{ $stats['today'] ? 'green' : 'text-dim' }})">{{ $stats['today'] }}<span class="fq-kpi-unit">{{ $stats['today'] === 1 ? 'order' : 'orders' }}</span></div>
        <div class="fq-kpi-divider"></div>
        <div>
            <div class="fq-kpi-stat"><span class="fq-kpi-stat-l">Handed over</span><span class="fq-kpi-stat-v">{{ \App\Livewire\Warehouse\Sales\FulfillmentQueue::packLabel($stats['today_boxes'], $stats['today_items']) }}</span></div>
            <div class="fq-kpi-stat"><span class="fq-kpi-stat-l">Via transporter · pickup</span><span class="fq-kpi-stat-v">{{ $stats['today_transport'] }} · {{ $stats['today'] - $stats['today_transport'] }}</span></div>
        </div>
    </div>
</div>

@if($activeTab === 'pending')
    @if($dispatchMethod === 'scan')
        {{-- ── Scan mode: look up by pickup code (fallback: name/phone) ── --}}
        <div class="fq-card">
            <div class="fq-card-head">
                <div><div class="fq-card-title">Scan pickup code</div><div class="fq-card-sub">Printed on the customer's receipt</div></div>
            </div>
            <div class="fq-scan-body">
                <form class="fq-scan-row" wire:submit="scanReceipt">
                    <div class="fq-scan-wrap">
                        <svg class="fq-scan-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2M7 12h10M7 8h4M7 16h7"/></svg>
                        <input type="text" class="fq-scan-input" wire:model="receiptCode" placeholder="Scan or type a code, e.g. 7K2-9XP-4QW-1ZM"
                               autocomplete="off" maxlength="15" data-code-input autofocus aria-label="Pickup code">
                    </div>
                    <button type="submit" class="fq-btn fq-btn-primary" wire:loading.attr="disabled" wire:target="scanReceipt">Look up</button>
                </form>
                <div class="fq-scan-foot">
                    <button type="button" class="fq-link" wire:click="toggleCustomerSearch">
                        {{ $showCustomerSearch ? 'Hide name / phone search' : 'No receipt? Search by name or phone' }}
                    </button>
                </div>
                @if($showCustomerSearch)
                    <div class="fq-search-wrap" style="width:100%;margin-top:12px">
                        <svg class="fq-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" class="fq-search" wire:model.live.debounce.300ms="customerSearch" placeholder="Customer name or phone…" autocomplete="off">
                    </div>
                @endif
            </div>
        </div>

        @if($scannedCode)
            @if($scannedSale && $scannedSale->fulfillment_status === 'pending')
                <div class="fq-card">
                    <div class="fq-card-head">
                        <div><div class="fq-card-title">Ready to dispatch</div><div class="fq-card-sub">Code {{ $scannedSale->formatted_pickup_code ?? $scannedCode }}</div></div>
                        <button type="button" class="fq-link" wire:click="clearScan">Clear</button>
                    </div>
                    @include('livewire.warehouse.sales.partials.pending-row', ['sale' => $scannedSale, 'source' => 'scan'])
                </div>
            @elseif($scannedSale)
                @include('livewire.warehouse.sales.partials.lookup-result', ['sale' => $scannedSale, 'clearable' => true])
            @else
                <div class="fq-notice" style="border-left-color:var(--border-hi)">
                    <div class="fq-notice-main">
                        <div class="fq-notice-t">No order found</div>
                        <div class="fq-notice-s">Nothing at {{ $warehouseName }} matches <span style="font-family:var(--mono);font-weight:700;color:var(--text)">{{ $scannedCode }}</span>. Check the code, or search by name.</div>
                    </div>
                    <button type="button" class="fq-link" wire:click="clearScan">Clear</button>
                </div>
            @endif
        @elseif($showCustomerSearch && $customerSearch !== '')
            @php $pendingMatches = $customerSearchResults->where('fulfillment_status', 'pending'); @endphp
            @if($customerSearchResults->isEmpty())
                <div class="fq-notice" style="border-left-color:var(--border-hi)">
                    <div class="fq-notice-main">
                        <div class="fq-notice-t">No matching orders</div>
                        <div class="fq-notice-s">Nothing at {{ $warehouseName }} for “{{ $customerSearch }}”.</div>
                    </div>
                </div>
            @else
                @if($pendingMatches->isNotEmpty())
                    <div class="fq-card">
                        <div class="fq-card-head"><div class="fq-card-title">Awaiting dispatch · {{ $pendingMatches->count() }}</div></div>
                        @foreach($pendingMatches as $csale)
                            @include('livewire.warehouse.sales.partials.pending-row', ['sale' => $csale, 'source' => 'scan'])
                        @endforeach
                    </div>
                @endif
                @foreach($customerSearchResults->where('fulfillment_status', '!=', 'pending') as $csale)
                    @include('livewire.warehouse.sales.partials.lookup-result', ['sale' => $csale])
                @endforeach
            @endif
        @endif
    @else
        {{-- ── Queue mode: browsable list of orders awaiting pickup ── --}}
        <div class="fq-card">
            <div class="fq-card-head">
                <div>
                    <div class="fq-card-title">Awaiting dispatch</div>
                    <div class="fq-card-sub">{{ $pendingSales->count() }} {{ $pendingSales->count() === 1 ? 'order' : 'orders' }}{{ $queueDateFilter !== 'all' || $queueCustomerSearch !== '' ? ' match the filter' : '' }} · newest first</div>
                </div>
                <div class="fq-search-wrap">
                    <svg class="fq-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" class="fq-search" wire:model.live.debounce.300ms="queueCustomerSearch" placeholder="Customer name or phone…" autocomplete="off">
                </div>
            </div>
            <div class="fq-presets">
                @foreach($presets as $key => $label)
                    <button type="button" class="fq-preset {{ $queueDateFilter === $key ? 'active' : '' }}" wire:click="$set('queueDateFilter', '{{ $key }}')">{{ $label }}</button>
                @endforeach
            </div>

            @forelse($pendingSales as $sale)
                @include('livewire.warehouse.sales.partials.pending-row', ['sale' => $sale, 'source' => 'queue'])
            @empty
                <div class="fq-empty">
                    <div class="fq-empty-title">{{ $queueDateFilter !== 'all' || $queueCustomerSearch !== '' ? 'No orders match this filter' : 'Nothing waiting' }}</div>
                    <div class="fq-empty-sub">{{ $queueDateFilter !== 'all' || $queueCustomerSearch !== '' ? 'Try another period or clear the search.' : 'Warehouse-direct orders appear here as soon as they’re sold and stay until dispatched.' }}</div>
                </div>
            @endforelse
        </div>
    @endif
@else
    {{-- ── History ── --}}
    <div class="fq-card">
        <div class="fq-card-head">
            <div>
                <div class="fq-card-title">Dispatch history</div>
                <div class="fq-card-sub">{{ $fulfilledHistory->count() }}{{ $fulfilledHistory->count() >= 100 ? '+' : '' }} {{ $fulfilledHistory->count() === 1 ? 'dispatch' : 'dispatches' }} · click a row for details</div>
            </div>
            <div class="fq-search-wrap">
                <svg class="fq-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="fq-search" wire:model.live.debounce.300ms="historyCustomerSearch" placeholder="Customer name or phone…" autocomplete="off">
            </div>
        </div>
        <div class="fq-presets">
            @foreach($presets as $key => $label)
                <button type="button" class="fq-preset {{ $historyDateFilter === $key ? 'active' : '' }}" wire:click="$set('historyDateFilter', '{{ $key }}')">{{ $label }}</button>
            @endforeach
        </div>

        @if($fulfilledHistory->isEmpty())
            <div class="fq-empty">
                <div class="fq-empty-title">{{ $historyDateFilter !== 'all' || $historyCustomerSearch !== '' ? 'No dispatches match this filter' : 'No dispatches yet' }}</div>
                <div class="fq-empty-sub">{{ $historyDateFilter !== 'all' || $historyCustomerSearch !== '' ? 'Try another period or clear the search.' : 'Dispatched orders appear here with who collected them and their signature.' }}</div>
            </div>
        @else
            @php $cols = [150, 210, 170, 250, 170, 150]; @endphp
            <div class="fq-scroll">
                <table class="fq-table" style="min-width:{{ array_sum($cols) }}px">
                    <colgroup>@foreach($cols as $w)<col style="width:{{ $w }}px">@endforeach</colgroup>
                    <thead>
                        <tr><th>Dispatched</th><th>Sale</th><th>Shop</th><th>Items</th><th>Handed to</th><th>Confirmed by</th></tr>
                    </thead>
                    <tbody>
                        @foreach($fulfilledHistory as $sale)
                            @php
                                [$hB, $hI] = \App\Livewire\Warehouse\Sales\FulfillmentQueue::packTotals($sale);
                                $hProds = \App\Livewire\Warehouse\Sales\FulfillmentQueue::packList($sale)->map(fn ($p) => $p['name'] . ' ' . \App\Livewire\Warehouse\Sales\FulfillmentQueue::packQty($p));
                            @endphp
                            <tr wire:key="h-{{ $sale->id }}" class="{{ $expandedHistoryId === $sale->id ? 'active' : '' }}" wire:click="toggleHistory({{ $sale->id }})">
                                <td>
                                    <div style="font-weight:700;color:var(--text);white-space:nowrap">{{ local_time($sale->fulfillment_confirmed_at)?->format('d M, H:i') ?? '—' }}</div>
                                </td>
                                <td>
                                    <div class="fq-ellip" style="font-family:var(--mono);font-weight:700;color:var(--text)">{{ $sale->sale_number }}</div>
                                    <div class="fq-sub fq-ellip">{{ $sale->customer_name ?: 'Walk-in customer' }}</div>
                                </td>
                                <td><div class="fq-ellip">{{ $sale->shop?->name ?? '—' }}</div></td>
                                <td>
                                    <div class="fq-ellip" style="color:var(--text)" title="{{ $hProds->implode(', ') }}">{{ $hProds->implode(', ') }}</div>
                                    <div class="fq-sub">{{ \App\Livewire\Warehouse\Sales\FulfillmentQueue::packLabel($hB, $hI) }}</div>
                                </td>
                                <td>
                                    <div class="fq-ellip" style="color:var(--text)">{{ $sale->fulfillment_recipient_name ?? '—' }}</div>
                                    <div class="fq-sub fq-ellip">{{ $sale->fulfillment_method === 'transporter' ? ($sale->fulfillmentTransporter?->name ?? 'Transporter') : 'Customer pickup' }}</div>
                                </td>
                                <td><div class="fq-ellip">{{ $sale->fulfillmentConfirmedBy?->name ?? '—' }}</div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endif

{{-- ── History detail drawer ── --}}
@if($historySale)
    @php
        $dProds = \App\Livewire\Warehouse\Sales\FulfillmentQueue::packList($historySale);
        [$dB, $dI] = \App\Livewire\Warehouse\Sales\FulfillmentQueue::packTotals($historySale);
    @endphp
    <div x-data @keydown.escape.window="$wire.closeHistory()" wire:key="fq-drawer-{{ $historySale->id }}">
        <div class="fq-d-overlay" wire:click="closeHistory"></div>
        <aside class="fq-drawer" role="dialog" aria-modal="true" aria-labelledby="fq-d-title">
            <div class="fq-d-head">
                <div style="min-width:0">
                    <h2 class="fq-modal-title" id="fq-d-title" style="font-family:var(--mono)">{{ $historySale->sale_number }}</h2>
                    <div class="fq-modal-sub">
                        <span class="fq-badge" style="background:var(--green-dim);color:var(--green);vertical-align:middle">Dispatched</span>
                        {{ local_time($historySale->fulfillment_confirmed_at)?->format('l, d M Y · H:i') }}
                    </div>
                </div>
                <button type="button" class="fq-d-close" wire:click="closeHistory" aria-label="Close">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="fq-d-body">
                <div class="fq-sec">Handover</div>
                <div class="fq-drow"><span class="fq-drow-l">Handed to</span><span class="fq-drow-v">{{ $historySale->fulfillment_recipient_name ?? '—' }}</span></div>
                <div class="fq-drow"><span class="fq-drow-l">Method</span><span class="fq-drow-v">{{ $historySale->fulfillment_method === 'transporter' ? 'Transporter · ' . ($historySale->fulfillmentTransporter?->name ?? '—') : 'Customer pickup' }}</span></div>
                <div class="fq-drow"><span class="fq-drow-l">Confirmed by</span><span class="fq-drow-v">{{ $historySale->fulfillmentConfirmedBy?->name ?? '—' }}</span></div>
                @if($historySale->fulfillment_signature)
                    <div style="padding:10px 22px 4px">
                        <div class="fq-drow-l" style="font-size:12px;margin-bottom:6px">Signature</div>
                        <img src="{{ $historySale->fulfillment_signature }}" alt="Recipient signature" style="max-width:100%;height:auto;max-height:140px;border:1px solid var(--border);border-radius:8px;background:var(--surface)">
                    </div>
                @endif

                <div class="fq-sec">Order</div>
                <div class="fq-drow"><span class="fq-drow-l">Customer</span><span class="fq-drow-v">{{ $historySale->customer_name ?: 'Walk-in customer' }}@if($historySale->customer_phone) <span style="font-family:var(--mono);color:var(--text-dim);font-weight:500">· {{ $historySale->customer_phone }}</span>@endif</span></div>
                <div class="fq-drow"><span class="fq-drow-l">Sold at</span><span class="fq-drow-v">{{ $historySale->shop?->name ?? '—' }}</span></div>
                <div class="fq-drow"><span class="fq-drow-l">Sold by</span><span class="fq-drow-v">{{ $historySale->soldBy?->name ?? '—' }} · {{ local_time($historySale->sale_date)->format('d M, H:i') }}</span></div>
                @if($historySale->fulfillment_notes)
                    <div class="fq-drow"><span class="fq-drow-l">Note</span><span class="fq-drow-v" style="font-weight:500;font-style:italic">{{ $historySale->fulfillment_notes }}</span></div>
                @endif

                <div class="fq-sec">Out of {{ $warehouseName }} · {{ \App\Livewire\Warehouse\Sales\FulfillmentQueue::packLabel($dB, $dI) }}</div>
                @foreach($dProds as $p)
                    <div class="fq-drow">
                        <span class="fq-drow-l" style="color:var(--text-sub)">{{ $p['name'] }}@if($p['sku']) <span style="font-family:var(--mono);font-size:11px;color:var(--text-dim)">{{ $p['sku'] }}</span>@endif</span>
                        <span class="fq-drow-v" style="font-family:var(--mono)">{{ \App\Livewire\Warehouse\Sales\FulfillmentQueue::packQty($p) }}</span>
                    </div>
                @endforeach
            </div>
            <div class="fq-modal-foot">
                <a class="fq-btn fq-btn-ghost" href="{{ route('warehouse.sales.fulfillment.picking-slip', $historySale->id) }}" target="_blank" rel="noopener">Print slip</a>
                <button type="button" class="fq-btn fq-btn-ghost" wire:click="closeHistory">Done</button>
            </div>
        </aside>
    </div>
@endif

{{-- ── Dispatch confirmation modal ── --}}
@if($confirmingSale)
    @php
        $cProds = \App\Livewire\Warehouse\Sales\FulfillmentQueue::packList($confirmingSale);
        [$cB, $cI] = \App\Livewire\Warehouse\Sales\FulfillmentQueue::packTotals($confirmingSale);
        $isTransport = $confirmingSale->fulfillment_method === 'transporter';
    @endphp
    <div class="fq-overlay" wire:key="fq-confirm-{{ $confirmingSale->id }}" x-data @keydown.escape.window="$wire.cancelFulfillment()" @click.self="$wire.cancelFulfillment()">
        <div class="fq-modal" role="dialog" aria-modal="true" aria-labelledby="fq-c-title">
            <div class="fq-modal-head">
                <h2 class="fq-modal-title" id="fq-c-title">Dispatch {{ $confirmingSale->sale_number }}</h2>
                <p class="fq-modal-sub">
                    Hand over {{ \App\Livewire\Warehouse\Sales\FulfillmentQueue::packLabel($cB, $cI) }} to
                    {{ $isTransport ? ($confirmingSale->fulfillmentTransporter?->name ?? 'the transporter') : ($confirmingSale->customer_name ?: 'the customer') }}.
                    This can’t be undone.
                </p>
            </div>
            <form class="fq-modal-body" wire:submit="markFulfilled({{ $confirmingSale->id }})" id="fq-confirm-form">
                <div class="fq-pack">
                    @foreach($cProds as $p)
                        <div class="fq-pack-row"><span style="color:var(--text)">{{ $p['name'] }}</span><span style="font-family:var(--mono);font-weight:700;color:var(--text)">{{ \App\Livewire\Warehouse\Sales\FulfillmentQueue::packQty($p) }}</span></div>
                    @endforeach
                </div>

                <div class="fq-field">
                    <label class="fq-label" for="fq-recipient">{{ $isTransport ? 'Driver / agent collecting' : 'Person collecting' }} <span>*</span></label>
                    <input id="fq-recipient" type="text" class="fq-input" wire:model="recipientName"
                           placeholder="{{ $isTransport ? 'Name of the driver or agent' : 'Full name' }}" autocomplete="off"
                           x-init="$nextTick(() => $el.focus())">
                    @error('recipientName') <div class="fq-error">{{ $message }}</div> @enderror
                </div>

                @if($requireSignature)
                <div class="fq-field" style="margin-bottom:0">
                    <label class="fq-label">Signature <span>*</span></label>
                    <div class="fq-sig-wrap" wire:ignore wire:key="sig-wrap-{{ $confirmingSale->id }}">
                        <canvas id="sig-{{ $confirmingSale->id }}" class="fq-sig-canvas" data-sig-canvas width="880" height="280"></canvas>
                        <span class="fq-sig-hint">Sign here</span>
                        <button type="button" class="fq-sig-clear" data-sig-clear="sig-{{ $confirmingSale->id }}">Clear</button>
                    </div>
                    {{-- Outside wire:ignore so the error still renders --}}
                    @error('signatureData') <div class="fq-error">{{ $message }}</div> @enderror
                </div>
                @endif
            </form>
            <div class="fq-modal-foot">
                <button type="button" class="fq-btn fq-btn-ghost" wire:click="cancelFulfillment">Cancel</button>
                <button type="submit" form="fq-confirm-form" class="fq-btn fq-btn-primary" wire:loading.attr="disabled" wire:target="markFulfilled">
                    <span wire:loading.remove wire:target="markFulfilled">Confirm dispatch</span>
                    <span wire:loading.flex wire:target="markFulfilled" style="align-items:center;gap:6px">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="animation:fq-spin 1s linear infinite"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                        Saving…
                    </span>
                </button>
            </div>
        </div>
    </div>
@endif
</div>

{{--
    Signature pad init lives here (the component's root view) rather than in
    a partial. @script inside a Blade partial rendered via @include broke
    Livewire's AJAX response — the update response body came back with the
    whole component's rendered HTML prepended raw before the JSON envelope,
    which the client then failed to JSON.parse. _initSignaturePads() queries
    [data-sig-canvas] globally, so it doesn't need to sit next to the canvas.
--}}
@script
<script>
    window._initSignaturePads = window._initSignaturePads || function () {
        document.querySelectorAll('[data-sig-canvas]:not([data-sig-ready])').forEach(function (canvas) {
            canvas.dataset.sigReady = '1';

            const ctx = canvas.getContext('2d');
            ctx.lineWidth = 3;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = (getComputedStyle(document.documentElement).getPropertyValue('--text').trim()) || '#1a1f36';

            const hint = canvas.parentElement.querySelector('.fq-sig-hint');
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
                if (hint) hint.style.display = 'none';
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
                    if (hint) hint.style.display = '';
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
