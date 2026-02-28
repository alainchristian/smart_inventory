<x-app-layout>
    @push('scripts')
    <script>
        // Update topbar page title
        document.addEventListener('DOMContentLoaded', function() {
            const titleElement = document.querySelector('[data-page-title]');
            if (titleElement) {
                titleElement.textContent = 'Dashboard';
            }
        });
    </script>
    @endpush

    {{-- Page header + time filter --}}
    <div class="dashboard-page-header">
        <div>
            <h1>Owner Dashboard</h1>
            <p>Real-time business metrics and insights</p>
        </div>
        <livewire:dashboard.time-filter />
    </div>

    {{-- Inventory Health Row (Problem 8) - Consistent with Business Overview Cards --}}
    @if(isset($stats))
    <div class="section-label">Inventory Health</div>
    <div class="biz-kpi-grid" style="margin-bottom: 24px">
        {{-- Card 1: Stock Cost Value --}}
        <div class="bkpi violet" style="animation:fadeUp .4s ease .05s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon violet">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        </svg>
                    </div>
                    <span class="bkpi-name">Stock Cost Value</span>
                </div>
                <span class="bkpi-pct violet">Owner</span>
            </div>
            <div class="bkpi-value">{{ number_format($stats['inventory_value']) }}</div>
            <div class="bkpi-meta">What you paid · RWF</div>
        </div>

        {{-- Card 2: Retail Value --}}
        <div class="bkpi blue" style="animation:fadeUp .4s ease .10s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span class="bkpi-name">Potential Retail</span>
                </div>
                <span class="bkpi-pct blue">{{ number_format($stats['total_items_in_stock']) }}</span>
            </div>
            <div class="bkpi-value">{{ number_format($stats['retail_value']) }}</div>
            <div class="bkpi-meta">{{ number_format($stats['total_items_in_stock']) }} items in stock · RWF</div>
        </div>

        {{-- Card 3: Potential Profit --}}
        <div class="bkpi green" style="animation:fadeUp .4s ease .15s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                            <polyline points="17 6 23 6 23 12"/>
                        </svg>
                    </div>
                    <span class="bkpi-name">Gross Margin</span>
                </div>
                @php $marginPct = $stats['retail_value'] > 0 ? ($stats['potential_profit'] / $stats['retail_value']) * 100 : 0; @endphp
                <span class="bkpi-pct green">{{ number_format($marginPct, 1) }}%</span>
            </div>
            <div class="bkpi-value" style="color:var(--green)">{{ number_format($stats['potential_profit']) }}</div>
            <div class="bkpi-meta">Expected profit margin · RWF</div>
        </div>

        {{-- Card 4: Items in Stock --}}
        <div class="bkpi pink" style="animation:fadeUp .4s ease .20s both">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="bkpi-icon pink">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="3" width="15" height="13" rx="2"/>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                            <circle cx="5.5" cy="18.5" r="2.5"/>
                            <circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                    </div>
                    <span class="bkpi-name">Total Boxes</span>
                </div>
                <span class="bkpi-pct pink">Active</span>
            </div>
            <div class="bkpi-value">{{ number_format($stats['total_boxes']) }}</div>
            <div class="bkpi-meta">{{ number_format($stats['total_items_in_stock']) }} items total</div>
        </div>
    </div>
    @endif

    {{-- Row 1: Business KPIs --}}
    <div class="section-label">Business Overview</div>
    <livewire:dashboard.business-kpi-row />

    {{-- Row 2: Ops KPIs --}}
    <div class="section-label">Operations at a Glance</div>
    <livewire:dashboard.ops-kpi-row />

    {{-- Row 3: Sales chart + Top shops --}}
    <div class="section-label">Sales Performance &amp; Shop Rankings</div>
    <div class="row-sales-shops">
        <livewire:dashboard.sales-performance />
        <livewire:dashboard.top-shops />
    </div>

    {{-- Row 4: Transfers + Activity + Stock distribution --}}
    <div class="section-label">Operations &amp; Activity</div>
    <div class="row-ops-activity">
        <livewire:dashboard.transfer-status />
        <livewire:dashboard.activity-feed />
        <livewire:dashboard.stock-distribution />
    </div>

    {{-- Row 5: Box movements + Alerts + System status --}}
    <div class="section-label">Inventory Traceability &amp; Alerts</div>
    <div class="row-trace-alerts">
        <livewire:dashboard.recent-movements />
        <div class="right-stack-panel">
            <livewire:dashboard.alerts-panel />
            <livewire:dashboard.system-status />
        </div>
    </div>

</x-app-layout>
