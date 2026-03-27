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
    <div class="section-label">Business Overview</div>
    <livewire:dashboard.business-kpi-row wire:poll.60s />

    {{-- Row 2: Ops strip --}}
    <div class="section-label">Operations at a Glance</div>
    <livewire:dashboard.ops-kpi-row />

    {{-- Row 3: Sales chart + Top shops --}}
    <div class="section-label">Sales Performance &amp; Shop Rankings</div>
    <div class="row-sales-shops">
        <livewire:dashboard.sales-performance wire:init="loadChart" />
        <livewire:dashboard.top-shops />
    </div>

    {{-- Row 4: Transfers + Activity + Stock Heat Map --}}
    <div class="section-label">Operations &amp; Activity</div>
    <div class="row-ops-activity">
        <livewire:dashboard.transfer-status />
        <livewire:dashboard.activity-feed />
        <livewire:dashboard.stock-heat-map />
    </div>

    {{-- Row 5: Alerts & System Health (merged) --}}
    <div class="section-label">Alerts &amp; System Health</div>
    <livewire:dashboard.alerts-and-health />

</x-app-layout>
