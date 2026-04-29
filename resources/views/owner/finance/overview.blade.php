<x-app-layout>
<style>
.fn-ov-head { display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:20px;flex-wrap:wrap; }
@media(max-width:640px) { .fn-ov-head { margin-bottom:12px; } }
</style>
<div class="fn-ov-head">
    <div>
        <h1 style="font-size:22px;font-weight:800;color:var(--text);margin:0 0 2px;letter-spacing:-0.3px;">Finance Overview</h1>
        <p style="font-size:13px;color:var(--text-dim);margin:0;">Multi-day revenue, expenses and cash summary</p>
    </div>
    <a href="{{ route('owner.finance.daily') }}"
       style="padding:7px 14px;border-radius:8px;font-size:13px;font-weight:500;
              background:var(--surface2);color:var(--text-dim);border:1px solid var(--border);
              text-decoration:none;white-space:nowrap;flex-shrink:0;">
        Daily Report →
    </a>
</div>
<livewire:owner.finance.finance-overview />
</x-app-layout>
