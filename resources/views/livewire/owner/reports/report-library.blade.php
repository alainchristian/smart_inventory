@php
    $presets = \App\Services\Reports\ReportPeriod::PRESETS;
    $me = auth()->id();
@endphp
<div class="rl-page" style="font-family:var(--font)">
<style>
.rl-page { padding:0 0 80px }
.rl-header { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px;flex-wrap:wrap }
.rl-title  { font-size:22px;font-weight:800;color:var(--text);margin:0 0 4px }
.rl-sub    { font-size:13px;color:var(--text-dim);margin:0;max-width:620px }

.rl-btn { padding:9px 16px;border-radius:var(--rsm);font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font);transition:all var(--tr);
          display:inline-flex;align-items:center;justify-content:center;gap:6px;white-space:nowrap;text-decoration:none }
.rl-btn-primary { background:var(--accent);color:#fff;border:none;box-shadow:0 3px 10px rgba(59,111,212,.25) }
.rl-btn-primary:hover { opacity:.88 }
.rl-btn-ghost { background:var(--surface);color:var(--text-sub);border:1px solid var(--border) }
.rl-btn-ghost:hover { background:var(--surface2);color:var(--text) }

/* Templates */
.rl-card { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);min-width:0 }
.rl-card-head { padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px }
.rl-card-title { font-size:13px;font-weight:700;color:var(--text);margin:0 }
.rl-card-sub { font-size:12px;color:var(--text-dim);margin-top:2px }
.rl-link { font-size:12px;font-weight:600;color:var(--accent);background:none;border:none;cursor:pointer;font-family:var(--font);white-space:nowrap }
.rl-tmpls { display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;padding:14px 18px 16px }
.rl-tmpl { display:flex;gap:10px;align-items:flex-start;padding:11px 12px;border:1.5px solid var(--border);border-radius:10px;text-decoration:none;
           transition:all var(--tr);min-width:0 }
.rl-tmpl:hover { border-color:var(--accent);background:var(--accent-dim) }
.rl-tmpl-icon { width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0 }
.rl-tmpl-name { font-size:13px;font-weight:700;color:var(--text);line-height:1.3 }
.rl-tmpl-desc { font-size:12px;color:var(--text-dim);margin-top:2px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden }

/* Toolbar */
.rl-toolbar { display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin:22px 0 12px }
.rl-tabs { display:flex;gap:4px }
.rl-tab { display:flex;align-items:center;gap:7px;padding:7px 14px;border-radius:9px;border:1.5px solid var(--border);cursor:pointer;font-size:13px;font-weight:600;
          font-family:var(--font);background:var(--surface);color:var(--text-dim);transition:all var(--tr);white-space:nowrap }
.rl-tab:hover { border-color:var(--accent);color:var(--accent) }
.rl-tab.active { background:var(--accent);border-color:var(--accent);color:#fff }
.rl-tab-count { font-size:11px;font-weight:700;padding:1px 7px;border-radius:20px;background:rgba(255,255,255,.22);font-family:var(--mono) }
.rl-tab:not(.active) .rl-tab-count { background:var(--surface2);color:var(--text-dim) }
.rl-search-wrap { flex:1;min-width:200px;position:relative }
.rl-search-icon { position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--text-dim);pointer-events:none }
.rl-search { width:100%;padding:9px 11px 9px 34px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;background:var(--surface);color:var(--text);
             outline:none;box-sizing:border-box;font-family:var(--font) }
.rl-search:focus, .rl-select:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.rl-select { padding:9px 12px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;background:var(--surface);color:var(--text);outline:none;cursor:pointer;font-family:var(--font) }

/* Table */
.rl-scroll { overflow-x:auto;-webkit-overflow-scrolling:touch }
.rl-table { width:100%;border-collapse:collapse;min-width:860px }
.rl-table thead tr { border-bottom:2px solid var(--border) }
.rl-table th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.rl-table td { padding:12px 16px;font-size:13px;color:var(--text-sub);vertical-align:middle;white-space:nowrap }
.rl-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.rl-table tbody tr:last-child { border-bottom:none }
.rl-table tbody tr:hover { background:var(--surface2) }
.rl-name { display:block;font-size:14px;font-weight:700;color:var(--text);text-decoration:none;max-width:360px;overflow:hidden;text-overflow:ellipsis }
.rl-name:hover { color:var(--accent) }
.rl-desc { font-size:12px;color:var(--text-dim);margin-top:2px;max-width:360px;overflow:hidden;text-overflow:ellipsis }
.rl-num { font-family:var(--mono);text-align:right }
.rl-badge { display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;white-space:nowrap }
.rl-dim { color:var(--text-dim) }
.rl-acts { display:flex;gap:4px;justify-content:flex-end;align-items:center }
.rl-action { padding:5px 11px;border-radius:7px;border:1.5px solid var(--border);background:transparent;font-size:12px;font-weight:600;cursor:pointer;
             font-family:var(--font);color:var(--text-sub);transition:all var(--tr);white-space:nowrap;text-decoration:none }
