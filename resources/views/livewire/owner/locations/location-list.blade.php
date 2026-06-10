<div style="font-family:var(--font)">
<style>
/* ── KPI strip ───────────────────────────────────────── */
.lm-kpis       { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px }
.lm-kpi        { background:var(--surface);border:none;border-radius:var(--r);
                 box-shadow:var(--shadow-card);
                 padding:22px 20px;display:flex;flex-direction:column;gap:16px;
                 transition:box-shadow var(--tr) }
.lm-kpi:hover  { box-shadow:var(--shadow-card-hover) }
.lm-kpi-row    { display:flex;align-items:center;gap:12px }
.lm-kpi-icon   { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;
                 justify-content:center;flex-shrink:0 }
.lm-kpi-body   { flex:1;min-width:0 }
.lm-kpi-label  { font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;
                 color:var(--text-dim);line-height:1.2 }
.lm-kpi-sub    { font-size:12px;color:var(--text-dim);margin-top:2px }
.lm-kpi-val    { font-size:24px;font-weight:800;font-family:var(--mono);letter-spacing:-1px;
                 line-height:1;flex-shrink:0 }
.lm-kpi-bar    { height:3px;border-radius:3px }

/* ── Tab strip ───────────────────────────────────────── */
.lm-tabs      { display:flex;gap:4px;margin-bottom:16px;flex-wrap:wrap }
.lm-tab       { display:flex;align-items:center;gap:7px;padding:8px 18px;border-radius:9px;
                border:1.5px solid var(--border);cursor:pointer;font-size:13px;font-weight:600;
                font-family:var(--font);background:var(--surface);color:var(--text-dim);
                transition:all var(--tr);white-space:nowrap }
.lm-tab:hover { border-color:var(--accent);color:var(--accent) }
.lm-tab.active { background:var(--accent);border-color:var(--accent);color:#fff }
.lm-tab-count { font-size:11px;font-weight:700;padding:1px 7px;border-radius:20px;
                background:rgba(255,255,255,.22);font-family:var(--mono) }
.lm-tab:not(.active) .lm-tab-count { background:var(--surface2);color:var(--text-dim) }

/* ── Filter bar ──────────────────────────────────────── */
.lm-bar          { display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:16px }
.lm-search-wrap  { flex:1;min-width:200px;position:relative }
.lm-search-icon  { position:absolute;left:11px;top:50%;transform:translateY(-50%);
                   width:14px;height:14px;color:var(--text-dim);pointer-events:none }
.lm-search       { width:100%;padding:9px 11px 9px 34px;border:1.5px solid var(--border);
                   border-radius:10px;font-size:14px;background:var(--surface);color:var(--text);
                   outline:none;box-sizing:border-box;font-family:var(--font);
                   transition:border-color var(--tr) }
.lm-search:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.lm-select       { padding:9px 12px;border:1.5px solid var(--border);border-radius:10px;
                   font-size:13px;background:var(--surface);color:var(--text);outline:none;
                   cursor:pointer;font-family:var(--font) }
.lm-btn-new      { display:flex;align-items:center;gap:7px;padding:9px 18px;background:var(--accent);
                   color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;
                   cursor:pointer;font-family:var(--font);box-shadow:0 3px 10px rgba(59,111,212,.25);
                   transition:opacity var(--tr);white-space:nowrap }
.lm-btn-new:hover { opacity:.88 }

/* ── Table ───────────────────────────────────────────── */
.lm-table-wrap { background:var(--surface);border:none;border-radius:var(--r);overflow:hidden;box-shadow:var(--shadow-card) }
.lm-table      { width:100%;border-collapse:collapse }
.lm-table thead tr { background:var(--bg);border-bottom:1px solid var(--border) }
.lm-table thead th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;
                     letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.lm-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.lm-table tbody tr:last-child { border-bottom:none }
.lm-table tbody tr:hover { background:var(--surface2) }
.lm-table tbody tr.inactive { opacity:.5 }
.lm-table td  { padding:13px 16px;font-size:13px;vertical-align:middle }

/* Location icon */
.lm-icon { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;
           justify-content:center;flex-shrink:0 }

/* Stat pill */
.lm-stat { display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;
           padding:3px 9px;border-radius:20px;white-space:nowrap }

/* Status badge */
.lm-badge     { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;
                padding:3px 9px;border-radius:6px;white-space:nowrap }
.lm-badge-dot { width:6px;height:6px;border-radius:50%;flex-shrink:0 }

/* Row action buttons */
.lm-action        { padding:5px 11px;border-radius:7px;border:1.5px solid var(--border);
                    background:transparent;font-size:12px;font-weight:600;cursor:pointer;
                    font-family:var(--font);color:var(--text-sub);transition:all var(--tr);white-space:nowrap }
.lm-action:hover  { border-color:var(--accent);color:var(--accent) }
.lm-action.danger:hover  { border-color:var(--amber);color:var(--amber) }
.lm-action.restore:hover { border-color:var(--green);color:var(--green) }

/* Inline confirm row */
.lm-confirm-row  { background:rgba(217,119,6,.05) !important }
.lm-confirm-wrap { display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap }
.lm-confirm-warn { font-size:12px;color:var(--amber);margin-top:4px;font-weight:500 }
.lm-confirm-yes  { padding:6px 16px;background:var(--amber);color:#fff;border:none;
                   border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;font-family:var(--font) }
.lm-confirm-no   { padding:6px 14px;background:transparent;border:1.5px solid var(--border);
                   color:var(--text-sub);border-radius:8px;font-size:12px;font-weight:600;
                   cursor:pointer;font-family:var(--font) }

/* Empty state */
.lm-empty       { padding:60px 20px;text-align:center }
.lm-empty-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.lm-empty-sub   { font-size:13px;color:var(--text-dim) }

/* ── Drawer ──────────────────────────────────────────── */
.lm-overlay     { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);
                  backdrop-filter:blur(2px) }
