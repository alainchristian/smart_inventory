{{-- ┌─────────────────────────────────────────────────────────────────────────┐
    │  Owner · Inventory Report                                               │
    │  Tabs: Overview · Valuation · Stock Health · Replenishment             │
    │  Design system: var(--*) tokens, .bkpi pattern                        │
    └─────────────────────────────────────────────────────────────────────────┘ --}}
<div>
<style>
.iv-page-title { font-size:24px;font-weight:700;color:var(--text);letter-spacing:-0.5px;margin:0 0 4px }
.iv-page-subtitle { font-size:13px;color:var(--text-dim);font-family:var(--mono) }
.iv-tab-btn { font-size:13px !important; }
.iv-tab-btn svg { width:15px !important; height:15px !important; }
.iv-section-title { font-size:15px;font-weight:700;color:var(--text);margin:0 0 14px }
.iv-section-sub { font-size:12px;color:var(--text-dim);margin:-10px 0 14px }
.iv-kpi-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:12px;margin-bottom:28px }
.iv-table { width:100%;border-collapse:collapse }
.iv-table thead th { font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;padding:10px 12px;border-bottom:1px solid var(--border);text-align:left;white-space:nowrap }
.iv-table thead th.r { text-align:right }
.iv-table tbody td { font-size:13px;color:var(--text);padding:10px 12px;border-bottom:1px solid var(--border);vertical-align:middle }
.iv-table tbody td.r { text-align:right }
.iv-table tbody tr:last-child td { border-bottom:none }
.iv-table tfoot td { font-size:13px;font-weight:700;color:var(--text);padding:10px 12px;border-top:1px solid var(--border) }
.iv-table tfoot td.r { text-align:right }
.iv-card { background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:20px }
.iv-two-col { display:grid;grid-template-columns:1fr 1fr;gap:20px }
.iv-stat-block { background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:16px 20px }
.iv-abc-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px }
.iv-badge { display:inline-flex;align-items:center;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.3px }
.iv-chip { display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700 }
.iv-table-scroll { overflow-x:auto;-webkit-overflow-scrolling:touch }

@@media(max-width:900px) {
    .iv-two-col { grid-template-columns:1fr !important }
    .iv-abc-grid { grid-template-columns:1fr 1fr !important }
}
@@media(max-width:640px) {
    .iv-kpi-grid { grid-template-columns:1fr 1fr !important;gap:8px !important }
    .iv-abc-grid { grid-template-columns:1fr 1fr !important }
    .iv-tab-bar { overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:none;flex-wrap:nowrap !important }
    .iv-tab-bar::-webkit-scrollbar { display:none }
    .iv-tab-bar button span.iv-tab-lbl { display:none }
    .iv-tab-bar button { padding:10px 12px !important }
}
</style>

@php $isOwner = auth()->user()->isOwner(); @endphp

