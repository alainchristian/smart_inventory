{{-- ┌─────────────────────────────────────────────────────────────────────────┐
    │  Owner · Report Viewer                                                  │
    │  Runs and displays a saved custom report                                │
    └─────────────────────────────────────────────────────────────────────────┘ --}}
<div>
<style>
.rv-page-title { font-size:22px;font-weight:700;color:var(--text);letter-spacing:-0.5px;margin:0 0 4px }
.rv-page-subtitle { font-size:13px;color:var(--text-sub) }
.rv-hdr { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px;flex-wrap:wrap }
.rv-hdr-meta { display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:10px }
.rv-chip { display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.3px;background:var(--surface2);color:var(--text-sub);border:1px solid var(--border) }
.rv-run-btn { padding:10px 22px;background:var(--accent);color:#fff;border:none;border-radius:var(--rsm);font-size:14px;font-weight:700;cursor:pointer }
.rv-run-btn:hover { opacity:.9 }
.rv-run-btn:disabled { opacity:.5;cursor:default }
.rv-edit-btn { display:inline-flex;align-items:center;gap:6px;padding:9px 18px;background:transparent;border:1px solid var(--border);border-radius:var(--rsm);font-size:13px;color:var(--text-sub);text-decoration:none;cursor:pointer }
.rv-edit-btn:hover { background:var(--surface2);color:var(--text) }
.rv-placeholder { text-align:center;padding:80px 20px;border:2px dashed var(--border);border-radius:var(--r);margin-top:8px }
.rv-results { display:flex;flex-wrap:wrap;gap:16px;margin-top:8px }
.rv-block-half { width:calc(50% - 8px);box-sizing:border-box }
.rv-block-full { width:100%;box-sizing:border-box }
.rv-block-card { background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden }
.rv-block-header { padding:12px 16px;border-bottom:1px solid var(--border);font-size:14px;font-weight:700;color:var(--text) }
.rv-block-body { padding:16px }
.rv-kpi-value { font-size:28px;font-weight:700;color:var(--text);letter-spacing:-1px;margin:0 }
.rv-kpi-sub { font-size:12px;color:var(--text-dim);margin-top:4px }
.rv-table { width:100%;border-collapse:collapse }
.rv-table thead th { font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;padding:8px 10px;border-bottom:1px solid var(--border);text-align:left;white-space:nowrap }
.rv-table tbody td { font-size:13px;color:var(--text);padding:8px 10px;border-bottom:1px solid var(--border);vertical-align:middle }
.rv-table tbody tr:last-child td { border-bottom:none }
.rv-table-scroll { overflow-x:auto;-webkit-overflow-scrolling:touch }
.rv-error-card { background:var(--danger-glow);border:1px solid var(--red-dim);border-radius:var(--rsm);padding:14px;color:var(--red) }
.rv-chart-wrap { position:relative;height:240px }
.rv-cache-badge { display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:var(--success-glow);color:var(--success);border:1px solid var(--success) }
.rv-icon-btn { display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:transparent;border:1px solid var(--border);border-radius:var(--rsm);font-size:13px;color:var(--text-sub);cursor:pointer;text-decoration:none }
.rv-icon-btn:hover { background:var(--surface2);color:var(--text) }
.rv-icon-btn.active { background:var(--accent-dim);color:var(--accent);border-color:var(--accent-dim) }
.rv-kpi-delta { display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700;padding:2px 7px;border-radius:10px;margin-left:8px }
.rv-kpi-delta.up { background:rgba(0,200,100,.12);color:var(--success) }
.rv-kpi-delta.down { background:rgba(220,50,50,.1);color:var(--red) }
.rv-threshold-dot { display:inline-block;width:10px;height:10px;border-radius:50%;margin-left:8px;vertical-align:middle }
.rv-threshold-dot.ok { background:var(--success) }
.rv-threshold-dot.warn { background:var(--amber) }
.rv-threshold-dot.crit { background:var(--red) }
.rv-text-block { font-size:14px;line-height:1.7;color:var(--text);white-space:pre-wrap }
.rv-annotate-btn { background:transparent;border:none;cursor:pointer;color:var(--text-dim);font-size:11px;padding:2px 6px;border-radius:4px }
.rv-annotate-btn:hover { background:var(--surface2);color:var(--accent) }
.rv-drawer { position:fixed;top:0;right:0;width:380px;max-width:100%;height:100vh;background:var(--surface);border-left:1px solid var(--border);z-index:9999;box-shadow:-4px 0 20px rgba(0,0,0,.12);overflow-y:auto;display:flex;flex-direction:column }
.rv-drawer-hdr { padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between }
.rv-drawer-title { font-size:15px;font-weight:700;color:var(--text) }
.rv-drawer-close { background:transparent;border:none;cursor:pointer;color:var(--text-dim);font-size:20px;line-height:1 }
.rv-history-item { padding:12px 20px;border-bottom:1px solid var(--border);cursor:pointer }
.rv-history-item:hover { background:var(--surface2) }
.rv-history-item.active { background:var(--accent-dim) }
.rv-annotation-form { padding:16px 20px;border-bottom:1px solid var(--border) }
@@media(max-width:840px) {
    .rv-block-half { width:100% }
    .rv-drawer { width:100% }
}
</style>

@php
function extractKpiValue(string $metricId, array $data): string {
    return match($metricId) {
        'sales_revenue'           => number_format($data['total_revenue'] ?? 0) . ' RWF',
        'sales_gross_profit'      => number_format($data['gross_profit'] ?? 0) . ' RWF',
        'sales_transaction_count' => number_format($data['transactions_count'] ?? 0),
        'sales_avg_basket'        => number_format($data['avg_basket'] ?? 0) . ' RWF',
        'inventory_cost_value'    => number_format($data['purchase_value'] ?? 0) . ' RWF',
        'inventory_retail_value'  => number_format($data['retail_value'] ?? 0) . ' RWF',
        'inventory_fill_rate'     => round($data['fill_rate'] ?? 0, 1) . '%',
        'inventory_dead_stock'    => ($data['dead_stock_count'] ?? 0) . ' products',
        'loss_total'              => number_format(($data['total_refunds'] ?? 0) + ($data['damaged_loss'] ?? 0)) . ' RWF',
        'loss_return_rate'        => round($data['return_rate'] ?? 0, 1) . '%',
        'loss_damaged_value'      => number_format($data['damaged_loss'] ?? 0) . ' RWF',
        'loss_shrinkage'          => round($data['shrinkage_pct'] ?? 0, 2) . '%',
        'ops_stock_turnover'      => round($data['turnover_rate'] ?? 0, 2) . '×',
        'ops_damaged_pending'     => ($data['count'] ?? 0) . ' items',
        'ops_low_stock_count'     => ($data['low_stock_count'] ?? 0) . ' products',
        'sales_voided'            => number_format($data['voided_count'] ?? 0) . ' transactions',
        'replenishment_critical'  => count($data) . ' products',
        'transfers_kpis'          => number_format($data['total_transfers'] ?? 0) . ' transfers',
        default                   => (string) (collect($data)->first(fn($v) => is_numeric($v)) ?? '—'),
    };
}

function extractKpiSub(string $metricId, array $data): string {
    return match($metricId) {
        'sales_revenue'           => 'Growth: ' . round($data['growth_percentage'] ?? 0, 1) . '%',
        'sales_gross_profit'      => 'Margin: ' . round($data['margin_pct'] ?? 0, 1) . '%',
        'sales_transaction_count' => 'Previous: ' . number_format($data['previous_transactions'] ?? 0),
        'sales_avg_basket'        => 'Based on ' . number_format($data['transactions_count'] ?? 0) . ' transactions',
        'inventory_cost_value'    => 'Potential profit: ' . number_format($data['potential_profit'] ?? 0) . ' RWF',
        'inventory_retail_value'  => 'Cost: ' . number_format($data['purchase_value'] ?? 0) . ' RWF',
        'inventory_fill_rate'     => 'Items vs box capacity',
        'inventory_dead_stock'    => 'No sales in 90 days',
        'loss_total'              => 'Refunds: ' . number_format($data['total_refunds'] ?? 0) . ' RWF',
        'loss_return_rate'        => number_format($data['returns_count'] ?? 0) . ' returns',
        'loss_shrinkage'          => number_format($data['items_damaged_90d'] ?? 0) . ' items damaged in 90 days',
        'ops_stock_turnover'      => 'Annual COGS ÷ avg inventory',
        'transfers_kpis'          => 'Discrepancy rate: ' . round($data['discrepancy_rate'] ?? 0, 1) . '%',
        default                   => '',
    };
}
@endphp

{{-- ══════════════════════════════════════════════════════════════════════════
     PAGE HEADER
══════════════════════════════════════════════════════════════════════════ --}}

@if (session('success'))
<div style="padding:10px 16px;background:var(--success-glow);border:1px solid var(--success);border-radius:var(--rsm);font-size:13px;color:var(--success);margin-bottom:14px">{{ session('success') }}</div>
@endif
@if (session('error'))
<div style="padding:10px 16px;background:var(--danger-glow);border:1px solid var(--red-dim);border-radius:var(--rsm);font-size:13px;color:var(--red);margin-bottom:14px">{{ session('error') }}</div>
@endif

<div class="rv-hdr">
    <div style="flex:1;min-width:0">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px">
            <a href="{{ route('owner.reports.custom.library') }}" class="rv-edit-btn" wire:navigate style="padding:5px 10px;font-size:12px">← Library</a>
            <h1 class="rv-page-title">{{ $report->name }}</h1>
            @if ($report->is_shared)
            <span class="rv-chip" style="background:var(--accent-dim);color:var(--accent)">Shared</span>
            @endif
            @if ($report->pinned_to_dashboard)
            <span class="rv-chip" style="background:var(--amber-dim,rgba(255,180,0,.12));color:var(--amber)">📌 Pinned</span>
            @endif
        </div>
        @if ($report->description)
        <p class="rv-page-subtitle">{{ $report->description }}</p>
        @endif
        <div class="rv-hdr-meta">
            @php
                $cfg = $report->resolvedConfig();
                $rangeLabels = ['today'=>'Today','week'=>'This week','month'=>'This month','quarter'=>'This quarter','year'=>'This year','custom'=>'Custom'];
            @endphp
            <span class="rv-chip">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                {{ $rangeLabels[$cfg['date_range']] ?? $cfg['date_range'] }}
            </span>
            <span class="rv-chip">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                {{ $cfg['location_filter'] === 'all' ? 'All Locations' : $cfg['location_filter'] }}
            </span>
            @if (!empty($cfg['comparison_mode']) && $cfg['comparison_mode'] !== 'none')
            <span class="rv-chip" style="background:var(--violet-dim,rgba(130,80,255,.1));color:var(--violet)">vs {{ $cfg['comparison_mode'] === 'prior_year' ? 'Prior Year' : 'Prior Period' }}</span>
            @endif
            <span class="rv-chip">{{ $report->blockCount() }} blocks</span>
            @if ($report->hasFreshCache())
            <span class="rv-cache-badge">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Cached {{ $report->results_cached_at->diffForHumans() }}
            </span>
            @elseif ($report->last_run_at)
            <span class="rv-chip">Last run {{ $report->last_run_at->diffForHumans() }} · {{ $report->run_count }}×</span>
            @endif
            <span class="rv-chip">By {{ $report->creator->name ?? 'Unknown' }}</span>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;flex-wrap:wrap">
        {{-- Export CSV --}}
        @if ($hasRun)
        <button class="rv-icon-btn" wire:click="exportCsv" title="Export CSV">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            CSV
        </button>
        {{-- Print --}}
        <a href="{{ route('owner.reports.custom.print', $report->id) }}" target="_blank" class="rv-icon-btn" title="Print / PDF">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Print
        </a>
        @endif
        {{-- History --}}
        <button class="rv-icon-btn {{ $showHistory ? 'active' : '' }}" wire:click="toggleHistory" title="Run History">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            History
        </button>
        {{-- Pin --}}
        @if ($report->created_by === auth()->id())
        <button class="rv-icon-btn {{ $report->pinned_to_dashboard ? 'active' : '' }}" wire:click="togglePin" title="{{ $report->pinned_to_dashboard ? 'Unpin from dashboard' : 'Pin to dashboard' }}">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v8l3.5 3.5M8 9h8M12 2L8 6M12 2l4 4M12 22v-4"/></svg>
            {{ $report->pinned_to_dashboard ? 'Unpin' : 'Pin' }}
        </button>
        @endif
        {{-- Edit --}}
        @if ($report->created_by === auth()->id())
        <a href="{{ route('owner.reports.custom.builder') }}?reportId={{ $report->id }}" class="rv-icon-btn" wire:navigate>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
        </a>
        @endif
        {{-- Run --}}
        <button class="rv-run-btn" wire:click="run" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="run" style="display:inline">▶ Run Report</span>
            <span wire:loading wire:target="run" style="display:none">Running…</span>
        </button>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     HISTORY DRAWER
══════════════════════════════════════════════════════════════════════════ --}}
@if ($showHistory)
<div class="rv-drawer">
    <div class="rv-drawer-hdr">
        <span class="rv-drawer-title">Run History</span>
        <button class="rv-drawer-close" wire:click="toggleHistory">×</button>
    </div>
    <div style="flex:1;overflow-y:auto">
        @forelse ($report->runHistory()->limit(12)->get() as $run)
        <div class="rv-history-item {{ $viewingHistoryId === $run->id ? 'active' : '' }}"
             wire:click="viewHistoryRun({{ $run->id }})">
            <div style="font-size:13px;font-weight:600;color:var(--text)">
                {{ $run->run_at->format('d M Y H:i') }}
                @if ($run->was_scheduled) <span style="font-size:10px;color:var(--accent);font-weight:700;margin-left:4px">SCHEDULED</span> @endif
            </div>
            <div style="font-size:12px;color:var(--text-dim);margin-top:2px">
                By {{ $run->runner->name ?? 'System' }} · {{ $run->duration_ms }}ms
            </div>
        </div>
        @empty
        <div style="padding:20px;text-align:center;font-size:13px;color:var(--text-dim)">No history yet</div>
        @endforelse
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     ANNOTATION FORM MODAL
══════════════════════════════════════════════════════════════════════════ --}}
@if ($showAnnotationForm)
<div style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:10000;display:flex;align-items:center;justify-content:center">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:24px;width:440px;max-width:90vw">
        <div style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:12px">Add Annotation</div>
        <textarea wire:model="annotationText" rows="4"
                  style="width:100%;padding:10px 12px;background:var(--surface2);border:1px solid var(--border);border-radius:var(--rsm);font-size:13px;color:var(--text);resize:vertical;box-sizing:border-box"
                  placeholder="Add a note for this block…"></textarea>
        <div style="display:flex;gap:8px;margin-top:12px;justify-content:flex-end">
            <button wire:click="$set('showAnnotationForm',false)" style="padding:8px 16px;background:transparent;border:1px solid var(--border);border-radius:var(--rsm);font-size:13px;cursor:pointer;color:var(--text-sub)">Cancel</button>
            <button wire:click="saveAnnotation" style="padding:8px 16px;background:var(--accent);color:#fff;border:none;border-radius:var(--rsm);font-size:13px;font-weight:700;cursor:pointer">Save</button>
        </div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     RESULTS
