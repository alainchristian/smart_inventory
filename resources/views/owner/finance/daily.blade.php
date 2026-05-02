<x-app-layout>
<style>
.fn-dy-head { display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:24px;flex-wrap:wrap; }
@media(max-width:640px) { .fn-dy-head { margin-bottom:14px; } }
</style>
<div class="fn-dy-head">
    <div>
        <h1 style="font-size:22px;font-weight:800;color:var(--text);margin:0 0 2px;letter-spacing:-0.3px;">Daily Close Report</h1>
        <p style="font-size:13px;color:var(--text-dim);margin:0;">Review and lock daily sessions by date</p>
    </div>
    <a href="{{ route('owner.finance.overview') }}"
       style="padding:7px 16px;border-radius:8px;font-size:13px;font-weight:600;
              background:white;color:var(--text-dim);border:1px solid var(--border);
              text-decoration:none;white-space:nowrap;flex-shrink:0;
              box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        Finance Overview →
    </a>
</div>
<livewire:owner.finance.daily-close-report />
</x-app-layout>
