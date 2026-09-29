@php
    $domainMeta = [
        'sales'         => ['Sales',         'accent', '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>'],
        'inventory'     => ['Inventory',     'violet', '<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>'],
        'replenishment' => ['Restocking',    'amber',  '<polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>'],
        'loss'          => ['Losses',        'red',    '<polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/>'],
        'transfers'     => ['Transfers',     'accent', '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>'],
        'operations'    => ['Operations',    'amber',  '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>'],
        'finance'       => ['Finance',       'green',  '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>'],
        'content'       => ['Text',          'accent', '<line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="3" y2="18"/>'],
    ];
    $vizLabel = ['kpi_card' => 'Summary card', 'table' => 'Table', 'bar_chart' => 'Bar chart', 'line_chart' => 'Line chart', 'text' => 'Text'];
    $presets  = \App\Services\Reports\ReportPeriod::PRESETS;
    $compares = \App\Services\Reports\ReportPeriod::COMPARISONS;
    $previews = $this->previews;
    $cancelUrl = $editingReportId ? route('owner.reports.custom.view', $editingReportId) : route('owner.reports.custom.library');
@endphp
<div class="rb-page" style="font-family:var(--font)" wire:init="loadPreviews">
<style>
.rb-page { padding:0 0 90px }
.rb-back { display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:var(--text-dim);text-decoration:none;margin-bottom:10px }
.rb-back:hover { color:var(--accent) }
.rb-header { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:18px;flex-wrap:wrap }
.rb-title  { font-size:22px;font-weight:800;color:var(--text);margin:0 0 4px }
.rb-sub    { font-size:13px;color:var(--text-dim);margin:0 }
.rb-header-actions { display:flex;gap:8px }

.rb-btn { padding:9px 16px;border-radius:var(--rsm);font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font);transition:all var(--tr);
          display:inline-flex;align-items:center;justify-content:center;gap:6px;white-space:nowrap;text-decoration:none }
.rb-btn-primary { background:var(--accent);color:#fff;border:none;box-shadow:0 3px 10px rgba(59,111,212,.25) }
.rb-btn-primary:hover { opacity:.88 }
.rb-btn-primary:disabled { opacity:.5;cursor:not-allowed }
.rb-btn-ghost { background:var(--surface);color:var(--text-sub);border:1px solid var(--border) }
.rb-btn-ghost:hover { background:var(--surface2);color:var(--text) }
.rb-btn-sm { padding:6px 11px;font-size:12px }
@keyframes rb-spin { to { transform:rotate(360deg) } }

.rb-card { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);min-width:0 }
.rb-card-head { padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px }
.rb-card-title { font-size:13px;font-weight:700;color:var(--text);margin:0 }
.rb-card-sub { font-size:12px;color:var(--text-dim);margin-top:2px }

/* Report settings */
.rb-settings { padding:18px 20px;display:grid;gap:14px;margin-bottom:18px }
.rb-row { display:grid;grid-template-columns:1fr 1fr;gap:14px }
.rb-row4 { display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;align-items:end }
.rb-label { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:6px;letter-spacing:.3px }
.rb-label span { color:var(--red) }
.rb-input, .rb-select, .rb-textarea { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--surface);
          color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.rb-input:focus, .rb-select:focus, .rb-textarea:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.rb-input:disabled, .rb-select:disabled { color:var(--text-dim);cursor:not-allowed }
.rb-input-name { font-size:16px;font-weight:700 }
.rb-textarea { min-height:140px;resize:vertical;line-height:1.55 }
.rb-error { font-size:11px;color:var(--red);margin-top:4px }
.rb-hint  { font-size:11px;color:var(--text-dim);margin-top:4px;line-height:1.5 }
.rb-dates { display:flex;gap:8px }
.rb-toggle-row { display:flex;align-items:center;gap:10px;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;cursor:pointer }
.rb-toggle { position:relative;width:38px;height:21px;flex-shrink:0 }
.rb-toggle input { position:absolute;opacity:0;width:0;height:0 }
.rb-toggle-track { position:absolute;inset:0;border-radius:21px;background:var(--surface3);border:1.5px solid var(--border);transition:background var(--tr) }
.rb-toggle input:checked ~ .rb-toggle-track { background:var(--green);border-color:var(--green) }
.rb-toggle-knob { position:absolute;top:2px;left:2px;width:14px;height:14px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:transform var(--tr) }
.rb-toggle input:checked ~ .rb-toggle-track .rb-toggle-knob { transform:translateX(17px) }
.rb-toggle-text { font-size:13px;color:var(--text-sub);line-height:1.3 }
.rb-toggle-text b { display:block;color:var(--text);font-size:13px }
.rb-schedule summary { font-size:12px;font-weight:700;color:var(--accent);cursor:pointer;list-style:none }
.rb-schedule summary::-webkit-details-marker { display:none }
.rb-schedule[open] summary { margin-bottom:10px }