.lm-drawer      { position:fixed;top:0;right:0;bottom:0;z-index:401;
                  width:460px;max-width:100vw;background:var(--surface);
                  border-left:1px solid var(--border);
                  box-shadow:-8px 0 40px rgba(26,31,54,.14);
                  display:flex;flex-direction:column;
                  transform:translateX(100%);
                  transition:transform .22s cubic-bezier(.4,0,.2,1) }
.lm-drawer.open { transform:translateX(0) }
.lm-drawer-head { display:flex;align-items:center;justify-content:space-between;
                  padding:18px 22px;border-bottom:1px solid var(--border);flex-shrink:0 }
.lm-drawer-title { font-size:16px;font-weight:800;color:var(--text) }
.lm-drawer-close { width:32px;height:32px;border-radius:8px;border:none;
                   background:var(--surface2);color:var(--text-sub);cursor:pointer;
                   display:flex;align-items:center;justify-content:center;
                   transition:background var(--tr) }
.lm-drawer-close:hover { background:var(--surface3) }
.lm-drawer-body { flex:1;overflow-y:auto;padding:22px }
.lm-drawer-foot { padding:16px 22px;border-top:1px solid var(--border);
                  display:flex;gap:10px;flex-shrink:0 }

/* Drawer form */
.lm-field       { margin-bottom:18px }
.lm-field-row   { display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px }
.lm-label       { display:block;font-size:12px;font-weight:700;color:var(--text-sub);
                  margin-bottom:6px;letter-spacing:.3px }
.lm-label span  { color:var(--red) }
.lm-input       { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;
                  font-size:14px;background:var(--surface);color:var(--text);outline:none;
                  box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.lm-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.lm-error       { font-size:11px;color:var(--red);margin-top:4px }
.lm-hint        { font-size:11px;color:var(--text-dim);margin-top:4px;line-height:1.5 }

/* Toggle */
.lm-toggle-row    { display:flex;align-items:center;justify-content:space-between;
                    padding:14px;background:var(--surface2);border-radius:10px;
                    border:1px solid var(--border) }
.lm-toggle        { position:relative;width:42px;height:23px;flex-shrink:0;cursor:pointer }
.lm-toggle input  { position:absolute;opacity:0;width:0;height:0 }
.lm-toggle-track  { position:absolute;inset:0;border-radius:23px;background:var(--surface3);
                    border:1.5px solid var(--border);
                    transition:background var(--tr),border-color var(--tr) }
.lm-toggle input:checked ~ .lm-toggle-track { background:var(--green);border-color:var(--green) }
.lm-toggle-knob   { position:absolute;top:2.5px;left:2.5px;width:18px;height:18px;border-radius:50%;
                    background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.18);
                    transition:transform var(--tr);pointer-events:none }
