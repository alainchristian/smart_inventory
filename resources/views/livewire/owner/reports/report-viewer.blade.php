@php
    use App\Services\Reports\ReportFormat as F;

    // Icon + colour per domain
    $domainIcon = [
        'sales'         => ['accent', '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>'],
        'inventory'     => ['violet', '<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>'],
        'replenishment' => ['amber', '<polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>'],
        'loss'          => ['red', '<polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/>'],
        'transfers'     => ['accent', '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>'],
        'operations'    => ['amber', '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06A1.65 1.65 0 004.6 15a1.65 1.65 0 00-1.51-1H3a2 2 0 110-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06A1.65 1.65 0 009 4.6a1.65 1.65 0 001-1.51V3a2 2 0 114 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z"/>'],
        'finance'       => ['green', '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>'],
    ];
    $toneColor = ['good' => 'green', 'warn' => 'amber', 'bad' => 'red', 'neutral' => 'accent'];
    $defaultTextTitle = 'Text / Narrative';
@endphp
<div class="rv-page" style="font-family:var(--font)" wire:init="load">
<style>
.rv-page { padding:0 0 80px }

/* Header */
.rv-back { display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:var(--text-dim);text-decoration:none;margin-bottom:10px }
.rv-back:hover { color:var(--accent) }
.rv-header { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:18px;flex-wrap:wrap }
.rv-title  { font-size:22px;font-weight:800;color:var(--text);margin:0 0 4px }
.rv-desc   { font-size:13px;color:var(--text-sub);margin:0 0 6px;max-width:720px }
.rv-meta   { display:flex;align-items:center;gap:6px;flex-wrap:wrap;font-size:12px;color:var(--text-dim) }
.rv-pill   { display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;white-space:nowrap }
.rv-actions { display:flex;gap:8px;align-items:center;flex-wrap:wrap }

/* Buttons */
.rv-btn { padding:8px 14px;border-radius:var(--rsm);font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font);
          transition:all var(--tr);display:inline-flex;align-items:center;gap:6px;white-space:nowrap;text-decoration:none }
.rv-btn-primary { background:var(--accent);color:#fff;border:none;box-shadow:0 3px 10px rgba(59,111,212,.25) }
.rv-btn-primary:hover { opacity:.88 }
.rv-btn-ghost { background:var(--surface);color:var(--text-sub);border:1px solid var(--border) }
.rv-btn-ghost:hover { background:var(--surface2);color:var(--text) }
.rv-btn:disabled { opacity:.5;cursor:not-allowed }
.rv-menu { position:relative }
.rv-menu-list { position:absolute;right:0;top:calc(100% + 6px);z-index:30;min-width:190px;background:var(--surface);
                border-radius:var(--rsm);box-shadow:var(--shadow-card-hover);padding:6px }
.rv-menu-item { display:flex;align-items:center;gap:8px;width:100%;padding:8px 10px;border:none;background:transparent;
                border-radius:6px;font-size:13px;font-weight:500;color:var(--text-sub);cursor:pointer;font-family:var(--font);text-decoration:none;text-align:left }
.rv-menu-item:hover { background:var(--surface2);color:var(--text) }
.rv-menu-note { font-size:11px;color:var(--text-dim);padding:6px 10px 2px }

/* Filters (§6.2) */
.rv-filters { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);margin-bottom:16px;min-width:0;max-width:100% }
.rv-presets { display:flex;gap:4px;overflow-x:auto;padding:10px 14px;border-bottom:1px solid var(--border);scrollbar-width:none;flex-wrap:nowrap;min-width:0 }
.rv-presets::-webkit-scrollbar { display:none }
.rv-preset { padding:5px 11px;border-radius:6px;font-size:12px;font-weight:600;border:1px solid transparent;background:transparent;
             color:var(--text-dim);cursor:pointer;white-space:nowrap;flex-shrink:0;transition:all var(--tr);font-family:var(--font) }
.rv-preset:hover  { background:var(--surface2);color:var(--text);border-color:var(--border) }
.rv-preset.active { background:var(--accent);color:#fff;border-color:var(--accent);box-shadow:0 2px 8px rgba(0,0,0,.12) }
.rv-filter-row { display:flex;align-items:center;flex-wrap:wrap }
.rv-seg { display:flex;align-items:center;gap:8px;padding:8px 14px;border-right:1px solid var(--border);min-width:0 }
.rv-seg:last-child { border-right:none }
.rv-seg-grow { flex:1 }
.rv-seg-label { font-size:11px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--text-dim);flex-shrink:0 }
.rv-date, .rv-select { padding:0;border:none;background:transparent;color:var(--text);font-size:13px;font-weight:600;
                       font-family:var(--font);cursor:pointer;outline:none;min-width:0 }