══════════════════════════════════════════════════════════════════════════ --}}
@if (! $hasRun)
<div class="rv-placeholder">
    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
         style="margin:0 auto 14px;display:block;color:var(--text-dim);opacity:.4">
        <polygon points="5 3 19 12 5 21 5 3"/>
    </svg>
    <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Click Run Report to generate results</div>
    <div style="font-size:13px;color:var(--text-dim)">This report has {{ $report->blockCount() }} metric {{ Str::plural('block', $report->blockCount()) }}</div>
</div>
@else
<div class="rv-results">
    @foreach ($results as $blockResult)
    @php
        $block    = $blockResult['block'];
        $meta     = $blockResult['meta'];
        $data     = $blockResult['data'];
        $viz      = $block['viz'];
        $width    = $block['width'] ?? 'half';
        $metricId = $block['metric_id'];
        $wClass   = $width === 'full' ? 'rv-block-full' : 'rv-block-half';

        // Step 17: Conditional visibility — skip block if show_if_nonzero and all numeric values are zero
        if (!empty($block['show_if_nonzero'])) {
            $hasNonZero = collect($data)
                ->filter(fn($v, $k) => !str_starts_with((string)$k, '_') && is_numeric($v) && $v != 0)
                ->isNotEmpty();
            if (!$hasNonZero) continue;
        }
    @endphp
    <div class="{{ $wClass }}">
        <div class="rv-block-card">
            <div class="rv-block-header" style="display:flex;align-items:center;justify-content:space-between;gap:8px">
                <span>{{ $block['title'] }}</span>
                <div style="display:flex;align-items:center;gap:6px">
                    {{-- Annotation button --}}
                    <button class="rv-annotate-btn" wire:click="openAnnotation('{{ $block['id'] }}')" title="Add annotation">+ note</button>
                    {{-- Threshold dot (Step 11) --}}
                    @php
                        $threshWarn = $block['threshold_warning'] ?? null;
                        $threshCrit = $block['threshold_critical'] ?? null;
                        if (($threshWarn || $threshCrit) && $viz === 'kpi_card' && !isset($data['error'])) {
                            $numVal = collect($data)->filter(fn($v, $k) => !str_starts_with($k, '_') && is_numeric($v))->first();
                            if ($numVal !== null) {
                                if ($threshCrit !== null && $numVal >= $threshCrit) { $dotClass = 'crit'; }
                                elseif ($threshWarn !== null && $numVal >= $threshWarn) { $dotClass = 'warn'; }
                                else { $dotClass = 'ok'; }
                            } else { $dotClass = null; }
                        } else { $dotClass = null; }
                    @endphp
                    @if ($dotClass)
                    <span class="rv-threshold-dot {{ $dotClass }}" title="Threshold status: {{ $dotClass }}"></span>
                    @endif
                </div>
            </div>
            <div class="rv-block-body">

                {{-- ERROR --}}
                @if (isset($data['error']))
                <div class="rv-error-card">
                    <strong>Block failed to run:</strong> {{ $data['error'] }}
                </div>

                {{-- TEXT BLOCK --}}
                @elseif ($viz === 'text')
                <div class="rv-text-block">{{ $block['content'] ?? '' }}</div>

                {{-- KPI CARD --}}
                @elseif ($viz === 'kpi_card')
                @php
                    $kpiVal = extractKpiValue($metricId, $data);
                    $kpiSub = extractKpiSub($metricId, $data);
                    // Comparison delta
                    $compData = $data['_comparison'] ?? null;
                    $compPeriod = $data['_comparison_period'] ?? null;
                    $delta = null;
                    if ($compData && is_array($compData)) {
                        $curNum = collect($data)->filter(fn($v,$k)=>!str_starts_with($k,'_')&&is_numeric($v))->first();
                        $prevNum = collect($compData)->filter(fn($v,$k)=>!str_starts_with($k,'_')&&is_numeric($v))->first();
                        if ($prevNum != 0 && $prevNum !== null && $curNum !== null) {
                            $delta = round((($curNum - $prevNum) / abs($prevNum)) * 100, 1);
                        }
                    }
                @endphp
                <div style="display:flex;align-items:flex-start;gap:8px;flex-wrap:wrap">
                    <p class="rv-kpi-value">{{ $kpiVal }}</p>
                    @if ($delta !== null)
                    <span class="rv-kpi-delta {{ $delta >= 0 ? 'up' : 'down' }}">
                        {{ $delta >= 0 ? '▲' : '▼' }} {{ abs($delta) }}%
                    </span>
                    @endif
                </div>
                @if ($kpiSub) <p class="rv-kpi-sub">{{ $kpiSub }}</p> @endif
                @if ($compPeriod) <p class="rv-kpi-sub" style="margin-top:4px">vs {{ $compPeriod }}</p> @endif

                {{-- TABLE --}}
                @elseif ($viz === 'table')
                @php
                    $rows = is_array($data) && isset($data[0]) ? $data : [$data];
                    $keys = !empty($rows[0]) && is_array($rows[0]) ? array_filter(array_keys((array)$rows[0]), fn($k)=>!str_starts_with($k,'_')) : [];
                @endphp
                @if (empty($rows) || empty($keys))
                <div style="font-size:13px;color:var(--text-dim);padding:8px 0">No data available</div>
                @else
                <div class="rv-table-scroll">
                    <table class="rv-table">
                        <thead>
                            <tr>
                                @foreach ($keys as $k)
                                <th>{{ ucwords(str_replace('_',' ',$k)) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                            <tr>
                                @foreach ($keys as $k)
                                <td>
                                    @php $v = is_array($row) ? ($row[$k] ?? '—') : (is_object($row) ? ($row->$k ?? '—') : '—') @endphp
                                    @if (is_numeric($v) && !is_bool($v)) {{ number_format((float)$v) }}
                                    @elseif (is_bool($v)) {{ $v ? 'Yes' : 'No' }}
                                    @elseif (is_null($v)) —
                                    @else {{ $v }}
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                {{-- BAR CHART / LINE CHART --}}
                @elseif (in_array($viz, ['bar_chart', 'line_chart']))
                @php
                    $chartId = 'chart_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $block['id']);
                    $rows = is_array($data) && isset($data[0]) ? $data : [];

                    // Determine labels and dataset based on metric_id
                    if ($metricId === 'sales_revenue_trend') {
                        $labels   = array_column($rows, 'date');
                        $datasets = [['label'=>'Revenue','data'=>array_map(fn($r)=>$r['revenue']??0, $rows),'bg'=>'var(--accent)']];
                    } elseif ($metricId === 'sales_by_shop') {
                        $labels   = array_map(fn($r)=>is_array($r)?($r['shop_name']??''):(is_object($r)?$r->shop_name??'':''), $rows);
                        $datasets = [['label'=>'Revenue','data'=>array_map(fn($r)=>is_array($r)?($r['revenue']??0):(is_object($r)?$r->revenue??0:0), $rows),'bg'=>'var(--accent)']];
                    } elseif ($metricId === 'sales_top_products') {
                        $labels   = array_map(fn($r)=>is_array($r)?($r['product_name']??''):(is_object($r)?$r->product_name??'':''), $rows);
                        $datasets = [['label'=>'Revenue','data'=>array_map(fn($r)=>is_array($r)?($r['revenue']??0):(is_object($r)?$r->revenue??0:0), $rows),'bg'=>'var(--accent)']];
                    } elseif ($metricId === 'sales_payment_methods') {
                        $labels   = array_map(fn($r)=>is_array($r)?($r['label']??''):(is_object($r)?$r->label??'':''), $rows);
                        $datasets = [['label'=>'Revenue','data'=>array_map(fn($r)=>is_array($r)?($r['revenue']??0):(is_object($r)?$r->revenue??0:0), $rows),'bg'=>'var(--accent)']];
                    } elseif ($metricId === 'inventory_aging') {
                        $labels   = array_map(fn($r)=>is_array($r)?($r['age_bracket']??''):(is_object($r)?$r->age_bracket??'':''), $rows);
                        $datasets = [['label'=>'Boxes','data'=>array_map(fn($r)=>is_array($r)?($r['box_count']??0):(is_object($r)?$r->box_count??0:0), $rows),'bg'=>'var(--amber)']];
                    } elseif ($metricId === 'inventory_category_concentration') {
                        $labels   = array_map(fn($r)=>is_array($r)?($r['category_name']??''):(is_object($r)?$r->category_name??'':''), $rows);
                        $datasets = [['label'=>'Cost Value','data'=>array_map(fn($r)=>is_array($r)?($r['cost_value']??0):(is_object($r)?$r->cost_value??0:0), $rows),'bg'=>'var(--violet)']];
                    } elseif ($metricId === 'transfers_routes') {
                        $labels   = array_map(fn($r)=>is_array($r)?($r['warehouse_name']??''.' → '.($r['shop_name']??'')):(is_object($r)?($r->warehouse_name??'').' → '.($r->shop_name??''):''), $rows);
                        $datasets = [['label'=>'Transfers','data'=>array_map(fn($r)=>is_array($r)?($r['transfer_count']??0):(is_object($r)?$r->transfer_count??0:0), $rows),'bg'=>'var(--accent)']];
                    } else {
                        // Generic: use first string key as label, first numeric key as value
                        $keys = !empty($rows[0]) ? array_keys((array)$rows[0]) : [];
                        $labelKey = collect($keys)->first(fn($k)=>is_string(is_array($rows[0])?($rows[0][$k]??null):null)) ?? ($keys[0]??'label');
                        $valKey   = collect($keys)->first(fn($k)=>is_numeric(is_array($rows[0])?($rows[0][$k]??null):null)) ?? ($keys[1]??'value');
                        $labels   = array_map(fn($r)=>is_array($r)?($r[$labelKey]??''):'', $rows);
                        $datasets = [['label'=>ucwords(str_replace('_',' ',$valKey)),'data'=>array_map(fn($r)=>is_array($r)?($r[$valKey]??0):0, $rows),'bg'=>'var(--accent)']];
                    }
                @endphp
                <div class="rv-chart-wrap">
                    <canvas id="{{ $chartId }}"
                            data-type="{{ $viz === 'line_chart' ? 'line' : 'bar' }}"
                            data-labels="{{ json_encode($labels) }}"
                            data-datasets="{{ json_encode($datasets) }}"></canvas>
                </div>
                @endif

            </div>{{-- /rv-block-body --}}
        </div>{{-- /rv-block-card --}}
    </div>
    @endforeach
</div>

@script
<script>
(function() {
    document.querySelectorAll('[data-labels]').forEach(function(canvas) {
        var orphan = Chart.getChart(canvas);
        if (orphan) orphan.destroy();

        var type     = canvas.dataset.type || 'bar';
        var labels   = JSON.parse(canvas.dataset.labels   || '[]');
        var rawDs    = JSON.parse(canvas.dataset.datasets || '[]');

        var datasets = rawDs.map(function(d) {
            var isLine = type === 'line';
            return {
                label: d.label,
                data:  d.data,
                backgroundColor:   isLine ? 'transparent' : d.bg,
                borderColor:       d.bg,
                borderWidth:       isLine ? 2 : 0,
                borderRadius:      isLine ? 0 : 4,
                fill:              false,
                tension:           0.3,
                pointRadius:       isLine ? 3 : 0,
            };
        });

        canvas._chartInstance = new Chart(canvas, {
            type: type,
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { font: { size: 12 }, padding: 14, usePointStyle: true } },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 }, maxRotation: 45 } },
                    y: { beginAtZero: true, grid: { color: 'rgba(128,128,128,.08)' }, ticks: { font: { size: 11 } } }
                }
            }
        });
    });
})();
</script>
@endscript
@endif

</div>
