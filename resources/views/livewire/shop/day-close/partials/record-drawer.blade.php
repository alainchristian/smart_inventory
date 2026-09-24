{{--
    Slide-in drawer for recording an expense / owner withdrawal / bank deposit
    against an open session. Opened from anywhere on the page with
        $dispatch('dc-record', { type: 'expense' | 'withdrawal' | 'deposit' })
    Closes itself when the child form reports a successful save.
    Expects: $dailySessionId
--}}
<div x-data="{ type: null }"
     @dc-record.window="type = $event.detail.type"
     @dc-record-close.window="type = null"
     @expense-added.window="type = null"
     @withdrawal-added.window="type = null"
     @deposit-added.window="type = null"
     @keydown.escape.window="type = null"
     x-effect="document.documentElement.style.overflow = type ? 'hidden' : ''"
     style="font-family:var(--font)">
<style>
.rd-overlay { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);backdrop-filter:blur(2px); }
.rd-drawer  { position:fixed;top:0;right:0;bottom:0;z-index:401;width:440px;max-width:100vw;background:var(--surface);
              border-left:1px solid var(--border);box-shadow:-8px 0 40px rgba(26,31,54,.14);display:flex;flex-direction:column;
              transform:translateX(100%);transition:transform .22s cubic-bezier(.4,0,.2,1);visibility:hidden; }
.rd-drawer.open { transform:translateX(0);visibility:visible; }
.rd-head  { display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:18px 22px;border-bottom:1px solid var(--border);flex-shrink:0; }
.rd-title { font-size:16px;font-weight:800;color:var(--text);margin:0; }
.rd-sub   { font-size:12px;color:var(--text-dim);margin-top:3px;line-height:1.45; }
.rd-close { width:32px;height:32px;border-radius:8px;border:none;background:var(--surface2);color:var(--text-sub);cursor:pointer;
            display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background var(--tr); }
.rd-close:hover { background:var(--surface3); }
.rd-body  { flex:1;overflow-y:auto;padding:22px; }
@media (max-width:640px) {
    .rd-close { padding:0 !important;min-height:32px !important;min-width:32px !important; }
}
@media (max-width:768px) {
    .rd-drawer { left:0;width:auto; }
    .rd-body   { padding:16px; }
}
</style>

    <div class="rd-overlay" x-show="type" x-cloak x-transition.opacity.duration.150ms @click="type = null"></div>

    <aside class="rd-drawer" :class="{ open: type }" role="dialog" aria-modal="true" :aria-hidden="!type">
        <div class="rd-head">
            <div>
                <h2 class="rd-title" x-text="{ expense: 'Record expense', withdrawal: 'Owner withdrawal', deposit: 'Bank deposit' }[type] ?? ''"></h2>
                <div class="rd-sub" x-text="{
                    expense: 'An operational cost paid out of today\'s takings.',
                    withdrawal: 'Cash or MoMo taken by the owner. Reported separately from expenses.',
                    deposit: 'Money moved from the drawer or MoMo into the bank.'
                }[type] ?? ''"></div>
            </div>
            <button type="button" class="rd-close" @click="type = null" aria-label="Close">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="rd-body">
            <div x-show="type === 'expense'">
                <livewire:shop.day-close.add-expense :dailySessionId="$dailySessionId" :inDrawer="true" :key="'rd-exp-'.$dailySessionId" />
            </div>
            <div x-show="type === 'withdrawal'">
                <livewire:shop.day-close.add-withdrawal :dailySessionId="$dailySessionId" :inDrawer="true" :key="'rd-wd-'.$dailySessionId" />
            </div>
            <div x-show="type === 'deposit'">
                <livewire:shop.day-close.add-bank-deposit :dailySessionId="$dailySessionId" :inDrawer="true" :key="'rd-dep-'.$dailySessionId" />
            </div>
        </div>
    </aside>
</div>
