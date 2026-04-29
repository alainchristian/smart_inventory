<x-app-layout>
    <div class="max-w-5xl mx-auto px-4 py-6">

        {{-- Header --}}
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
            <div>
                <h1 style="font-size:18px;font-weight:800;color:var(--text);letter-spacing:-.3px;margin:0;">Receipt Search</h1>
                <p style="font-size:13px;color:var(--text-dim);margin:3px 0 0;">Find and reprint receipts by date, customer, sale number or product</p>
            </div>
            <a href="{{ route('shop.pos') }}"
               style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;font-weight:600;color:var(--text-dim);text-decoration:none;background:var(--surface2);white-space:nowrap;flex-shrink:0;">
                ← Back to POS
            </a>
        </div>

        <livewire:shop.sales.reprint-search />

    </div>
</x-app-layout>
