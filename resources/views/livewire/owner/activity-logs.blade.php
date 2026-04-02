<div style="font-family:var(--font)">
<style>
/* ── Controls ────────────────────────────────────── */
.al-controls { display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:14px }
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

/* ── Filter chips ─────────────────────────────────── */
.al-chips { display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px }
.al-chip { display:inline-flex;align-items:center;gap:6px;padding:4px 10px 4px 12px;background:var(--surface2);border:1px solid var(--border);border-radius:20px;font-size:11px;font-weight:600;color:var(--text-sub) }
.al-chip-x { width:14px;height:14px;border-radius:50%;background:var(--border);color:var(--text-dim);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:10px;line-height:1;padding:0;font-family:var(--font) }
.al-chip-x:hover { background:var(--amber-dim);color:var(--amber) }

/* ── Count bar ────────────────────────────────────── */
.al-count-bar { display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;font-size:12px;color:var(--text-dim) }
.al-count-num { font-family:var(--mono);font-weight:700;color:var(--text-sub) }

/* ── Table ────────────────────────────────────────── */
.al-wrap { background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden }
.al-table { width:100%;border-collapse:collapse;font-size:13px }
.al-table thead tr { background:var(--bg);border-bottom:1px solid var(--border) }
.al-table thead th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.al-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.al-table tbody tr:last-child { border-bottom:none }
.al-table tbody tr.al-row:hover { background:var(--surface2);cursor:pointer }
.al-table td { padding:11px 16px;vertical-align:middle }
.al-table tr.al-expanded-row { background:var(--bg);border-bottom:2px solid var(--border) }
.al-table tr.al-expanded-row td { padding:0 }

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

/* ── Context cell ───────────────────────────────────*/
.al-ref  { font-family:var(--mono);font-size:12px;font-weight:700;color:var(--text) }
.al-ctx  { font-size:11px;color:var(--text-dim);margin-top:2px }

/* ── IP ─────────────────────────────────────────────*/
.al-ip { font-family:var(--mono);font-size:11px;color:var(--text-dim) }

/* ── Expand toggle ──────────────────────────────────*/
.al-expand-btn { width:26px;height:26px;border-radius:7px;border:1.5px solid var(--border);background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all var(--tr);color:var(--text-dim);padding:0 }
.al-expand-btn:hover { border-color:var(--accent);color:var(--accent) }
.al-expand-btn.open { background:var(--surface2);border-color:var(--accent);color:var(--accent) }

/* ── Expanded detail panel ──────────────────────────*/
.al-detail { padding:16px 20px 20px;display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px }
.al-detail-block { background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:12px 14px }
.al-detail-title { font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--text-dim);margin-bottom:8px }
.al-detail-row { display:flex;justify-content:space-between;align-items:baseline;gap:8px;padding:3px 0;border-bottom:1px solid var(--border) }
.al-detail-row:last-child { border-bottom:none }
.al-detail-key { font-size:11px;color:var(--text-sub);flex-shrink:0;max-width:45% }
.al-detail-val { font-size:11px;font-family:var(--mono);color:var(--text);word-break:break-all;text-align:right }
.al-detail-val.old { color:var(--amber);text-decoration:line-through }
.al-detail-val.new { color:var(--green) }

/* ── Pagination ─────────────────────────────────────*/
.al-paginate { display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:var(--bg);border-top:1px solid var(--border);font-size:12px;color:var(--text-dim);gap:10px;flex-wrap:wrap }
.al-paginate-info { font-family:var(--mono) }

/* ── Empty ──────────────────────────────────────────*/
.al-empty { padding:60px 20px;text-align:center }
.al-empty-icon { font-size:32px;margin-bottom:10px }
.al-empty-title { font-size:14px;font-weight:700;color:var(--text-sub) }
.al-empty-sub { font-size:12px;color:var(--text-dim);margin-top:4px }

