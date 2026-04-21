<x-app-layout>
    <div class="max-w-5xl mx-auto px-4 py-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold" style="color:var(--text);letter-spacing:-.3px;">Receipt Search</h1>
                <p class="text-sm mt-0.5" style="color:var(--text-dim);">Find and reprint receipts by date, customer, sale number or product</p>
            </div>
            <a href="{{ route('shop.pos') }}"
               style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;font-weight:600;color:var(--text-dim);text-decoration:none;background:var(--surface-raised);">
                ← Back to POS
            </a>
        </div>

        <livewire:shop.sales.reprint-search />

    </div>
</x-app-layout>
