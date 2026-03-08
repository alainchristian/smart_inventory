<style>
/* Page header responsive */
.page-header-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}

.info-alert-box {
    margin-bottom: 24px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    padding: 14px 16px;
}

@media(max-width:768px) {
    .page-header-wrap > div h1 {
        font-size: 22px;
        margin-bottom: 4px;
    }
    .page-header-wrap > div p {
        font-size: 14px;
    }
    .info-alert-box {
        margin-bottom: 20px;
        padding: 12px 14px;
    }
    .info-alert-box p {
        font-size: 13px;
    }
}

@media(max-width:640px) {
    .page-header-wrap > div h1 {
        font-size: 20px;
    }
    .info-alert-box {
        margin-bottom: 16px;
        padding: 10px 12px;
    }
    .info-alert-box p {
        font-size: 12px;
        line-height: 1.5;
    }
    .info-alert-box svg {
        width: 18px;
        height: 18px;
    }
}

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

</style>
<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-6">
                <div class="page-header-wrap">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Transfer Requests</h1>
                        <p class="mt-1 text-base text-gray-600">View and manage your inventory transfer requests</p>
                    </div>
                </div>
            </div>

            <!-- Info Alert (hidden on mobile via CSS) -->
            <div class="info-alert-box">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-blue-700">
                        <strong>Transfer Workflow:</strong> Request transfers, track status, and receive inventory when delivered.
                    </p>
                </div>
            </div>

            <!-- Livewire Component -->
            <livewire:shop.transfers.transfers-list />
        </div>
    </div>
</x-app-layout>