@media (max-width:860px) {
    .al-controls { gap:7px }
    .al-select,.al-date { font-size:12px;padding:7px 9px }
    .al-table .al-col-ip,.al-table .al-col-entity { display:none }
}
</style>

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

    {{-- Action filter --}}
    <select wire:model.live="filterAction" class="al-select">
        <option value="">All actions</option>
        @foreach($this->distinctActions as $a)
            <option value="{{ $a['value'] }}">{{ $a['label'] }}</option>
        @endforeach
    </select>

    {{-- Entity type filter --}}
    <select wire:model.live="filterEntity" class="al-select">
        <option value="">All types</option>
        @foreach($this->distinctEntities as $e)
            <option value="{{ $e }}">{{ $e }}</option>
        @endforeach
    </select>

    {{-- Date from --}}
    <input wire:model.live="dateFrom" type="date" class="al-date" title="From date">

    {{-- Date to --}}
    <input wire:model.live="dateTo" type="date" class="al-date" title="To date">

    @if($this->hasActiveFilters)
    <button wire:click="clearFilters" class="al-clear-btn">
        ✕ Clear
    </button>
    @endif
</div>

{{-- ── Active filter chips ──────────────────────────────────────── --}}
@if($this->hasActiveFilters)
<div class="al-chips">
    @if($search !== '')
    <span class="al-chip">
        "{{ $search }}"
        <button class="al-chip-x" wire:click="$set('search','')">✕</button>
    </span>
    @endif
    @if($filterUser !== '')
    <span class="al-chip">
        User: {{ collect($this->distinctUsers)->firstWhere('id', (int)$filterUser)['name'] ?? $filterUser }}
        <button class="al-chip-x" wire:click="$set('filterUser','')">✕</button>
    </span>
    @endif
    @if($filterAction !== '')
    <span class="al-chip">
        Action: {{ ucfirst(str_replace('_',' ',$filterAction)) }}
        <button class="al-chip-x" wire:click="$set('filterAction','')">✕</button>
    </span>
    @endif
    @if($filterEntity !== '')
    <span class="al-chip">
        Type: {{ $filterEntity }}
        <button class="al-chip-x" wire:click="$set('filterEntity','')">✕</button>
    </span>
    @endif
    @if($dateFrom !== '')
    <span class="al-chip">
        From: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}
        <button class="al-chip-x" wire:click="$set('dateFrom','')">✕</button>
    </span>
    @endif
    @if($dateTo !== '')
    <span class="al-chip">
        To: {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
        <button class="al-chip-x" wire:click="$set('dateTo','')">✕</button>
    </span>
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
    <span wire:loading wire:target="search,filterUser,filterAction,filterEntity,dateFrom,dateTo"
          style="display:none;font-size:11px;color:var(--text-dim)">Loading…</span>
</div>

{{-- ── Table ─────────────────────────────────────────────────────── --}}
<div class="al-wrap">
    <table class="al-table">
        <thead>
            <tr>
                <th style="width:140px">Time</th>
                <th style="width:140px">User</th>
                <th style="width:200px">Action</th>
                <th>Reference / Context</th>
                <th class="al-col-entity" style="width:110px">Type</th>
                <th class="al-col-ip" style="width:110px">IP</th>
                <th style="width:38px"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                @php
                    $parsed  = $this->parseAction($log);
                    $context = $this->buildContext($log);
                    $isExp   = $expandedId === $log->id;
                    $changes = $log->getChanges();
                    $details = $log->details ?? [];
                    $hasDetail = !empty($details) || !empty($changes) || $log->old_values || $log->new_values;
                @endphp

                {{-- Main row --}}
                <tr class="al-row {{ $isExp ? 'al-expanded-active' : '' }}"
                    wire:click="toggleExpand({{ $log->id }})"
                    style="{{ $isExp ? 'background:var(--surface2)' : '' }}">

                    {{-- Time --}}
                    <td>
                        <div class="al-time">{{ $log->created_at->format('d M Y') }}</div>
                        <div class="al-time-rel">{{ $log->created_at->format('H:i:s') }}</div>
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
                            {{-- Icon --}}
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

                    {{-- Expand caret --}}
                    <td style="text-align:center">
                        @if($hasDetail)
                        <button class="al-expand-btn {{ $isExp ? 'open' : '' }}"
                                wire:click.stop="toggleExpand({{ $log->id }})"
                                title="{{ $isExp ? 'Collapse' : 'Expand' }}">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2.5"
                                 style="transform:{{ $isExp ? 'rotate(180deg)' : 'none' }};transition:transform .15s">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </button>
                        @endif
                    </td>
                </tr>

                {{-- Expanded detail row --}}
                @if($isExp && $hasDetail)
                <tr class="al-expanded-row">
                    <td colspan="7">
                        <div class="al-detail">

                            {{-- Details JSON --}}
                            @if(!empty($details))
                            <div class="al-detail-block">
                                <div class="al-detail-title">Details</div>
                                @foreach($details as $k => $v)
                                <div class="al-detail-row">
                                    <span class="al-detail-key">{{ ucfirst(str_replace('_',' ',$k)) }}</span>
                                    <span class="al-detail-val">
                                        @if(is_array($v))
                                            {{ json_encode($v) }}
                                        @elseif(is_bool($v))
                                            {{ $v ? 'Yes' : 'No' }}
                                        @elseif(is_numeric($v) && str_contains(strtolower($k), 'total') || str_contains(strtolower($k), 'amount') || str_contains(strtolower($k), 'price'))
                                            {{ number_format($v) }} RWF
                                        @else
                                            {{ $v }}
                                        @endif
                                    </span>
                                </div>
                                @endforeach
                            </div>
                            @endif

                            {{-- Field changes (old → new) --}}
                            @if(!empty($changes))
                            <div class="al-detail-block">
                                <div class="al-detail-title">Changes</div>
                                @foreach($changes as $field => $diff)
                                <div class="al-detail-row" style="flex-wrap:wrap;gap:4px">
                                    <span class="al-detail-key" style="width:100%;max-width:100%;font-weight:700">{{ ucfirst(str_replace('_',' ',$field)) }}</span>
                                    <span class="al-detail-val old" style="flex:1;text-align:left">{{ is_array($diff['old']) ? json_encode($diff['old']) : ($diff['old'] ?? '—') }}</span>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--text-dim)" stroke-width="2" style="flex-shrink:0"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                    <span class="al-detail-val new" style="flex:1;text-align:right">{{ is_array($diff['new']) ? json_encode($diff['new']) : ($diff['new'] ?? '—') }}</span>
                                </div>
                                @endforeach
                            </div>
                            @endif

                            {{-- Raw new_values if no diff summary --}}
                            @if(!empty($log->new_values) && empty($changes))
                            <div class="al-detail-block">
                                <div class="al-detail-title">New values</div>
                                @foreach($log->new_values as $k => $v)
                                <div class="al-detail-row">
                                    <span class="al-detail-key">{{ ucfirst(str_replace('_',' ',$k)) }}</span>
                                    <span class="al-detail-val">{{ is_array($v) ? json_encode($v) : $v }}</span>
                                </div>
                                @endforeach
                            </div>
                            @endif

                            {{-- Meta (timestamp, entity id) --}}
                            <div class="al-detail-block">
                                <div class="al-detail-title">Meta</div>
                                <div class="al-detail-row">
                                    <span class="al-detail-key">Log ID</span>
                                    <span class="al-detail-val">#{{ $log->id }}</span>
                                </div>
                                <div class="al-detail-row">
                                    <span class="al-detail-key">Timestamp</span>
                                    <span class="al-detail-val">{{ $log->created_at->format('d M Y H:i:s') }}</span>
                                </div>
                                @if($log->entity_id)
                                <div class="al-detail-row">
                                    <span class="al-detail-key">Entity ID</span>
                                    <span class="al-detail-val">{{ $log->entity_type }} #{{ $log->entity_id }}</span>
                                </div>
                                @endif
                                @if($log->ip_address)
                                <div class="al-detail-row">
                                    <span class="al-detail-key">IP address</span>
                                    <span class="al-detail-val">{{ $log->ip_address }}</span>
                                </div>
                                @endif
                                @if($log->user_agent)
                                <div class="al-detail-row">
                                    <span class="al-detail-key">User agent</span>
                                    <span class="al-detail-val" style="font-size:10px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $log->user_agent }}">
                                        {{ Str::limit($log->user_agent, 60) }}
                                    </span>
                                </div>
                                @endif
                            </div>

                        </div>
                    </td>
                </tr>
                @endif

            @empty
                <tr>
                    <td colspan="7">
                        <div class="al-empty">
                            <div class="al-empty-icon">📋</div>
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

    {{-- ── Pagination ───────────────────────────────────────────── --}}
    @if($logs->hasPages())
    <div class="al-paginate">
        <span class="al-paginate-info">
            Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}
            &nbsp;·&nbsp; {{ number_format($logs->total()) }} total entries
        </span>
        <div>{{ $logs->links() }}</div>
    </div>
    @endif
</div>

</div>
