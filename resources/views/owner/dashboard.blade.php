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

    {{-- Pending Actions Banner --}}
    @php
        $pendingApprovalCount = \App\Models\Transfer::where('status', 'pending')->count();
        $discrepancyCount     = \App\Models\Transfer::where('has_discrepancy', true)->count();
        $criticalAlertsCount  = \App\Models\Alert::where('severity', 'critical')
                                    ->whereNull('resolved_at')
                                    ->where('is_dismissed', false)
                                    ->count();

        // Damaged goods — check actual model name with: ls app/Models | grep -i damage
        // Adjust class name below if different (e.g. DamagedGoods, DamagedGood)
        $damagedPendingCount = 0;
        if (class_exists(\App\Models\DamagedGood::class)) {
            $damagedPendingCount = \App\Models\DamagedGood::where('disposition', 'pending')->count();
        }

        $hasPendingActions = ($pendingApprovalCount + $discrepancyCount + $criticalAlertsCount + $damagedPendingCount) > 0;
    @endphp

    @if($hasPendingActions)
    <div class="mb-4 bg-amber-50 border border-amber-200 rounded-lg p-3">
        <p class="text-xs font-bold text-amber-800 uppercase tracking-wide mb-2">⚡ Requires Your Attention</p>
        <div class="flex flex-wrap gap-2">
            @if($pendingApprovalCount > 0)
            <a href="{{ route('warehouse.transfers.index') }}"
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white border border-amber-300 rounded-lg text-xs font-semibold text-amber-800 hover:bg-amber-100 transition-colors">
                <span class="w-5 h-5 bg-amber-500 text-white rounded-full flex items-center justify-center text-xs font-bold">{{ $pendingApprovalCount }}</span>
                <span>Transfer{{ $pendingApprovalCount > 1 ? 's' : '' }} Awaiting Approval</span>
            </a>
            @endif
            @if($discrepancyCount > 0)
            <a href="{{ route('warehouse.transfers.index') }}"
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white border border-red-300 rounded-lg text-xs font-semibold text-red-800 hover:bg-red-50 transition-colors">
                <span class="w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs font-bold">{{ $discrepancyCount }}</span>
                <span>Unresolved Discrepanc{{ $discrepancyCount > 1 ? 'ies' : 'y' }}</span>
            </a>
            @endif
            @if($damagedPendingCount > 0)
            <a href="#"
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white border border-orange-300 rounded-lg text-xs font-semibold text-orange-800 hover:bg-orange-50 transition-colors">
                <span class="w-5 h-5 bg-orange-500 text-white rounded-full flex items-center justify-center text-xs font-bold">{{ $damagedPendingCount }}</span>
                <span>Damaged Goods Pending Decision</span>
            </a>
            @endif
            @if($criticalAlertsCount > 0)
            <a href="#"
               class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white border border-red-300 rounded-lg text-xs font-semibold text-red-800 hover:bg-red-50 transition-colors">
                <span class="w-5 h-5 bg-red-600 text-white rounded-full flex items-center justify-center text-xs font-bold">{{ $criticalAlertsCount }}</span>
                <span>Critical Alert{{ $criticalAlertsCount > 1 ? 's' : '' }}</span>
            </a>
            @endif
        </div>
    </div>
    @endif

    {{-- Row 1: Business KPIs (Livewire — period-aware, includes realised margin) --}}
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

    {{-- Row 5: Owner Actions + Alerts + System status --}}
    <div class="section-label">Requires Your Attention</div>
    <div class="row-trace-alerts">
        <livewire:dashboard.owner-actions />
        <div class="right-stack-panel">
            <livewire:dashboard.alerts-panel />
            <livewire:dashboard.system-status />
        </div>
    </div>

</x-app-layout>