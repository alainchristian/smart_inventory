<x-app-layout>
    <div class="py-2 sm:py-6 lg:py-8">
        <div class="max-w-5xl mx-auto px-0 sm:px-4 lg:px-8">

            <div class="mb-4 sm:mb-6 flex items-center gap-3 flex-wrap">
                <a href="{{ route('warehouse.transfers.index') }}"
                   class="p-2.5 rounded-lg flex-shrink-0"
                   style="background:var(--surface2);color:var(--text-dim);border:1px solid var(--border);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="flex-1">
                    <h1 class="text-2xl sm:text-3xl font-bold" style="color:var(--text);">Review Transfer</h1>
                    <p class="text-sm mt-0.5" style="color:var(--text-dim);">Approve or reject this transfer request</p>
                </div>
                @if(in_array($transfer->status, [\App\Enums\TransferStatus::IN_TRANSIT, \App\Enums\TransferStatus::DELIVERED, \App\Enums\TransferStatus::RECEIVED]))
                <a href="{{ route('warehouse.transfers.delivery-note', $transfer) }}" target="_blank"
                   style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;
                          font-size:12px;font-weight:700;text-decoration:none;white-space:nowrap;
                          background:var(--surface2);color:var(--text);border:1px solid var(--border);transition:all .15s;"
                   onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'"
                   onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)'">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Delivery Note
                </a>
                @endif
            </div>

            <livewire:warehouse-manager.transfers.review-transfer :transfer="$transfer" />

        </div>
    </div>
</x-app-layout>
