<div style="font-family:var(--font)">
<style>
/* ── Controls ────────────────────────────────────── */
.al-controls { display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:10px }
.al-search-wrap { flex:1;min-width:200px;position:relative }
.al-search-icon { position:absolute;left:11px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--text-dim);pointer-events:none }
.al-search { width:100%;padding:9px 11px 9px 33px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;background:var(--surface);color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.al-search:focus { border-color:var(--accent) }
.al-select { padding:8px 12px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;background:var(--surface);color:var(--text);outline:none;cursor:pointer;font-family:var(--font);transition:border-color var(--tr) }
.al-select:focus { border-color:var(--accent) }
.al-date { padding:8px 11px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;background:var(--surface);color:var(--text);outline:none;font-family:var(--font);transition:border-color var(--tr) }
.al-date:focus { border-color:var(--accent) }
.al-clear-btn { padding:8px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:12px;font-weight:600;background:transparent;color:var(--text-sub);cursor:pointer;font-family:var(--font);transition:all var(--tr);white-space:nowrap }
.al-clear-btn:hover { border-color:var(--accent);color:var(--accent) }
.al-export-btn { display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:none;border-radius:10px;font-size:12px;font-weight:700;background:var(--accent);color:#fff;cursor:pointer;font-family:var(--font);transition:opacity var(--tr);white-space:nowrap;box-shadow:0 3px 10px rgba(59,111,212,.25) }
.al-export-btn:hover { opacity:.88 }

/* ── Quick range presets ────────────────────────────*/
.al-presets { display:flex;gap:6px;margin-bottom:10px;flex-wrap:wrap }
.al-preset-btn { padding:6px 14px;border-radius:8px;border:1.5px solid var(--border);background:var(--surface);
                 color:var(--text-dim);font-size:12px;font-weight:600;cursor:pointer;font-family:var(--font);
                 transition:all var(--tr);white-space:nowrap }
.al-preset-btn:hover  { border-color:var(--accent);color:var(--accent) }
.al-preset-btn.active { background:var(--accent);border-color:var(--accent);color:#fff }

/* ── Filter chips ─────────────────────────────────── */
.al-chips { display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px }
.al-chip { display:inline-flex;align-items:center;gap:6px;padding:4px 10px 4px 12px;background:var(--surface2);border:1px solid var(--border);border-radius:20px;font-size:11px;font-weight:600;color:var(--text-sub) }
.al-chip-x { width:14px;height:14px;border-radius:50%;background:var(--border);color:var(--text-dim);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:10px;line-height:1;padding:0;font-family:var(--font) }
.al-chip-x:hover { background:var(--amber-dim);color:var(--amber) }

/* ── Count bar ────────────────────────────────────── */
.al-count-bar { display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;font-size:12px;color:var(--text-dim);flex-wrap:wrap;gap:8px }
.al-count-num { font-family:var(--mono);font-weight:700;color:var(--text-sub) }

/* ── Table ────────────────────────────────────────── */
.al-wrap { background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden }
.al-scroll { overflow-x:auto;-webkit-overflow-scrolling:touch }
.al-table { width:100%;border-collapse:collapse;font-size:13px }
.al-table thead tr { background:var(--bg);border-bottom:1px solid var(--border) }
.al-table thead th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.al-th-sort { cursor:pointer;user-select:none;display:inline-flex;align-items:center;gap:4px }
.al-th-sort:hover { color:var(--accent) }
.al-th-sort svg { flex-shrink:0;opacity:.4 }
.al-th-sort.active svg { opacity:1;color:var(--accent) }
.al-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.al-table tbody tr:last-child { border-bottom:none }
.al-table tbody tr.al-row:hover { background:var(--surface2);cursor:pointer }
.al-table tbody tr.al-row.failed { border-left:3px solid var(--red) }
.al-table td { padding:11px 16px;vertical-align:middle }

/* ── Time cell ──────────────────────────────────────*/
.al-time { font-family:var(--mono);font-size:11px;color:var(--text-dim);white-space:nowrap }
.al-time-rel { font-size:11px;color:var(--text-dim);margin-top:1px }

/* ── User cell ──────────────────────────────────────*/
.al-user { display:flex;align-items:center;gap:8px }
.al-avatar { width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0;background:var(--accent) }
.al-username { font-size:12px;font-weight:600;color:var(--text);white-space:nowrap }

/* ── Action badge ───────────────────────────────────*/
.al-badge { display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap }
.al-badge.green  { background:rgba(34,197,94,.12);color:#16a34a }
.al-badge.red    { background:rgba(239,68,68,.12);color:#dc2626 }
.al-badge.amber  { background:rgba(245,158,11,.12);color:#d97706 }
.al-badge.blue   { background:rgba(59,130,246,.12);color:#2563eb }
.al-badge.default{ background:var(--surface2);color:var(--text-sub) }

/* ── Severity dot ────────────────────────────────────*/
.al-sev { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700 }
.al-sev-dot { width:7px;height:7px;border-radius:50% }
.al-sev.info     .al-sev-dot { background:var(--text-dim) }
.al-sev.info     { color:var(--text-dim) }
.al-sev.warning  .al-sev-dot { background:var(--amber) }
.al-sev.warning  { color:var(--amber) }
.al-sev.critical .al-sev-dot { background:var(--red) }
.al-sev.critical { color:var(--red) }

/* ── Module pill ─────────────────────────────────────*/
.al-module { font-size:11px;font-weight:600;color:var(--text-sub);background:var(--surface2);padding:2px 8px;border-radius:6px;white-space:nowrap }

/* ── Context cell ───────────────────────────────────*/
.al-ref  { font-family:var(--mono);font-size:12px;font-weight:700;color:var(--text) }
.al-ctx  { font-size:11px;color:var(--text-dim);margin-top:2px }

/* ── IP ─────────────────────────────────────────────*/
.al-ip { font-family:var(--mono);font-size:11px;color:var(--text-dim) }

/* ── Pagination ─────────────────────────────────────*/
.al-paginate { display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:var(--bg);border-top:1px solid var(--border);font-size:12px;color:var(--text-dim);gap:10px;flex-wrap:wrap }
.al-paginate-info { font-family:var(--mono) }
.al-perpage { display:flex;align-items:center;gap:6px }
.al-perpage select { padding:4px 8px;border:1.5px solid var(--border);border-radius:7px;font-size:12px;background:var(--surface);color:var(--text);font-family:var(--font);cursor:pointer }

/* ── Empty ──────────────────────────────────────────*/
.al-empty { padding:60px 20px;text-align:center }
.al-empty-icon { font-size:32px;margin-bottom:10px }
.al-empty-title { font-size:14px;font-weight:700;color:var(--text-sub) }
.al-empty-sub { font-size:12px;color:var(--text-dim);margin-top:4px }

/* ── Drawer ──────────────────────────────────────────*/
.al-overlay { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);backdrop-filter:blur(2px) }
.al-drawer  { position:fixed;top:0;right:0;bottom:0;z-index:401;
              width:520px;max-width:100vw;background:var(--surface);
              border-left:1px solid var(--border);
              box-shadow:-8px 0 40px rgba(26,31,54,.14);
              display:flex;flex-direction:column;
              transform:translateX(100%);transition:transform .22s cubic-bezier(.4,0,.2,1) }
.al-drawer.open { transform:translateX(0) }
.al-drawer-head  { display:flex;align-items:flex-start;justify-content:space-between;
                   padding:18px 22px;border-bottom:1px solid var(--border);flex-shrink:0;gap:12px }
.al-drawer-title { font-size:15px;font-weight:800;color:var(--text) }
.al-drawer-sub   { font-size:12px;color:var(--text-dim);margin-top:3px;font-family:var(--mono) }
.al-drawer-close { width:32px;height:32px;border-radius:8px;border:none;flex-shrink:0;
                   background:var(--surface2);color:var(--text-sub);cursor:pointer;
                   display:flex;align-items:center;justify-content:center;transition:background var(--tr) }
.al-drawer-close:hover { background:var(--surface3) }
.al-drawer-body { flex:1;overflow-y:auto;padding:20px 22px;display:flex;flex-direction:column;gap:16px }
.al-d-block { background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:14px 16px }
.al-d-title { font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--text-dim);margin-bottom:10px }
.al-d-row { display:flex;justify-content:space-between;align-items:baseline;gap:10px;padding:4px 0;border-bottom:1px solid var(--border) }
.al-d-row:last-child { border-bottom:none }
.al-d-key { font-size:11px;color:var(--text-sub);flex-shrink:0 }
.al-d-val { font-size:11px;font-family:var(--mono);color:var(--text);word-break:break-all;text-align:right }

.al-diff-row { padding:8px 0;border-bottom:1px solid var(--border) }
.al-diff-row:last-child { border-bottom:none }
.al-diff-field { font-size:11px;font-weight:700;color:var(--text-sub);margin-bottom:5px }
.al-diff-cols { display:grid;grid-template-columns:1fr auto 1fr;gap:8px;align-items:center }
.al-diff-old  { font-size:11px;font-family:var(--mono);color:var(--red);background:var(--red-dim);
                padding:5px 8px;border-radius:6px;word-break:break-all;text-decoration:line-through;opacity:.85 }
.al-diff-new  { font-size:11px;font-family:var(--mono);color:var(--green);background:var(--green-dim);
                padding:5px 8px;border-radius:6px;word-break:break-all }

.al-related-link { display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:700;color:var(--accent);
                    background:var(--accent-dim);padding:8px 14px;border-radius:9px;border:none;cursor:pointer;
                    font-family:var(--font);transition:opacity var(--tr) }
.al-related-link:hover { opacity:.8 }

@media (max-width:860px) {
    .al-controls { gap:7px }
    .al-select,.al-date { font-size:12px;padding:7px 9px }
    .al-table .al-col-ip,.al-table .al-col-entity,.al-table .al-col-module { display:none }
    .al-drawer { width:100vw }
}
</style>

{{-- ── Quick date-range presets ─────────────────────────────────────── --}}
<div class="al-presets">
    <button class="al-preset-btn {{ $rangePreset === '24h' ? 'active' : '' }}" wire:click="setRangePreset('24h')">Last 24h</button>
    <button class="al-preset-btn {{ $rangePreset === '7d' ? 'active' : '' }}" wire:click="setRangePreset('7d')">Last 7d</button>
    <button class="al-preset-btn {{ $rangePreset === '30d' ? 'active' : '' }}" wire:click="setRangePreset('30d')">Last 30d</button>
</div>

{{-- ── Controls ─────────────────────────────────────────────────── --}}
<div class="al-controls">
    {{-- Search --}}
    <div class="al-search-wrap">
        <svg class="al-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input wire:model.live.debounce.300ms="search"
               class="al-search"
               type="text"
               placeholder="Search reference, user, action…">
    </div>

    {{-- User filter --}}
    <select wire:model.live="filterUser" class="al-select">
        <option value="">All users</option>
        @foreach($this->distinctUsers as $u)
            <option value="{{ $u['id'] }}">{{ $u['name'] }}</option>
        @endforeach
    </select>

    {{-- Module filter --}}
    <select wire:model.live="filterModule" class="al-select">
        <option value="">All modules</option>
        @foreach($this->distinctModules as $m)
            <option value="{{ $m }}">{{ ucfirst(str_replace('_',' ',$m)) }}</option>
        @endforeach
    </select>

    {{-- Action Type filter --}}
    <select wire:model.live="filterActionType" class="al-select">
        <option value="">All action types</option>
        @foreach(\App\Livewire\Owner\ActivityLogs::actionTypeOptions() as $t)
            <option value="{{ $t['value'] }}">{{ $t['label'] }}</option>
        @endforeach
    </select>

    {{-- Action filter --}}
    <select wire:model.live="filterAction" class="al-select">
        <option value="">All actions</option>
        @foreach($this->distinctActions as $a)
            <option value="{{ $a['value'] }}">{{ $a['label'] }}</option>
        @endforeach
    </select>

    {{-- Severity filter --}}
    <select wire:model.live="filterSeverity" class="al-select">
        <option value="">All severities</option>
        <option value="info">Info</option>
        <option value="warning">Warning</option>
        <option value="critical">Critical</option>
    </select>

    {{-- Status filter --}}
    <select wire:model.live="filterStatus" class="al-select">
        <option value="">All statuses</option>
        <option value="success">Success</option>
        <option value="failed">Failed</option>
    </select>

    {{-- Date from --}}
    <input wire:model.live="dateFrom" type="date" class="al-date" title="From date">

    {{-- Date to --}}
    <input wire:model.live="dateTo" type="date" class="al-date" title="To date">

    <button class="al-export-btn" wire:click="exportCsv">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        Export CSV
    </button>

    @if($this->hasActiveFilters)
    <button wire:click="clearFilters" class="al-clear-btn" style="display:inline-flex;align-items:center;gap:4px">
        <x-icon name="x" size="11" /> Clear
    </button>
    @endif
</div>

{{-- ── Active filter chips ──────────────────────────────────────── --}}
@if($this->hasActiveFilters)
<div class="al-chips">
    @if($search !== '')
    <span class="al-chip">"{{ $search }}" <button class="al-chip-x" wire:click="$set('search','')"><x-icon name="x" size="10" /></button></span>
    @endif
    @if($filterUser !== '')
    <span class="al-chip">User: {{ collect($this->distinctUsers)->firstWhere('id', (int)$filterUser)['name'] ?? $filterUser }} <button class="al-chip-x" wire:click="$set('filterUser','')"><x-icon name="x" size="10" /></button></span>
    @endif
    @if($filterModule !== '')
    <span class="al-chip">Module: {{ ucfirst(str_replace('_',' ',$filterModule)) }} <button class="al-chip-x" wire:click="$set('filterModule','')"><x-icon name="x" size="10" /></button></span>
    @endif
    @if($filterActionType !== '')
    <span class="al-chip">Type: {{ collect(\App\Livewire\Owner\ActivityLogs::actionTypeOptions())->firstWhere('value',$filterActionType)['label'] ?? $filterActionType }} <button class="al-chip-x" wire:click="$set('filterActionType','')"><x-icon name="x" size="10" /></button></span>
    @endif
    @if($filterAction !== '')
    <span class="al-chip">Action: {{ ucfirst(str_replace('_',' ',$filterAction)) }} <button class="al-chip-x" wire:click="$set('filterAction','')"><x-icon name="x" size="10" /></button></span>
    @endif
    @if($filterSeverity !== '')
    <span class="al-chip">Severity: {{ ucfirst($filterSeverity) }} <button class="al-chip-x" wire:click="$set('filterSeverity','')"><x-icon name="x" size="10" /></button></span>
    @endif
    @if($filterStatus !== '')
    <span class="al-chip">Status: {{ ucfirst($filterStatus) }} <button class="al-chip-x" wire:click="$set('filterStatus','')"><x-icon name="x" size="10" /></button></span>
    @endif
    @if($filterEntity !== '')
    <span class="al-chip">Entity: {{ $filterEntity }} <button class="al-chip-x" wire:click="$set('filterEntity','')"><x-icon name="x" size="10" /></button></span>
    @endif
    @if($filterTraceId !== null)
    <span class="al-chip">Related to trace #{{ Str::limit($filterTraceId, 8, '') }} <button class="al-chip-x" wire:click="clearTraceFilter"><x-icon name="x" size="10" /></button></span>
    @endif
    @if($dateFrom !== '')
    <span class="al-chip">From: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} <button class="al-chip-x" wire:click="$set('dateFrom','')"><x-icon name="x" size="10" /></button></span>
    @endif
    @if($dateTo !== '')
    <span class="al-chip">To: {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }} <button class="al-chip-x" wire:click="$set('dateTo','')"><x-icon name="x" size="10" /></button></span>
    @endif
</div>
@endif

{{-- ── Count bar ─────────────────────────────────────────────────── --}}
<div class="al-count-bar">
    <span>
        Showing <span class="al-count-num">{{ number_format($logs->firstItem() ?? 0) }}–{{ number_format($logs->lastItem() ?? 0) }}</span>
        of <span class="al-count-num">{{ number_format($logs->total()) }}</span>
        {{ $logs->total() === 1 ? 'entry' : 'entries' }}
    </span>
    <span style="font-size:11px;color:var(--text-dim)">Logs are immutable and hash-chained — append-only, tamper-evident</span>
</div>

{{-- ── Table ─────────────────────────────────────────────────────── --}}
<div class="al-wrap">
<div class="al-scroll">
    <table class="al-table">
        <thead>
            <tr>
                <th style="width:120px">
                    <span class="al-th-sort {{ $sortBy === 'created_at' ? 'active' : '' }}" wire:click="sortByColumn('created_at')">
                        Time
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            @if($sortBy === 'created_at' && $sortDir === 'asc')<polyline points="18 15 12 9 6 15"/>@else<polyline points="6 9 12 15 18 9"/>@endif
                        </svg>
                    </span>
                </th>
                <th style="width:140px">
                    <span class="al-th-sort {{ $sortBy === 'user_name' ? 'active' : '' }}" wire:click="sortByColumn('user_name')">
                        User
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            @if($sortBy === 'user_name' && $sortDir === 'asc')<polyline points="18 15 12 9 6 15"/>@else<polyline points="6 9 12 15 18 9"/>@endif
                        </svg>
                    </span>
                </th>
                <th style="width:190px">
                    <span class="al-th-sort {{ $sortBy === 'action' ? 'active' : '' }}" wire:click="sortByColumn('action')">
                        Action
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            @if($sortBy === 'action' && $sortDir === 'asc')<polyline points="18 15 12 9 6 15"/>@else<polyline points="6 9 12 15 18 9"/>@endif
                        </svg>
                    </span>
                </th>
                <th>Reference / Context</th>
                <th class="al-col-module" style="width:110px">Module</th>
                <th style="width:90px">Severity</th>
                <th class="al-col-entity" style="width:110px">Type</th>
                <th class="al-col-ip" style="width:110px">IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                @php
                    $parsed  = $this->parseAction($log);
                    $context = $this->buildContext($log);
                    $module  = $log->module ?? $log->entity_type ?? '—';
                @endphp

                <tr class="al-row {{ $log->status === 'failed' ? 'failed' : '' }}"
                    wire:click="openDrawer({{ $log->id }})">

                    {{-- Time --}}
                    <td>
                        <div class="al-time">{{ local_time($log->created_at)->format('d M Y') }}</div>
                        <div class="al-time-rel">{{ local_time($log->created_at)->format('H:i:s') }}</div>
                    </td>

                    {{-- User --}}
                    <td>
                        <div class="al-user">
                            <div class="al-avatar"
                                 style="background:{{ ['#3b82f6','#8b5cf6','#06b6d4','#f59e0b','#10b981'][crc32($log->user_name ?? '') % 5] }}">
                                {{ strtoupper(substr($log->user_name ?? '?', 0, 1)) }}
                            </div>
                            <span class="al-username">{{ $log->user_name ?? 'System' }}</span>
                        </div>
                    </td>

                    {{-- Action badge --}}
                    <td>
                        <span class="al-badge {{ $parsed['color'] }}">
                            @if($parsed['icon'] === 'check')
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            @elseif($parsed['icon'] === 'x')
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            @elseif($parsed['icon'] === 'warning')
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            @elseif($parsed['icon'] === 'transfer')
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
                            @elseif($parsed['icon'] === 'sale')
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                            @elseif($parsed['icon'] === 'box')
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                            @elseif($parsed['icon'] === 'user')
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            @else
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            @endif
                            {{ $parsed['label'] }}
                        </span>
                    </td>

                    {{-- Reference / context --}}
                    <td>
                        @if($log->entity_identifier)
                            <div class="al-ref">{{ $log->entity_identifier }}</div>
                        @endif
                        @if($context && $context !== $log->entity_identifier)
                            <div class="al-ctx">{{ $context }}</div>
                        @endif
                    </td>

                    {{-- Module --}}
                    <td class="al-col-module">
                        <span class="al-module">{{ ucfirst(str_replace('_',' ',$module)) }}</span>
                    </td>

                    {{-- Severity --}}
                    <td>
                        <span class="al-sev {{ $log->severity }}">
                            <span class="al-sev-dot"></span>{{ ucfirst($log->severity) }}
                        </span>
                    </td>

                    {{-- Entity type --}}
                    <td class="al-col-entity">
                        @if($log->entity_type)
                            <span style="font-size:11px;color:var(--text-dim)">{{ $log->entity_type }}</span>
                        @endif
                    </td>

                    {{-- IP --}}
                    <td class="al-col-ip">
                        <span class="al-ip">{{ $log->ip_address ?? '—' }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        <div class="al-empty">
                            <div class="al-empty-icon"><x-icon name="clipboard" size="28" /></div>
                            <div class="al-empty-title">No log entries found</div>
                            <div class="al-empty-sub">
                                @if($this->hasActiveFilters)
                                    Try adjusting your filters or <button wire:click="clearFilters" style="background:none;border:none;color:var(--accent);cursor:pointer;font-family:var(--font);font-size:inherit;padding:0">clear all filters</button>.
                                @else
                                    Activity will appear here as actions are performed in the system.
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

    {{-- ── Pagination ───────────────────────────────────────────── --}}
    <div class="al-paginate">
        <span class="al-paginate-info">
            Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}
            &nbsp;·&nbsp; {{ number_format($logs->total()) }} total entries
        </span>
        <div class="al-perpage">
            <span>Rows per page:</span>
            <select wire:model.live="perPage">
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div>{{ $logs->links() }}</div>
    </div>
</div>

{{-- ── Slide-over drawer ────────────────────────────────────────── --}}
@if($this->selectedLog)
    @php($log = $this->selectedLog)
    @php($parsed = $this->parseAction($log))
    {{-- Built from the persisted old_values/new_values, not getChanges() —
         that method reflects Eloquent's in-memory dirty-tracking for the
         current request, which is always empty for a model freshly loaded
         from a listing query. --}}
    @php($changes = (!empty($log->old_values) && !empty($log->new_values)) ? $log->new_values : [])
    @php($details = $log->details ?? [])

    <div class="al-overlay" wire:click="closeDrawer"></div>
    <div class="al-drawer open">
        <div class="al-drawer-head">
            <div>
                <div class="al-drawer-title">{{ $parsed['label'] }}</div>
                <div class="al-drawer-sub">Log #{{ $log->id }} · {{ local_time($log->created_at)->format('d M Y H:i:s') }}</div>
            </div>
            <button class="al-drawer-close" wire:click="closeDrawer">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="al-drawer-body">

            {{-- Status / severity --}}
            <div class="al-d-block" style="display:flex;gap:16px">
                <span class="al-badge {{ $log->status === 'failed' ? 'red' : 'green' }}">{{ ucfirst($log->status) }}</span>
                <span class="al-sev {{ $log->severity }}"><span class="al-sev-dot"></span>{{ ucfirst($log->severity) }} severity</span>
            </div>

            {{-- Actor --}}
            <div class="al-d-block">
                <div class="al-d-title">Actor</div>
                <div class="al-d-row"><span class="al-d-key">Name</span><span class="al-d-val">{{ $log->user_name ?? 'System' }}</span></div>
                <div class="al-d-row"><span class="al-d-key">Actor type</span><span class="al-d-val">{{ ucfirst($log->actor_type) }}</span></div>
                @if($log->actor_role_snapshot)
                <div class="al-d-row"><span class="al-d-key">Role at time of action</span><span class="al-d-val">{{ ucfirst(str_replace('_',' ',$log->actor_role_snapshot)) }}</span></div>
                @endif
            </div>

            {{-- Request context --}}
            <div class="al-d-block">
                <div class="al-d-title">Request Context</div>
                @if($log->ip_address)
                <div class="al-d-row"><span class="al-d-key">IP address</span><span class="al-d-val">{{ $log->ip_address }}</span></div>
                @endif
                @if($log->session_id)
                <div class="al-d-row"><span class="al-d-key">Session ID</span><span class="al-d-val">{{ Str::limit($log->session_id, 20) }}</span></div>
                @endif
                @if($log->device_fingerprint)
                <div class="al-d-row"><span class="al-d-key">Device fingerprint</span><span class="al-d-val">{{ $log->device_fingerprint }}</span></div>
                @endif
                @if($log->user_agent)
                <div class="al-d-row"><span class="al-d-key">User agent</span><span class="al-d-val" style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $log->user_agent }}">{{ Str::limit($log->user_agent, 50) }}</span></div>
                @endif
            </div>

            {{-- Changes: side-by-side diff --}}
            @if(!empty($changes))
            <div class="al-d-block">
                <div class="al-d-title">Changes</div>
                @foreach($changes as $field => $new)
                    @php($old = $log->old_values[$field] ?? null)
                    <div class="al-diff-row">
                        <div class="al-diff-field">{{ ucfirst(str_replace('_',' ',$field)) }}</div>
                        <div class="al-diff-cols">
                            <div class="al-diff-old">{{ is_array($old) ? json_encode($old) : ($old ?? '—') }}</div>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--text-dim)" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            <div class="al-diff-new">{{ is_array($new) ? json_encode($new) : ($new ?? '—') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
            @elseif(!empty($log->new_values))
            <div class="al-d-block">
                <div class="al-d-title">New values</div>
                @foreach($log->new_values as $k => $v)
                <div class="al-d-row"><span class="al-d-key">{{ ucfirst(str_replace('_',' ',$k)) }}</span><span class="al-d-val">{{ is_array($v) ? json_encode($v) : $v }}</span></div>
                @endforeach
            </div>
            @elseif(!empty($log->old_values))
            <div class="al-d-block">
                <div class="al-d-title">{{ $log->action === 'deleted' ? 'Deleted record values' : 'Previous values' }}</div>
                @foreach($log->old_values as $k => $v)
                <div class="al-d-row"><span class="al-d-key">{{ ucfirst(str_replace('_',' ',$k)) }}</span><span class="al-d-val">{{ is_array($v) ? json_encode($v) : $v }}</span></div>
                @endforeach
            </div>
            @endif

            {{-- Details --}}
            @if(!empty($details))
            <div class="al-d-block">
                <div class="al-d-title">Details</div>
                @foreach($details as $k => $v)
                <div class="al-d-row">
                    <span class="al-d-key">{{ ucfirst(str_replace('_',' ',$k)) }}</span>
                    <span class="al-d-val">
                        @if(is_array($v)){{ json_encode($v) }}
                        @elseif(is_bool($v)){{ $v ? 'Yes' : 'No' }}
                        @elseif(is_numeric($v) && (str_contains(strtolower($k),'total') || str_contains(strtolower($k),'amount') || str_contains(strtolower($k),'price')))
                            {{ number_format($v) }} RWF
                        @else{{ $v }}
                        @endif
                    </span>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Related entries (same trace_id) --}}
            @if($log->trace_id)
            <div class="al-d-block">
                <div class="al-d-title">Related Entries</div>
                <button class="al-related-link" wire:click="viewRelated('{{ $log->trace_id }}')">
                    View {{ $this->relatedLogs->count() }} related {{ $this->relatedLogs->count() === 1 ? 'entry' : 'entries' }} in this action
                </button>
            </div>
            @endif

            {{-- Meta --}}
            <div class="al-d-block">
                <div class="al-d-title">Meta</div>
                <div class="al-d-row"><span class="al-d-key">Log ID</span><span class="al-d-val">#{{ $log->id }}</span></div>
                @if($log->entity_id)
                <div class="al-d-row"><span class="al-d-key">Entity</span><span class="al-d-val">{{ $log->entity_type }} #{{ $log->entity_id }}</span></div>
                @endif
                @if($log->trace_id)
                <div class="al-d-row"><span class="al-d-key">Trace ID</span><span class="al-d-val">{{ $log->trace_id }}</span></div>
                @endif
            </div>

        </div>
    </div>
@endif

</div>
