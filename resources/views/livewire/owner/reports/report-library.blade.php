<div style="font-family:var(--font)">
<style>
/* ── Report Library (rl-) ─────────────────────────────────────── */
.rl-header       { display:flex;align-items:flex-start;justify-content:space-between;
                   gap:12px;margin-bottom:24px;flex-wrap:wrap }
.rl-title        { font-size:22px;font-weight:800;color:var(--text);letter-spacing:-.5px }
.rl-sub          { font-size:13px;color:var(--text-dim);margin-top:3px }
.rl-btn-new      { display:inline-flex;align-items:center;gap:6px;padding:9px 18px;
                   background:var(--accent);color:#fff;border-radius:9px;
                   font-size:13px;font-weight:700;text-decoration:none;
                   transition:opacity var(--tr);white-space:nowrap;flex-shrink:0 }
.rl-btn-new:hover { opacity:.88 }

/* Flash */
.rl-flash        { padding:11px 16px;background:var(--green-dim);color:var(--green);
                   border-radius:9px;border-left:3px solid var(--green);
                   font-size:13px;font-weight:600;margin-bottom:16px }

/* Section label */
.rl-section-label { font-size:10.5px;font-weight:700;text-transform:uppercase;
                    letter-spacing:.6px;color:var(--text-dim);margin-bottom:10px }

/* Templates strip */
.rl-templates-strip    { display:flex;gap:12px;overflow-x:auto;padding-bottom:12px;
                          margin-bottom:28px;scrollbar-width:none }
.rl-templates-strip::-webkit-scrollbar { display:none }
.rl-template-card      { flex-shrink:0;width:240px;background:var(--surface);
                          border-radius:var(--r);box-shadow:var(--shadow-card);
                          border-left:4px solid var(--accent);overflow:hidden;
                          text-decoration:none;display:flex;flex-direction:column;
                          transition:box-shadow var(--tr),transform var(--tr) }
.rl-template-card:hover { box-shadow:var(--shadow-card-hover);transform:translateY(-2px) }
.rl-template-top       { display:flex;align-items:flex-start;gap:10px;padding:14px 14px 10px }
.rl-template-icon      { width:36px;height:36px;border-radius:9px;flex-shrink:0;
                          display:flex;align-items:center;justify-content:center }
.rl-template-body      { flex:1;min-width:0 }
.rl-template-name      { font-size:12px;font-weight:700;color:var(--text);
                          white-space:nowrap;overflow:hidden;text-overflow:ellipsis }
.rl-template-desc      { font-size:11px;color:var(--text-dim);margin-top:2px;
                          display:-webkit-box;-webkit-line-clamp:2;
                          -webkit-box-orient:vertical;overflow:hidden }
.rl-template-chips     { display:flex;flex-wrap:wrap;gap:4px;padding:0 14px 12px }
.rl-template-chip      { font-size:10px;font-weight:600;padding:2px 7px;
                          border-radius:5px;background:var(--surface2);color:var(--text-dim);
                          white-space:nowrap }
.rl-template-chip-count { background:var(--accent-dim);color:var(--accent) }
.rl-template-cta       { font-size:11.5px;font-weight:700;color:var(--accent);
                          padding:8px 14px 12px;margin-top:auto;
                          display:flex;align-items:center;gap:4px }

/* Filter bar */
.rl-filter-bar   { display:flex;align-items:center;justify-content:space-between;
                   gap:12px;margin-bottom:16px;flex-wrap:wrap }
.rl-filter-left  { display:flex;align-items:center;gap:10px;flex-wrap:wrap }
.rl-search-wrap  { position:relative;display:flex;align-items:center }
.rl-search-icon  { position:absolute;left:10px;color:var(--text-dim);pointer-events:none }
.rl-search       { padding:7px 12px 7px 32px;border:1.5px solid var(--border);
                   border-radius:9px;font-size:13px;background:var(--surface);
                   color:var(--text);outline:none;font-family:var(--font);
                   transition:border-color var(--tr);width:220px }
.rl-search:focus { border-color:var(--accent) }
.rl-filter-tabs  { display:flex;gap:2px;background:var(--surface2);
                   border-radius:8px;padding:3px }
.rl-filter-tab   { padding:5px 12px;border:none;border-radius:6px;cursor:pointer;
                   font-size:12px;font-weight:600;font-family:var(--font);
                   background:transparent;color:var(--text-dim);transition:all var(--tr) }
.rl-filter-tab.active { background:var(--surface);color:var(--accent);
                         box-shadow:0 1px 4px rgba(0,0,0,.08) }

