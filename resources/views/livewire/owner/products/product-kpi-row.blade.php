{{-- Shared .ui-kpi summary cards (app.css) --}}
<div class="ui-kpis m-kpis">

  {{-- Card 1: Products --}}
    <div class="ui-kpi">
        <div class="ui-kpi-row">
            <div class="ui-kpi-icon" style="background:var(--accent-dim);color:var(--accent)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <div class="ui-kpi-body">
                <div class="ui-kpi-label">Products</div>
                <div class="ui-kpi-sub">Total in catalog · all categories</div>
            </div>
        </div>
        <div class="ui-kpi-val" style="color:var(--accent)">{{ number_format($totalProducts) }}</div>
        <div class="ui-kpi-divider"></div>
        <div class="ui-kpi-footer">
            <div class="ui-kpi-stat">
                <span class="ui-kpi-stat-v">{{ number_format($totalActive) }}</span>
                <span class="ui-kpi-stat-l">Active</span>
            </div>
            <div class="ui-kpi-stat">
                <span class="ui-kpi-stat-v" style="{{ $totalInactive > 0 ? 'color:var(--amber)' : '' }}">{{ number_format($totalInactive) }}</span>
                <span class="ui-kpi-stat-l">Inactive</span>
            </div>
            <div class="ui-kpi-stat">
                <span class="ui-kpi-stat-v">{{ $totalProducts > 0 ? round($totalActive / $totalProducts * 100) : 0 }}%</span>
                <span class="ui-kpi-stat-l">Coverage</span>
            </div>
        </div>
    </div>

  {{-- Card 2: Low Stock --}}
    <div class="ui-kpi">
        <div class="ui-kpi-row">
            <div class="ui-kpi-icon" style="background:{{ $lowStockCount > 0 ? 'var(--red-dim)' : 'var(--green-dim)' }};color:{{ $lowStockCount > 0 ? 'var(--red)' : 'var(--green)' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div class="ui-kpi-body">
                <div class="ui-kpi-label">Low Stock</div>
                <div class="ui-kpi-sub">Below threshold · all locations</div>
            </div>
        </div>
        <div class="ui-kpi-val" style="color:{{ $lowStockCount > 0 ? 'var(--red)' : 'var(--green)' }}">{{ number_format($lowStockCount) }}</div>
        <div class="ui-kpi-divider"></div>
        <div class="ui-kpi-footer">
            <div class="ui-kpi-stat">
                <span class="ui-kpi-stat-v" style="{{ $lowStockCount > 0 ? 'color:var(--amber)' : '' }}">{{ number_format($lowStockCount) }}</span>
                <span class="ui-kpi-stat-l">Low</span>
            </div>
            <div class="ui-kpi-stat">
                <span class="ui-kpi-stat-v" style="{{ $zeroStockCount > 0 ? 'color:var(--red)' : '' }}">{{ number_format($zeroStockCount) }}</span>
                <span class="ui-kpi-stat-l">Out of stock</span>
            </div>
            <div class="ui-kpi-stat">
                <span class="ui-kpi-stat-v">{{ number_format(max(0, $totalActive - $lowStockCount)) }}</span>
                <span class="ui-kpi-stat-l">Healthy</span>
            </div>
        </div>
    </div>

  {{-- Card 3: Price Overrides --}}
    <div class="ui-kpi">
        <div class="ui-kpi-row">
            <div class="ui-kpi-icon" style="background:{{ $priceOverrideCount > 0 ? 'var(--amber-dim)' : 'var(--green-dim)' }};color:{{ $priceOverrideCount > 0 ? 'var(--amber)' : 'var(--green)' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="ui-kpi-body">
                <div class="ui-kpi-label">Price Overrides</div>
                <div class="ui-kpi-sub">Changed prices · {{ $periodLabel }}</div>
            </div>
        </div>
        <div class="ui-kpi-val" style="color:{{ $priceOverrideCount > 0 ? 'var(--amber)' : 'var(--green)' }}">{{ number_format($priceOverrideCount) }}</div>
        <div class="ui-kpi-divider"></div>
        <div class="ui-kpi-footer">
            <div class="ui-kpi-stat">
                <span class="ui-kpi-stat-v">{{ number_format($overrideLines) }}</span>
                <span class="ui-kpi-stat-l">Lines changed</span>
            </div>
            <div class="ui-kpi-stat">
                <span class="ui-kpi-stat-v">{{ number_format($overrideDiscounts) }}</span>
                <span class="ui-kpi-stat-l">Discounts</span>
            </div>
            <div class="ui-kpi-stat">
                <span class="ui-kpi-stat-v">{{ number_format($overrideMarkups) }}</span>
                <span class="ui-kpi-stat-l">Markups</span>
            </div>
        </div>
    </div>

  {{-- Card 4: Best Margin (owner-only) --}}
  @can('viewPurchasePrice')
    <div class="ui-kpi">
        <div class="ui-kpi-row">
            <div class="ui-kpi-icon" style="background:var(--violet-dim);color:var(--violet)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            </div>
            <div class="ui-kpi-body">
                <div class="ui-kpi-label">Best Margin</div>
                <div class="ui-kpi-sub">Highest-margin active product</div>
            </div>
        </div>
        <div class="ui-kpi-val" style="color:{{ $bestMarginPct !== null ? 'var(--violet)' : 'var(--text-dim)' }}">{{ $bestMarginPct !== null ? $bestMarginPct . '%' : '—' }}</div>
        <div class="ui-kpi-divider"></div>
        <div class="ui-kpi-footer">
            <div class="ui-kpi-stat">
                <span class="ui-kpi-stat-v" title="{{ $bestMarginName }}">{{ $bestMarginName ?? '—' }}</span>
                <span class="ui-kpi-stat-l">Product</span>
            </div>
            <div class="ui-kpi-stat">
                <span class="ui-kpi-stat-v">{{ $avgMarginPct !== null ? $avgMarginPct . '%' : '—' }}</span>
                <span class="ui-kpi-stat-l">Avg margin</span>
            </div>
            <div class="ui-kpi-stat">
                <span class="ui-kpi-stat-v" style="{{ $belowCostCount > 0 ? 'color:var(--red)' : '' }}">{{ number_format($belowCostCount) }}</span>
                <span class="ui-kpi-stat-l">Priced below cost</span>
            </div>
        </div>
    </div>
  @endcan

</div>