/* Layout */
.rb-layout { display:grid;grid-template-columns:300px minmax(0,1fr);gap:18px;align-items:start }
.rb-aside { position:sticky;top:calc(var(--topbar-height) + 12px);max-height:calc(100vh - var(--topbar-height) - 24px);display:flex;flex-direction:column }

/* Catalogue */
.rb-cat-top { padding:12px;border-bottom:1px solid var(--border);display:grid;gap:10px }
.rb-domains { display:flex;gap:4px;overflow-x:auto;scrollbar-width:none;flex-wrap:nowrap }
.rb-domains::-webkit-scrollbar { display:none }
.rb-domain { padding:4px 10px;border-radius:6px;font-size:12px;font-weight:600;border:1px solid transparent;background:transparent;color:var(--text-dim);
             cursor:pointer;white-space:nowrap;flex-shrink:0;font-family:var(--font) }
.rb-domain:hover { background:var(--surface2);color:var(--text) }
.rb-domain.active { background:var(--accent);color:#fff }
.rb-cat-list { overflow-y:auto;flex:1;padding:4px 0 8px }
.rb-cat-group { padding:10px 14px 4px;font-size:10px;font-weight:700;letter-spacing:.7px;text-transform:uppercase;color:var(--accent) }
.rb-cat-item { display:block;width:100%;text-align:left;padding:9px 14px;border:none;background:transparent;cursor:pointer;font-family:var(--font);
               border-left:3px solid transparent;transition:background var(--tr) }
.rb-cat-item:hover { background:var(--surface2);border-left-color:var(--accent) }
.rb-cat-name { display:flex;align-items:center;justify-content:space-between;gap:8px;font-size:13px;font-weight:600;color:var(--text) }
.rb-cat-desc { font-size:12px;color:var(--text-dim);margin-top:2px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden }
.rb-cat-chips { display:flex;gap:4px;flex-wrap:wrap;margin-top:5px }
.rb-chip { font-size:10px;font-weight:600;padding:1px 6px;border-radius:4px;background:var(--surface2);color:var(--text-dim);white-space:nowrap }
.rb-used { font-size:10px;font-weight:700;padding:1px 6px;border-radius:4px;background:var(--green-dim);color:var(--green);white-space:nowrap }
.rb-add-icon { color:var(--accent);flex-shrink:0 }

/* Canvas */
.rb-canvas-head { display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px;flex-wrap:wrap }
.rb-canvas-title { font-size:14px;font-weight:700;color:var(--text) }
.rb-canvas-note { font-size:12px;color:var(--text-dim) }
.rb-blocks { display:grid;gap:8px }
.rb-block { display:flex;align-items:center;gap:12px;padding:12px 12px 12px 8px;background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);
            transition:box-shadow var(--tr);min-width:0;border-left:3px solid transparent }