/* Sort bar */
.rl-sort-bar     { display:flex;gap:2px;background:var(--surface2);
                   border-radius:8px;padding:3px }
.rl-sort-btn     { padding:5px 12px;border:none;border-radius:6px;cursor:pointer;
                   font-size:11.5px;font-weight:600;font-family:var(--font);
                   background:transparent;color:var(--text-dim);
                   transition:all var(--tr);white-space:nowrap }
.rl-sort-btn.active { background:var(--surface);color:var(--accent);
                       box-shadow:0 1px 4px rgba(0,0,0,.08) }

/* Report grid */
.rl-report-grid  { display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
                   gap:14px;margin-bottom:24px }
.rl-report-card  { background:var(--surface);border-radius:var(--r);
                   box-shadow:var(--shadow-card);overflow:hidden;position:relative;
                   display:flex;flex-direction:column;
                   transition:box-shadow var(--tr) }
.rl-report-card:hover { box-shadow:var(--shadow-card-hover) }
.rl-card-accent  { position:absolute;left:0;top:0;bottom:0;width:4px;
                   background:var(--card-accent, var(--accent)) }
.rl-card-body    { padding:16px 16px 14px 20px;display:flex;flex-direction:column;flex:1 }
.rl-card-top     { display:flex;align-items:flex-start;gap:8px;margin-bottom:8px }
.rl-card-name    { flex:1;font-size:14px;font-weight:700;color:var(--text);
                   line-height:1.3;min-width:0;word-break:break-word }
.rl-card-desc    { font-size:12px;color:var(--text-dim);margin-bottom:10px;
                   display:-webkit-box;-webkit-line-clamp:2;
                   -webkit-box-orient:vertical;overflow:hidden }

/* Block type icons */
.rl-block-icons  { display:flex;align-items:center;gap:6px;margin-bottom:10px;flex-wrap:wrap }
.rl-block-icon   { display:inline-flex;align-items:center;gap:3px;font-size:11px;
                   font-weight:600;color:var(--text-dim);
                   background:var(--surface2);padding:2px 7px;border-radius:5px }

/* Chips */
.rl-chips        { display:flex;flex-wrap:wrap;gap:5px;margin-bottom:12px }
.rl-chip         { font-size:10.5px;font-weight:600;padding:2px 8px;border-radius:5px;
                   background:var(--surface2);color:var(--text-dim) }
.rl-chip-accent  { background:var(--accent-dim);color:var(--accent) }
.rl-chip-green   { background:var(--green-dim);color:var(--green) }
.rl-chip-amber   { background:var(--amber-dim);color:var(--amber) }

/* Card footer */
.rl-card-footer  { display:flex;align-items:center;justify-content:space-between;
                   margin-top:auto;padding-top:10px;border-top:1px solid var(--border) }
.rl-card-meta    { font-size:11px;color:var(--text-dim) }

/* Action menu */
.rl-action-trigger  { width:28px;height:28px;border:none;border-radius:6px;
                      background:transparent;color:var(--text-dim);cursor:pointer;
                      display:flex;align-items:center;justify-content:center;
                      flex-shrink:0;transition:background var(--tr) }
.rl-action-trigger:hover { background:var(--surface2);color:var(--text) }
.rl-action-wrap     { position:relative;flex-shrink:0 }
.rl-action-dropdown { position:absolute;right:0;top:32px;z-index:200;
                      background:var(--surface);border-radius:10px;
                      box-shadow:0 4px 20px rgba(0,0,0,.14);
                      min-width:160px;padding:5px;overflow:hidden }
.rl-action-item     { display:flex;align-items:center;gap:8px;width:100%;padding:8px 12px;
                      border:none;border-radius:6px;background:transparent;
                      color:var(--text-sub);font-size:12.5px;font-weight:600;
                      font-family:var(--font);cursor:pointer;text-decoration:none;
                      transition:background var(--tr) }
.rl-action-item:hover  { background:var(--surface2) }
.rl-action-item.danger { color:var(--red) }
.rl-action-item.danger:hover { background:var(--red-dim) }
.rl-action-sep      { height:1px;background:var(--border);margin:4px 0 }

/* Empty state */
.rl-empty        { padding:64px 20px;text-align:center }
.rl-empty-icon   { width:56px;height:56px;border-radius:16px;background:var(--surface2);
                   display:flex;align-items:center;justify-content:center;
                   margin:0 auto 16px;color:var(--text-dim) }
