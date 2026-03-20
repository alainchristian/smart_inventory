{{-- ┌─────────────────────────────────────────────────────────────────────────┐
    │  Owner · Report Builder                                                 │
    │  Two-panel: Metric catalogue (left) + Canvas (right)                   │
    └─────────────────────────────────────────────────────────────────────────┋ --}}
<div>
<style>
.rb-page-title { font-size:22px;font-weight:700;color:var(--text);letter-spacing:-0.5px;margin:0 0 4px }
.rb-page-subtitle { font-size:13px;color:var(--text-dim) }
.rb-layout { display:grid;grid-template-columns:320px 1fr;gap:20px;align-items:start }
.rb-panel { background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden }
.rb-panel-hdr { padding:14px 16px;border-bottom:1px solid var(--border);font-size:12px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px }
.rb-search-input { width:100%;padding:10px 14px;background:var(--surface2);border:none;border-bottom:1px solid var(--border);font-size:13px;color:var(--text);box-sizing:border-box }
.rb-search-input:focus { outline:none;background:var(--surface);border-bottom-color:var(--accent) }
.rb-domain-tabs { display:flex;overflow-x:auto;scrollbar-width:none;border-bottom:1px solid var(--border) }
.rb-domain-tabs::-webkit-scrollbar { display:none }
.rb-domain-tab { padding:8px 12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;cursor:pointer;border:none;background:transparent;color:var(--text-dim);white-space:nowrap;border-bottom:2px solid transparent;margin-bottom:-1px }
.rb-domain-tab.active { color:var(--accent);border-bottom-color:var(--accent) }
.rb-domain-tab:hover { color:var(--text) }
.rb-metric-list { max-height:calc(100vh - 280px);overflow-y:auto }
.rb-domain-section { padding:10px 0 }
.rb-domain-label { font-size:10px;font-weight:800;color:var(--text-dim);text-transform:uppercase;letter-spacing:.7px;padding:6px 14px 4px }
.rb-metric-item { display:flex;align-items:center;justify-content:space-between;gap:8px;padding:8px 14px;cursor:default }
.rb-metric-item:hover { background:var(--surface2) }
.rb-metric-info { flex:1;min-width:0 }
.rb-metric-name { font-size:13px;font-weight:600;color:var(--text) }
.rb-metric-desc { font-size:11px;color:var(--text-dim);margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis }
.rb-add-btn { flex-shrink:0;padding:4px 10px;background:var(--accent-dim);color:var(--accent);border:1px solid var(--accent-dim);border-radius:var(--rsm);font-size:12px;font-weight:700;cursor:pointer }
.rb-add-btn:hover { background:var(--accent);color:#fff }
.rb-added-check { flex-shrink:0;display:inline-flex;align-items:center;gap:4px;padding:4px 10px;color:var(--success);font-size:12px;font-weight:700 }
.rb-canvas-panel { background:var(--surface);border:1px solid var(--border);border-radius:var(--r) }
.rb-canvas-hdr { padding:14px 20px;border-bottom:1px solid var(--border) }
.rb-meta-section { padding:16px 20px;border-bottom:1px solid var(--border);display:flex;flex-direction:column;gap:12px }
.rb-form-row { display:grid;grid-template-columns:1fr 1fr;gap:12px }
.rb-label { font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:4px }
.rb-input { width:100%;padding:8px 12px;background:var(--surface2);border:1px solid var(--border);border-radius:var(--rsm);font-size:13px;color:var(--text);box-sizing:border-box }
.rb-input:focus { outline:none;border-color:var(--accent) }
.rb-select { width:100%;padding:8px 12px;background:var(--surface2);border:1px solid var(--border);border-radius:var(--rsm);font-size:13px;color:var(--text) }
.rb-canvas-body { padding:16px 20px;display:flex;flex-direction:column;gap:10px;min-height:160px }
.rb-empty-canvas { display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:160px;border:2px dashed var(--border);border-radius:var(--r);color:var(--text-dim);text-align:center;padding:32px }
.rb-block-card { background:var(--surface2);border:1px solid var(--border);border-radius:var(--rsm);padding:12px 14px }
.rb-block-top { display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px }
.rb-block-title-input { flex:1;padding:5px 8px;background:var(--surface);border:1px solid var(--border);border-radius:4px;font-size:13px;font-weight:600;color:var(--text);min-width:0 }
.rb-block-title-input:focus { outline:none;border-color:var(--accent) }
.rb-block-controls { display:flex;align-items:center;gap:6px;flex-wrap:wrap }
.rb-block-btn { padding:4px 8px;background:var(--surface);border:1px solid var(--border);border-radius:4px;font-size:11px;cursor:pointer;color:var(--text-sub) }
.rb-block-btn:hover { background:var(--surface2);color:var(--text) }
.rb-block-btn.active { background:var(--accent-dim);color:var(--accent);border-color:var(--accent-dim);font-weight:700 }
.rb-block-remove { padding:4px 8px;background:transparent;border:1px solid var(--red-dim);border-radius:4px;font-size:11px;cursor:pointer;color:var(--red) }
.rb-block-remove:hover { background:var(--danger-glow) }
.rb-block-meta { font-size:11px;color:var(--text-dim);display:flex;align-items:center;gap:8px }
.rb-block-domain { display:inline-block;padding:1px 7px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;background:var(--surface);border:1px solid var(--border) }
.rb-canvas-footer { padding:14px 20px;border-top:1px solid var(--border);display:flex;align-items:center;gap:10px }
.rb-save-btn { padding:10px 24px;background:var(--accent);color:#fff;border:none;border-radius:var(--rsm);font-size:14px;font-weight:700;cursor:pointer }
.rb-save-btn:hover { opacity:.9 }
.rb-error { font-size:12px;color:var(--red);margin-top:2px }
.rb-template-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-bottom:20px }
.rb-template-card { background:var(--surface);border:1px solid var(--border);border-radius:var(--rsm);padding:12px;cursor:pointer;transition:border-color .15s }
.rb-template-card:hover { border-color:var(--accent) }
.rb-template-card-name { font-size:13px;font-weight:700;color:var(--text);margin-bottom:3px }
.rb-template-card-desc { font-size:11px;color:var(--text-dim) }
.rb-block-advanced { border-top:1px solid var(--border);margin-top:8px;padding-top:8px }
.rb-block-advanced-toggle { font-size:11px;color:var(--text-dim);cursor:pointer;display:flex;align-items:center;gap:4px;background:none;border:none;padding:0 }
.rb-block-advanced-toggle:hover { color:var(--text) }
@@media(max-width:900px) {
    .rb-layout { grid-template-columns:1fr }
    .rb-metric-list { max-height:300px }
}
</style>

{{-- ══════════════════════════════════════════════════════════════════════════
     PAGE HEADER
══════════════════════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px;flex-wrap:wrap">
    <div>
        <h1 class="rb-page-title">{{ $editingReportId ? 'Edit Report' : 'New Report' }}</h1>
        <p class="rb-page-subtitle">Add metric blocks from the catalogue, then save</p>
    </div>
    <a href="{{ route('owner.reports.custom.library') }}" class="rb-block-btn" wire:navigate>
        ← Back to Library
    </a>
</div>

@if (session('success'))
<div style="padding:10px 16px;background:var(--success-glow);border:1px solid var(--success);border-radius:var(--rsm);font-size:13px;color:var(--success);margin-bottom:16px">
    {{ session('success') }}
</div>
@endif

{{-- ── Templates ──────────────────────────────────────────────────────────── --}}
@if (!$editingReportId)
@php $tmplList = app(\App\Services\Reports\ReportTemplates::class)->list(); @endphp
<div style="margin-bottom:20px">
    <div style="font-size:11px;font-weight:800;color:var(--text-dim);text-transform:uppercase;letter-spacing:.7px;margin-bottom:8px">
        Start from a Template
    </div>
    <div class="rb-template-grid">
        @foreach ($tmplList as $tmpl)
        <div class="rb-template-card" wire:click="loadTemplate('{{ $tmpl['key'] }}')" title="Load template">
            <div class="rb-template-card-name">{{ $tmpl['name'] }}</div>
            <div class="rb-template-card-desc">{{ $tmpl['description'] }}</div>
        </div>
        @endforeach
        <div class="rb-template-card" style="display:flex;align-items:center;justify-content:center;border-style:dashed;color:var(--text-dim);font-size:12px">
            Start blank ↓
        </div>
    </div>
</div>
@endif

<div class="rb-layout">
    {{-- ── LEFT: Metric Catalogue ─────────────────────────────────────────── --}}
    <div class="rb-panel">
        <div class="rb-panel-hdr">Metric Catalogue</div>

        <input type="text" class="rb-search-input" placeholder="Search metrics…"
               wire:model.live.debounce.300ms="catalogueSearch">

        <div class="rb-domain-tabs">
            @foreach (['all'=>'All','sales'=>'Sales','inventory'=>'Inventory','replenishment'=>'Repl.','loss'=>'Loss','transfers'=>'Transfers','operations'=>'Ops','content'=>'Text'] as $key => $lbl)
            <button class="rb-domain-tab {{ $catalogueDomain === $key ? 'active' : '' }}"
                    wire:click="$set('catalogueDomain','{{ $key }}')">{{ $lbl }}</button>
            @endforeach
        </div>

        <div class="rb-metric-list">
            @forelse ($catalogue as $domain => $metrics)
            <div class="rb-domain-section">
                <div class="rb-domain-label">{{ ucfirst($domain) }}</div>
                @foreach ($metrics as $metric)
                <div class="rb-metric-item">
                    <div class="rb-metric-info">
                        <div class="rb-metric-name">{{ $metric['label'] }}</div>
                        <div class="rb-metric-desc">{{ $metric['description'] }}</div>
                    </div>
                    @if (in_array($metric['id'], $addedMetricIds))
                    <span class="rb-added-check">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        Added
                    </span>
                    @else
                    <button class="rb-add-btn" wire:click="addBlock('{{ $metric['id'] }}')">+ Add</button>
                    @endif
                </div>
                @endforeach
            </div>
            @empty
            <div style="padding:24px;text-align:center;font-size:13px;color:var(--text-dim)">
                No metrics match your search
            </div>
            @endforelse
        </div>
    </div>

    {{-- ── RIGHT: Canvas ──────────────────────────────────────────────────── --}}
    <div class="rb-canvas-panel">
        {{-- Report meta --}}
        <div class="rb-meta-section">
            <div>
                <label class="rb-label">Report Name</label>
                <input type="text" class="rb-input" placeholder="e.g. Monthly Operations Review"
                       wire:model="reportName">
                @error('reportName') <span class="rb-error">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="rb-label">Description (optional)</label>
                <input type="text" class="rb-input" placeholder="What is this report for?"
                       wire:model="reportDescription">
            </div>
            <div class="rb-form-row">
                <div>
                    <label class="rb-label">Date Range</label>
                    <select class="rb-select" wire:model.live="dateRange">
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div>
                    <label class="rb-label">Location Filter</label>
                    <select class="rb-select" wire:model="locationFilter">
                        <option value="all">All Locations</option>
                        @if ($warehouses->isNotEmpty())
                        <optgroup label="Warehouses">
                            @foreach ($warehouses as $wh)
                            <option value="warehouse:{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </optgroup>
                        @endif
                        @if ($shops->isNotEmpty())
                        <optgroup label="Shops">
                            @foreach ($shops as $shop)
                            <option value="shop:{{ $shop->id }}">{{ $shop->name }}</option>
                            @endforeach
                        </optgroup>
                        @endif
                    </select>
                </div>
            </div>
            @if ($dateRange === 'custom')
            <div class="rb-form-row">
                <div>
                    <label class="rb-label">From</label>
                    <input type="date" class="rb-input" wire:model="dateFrom">
                </div>
                <div>
                    <label class="rb-label">To</label>
                    <input type="date" class="rb-input" wire:model="dateTo">
                </div>
            </div>
            @endif
            <div class="rb-form-row">
                <div>
                    <label class="rb-label">Period Comparison</label>
                    <select class="rb-select" wire:model="comparisonMode">
                        <option value="none">None</option>
                        <option value="prior_period">Prior Period</option>
                        <option value="prior_year">Prior Year</option>
                    </select>
                </div>
                <div style="display:flex;align-items:flex-end;padding-bottom:2px">
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
                        <input type="checkbox" wire:model="isShared" style="accent-color:var(--accent)">
                        <span style="color:var(--text)">Share with all owners</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Canvas blocks --}}
        <div class="rb-canvas-body">
            @error('canvas') <div class="rb-error" style="margin-bottom:8px">{{ $message }}</div> @enderror

            @if (empty($canvas))
            <div class="rb-empty-canvas">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:10px;opacity:.3"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/></svg>
                <div style="font-size:14px;font-weight:600;margin-bottom:4px">Canvas is empty</div>
                <div style="font-size:12px">Add blocks from the catalogue on the left</div>
            </div>
            @else
            @foreach ($canvas as $idx => $block)
            @php $meta = $flatCatalogue[$block['metric_id']] ?? null; @endphp
            <div class="rb-block-card">
                <div class="rb-block-top">
                    <input type="text" class="rb-block-title-input"
                           value="{{ $block['title'] }}"
                           wire:change="updateBlockTitle('{{ $block['id'] }}', $event.target.value)">
                    <div style="display:flex;align-items:center;gap:4px">
                        <button class="rb-block-btn" wire:click="moveBlockUp('{{ $block['id'] }}')"
                                {{ $idx === 0 ? 'disabled' : '' }} title="Move up">↑</button>
                        <button class="rb-block-btn" wire:click="moveBlockDown('{{ $block['id'] }}')"
                                {{ $idx === count($canvas) - 1 ? 'disabled' : '' }} title="Move down">↓</button>
                        <button class="rb-block-remove" wire:click="removeBlock('{{ $block['id'] }}')">✕ Remove</button>
                    </div>
                </div>
                <div class="rb-block-controls">
                    <span style="font-size:11px;color:var(--text-dim);margin-right:4px">Width:</span>
                    <button class="rb-block-btn {{ $block['width'] === 'half' ? 'active' : '' }}"
                            wire:click="updateBlockWidth('{{ $block['id'] }}','half')">Half</button>
                    <button class="rb-block-btn {{ $block['width'] === 'full' ? 'active' : '' }}"
                            wire:click="updateBlockWidth('{{ $block['id'] }}','full')">Full</button>

                    @if ($meta && count($meta['viz_options']) > 1)
                    <span style="font-size:11px;color:var(--text-dim);margin-left:8px;margin-right:4px">Viz:</span>
                    @foreach ($meta['viz_options'] as $vizOpt)
                    @php $vizLabels = ['kpi_card'=>'KPI Card','table'=>'Table','bar_chart'=>'Bar Chart','line_chart'=>'Line Chart'] @endphp
                    <button class="rb-block-btn {{ $block['viz'] === $vizOpt ? 'active' : '' }}"
                            wire:click="updateBlockViz('{{ $block['id'] }}','{{ $vizOpt }}')">
                        {{ $vizLabels[$vizOpt] ?? $vizOpt }}
                    </button>
                    @endforeach
                    @endif
                </div>
                <div class="rb-block-meta" style="margin-top:8px">
                    @if ($meta)
                    <span class="rb-block-domain">{{ $meta['domain'] }}</span>
                    <span>{{ $meta['description'] }}</span>
                    @endif
                </div>

                {{-- Text block content editor --}}
                @if ($block['metric_id'] === 'text_block')
                <div style="margin-top:10px">
                    <label class="rb-label">Block Content</label>
                    <textarea class="rb-input" rows="4" placeholder="Write narrative, notes, or context…"
                              style="resize:vertical;font-family:var(--font)"
                              wire:change="updateBlockContent('{{ $block['id'] }}', $event.target.value)">{{ $block['content'] ?? '' }}</textarea>
                </div>
                @else

                {{-- Advanced options toggle --}}
                <div class="rb-block-advanced" x-data="{ open: false }">
                    <button class="rb-block-advanced-toggle" type="button" @click="open = !open">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" x-bind:style="open ? 'transform:rotate(90deg)' : ''"><polyline points="9 18 15 12 9 6"/></svg>
                        Advanced options
                    </button>
                    <div x-show="open" style="margin-top:8px;display:flex;flex-direction:column;gap:8px">

                        {{-- Location/date overrides --}}
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                            <div>
                                <label class="rb-label" style="font-size:10px">Location Override</label>
                                <select class="rb-select" style="font-size:12px;padding:5px 8px"
                                        wire:change="updateBlockOverride('{{ $block['id'] }}', 'location_filter_override', $event.target.value)">
                                    <option value="">Use report default</option>
                                    <option value="all" {{ ($block['location_filter_override'] ?? '') === 'all' ? 'selected' : '' }}>All Locations</option>
                                    @if ($warehouses->isNotEmpty())
                                    <optgroup label="Warehouses">
                                        @foreach ($warehouses as $wh)
                                        <option value="warehouse:{{ $wh->id }}" {{ ($block['location_filter_override'] ?? '') === 'warehouse:'.$wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    @endif
                                    @if ($shops->isNotEmpty())
                                    <optgroup label="Shops">
                                        @foreach ($shops as $shop)
                                        <option value="shop:{{ $shop->id }}" {{ ($block['location_filter_override'] ?? '') === 'shop:'.$shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    @endif
                                </select>
                            </div>
                            <div>
                                <label class="rb-label" style="font-size:10px">Date Range Override</label>
                                <select class="rb-select" style="font-size:12px;padding:5px 8px"
                                        wire:change="updateBlockOverride('{{ $block['id'] }}', 'date_range_override', $event.target.value)">
                                    <option value="">Use report default</option>
                                    @foreach (['today'=>'Today','week'=>'This Week','month'=>'This Month','quarter'=>'This Quarter','year'=>'This Year'] as $drk => $drl)
                                    <option value="{{ $drk }}" {{ ($block['date_range_override'] ?? '') === $drk ? 'selected' : '' }}>{{ $drl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if (isset($meta) && in_array($block['viz'] ?? '', ['kpi_card','scorecard']))
                        {{-- Threshold inputs --}}
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                            <div>
                                <label class="rb-label" style="font-size:10px">⚠ Warning Threshold (RWF)</label>
                                <input type="number" class="rb-input" style="font-size:12px;padding:5px 8px"
                                       placeholder="e.g. 100000"
                                       value="{{ $block['threshold_warning'] ?? '' }}"
                                       wire:change="updateBlockThreshold('{{ $block['id'] }}', 'threshold_warning', $event.target.value)">
                            </div>
                            <div>
                                <label class="rb-label" style="font-size:10px">🔴 Critical Threshold (RWF)</label>
                                <input type="number" class="rb-input" style="font-size:12px;padding:5px 8px"
                                       placeholder="e.g. 50000"
                                       value="{{ $block['threshold_critical'] ?? '' }}"
                                       wire:change="updateBlockThreshold('{{ $block['id'] }}', 'threshold_critical', $event.target.value)">
                            </div>
                        </div>
                        @endif

                        @if (isset($meta) && in_array($block['viz'] ?? '', ['table','bar_chart']))
                        {{-- Sort / limit controls --}}
                        <div style="display:grid;grid-template-columns:1fr 1fr 80px;gap:8px">
                            <div>
                                <label class="rb-label" style="font-size:10px">Sort Column</label>
                                <input type="text" class="rb-input" style="font-size:12px;padding:5px 8px"
                                       placeholder="e.g. revenue"
                                       value="{{ $block['block_options']['sort_by'] ?? '' }}"
                                       wire:change="updateBlockOption('{{ $block['id'] }}', 'sort_by', $event.target.value)">
                            </div>
                            <div>
                                <label class="rb-label" style="font-size:10px">Sort Direction</label>
                                <select class="rb-select" style="font-size:12px;padding:5px 8px"
                                        wire:change="updateBlockOption('{{ $block['id'] }}', 'sort_direction', $event.target.value)">
                                    <option value="">Default</option>
                                    <option value="desc" {{ ($block['block_options']['sort_direction'] ?? '') === 'desc' ? 'selected' : '' }}>Desc</option>
                                    <option value="asc"  {{ ($block['block_options']['sort_direction'] ?? '') === 'asc'  ? 'selected' : '' }}>Asc</option>
                                </select>
                            </div>
                            <div>
                                <label class="rb-label" style="font-size:10px">Limit rows</label>
                                <input type="number" min="1" max="100" class="rb-input" style="font-size:12px;padding:5px 8px"
                                       placeholder="All"
                                       value="{{ $block['block_options']['limit'] ?? '' }}"
                                       wire:change="updateBlockOption('{{ $block['id'] }}', 'limit', $event.target.value)">
                            </div>
                        </div>
                        @endif

                        {{-- Show if nonzero --}}
                        <label style="display:flex;align-items:center;gap:8px;font-size:12px;cursor:pointer;color:var(--text-dim)">
                            <input type="checkbox" style="accent-color:var(--accent)"
                                   {{ !empty($block['show_if_nonzero']) ? 'checked' : '' }}
                                   wire:click="updateBlockShowIfNonzero('{{ $block['id'] }}', {{ empty($block['show_if_nonzero']) ? 'true' : 'false' }})">
                            Hide this block when value is zero
                        </label>

                    </div>
                </div>
                @endif
            </div>
            @endforeach
            @endif
        </div>

        {{-- Schedule section --}}
        <div style="padding:14px 20px;border-top:1px solid var(--border);background:var(--surface2)">
            <div style="font-size:11px;font-weight:800;color:var(--text-dim);text-transform:uppercase;letter-spacing:.7px;margin-bottom:10px">
                Scheduled Delivery (optional)
            </div>
            <div class="rb-form-row">
                <div>
                    <label class="rb-label">Cron Expression</label>
                    <input type="text" class="rb-input" placeholder="e.g. 0 8 1 * * (1st of month 8am)"
                           wire:model="scheduleCron">
                    <div style="font-size:10px;color:var(--text-dim);margin-top:3px">Leave blank to disable</div>
                </div>
                <div>
                    <label class="rb-label">Email Recipients</label>
                    <input type="text" class="rb-input" placeholder="alice@example.com, bob@example.com"
                           wire:model="scheduleRecipients">
                    <div style="font-size:10px;color:var(--text-dim);margin-top:3px">Comma-separated</div>
                </div>
            </div>
        </div>

        <div class="rb-canvas-footer">
            <button class="rb-save-btn" wire:click="save" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">{{ $editingReportId ? 'Update Report' : 'Save Report' }}</span>
                <span wire:loading wire:target="save" style="display:none">Saving…</span>
            </button>
            <span style="font-size:13px;color:var(--text-dim)">{{ count($canvas) }} {{ Str::plural('block', count($canvas)) }}</span>
        </div>
    </div>
</div>

</div>