.rb-block:hover { box-shadow:var(--shadow-card-hover) }
.rb-block.active { border-left-color:var(--accent);box-shadow:0 0 0 2px var(--accent-glow),var(--shadow-card) }
.rb-block.sortable-ghost { opacity:.35 }
.rb-handle { cursor:grab;color:var(--text-dim);padding:6px 2px;display:flex;touch-action:none }
.rb-handle:active { cursor:grabbing }
.rb-icon { width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0 }
.rb-block-main { flex:1;min-width:0;cursor:pointer;border:none;background:none;text-align:left;padding:0;font-family:var(--font) }
.rb-block-title { font-size:14px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis }
.rb-block-title.untitled { color:var(--text-dim);font-style:italic }
.rb-block-meta { display:flex;gap:6px;align-items:center;flex-wrap:wrap;margin-top:4px;font-size:12px;color:var(--text-dim) }
.rb-tag { font-size:11px;font-weight:600;padding:1px 7px;border-radius:5px;white-space:nowrap }
.rb-preview { text-align:right;min-width:0;max-width:210px;flex-shrink:0 }
.rb-preview-v { font-family:var(--mono);font-size:14px;font-weight:800;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis }
.rb-preview-l { font-size:11px;color:var(--text-dim);white-space:nowrap;overflow:hidden;text-overflow:ellipsis }
.rb-preview-err { font-size:12px;color:var(--red) }
.rb-skel { display:inline-block;width:90px;height:14px;border-radius:4px;background:var(--surface2) }
.rb-acts { display:flex;gap:2px;flex-shrink:0 }
.rb-ico-btn { width:30px;height:30px;border-radius:7px;border:none;background:transparent;color:var(--text-dim);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all var(--tr) }
.rb-ico-btn:hover { background:var(--surface2);color:var(--text) }
.rb-ico-btn.danger:hover { color:var(--red) }
.rb-ico-btn:disabled { opacity:.3;cursor:default }

/* Empty canvas: templates */
.rb-empty { padding:22px }
.rb-empty-title { font-size:15px;font-weight:700;color:var(--text);margin:0 0 4px }
.rb-empty-sub { font-size:13px;color:var(--text-dim);margin:0 0 16px }
.rb-tmpls { display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px }
.rb-tmpl { display:flex;gap:10px;align-items:flex-start;text-align:left;padding:12px;border:1.5px solid var(--border);border-radius:10px;background:var(--surface);
           cursor:pointer;font-family:var(--font);transition:all var(--tr) }
.rb-tmpl:hover { border-color:var(--accent);background:var(--accent-dim) }
.rb-tmpl-name { font-size:13px;font-weight:700;color:var(--text) }
.rb-tmpl-desc { font-size:12px;color:var(--text-dim);margin-top:2px;line-height:1.4 }

/* Drawer (§14) */
.rb-overlay { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.35) }
.rb-drawer { position:fixed;top:0;right:0;bottom:0;z-index:401;width:440px;max-width:100vw;background:var(--surface);border-left:1px solid var(--border);
             box-shadow:-8px 0 40px rgba(26,31,54,.14);display:flex;flex-direction:column }