.lm-toggle input:checked ~ .lm-toggle-track .lm-toggle-knob { transform:translateX(19px) }

/* Save / cancel */
.lm-save-btn      { flex:1;padding:12px;background:var(--accent);color:#fff;border:none;
                    border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;
                    font-family:var(--font);box-shadow:0 3px 10px rgba(59,111,212,.25);
                    transition:opacity var(--tr) }
.lm-save-btn:hover    { opacity:.88 }
.lm-save-btn:disabled { opacity:.5;cursor:not-allowed }
.lm-cancel-btn    { padding:12px 20px;background:transparent;border:1.5px solid var(--border);
                    color:var(--text-sub);border-radius:10px;font-size:14px;font-weight:600;
                    cursor:pointer;font-family:var(--font);transition:all var(--tr) }
.lm-cancel-btn:hover { border-color:var(--border-hi);color:var(--text) }

@keyframes lm-spin { to { transform:rotate(360deg) } }

/* Mobile */
@media(max-width:768px) {
    .lm-kpis     { grid-template-columns:1fr 1fr;gap:8px }
    .lm-kpi      { padding:12px 14px }
    .lm-kpi-val  { font-size:20px }
    .lm-hide-mob { display:none !important }
    .lm-bar      { flex-direction:column;align-items:stretch }
    .lm-select,.lm-btn-new { width:100%;justify-content:center }
    .lm-drawer   { width:100vw }
    .lm-drawer-body { padding:16px }
    .lm-drawer-foot { flex-direction:column }
    .lm-save-btn,.lm-cancel-btn { width:100%;text-align:center }
    .lm-field-row { grid-template-columns:1fr }
    .lm-table td,.lm-table th { padding:10px 10px }
}
/* Page header */
.lm-page-header { display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:24px;flex-wrap:wrap }
.lm-page-title  { font-size:26px;font-weight:800;color:var(--text);letter-spacing:-.4px }
.lm-page-sub    { font-size:13px;color:var(--text-dim);margin-top:3px }

@media(max-width:480px) {
    .lm-hide-sm    { display:none !important }
    .lm-action     { padding:4px 8px;font-size:11px }
    .lm-kpis       { grid-template-columns:1fr }
    .lm-page-title { font-size:21px }
}
</style>

{{-- ── Page header ──────────────────────────────────────────────────────── --}}
<div class="lm-page-header">
    <div>
        <div class="lm-page-title">Locations</div>
        <div class="lm-page-sub">Manage warehouses and retail shops across your business</div>
    </div>
    <button wire:click="openCreate" class="lm-btn-new">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        New {{ $activeTab === 'warehouses' ? 'Warehouse' : 'Shop' }}
    </button>
</div>

