<x-app-layout>
<style>
.sh-page  { padding:12px 0 60px; }
.sh-inner { max-width:1200px;margin:0 auto;padding:0 16px; }

.sh-header { display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;flex-wrap:wrap; }
.sh-header-left  { display:flex;align-items:center;gap:12px; }
.sh-back-btn {
    width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;
    background:var(--surface2);border:1px solid var(--border);color:var(--text-dim);
    text-decoration:none;flex-shrink:0;transition:border-color 0.15s;
}
.sh-back-btn:hover { border-color:var(--accent); }

@media (max-width:480px) {
    .sh-page  { padding:8px 0 60px; }
    .sh-inner { padding:0 8px; }
    .sh-header { margin-bottom:12px; }
}
</style>

<div class="sh-page">
    <div class="sh-inner">

        <div class="sh-header">
            <div class="sh-header-left">
                <a href="{{ route('shop.day-close.index') }}" class="sh-back-btn">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 style="font-size:20px;font-weight:800;color:var(--text);margin:0;line-height:1.2;">Session History</h1>
                    <p style="font-size:12px;color:var(--text-dim);margin:2px 0 0;">All daily sessions for your shop</p>
                </div>
            </div>
        </div>

        <livewire:shop.day-close.session-history />
    </div>
</div>
</x-app-layout>
