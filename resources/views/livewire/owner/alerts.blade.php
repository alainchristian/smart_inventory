<div style="font-family:var(--font)">
<style>
/* ── Tabs ─────────────────────────────────────────── */
.alp-tabs { display:flex;gap:4px;margin-bottom:16px;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:4px;width:fit-content }
.alp-tab { padding:7px 16px;border-radius:9px;border:none;background:transparent;font-size:12px;font-weight:600;color:var(--text-sub);cursor:pointer;font-family:var(--font);transition:all var(--tr);white-space:nowrap;display:flex;align-items:center;gap:6px }
.alp-tab.active { background:var(--accent);color:#fff }
.alp-tab:not(.active):hover { background:var(--surface2);color:var(--text) }
.alp-tab-count { display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;padding:0 5px;border-radius:9px;font-size:10px;font-weight:700;background:rgba(255,255,255,.25);color:inherit }
.alp-tab:not(.active) .alp-tab-count { background:var(--border);color:var(--text-dim) }

/* ── Controls ─────────────────────────────────────── */
.alp-controls { display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:14px }
.alp-search-wrap { flex:1;min-width:200px;position:relative }
.alp-search-icon { position:absolute;left:11px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--text-dim);pointer-events:none }
.alp-search { width:100%;padding:9px 11px 9px 33px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;background:var(--surface);color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.alp-search:focus { border-color:var(--accent) }
.alp-select { padding:8px 12px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;background:var(--surface);color:var(--text);outline:none;cursor:pointer;font-family:var(--font);transition:border-color var(--tr) }
.alp-select:focus { border-color:var(--accent) }
.alp-clear-btn { padding:8px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:12px;font-weight:600;background:transparent;color:var(--text-sub);cursor:pointer;font-family:var(--font);transition:all var(--tr);white-space:nowrap }
.alp-clear-btn:hover { border-color:var(--accent);color:var(--accent) }
.alp-resolve-all { padding:8px 14px;border:none;border-radius:10px;font-size:12px;font-weight:600;background:var(--green-dim,rgba(34,197,94,.12));color:var(--green,#16a34a);cursor:pointer;font-family:var(--font);transition:all var(--tr);white-space:nowrap }
.alp-resolve-all:hover { background:var(--green,#16a34a);color:#fff }

/* ── Count bar ────────────────────────────────────── */
.alp-count-bar { display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;font-size:12px;color:var(--text-dim) }
.alp-count-num { font-family:var(--mono);font-weight:700;color:var(--text-sub) }

/* ── Card list ────────────────────────────────────── */
.alp-list { display:flex;flex-direction:column;gap:8px }
.alp-card { background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:14px 16px;display:flex;gap:12px;align-items:flex-start;transition:box-shadow var(--tr) }
.alp-card:hover { box-shadow:0 2px 8px rgba(0,0,0,.06) }
.alp-card.sev-critical { border-left:3px solid var(--danger,#dc2626) }
.alp-card.sev-warning  { border-left:3px solid var(--amber,#d97706) }
.alp-card.sev-info     { border-left:3px solid var(--accent) }

/* ── Severity icon ────────────────────────────────── */
.alp-icon { width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0 }
.alp-icon.critical { background:rgba(220,38,38,.12);color:var(--danger,#dc2626) }
.alp-icon.warning  { background:rgba(217,119,6,.12);color:var(--amber,#d97706) }
.alp-icon.info     { background:var(--accent-dim);color:var(--accent) }

/* ── Card body ────────────────────────────────────── */
.alp-body { flex:1;min-width:0 }
.alp-title { font-size:13.5px;font-weight:700;color:var(--text);margin:0 0 3px }
.alp-msg { font-size:12.5px;color:var(--text-sub);margin:0 0 6px;line-height:1.5 }
.alp-meta { display:flex;align-items:center;gap:10px;flex-wrap:wrap }
.alp-sev { display:inline-flex;align-items:center;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;letter-spacing:.4px }
.alp-sev.critical { background:rgba(220,38,38,.12);color:var(--danger,#dc2626) }
.alp-sev.warning  { background:rgba(217,119,6,.12);color:var(--amber,#d97706) }
.alp-sev.info     { background:var(--accent-dim);color:var(--accent) }
.alp-time { font-family:var(--mono);font-size:11px;color:var(--text-dim) }
.alp-entity { font-size:11px;color:var(--text-dim);background:var(--surface2);padding:2px 7px;border-radius:5px }

/* ── Card actions ─────────────────────────────────── */
.alp-actions { display:flex;flex-direction:column;gap:6px;flex-shrink:0 }
.alp-btn-resolve { padding:5px 12px;border:none;border-radius:8px;font-size:11px;font-weight:600;background:rgba(34,197,94,.12);color:var(--green,#16a34a);cursor:pointer;font-family:var(--font);transition:all var(--tr);white-space:nowrap }
.alp-btn-resolve:hover { background:var(--green,#16a34a);color:#fff }
.alp-btn-dismiss { padding:5px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:11px;font-weight:600;background:transparent;color:var(--text-sub);cursor:pointer;font-family:var(--font);transition:all var(--tr);white-space:nowrap }
.alp-btn-dismiss:hover { border-color:var(--text-dim);color:var(--text) }

/* ── Status pills (resolved/dismissed) ───────────────*/
.alp-resolved-pill { display:inline-flex;align-items:center;gap:5px;padding:4px 10px;background:rgba(34,197,94,.10);color:var(--green,#16a34a);border-radius:20px;font-size:11px;font-weight:600 }
.alp-dismissed-pill { display:inline-flex;align-items:center;gap:5px;padding:4px 10px;background:var(--surface2);color:var(--text-dim);border-radius:20px;font-size:11px;font-weight:600 }

/* ── Empty ────────────────────────────────────────── */
.alp-empty { text-align:center;padding:64px 24px;color:var(--text-dim) }
.alp-empty svg { width:48px;height:48px;margin:0 auto 12px;opacity:.2 }
.alp-empty p { margin:0;font-size:13px }
.alp-empty p + p { margin-top:4px;font-size:12px }
</style>

{{-- Status tabs --}}
<div class="alp-tabs">
    <button wire:click="$set('filterStatus','unresolved')" class="alp-tab {{ $filterStatus === 'unresolved' ? 'active' : '' }}">
        Active
        <span class="alp-tab-count">{{ $counts['unresolved'] }}</span>
    </button>
    <button wire:click="$set('filterStatus','resolved')" class="alp-tab {{ $filterStatus === 'resolved' ? 'active' : '' }}">
        Resolved
        <span class="alp-tab-count">{{ $counts['resolved'] }}</span>
    </button>
    <button wire:click="$set('filterStatus','dismissed')" class="alp-tab {{ $filterStatus === 'dismissed' ? 'active' : '' }}">
        Dismissed
        <span class="alp-tab-count">{{ $counts['dismissed'] }}</span>
    </button>
    <button wire:click="$set('filterStatus','all')" class="alp-tab {{ $filterStatus === 'all' ? 'active' : '' }}">
        All
    </button>
</div>

{{-- Controls --}}
<div class="alp-controls">
    <div class="alp-search-wrap">
        <svg class="alp-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
        </svg>
        <input wire:model.live.debounce.300ms="search" class="alp-search" type="text" placeholder="Search alerts…">
    </div>

    <select wire:model.live="filterSeverity" class="alp-select">
        <option value="">All severities</option>
        <option value="critical">Critical</option>
        <option value="warning">Warning</option>
        <option value="info">Info</option>
    </select>

    @if ($filterStatus === 'unresolved' && $counts['unresolved'] > 0)
        <button wire:click="resolveAll" wire:confirm="Mark all active alerts as resolved?" class="alp-resolve-all">
            Resolve All
        </button>
    @endif

    @if ($search !== '' || $filterSeverity !== '')
        <button wire:click="clearFilters" class="alp-clear-btn">Clear filters</button>
    @endif
</div>

{{-- Count bar --}}
<div class="alp-count-bar">
    <span><span class="alp-count-num">{{ $alerts->total() }}</span> {{ Str::plural('alert', $alerts->total()) }}</span>
    @if ($alerts->hasPages())
        <span>Page {{ $alerts->currentPage() }} of {{ $alerts->lastPage() }}</span>
    @endif
</div>

{{-- Alert list --}}
<div class="alp-list">
    @forelse ($alerts as $alert)
        @php
            $sev = $alert->severity->value;
            $iconPath = match(true) {
                $sev === 'critical' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                $sev === 'warning'  => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                default             => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            };
        @endphp

        <div class="alp-card sev-{{ $sev }}">
            {{-- Icon --}}
            <div class="alp-icon {{ $sev }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/>
                </svg>
            </div>

            {{-- Body --}}
            <div class="alp-body">
                <p class="alp-title">{{ $alert->title }}</p>
                <p class="alp-msg">{{ $alert->message }}</p>
                <div class="alp-meta">
                    <span class="alp-sev {{ $sev }}">{{ strtoupper($sev) }}</span>
                    <span class="alp-time">{{ $alert->created_at->diffForHumans() }}</span>
                    @if ($alert->entity_type)
                        <span class="alp-entity">{{ $alert->entity_type }}</span>
                    @endif
                    @if ($alert->is_resolved)
                        <span class="alp-resolved-pill">
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Resolved {{ $alert->resolved_at?->diffForHumans() }}
                        </span>
                    @elseif ($alert->is_dismissed)
                        <span class="alp-dismissed-pill">Dismissed</span>
                    @endif
                    @if ($alert->action_url && $alert->action_label)
                        <a href="{{ $alert->action_url }}" style="font-size:11px;color:var(--accent);font-weight:600;text-decoration:none" wire:navigate>
                            {{ $alert->action_label }} →
                        </a>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            @if (!$alert->is_resolved && !$alert->is_dismissed)
                <div class="alp-actions">
                    <button wire:click="resolve({{ $alert->id }})" class="alp-btn-resolve">Resolve</button>
                    <button wire:click="dismiss({{ $alert->id }})" class="alp-btn-dismiss">Dismiss</button>
                </div>
            @endif
        </div>
    @empty
        <div class="alp-empty">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p>No alerts found</p>
            <p>{{ $filterStatus === 'unresolved' ? 'All clear — no active alerts.' : 'Nothing matches your filters.' }}</p>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
@if ($alerts->hasPages())
    <div style="margin-top:16px">
        {{ $alerts->links() }}
    </div>
@endif

</div>