.rv-date { width:118px }
.rv-date:focus, .rv-select:focus { color:var(--accent) }
.rv-reset { font-size:12px;font-weight:600;color:var(--accent);background:none;border:none;cursor:pointer;font-family:var(--font);white-space:nowrap }
.rv-phone-bar { display:none }

/* Context line */
.rv-context { display:flex;align-items:center;gap:8px;flex-wrap:wrap;font-size:12px;color:var(--text-dim);margin:0 2px 16px }
.rv-context strong { color:var(--text-sub);font-weight:700 }
.rv-dot { width:3px;height:3px;border-radius:50%;background:var(--text-dim) }

/* Loading */
.rv-body { transition:opacity var(--tr) }
.rv-busy { opacity:.45;pointer-events:none }
.rv-skel { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);height:150px;position:relative;overflow:hidden }
.rv-skel::after { content:'';position:absolute;inset:0;background:linear-gradient(90deg,transparent,var(--surface2),transparent);animation:rv-shine 1.2s infinite }
@keyframes rv-shine { from { transform:translateX(-100%) } to { transform:translateX(100%) } }
@keyframes rv-spin  { to { transform:rotate(360deg) } }
.rv-spinning { animation:rv-spin 1s linear infinite }

/* Key findings */
.rv-findings { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);margin-bottom:20px }
.rv-findings-head { padding:12px 18px;border-bottom:1px solid var(--border);font-size:13px;font-weight:700;color:var(--text) }
.rv-findings-grid { display:grid;grid-template-columns:repeat(2,minmax(0,1fr)) }
.rv-finding { padding:12px 18px 12px 15px;border-left:3px solid;border-bottom:1px solid var(--border) }
.rv-finding-title { font-size:11px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--text-dim);margin-bottom:3px }
.rv-finding-text  { font-size:13px;color:var(--text-sub);line-height:1.45 }

/* KPI cards: shared .ui-kpi (app.css); page bits only */
.rv-kpi-status { width:8px;height:8px;border-radius:50%;flex-shrink:0 }
.rv-kpi-info   { color:var(--text-dim);cursor:help;display:inline-flex;vertical-align:-2px;margin-left:4px }
.rv-kpi-more   { align-self:flex-start;font-size:12px;font-weight:600;color:var(--accent);background:none;border:none;padding:0;cursor:pointer;font-family:var(--font) }
.rv-kpi-more:hover { text-decoration:underline }
.rv-kpi-error  { font-size:13px;color:var(--red) }

/* Blocks */
.rv-grid  { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px }
.rv-full  { grid-column:1 / -1 }
.rv-card  { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);min-width:0;display:flex;flex-direction:column }
.rv-card-head  { padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:flex-start;justify-content:space-between;gap:12px }
.rv-card-title { font-size:14px;font-weight:700;color:var(--text);margin:0 }
.rv-card-sub   { font-size:12px;color:var(--text-dim);margin-top:2px }
.rv-card-total { font-family:var(--mono);font-size:15px;font-weight:800;color:var(--text);white-space:nowrap;text-align:right }
.rv-card-total small { display:block;font-family:var(--font);font-size:11px;font-weight:600;color:var(--text-dim);margin-top:2px }
.rv-card-body  { padding:4px 0;flex:1;min-width:0 }
.rv-card-foot  { padding:10px 18px;border-top:1px solid var(--border);display:flex;flex-direction:column;gap:6px }
.rv-insight { display:flex;align-items:flex-start;gap:8px;font-size:12.5px;color:var(--text-sub);line-height:1.45 }
.rv-insight-dot { width:7px;height:7px;border-radius:50%;margin-top:5px;flex-shrink:0 }
.rv-note { font-size:11.5px;color:var(--text-dim);line-height:1.45 }
.rv-text { padding:16px 18px;font-size:14px;line-height:1.65;color:var(--text-sub);white-space:pre-wrap }
.rv-error { margin:14px 18px;padding:10px 14px;border-left:3px solid var(--red);font-size:13px;color:var(--text-sub) }

