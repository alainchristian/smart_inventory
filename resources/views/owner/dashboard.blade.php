<x-app-layout>
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const el = document.querySelector('[data-page-title]');
            if (el) el.textContent = 'Dashboard';
        });
    </script>
    @endpush

    {{-- Global period filter — full-width surface card, matching shop/warehouse dashboards --}}
    <livewire:dashboard.time-filter />

    {{-- ── Row 1: 5 KPI Cards ──────────────────────────────────────────── --}}
    <livewire:dashboard.business-kpi-row wire:poll.25s />

    {{-- ── Row 2: Revenue Trend · Sales by Shop · Revenue by Category ──── --}}
    <div class="row-trend-shops">
        <livewire:dashboard.sales-performance wire:init="loadChart" />
        <livewire:dashboard.top-shops />
        <livewire:dashboard.revenue-by-category />
    </div>

    {{-- ── Row 3: Cash · Business · Warehouse · Credit · Top Shops ──────── --}}
    <livewire:dashboard.business-snapshot />

    {{-- ── Row 4: Top Shops · Expenses · Transactions · Insights ──────── --}}
    <div class="row-bottom-four">
        <livewire:dashboard.top-performing-shops />
        <livewire:dashboard.expenses-breakdown />
        <livewire:dashboard.recent-transactions />
        <livewire:dashboard.business-insights />
    </div>

</x-app-layout>
