{{-- ┌─────────────────────────────────────────────────────────────────────────┐
    │  Owner · Custom Reports Library                                         │
    │  List, manage, duplicate, delete saved custom reports                   │
    └─────────────────────────────────────────────────────────────────────────┘ --}}
<div>
<style>
.rl-page-title { font-size:24px;font-weight:700;color:var(--text);letter-spacing:-0.5px;margin:0 0 4px }
.rl-page-subtitle { font-size:13px;color:var(--text-dim) }
.rl-card { background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:18px 20px;display:flex;flex-direction:column;gap:10px;transition:border-color .15s }
.rl-card:hover { border-color:var(--accent-dim) }
.rl-report-name { font-size:15px;font-weight:700;color:var(--text);margin:0 }
.rl-report-desc { font-size:13px;color:var(--text-sub);margin:0 }
.rl-chip { display:inline-flex;align-items:center;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.3px }
.rl-meta { font-size:12px;color:var(--text-dim) }
.rl-actions { display:flex;align-items:center;gap:8px;margin-top:4px;flex-wrap:wrap }
.rl-btn-primary { display:inline-flex;align-items:center;gap:6px;padding:7px 16px;background:var(--accent);color:#fff;border-radius:var(--rsm);font-size:13px;font-weight:700;text-decoration:none;border:none;cursor:pointer }
.rl-btn-primary:hover { opacity:.9 }
.rl-btn-ghost { display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:transparent;color:var(--text-sub);border:1px solid var(--border);border-radius:var(--rsm);font-size:13px;cursor:pointer }
.rl-btn-ghost:hover { background:var(--surface2);color:var(--text) }
.rl-btn-danger { display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:transparent;color:var(--red);border:1px solid var(--red-dim);border-radius:var(--rsm);font-size:13px;cursor:pointer }
.rl-btn-danger:hover { background:var(--danger-glow) }
.rl-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px }
.rl-filter-bar { display:flex;align-items:center;gap:10px;margin-bottom:20px;flex-wrap:wrap }
.rl-search { flex:1;min-width:200px;padding:8px 12px;background:var(--surface);border:1px solid var(--border);border-radius:var(--rsm);font-size:13px;color:var(--text) }
.rl-search:focus { outline:none;border-color:var(--accent) }
.rl-tab-filter { display:flex;gap:4px }
.rl-tab-filter button { padding:7px 14px;border-radius:var(--rsm);font-size:13px;cursor:pointer;border:1px solid var(--border);background:var(--surface);color:var(--text-sub) }
.rl-tab-filter button.active { background:var(--accent-dim);color:var(--accent);border-color:var(--accent-dim);font-weight:700 }
.rl-empty { text-align:center;padding:60px 20px }
.rl-empty-icon { width:56px;height:56px;margin:0 auto 16px;color:var(--text-dim);opacity:.4 }
.rl-empty-title { font-size:16px;font-weight:700;color:var(--text);margin:0 0 6px }
.rl-empty-sub { font-size:13px;color:var(--text-dim);margin:0 0 20px }
@@media(max-width:640px) {
    .rl-filter-bar { flex-direction:column;align-items:stretch }
    .rl-tab-filter { flex-wrap:wrap }
}
</style>

{{-- ══════════════════════════════════════════════════════════════════════════
     PAGE HEADER
══════════════════════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:24px;flex-wrap:wrap">
    <div>
        <h1 class="rl-page-title">Custom Reports</h1>
        <p class="rl-page-subtitle">Build, save, and re-run your own report combinations</p>
    </div>
    <a href="{{ route('owner.reports.custom.builder') }}" class="rl-btn-primary" wire:navigate>
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Report
    </a>
</div>

@if (session('success'))
<div style="padding:10px 16px;background:var(--success-glow);border:1px solid var(--success);border-radius:var(--rsm);font-size:13px;color:var(--success);margin-bottom:16px">
    {{ session('success') }}
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     FILTER BAR
══════════════════════════════════════════════════════════════════════════ --}}
<div class="rl-filter-bar">
    <input type="text" class="rl-search" placeholder="Search reports…" wire:model.live.debounce.300ms="search">
    <div class="rl-tab-filter">
        <button wire:click="$set('filter','all')" class="{{ $filter === 'all' ? 'active' : '' }}">All</button>
        <button wire:click="$set('filter','mine')" class="{{ $filter === 'mine' ? 'active' : '' }}">Mine</button>
        <button wire:click="$set('filter','shared')" class="{{ $filter === 'shared' ? 'active' : '' }}">Shared</button>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     REPORT GRID
