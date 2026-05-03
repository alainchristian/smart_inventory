<x-app-layout>
    <div class="py-2 sm:py-6 lg:py-8">
        <div class="max-w-5xl mx-auto px-0 sm:px-4 lg:px-8">

            <div class="mb-4 sm:mb-6 flex items-center justify-between gap-3 flex-wrap">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold" style="color:var(--text);">Transfer Requests</h1>
                    <p class="text-sm mt-0.5" style="color:var(--text-dim);">Track your inventory requests from warehouse</p>
                </div>
                <a href="{{ route('shop.transfers.request') }}"
                   style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:9px;
                          background:var(--accent);color:#fff;font-size:13px;font-weight:700;
                          text-decoration:none;transition:opacity .15s;white-space:nowrap;"
                   onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    New Request
                </a>
            </div>

            <livewire:shop.transfers.transfers-list />

        </div>
    </div>
</x-app-layout>
