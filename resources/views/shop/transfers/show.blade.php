<style>
/* Page header responsive */
.page-header-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    padding: 10px 16px;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    background: white;
    text-decoration: none;
    transition: all 0.15s;
}
.back-btn:hover {
    background: #f9fafb;
}
.back-btn svg {
    margin-right: 8px;
    flex-shrink: 0;
}

@media(max-width:768px) {
    .page-header-wrap {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
    .back-btn {
        order: -1;
        justify-content: center;
        width: 100%;
        padding: 11px 16px;
        font-size: 15px;
    }
    .page-header-wrap > div h1 {
        font-size: 22px;
    }
    .page-header-wrap > div p {
        font-size: 14px;
    }
}

@media(max-width:640px) {
    .page-header-wrap > div h1 {
        font-size: 20px;
    }
    .page-header-wrap > div p {
        font-size: 13px;
        margin-top: 4px;
    }
    .back-btn {
        padding: 12px 16px;
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
                        <h1 class="text-2xl font-bold text-gray-900">Transfer Details</h1>
                        <p class="mt-1 text-base text-gray-600">View your transfer request status and details</p>
                    </div>
                    <a href="{{ route('shop.transfers.index') }}" class="back-btn">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Transfers
                    </a>
                </div>
            </div>

            <!-- Livewire Component -->
            <livewire:shop.transfers.view-transfer :transfer="$transfer" />
        </div>
    </div>
</x-app-layout>