{{-- ── KPI strip ────────────────────────────────────────────────────────── --}}
<div class="section-label">Overview</div>
<div class="lm-kpis">
    <div class="lm-kpi">
        <div class="lm-kpi-row">
            <div class="lm-kpi-icon" style="background:var(--green-dim);color:var(--green)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="2" y="7" width="20" height="14" rx="2"/>
                    <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                </svg>
            </div>
            <div class="lm-kpi-body">
                <div class="lm-kpi-label">Warehouses</div>
                <div class="lm-kpi-sub">{{ $stats['warehouses_active'] }}/{{ $stats['warehouses_total'] }} active</div>
            </div>
            <div class="lm-kpi-val" style="color:var(--green)">{{ $stats['warehouses_active'] }}</div>
        </div>
        <div class="lm-kpi-bar" style="background:var(--green-dim)">
            <div style="height:100%;border-radius:3px;background:var(--green);
                        width:{{ $stats['warehouses_total'] > 0 ? round($stats['warehouses_active']/$stats['warehouses_total']*100) : 0 }}%">
            </div>
        </div>
    </div>
    <div class="lm-kpi">
        <div class="lm-kpi-row">
            <div class="lm-kpi-icon" style="background:var(--accent-dim);color:var(--accent)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
            <div class="lm-kpi-body">
                <div class="lm-kpi-label">Shops</div>
                <div class="lm-kpi-sub">{{ $stats['shops_active'] }}/{{ $stats['shops_total'] }} active</div>
            </div>
            <div class="lm-kpi-val" style="color:var(--accent)">{{ $stats['shops_active'] }}</div>
        </div>
        <div class="lm-kpi-bar" style="background:var(--accent-dim)">
            <div style="height:100%;border-radius:3px;background:var(--accent);
                        width:{{ $stats['shops_total'] > 0 ? round($stats['shops_active']/$stats['shops_total']*100) : 0 }}%">
            </div>
        </div>
    </div>
    <div class="lm-kpi">
        <div class="lm-kpi-row">
            <div class="lm-kpi-icon" style="background:var(--violet-dim);color:var(--violet)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="2" y1="12" x2="22" y2="12"/>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                </svg>
            </div>
            <div class="lm-kpi-body">
                <div class="lm-kpi-label">Total Locations</div>
                <div class="lm-kpi-sub">across the business</div>
            </div>
            <div class="lm-kpi-val">{{ $stats['warehouses_total'] + $stats['shops_total'] }}</div>
        </div>
        <div class="lm-kpi-bar" style="background:var(--violet-dim)">
            <div style="height:100%;border-radius:3px;background:var(--violet);width:100%"></div>
        </div>
    </div>
    @php
        $totalAll = $stats['warehouses_total'] + $stats['shops_total'];
        $totalActive = $stats['warehouses_active'] + $stats['shops_active'];
    @endphp
    <div class="lm-kpi">
        <div class="lm-kpi-row">
            <div class="lm-kpi-icon" style="background:var(--green-dim);color:var(--green)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="lm-kpi-body">
                <div class="lm-kpi-label">Active Total</div>
                <div class="lm-kpi-sub">warehouses + shops</div>
            </div>
            <div class="lm-kpi-val" style="color:var(--green)">{{ $totalActive }}</div>
        </div>
        <div class="lm-kpi-bar" style="background:var(--green-dim)">
            <div style="height:100%;border-radius:3px;background:var(--green);
                        width:{{ $totalAll > 0 ? round($totalActive/$totalAll*100) : 0 }}%">
            </div>
        </div>
    </div>
</div>

{{-- ── Tabs ──────────────────────────────────────────────────────────────── --}}
<div class="lm-tabs">
    <button wire:click="setTab('warehouses')"
            class="lm-tab {{ $activeTab === 'warehouses' ? 'active' : '' }}">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="2" y="7" width="20" height="14" rx="2"/>
            <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
        </svg>
        Warehouses
        <span class="lm-tab-count">{{ $stats['warehouses_total'] }}</span>
    </button>
    <button wire:click="setTab('shops')"
            class="lm-tab {{ $activeTab === 'shops' ? 'active' : '' }}">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        Shops
        <span class="lm-tab-count">{{ $stats['shops_total'] }}</span>
    </button>
</div>

{{-- ── Controls ──────────────────────────────────────────────────────────── --}}
<div class="lm-bar">
    <div class="lm-search-wrap">
        <svg class="lm-search-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <input wire:model.live.debounce.300ms="search"
               class="lm-search" type="text"
               placeholder="Search by name, code, or city…">
    </div>
    <select wire:model.live="statusFilter" class="lm-select">
        <option value="all">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
    </select>
</div>