/* Tables */
.rv-scroll { overflow-x:auto;-webkit-overflow-scrolling:touch }
.rv-table  { width:100%;border-collapse:collapse }
.rv-table thead tr { border-bottom:2px solid var(--border) }
.rv-table th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.rv-table td { padding:10px 16px;font-size:13px;color:var(--text-sub);white-space:nowrap;max-width:320px;overflow:hidden;text-overflow:ellipsis }
.rv-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.rv-table tbody tr:last-child { border-bottom:none }
.rv-table tbody tr:hover { background:var(--surface2) }
.rv-table tfoot tr { border-top:2px solid var(--border) }
.rv-table tfoot td { font-weight:700;color:var(--text) }
.rv-table .rv-num { text-align:right;font-family:var(--mono);font-size:12.5px }
.rv-table .rv-first { color:var(--text);font-weight:600 }
.rv-table .rv-neg { color:var(--red) }
.rv-empty { padding:28px 18px;text-align:center;font-size:13px;color:var(--text-dim) }

/* Charts */
.rv-chart { position:relative;height:260px;padding:12px 14px 8px }
.rv-chart canvas { display:block }

/* Placeholder (report with no blocks) */
.rv-placeholder { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);padding:60px 20px;text-align:center }
.rv-placeholder-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.rv-placeholder-sub   { font-size:13px;color:var(--text-dim) }

/* History drawer (§14) */
.rv-overlay { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);backdrop-filter:blur(2px) }
.rv-drawer  { position:fixed;top:0;right:0;bottom:0;z-index:401;width:460px;max-width:100vw;background:var(--surface);
              border-left:1px solid var(--border);box-shadow:-8px 0 40px rgba(26,31,54,.14);display:flex;flex-direction:column }
.rv-drawer-head  { display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--border);flex-shrink:0 }
.rv-drawer-title { font-size:16px;font-weight:800;color:var(--text) }
.rv-drawer-close { width:32px;height:32px;border-radius:8px;border:none;background:var(--surface2);color:var(--text-sub);cursor:pointer;display:flex;align-items:center;justify-content:center }
.rv-drawer-body  { flex:1;overflow-y:auto;padding:8px 0 }
.rv-run { display:block;width:100%;text-align:left;padding:14px 22px;border:none;border-bottom:1px solid var(--border);background:transparent;cursor:pointer;font-family:var(--font) }
.rv-run:hover { background:var(--surface2) }
.rv-run-top { display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:4px }
.rv-run-period { font-size:13px;font-weight:700;color:var(--text) }
.rv-run-when   { font-size:11px;color:var(--text-dim);white-space:nowrap }
.rv-run-meta   { font-size:12px;color:var(--text-dim) }
.rv-run-figs   { display:flex;flex-wrap:wrap;gap:4px 12px;margin-top:6px }
.rv-run-fig    { font-size:12px;color:var(--text-sub) }
.rv-run-fig b  { font-family:var(--mono);color:var(--text);font-weight:700 }

/* Details sheet body */
.rv-detail-section { margin-bottom:18px }
.rv-detail-title { font-size:12px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--accent);margin:0 0 6px }
.rv-detail-kpi { font-size:13px;color:var(--text-sub);margin-bottom:14px;line-height:1.5 }

@media (max-width:1100px) {
    .rv-grid { grid-template-columns:1fr }
}
@media (max-width:768px) {
    .rv-drawer { left:0;width:auto }
    .rv-findings-grid { grid-template-columns:1fr }
}
@media (max-width:640px) {
    .rv-title { font-size:var(--m-fs-title) }
    .rv-header { margin-bottom:12px }
    .rv-actions { width:100% }
    .rv-actions > * { flex:1;justify-content:center }
    .rv-phone-bar { display:flex;margin-bottom:12px }
    .rv-phone-period { flex:1;min-width:0;font-size:13px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis }
    .rv-filters.m-filter-panel { margin-bottom:0 }
    .rv-filter-row { flex-direction:column;align-items:stretch }
    .rv-seg { border-right:none;border-bottom:1px solid var(--border);padding:10px 14px }
    .rv-seg:last-child { border-bottom:none }
    .rv-date { width:auto;flex:1 }
    .rv-select { flex:1 }
    .rv-context { margin-bottom:12px }
    .rv-grid { gap:12px }
    .rv-card-head { padding:12px 14px }
    .rv-chart { height:220px }
}
</style>

{{-- ═══ Header ═══════════════════════════════════════════════════════════ --}}
<a href="{{ route('owner.reports.custom.library') }}" class="rv-back" wire:navigate>
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Custom reports
</a>