.rl-empty-title  { font-size:16px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.rl-empty-sub    { font-size:13px;color:var(--text-dim);margin-bottom:20px }
.rl-empty-cta    { display:inline-flex;align-items:center;gap:6px;padding:10px 20px;
                   background:var(--accent);color:#fff;border-radius:9px;
                   font-size:13px;font-weight:700;text-decoration:none;
                   transition:opacity var(--tr) }
.rl-empty-cta:hover { opacity:.88 }

@media(max-width:640px) {
    .rl-filter-bar  { flex-direction:column;align-items:stretch }
    .rl-filter-left { flex-direction:column }
    .rl-search      { width:100% }
    .rl-sort-bar    { overflow-x:auto;scrollbar-width:none }
    .rl-sort-bar::-webkit-scrollbar { display:none }
}
</style>

@if(session('success'))
<div class="rl-flash">{{ session('success') }}</div>
@endif

{{-- Page Header --}}
<div class="rl-header">
    <div>
        <div class="rl-title">Report Library</div>
        <div class="rl-sub">Custom analytics reports for your business</div>
    </div>
    <a href="{{ route('owner.reports.custom.builder') }}" class="rl-btn-new">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        New Report
    </a>
</div>

{{-- Quick-Start Templates --}}
<div class="rl-section-label">Quick-Start Templates</div>
<div class="rl-templates-strip">
    @foreach($tmplList as $t)
    <a href="{{ route('owner.reports.custom.builder') }}?template={{ $t['key'] }}"
       class="rl-template-card"
       style="border-left-color:var({{ $t['color'] }})">
        <div class="rl-template-top">
            <div class="rl-template-icon"
                 style="background:var({{ $t['color'] }}-dim);color:var({{ $t['color'] }})">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    {!! $t['icon'] !!}
                </svg>
            </div>
            <div class="rl-template-body">
                <div class="rl-template-name">{{ $t['name'] }}</div>
                <div class="rl-template-desc">{{ $t['description'] }}</div>
            </div>
        </div>
        <div class="rl-template-chips">
            @foreach($t['metrics_preview'] as $m)
                <span class="rl-template-chip">{{ $m }}</span>
            @endforeach
            <span class="rl-template-chip rl-template-chip-count">{{ $t['block_count'] }} blocks</span>
        </div>
        <div class="rl-template-cta">
            Use template
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </div>
    </a>
    @endforeach
</div>

{{-- Filter + Sort Bar --}}
<div class="rl-filter-bar">
    <div class="rl-filter-left">
        <div class="rl-search-wrap">
            <svg class="rl-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" class="rl-search" placeholder="Search reports…">
        </div>
        <div class="rl-filter-tabs">
            @foreach(['all' => 'All', 'mine' => 'Mine', 'shared' => 'Shared'] as $key => $label)
                <button class="rl-filter-tab {{ $filter === $key ? 'active' : '' }}"
                        wire:click="$set('filter', '{{ $key }}')">{{ $label }}</button>
            @endforeach
        </div>
    </div>
    <div class="rl-sort-bar">
        @foreach(['last_run' => 'Recent', 'run_count' => 'Most Run', 'alpha' => 'A–Z', 'created' => 'Newest'] as $key => $label)
            <button class="rl-sort-btn {{ $sortBy === $key ? 'active' : '' }}"
                    wire:click="$set('sortBy', '{{ $key }}')">{{ $label }}</button>
        @endforeach
    </div>
</div>

@php
    $metricReg    = collect(app(\App\Services\Reports\MetricRegistry::class)->catalogue())->keyBy('id');
    $domainColors = [
        'sales'         => 'accent',
        'inventory'     => 'green',
        'replenishment' => 'amber',
        'loss'          => 'red',
        'transfers'     => 'violet',
        'operations'    => 'text-sub',
        'finance'       => 'green',
        'content'       => 'text-dim',
    ];
    $vizLabels = ['kpi_card' => 'KPI', 'table' => 'Table', 'bar_chart' => 'Bar', 'line_chart' => 'Line'];
@endphp

{{-- Report Grid --}}
@if($reports->total() > 0)
<div class="rl-report-grid">
    @foreach($reports as $report)
        @php
            $blocks    = collect($report->config['blocks'] ?? []);
            $topDomain = $blocks
                ->map(fn ($b) => $metricReg[$b['metric_id']]['domain'] ?? null)
                ->filter()
                ->groupBy(fn ($d) => $d)
                ->sortByDesc->count()
                ->keys()->first() ?? 'sales';
            $cardColor = $domainColors[$topDomain] ?? 'accent';
            $vizCounts = $blocks->countBy('viz');
            $isOwner   = $report->created_by === auth()->id();
        @endphp
        <div class="rl-report-card"
             style="--card-accent:var(--{{ $cardColor }})"
             x-data="{ menuOpen: false }">

            <div class="rl-card-accent"></div>

            <div class="rl-card-body">
                <div class="rl-card-top">
                    <div class="rl-card-name">{{ $report->name }}</div>
                    <div class="rl-action-wrap">
                        <button class="rl-action-trigger" @click.stop="menuOpen = !menuOpen">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                            </svg>
                        </button>
                        <div class="rl-action-dropdown"
                             x-show="menuOpen"
                             @click.outside="menuOpen = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100">
                            <a href="{{ route('owner.reports.custom.view', $report->id) }}" class="rl-action-item">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                Run Report
                            </a>
                            @if($isOwner)
                                <a href="{{ route('owner.reports.custom.builder') }}?reportId={{ $report->id }}" class="rl-action-item">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit
                                </a>
                                <button class="rl-action-item" wire:click="duplicateReport({{ $report->id }})">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                                    Duplicate
                                </button>
                                <button class="rl-action-item" wire:click="toggleShare({{ $report->id }})">
                                    @if($report->is_shared)
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                        Make Private
                                    @else
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                                        Share with all owners
                                    @endif
                                </button>
                                <div class="rl-action-sep"></div>
                                <button class="rl-action-item danger"
                                        wire:click="deleteReport({{ $report->id }})"
                                        wire:confirm="Delete '{{ addslashes($report->name) }}'? This cannot be undone.">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                                    Delete
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                @if($report->description)
                    <div class="rl-card-desc">{{ $report->description }}</div>
                @endif

                {{-- Block type mini icons --}}
                @if($blocks->count())
                <div class="rl-block-icons">
                    @foreach($vizLabels as $vizKey => $vizLabel)
                        @if($vizCounts->get($vizKey))
                        <span class="rl-block-icon">
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                @if($vizKey === 'kpi_card')
                                    <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>
                                @elseif($vizKey === 'table')
                                    <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
                                @elseif($vizKey === 'bar_chart')
                                    <rect x="18" y="3" width="4" height="18" rx="1"/><rect x="10" y="8" width="4" height="13" rx="1"/><rect x="2" y="13" width="4" height="8" rx="1"/>
                                @elseif($vizKey === 'line_chart')
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                                @endif
                            </svg>
                            {{ $vizCounts->get($vizKey) }} {{ $vizLabel }}
                        </span>
                        @endif
                    @endforeach
                </div>
                @endif

                {{-- Chips --}}
                <div class="rl-chips">
                    <span class="rl-chip">{{ $report->blockCount() }} blocks</span>
                    <span class="rl-chip">{{ ucfirst(str_replace('_', ' ', $report->config['date_range'] ?? 'custom')) }}</span>
                    @if($report->is_shared)
                        <span class="rl-chip rl-chip-accent">Shared</span>
                    @endif
                    @if($report->schedule_cron)
                        <span class="rl-chip rl-chip-green">Scheduled</span>
                    @endif
                    @if(! $report->last_run_at)
                        <span class="rl-chip rl-chip-amber">Never run</span>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="rl-card-footer">
                    <span class="rl-card-meta">by {{ $report->creator->name ?? 'Unknown' }}</span>
                    <span class="rl-card-meta">
                        @if($report->last_run_at)
                            {{ $report->last_run_at->diffForHumans() }}@if($report->run_count > 1) &middot; {{ number_format($report->run_count) }}&times;@endif
                        @else
                            Not yet run
                        @endif
                    </span>
                </div>
            </div>
        </div>
    @endforeach
</div>
{{ $reports->links() }}

@else
<div class="rl-empty">
    <div class="rl-empty-icon">
        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/>
        </svg>
    </div>
    @if($search)
        <div class="rl-empty-title">No results for "{{ $search }}"</div>
        <div class="rl-empty-sub">Try a different search term or clear the filter</div>
    @elseif($filter === 'shared')
        <div class="rl-empty-title">No shared reports yet</div>
        <div class="rl-empty-sub">Reports you share will appear here for all owners</div>
    @else
        <div class="rl-empty-title">No reports yet</div>
        <div class="rl-empty-sub">Create your first custom report and start tracking what matters most</div>
        <a href="{{ route('owner.reports.custom.builder') }}" class="rl-empty-cta">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Create First Report
        </a>
    @endif
</div>
@endif

</div>