.rl-action:hover { border-color:var(--accent);color:var(--accent) }
.rl-ico-btn { width:30px;height:30px;border-radius:7px;border:none;background:transparent;color:var(--text-dim);cursor:pointer;display:flex;align-items:center;justify-content:center }
.rl-ico-btn:hover { background:var(--surface3);color:var(--text) }
.rl-ico-btn.danger:hover { color:var(--red) }

/* Inline delete confirm (§13) */
.rl-confirm-row { background:rgba(217,119,6,.05) !important }
.rl-confirm { display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;white-space:normal }
.rl-confirm-text { font-size:13px;color:var(--text-sub) }
.rl-confirm-text b { color:var(--text) }
.rl-confirm-yes { padding:6px 16px;background:var(--red);color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;font-family:var(--font) }
.rl-confirm-no  { padding:6px 14px;background:transparent;border:1.5px solid var(--border);color:var(--text-sub);border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;font-family:var(--font) }

.rl-empty { padding:56px 20px;text-align:center }
.rl-empty-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.rl-empty-sub { font-size:13px;color:var(--text-dim);margin-bottom:16px }
.rl-pages { padding:12px 16px;border-top:1px solid var(--border) }

@media (max-width:1100px) { .rl-tmpls { grid-template-columns:repeat(2,minmax(0,1fr)) } }
@media (max-width:640px) {
    .rl-title { font-size:var(--m-fs-title) }
    .rl-header .rl-btn-primary { width:100% }
    .rl-tmpls { grid-template-columns:1fr;padding:12px }
    .rl-toolbar { margin-top:16px }
    .rl-tabs { width:100%;overflow-x:auto;scrollbar-width:none }
    .rl-tab { flex:1;justify-content:center;padding:7px 10px }
    .rl-search { font-size:16px }
    .rl-select { width:100% }
    .rl-name, .rl-desc { max-width:190px }
    .rl-tmpls:not(.rl-tmpls-all) .rl-tmpl:nth-child(n+3) { display:none }
}
</style>

{{-- ═══ Header ═══════════════════════════════════════════════════════════ --}}
<div class="rl-header m-page-head">
    <div>
        <h1 class="rl-title m-dup-title">Custom reports</h1>
        <p class="rl-sub">Reports you build from sales, stock, losses and finance figures. Open one to change its period or shop, print it or export it.</p>
    </div>
    <a href="{{ route('owner.reports.custom.builder') }}" class="rl-btn rl-btn-primary" wire:navigate>
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New report
    </a>
</div>

{{-- ═══ Templates ════════════════════════════════════════════════════════ --}}
<div class="rl-card" x-data="{ all:false }">
    <div class="rl-card-head">
        <div>
            <h2 class="rl-card-title">Start from a template</h2>
            <div class="rl-card-sub">Opens the builder with the blocks filled in. Change anything before saving.</div>
        </div>
        @if (count($templates) > 2)
            <button class="rl-link" @click="all = !all" x-text="all ? 'Show fewer' : 'Show all {{ count($templates) }}'"></button>
        @endif
    </div>
    <div class="rl-tmpls" :class="{ 'rl-tmpls-all': all }">
        @foreach ($templates as $i => $t)
            <a href="{{ route('owner.reports.custom.builder') }}?template={{ $t['key'] }}" class="rl-tmpl" wire:navigate
               @if ($i >= 4) x-show="all" x-cloak @endif wire:key="tmpl-{{ $t['key'] }}">
                <span class="rl-tmpl-icon" style="background:var({{ $t['color'] }}-dim);color:var({{ $t['color'] }})">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">{!! $t['icon'] !!}</svg>
                </span>
                <span style="min-width:0">
                    <span class="rl-tmpl-name" style="display:block">{{ $t['name'] }}</span>
                    <span class="rl-tmpl-desc">{{ $t['description'] }} · {{ $t['block_count'] }} blocks</span>
                </span>
            </a>
        @endforeach
    </div>
</div>

{{-- ═══ Toolbar ══════════════════════════════════════════════════════════ --}}
<div class="rl-toolbar">
    <div class="rl-tabs">
        @foreach (['all' => ['All', $counts->all_count], 'mine' => ['Mine', $counts->mine_count], 'shared' => ['Shared with me', $counts->shared_count]] as $key => [$label, $n])
            <button class="rl-tab {{ $filter === $key ? 'active' : '' }}" wire:click="setFilter('{{ $key }}')">
                {{ $label }} <span class="rl-tab-count">{{ $n }}</span>
            </button>
        @endforeach
    </div>
    <div class="rl-search-wrap">
        <svg class="rl-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input class="rl-search" wire:model.live.debounce.300ms="search" placeholder="Search reports…" aria-label="Search reports">
    </div>
    <select class="rl-select" wire:model.live="sortBy" aria-label="Sort">
        <option value="last_run">Recently run</option>
        <option value="run_count">Most run</option>
        <option value="created">Newest</option>
        <option value="alpha">Name A–Z</option>
    </select>
</div>

