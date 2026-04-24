<x-app-layout>
<div style="padding:28px 0 80px;">
    <div style="max-width:1100px;margin:0 auto;padding:0 20px;">

        {{-- Header --}}
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:28px;">
            <a href="{{ route('shop.day-close.index') }}"
               style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;
                      background:var(--surface-raised);border:1px solid var(--border);color:var(--text-dim);
                      text-decoration:none;flex-shrink:0;transition:border-color 0.15s;"
               onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 style="font-size:22px;font-weight:800;color:var(--text);margin:0;">Session History</h1>
                <p style="font-size:13px;color:var(--text-dim);margin:0;">Closed and locked daily sessions</p>
            </div>
        </div>

        <livewire:shop.day-close.session-history />
    </div>
</div>
</x-app-layout>