{{-- ══════════════════════════════════════════════════════════════════════════
     PAGE HEADER
══════════════════════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:24px;flex-wrap:wrap">
    <div>
        <h1 class="iv-page-title">Inventory Report</h1>
        <div class="iv-page-subtitle">Valuation, stock health, velocity classification, and replenishment intelligence</div>
    </div>

    {{-- Location filter --}}
    <div>
        <select wire:model.live="locationFilter"
            style="padding:6px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:13px;font-weight:600;cursor:pointer">
            <option value="all">All Locations</option>
            <option value="warehouses">All Warehouses</option>
            <option value="shops">All Shops</option>
            @foreach($this->warehouses as $wh)
                <option value="warehouse:{{ $wh->id }}">{{ $wh->name }}</option>
            @endforeach
            @foreach($this->shops as $shop)
                <option value="shop:{{ $shop->id }}">{{ $shop->name }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     HEADLINE KPI ROW (always visible)
══════════════════════════════════════════════════════════════════════════ --}}
@php
    $kpis        = $this->inventoryKpis;
    $fillRate    = $this->portfolioFillRate;
    $shrinkage   = $this->shrinkageStats;
    $isWarehouse = str_starts_with($locationFilter, 'warehouse:');

    $fillColor = match(true) {
        $fillRate === null   => 'var(--text-dim)',
        $fillRate >= 70      => 'var(--success)',
        $fillRate >= 40      => 'var(--amber)',
        default              => 'var(--red)',
    };
    $shrinkColor = match(true) {
        $shrinkage['shrinkage_pct'] > 2    => 'var(--red)',
        $shrinkage['shrinkage_pct'] >= 0.5 => 'var(--amber)',
        default                             => 'var(--success)',
    };
    $marginPct = $kpis['retail_value'] > 0
        ? round(($kpis['potential_profit'] / $kpis['retail_value']) * 100, 1)
        : 0;
@endphp
<div class="iv-kpi-grid">

    {{-- Card 1: Cost Value --}}
    <div class="bkpi" style="--bkpi-accent:var(--accent)">
        <div class="bkpi-icon" style="color:var(--accent)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        </div>
        <div class="bkpi-value">{{ number_format($kpis['purchase_value']) }}</div>
        <div class="bkpi-label">Cost Value</div>
        <div class="bkpi-meta">RWF · capital invested</div>
    </div>

    {{-- Card 2: Retail Value --}}
    <div class="bkpi" style="--bkpi-accent:var(--success)">
        <div class="bkpi-icon" style="color:var(--success)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><circle cx="12" cy="12" r="2"/></svg>
        </div>
        <div class="bkpi-value">{{ number_format($kpis['retail_value']) }}</div>
        <div class="bkpi-label">Retail Value</div>
        <div class="bkpi-meta">RWF · at selling price</div>
    </div>

    {{-- Card 3: Potential Margin --}}
    <div class="bkpi" style="--bkpi-accent:var(--violet)">
        <div class="bkpi-icon" style="color:var(--violet)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
        </div>
        <div class="bkpi-value">{{ number_format($kpis['potential_profit']) }}</div>
        <div class="bkpi-label">Potential Margin</div>
        <div class="bkpi-meta">{{ $marginPct }}% margin</div>
    </div>

    {{-- Card 4: Fill Rate --}}
    <div class="bkpi" style="--bkpi-accent:{{ $fillColor }}">
        <div class="bkpi-icon" style="color:{{ $fillColor }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
        </div>
        <div class="bkpi-value" style="color:{{ $fillColor }}">
            @if($fillRate !== null) {{ $fillRate }}% @else — @endif
        </div>
        <div class="bkpi-label">Fill Rate</div>
        <div class="bkpi-meta">@if($fillRate === null) No stock @else items vs capacity @endif</div>
    </div>

    {{-- Card 5: Stock Turnover --}}
    <div class="bkpi" style="--bkpi-accent:var(--accent)">
        <div class="bkpi-icon" style="color:var(--accent)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
        </div>
        <div class="bkpi-value">
            @if($isWarehouse) — @else {{ number_format($kpis['turnover_rate'], 2) }}× @endif
        </div>
        <div class="bkpi-label">Stock Turnover</div>
        <div class="bkpi-meta">@if($isWarehouse) N/A for warehouses @else annual COGS ÷ inventory @endif</div>
    </div>

    {{-- Card 6: Shrinkage Rate --}}
    <div class="bkpi" style="--bkpi-accent:{{ $shrinkColor }}">
        <div class="bkpi-icon" style="color:{{ $shrinkColor }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div class="bkpi-value" style="color:{{ $shrinkColor }}">{{ $shrinkage['shrinkage_pct'] }}%</div>
        <div class="bkpi-label">Shrinkage Rate</div>
        <div class="bkpi-meta">{{ number_format($shrinkage['items_damaged_90d']) }} units damaged (90d)</div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB BAR
══════════════════════════════════════════════════════════════════════════ --}}
<div class="iv-tab-bar" style="display:flex;gap:2px;border-bottom:2px solid var(--border);margin-bottom:28px">
    @php
        $tabs = [
            'overview'      => ['label' => 'Overview',      'icon' => 'M3 3h7v7H3zm11 0h7v7h-7zM3 14h7v7H3zm11 0h7v7h-7z'],
            'valuation'     => ['label' => 'Valuation',     'icon' => 'M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6'],
            'health'        => ['label' => 'Stock Health',  'icon' => 'M22 12h-4l-3 9L9 3l-3 9H2'],
            'replenishment' => ['label' => 'Replenishment', 'icon' => 'M4 4h16v16H4zM4 10h16M10 4v16'],
        ];
    @endphp
    @foreach($tabs as $key => $tab)
        <button type="button" wire:click="setTab('{{ $key }}')" class="iv-tab-btn"
            style="display:flex;align-items:center;gap:7px;padding:10px 18px;border:none;background:none;cursor:pointer;font-size:13px;font-weight:600;
                   color:{{ $activeTab === $key ? 'var(--accent)' : 'var(--text-sub)' }};
                   border-bottom:2px solid {{ $activeTab === $key ? 'var(--accent)' : 'transparent' }};
                   margin-bottom:-2px;transition:color .15s">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="{{ $tab['icon'] }}"/>
            </svg>
            <span class="iv-tab-lbl">{{ $tab['label'] }}</span>
        </button>
    @endforeach
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: OVERVIEW
══════════════════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'overview')

    {{-- ── Movement Trend Chart ──────────────────────────────────────────── --}}
    <div class="iv-card" style="margin-bottom:20px">
        <div class="iv-section-title">Stock Movement — Last 12 Weeks</div>
        <div class="iv-section-sub">Boxes received vs items consumed per week</div>
        <div x-data="invMovChart()"
             x-init="init()"
             data-chart='@json($this->inventoryMovementTrend)'>
            <div wire:ignore>
                <canvas id="invMovChart" style="max-height:280px"></canvas>
            </div>
        </div>
    </div>

    {{-- ── ABC Velocity Summary ─────────────────────────────────────────── --}}
    @php $vel = $this->velocityClassification; $vs = $vel['summary'] ?? []; @endphp
    <div style="margin-bottom:24px">
        <div class="iv-section-title">ABC Velocity Classification</div>
        <div class="iv-abc-grid">
            <div class="iv-stat-block" style="border-left:3px solid var(--success)">
                <div style="font-size:11px;font-weight:700;color:var(--success);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">A — Fast Movers</div>
                <div style="font-size:26px;font-weight:700;color:var(--text)">{{ $vs['A_count'] ?? 0 }}</div>
                <div style="font-size:11px;color:var(--text-dim);margin:2px 0">products</div>
                <div style="font-size:12px;color:var(--text-sub);margin-top:6px">{{ number_format($vs['A_cost_value'] ?? 0) }} RWF</div>
                <div style="font-size:11px;color:var(--text-dim)">Top 70% of revenue</div>
            </div>
            <div class="iv-stat-block" style="border-left:3px solid var(--amber)">
                <div style="font-size:11px;font-weight:700;color:var(--amber);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">B — Medium Movers</div>
                <div style="font-size:26px;font-weight:700;color:var(--text)">{{ $vs['B_count'] ?? 0 }}</div>
                <div style="font-size:11px;color:var(--text-dim);margin:2px 0">products</div>
                <div style="font-size:12px;color:var(--text-sub);margin-top:6px">{{ number_format($vs['B_cost_value'] ?? 0) }} RWF</div>
                <div style="font-size:11px;color:var(--text-dim)">70–90% of revenue</div>
            </div>
            <div class="iv-stat-block" style="border-left:3px solid var(--amber)">
                <div style="font-size:11px;font-weight:700;color:var(--amber);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">C — Slow Movers</div>
                <div style="font-size:26px;font-weight:700;color:var(--text)">{{ $vs['C_count'] ?? 0 }}</div>
                <div style="font-size:11px;color:var(--text-dim);margin:2px 0">products</div>
                <div style="font-size:12px;color:var(--text-sub);margin-top:6px">{{ number_format($vs['C_cost_value'] ?? 0) }} RWF</div>
                <div style="font-size:11px;color:var(--text-dim)">Bottom 10% of revenue</div>
            </div>
            <div class="iv-stat-block" style="border-left:3px solid var(--red)">
                <div style="font-size:11px;font-weight:700;color:var(--red);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Dead Stock</div>
                <div style="font-size:26px;font-weight:700;color:var(--text)">{{ $vs['Dead_count'] ?? 0 }}</div>
                <div style="font-size:11px;color:var(--text-dim);margin:2px 0">products</div>
                <div style="font-size:12px;color:var(--text-sub);margin-top:6px">{{ number_format($vs['Dead_cost_value'] ?? 0) }} RWF</div>
                <div style="font-size:11px;color:var(--text-dim)">No sales in 90 days</div>
            </div>
        </div>
    </div>

    {{-- ── Category Concentration ───────────────────────────────────────── --}}
    <div class="iv-card">
        <div class="iv-section-title">Category Concentration</div>
        <div class="iv-table-scroll">
            <table class="iv-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="r">Products</th>
                        <th class="r">Items</th>
                        <th class="r">Cost Value</th>
                        <th style="min-width:180px">% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->categoryConcentration as $i => $cat)
                        <tr>
                            <td style="font-weight:600">{{ $cat['category_name'] }}</td>
                            <td class="r" style="color:var(--text-dim)">{{ $cat['product_count'] }}</td>
                            <td class="r">{{ number_format($cat['total_items']) }}</td>
                            <td class="r" style="font-family:var(--mono)">{{ number_format($cat['cost_value']) }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px">
                                    <div style="flex:1;height:6px;background:var(--surface2);border-radius:3px;overflow:hidden">
                                        <div style="height:100%;width:{{ $cat['pct_of_total'] }}%;background:var(--accent);opacity:{{ max(0.3, 1 - $i * 0.12) }};border-radius:3px"></div>
                                    </div>
                                    <span style="font-size:12px;color:var(--text-sub);min-width:36px;text-align:right">{{ $cat['pct_of_total'] }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center;color:var(--text-dim);padding:24px">No category data available</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: VALUATION
══════════════════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'valuation')

    {{-- ── Value by Location ────────────────────────────────────────────── --}}
    @php $byLoc = $this->inventoryByLocation; @endphp
    <div style="margin-bottom:24px">
        <div class="iv-section-title">Value by Location</div>
        <div class="iv-two-col">

            {{-- Warehouses --}}
            <div class="iv-card">
                <div style="font-size:13px;font-weight:700;color:var(--text-sub);margin-bottom:12px;display:flex;align-items:center;gap:6px">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Warehouses
                </div>
                <div class="iv-table-scroll">
                    <table class="iv-table">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th class="r">Items</th>
                                <th class="r">Cost Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($byLoc['warehouses'] as $row)
                                <tr>
                                    <td>{{ $row['location_name'] }}</td>
                                    <td class="r">{{ number_format($row['items_count']) }}</td>
                                    <td class="r" style="font-family:var(--mono)">{{ number_format($row['value']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="text-align:center;color:var(--text-dim)">No warehouse stock</td></tr>
                            @endforelse
                        </tbody>
                        @if(count($byLoc['warehouses']) > 1)
                        <tfoot>
                            <tr>
                                <td>Total</td>
                                <td class="r">{{ number_format(collect($byLoc['warehouses'])->sum('items_count')) }}</td>
                                <td class="r" style="font-family:var(--mono)">{{ number_format(collect($byLoc['warehouses'])->sum('value')) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Shops --}}
            <div class="iv-card">
                <div style="font-size:13px;font-weight:700;color:var(--text-sub);margin-bottom:12px;display:flex;align-items:center;gap:6px">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    Shops
                </div>
                <div class="iv-table-scroll">
                    <table class="iv-table">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th class="r">Items</th>
                                <th class="r">Cost Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($byLoc['shops'] as $row)
                                <tr>
                                    <td>{{ $row['location_name'] }}</td>
                                    <td class="r">{{ number_format($row['items_count']) }}</td>
                                    <td class="r" style="font-family:var(--mono)">{{ number_format($row['value']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="text-align:center;color:var(--text-dim)">No shop stock</td></tr>
                            @endforelse
                        </tbody>
                        @if(count($byLoc['shops']) > 1)
                        <tfoot>
                            <tr>
                                <td>Total</td>
                                <td class="r">{{ number_format(collect($byLoc['shops'])->sum('items_count')) }}</td>
                                <td class="r" style="font-family:var(--mono)">{{ number_format(collect($byLoc['shops'])->sum('value')) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Top 20 Products by Capital Value ────────────────────────────── --}}
    @php
        $topProds = $this->topProductsByValue;
        $maxVal   = collect($topProds)->max('purchase_value') ?: 1;
    @endphp
    <div class="iv-card">
        <div class="iv-section-title">Top 20 Products by Capital Value</div>
        <div class="iv-table-scroll">
            <table class="iv-table">
                <thead>
                    <tr>
                        <th style="width:36px">#</th>
                        <th>Product</th>
                        <th class="r">Items</th>
                        <th class="r">Cost Value</th>
                        <th class="r">Retail Value</th>
                        <th class="r">Locations</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topProds as $i => $p)
                        <tr style="{{ $i < 3 ? 'background:color-mix(in srgb, var(--accent) 6%, transparent)' : '' }}">
                            <td style="color:var(--text-dim);font-size:12px;font-family:var(--mono)">{{ $i + 1 }}</td>
                            <td>
                                <div style="font-weight:600">{{ $p['product_name'] }}</div>
                                <div style="margin-top:4px;height:3px;background:var(--surface2);border-radius:2px;max-width:160px;overflow:hidden">
                                    <div style="height:100%;width:{{ round($p['purchase_value'] / $maxVal * 100) }}%;background:var(--accent);border-radius:2px"></div>
                                </div>
                            </td>
                            <td class="r">{{ number_format($p['items_count']) }}</td>
                            <td class="r" style="font-family:var(--mono)">{{ number_format($p['purchase_value']) }}</td>
                            <td class="r" style="font-family:var(--mono);color:var(--text-dim)">{{ number_format($p['retail_value']) }}</td>
                            <td class="r" style="color:var(--text-dim)">{{ $p['location_count'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;color:var(--text-dim);padding:24px">No products with stock found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: STOCK HEALTH
══════════════════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'health')

    @php
        $health   = $this->stockHealth;
        $aging    = $this->agingAnalysis;
        $expiring = $this->expiringStock;
        $vel      = $this->velocityClassification;
        $vs       = $vel['summary'] ?? [];

        $agingOrder  = ['0-30 days', '31-60 days', '61-90 days', '90+ days'];
        $agingColors = [
            '0-30 days'  => 'var(--success)',
            '31-60 days' => 'var(--accent)',
            '61-90 days' => 'var(--amber)',
            '90+ days'   => 'var(--red)',
        ];
        $agingKeyed = collect($aging)->keyBy('age_bracket');
        $agingTotal = collect($aging)->sum('items_count') ?: 1;
    @endphp

    {{-- ── Health Summary Cards ─────────────────────────────────────────── --}}
    <div class="iv-kpi-grid" style="margin-bottom:24px">
        <div class="bkpi" style="--bkpi-accent:var(--amber)">
            <div class="bkpi-icon" style="color:var(--amber)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </div>
            <div class="bkpi-value">{{ number_format($health['low_stock_count']) }}</div>
            <div class="bkpi-label">Low Stock</div>
            <div class="bkpi-meta">products below threshold</div>
        </div>
        <div class="bkpi" style="--bkpi-accent:var(--red)">
            <div class="bkpi-icon" style="color:var(--red)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
            </div>
            <div class="bkpi-value">{{ number_format($health['dead_stock_count']) }}</div>
            <div class="bkpi-label">Dead Stock</div>
            <div class="bkpi-meta">no sales in 90 days</div>
        </div>
        <div class="bkpi" style="--bkpi-accent:{{ count($expiring) > 0 ? 'var(--red)' : 'var(--success)' }}">
            <div class="bkpi-icon" style="color:{{ count($expiring) > 0 ? 'var(--red)' : 'var(--success)' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="bkpi-value" style="color:{{ count($expiring) > 0 ? 'var(--red)' : 'var(--success)' }}">{{ count($expiring) }}</div>
            <div class="bkpi-label">Expiring</div>
            <div class="bkpi-meta">within 30 days</div>
        </div>
        <div class="bkpi" style="--bkpi-accent:{{ $shrinkage['items_damaged_90d'] > 0 ? 'var(--amber)' : 'var(--success)' }}">
            <div class="bkpi-icon" style="color:{{ $shrinkage['items_damaged_90d'] > 0 ? 'var(--amber)' : 'var(--success)' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div class="bkpi-value">{{ number_format($shrinkage['items_damaged_90d']) }}</div>
            <div class="bkpi-label">Damaged (90d)</div>
            <div class="bkpi-meta">{{ number_format($shrinkage['estimated_loss']) }} RWF loss</div>
        </div>
    </div>

    {{-- ── Stock Aging ──────────────────────────────────────────────────── --}}
    <div class="iv-card" style="margin-bottom:20px">
        <div class="iv-section-title">Stock Aging</div>
        {{-- Stacked bar --}}
        <div style="height:20px;border-radius:6px;overflow:hidden;display:flex;margin-bottom:10px">
            @foreach($agingOrder as $bracket)
                @php
                    $bData = $agingKeyed->get($bracket);
                    $pct   = $bData ? round($bData['items_count'] / $agingTotal * 100) : 0;
                @endphp
                @if($pct > 0)
                <div style="width:{{ $pct }}%;background:{{ $agingColors[$bracket] }}" title="{{ $bracket }}: {{ $pct }}%"></div>
                @endif
            @endforeach
        </div>
        {{-- Legend --}}
        <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:16px">
            @foreach($agingOrder as $bracket)
                <div style="display:flex;align-items:center;gap:5px">
                    <div style="width:10px;height:10px;border-radius:2px;background:{{ $agingColors[$bracket] }}"></div>
                    <span style="font-size:11px;color:var(--text-dim)">{{ $bracket }}</span>
                </div>
            @endforeach
        </div>
        {{-- Table --}}
        <div class="iv-table-scroll">
            <table class="iv-table">
                <thead>
                    <tr>
                        <th>Age Bracket</th>
                        <th class="r">Boxes</th>
                        <th class="r">Items</th>
                        <th class="r">Cost Value</th>
                        <th class="r">% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agingOrder as $bracket)
                        @php $bData = $agingKeyed->get($bracket); @endphp
                        @if($bData)
                        <tr>
                            <td>
                                <span class="iv-chip" style="background:{{ $agingColors[$bracket] }}20;color:{{ $agingColors[$bracket] }}">
                                    {{ $bData['age_bracket'] }}
                                </span>
                            </td>
                            <td class="r">{{ number_format($bData['box_count']) }}</td>
                            <td class="r">{{ number_format($bData['items_count']) }}</td>
                            <td class="r" style="font-family:var(--mono)">{{ number_format($bData['value']) }}</td>
                            <td class="r">{{ round($bData['items_count'] / $agingTotal * 100, 1) }}%</td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── ABC Classification Detail ─────────────────────────────────────── --}}
    <div class="iv-card" style="margin-bottom:20px">
        <div class="iv-section-title">ABC Classification Detail</div>
        @foreach(['A' => ['label' => 'A — Fast Movers', 'color' => 'var(--success)'], 'B' => ['label' => 'B — Medium Movers', 'color' => 'var(--amber)'], 'C' => ['label' => 'C — Slow Movers', 'color' => 'var(--amber)']] as $cls => $meta)
            @if(count($vel[$cls] ?? []) > 0)
            <details style="margin-bottom:8px">
                <summary style="cursor:pointer;padding:10px 12px;background:var(--surface2);border-radius:6px;font-size:13px;font-weight:600;color:{{ $meta['color'] }};list-style:none;display:flex;justify-content:space-between;align-items:center">
                    {{ $meta['label'] }}
                    <span style="font-size:11px;color:var(--text-dim)">{{ count($vel[$cls]) }} products</span>
                </summary>
                <div class="iv-table-scroll">
                    <table class="iv-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="r">Items in Stock</th>
                                <th class="r">Revenue (90d)</th>
                                <th class="r">Rev %</th>
                                <th class="r">Cost Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vel[$cls] as $p)
                            <tr>
                                <td>{{ $p['product_name'] }}</td>
                                <td class="r">{{ number_format($p['items_in_stock']) }}</td>
                                <td class="r" style="font-family:var(--mono)">{{ number_format($p['revenue_90d']) }}</td>
                                <td class="r" style="color:var(--text-dim)">{{ $p['revenue_pct'] }}%</td>
                                <td class="r" style="font-family:var(--mono)">{{ number_format($p['cost_value']) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
            @endif
        @endforeach

        {{-- Dead stock --}}
        @if(count($vel['Dead'] ?? []) > 0)
        <div style="margin-top:12px;border:1px solid var(--red);border-radius:6px;overflow:hidden">
            <div style="background:rgba(239,68,68,.08);padding:10px 14px;display:flex;justify-content:space-between;align-items:center">
                <span style="font-size:13px;font-weight:700;color:var(--red)">Dead Stock — No Sales in 90 Days</span>
                <span style="font-size:11px;color:var(--text-dim)">{{ count($vel['Dead']) }} products</span>
            </div>
            <p style="font-size:12px;color:var(--text-dim);padding:8px 14px 4px;margin:0">These products hold capital but generate no revenue.</p>
            <div class="iv-table-scroll">
                <table class="iv-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="r">Items in Stock</th>
                            <th class="r">Cost Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vel['Dead'] as $p)
                        <tr>
                            <td>{{ $p['product_name'] }}</td>
                            <td class="r">{{ number_format($p['items_in_stock']) }}</td>
                            <td class="r" style="font-family:var(--mono);color:var(--red)">{{ number_format($p['cost_value']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- ── Expiring Stock ───────────────────────────────────────────────── --}}
    <div class="iv-card">
        <div class="iv-section-title">Expiring Stock (Next 30 Days)</div>
        @if(empty($expiring))
            <div style="text-align:center;padding:28px 0">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2" style="margin-bottom:8px"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <div style="font-size:14px;font-weight:600;color:var(--success)">No expiring stock</div>
                <div style="font-size:12px;color:var(--text-dim);margin-top:4px">All stocked items are within safe expiry range</div>
            </div>
        @else
            <div class="iv-table-scroll">
                <table class="iv-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="r">Expiry Date</th>
                            <th class="r">Days Left</th>
                            <th class="r">Items</th>
                            <th class="r">Cost Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expiring as $item)
                            @php
                                $expiryCarbon = \Carbon\Carbon::parse($item['expiry_date'])->startOfDay();
                                $daysLeft     = (int) now()->startOfDay()->diffInDays($expiryCarbon, false);
                                $daysLeft     = max(0, $daysLeft);
                                $expColor     = $daysLeft <= 7  ? 'var(--red)'
                                              : ($daysLeft <= 14 ? 'var(--amber)' : 'var(--amber)');
                            @endphp
                            <tr>
                                <td style="font-weight:600">{{ $item['product_name'] }}</td>
                                <td class="r" style="color:{{ $expColor }};font-family:var(--mono)">
                                    {{ \Carbon\Carbon::parse($item['expiry_date'])->format('d M Y') }}
                                </td>
                                <td class="r">
                                    <span class="iv-chip" style="background:{{ $expColor }}20;color:{{ $expColor }}">{{ $daysLeft }}d</span>
                                </td>
                                <td class="r">{{ number_format($item['items_count']) }}</td>
                                <td class="r" style="font-family:var(--mono)">{{ number_format($item['value']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     TAB: REPLENISHMENT
══════════════════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'replenishment')

    @php
        $doh = $this->daysOnHandPerProduct;
        $vel = $this->velocityClassification;

        $filteredDoh = collect($doh);
        if ($urgencyFilter === 'critical') {
            $filteredDoh = $filteredDoh->filter(fn($p) => $p['is_critical']);
        } elseif ($urgencyFilter === 'reorder') {
            $filteredDoh = $filteredDoh->filter(fn($p) => $p['is_low'] || $p['is_critical']);
        }
    @endphp

    {{-- ── Urgency filter strip ─────────────────────────────────────────── --}}
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap">
        <span style="font-size:12px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px">Filter:</span>
        <select wire:model.live="urgencyFilter"
            style="padding:5px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:13px;cursor:pointer">
            <option value="all">All Products</option>
            <option value="critical">Critical only (≤ 7 days)</option>
            <option value="reorder">Needs reorder (≤ 14 days)</option>
        </select>
        <span style="font-size:12px;color:var(--text-dim)">{{ $filteredDoh->count() }} products</span>
    </div>

    {{-- ── Replenishment Urgency Table ──────────────────────────────────── --}}
    <div class="iv-card" style="margin-bottom:24px">
        <div class="iv-section-title">Products Requiring Action — Sorted by Urgency</div>
        @if($filteredDoh->isEmpty())
            <div style="text-align:center;padding:28px 0;color:var(--success)">
                <div style="font-size:14px;font-weight:600">
                    @if($urgencyFilter === 'critical') No critical products
                    @elseif($urgencyFilter === 'reorder') No products needing reorder
                    @else No products with stock data
                    @endif
                </div>
            </div>
        @else
            <div class="iv-table-scroll">
                <table class="iv-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="r">Stock</th>
                            <th class="r">Sold (30d)</th>
                            <th class="r">Avg/Day</th>
                            <th class="r">Days on Hand</th>
                            <th class="r">Status</th>
                            @if($isOwner)<th class="r">Suggested Order</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($filteredDoh as $p)
                            @php
                                $dohColor = match(true) {
                                    $p['days_on_hand'] === null => 'var(--text-dim)',
                                    $p['days_on_hand'] <= 7    => 'var(--red)',
                                    $p['days_on_hand'] <= 14   => 'var(--amber)',
                                    default                    => 'var(--success)',
                                };
                                $statusLabel = match(true) {
                                    $p['days_on_hand'] === null => ['text' => 'No data',  'color' => 'var(--text-dim)'],
                                    $p['is_critical']           => ['text' => 'Critical', 'color' => 'var(--red)'],
                                    $p['is_low']                => ['text' => 'Reorder',  'color' => 'var(--amber)'],
                                    default                     => ['text' => 'OK',        'color' => 'var(--success)'],
                                };
                                $suggested = $p['avg_daily_sales'] > 0
                                    ? max(0, (int) ceil(30 * $p['avg_daily_sales']) - $p['items_remaining'])
                                    : null;
                            @endphp
                            <tr>
                                <td style="font-weight:600">{{ $p['product_name'] }}</td>
                                <td class="r">{{ number_format($p['items_remaining']) }}</td>
                                <td class="r" style="color:var(--text-dim)">{{ number_format($p['units_sold_30d']) }}</td>
                                <td class="r" style="color:var(--text-dim)">{{ $p['avg_daily_sales'] > 0 ? number_format($p['avg_daily_sales'], 1) : '—' }}</td>
                                <td class="r">
                                    @if($p['days_on_hand'] !== null)
                                        <span class="iv-chip" style="background:{{ $dohColor }}20;color:{{ $dohColor }}">{{ $p['days_on_hand'] }}d</span>
                                    @else
                                        <span style="color:var(--text-dim)">— No velocity</span>
                                    @endif
                                </td>
                                <td class="r">
                                    <span class="iv-badge" style="background:{{ $statusLabel['color'] }}20;color:{{ $statusLabel['color'] }}">
                                        {{ $statusLabel['text'] }}
                                    </span>
                                </td>
                                @if($isOwner)
                                <td class="r" style="font-family:var(--mono);color:var(--text-sub)">
                                    {{ $suggested !== null ? number_format($suggested) : '—' }}
                                </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ── Dead Stock Capital Lock ───────────────────────────────────────── --}}
    @php
        $deadItems   = $vel['Dead'] ?? [];
        $deadCapital = $vel['summary']['Dead_cost_value'] ?? 0;
    @endphp
    @if(count($deadItems) > 0)
    <div class="iv-card" style="border-color:var(--red)">
        <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:16px">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2" style="flex-shrink:0;margin-top:2px"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <div>
                <div class="iv-section-title" style="color:var(--red);margin-bottom:4px">Dead Stock Capital Lock</div>
                <div style="font-size:13px;color:var(--text-sub)">
                    The following products have inventory but zero sales in 90 days.
                    They represent <strong>{{ number_format($deadCapital) }} RWF</strong> in locked capital.
                </div>
            </div>
        </div>
        <div class="iv-table-scroll">
            <table class="iv-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="r">Items in Stock</th>
                        <th class="r">Cost Value</th>
                        <th class="r">Last Sale</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deadItems as $p)
                    <tr>
                        <td style="font-weight:600">{{ $p['product_name'] }}</td>
                        <td class="r">{{ number_format($p['items_in_stock']) }}</td>
                        <td class="r" style="font-family:var(--mono);color:var(--red)">{{ number_format($p['cost_value']) }}</td>
                        <td class="r" style="color:var(--text-dim)">90d+</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="iv-card" style="text-align:center;padding:28px">
        <div style="font-size:14px;font-weight:600;color:var(--success)">No dead stock detected</div>
        <div style="font-size:12px;color:var(--text-dim);margin-top:4px">All products have had sales activity in the last 90 days</div>
    </div>
    @endif

@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     CHART.JS — Inventory Movement Trend (Livewire 3 @script)
══════════════════════════════════════════════════════════════════════════ --}}
@script
<script>
Alpine.data('invMovChart', () => ({
    init() {
        var el     = this.$el;
        var canvas = el.querySelector('#invMovChart');
        if (!canvas) return;

        var orphan = Chart.getChart(canvas);
        if (orphan) orphan.destroy();
        if (canvas._chartInstance) {
            canvas._chartInstance.destroy();
            delete canvas._chartInstance;
        }

        var raw = JSON.parse(el.dataset.chart || '[]');

        canvas._chartInstance = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: raw.map(function(d) { return d.week_label; }),
                datasets: [
                    {
                        label: 'Boxes Received',
                        data: raw.map(function(d) { return d.boxes_received; }),
                        backgroundColor: 'var(--accent)',
                        borderRadius: 3,
                        borderSkipped: false,
                    },
                    {
                        label: 'Items Consumed',
                        data: raw.map(function(d) { return d.items_consumed; }),
                        backgroundColor: 'var(--amber)',
                        borderRadius: 3,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { size: 12 }, padding: 16, usePointStyle: true }
                    },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, grid: { color: 'rgba(128,128,128,.08)' } }
                }
            }
        });
    }
}));
</script>
@endscript

</div>{{-- end root div --}}