<div class="rv-header m-page-head">
    <div style="min-width:0">
        <h1 class="rv-title">{{ $report->name }}</h1>
        @if ($report->description)
            <p class="rv-desc">{{ $report->description }}</p>
        @endif
        <div class="rv-meta">
            <span>By {{ $report->creator?->name ?? 'Unknown' }}</span>
            <span class="rv-dot"></span>
            <span>{{ $blockCount }} {{ Str::plural('block', $blockCount) }}</span>
            @if ($report->is_shared)
                <span class="rv-pill" style="background:var(--accent-dim);color:var(--accent)">Shared</span>
            @endif
            @if ($report->schedule_cron)
                <span class="rv-pill" style="background:var(--green-dim);color:var(--green)">Scheduled</span>
            @endif
        </div>
    </div>

    <div class="rv-actions">
        <button class="rv-btn rv-btn-ghost" wire:click="refresh" wire:loading.attr="disabled" wire:target="refresh" @disabled(! $ready) title="Run again with the latest data">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" wire:loading.class="rv-spinning" wire:target="refresh"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
            Refresh
        </button>
        <button class="rv-btn rv-btn-ghost" wire:click="toggleHistory">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            History
        </button>
        <div class="rv-menu" x-data="{ open:false }" @click.outside="open = false" @keydown.escape.window="open = false">
            <button class="rv-btn rv-btn-ghost" @click="open = !open" @disabled(! $ready)>
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="rv-menu-list" x-show="open" x-cloak x-transition.opacity.duration.100ms>
                {{-- Plain links: the browser downloads the file, no Livewire round trip --}}
                <a class="rv-menu-item" href="{{ $exportUrls['pdf'] }}" @click="open = false">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/></svg>
                    PDF (to print or send)
                </a>
                <a class="rv-menu-item" href="{{ $exportUrls['xlsx'] }}" @click="open = false">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
                    Excel workbook
                </a>
                <a class="rv-menu-item" href="{{ $exportUrls['csv'] }}" @click="open = false">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    CSV file
                </a>
                <div class="rv-menu-note">Exports use the period and location shown.</div>
            </div>
        </div>
        @if ($canEdit)
            <a href="{{ route('owner.reports.custom.edit', $report->id) }}" class="rv-btn rv-btn-primary" wire:navigate>
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit report
            </a>
        @endif
    </div>
</div>