{{-- ═══ Reports ══════════════════════════════════════════════════════════ --}}
<div class="rl-card">
    @if ($reports->isEmpty())
        <div class="rl-empty">
            @if ($search !== '')
                <div class="rl-empty-title">No reports match "{{ $search }}"</div>
                <div class="rl-empty-sub">Try another word, or clear the search.</div>
                <button class="rl-btn rl-btn-ghost" wire:click="$set('search', '')">Clear search</button>
            @elseif ($filter === 'shared')
                <div class="rl-empty-title">Nothing shared with you yet</div>
                <div class="rl-empty-sub">Reports other owners share appear here.</div>
            @else
                <div class="rl-empty-title">No reports yet</div>
                <div class="rl-empty-sub">Start from a template above, or build one from scratch.</div>
                <a href="{{ route('owner.reports.custom.builder') }}" class="rl-btn rl-btn-primary" wire:navigate>New report</a>
            @endif
        </div>
    @else
        <div class="rl-scroll m-scroll">
            <table class="rl-table m-sticky-first">
                <thead>
                    <tr>
                        <th>Report</th>
                        <th style="text-align:right">Blocks</th>
                        <th>Default period</th>
                        <th>Owner</th>
                        <th>Last run</th>
                        <th style="text-align:right">Runs</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $r)
                        @php
                            $cfg = $r->resolvedConfig();
                            $mine = $r->created_by === $me;
                        @endphp
                        @if ($confirmDeleteId === $r->id)
                            <tr class="rl-confirm-row" wire:key="row-{{ $r->id }}">
                                <td colspan="7">
                                    <div class="rl-confirm">
                                        <span class="rl-confirm-text">Delete <b>{{ $r->name }}</b>? Its run history goes with it.@if ($r->is_shared) Other owners will lose it too.@endif</span>
                                        <span style="display:flex;gap:8px">
                                            <button class="rl-confirm-no" wire:click="cancelDelete">Keep</button>
                                            <button class="rl-confirm-yes" wire:click="deleteReport({{ $r->id }})">Delete report</button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            @continue
                        @endif
                        <tr wire:key="row-{{ $r->id }}">
                            <td>
                                <a href="{{ route('owner.reports.custom.view', $r->id) }}" class="rl-name" wire:navigate title="{{ $r->name }}">{{ $r->name }}</a>
                                @if ($r->description) <div class="rl-desc" title="{{ $r->description }}">{{ $r->description }}</div> @endif
                            </td>
                            <td class="rl-num">{{ $r->blockCount() }}</td>
                            <td>
                                {{ $cfg['date_range'] === 'custom' && $cfg['date_from'] && $cfg['date_to']
                                    ? \App\Services\Reports\ReportPeriod::label($cfg['date_from'], $cfg['date_to'])
                                    : ($presets[$cfg['date_range']] ?? 'This month') }}
                                @if ($r->schedule_cron)
                                    <span class="rl-badge" style="background:var(--green-dim);color:var(--green);margin-left:4px">Emailed</span>
                                @endif
                            </td>
                            <td>
                                {{ $mine ? 'You' : ($r->creator?->name ?? 'Unknown') }}
                                @if ($r->is_shared)
                                    <span class="rl-badge" style="background:var(--accent-dim);color:var(--accent);margin-left:4px">Shared</span>
                                @endif
                            </td>
                            <td class="{{ $r->last_run_at ? '' : 'rl-dim' }}" @if($r->last_run_at) title="{{ local_time($r->last_run_at)->format('j M Y, H:i') }}" @endif>
                                {{ $r->last_run_at ? $r->last_run_at->diffForHumans() : 'Never' }}
                            </td>
                            <td class="rl-num">{{ number_format($r->run_count) }}</td>
                            <td>
                                <div class="rl-acts">
                                    <a href="{{ route('owner.reports.custom.view', $r->id) }}" class="rl-action" wire:navigate>Open</a>
                                    @if ($mine)
                                        <a class="rl-ico-btn" href="{{ route('owner.reports.custom.edit', $r->id) }}" wire:navigate title="Edit" aria-label="Edit {{ $r->name }}">
                                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                    @endif
                                    <button class="rl-ico-btn" wire:click="duplicateReport({{ $r->id }})" title="Make a copy" aria-label="Make a copy of {{ $r->name }}">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                                    </button>
                                    @if ($mine)
                                        <button class="rl-ico-btn" wire:click="toggleShare({{ $r->id }})" title="{{ $r->is_shared ? 'Stop sharing' : 'Share with other owners' }}" aria-label="{{ $r->is_shared ? 'Stop sharing' : 'Share' }} {{ $r->name }}" @if($r->is_shared) style="color:var(--accent)" @endif>
                                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                                        </button>
                                        <button class="rl-ico-btn danger" wire:click="askDelete({{ $r->id }})" title="Delete" aria-label="Delete {{ $r->name }}">
                                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($reports->hasPages())
            <div class="rl-pages">{{ $reports->links() }}</div>
        @endif
    @endif
</div>
</div>