{{-- ══════════════════════════════════════════════
     WAREHOUSES TABLE
══════════════════════════════════════════════ --}}
@if($activeTab === 'warehouses')
<div class="section-label">Warehouses</div>
<div class="lm-table-wrap">
    <div style="overflow-x:auto">
    <table class="lm-table">
        <thead>
            <tr>
                <th style="width:50px"></th>
                <th>Name & Code</th>
                <th class="lm-hide-mob">City</th>
                <th class="lm-hide-mob">Phone</th>
                <th class="lm-hide-sm">Managers</th>
                <th>Boxes</th>
                <th class="lm-hide-mob">Transfers</th>
                <th>Status</th>
                <th style="text-align:right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            @if($confirmToggleId === $row->id)
            <tr class="lm-confirm-row">
                <td colspan="9" style="padding:14px 16px">
                    <div class="lm-confirm-wrap">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                             viewBox="0 0 24 24" style="color:var(--amber);flex-shrink:0;margin-top:1px">
                            <path d="m10.29 3.86-8.59 14.89A2 2 0 0 0 3.43 22h17.14a2 2 0 0 0 1.73-3.25L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        <div style="flex:1">
                            <div style="font-size:13px;font-weight:600;color:var(--text)">
                                {{ $confirmToggleActive ? 'Deactivate' : 'Reactivate' }}
                                <strong>{{ $confirmToggleName }}</strong>?
                            </div>
                            @if($confirmToggleWarning)
                            <div class="lm-confirm-warn">⚠ {{ $confirmToggleWarning }}</div>
                            @endif
                        </div>
                        <button wire:click="executeToggle" class="lm-confirm-yes">
                            {{ $confirmToggleActive ? 'Yes, deactivate' : 'Yes, activate' }}
                        </button>
                        <button wire:click="cancelToggle" class="lm-confirm-no">Cancel</button>
                    </div>
                </td>
            </tr>
            @else
            <tr class="{{ !$row->is_active ? 'inactive' : '' }}">
                <td style="padding-left:14px">
                    <div class="lm-icon" style="background:var(--green-dim);color:var(--green)">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="2" y="7" width="20" height="14" rx="2"/>
                            <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                        </svg>
                    </div>
                </td>
                <td>
                    <div style="font-weight:700;color:var(--text);font-size:14px">{{ $row->name }}</div>
                    <div style="font-size:11px;font-family:var(--mono);color:var(--text-dim);margin-top:1px">
                        {{ $row->code }}
                    </div>
                </td>
                <td class="lm-hide-mob" style="color:var(--text-sub)">{{ $row->city ?? '—' }}</td>
                <td class="lm-hide-mob" style="font-size:12px;font-family:var(--mono);color:var(--text-sub)">
                    {{ $row->phone ?? '—' }}
                </td>
                <td class="lm-hide-sm">
                    <span class="lm-stat" style="background:var(--accent-dim);color:var(--accent)">
                        {{ $row->manager_count }} {{ Str::plural('manager', $row->manager_count) }}
                    </span>
                </td>
                <td>
                    <span class="lm-stat" style="background:var(--surface2);color:var(--text-sub)">
                        {{ number_format($row->box_count) }} boxes
                    </span>
                </td>
                <td class="lm-hide-mob">
                    @if($row->active_transfers > 0)
                    <span class="lm-stat" style="background:var(--amber-dim);color:var(--amber)">
                        {{ $row->active_transfers }} active
                    </span>
                    @else
                    <span style="color:var(--text-dim)">—</span>
                    @endif
                </td>
                <td>
                    @if($row->is_active)
                    <span class="lm-badge" style="background:var(--green-dim);color:var(--green)">
                        <span class="lm-badge-dot" style="background:var(--green)"></span> Active
                    </span>
                    @else
                    <span class="lm-badge" style="background:var(--surface2);color:var(--text-dim)">
                        <span class="lm-badge-dot" style="background:var(--text-dim)"></span> Inactive
                    </span>
                    @endif
                </td>
                <td style="text-align:right">
                    <div style="display:flex;gap:6px;justify-content:flex-end">
                        <button wire:click="openEdit({{ $row->id }})" class="lm-action">Edit</button>
                        <button wire:click="confirmToggle({{ $row->id }})"
                                class="lm-action {{ $row->is_active ? 'danger' : 'restore' }}">
                            {{ $row->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </div>
                </td>
            </tr>
            @endif
            @empty
            <tr><td colspan="9">
                <div class="lm-empty">
                    <svg width="38" height="38" fill="none" stroke="currentColor" stroke-width="1.5"
                         viewBox="0 0 24 24" style="color:var(--border);margin:0 auto 12px;display:block">
                        <rect x="2" y="7" width="20" height="14" rx="2"/>
                        <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                    </svg>
                    <div class="lm-empty-title">
                        {{ $search ? 'No warehouses match "' . $search . '"' : 'No warehouses yet' }}
                    </div>
                    <div class="lm-empty-sub">
                        {{ !$search ? 'Click "New Warehouse" to add your first storage location.' : '' }}
                    </div>
                </div>
            </td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    @if($rows->hasPages())
    <div style="padding:12px 16px;border-top:1px solid var(--border)">{{ $rows->links() }}</div>
    @endif
</div>

{{-- ══════════════════════════════════════════════
     SHOPS TABLE
══════════════════════════════════════════════ --}}
@else
<div class="section-label">Shops</div>
<div class="lm-table-wrap">
    <div style="overflow-x:auto">
    <table class="lm-table">
        <thead>
            <tr>
                <th style="width:50px"></th>
                <th>Name & Code</th>
                <th class="lm-hide-mob">City</th>
                <th class="lm-hide-mob">Default Warehouse</th>
                <th class="lm-hide-sm">Managers</th>
                <th>Boxes</th>
                <th class="lm-hide-mob">Sales Today</th>
                <th>Status</th>
                <th style="text-align:right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            @if($confirmToggleId === $row->id)
            <tr class="lm-confirm-row">
                <td colspan="9" style="padding:14px 16px">
                    <div class="lm-confirm-wrap">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                             viewBox="0 0 24 24" style="color:var(--amber);flex-shrink:0;margin-top:1px">
                            <path d="m10.29 3.86-8.59 14.89A2 2 0 0 0 3.43 22h17.14a2 2 0 0 0 1.73-3.25L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        <div style="flex:1">
                            <div style="font-size:13px;font-weight:600;color:var(--text)">
                                {{ $confirmToggleActive ? 'Deactivate' : 'Reactivate' }}
                                <strong>{{ $confirmToggleName }}</strong>?
                            </div>
                            @if($confirmToggleWarning)
                            <div class="lm-confirm-warn">⚠ {{ $confirmToggleWarning }}</div>
                            @endif
                        </div>
                        <button wire:click="executeToggle" class="lm-confirm-yes">
                            {{ $confirmToggleActive ? 'Yes, deactivate' : 'Yes, activate' }}
                        </button>
                        <button wire:click="cancelToggle" class="lm-confirm-no">Cancel</button>
                    </div>
                </td>
            </tr>
            @else
            <tr class="{{ !$row->is_active ? 'inactive' : '' }}">
                <td style="padding-left:14px">
                    <div class="lm-icon" style="background:var(--accent-dim);color:var(--accent)">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                    </div>
                </td>
                <td>
                    <div style="font-weight:700;color:var(--text);font-size:14px">{{ $row->name }}</div>
                    <div style="font-size:11px;font-family:var(--mono);color:var(--text-dim);margin-top:1px">
                        {{ $row->code }}
                    </div>
                </td>
                <td class="lm-hide-mob" style="color:var(--text-sub)">{{ $row->city ?? '—' }}</td>
                <td class="lm-hide-mob" style="font-size:13px;color:var(--text-sub)">
                    {{ $row->defaultWarehouse?->name ?? '—' }}
                </td>
                <td class="lm-hide-sm">
                    <span class="lm-stat" style="background:var(--violet-dim);color:var(--violet)">
                        {{ $row->manager_count }} {{ Str::plural('manager', $row->manager_count) }}
                    </span>
                </td>
                <td>
                    <span class="lm-stat" style="background:var(--surface2);color:var(--text-sub)">
                        {{ number_format($row->box_count) }} boxes
                    </span>
                </td>
                <td class="lm-hide-mob">
                    @if($row->sales_today > 0)
                    <span class="lm-stat" style="background:var(--green-dim);color:var(--green)">
                        {{ $row->sales_today }} sales
                    </span>
                    @else
                    <span style="color:var(--text-dim)">—</span>
                    @endif
                </td>
                <td>
                    @if($row->is_active)
                    <span class="lm-badge" style="background:var(--green-dim);color:var(--green)">
                        <span class="lm-badge-dot" style="background:var(--green)"></span> Active
                    </span>
                    @else
                    <span class="lm-badge" style="background:var(--surface2);color:var(--text-dim)">
                        <span class="lm-badge-dot" style="background:var(--text-dim)"></span> Inactive
                    </span>
                    @endif
                </td>
                <td style="text-align:right">
                    <div style="display:flex;gap:6px;justify-content:flex-end">
                        <button wire:click="openEdit({{ $row->id }})" class="lm-action">Edit</button>
                        <button wire:click="confirmToggle({{ $row->id }})"
                                class="lm-action {{ $row->is_active ? 'danger' : 'restore' }}">
                            {{ $row->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </div>
                </td>
            </tr>
            @endif
            @empty
            <tr><td colspan="9">
                <div class="lm-empty">
                    <svg width="38" height="38" fill="none" stroke="currentColor" stroke-width="1.5"
                         viewBox="0 0 24 24" style="color:var(--border);margin:0 auto 12px;display:block">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    <div class="lm-empty-title">
                        {{ $search ? 'No shops match "' . $search . '"' : 'No shops yet' }}
                    </div>
                    <div class="lm-empty-sub">
                        {{ !$search ? 'Click "New Shop" to add your first retail location.' : '' }}
                    </div>
                </div>
            </td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    @if($rows->hasPages())
    <div style="padding:12px 16px;border-top:1px solid var(--border)">{{ $rows->links() }}</div>
    @endif
</div>
@endif

{{-- ══════════════════════════════════════════════
     DRAWER
══════════════════════════════════════════════ --}}
@if($showDrawer)
<div class="lm-overlay" wire:click="closeDrawer"></div>
@endif

<div class="lm-drawer {{ $showDrawer ? 'open' : '' }}">
    <div class="lm-drawer-head">
        <div class="lm-drawer-title">
            @if($isEditing)
                Edit {{ $activeTab === 'warehouses' ? 'Warehouse' : 'Shop' }}
            @else
                New {{ $activeTab === 'warehouses' ? 'Warehouse' : 'Shop' }}
            @endif
        </div>
        <button wire:click="closeDrawer" class="lm-drawer-close">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    <div class="lm-drawer-body">
        <div class="lm-field-row">
            <div>
                <label class="lm-label">Name <span>*</span></label>
                <input wire:model="form_name" type="text" class="lm-input"
                       placeholder="{{ $activeTab === 'warehouses' ? 'e.g. Main Warehouse' : 'e.g. Remera Shop' }}">
                @error('form_name') <div class="lm-error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="lm-label">Code <span>*</span></label>
                <input wire:model="form_code" type="text" class="lm-input"
                       placeholder="{{ $activeTab === 'warehouses' ? 'e.g. WH-001' : 'e.g. SHOP-REM' }}"
                       style="text-transform:uppercase">
                @error('form_code') <div class="lm-error">{{ $message }}</div> @enderror
                <div class="lm-hint">Short unique identifier. Uppercase recommended.</div>
            </div>
        </div>

        <div class="lm-field">
            <label class="lm-label">Street Address</label>
            <input wire:model="form_address" type="text" class="lm-input"
                   placeholder="e.g. KG 9 Ave, Remera">
        </div>

        <div class="lm-field-row">
            <div>
                <label class="lm-label">City</label>
                <input wire:model="form_city" type="text" class="lm-input" placeholder="e.g. Kigali">
            </div>
            <div>
                <label class="lm-label">Phone</label>
                <input wire:model="form_phone" type="text" class="lm-input" placeholder="+250 788 000 000">
            </div>
        </div>

        @if($activeTab === 'shops')
        <div class="lm-field">
            <label class="lm-label">Default Warehouse <span>*</span></label>
            <select wire:model="form_default_warehouse_id" class="lm-input">
                <option value="">— Select warehouse —</option>
                @foreach($this->activeWarehouses as $wh)
                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                @endforeach
            </select>
            @error('form_default_warehouse_id')
                <div class="lm-error">{{ $message }}</div>
            @enderror
            <div class="lm-hint">Transfer requests from this shop will default to this warehouse.</div>
        </div>
        @endif

        <div style="border-top:1px solid var(--border);margin:20px 0"></div>

        <div class="lm-toggle-row">
            <div>
                <div style="font-size:14px;font-weight:600;color:var(--text)">Location Active</div>
                <div style="font-size:12px;color:var(--text-dim);margin-top:2px">
                    Inactive locations cannot receive stock or process sales
                </div>
            </div>
            <label class="lm-toggle">
                <input type="checkbox" wire:model="form_is_active">
                <div class="lm-toggle-track"><div class="lm-toggle-knob"></div></div>
            </label>
        </div>
    </div>

    <div class="lm-drawer-foot">
        <button wire:click="closeDrawer" class="lm-cancel-btn">Cancel</button>
        <button wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
                class="lm-save-btn">
            <span wire:loading.remove wire:target="save">
                {{ $isEditing ? 'Save Changes' : 'Create ' . ($activeTab === 'warehouses' ? 'Warehouse' : 'Shop') }}
            </span>
            <span wire:loading wire:target="save"
                  style="display:none;align-items:center;gap:8px;justify-content:center">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5"
                     style="animation:lm-spin 1s linear infinite">
                    <path d="M21 12a9 9 0 11-6.219-8.56"/>
                </svg>
                Saving…
            </span>
        </button>
    </div>
</div>

</div>