.rb-drawer-head { display:flex;align-items:center;justify-content:space-between;gap:10px;padding:16px 20px;border-bottom:1px solid var(--border);flex-shrink:0 }
.rb-drawer-title { font-size:16px;font-weight:800;color:var(--text) }
.rb-drawer-sub { font-size:12px;color:var(--text-dim);margin-top:2px }
.rb-drawer-close { width:32px;height:32px;border-radius:8px;border:none;background:var(--surface2);color:var(--text-sub);cursor:pointer;display:flex;align-items:center;justify-content:center }
.rb-drawer-body { flex:1;overflow-y:auto;padding:18px 20px }
.rb-drawer-foot { padding:14px 20px;border-top:1px solid var(--border);display:flex;gap:10px;flex-shrink:0 }
.rb-field { margin-bottom:18px }
.rb-section { font-size:10px;font-weight:700;letter-spacing:.7px;text-transform:uppercase;color:var(--accent);margin:22px 0 10px;padding-top:14px;border-top:1px solid var(--border) }
.rb-seg { display:flex;gap:4px;flex-wrap:wrap }
.rb-seg-btn { padding:7px 12px;border-radius:8px;border:1.5px solid var(--border);background:var(--surface);font-size:12px;font-weight:600;color:var(--text-sub);cursor:pointer;font-family:var(--font) }
.rb-seg-btn:hover { border-color:var(--accent);color:var(--accent) }
.rb-seg-btn.active { background:var(--accent);border-color:var(--accent);color:#fff }
.rb-disabled-note { font-size:12px;color:var(--text-dim);padding:10px 12px;border-left:3px solid var(--border);line-height:1.5 }

@media (max-width:1100px) {
    .rb-row4 { grid-template-columns:1fr 1fr }
    .rb-layout { grid-template-columns:260px minmax(0,1fr) }
}
@media (max-width:900px) {
    .rb-layout { grid-template-columns:1fr }
    .rb-aside { display:none }
    .rb-preview { max-width:130px }
}
@media (max-width:768px) {
    .rb-drawer { left:0;width:auto }
    .rb-drawer-foot > * { flex:1 }
}
@media (max-width:640px) {
    .rb-title { font-size:var(--m-fs-title) }
    .rb-header-actions { display:none }
    .rb-row, .rb-row4 { grid-template-columns:1fr }
    .rb-settings { padding:14px }
    .rb-block { gap:8px;padding:10px 8px 10px 4px }
    .rb-icon { display:none }
    .rb-preview { display:none }
    .rb-acts .rb-move { display:none }
    .rb-input, .rb-select, .rb-textarea { font-size:16px }
}
@media (min-width:901px) { .rb-add-phone { display:none !important } }
</style>

{{-- ═══ Header ═══════════════════════════════════════════════════════════ --}}
<a href="{{ $cancelUrl }}" class="rb-back" wire:navigate>
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    {{ $editingReportId ? 'Back to the report' : 'Custom reports' }}
</a>
<div class="rb-header m-page-head">
    <div>
        <h1 class="rb-title">{{ $editingReportId ? 'Edit report' : 'New report' }}</h1>
        <p class="rb-sub">Add blocks from the list, drag them into order, and click a block to set it up.</p>
    </div>
    <div class="rb-header-actions">
        <a href="{{ $cancelUrl }}" class="rb-btn rb-btn-ghost" wire:navigate>Cancel</a>
        <button class="rb-btn rb-btn-primary" wire:click="save" wire:loading.attr="disabled" wire:target="save">
            <span wire:loading.remove wire:target="save">Save report</span>
            <span wire:loading wire:target="save" style="display:none">Saving…</span>
        </button>
    </div>
</div>

{{-- ═══ Report settings ══════════════════════════════════════════════════ --}}
<div class="rb-card rb-settings">
    <div class="rb-row">
        <div>
            <label class="rb-label" for="rb-name">Report name <span>*</span></label>
            <input id="rb-name" class="rb-input rb-input-name" wire:model.blur="reportName" placeholder="e.g. Monthly shop review" maxlength="120">
            @error('reportName') <div class="rb-error">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="rb-label" for="rb-desc">Description</label>
            <input id="rb-desc" class="rb-input" wire:model.blur="reportDescription" placeholder="What is this report for?" maxlength="500">
        </div>
    </div>
    <div class="rb-row4">
        <div>
            <label class="rb-label">Default period</label>
            <select class="rb-select" wire:model.live="dateRange">
                @foreach ($presets as $k => $l) <option value="{{ $k }}">{{ $l }}</option> @endforeach
            </select>
        </div>
        @if ($dateRange === 'custom')
            <div>
                <label class="rb-label">From / to</label>
                <div class="rb-dates">
                    <input type="date" class="rb-input" wire:model.live="dateFrom" aria-label="From">
                    <input type="date" class="rb-input" wire:model.live="dateTo" aria-label="To">
                </div>
                @error('dateFrom') <div class="rb-error">{{ $message }}</div> @enderror
                @error('dateTo') <div class="rb-error">{{ $message }}</div> @enderror
            </div>
        @endif
        <div>
            <label class="rb-label">Default location</label>
            <select class="rb-select" wire:model.live="locationFilter">
                <option value="all">All locations</option>
                <optgroup label="Shops">@foreach ($shops as $id => $n) <option value="shop:{{ $id }}">{{ $n }}</option> @endforeach</optgroup>
                <optgroup label="Warehouses">@foreach ($warehouses as $id => $n) <option value="warehouse:{{ $id }}">{{ $n }}</option> @endforeach</optgroup>
            </select>
        </div>
        <div>
            <label class="rb-label">Compare with</label>
            <select class="rb-select" wire:model.live="comparisonMode">
                @foreach ($compares as $k => $l) <option value="{{ $k }}">{{ $l }}</option> @endforeach
            </select>
        </div>
        <label class="rb-toggle-row">
            <span class="rb-toggle">
                <input type="checkbox" wire:model.live="isShared">
                <span class="rb-toggle-track"><span class="rb-toggle-knob"></span></span>
            </span>
            <span class="rb-toggle-text"><b>Share with other owners</b>They can view it, not edit it.</span>
        </label>
    </div>
    <details class="rb-schedule" @if($scheduleCron || $scheduleRecipients || $errors->has('scheduleCron') || $errors->has('scheduleRecipients')) open @endif>
        <summary>Email schedule {{ $scheduleCron ? '· on' : '' }}</summary>
        <div class="rb-row">
            <div>
                <label class="rb-label">When (cron)</label>
                <input class="rb-input" wire:model.blur="scheduleCron" placeholder="0 8 * * 1  (Mondays at 08:00)">
                @error('scheduleCron') <div class="rb-error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="rb-label">Send to</label>
                <input class="rb-input" wire:model.blur="scheduleRecipients" placeholder="name@example.com, other@example.com">
                @error('scheduleRecipients') <div class="rb-error">{{ $message }}</div> @enderror
            </div>
        </div>
    </details>
</div>

{{-- ═══ Catalogue + canvas ═══════════════════════════════════════════════ --}}
<div class="rb-layout">

    {{-- Catalogue (desktop) --}}
    <aside class="rb-card rb-aside">
        @include('livewire.owner.reports.partials.builder-catalogue')
    </aside>

    {{-- Canvas --}}
    <section style="min-width:0">
        <div class="rb-canvas-head">
            <div>
                <div class="rb-canvas-title">Blocks ({{ count($canvas) }})</div>
                <div class="rb-canvas-note">Previews use {{ $previewPeriod }} · {{ \App\Services\Reports\ReportContext::locationLabel($locationFilter) }}</div>
            </div>
            <div style="display:flex;gap:8px">
                <button class="rb-btn rb-btn-primary rb-btn-sm rb-add-phone" wire:click="$set('showCatalogue', true)">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add block
                </button>
                @if (count($canvas) > 0)
                    <span x-data="{ sure:false }" style="display:flex;gap:6px">
                        <button class="rb-btn rb-btn-ghost rb-btn-sm" x-show="!sure" @click="sure = true">Clear all</button>
                        <button class="rb-btn rb-btn-ghost rb-btn-sm" x-show="sure" x-cloak @click="sure = false">Keep</button>
                        <button class="rb-btn rb-btn-sm" x-show="sure" x-cloak wire:click="clearCanvas" @click="sure = false"
                                style="background:var(--red);color:#fff;border:none">Remove all {{ count($canvas) }}</button>
                    </span>
                @endif
            </div>
        </div>
        @error('canvas') <div class="rb-error" style="margin:-4px 0 10px;font-size:13px">{{ $message }}</div> @enderror

        @if (count($canvas) === 0)
            <div class="rb-card rb-empty">
                <p class="rb-empty-title">Start from a template, or add blocks from the list</p>
                <p class="rb-empty-sub">A template fills in a set of blocks you can then change.</p>
                <div class="rb-tmpls">
                    @foreach ($templates as $t)
                        <button class="rb-tmpl" wire:click="loadTemplate('{{ $t['key'] }}')" wire:key="tmpl-{{ $t['key'] }}">
                            <span class="rb-icon" style="background:var({{ $t['color'] }}-dim);color:var({{ $t['color'] }})">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">{!! $t['icon'] !!}</svg>
                            </span>
                            <span>
                                <span class="rb-tmpl-name">{{ $t['name'] }}</span>
                                <span class="rb-tmpl-desc" style="display:block">{{ $t['description'] }} · {{ $t['block_count'] }} blocks</span>
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        @else
            <div class="rb-blocks" id="rb-blocks" wire:key="rb-blocks">
                @foreach ($canvas as $i => $block)
                    @php
                        $m = $flat[$block['metric_id']] ?? null;
                        [, $col, $icon] = $domainMeta[$m['domain'] ?? 'content'] ?? $domainMeta['content'];
                        $p = $previews[$block['id']] ?? null;
                        $isText = $block['metric_id'] === 'text_block';
                    @endphp
                    <div class="rb-block {{ $selectedBlockId === $block['id'] ? 'active' : '' }}" data-id="{{ $block['id'] }}" wire:key="blk-{{ $block['id'] }}">
                        <span class="rb-handle" title="Drag to reorder" aria-label="Drag to reorder">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/></svg>
                        </span>
                        <span class="rb-icon" style="background:var(--{{ $col }}-dim);color:var(--{{ $col }})">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">{!! $icon !!}</svg>
                        </span>
                        <button class="rb-block-main" wire:click="selectBlock('{{ $block['id'] }}')">
                            <div class="rb-block-title {{ $block['title'] === '' ? 'untitled' : '' }}">{{ $block['title'] !== '' ? $block['title'] : 'Untitled' }}</div>
                            <div class="rb-block-meta">
                                @if ($m && $block['title'] !== $m['label']) <span>{{ $m['label'] }}</span> @endif
                                <span class="rb-tag" style="background:var(--surface2);color:var(--text-sub)">{{ $vizLabel[$block['viz']] ?? $block['viz'] }}</span>
                                @unless ($isText || $block['viz'] === 'kpi_card')
                                    <span class="rb-tag" style="background:var(--surface2);color:var(--text-dim)">{{ $block['width'] === 'full' ? 'Full width' : 'Half width' }}</span>
                                @endunless
                                @if (! empty($block['date_range_override']))
                                    <span class="rb-tag" style="background:var(--violet-dim);color:var(--violet)">{{ $presets[$block['date_range_override']] ?? 'Own period' }}</span>
                                @endif
                                @if (! empty($block['location_filter_override']))
                                    <span class="rb-tag" style="background:var(--violet-dim);color:var(--violet)">{{ \App\Services\Reports\ReportContext::locationLabel($block['location_filter_override']) }}</span>
                                @endif
                                @if ($isText && trim($block['content'] ?? '') === '')
                                    <span class="rb-tag" style="background:var(--amber-dim);color:var(--amber)">No text yet</span>
                                @endif
                            </div>
                        </button>
                        <div class="rb-preview">
                            @if ($isText)
                                <div class="rb-preview-l" title="{{ $block['content'] ?? '' }}">{{ Str::limit($block['content'] ?? '', 40) }}</div>
                            @elseif (! $previewsReady)
                                <span class="rb-skel"></span>
                            @elseif ($p && $p['error'])
                                <div class="rb-preview-err">Couldn't load</div>
                            @elseif ($p)
                                <div class="rb-preview-v">{{ $p['headline'] ?? '—' }}</div>
                                <div class="rb-preview-l">{{ $p['rows'] > 0 && $block['viz'] !== 'kpi_card' ? $p['rows'] . ' ' . Str::plural('row', $p['rows']) : $p['label'] }}</div>
                            @endif
                        </div>
                        <div class="rb-acts">
                            <button class="rb-ico-btn rb-move" wire:click="moveBlock('{{ $block['id'] }}', -1)" @disabled($i === 0) title="Move up" aria-label="Move up">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg>
                            </button>
                            <button class="rb-ico-btn rb-move" wire:click="moveBlock('{{ $block['id'] }}', 1)" @disabled($i === count($canvas) - 1) title="Move down" aria-label="Move down">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <button class="rb-ico-btn" wire:click="duplicateBlock('{{ $block['id'] }}')" title="Duplicate" aria-label="Duplicate">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                            </button>
                            <button class="rb-ico-btn danger" wire:click="removeBlock('{{ $block['id'] }}')" title="Remove" aria-label="Remove">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>

{{-- Sticky Cancel / Save on phones --}}
<div class="m-actions m-only" style="margin-top:16px">
    <a href="{{ $cancelUrl }}" class="rb-btn rb-btn-ghost" wire:navigate>Cancel</a>
    <button class="rb-btn rb-btn-primary" wire:click="save" wire:loading.attr="disabled" wire:target="save">Save report</button>
</div>

{{-- ═══ Catalogue sheet (phones / narrow screens) ═══════════════════════ --}}
@if ($showCatalogue)
    <div class="m-sheet-overlay" wire:click="$set('showCatalogue', false)"></div>
    <div class="m-sheet" role="dialog" aria-modal="true" style="display:flex;flex-direction:column">
        <div class="m-sheet-handle"></div>
        <div class="m-sheet-head">
            <h2 class="m-sheet-title">Add a block</h2>
            <button class="rb-drawer-close m-tap" wire:click="$set('showCatalogue', false)" aria-label="Close">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div style="flex:1;min-height:0;display:flex;flex-direction:column">
            @include('livewire.owner.reports.partials.builder-catalogue')
        </div>
    </div>
@endif

{{-- ═══ Block drawer ═════════════════════════════════════════════════════ --}}
@if ($selected)
    @php
        $m = $flat[$selected['metric_id']];
        $isText = $selected['metric_id'] === 'text_block';
        $p = $previews[$selected['id']] ?? null;
        $columns = $p['columns'] ?? [];
    @endphp
    <div class="rb-overlay" wire:click="closeBlock"></div>
    <div class="rb-drawer" role="dialog" aria-modal="true" @keydown.escape.window="$wire.closeBlock()" wire:key="drawer-{{ $selected['id'] }}">
        <div class="rb-drawer-head">
            <div style="min-width:0">
                <div class="rb-drawer-title">{{ $m['label'] }}</div>
                <div class="rb-drawer-sub">{{ $m['description'] }}</div>
            </div>
            <button class="rb-drawer-close" wire:click="closeBlock" aria-label="Close">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="rb-drawer-body">
            <div class="rb-field">
                <label class="rb-label" for="rb-b-title">{{ $isText ? 'Heading' : 'Title' }}</label>
                <input id="rb-b-title" class="rb-input" wire:model.live.debounce.400ms="edit.title" maxlength="120" placeholder="{{ $isText ? 'Optional' : $m['label'] }}">
            </div>

            @if ($isText)
                <div class="rb-field">
                    <label class="rb-label" for="rb-b-text">Text</label>
                    <textarea id="rb-b-text" class="rb-textarea" wire:model.live.debounce.600ms="edit.content" maxlength="5000" placeholder="Context, conclusions or notes for whoever reads the report"></textarea>
                </div>
            @else
                @if (count($m['viz_options']) > 1)
                    <div class="rb-field">
                        <label class="rb-label">Show as</label>
                        <div class="rb-seg">
                            @foreach ($m['viz_options'] as $v)
                                <button type="button" class="rb-seg-btn {{ ($edit['viz'] ?? '') === $v ? 'active' : '' }}" wire:click="$set('edit.viz', '{{ $v }}')">{{ $vizLabel[$v] ?? $v }}</button>
                            @endforeach
                        </div>
                        @if (($edit['viz'] ?? '') === 'kpi_card')
                            <div class="rb-hint">Summary cards sit together at the top of the report.</div>
                        @endif
                    </div>
                @endif

                @if (($edit['viz'] ?? '') !== 'kpi_card')
                    <div class="rb-field">
                        <label class="rb-label">Width</label>
                        <div class="rb-seg">
                            <button type="button" class="rb-seg-btn {{ ($edit['width'] ?? '') === 'half' ? 'active' : '' }}" wire:click="$set('edit.width', 'half')">Half width</button>
                            <button type="button" class="rb-seg-btn {{ ($edit['width'] ?? '') === 'full' ? 'active' : '' }}" wire:click="$set('edit.width', 'full')">Full width</button>
                        </div>
                    </div>

                    @if ($columns)
                        <div class="rb-section">Rows</div>
                        <div class="rb-row" style="margin-bottom:18px">
                            <div>
                                <label class="rb-label">Sort by</label>
                                <select class="rb-select" wire:model.live="edit.sort_by">
                                    <option value="">Default order</option>
                                    @foreach ($columns as $c) <option value="{{ $c['key'] }}">{{ $c['label'] }}</option> @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="rb-label">Order</label>
                                <select class="rb-select" wire:model.live="edit.sort_direction" @disabled(($edit['sort_by'] ?? '') === '')>
                                    <option value="desc">Highest first / Z→A</option>
                                    <option value="asc">Lowest first / A→Z</option>
                                </select>
                            </div>
                        </div>
                        <div class="rb-field">
                            <label class="rb-label">Show</label>
                            <select class="rb-select" wire:model.live="edit.limit">
                                <option value="">All rows</option>
                                @foreach ([5, 10, 20, 50] as $n) <option value="{{ $n }}">Top {{ $n }}</option> @endforeach
                            </select>
                        </div>
                    @elseif (! $previewsReady)
                        <div class="rb-hint">Loading the columns you can sort by…</div>
                    @endif
                @else
                    <div class="rb-section">Alert levels</div>
                    <div class="rb-hint" style="margin:-4px 0 10px">
                        {{ $m['good'] === 'up'
                            ? 'Colour the card when the figure falls to or below these values.'
                            : 'Colour the card when the figure rises to or above these values.' }}
                        Leave empty for none.
                    </div>
                    <div class="rb-row" style="margin-bottom:18px">
                        <div>
                            <label class="rb-label">Warning (amber)</label>
                            <input type="number" step="any" class="rb-input" wire:model.live.debounce.500ms="edit.threshold_warning">
                        </div>
                        <div>
                            <label class="rb-label">Critical (red)</label>
                            <input type="number" step="any" class="rb-input" wire:model.live.debounce.500ms="edit.threshold_critical">
                        </div>
                    </div>
                @endif

                <div class="rb-section">This block only</div>
                <div class="rb-field">
                    <label class="rb-label">Period</label>
                    @if ($m['needs_dates'])
                        <select class="rb-select" wire:model.live="edit.date_range">
                            <option value="">Same as the report</option>
                            @foreach ($presets as $k => $l) <option value="{{ $k }}">{{ $l }}</option> @endforeach
                        </select>
                        @if (($edit['date_range'] ?? '') === 'custom')
                            <div class="rb-dates" style="margin-top:8px">
                                <input type="date" class="rb-input" wire:model.live="edit.date_from" aria-label="From">
                                <input type="date" class="rb-input" wire:model.live="edit.date_to" aria-label="To">
                            </div>
                        @endif
                    @else
                        <div class="rb-disabled-note">{{ $m['period_note'] ?? "The report period doesn't apply to this figure." }}</div>
                    @endif
                </div>
                <div class="rb-field">
                    <label class="rb-label">Location</label>
                    @if ($m['locations'] === 'none')
                        <div class="rb-disabled-note">Always covers every location.</div>
                    @else
                        <select class="rb-select" wire:model.live="edit.location">
                            <option value="">Same as the report</option>
                            <optgroup label="Shops">@foreach ($shops as $id => $n) <option value="shop:{{ $id }}">{{ $n }}</option> @endforeach</optgroup>
                            @if ($m['locations'] === 'any')
                                <optgroup label="Warehouses">@foreach ($warehouses as $id => $n) <option value="warehouse:{{ $id }}">{{ $n }}</option> @endforeach</optgroup>
                            @endif
                        </select>
                        @if ($m['locations'] === 'shop')
                            <div class="rb-hint">Recorded per shop, so it can't be filtered by warehouse.</div>
                        @endif
                    @endif
                </div>

                @if ($p && ! $p['error'])
                    <div class="rb-section">Preview</div>
                    <div style="font-size:13px;color:var(--text-sub)">
                        <b style="font-family:var(--mono);color:var(--text)">{{ $p['headline'] ?? '—' }}</b> {{ $p['label'] }}
                        @if ($p['rows'] > 0) · {{ $p['rows'] }} {{ Str::plural('row', $p['rows']) }} @endif
                    </div>
                    @foreach ($p['notes'] as $note) <div class="rb-hint">{{ $note }}</div> @endforeach
                @endif
            @endif
        </div>
        <div class="rb-drawer-foot">
            <button class="rb-btn rb-btn-ghost" wire:click="removeBlock('{{ $selected['id'] }}')" style="color:var(--red)">Remove block</button>
            <button class="rb-btn rb-btn-primary" style="flex:1" wire:click="closeBlock">Done</button>
        </div>
    </div>
@endif

@script
<script>
(function () {
    function init() {
        const list = $wire.$el.querySelector('#rb-blocks');
        if (!list || list._sortable || typeof Sortable === 'undefined') return;
        list._sortable = Sortable.create(list, {
            handle: '.rb-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd() {
                $wire.reorderBlocks(Array.from(list.children).map(el => el.dataset.id));
            },
        });
    }
    init();
    Livewire.hook('commit', ({ component, succeed }) => {
        if (component.id === $wire.$id) succeed(() => requestAnimationFrame(init));
    });
})();
</script>
@endscript
</div>