{{-- ═══ Filters ══════════════════════════════════════════════════════════ --}}
<div x-data="{ f:false }">
    <div class="rv-phone-bar m-filter-bar">
        <span class="rv-phone-period m-grow">{{ $periodLabel }} · {{ $locationLabel }}</span>
        <button type="button" class="m-filter-toggle" @click="f = true">Filters</button>
    </div>
    <div class="m-sheet-overlay m-only" x-show="f" x-cloak @click="f = false"></div>

    <div class="rv-filters m-filter-panel" :class="{ open: f }">
        <div class="m-sheet-handle m-only"></div>
        <div class="rv-presets">
            @foreach ($presets as $key => $label)
                <button class="rv-preset {{ $preset === $key ? 'active' : '' }}" wire:click="setPreset('{{ $key }}')">{{ $label }}</button>
            @endforeach
        </div>
        <div class="rv-filter-row">
            <div class="rv-seg">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;color:var(--text-dim)"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
                <input type="date" class="rv-date" wire:model.live="dateFrom" max="{{ $dateTo }}" aria-label="From">
                <span style="font-size:13px;color:var(--text-dim)">→</span>
                <input type="date" class="rv-date" wire:model.live="dateTo" min="{{ $dateFrom }}" aria-label="To">
            </div>
            <div class="rv-seg rv-seg-grow">
                <span class="rv-seg-label">Location</span>
                <select class="rv-select" wire:model.live="location">
                    <option value="all">All locations</option>
                    @if ($shops->isNotEmpty())
                        <optgroup label="Shops">
                            @foreach ($shops as $id => $name)
                                <option value="shop:{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                    @if ($warehouses->isNotEmpty())
                        <optgroup label="Warehouses">
                            @foreach ($warehouses as $id => $name)
                                <option value="warehouse:{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>
            <div class="rv-seg rv-seg-grow">
                <span class="rv-seg-label">Compare</span>
                <select class="rv-select" wire:model.live="comparison">
                    @foreach (\App\Services\Reports\ReportPeriod::COMPARISONS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @unless ($this->isDefaultView)
                <div class="rv-seg">
                    <button class="rv-reset" wire:click="resetFilters">Reset to saved view</button>
                </div>
            @endunless
        </div>
        <div class="m-sheet-foot m-only"><button type="button" class="rv-btn rv-btn-primary" style="flex:1;justify-content:center" @click="f = false">Done</button></div>
    </div>
</div>

<div class="rv-context">
    {{-- period and location are already in the phone filter bar --}}
    <strong class="m-hide">{{ $periodLabel }}</strong>
    <span class="rv-dot m-hide"></span>
    <span class="m-hide">{{ $locationLabel }}</span>
    @if ($comparisonLabel)
        <span class="rv-dot m-hide"></span>
        <span>Compared with {{ $comparisonLabel }}</span>
    @endif
    <span wire:loading.delay wire:target="setPreset,dateFrom,dateTo,location,comparison,refresh,resetFilters,applyHistoryRun,load" style="display:none;color:var(--accent);font-weight:600">· Updating…</span>
</div>

{{-- ═══ Results ══════════════════════════════════════════════════════════ --}}
<div class="rv-body" wire:loading.class="rv-busy" wire:target="setPreset,dateFrom,dateTo,location,comparison,refresh,resetFilters,applyHistoryRun">

@if (! $ready)
    <div class="ui-kpis m-kpis">
        @for ($i = 0; $i < 4; $i++) <div class="rv-skel"></div> @endfor
    </div>
    <div class="rv-grid">
        <div class="rv-skel" style="height:260px"></div>
        <div class="rv-skel" style="height:260px"></div>
    </div>
@elseif ($blockCount === 0)
    <div class="rv-placeholder">
        <div class="rv-placeholder-title">This report has no blocks yet</div>
        <div class="rv-placeholder-sub">
            @if ($canEdit)
                <a href="{{ route('owner.reports.custom.edit', $report->id) }}" wire:navigate style="color:var(--accent);font-weight:600">Edit the report</a> to add figures, tables and charts.
            @else
                Ask {{ $report->creator?->name ?? 'its owner' }} to add some.
            @endif
        </div>
    </div>
@else

    {{-- Key findings --}}
    @if (count($findings) > 0)
        <div class="rv-findings">
            <div class="rv-findings-head">Key findings</div>
            <div class="rv-findings-grid">
                @foreach ($findings as $f)
                    <div class="rv-finding" style="border-left-color:var(--{{ $toneColor[$f['tone']] }})">
                        <div class="rv-finding-title">{{ $f['title'] }}</div>
                        <div class="rv-finding-text">{{ $f['text'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Summary cards: every KPI block --}}
    @if (count($kpis) > 0)
        <div class="ui-kpis m-kpis rv-kpis">
            @foreach ($kpis as $id => $entry)
                @php
                    $r      = $entry['result'];
                    $meta   = $entry['meta'];
                    [$col, $icon] = $domainIcon[$meta['domain'] ?? 'sales'] ?? $domainIcon['sales'];
                    $h      = $r['headline'];
                    $cmp    = $r['comparison'];
                    $status = $r['status'] ?? null;
                    $statusColor = ['ok' => 'green', 'warn' => 'amber', 'crit' => 'red'][$status] ?? null;
                    $valueColor  = $h && is_numeric($h['value']) && $h['value'] < 0 ? 'var(--red)' : 'var(--text)';
                    $hasDetails  = ! empty(app(\App\Services\Reports\MetricRegistry::class)->metric($meta['id'] ?? '')?->related());
                @endphp
                <div class="ui-kpi" wire:key="kpi-{{ $id }}">
                    <div class="ui-kpi-row">
                        <div class="ui-kpi-icon" style="background:var(--{{ $col }}-dim);color:var(--{{ $col }})">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">{!! $icon !!}</svg>
                        </div>
                        <div class="ui-kpi-body">
                            <div class="ui-kpi-label">
                                {{ $entry['block']['title'] ?? $meta['label'] }}
                                @if (! empty($r['notes']))
                                    <span class="rv-kpi-info" title="{{ implode(' ', $r['notes']) }}">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                    </span>
                                @endif
                            </div>
                            <div class="ui-kpi-sub">
                                @if ($cmp && $cmp['value'] !== null)
                                    {{ $cmp['period'] }}: {{ F::withUnit($cmp['value'], $h['type']) }}
                                @else
                                    {{ $h['label'] ?? '' }}
                                @endif
                            </div>
                        </div>
                        @if ($cmp && $cmp['pct'] !== null)
                            @php $bc = $cmp['good'] === true ? 'green' : ($cmp['good'] === false ? 'red' : null); @endphp
                            <span class="ui-kpi-badge" style="{{ $bc ? "background:var(--{$bc}-dim);color:var(--{$bc})" : 'background:var(--surface2);color:var(--text-dim)' }}">
                                {{ $cmp['pct'] >= 0 ? '▲' : '▼' }} {{ number_format(abs($cmp['pct']), 1) }}%
                            </span>
                        @elseif ($statusColor)
                            <span class="rv-kpi-status" style="background:var(--{{ $statusColor }})" title="{{ ['ok' => 'Within your threshold', 'warn' => 'Past your warning threshold', 'crit' => 'Past your critical threshold'][$status] }}"></span>
                        @endif
                    </div>

                    @if ($r['error'])
                        <div class="rv-kpi-error">{{ $r['error'] }}</div>
                    @else
                        @php
                            $shown = F::value($h['value'], $h['type']);
                        @endphp
                        <div class="ui-kpi-val" style="color:{{ $statusColor && $status !== 'ok' ? "var(--{$statusColor})" : $valueColor }}">{{ $shown }}@if ($h['type'] === 'money')<span class="ui-kpi-unit">RWF</span>@endif</div>

                        @if (! empty($r['stats']))
                            <div class="ui-kpi-divider"></div>
                            <div class="ui-kpi-footer">
                                @foreach (array_slice($r['stats'], 0, 3) as $s)
                                    <div class="ui-kpi-stat">
                                        <span class="ui-kpi-stat-v">{{ F::withUnit($s['value'], $s['type']) }}</span>
                                        <span class="ui-kpi-stat-l">{{ $s['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if ($hasDetails || ! empty($r['insight']))
                            <button class="rv-kpi-more" wire:click="openDetails('{{ $id }}')">Details</button>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Tables, charts and text --}}
    @if (count($blocks) > 0)
        <div class="rv-grid">
            @foreach ($blocks as $id => $entry)
                @php
                    $block = $entry['block'];
                    $r     = $entry['result'];
                    $viz   = $block['viz'] ?? 'table';
                    $full  = ($block['width'] ?? 'half') === 'full';
                    $isChart = in_array($viz, ['bar_chart', 'line_chart'], true) && ! empty($r['series']['labels']);
                @endphp

                @if (($block['metric_id'] ?? '') === 'text_block')
                    @continue (trim($block['content'] ?? '') === '' && ($block['title'] ?? $defaultTextTitle) === $defaultTextTitle)
                    <div class="rv-card rv-full" wire:key="blk-{{ $id }}">
                        @if (($block['title'] ?? '') !== '' && $block['title'] !== $defaultTextTitle)
                            <div class="rv-card-head"><h2 class="rv-card-title">{{ $block['title'] }}</h2></div>
                        @endif
                        <div class="rv-text">{{ $block['content'] ?? '' }}</div>
                    </div>
                    @continue
                @endif

                <div class="rv-card {{ $full ? 'rv-full' : '' }}" wire:key="blk-{{ $id }}">
                    <div class="rv-card-head">
                        <div style="min-width:0">
                            <h2 class="rv-card-title">{{ $block['title'] ?? $entry['meta']['label'] }}</h2>
                            @if (! empty($block['date_range_override']) || ! empty($block['location_filter_override']))
                                <div class="rv-card-sub">Has its own
                                    {{ collect([! empty($block['date_range_override']) ? 'period' : null, ! empty($block['location_filter_override']) ? 'location' : null])->filter()->join(' and ') }}
                                </div>
                            @else
                                <div class="rv-card-sub">{{ $entry['meta']['description'] ?? '' }}</div>
                            @endif
                        </div>
                        @if ($r && ! $r['error'] && $r['headline'] && ! empty($r['columns']))
                            <div class="rv-card-total">
                                {{ F::withUnit($r['headline']['value'], $r['headline']['type']) }}
                                <small>
                                    {{ $r['headline']['label'] }}
                                    @if ($r['comparison'] && $r['comparison']['pct'] !== null)
                                        @php $cc = $r['comparison']['good'] === true ? 'green' : ($r['comparison']['good'] === false ? 'red' : 'text-dim'); @endphp
                                        · <span style="color:var(--{{ $cc }})">{{ $r['comparison']['pct'] >= 0 ? '▲' : '▼' }} {{ number_format(abs($r['comparison']['pct']), 1) }}%</span>
                                    @endif
                                </small>
                            </div>
                        @endif
                    </div>

                    <div class="rv-card-body">
                        @if (! $r || $r['error'])
                            <div class="rv-error">{{ $r['error'] ?? "Couldn't load this block." }}</div>
                        @elseif ($isChart)
                            <div class="rv-chart" wire:key="chart-{{ $id }}-{{ md5(json_encode($r['series'])) }}"
                                 data-chart="{{ json_encode(['type' => $viz === 'line_chart' ? 'line' : 'bar', 'labels' => $r['series']['labels'], 'datasets' => $r['series']['datasets'], 'dateLabels' => ($r['columns'][0]['type'] ?? '') === 'date']) }}">
                                <canvas wire:ignore></canvas>
                            </div>
                        @elseif (in_array($viz, ['bar_chart', 'line_chart'], true))
                            <div class="rv-empty">Nothing to chart for this period.</div>
                        @else
                            @include('livewire.owner.reports.partials.result-table', ['r' => $r])
                        @endif
                    </div>

                    @if ($r && ! $r['error'] && (! empty($r['insight']) || ! empty($r['notes'])))
                        <div class="rv-card-foot">
                            @if (! empty($r['insight']))
                                <div class="rv-insight">
                                    <span class="rv-insight-dot" style="background:var(--{{ $toneColor[$r['insight']['tone']] ?? 'accent' }})"></span>
                                    <span>{{ $r['insight']['text'] }}</span>
                                </div>
                            @endif
                            @foreach ($r['notes'] as $note)
                                <div class="rv-note">{{ $note }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
@endif
</div>

{{-- ═══ Details sheet ════════════════════════════════════════════════════ --}}
@if ($detailBlockId !== '' && isset($kpis[$detailBlockId]))
    @php
        $entry = $kpis[$detailBlockId];
        $r = $entry['result'];
    @endphp
    <div class="m-sheet-overlay" wire:click="closeDetails"></div>
    <div class="m-sheet" role="dialog" aria-modal="true" @keydown.escape.window="$wire.closeDetails()">
        <div class="m-sheet-handle"></div>
        <div class="m-sheet-head">
            <h2 class="m-sheet-title">{{ $entry['block']['title'] ?? $entry['meta']['label'] }}</h2>
            <button class="rv-drawer-close m-tap" wire:click="closeDetails" aria-label="Close">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="m-sheet-body">
            <div class="rv-detail-kpi">
                <strong style="font-family:var(--mono);font-size:18px;color:var(--text)">{{ F::withUnit($r['headline']['value'] ?? null, $r['headline']['type'] ?? 'count') }}</strong>
                · {{ $periodLabel }} · {{ $locationLabel }}
                @if (! empty($r['insight']))<br>{{ $r['insight']['text'] }}@endif
                @foreach ($r['notes'] as $note)<br><span class="rv-note">{{ $note }}</span>@endforeach
            </div>

            <div class="rv-detail-section">
                <div class="rv-detail-title">All figures</div>
                @include('livewire.owner.reports.partials.result-table', ['r' => array_merge($r, ['columns' => [], 'rows' => []])])
            </div>

            @foreach ($this->details as $d)
                <div class="rv-detail-section">
                    <div class="rv-detail-title">{{ $d['title'] }}@if (count($d['result']['rows']) >= 5) · top 5 @endif</div>
                    @if ($d['result']['error'])
                        <div class="rv-error" style="margin:0">{{ $d['result']['error'] }}</div>
                    @else
                        @include('livewire.owner.reports.partials.result-table', ['r' => $d['result']])
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- ═══ History drawer ═══════════════════════════════════════════════════ --}}
@if ($showHistory)
    <div class="rv-overlay" wire:click="toggleHistory"></div>
    <div class="rv-drawer" role="dialog" aria-modal="true" @keydown.escape.window="$wire.toggleHistory()">
        <div class="rv-drawer-head">
            <div>
                <div class="rv-drawer-title">Run history</div>
                <div style="font-size:12px;color:var(--text-dim);margin-top:2px">The last 12 runs. Choose one to see that view again with today's data.</div>
            </div>
            <button class="rv-drawer-close" wire:click="toggleHistory" aria-label="Close">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="rv-drawer-body">
            @forelse ($this->history as $run)
                @php
                    $cfg = $run->config_snapshot ?? [];
                    [$hf, $ht] = \App\Services\Reports\ReportPeriod::resolve($cfg['date_range'] ?? 'month', $cfg['date_from'] ?? null, $cfg['date_to'] ?? null);
                @endphp
                <button class="rv-run" wire:click="applyHistoryRun({{ $run->id }})" wire:key="run-{{ $run->id }}">
                    <div class="rv-run-top">
                        <span class="rv-run-period">{{ \App\Services\Reports\ReportPeriod::label($hf, $ht) }}</span>
                        <span class="rv-run-when">{{ local_time($run->run_at)->format('j M, H:i') }}</span>
                    </div>
                    <div class="rv-run-meta">
                        {{ \App\Services\Reports\ReportContext::locationLabel($cfg['location_filter'] ?? 'all') }}
                        · {{ $run->was_scheduled ? 'Scheduled run' : ($run->runner?->name ?? 'Unknown') }}
                    </div>
                    @if (! empty($run->summary))
                        <div class="rv-run-figs">
                            @foreach (array_slice($run->summary, 0, 3) as $fig)
                                <span class="rv-run-fig">{{ $fig['title'] }}: <b>{{ F::withUnit($fig['value'], $fig['type']) }}</b></span>
                            @endforeach
                        </div>
                    @endif
                </button>
            @empty
                <div class="rv-empty">No runs yet.</div>
            @endforelse
        </div>
    </div>
@endif

@script
<script>
(function () {
    const css = () => getComputedStyle(document.documentElement);
    const palette = () => ['--accent', '--violet', '--green', '--amber', '--red'].map(v => css().getPropertyValue(v).trim() || '#3b6fd4');
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const shortDate = s => { const p = String(s).split('-'); return p.length === 3 ? (+p[2]) + ' ' + months[+p[1] - 1] : s; };
    const compact = v => {
        const a = Math.abs(v);
        if (a >= 1e9) return (v / 1e9).toFixed(1).replace(/\.0$/, '') + 'B';
        if (a >= 1e6) return (v / 1e6).toFixed(1).replace(/\.0$/, '') + 'M';
        if (a >= 1e3) return (v / 1e3).toFixed(0) + 'k';
        return String(Math.round(v * 10) / 10);
    };
    const full = (v, type) => {
        if (type === 'percent') return Number(v).toFixed(1) + '%';
        const n = Number(v).toLocaleString('en-US', { maximumFractionDigits: 1 });
        return type === 'money' ? n + ' RWF' : n;
    };

    function draw(wrap) {
        const canvas = wrap.querySelector('canvas');
        if (!canvas || typeof Chart === 'undefined') return;
        if (canvas._chart) { canvas._chart.destroy(); canvas._chart = null; }

        const spec = JSON.parse(wrap.dataset.chart || '{}');
        const labels = (spec.labels || []).map(l => spec.dateLabels ? shortDate(l) : l);
        const colors = palette();
        const isLine = spec.type === 'line';
        // Long category lists read better as horizontal bars
        const horizontal = !isLine && (labels.length > 6 || labels.some(l => String(l).length > 14));
        const type = (spec.datasets[0] || {}).type || 'count';

        const w = wrap.clientWidth - 28, h = horizontal ? Math.max(220, labels.length * 26 + 40) : wrap.clientHeight - 20;
        if (horizontal) wrap.style.height = (h + 20) + 'px';
        canvas.width = w; canvas.height = h;
        canvas.style.width = w + 'px'; canvas.style.height = h + 'px';

        const grid = css().getPropertyValue('--border').trim() || '#e2e6f3';
        const dim = css().getPropertyValue('--text-dim').trim() || '#7a81a0';
        const valueAxis = { beginAtZero: true, grid: { color: grid }, border: { display: false },
                            ticks: { color: dim, font: { size: 11 }, callback: v => type === 'percent' ? v + '%' : compact(v) } };
        const catAxis = { grid: { display: false }, border: { display: false },
                          ticks: { color: dim, font: { size: 11 }, autoSkip: true, maxRotation: 0,
                                   callback: function (v) { const l = String(this.getLabelForValue(v)); return l.length > 22 ? l.slice(0, 21) + '…' : l; } } };

        canvas._chart = new Chart(canvas, {
            type: isLine ? 'line' : 'bar',
            data: {
                labels,
                datasets: spec.datasets.map((d, i) => ({
                    label: d.label, data: d.data,
                    backgroundColor: isLine ? colors[i % colors.length] + '1a' : colors[i % colors.length],
                    borderColor: colors[i % colors.length],
                    borderWidth: isLine ? 2 : 0, borderRadius: isLine ? 0 : 4, maxBarThickness: 36,
                    fill: isLine, tension: .3, pointRadius: isLine && labels.length <= 31 ? 2.5 : 0,
                })),
            },
            options: {
                responsive: false, animation: false, indexAxis: horizontal ? 'y' : 'x',
                plugins: {
                    legend: { display: spec.datasets.length > 1 },
                    tooltip: { mode: 'index', intersect: false,
                               callbacks: { label: c => ' ' + c.dataset.label + ': ' + full(c.raw, spec.datasets[c.datasetIndex].type) } },
                },
                scales: horizontal ? { x: valueAxis, y: catAxis } : { x: catAxis, y: valueAxis },
            },
        });
    }

    let pending = false;
    function drawAll() {
        if (pending) return;
        pending = true;
        requestAnimationFrame(() => requestAnimationFrame(() => {
            pending = false;
            $wire.$el.querySelectorAll('.rv-chart').forEach(draw);
        }));
    }

    drawAll();
    Livewire.hook('commit', ({ component, succeed }) => {
        if (component.id !== $wire.$id) return;
        succeed(drawAll);
    });
    let resizeTimer;
    window.addEventListener('resize', () => { clearTimeout(resizeTimer); resizeTimer = setTimeout(drawAll, 200); });
})();
</script>
@endscript
</div>