══════════════════════════════════════════════════════════════════════════ --}}
@if ($reports->isEmpty())
<div class="rl-empty">
    <svg class="rl-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
    </svg>
    @if ($search || $filter !== 'all')
    <p class="rl-empty-title">No reports match your filters</p>
    <p class="rl-empty-sub">Try changing your search or filter</p>
    <button wire:click="$set('search','')" class="rl-btn-ghost">Clear filters</button>
    @else
    <p class="rl-empty-title">No reports yet</p>
    <p class="rl-empty-sub">Build your first custom report to get started</p>
    <a href="{{ route('owner.reports.custom.builder') }}" class="rl-btn-primary" wire:navigate>New Report</a>
    @endif
</div>
@else
<div class="rl-grid">
    @foreach ($reports as $report)
    <div class="rl-card">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
            <div style="flex:1;min-width:0">
                <p class="rl-report-name">{{ $report->name }}</p>
                @if ($report->description)
                <p class="rl-report-desc" style="margin-top:4px">{{ $report->description }}</p>
                @endif
            </div>
            @if ($report->is_shared)
            <span class="rl-chip" style="background:var(--accent-dim);color:var(--accent);white-space:nowrap;flex-shrink:0">Shared</span>
            @endif
        </div>

        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <span class="rl-chip" style="background:var(--surface2);color:var(--text-sub)">
                {{ $report->blockCount() }} {{ Str::plural('block', $report->blockCount()) }}
            </span>
            @php
                $cfg = $report->resolvedConfig();
                $rangeLabels = ['today'=>'Today','week'=>'This week','month'=>'This month','quarter'=>'This quarter','year'=>'This year','custom'=>'Custom range'];
            @endphp
            <span class="rl-chip" style="background:var(--surface2);color:var(--text-sub)">
                {{ $rangeLabels[$cfg['date_range']] ?? $cfg['date_range'] }}
            </span>
            @if ($report->schedule_cron)
            <span class="rl-chip" style="background:var(--violet-dim,#ede9fe);color:var(--violet,#7c3aed)" title="Scheduled: {{ $report->schedule_cron }}">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right:3px"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Scheduled
            </span>
            @endif
            @if ($report->pinned_to_dashboard)
            <span class="rl-chip" style="background:var(--amber-dim,#fef3c7);color:var(--amber,#d97706)" title="Pinned to dashboard">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right:3px"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                Pinned
            </span>
            @endif
        </div>

        <div class="rl-meta" style="display:flex;gap:14px;flex-wrap:wrap">
            <span>By {{ $report->creator->name ?? 'Unknown' }}</span>
            @if ($report->last_run_at)
            <span>Last run {{ $report->last_run_at->diffForHumans() }}</span>
            <span>{{ $report->run_count }} {{ Str::plural('run', $report->run_count) }}</span>
            @else
            <span style="color:var(--amber)">Never run</span>
            @endif
            @if (($report->view_logs_count ?? 0) > 0)
            <span>{{ $report->view_logs_count }} {{ Str::plural('view', $report->view_logs_count) }}</span>
            @endif
            <span>Created {{ $report->created_at->format('d M Y') }}</span>
        </div>

        <div class="rl-actions">
            <a href="{{ route('owner.reports.custom.view', $report->id) }}" class="rl-btn-primary" wire:navigate>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                Run
            </a>
            @if ($report->created_by === auth()->id())
            <a href="{{ route('owner.reports.custom.builder') }}?reportId={{ $report->id }}" class="rl-btn-ghost" wire:navigate>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
            </a>
            <button wire:click="duplicateReport({{ $report->id }})" class="rl-btn-ghost" title="Duplicate">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                Duplicate
            </button>
            <button wire:click="deleteReport({{ $report->id }})"
                    wire:confirm="Delete '{{ addslashes($report->name) }}'? This cannot be undone."
                    class="rl-btn-danger" title="Delete">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                Delete
            </button>
            @endif
        </div>
    </div>
    @endforeach
</div>

<div style="margin-top:20px">
    {{ $reports->links() }}
</div>
@endif

</div>
