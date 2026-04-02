<x-app-layout>
    @push('scripts')
    <script>
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

    {{-- Action Cards: replaces old amber banner + OwnerActions bottom panel --}}
    <livewire:dashboard.action-cards />

    {{-- Row 1: Business KPIs --}}
    <div class="section-label">Revenue &amp; Profit</div>
    <livewire:dashboard.business-kpi-row wire:poll.60s />

    {{-- Row 2: Ops strip --}}
    <div class="section-label">Live Operations</div>
    <livewire:dashboard.ops-kpi-row />

    {{-- Row 3: Sales chart + Top shops --}}
    <div class="section-label">Sales &amp; Shop Performance</div>
    <div class="row-sales-shops">
        <livewire:dashboard.sales-performance wire:init="loadChart" />
        <livewire:dashboard.top-shops />
    </div>

    {{-- Row 4: Transfers + Activity --}}
    <div class="section-label">Inventory &amp; Activity</div>
    <div class="row-ops-activity" style="grid-template-columns:1fr 1fr">
        <livewire:dashboard.transfer-status />
        <livewire:dashboard.activity-feed />
    </div>

    {{-- Row 5: Stock Coverage Map (full width) --}}
    <div class="section-label">Stock Coverage Map</div>
    <livewire:dashboard.stock-heat-map />

    {{-- Row 6: Owner Actions (requires attention + inline approvals) --}}
    <div class="section-label" style="margin-top:26px">Requires Your Attention</div>
    <livewire:dashboard.owner-actions />

    {{-- Row 7: Alerts & Notifications (merged) --}}
    <div class="section-label" style="margin-top:26px">Alerts &amp; Notifications</div>
    <livewire:dashboard.alerts-and-health />

</x-app-layout>
