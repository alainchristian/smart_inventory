<style>

/* Mission 2C: Responsive base — applied to all transfer pages */
@media(max-width:600px) {
    /* Cards */
    .tl-card, .rf-card {
        border-radius:var(--rsm, 8px);
    }
    /* Tables inside cards — make them scroll horizontally */
    table {
        display:block;
        overflow-x:auto;
        -webkit-overflow-scrolling:touch;
        white-space:nowrap;
    }
    /* Prevent text overflow on narrow screens */
    .tl-num, .rf-prod-name, .tl-route-node {
        max-width:140px;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
    }
    /* Badges wrap instead of overflow */
    .tl-card-meta, .tl-dates {
        flex-wrap:wrap;
        gap:4px;
    }
}


/* Responsive base — applied to all transfer pages */
@media(max-width:600px) {
    /* Cards */
    .tl-card, .rf-card {
        border-radius:var(--rsm, 8px);
    }
    /* Tables inside cards — make them scroll horizontally */
    table {
        display:block;
        overflow-x:auto;
        -webkit-overflow-scrolling:touch;
        white-space:nowrap;
    }
    /* Prevent text overflow on narrow screens */
    .tl-num, .rf-prod-name, .tl-route-node {
        max-width:140px;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
    }
    /* Badges wrap instead of overflow */
    .tl-card-meta, .tl-dates {
        flex-wrap:wrap;
        gap:4px;
    }
}

/* 2A - Transfer List Fixes */
@media(max-width:900px) {
    .tl-pipeline { grid-template-columns: repeat(3, 1fr); }
}
@media(max-width:600px) {
    .tl-pipeline { grid-template-columns: repeat(2, 1fr); gap:0; }
    .tl-pipeline-step { padding:10px 12px; }
    .tl-step-num  { font-size:20px; }
    .tl-step-sub  { display:none; }
    .tl-card-top    { flex-direction:column; padding:0 14px; }
    .tl-card-stats  { border-left:none; border-top:1px solid var(--border); margin:0 0 8px; flex-wrap:wrap; }
    .tl-stat        { padding:8px 14px; flex:1; min-width:80px; }
    .tl-bar         { gap:4px; padding:8px 10px; }
    .tl-chip        { padding:4px 10px; font-size:11px; }
    .tl-search      { width:100%; margin-left:0; margin-top:6px; }
    .tl-search input{ width:100%; }
    .tl-route-dash-line { width:20px; }
    .tl-card-foot   { flex-wrap:wrap; gap:6px; }
    .tl-action      { flex:1; justify-content:center; }
    .tl-foot-time   { width:100%; text-align:center; margin-left:0; }
    .tl-page-header         { flex-direction:column; align-items:flex-start; }
    .tl-page-header-left h1 { font-size:20px; }
    .tl-new-btn             { width:100%; justify-content:center; }
}
</style>
<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Transfer Requests</h1>
                        <p class="mt-1 text-2xl text-gray-600">Manage incoming transfer requests from shops</p>
                    </div>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-blue-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="text-2xl text-blue-700">
                            <strong>Review & Approve:</strong> Review transfer requests from shops, approve or reject them, then pack and ship the requested items.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Livewire Component -->
            <livewire:warehouse-manager.transfers.transfers-list />
        </div>
    </div>
</x-app-layout>
