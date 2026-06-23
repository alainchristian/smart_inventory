<div>
@if($sessionBlocked)
    <x-session-gate-blocked
        :reason="$sessionBlockReason"
        :session-date="$blockedSessionDate"
        :session-id="$blockedSessionId"
    />
@else
<div class="upos-page" style="font-family:var(--font)">

{{-- Toast stack --}}
<div x-data="{
    toasts: [],
    toast(msg, type) {
      const id = Date.now() + Math.random();
      this.toasts.push({ id, msg, type });
      setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 3800);
    }
  }"
  @notification.window="toast($event.detail.message, $event.detail.type)"
  style="position:fixed;top:72px;right:16px;z-index:9000;display:flex;flex-direction:column;gap:7px;pointer-events:none">
  <template x-for="t in toasts" :key="t.id">
    <div x-show="true"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         :style="`pointer-events:auto;padding:10px 14px;border-radius:8px;font-size:13px;font-weight:600;font-family:var(--font);box-shadow:0 4px 16px rgba(26,31,54,.15);max-width:320px;
           background:${t.type==='success'?'var(--green)':t.type==='error'?'var(--red)':t.type==='warning'?'var(--amber)':'var(--accent)'};color:#fff`"
         x-text="t.msg">
    </div>
  </template>
</div>
<style>
/* ── Scrollbars ────────────────────────────────────────────────────────── */
.upos-page ::-webkit-scrollbar, .upos-overlay ::-webkit-scrollbar, .upos-cart-drawer ::-webkit-scrollbar { width:6px; height:6px }
.upos-page ::-webkit-scrollbar-track, .upos-overlay ::-webkit-scrollbar-track, .upos-cart-drawer ::-webkit-scrollbar-track { background:transparent }
.upos-page ::-webkit-scrollbar-thumb, .upos-overlay ::-webkit-scrollbar-thumb, .upos-cart-drawer ::-webkit-scrollbar-thumb { background:var(--border); border-radius:3px }
.upos-page ::-webkit-scrollbar-thumb:hover, .upos-overlay ::-webkit-scrollbar-thumb:hover, .upos-cart-drawer ::-webkit-scrollbar-thumb:hover { background:var(--text-dim) }

/* ── Layout ────────────────────────────────────────────────────────────── */
.upos-page { padding:0 0 80px; overflow-x:hidden }
.upos-layout { display:grid; grid-template-columns:minmax(0,1fr) 380px; gap:16px; align-items:start }

/* ── Page header ────────────────────────────────────────────────────────── */
.upos-header { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:20px; flex-wrap:wrap }
.upos-header-left { display:flex; align-items:center; gap:12px }
.upos-header-icon { width:44px; height:44px; border-radius:12px; background:var(--accent-dim); display:flex; align-items:center; justify-content:center; flex-shrink:0 }
.upos-header-title { font-size:22px; font-weight:800; color:var(--text); margin:0 0 3px; line-height:1.2 }
.upos-header-sub { font-size:13px; color:var(--text-dim); margin:0 }
.upos-header-right { display:flex; align-items:center; gap:8px; flex-wrap:wrap }
.upos-shop-chip { display:flex; align-items:center; gap:6px; padding:6px 12px; background:var(--accent-dim); color:var(--accent); border-radius:20px; font-size:12px; font-weight:600; cursor:pointer; transition:background var(--tr); border:none }
.upos-shop-chip:hover { background:var(--accent-glow) }

/* ── Source badges ──────────────────────────────────────────────────────── */
.upos-badge { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:20px; font-size:10px; font-weight:700; letter-spacing:.3px; text-transform:uppercase; flex-shrink:0 }
.upos-badge.shop { background:color-mix(in srgb,var(--green) 10%,var(--surface)); color:var(--green) }
.upos-badge.warehouse { background:var(--accent-dim); color:var(--accent) }

/* ── Left panel ─────────────────────────────────────────────────────────── */
.upos-left { display:flex; flex-direction:column; gap:12px; min-width:0 }

/* ── Right panel (cart column) ──────────────────────────────────────────── */
.upos-right { align-self: stretch }

/* ── Search bar ─────────────────────────────────────────────────────────── */
.upos-search-card { background:var(--surface); border:none; box-shadow:var(--shadow-card); border-radius:var(--r); padding:12px 14px; display:flex; flex-direction:column; gap:10px; min-width:0 }
.upos-search-row { display:flex; gap:8px; align-items:center; min-width:0 }
.upos-search-wrap { position:relative; flex:1; min-width:0 }
.upos-search-icon { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-dim); pointer-events:none }
.upos-search-input { width:100%; padding:9px 12px 9px 34px; border-radius:var(--rsm); border:1.5px solid var(--border); background:var(--surface); color:var(--text); font-size:14px; font-family:var(--font); transition:border-color var(--tr); box-sizing:border-box }
.upos-search-input:focus { outline:none; border-color:var(--accent); background:var(--surface) }
.upos-icon-btn { width:36px; height:36px; border-radius:var(--rsm); border:1.5px solid var(--border); background:var(--surface); color:var(--text-dim); cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all var(--tr); flex-shrink:0 }
.upos-icon-btn:hover { border-color:var(--accent); color:var(--accent); background:var(--accent-dim) }
.upos-icon-btn.active { border-color:var(--accent); background:var(--accent-dim); color:var(--accent) }
.upos-barcode-input { flex:1; padding:9px 12px; border-radius:var(--rsm); border:1.5px solid var(--border); background:var(--surface); color:var(--text); font-size:14px; font-family:var(--mono); transition:border-color var(--tr) }
.upos-barcode-input:focus { outline:none; border-color:var(--accent); background:var(--surface) }

/* ── Filter pills ───────────────────────────────────────────────────────── */
.upos-filter-strip { display:flex; gap:6px; flex-wrap:wrap; min-width:0 }
.upos-filter-pill { padding:5px 14px; border-radius:20px; border:1.5px solid var(--border); background:var(--surface); color:var(--text-dim); font-size:12px; font-weight:600; cursor:pointer; transition:all var(--tr); white-space:nowrap }
.upos-filter-pill:hover { border-color:var(--accent); color:var(--accent) }
.upos-filter-pill.active { background:var(--accent); border-color:var(--accent); color:#fff; box-shadow:0 2px 8px rgba(0,0,0,.12) }
.upos-filter-pill .count { display:inline-block; background:var(--surface2); border-radius:10px; padding:1px 6px; font-size:10px; margin-left:4px; font-family:var(--mono) }
.upos-filter-pill.active .count { background:rgba(255,255,255,.2); color:#fff }

/* ── Scanner panel ──────────────────────────────────────────────────────── */
.upos-scanner-panel { background:var(--bg); border-radius:var(--rsm); padding:12px; display:flex; align-items:center; gap:12px }
.upos-scanner-qr { width:80px; height:80px; background:var(--surface); border-radius:var(--rsm); box-shadow:var(--shadow-card); display:flex; align-items:center; justify-content:center; flex-shrink:0; overflow:hidden }
.upos-scanner-info { flex:1; min-width:0 }
.upos-scanner-code { font-family:var(--mono); font-size:16px; font-weight:700; color:var(--text); letter-spacing:2px; word-break:break-all }
.upos-scanner-close { padding:5px 12px; border-radius:var(--rsm); border:1.5px solid var(--border); background:var(--surface); color:var(--text-dim); font-size:12px; cursor:pointer; transition:all var(--tr) }
.upos-scanner-close:hover { border-color:var(--red); color:var(--red) }

/* ── Held sales strip ───────────────────────────────────────────────────── */
.upos-held-strip { background:var(--surface); border:none; box-shadow:var(--shadow-card); border-radius:var(--r); padding:12px 14px }
.upos-held-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px }
.upos-held-title { font-size:13px; font-weight:700; color:var(--text) }
.upos-held-list { display:flex; flex-direction:column; gap:6px }
.upos-held-item { display:flex; align-items:center; gap:8px; padding:8px 10px; border-radius:var(--rsm); background:var(--bg); }
.upos-held-ref { font-size:12px; font-weight:700; font-family:var(--mono); color:var(--text); flex:1 }
.upos-held-meta { font-size:11px; color:var(--text-dim) }
.upos-held-total { font-size:12px; font-weight:700; font-family:var(--mono); color:var(--text-sub); white-space:nowrap }
.upos-held-action { padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; border:none; cursor:pointer; transition:all var(--tr) }
.upos-held-resume { background:var(--accent-dim); color:var(--accent) }
.upos-held-resume:hover { background:var(--accent-glow) }
.upos-held-discard { background:var(--red-dim); color:var(--red) }
.upos-held-discard:hover { opacity:.8 }
.upos-held-approval-badge { padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700; background:var(--amber-dim); color:var(--amber); white-space:nowrap }
.upos-held-approved-badge { padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700; background:color-mix(in srgb,var(--green) 10%,var(--surface)); color:var(--green); white-space:nowrap }

/* ── Stock grid ─────────────────────────────────────────────────────────── */
.upos-stock-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(162px,1fr)); gap:10px }
.upos-tile { background:var(--surface); border:none; box-shadow:var(--shadow-card); border-radius:var(--r); padding:12px; display:flex; flex-direction:column; gap:8px; cursor:pointer; transition:all var(--tr) }
.upos-tile:hover { box-shadow:var(--shadow-card-hover); transform:translateY(-2px) }
.upos-tile-top { display:flex; align-items:center; justify-content:space-between; gap:6px }
.upos-stock-count { font-size:10px; font-weight:700; font-family:var(--mono); color:var(--text-dim); background:var(--bg); padding:2px 7px; border-radius:10px }
.upos-tile-name { font-size:13px; font-weight:700; color:var(--text); line-height:1.3; flex:1 }
.upos-tile-cat { font-size:11px; color:var(--text-dim) }
.upos-tile-price { font-size:15px; font-weight:800; font-family:var(--mono); color:var(--accent) }
.upos-tile-bar-wrap { height:3px; background:var(--border); border-radius:2px; overflow:hidden }
.upos-tile-bar { height:3px; border-radius:2px; background:var(--green); transition:width .3s }
.upos-tile-add { width:100%; padding:7px 0; border-radius:var(--rsm); border:none; background:var(--accent-dim); color:var(--accent); font-size:12px; font-weight:700; font-family:var(--font); cursor:pointer; transition:all var(--tr) }
.upos-tile-add:hover { background:var(--accent-glow); transform:scale(1.02) }
.upos-empty-state { text-align:center; padding:40px 20px; color:var(--text-dim) }
.upos-empty-icon { width:48px; height:48px; margin:0 auto 12px; opacity:.4 }

/* ── Cart panel ─────────────────────────────────────────────────────────── */
.upos-cart-panel { background:var(--surface); border:none; box-shadow:var(--shadow-card); border-radius:var(--r); position:sticky; top:calc(var(--topbar-height) + 12px); display:flex; flex-direction:column; max-height:calc(100vh - var(--topbar-height) - 24px) }
.upos-cart-header { display:flex; align-items:center; gap:8px; padding:14px 16px 12px; border-bottom:1px solid var(--border); flex-shrink:0 }
.upos-cart-title { font-size:14px; font-weight:800; color:var(--text); flex:1 }
.upos-cart-badge { min-width:20px; height:20px; background:var(--accent); color:#fff; border-radius:10px; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; padding:0 5px; font-family:var(--mono) }
.upos-cart-clear { padding:4px 10px; border-radius:20px; border:1.5px solid var(--border); background:transparent; color:var(--text-dim); font-size:11px; cursor:pointer; transition:all var(--tr); font-family:var(--font) }
.upos-cart-clear:hover { border-color:var(--red); color:var(--red) }
.upos-cart-items { overflow-y:auto; flex:1; padding:10px; display:flex; flex-direction:column; gap:8px; min-height:0 }
.upos-cart-empty { text-align:center; padding:30px 16px; color:var(--text-dim); font-size:13px }
.upos-cart-item { background:var(--bg); border-radius:var(--rsm); padding:10px }
.upos-cart-item-top { display:flex; align-items:flex-start; justify-content:space-between; gap:8px; margin-bottom:6px }
.upos-cart-item-name { font-size:13px; font-weight:700; color:var(--text); line-height:1.3 }
.upos-cart-item-mode { display:inline-block; padding:1px 6px; border-radius:4px; font-size:10px; font-weight:700; background:var(--surface); color:var(--text-dim); margin-top:2px }
.upos-cart-item-row { display:flex; align-items:center; justify-content:space-between }
.upos-cart-item-qty { font-size:12px; color:var(--text-dim) }
.upos-cart-item-total { font-size:13px; font-weight:800; font-family:var(--mono); color:var(--text) }
.upos-cart-item-actions { display:flex; gap:4px }
.upos-cart-item-btn { width:26px; height:26px; border-radius:6px; border:1.5px solid var(--border); background:transparent; color:var(--text-dim); cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all var(--tr) }
.upos-cart-item-btn:hover { border-color:var(--accent); color:var(--accent) }
.upos-cart-item-btn.del:hover { border-color:var(--red); color:var(--red) }
.upos-cart-footer { padding:12px 14px; border-top:1px solid var(--border); flex-shrink:0 }
.upos-cart-subtotal { display:flex; justify-content:space-between; font-size:12px; color:var(--text-dim); margin-bottom:4px }
.upos-cart-total { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px }
.upos-cart-total-label { font-size:13px; font-weight:700; color:var(--text) }
.upos-cart-total-val { font-size:20px; font-weight:800; font-family:var(--mono); color:var(--text) }
.upos-btn-checkout { display:block; width:100%; padding:12px; border-radius:var(--rsm); border:none; background:var(--accent); color:#fff; font-size:14px; font-weight:700; font-family:var(--font); cursor:pointer; transition:opacity var(--tr); box-shadow:0 3px 10px rgba(59,111,212,.25) }
.upos-btn-checkout:hover { opacity:.9 }
.upos-btn-checkout:disabled { opacity:.5; cursor:not-allowed }
.upos-cart-actions { display:flex; gap:6px; margin-top:8px }
.upos-cart-action-btn { flex:1; padding:8px; border-radius:var(--rsm); border:1.5px solid var(--border); background:transparent; color:var(--text-dim); font-size:11px; font-weight:600; cursor:pointer; transition:all var(--tr); font-family:var(--font) }
.upos-cart-action-btn:hover { border-color:var(--accent); color:var(--accent) }
.upos-held-toggle { font-size:11px; color:var(--accent); cursor:pointer; text-decoration:none; background:none; border:none; font-family:var(--font); padding:0 }

/* ── Mobile FAB & drawer ─────────────────────────────────────────────────── */
.upos-cart-fab { position:fixed; bottom:24px; right:20px; width:56px; height:56px; border-radius:50%; border:none; background:var(--accent); color:#fff; cursor:pointer; display:none; align-items:center; justify-content:center; box-shadow:0 4px 16px var(--accent-glow); z-index:200; transition:opacity var(--tr) }
.upos-cart-fab:hover { opacity:.9 }
.upos-fab-badge { position:absolute; top:-2px; right:-2px; min-width:18px; height:18px; background:var(--red); color:#fff; border-radius:9px; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; padding:0 4px; font-family:var(--mono); border:2px solid var(--surface) }
.upos-cart-drawer-overlay { position:fixed; inset:0; background:rgba(26,31,54,.45); z-index:300; display:none; backdrop-filter:blur(3px) }
.upos-cart-drawer { position:fixed; bottom:0; left:0; right:0; height:88vh; background:var(--surface); border-radius:16px 16px 0 0; z-index:301; display:flex; flex-direction:column; box-shadow:0 -4px 32px rgba(26,31,54,.15); transform:translateY(100%); transition:transform .3s cubic-bezier(.4,0,.2,1) }
.upos-cart-drawer.open { transform:translateY(0) }
.upos-drawer-handle { width:36px; height:4px; background:var(--border); border-radius:2px; margin:10px auto 4px; flex-shrink:0 }

/* ── Buttons ─────────────────────────────────────────────────────────────── */
.upos-btn-primary { padding:10px 20px; border-radius:var(--rsm); border:none; background:var(--accent); color:#fff; font-size:14px; font-weight:700; font-family:var(--font); cursor:pointer; transition:opacity var(--tr); box-shadow:0 3px 10px rgba(59,111,212,.25) }
.upos-btn-primary:hover { opacity:.9 }
.upos-btn-primary:disabled { opacity:.5; cursor:not-allowed }
.upos-btn-ghost { padding:10px 20px; border-radius:var(--rsm); border:1.5px solid var(--border); background:transparent; color:var(--text-dim); font-size:14px; font-weight:600; font-family:var(--font); cursor:pointer; transition:all var(--tr) }
.upos-btn-ghost:hover { border-color:var(--accent); color:var(--accent) }
.upos-btn-sm { padding:7px 14px; font-size:12px; border-radius:var(--rsm); border:1.5px solid var(--border); background:transparent; color:var(--text-dim); font-family:var(--font); cursor:pointer; transition:all var(--tr) }
.upos-btn-sm:hover { border-color:var(--accent); color:var(--accent) }
.upos-btn-danger { background:var(--red); color:#fff; border:none; padding:10px 20px; border-radius:var(--rsm); font-size:14px; font-weight:700; font-family:var(--font); cursor:pointer; transition:opacity var(--tr) }
.upos-btn-danger:hover { opacity:.85 }

/* ── Form fields ─────────────────────────────────────────────────────────── */
.upos-field { display:flex; flex-direction:column; gap:5px }
.upos-label { font-size:12px; font-weight:600; color:var(--text-sub) }
.upos-input { padding:9px 12px; border-radius:var(--rsm); border:1.5px solid var(--border); background:var(--surface); color:var(--text); font-size:14px; font-family:var(--font); transition:border-color var(--tr); width:100%; box-sizing:border-box }
.upos-input:focus { outline:none; border-color:var(--accent) }
.upos-select { appearance:none; padding:9px 32px 9px 12px; border-radius:var(--rsm); border:1.5px solid var(--border); background:var(--surface) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a81a0' stroke-width='2'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") no-repeat right 10px center; color:var(--text); font-size:14px; font-family:var(--font); cursor:pointer; width:100%; box-sizing:border-box; transition:border-color var(--tr) }
.upos-select:focus { outline:none; border-color:var(--accent) }

/* ── Modal overlays ──────────────────────────────────────────────────────── */
.upos-overlay { position:fixed; inset:0; background:rgba(26,31,54,.5); z-index:400; display:flex; align-items:center; justify-content:center; padding:16px; backdrop-filter:blur(3px) }
.upos-modal-card { background:var(--surface); border-radius:var(--r); box-shadow:0 16px 48px rgba(26,31,54,.18); width:100%; max-height:92vh; overflow-y:auto; display:flex; flex-direction:column }

/* ── Staging modal ───────────────────────────────────────────────────────── */
.upos-sm-card { max-width:520px }
.upos-sm-head { padding:18px 20px 14px; border-bottom:1px solid var(--border) }
.upos-sm-title { font-size:16px; font-weight:800; color:var(--text); margin:0 0 4px }
.upos-sm-sub { font-size:12px; color:var(--text-dim); margin:0 }
.upos-sm-body { padding:16px 20px; display:flex; flex-direction:column; gap:14px }
.upos-sm-info { background:var(--bg); border-radius:var(--rsm); padding:10px 14px }
.upos-sm-info-row { display:flex; justify-content:space-between; font-size:12px; margin-bottom:3px }
.upos-mode-toggle { display:flex; border-radius:var(--rsm); overflow:hidden; border:1.5px solid var(--border) }
.upos-mode-btn { flex:1; padding:8px; border:none; background:transparent; color:var(--text-dim); font-size:13px; font-weight:600; font-family:var(--font); cursor:pointer; transition:all var(--tr) }
.upos-mode-btn.active { background:var(--accent); color:#fff }
.upos-stepper { display:flex; align-items:center; gap:0; border:1.5px solid var(--border); border-radius:var(--rsm); overflow:hidden }
.upos-stepper-btn { width:40px; height:40px; border:none; background:transparent; color:var(--text-sub); font-size:18px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background var(--tr); flex-shrink:0 }
.upos-stepper-btn:hover { background:var(--surface2) }
.upos-stepper-val { flex:1; text-align:center; font-size:16px; font-weight:700; font-family:var(--mono); color:var(--text); border:none; border-left:1px solid var(--border); border-right:1px solid var(--border); padding:8px 4px }
.upos-sm-total { background:var(--bg); border-radius:var(--rsm); padding:12px 14px; display:flex; justify-content:space-between; align-items:center }
.upos-sm-total-label { font-size:12px; color:var(--text-dim) }
.upos-sm-total-val { font-size:22px; font-weight:800; font-family:var(--mono); color:var(--text) }
.upos-sm-foot { display:flex; gap:10px; padding:14px 20px; border-top:1px solid var(--border) }
.upos-sm-foot button { flex:1 }
.upos-price-row { display:flex; align-items:center; gap:6px }
.upos-price-row .upos-input { flex:1 }
.upos-price-modified-badge { padding:2px 8px; border-radius:10px; background:var(--amber-dim); color:var(--amber); font-size:10px; font-weight:700; white-space:nowrap }

/* ── Checkout modal ──────────────────────────────────────────────────────── */
.upos-co-card { max-width:800px; height:min(900px,94vh) }
.upos-co-head { padding:14px 20px 12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-shrink:0 }
.upos-co-title { font-size:15px; font-weight:800; color:var(--text) }
.upos-co-close { width:28px; height:28px; border-radius:6px; border:1.5px solid var(--border); background:transparent; color:var(--text-dim); cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:14px; transition:all var(--tr) }
.upos-co-close:hover { border-color:var(--red); color:var(--red) }
.upos-co-body { display:grid; grid-template-columns:1fr 1fr; gap:0; flex:1; overflow:hidden; min-height:0 }
.upos-co-left { padding:16px 16px 16px 20px; border-right:1px solid var(--border); display:flex; flex-direction:column; gap:12px; overflow-y:auto }
.upos-co-right { padding:14px 18px 14px 14px; display:flex; flex-direction:column; gap:8px; overflow-y:auto }
.upos-co-section-title { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-dim); margin-bottom:6px }
.upos-order-summary { background:transparent; border:1.5px solid var(--border); border-radius:var(--rsm); padding:12px; max-height:220px; overflow-y:auto }
.upos-order-row { display:flex; justify-content:space-between; align-items:center; padding:5px 0; border-bottom:1px solid var(--border); gap:8px }
.upos-order-row:last-child { border-bottom:none }
.upos-order-item-name { font-size:12px; color:var(--text-sub); flex:1 }
.upos-order-item-badge { margin-left:4px }
.upos-order-item-total { font-size:12px; font-weight:700; font-family:var(--mono); color:var(--text); white-space:nowrap }
.upos-order-total-row { display:flex; justify-content:space-between; align-items:center; padding-top:10px; margin-top:4px; border-top:2px solid var(--border) }
.upos-order-total-label { font-size:13px; font-weight:700; color:var(--text) }
.upos-order-total-val { font-size:18px; font-weight:800; font-family:var(--mono); color:var(--text) }

/* Customer section */
.upos-customer-search-wrap { position:relative }
.upos-customer-input-row { display:flex; gap:6px }
.upos-customer-input-row .upos-input { flex:1 }
.upos-customer-dropdown { position:absolute; top:calc(100%+4px); left:0; right:0; background:var(--surface); border:1.5px solid var(--border); border-radius:var(--rsm); z-index:50; max-height:160px; overflow-y:auto; box-shadow:0 4px 16px rgba(26,31,54,.1) }
.upos-customer-row { display:flex; flex-direction:column; padding:8px 12px; cursor:pointer; transition:background var(--tr) }
.upos-customer-row:hover { background:var(--surface2) }
.upos-customer-row-name { font-size:13px; font-weight:600; color:var(--text) }
.upos-customer-row-phone { font-size:11px; color:var(--text-dim) }
.upos-customer-selected { background:var(--bg); border-radius:var(--rsm); padding:8px 12px; display:flex; align-items:center; justify-content:space-between }
.upos-new-customer-form { background:var(--bg); border-radius:var(--rsm); padding:12px; display:flex; flex-direction:column; gap:8px }
.upos-credit-warning { background:var(--amber-dim); border-radius:var(--rsm); padding:8px 12px; font-size:12px; color:var(--amber); display:flex; align-items:center; gap:6px }

/* Fulfillment block */
.upos-fulfillment-block { background:color-mix(in srgb,var(--accent) 5%,var(--surface)); border-radius:var(--rsm); padding:12px; border:1px solid var(--accent-dim) }
.upos-fulfillment-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--accent); margin-bottom:10px }
.upos-fulfillment-toggle { display:flex; border-radius:var(--rsm); overflow:hidden; border:1.5px solid var(--border); margin-bottom:10px }
.upos-fulfillment-btn { flex:1; padding:7px; border:none; background:transparent; color:var(--text-dim); font-size:12px; font-weight:600; font-family:var(--font); cursor:pointer; transition:all var(--tr) }
.upos-fulfillment-btn.active { background:var(--accent); color:#fff }
.upos-fulfillment-note { margin-top:8px; font-size:11px; color:var(--accent); display:flex; align-items:flex-start; gap:5px }

/* Balance strip */
.upos-bal-strip { background:var(--surface); background:color-mix(in srgb,var(--accent) 5%,var(--surface)); border:1.5px solid var(--accent-dim); border-radius:var(--rsm); padding:9px 12px }
.upos-bal-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:6px }
.upos-bal-label { font-size:11px; color:var(--text-dim) }
.upos-bal-val { font-size:15px; font-weight:800; font-family:var(--mono); color:var(--text) }
.upos-bal-progress { height:4px; background:var(--border); border-radius:2px; overflow:hidden; margin-bottom:5px }
.upos-bal-progress-fill { height:4px; background:var(--green); border-radius:2px; transition:width .2s }
.upos-bal-remain { font-size:11px; color:var(--text-dim); text-align:right }
.upos-bal-remain.red { color:var(--red) }
.upos-bal-remain.green { color:var(--green) }

/* Payment channels */
.upos-pay-row { display:flex; flex-direction:column; gap:3px }
.upos-pay-label-row { display:flex; align-items:center; justify-content:space-between }
.upos-pay-label { font-size:11px; font-weight:600; color:var(--text-sub) }
.upos-pay-auto-badge { font-size:10px; color:var(--text-dim); font-style:italic }
.upos-pay-ref { margin-top:2px }
.upos-pay-ref .upos-pay-input { padding:6px 10px; font-size:12px !important; font-weight:600; font-family:var(--font) }
.upos-pay-input { padding:8px 12px; border-radius:var(--rsm); border:1.5px solid var(--border); background:var(--surface); color:var(--text); font-size:16px; font-weight:700; font-family:var(--mono); transition:border-color var(--tr); width:100%; box-sizing:border-box }
.upos-pay-input:focus { outline:none; border-color:var(--accent) }
.upos-pay-input:disabled { opacity:.5; cursor:not-allowed }
.upos-cash-display { font-size:17px; font-weight:800; font-family:var(--mono); color:var(--text); line-height:1 }
.upos-co-complete { width:100%; padding:10px; border-radius:var(--rsm); border:none; background:var(--accent); color:#fff; font-size:14px; font-weight:700; font-family:var(--font); cursor:pointer; transition:opacity var(--tr); box-shadow:0 3px 10px rgba(59,111,212,.25) }
.upos-co-complete:hover { opacity:.9 }
.upos-co-complete:disabled { opacity:.5; cursor:not-allowed }

/* ── Receipt modal ───────────────────────────────────────────────────────── */
.upos-rc-card { max-width:480px }
.upos-rc-banner { background:var(--green); padding:20px 24px 16px; display:flex; align-items:center; gap:12px }
.upos-rc-banner-icon { width:40px; height:40px; background:rgba(255,255,255,.2); border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0 }
.upos-rc-banner-title { font-size:18px; font-weight:800; color:#fff; margin:0 0 3px }
.upos-rc-banner-sub { font-size:12px; color:rgba(255,255,255,.8); margin:0 }
.upos-rc-body { padding:20px 24px; display:flex; flex-direction:column; gap:14px }
.upos-rc-meta { display:flex; justify-content:space-between; font-size:12px; color:var(--text-dim) }
.upos-rc-items { background:var(--bg); border-radius:var(--rsm); padding:10px }
.upos-rc-item { display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid var(--border); gap:8px }
.upos-rc-item:last-child { border-bottom:none }
.upos-rc-item-name { font-size:12px; color:var(--text-sub); flex:1 }
.upos-rc-item-total { font-size:12px; font-weight:700; font-family:var(--mono); color:var(--text) }
.upos-rc-payments { background:var(--bg); border-radius:var(--rsm); padding:10px }
.upos-rc-pay-row { display:flex; justify-content:space-between; font-size:12px; padding:4px 0 }
.upos-rc-total-row { display:flex; justify-content:space-between; padding-top:8px; border-top:2px solid var(--border); margin-top:4px }
.upos-rc-total-label { font-size:14px; font-weight:700; color:var(--text) }
.upos-rc-total-val { font-size:18px; font-weight:800; font-family:var(--mono); color:var(--text) }
.upos-rc-wh-note { background:var(--amber-dim); border-radius:var(--rsm); padding:10px 12px; font-size:12px; color:var(--amber); display:flex; align-items:flex-start; gap:8px }
.upos-rc-foot { display:flex; gap:10px; padding:14px 24px; border-top:1px solid var(--border) }
.upos-rc-foot button { flex:1 }

/* ── Shop selection modal ────────────────────────────────────────────────── */
.upos-shop-modal-card { max-width:420px }
.upos-shop-modal-head { padding:20px 24px 14px; border-bottom:1px solid var(--border) }
.upos-shop-modal-title { font-size:16px; font-weight:800; color:var(--text); margin:0 0 4px }
.upos-shop-modal-sub { font-size:12px; color:var(--text-dim); margin:0 }
.upos-shop-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; padding:16px 24px }
.upos-shop-btn { padding:14px 12px; border-radius:var(--rsm); border:1.5px solid var(--border); background:var(--surface); color:var(--text); font-size:13px; font-weight:600; cursor:pointer; text-align:left; transition:all var(--tr); font-family:var(--font) }
.upos-shop-btn:hover { border-color:var(--accent); background:var(--accent-dim); color:var(--accent) }

/* ── Responsive ──────────────────────────────────────────────────────────── */
@media(max-width:1000px) {
    .upos-co-body { display:flex; flex-direction:column; overflow-y:auto; padding-bottom:14px; }
    .upos-co-left { border-right:none; border-bottom:1px solid var(--border); overflow-y:visible; flex:none; }
    .upos-co-right { overflow-y:visible; flex:none; }
    .upos-bal-strip { position:sticky; top:0; z-index:10; box-shadow:0 4px 12px rgba(26,31,54,.08); }
    .upos-co-complete { position:sticky; bottom:0; z-index:10; }
}
@media(max-width:860px) {
    .upos-layout { grid-template-columns:minmax(0,1fr) }
    .upos-right { display:none !important }
    .upos-cart-fab { display:flex }
    .upos-cart-drawer-overlay { display:block }
}
@media(max-width:640px) {
    .upos-filter-strip { flex-wrap:nowrap; overflow-x:auto; padding-bottom:2px; -ms-overflow-style:none; scrollbar-width:none }
    .upos-filter-strip::-webkit-scrollbar { display:none }
    .upos-stock-grid { grid-template-columns:repeat(auto-fill,minmax(140px,1fr)) }
    .upos-co-card { max-width:100%; margin:0 }
    .upos-sm-card { max-width:100%; margin:0 }
    .upos-rc-card { max-width:100%; margin:0 }
    .upos-shop-grid { grid-template-columns:1fr }
}
@media(max-width:400px) {
    .upos-stock-grid { grid-template-columns:1fr 1fr; gap:8px }
    .upos-tile { padding:10px; gap:6px }
    .upos-tile-name { font-size:12px }
    .upos-tile-price { font-size:14px }
}
</style>

{{-- ── Page header ────────────────────────────────────────────────────────── --}}
<div class="upos-header">
    <div class="upos-header-left">
        <div class="upos-header-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        </div>
        <div>
            <h1 class="upos-header-title">Point of Sale</h1>
            <p class="upos-header-sub">
                {{ $shopName }}
                @if($warehouseName) · <span style="color:var(--accent)">+{{ $warehouseName }}</span>@endif
            </p>
        </div>
    </div>
    <div class="upos-header-right">
        @if($isOwner && $shopId)
            <button class="upos-shop-chip" wire:click="$set('showShopSelectionModal',true)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                {{ $shopName }}
            </button>
        @endif
        <a href="{{ route('shop.receipts') }}" style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border:1.5px solid var(--border);border-radius:20px;font-size:12px;font-weight:600;color:var(--text-dim);text-decoration:none;background:var(--surface);transition:all var(--tr)"
           onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'"
           onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-dim)'">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Receipt History
        </a>
        @if(!empty($cart))
            <span style="font-size:12px;color:var(--text-dim)">{{ count($cart) }} item{{ count($cart) !== 1 ? 's' : '' }} · <strong style="font-family:var(--mono)">{{ number_format($cartTotal) }} RWF</strong></span>
        @endif
    </div>
</div>

{{-- ── Main layout ─────────────────────────────────────────────────────────── --}}
<div class="upos-layout">

{{-- ── Left: Stock browser ────────────────────────────────────────────────── --}}
<div class="upos-left">

    {{-- Search card --}}
    <div class="upos-search-card">
        <div class="upos-search-row">
            <div class="upos-search-wrap">
                <svg class="upos-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input class="upos-search-input" type="text" wire:model.live="searchQuery" placeholder="Search products…" autocomplete="off">
            </div>
            <button class="upos-icon-btn {{ $showScannerPanel ? 'active' : '' }}"
                wire:click="{{ $showScannerPanel ? 'disablePhoneScanner' : 'enablePhoneScanner' }}"
                title="Phone scanner">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 7h.01M17 7h.01M7 17h.01M17 17h.01M12 12h.01"/></svg>
            </button>
        </div>
        <div class="upos-search-row">
            <input class="upos-barcode-input" type="text" wire:model.live="barcodeInput" placeholder="Scan barcode…" autocomplete="off">
        </div>

        {{-- Filter pills --}}
        <div class="upos-filter-strip">
            <button class="upos-filter-pill {{ $stockFilter === 'all' ? 'active' : '' }}" wire:click="setStockFilter('all')">
                All <span class="count">{{ count($shopStock) + count($warehouseStock) }}</span>
            </button>
            <button class="upos-filter-pill {{ $stockFilter === 'shop' ? 'active' : '' }}" wire:click="setStockFilter('shop')">
                <span class="upos-badge shop" style="margin-right:2px">●</span> Shop <span class="count">{{ count($shopStock) }}</span>
            </button>
            @if($warehouseId)
            <button class="upos-filter-pill {{ $stockFilter === 'warehouse' ? 'active' : '' }}" wire:click="setStockFilter('warehouse')">
                <span class="upos-badge warehouse" style="margin-right:2px">●</span> Warehouse <span class="count">{{ count($warehouseStock) }}</span>
            </button>
            @endif
        </div>
    </div>

    {{-- Phone scanner panel --}}
    @if($showScannerPanel && $scannerSession)
    <div style="background:var(--surface);border:none;box-shadow:var(--shadow-card);border-radius:var(--r);padding:14px" wire:poll.2000ms="checkForScans">
        <div class="upos-scanner-panel">
            <div class="upos-scanner-qr">
                @php $qrAvailable = class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class); @endphp
                @if($qrAvailable)
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(72)->generate(route('scanner.mobile') . '?session=' . $scannerSession->session_code) !!}
                @else
                <div style="width:72px;height:72px;background:var(--surface2);border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--text-faint);font-size:10px;text-align:center;padding:6px">QR unavailable</div>
                @endif
            </div>
            <div class="upos-scanner-info">
                <div style="font-size:11px;color:var(--text-dim);margin-bottom:4px;font-weight:600;text-transform:uppercase;letter-spacing:.4px">Phone Scanner</div>
                <div class="upos-scanner-code">{{ $scannerSession->session_code }}</div>
                @if($lastProcessedScan)
                    <div style="font-size:11px;color:var(--green);margin-top:4px">Last scan: {{ $lastProcessedScan }}</div>
                @endif
            </div>
            <button class="upos-scanner-close" wire:click="disablePhoneScanner">Close</button>
        </div>
    </div>
    @endif

    {{-- Held sales strip --}}
    @if(!empty($heldSales))
    <div class="upos-held-strip">
        <div class="upos-held-header">
            <span class="upos-held-title">Held Sales ({{ count($heldSales) }})</span>
            <button class="upos-held-toggle" wire:click="$toggle('showHeldPanel')">{{ $showHeldPanel ? 'Hide' : 'Show' }}</button>
        </div>
        @if($showHeldPanel)
        <div class="upos-held-list">
            @foreach($heldSales as $held)
            <div class="upos-held-item">
                <div>
                    <div class="upos-held-ref">{{ $held['reference'] }}</div>
                    <div class="upos-held-meta">{{ $held['item_count'] }} item{{ $held['item_count'] !== 1 ? 's' : '' }} · {{ $held['age'] }}</div>
                </div>
                <span class="upos-held-total">{{ number_format($held['cart_total']) }}</span>
                @if($held['needs_approval'] && !$held['is_approved'])
                    <span class="upos-held-approval-badge">Needs Approval</span>
                @elseif($held['is_approved'])
                    <span class="upos-held-approved-badge">✓ Approved</span>
                @endif
                <button class="upos-held-action upos-held-resume" wire:click="resumeHeldSale({{ $held['id'] }})">Resume</button>
                <button class="upos-held-action upos-held-discard" wire:click="discardHeldSale({{ $held['id'] }})">×</button>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    {{-- Poll only when there are pending unapproved held sales --}}
    @if(collect($heldSales)->contains('is_approved', false))
    <div wire:poll.5000ms="checkApprovals" style="display:none"></div>
    @endif

    {{-- Stock grid --}}
    @if(count($displayedStock) > 0)
    <div class="upos-stock-grid">
        @foreach($displayedStock as $product)
        @php
            $isShop = $product['source'] === 'shop';
            $stockCount = $isShop
                ? ($product['stock']['full_boxes'] ?? 0) . ' box' . (($product['stock']['full_boxes'] ?? 0) !== 1 ? 'es' : '')
                : ($product['stock']['total_boxes'] ?? 0) . ' box' . (($product['stock']['total_boxes'] ?? 0) !== 1 ? 'es' : '');
            $maxStock = $isShop ? max(1, $product['stock']['total_items'] ?? 1) : max(1, ($product['stock']['total_items'] ?? 1));
            $barWidth = $isShop
                ? min(100, round((($product['stock']['total_items'] ?? 0) / $maxStock) * 100))
                : min(100, round((($product['stock']['total_boxes'] ?? 0) / max(1, $product['stock']['total_boxes'] ?? 1)) * 100));
        @endphp
        <div class="upos-tile" wire:key="tile-{{ $product['source'] }}-{{ $product['id'] }}">
            <div class="upos-tile-top">
                <span class="upos-badge {{ $product['source'] }}">{{ $isShop ? 'Shop' : 'WH' }}</span>
                <span class="upos-stock-count">{{ $stockCount }}</span>
            </div>
            <div class="upos-tile-name">{{ $product['name'] }}</div>
            @if($product['category'])
                <div class="upos-tile-cat">{{ $product['category'] }}</div>
            @endif
            <div class="upos-tile-price">{{ number_format($product['box_price']) }} RWF</div>
            <div class="upos-tile-bar-wrap">
                <div class="upos-tile-bar" style="width:{{ $barWidth }}%;background:{{ $isShop ? 'var(--green)' : 'var(--accent)' }}"></div>
            </div>
            <button class="upos-tile-add" type="button" wire:click="selectProduct({{ $product['id'] }}, '{{ $product['source'] }}')">+ Add</button>
        </div>
        @endforeach
    </div>
    @else
    <div class="upos-empty-state">
        <svg class="upos-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
        <p style="font-size:14px;font-weight:600;color:var(--text-dim);margin:0 0 4px">No products found</p>
        <p style="font-size:12px;color:var(--text-dim);margin:0">
            @if($searchQuery) No results for "{{ $searchQuery }}" @else No stock available @endif
        </p>
    </div>
    @endif

</div>{{-- end .upos-left --}}

{{-- ── Right: Cart panel ───────────────────────────────────────────────────── --}}
<div class="upos-right">
    @include('livewire.shop.sales.partials.upos-cart-panel')
</div>

</div>{{-- end .upos-layout --}}

{{-- ── Mobile FAB ─────────────────────────────────────────────────────────── --}}
<div x-data="{ drawerOpen: false }" @keydown.escape.window="drawerOpen=false">
    <button class="upos-cart-fab" @click="drawerOpen=true">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        @if(count($cart) > 0)
        <span class="upos-fab-badge">{{ count($cart) }}</span>
        @endif
    </button>

    {{-- Drawer overlay --}}
    <div class="upos-cart-drawer-overlay" x-show="drawerOpen" @click="drawerOpen=false" style="display:none"></div>

    {{-- Drawer --}}
    <div class="upos-cart-drawer" :class="drawerOpen ? 'open' : ''">
        <div class="upos-drawer-handle"></div>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:4px 16px 10px;flex-shrink:0">
            <span style="font-size:15px;font-weight:800;color:var(--text)">Cart</span>
            <button style="background:none;border:none;cursor:pointer;color:var(--text-dim);padding:4px" @click="drawerOpen=false">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div style="flex:1;overflow-y:auto;min-height:0">
            @include('livewire.shop.sales.partials.upos-cart-panel')
        </div>
    </div>
</div>

{{-- ── Shop Selection Modal ────────────────────────────────────────────────── --}}
@if($showShopSelectionModal)
<div class="upos-overlay" style="z-index:800">
    <div class="upos-modal-card upos-shop-modal-card">
        <div class="upos-shop-modal-head">
            <h2 class="upos-shop-modal-title">Select a Shop</h2>
            <p class="upos-shop-modal-sub">Choose the shop you're operating from</p>
        </div>
        <div style="padding:12px 24px 8px">
            <select class="upos-select" wire:model="shopId">
                <option value="">— Select shop —</option>
                @foreach($availableShops as $shop)
                    <option value="{{ $shop['id'] }}">{{ $shop['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div style="padding:12px 24px 20px">
            <button class="upos-btn-primary" style="width:100%" wire:click="selectShopFromModal" @if(!$shopId) disabled @endif>
                Confirm Shop
            </button>
        </div>
    </div>
</div>
@endif

{{-- ── Staging Modal ───────────────────────────────────────────────────────── --}}
@if($showAddModal && $stagingProduct)
<div class="upos-overlay">
    <div class="upos-modal-card upos-sm-card" @click.stop>
        {{-- Head --}}
        <div class="upos-sm-head">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                <span class="upos-badge {{ $stagingProduct['source'] }}">{{ $stagingProduct['source'] === 'shop' ? 'Shop Stock' : 'Warehouse Stock' }}</span>
            </div>
            <h3 class="upos-sm-title">{{ $stagingProduct['name'] }}</h3>
            <p class="upos-sm-sub">{{ $stagingProduct['sku'] }}@if($stagingProduct['category']) · {{ $stagingProduct['category'] }}@endif</p>
        </div>

        {{-- Body --}}
        <div class="upos-sm-body" x-data="{
            qty: @entangle('stagingQty'),
            price: @entangle('stagingPrice'),
            origPrice: {{ $stagingMode === 'box' ? $stagingProduct['box_price'] : $stagingProduct['selling_price'] }},
            maxQty: {{ ($stagingProduct['source'] === 'warehouse') ? ($stagingProduct['box_count'] ?? ($stagingStock['total_boxes'] ?? 9999)) : ($stagingMode === 'box' ? ($stagingStock['full_boxes'] ?? 9999) : ($stagingStock['total_items'] ?? 9999)) }},
            get isModified() { return parseInt(this.price) !== parseInt(this.origPrice); },
            get isOverStock() { return parseInt(this.qty) > this.maxQty; }
        }">

            {{-- Stock info --}}
            <div class="upos-sm-info">
                @if($stagingProduct['source'] === 'shop')
                    <div class="upos-sm-info-row"><span style="color:var(--text-dim)">Available items</span><span style="font-weight:700;font-family:var(--mono)">{{ number_format($stagingStock['total_items'] ?? 0) }}</span></div>
                    <div class="upos-sm-info-row"><span style="color:var(--text-dim)">Full boxes</span><span style="font-weight:700;font-family:var(--mono)">{{ $stagingProduct['has_full_box'] ? ($stagingStock['full_boxes'] ?? '—') : 'None' }}</span></div>
                    <div class="upos-sm-info-row"><span style="color:var(--text-dim)">Items/box</span><span style="font-weight:700;font-family:var(--mono)">{{ $stagingProduct['items_per_box'] }}</span></div>
                @else
                    <div class="upos-sm-info-row"><span style="color:var(--text-dim)">Available boxes</span><span style="font-weight:700;font-family:var(--mono)">{{ $stagingProduct['box_count'] ?? ($stagingStock['total_boxes'] ?? 0) }}</span></div>
                    <div class="upos-sm-info-row"><span style="color:var(--text-dim)">Items/box</span><span style="font-weight:700;font-family:var(--mono)">{{ $stagingProduct['items_per_box'] }}</span></div>
                @endif
            </div>

            {{-- Mode toggle (shop only) --}}
            @if($stagingProduct['source'] === 'shop' && ($stagingProduct['individual_sale_allowed'] ?? false))
            <div class="upos-field">
                <label class="upos-label">Sell as</label>
                <div class="upos-mode-toggle">
                    <button type="button" class="upos-mode-btn {{ $stagingMode === 'box' ? 'active' : '' }}" wire:click="$set('stagingMode','box')">Full Box</button>
                    <button type="button" class="upos-mode-btn {{ $stagingMode === 'item' ? 'active' : '' }}" wire:click="$set('stagingMode','item')">Individual Items</button>
                </div>
            </div>
            @elseif($stagingProduct['source'] === 'warehouse')
            <div style="font-size:12px;color:var(--text-dim);padding:4px 0">Warehouse sales are full boxes only.</div>
            @endif

            {{-- Quantity --}}
            <div class="upos-field">
                <label class="upos-label">{{ $stagingMode === 'box' ? 'Number of Boxes' : 'Number of Items' }}</label>
                <div class="upos-stepper">
                    <button type="button" class="upos-stepper-btn" @click="if(qty > 1) qty--">−</button>
                    <input class="upos-stepper-val" type="number" x-model.number="qty" min="1" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                    <button type="button" class="upos-stepper-btn" @click="qty++">+</button>
                </div>
                <div x-show="isOverStock" style="display:none; color:var(--amber); font-size:12px; margin-top:4px; font-weight:600; padding:6px 10px; background:var(--amber-dim); border-radius:var(--rsm);">
                    Warning: Only <span x-text="maxQty"></span> {{ $stagingMode === 'box' ? 'boxes' : 'items' }} available in stock.
                </div>
            </div>

            {{-- Price --}}
            <div class="upos-field">
                <label class="upos-label">{{ $stagingMode === 'box' ? 'Box Price (RWF)' : 'Item Price (RWF)' }}</label>
                @if($settingAllowPriceOverride)
                <div class="upos-price-row">
                    <input class="upos-input" type="number" x-model.number="price" min="0">
                    <span class="upos-price-modified-badge" x-show="isModified" style="display:none">Modified</span>
                </div>
                <div class="upos-field" style="margin-top:6px" x-show="isModified" style="display:none">
                    <label class="upos-label">Reason for price change</label>
                    <input class="upos-input" type="text" wire:model="stagingPriceReason" placeholder="Required">
                </div>
                @else
                <div style="font-size:14px;font-weight:700;font-family:var(--mono);color:var(--text);padding:9px 0" x-text="new Intl.NumberFormat().format(price) + ' RWF'"></div>
                @endif
            </div>

            {{-- Line total --}}
            <div class="upos-sm-total">
                <span class="upos-sm-total-label">Line Total</span>
                <span class="upos-sm-total-val" x-text="new Intl.NumberFormat().format(price * qty) + ' RWF'"></span>
            </div>

        </div>{{-- end .upos-sm-body --}}

        <div class="upos-sm-foot">
            <button type="button" class="upos-btn-ghost" wire:click="closeAddModal">Cancel</button>
            <button type="button" class="upos-btn-primary" wire:click="confirmAddToCart">
                {{ $stagingCartIndex !== null ? 'Update Item' : 'Add to Cart' }}
            </button>
        </div>
    </div>
</div>
@endif

{{-- ── Checkout Modal ──────────────────────────────────────────────────────── --}}
@if($showCheckoutModal)
<div class="upos-overlay" x-data="{
    card: '', momo: '', bank: '', credit: '',
    get total() { return {{ $cartTotal }} },
    get allocated() { return parseInt(this.card||0)+parseInt(this.momo||0)+parseInt(this.bank||0)+parseInt(this.credit||0) },
    get cash() { return Math.max(0, this.total - this.allocated) },
    get remain() { return this.total - this.allocated - this.cash },
    get pct() { return Math.min(100, Math.round(((this.allocated + this.cash) / Math.max(1,this.total)) * 100)) },
    complete() {
        $wire.set('payAmt_card', parseInt(this.card)||0)
        $wire.set('payAmt_mobile_money', parseInt(this.momo)||0)
        $wire.set('payAmt_bank_transfer', parseInt(this.bank)||0)
        $wire.set('payAmt_credit', parseInt(this.credit)||0)
        $wire.set('payAmt_cash', this.cash)
        $wire.completeSale()
    }
}">
    <div class="upos-modal-card upos-co-card" @click.stop>
        {{-- Head --}}
        <div class="upos-co-head">
            <h3 class="upos-co-title">Checkout</h3>
            <button class="upos-co-close" wire:click="closeCheckoutModal">×</button>
        </div>

        {{-- Body --}}
        <div class="upos-co-body">

            {{-- Left column --}}
            <div class="upos-co-left">

                {{-- Order summary --}}
                <div>
                    <div class="upos-co-section-title">Order Summary</div>
                    <div class="upos-order-summary">
                        @foreach($cart as $item)
                        <div class="upos-order-row">
                            <div style="flex:1;min-width:0">
                                <div class="upos-order-item-name">
                                    {{ $item['product_name'] }}
                                    <span class="upos-badge {{ $item['source'] ?? 'shop' }}" style="margin-left:4px;vertical-align:middle">{{ ($item['source'] ?? 'shop') === 'shop' ? 'S' : 'WH' }}</span>
                                </div>
                                <div style="font-size:11px;color:var(--text-dim)">{{ $item['qty'] }} × {{ number_format($item['price']) }} ({{ $item['mode'] === 'box' ? 'box' : 'item' }})</div>
                            </div>
                            <div class="upos-order-item-total">{{ number_format($item['line_total']) }}</div>
                        </div>
                        @endforeach
                        <div class="upos-order-total-row">
                            <span class="upos-order-total-label">Total</span>
                            <span class="upos-order-total-val">{{ number_format($cartTotal) }} RWF</span>
                        </div>
                    </div>
                </div>

                {{-- Customer --}}
                <div>
                    <div class="upos-co-section-title">Customer (Optional)</div>
                    @if($selectedCustomerId)
                        <div class="upos-customer-selected">
                            <div>
                                <div style="font-size:13px;font-weight:700;color:var(--text)">{{ $selectedCustomerName }}</div>
                                <div style="font-size:11px;color:var(--text-dim)">{{ $selectedCustomerPhone }}</div>
                            </div>
                            <button class="upos-btn-sm" wire:click="clearCustomer">Change</button>
                        </div>
                    @elseif($showNewCustomerForm)
                        <div class="upos-new-customer-form">
                            <div class="upos-field">
                                <label class="upos-label">Full Name</label>
                                <input class="upos-input" type="text" wire:model="newCustomerName" placeholder="Customer name">
                            </div>
                            <div class="upos-field">
                                <label class="upos-label">Phone</label>
                                <input class="upos-input" type="text" wire:model="newCustomerPhone" placeholder="07X XXX XXXX">
                            </div>
                            <div style="display:flex;gap:8px">
                                <button class="upos-btn-ghost" style="flex:1;padding:8px" wire:click="cancelNewCustomer">Cancel</button>
                                <button class="upos-btn-primary" style="flex:1;padding:8px" wire:click="saveNewCustomer">Save</button>
                            </div>
                        </div>
                    @else
                        <div class="upos-customer-search-wrap">
                            <div class="upos-customer-input-row">
                                <input class="upos-input" type="text" wire:model.live="customerSearch" wire:focus="openCustomerSearch" placeholder="Search by name or phone…">
                                <button class="upos-btn-sm" wire:click="showCreateCustomerForm">+ New</button>
                            </div>
                            @if($showCustomerSearch && count($customerResults) > 0)
                            <div class="upos-customer-dropdown">
                                @foreach($customerResults as $cust)
                                <div class="upos-customer-row" wire:click="selectCustomer({{ $cust['id'] }})">
                                    <span class="upos-customer-row-name">{{ $cust['name'] }}</span>
                                    <span class="upos-customer-row-phone">{{ $cust['phone'] }} @if($cust['outstanding_balance'] > 0) · <span style="color:var(--amber)">{{ number_format($cust['outstanding_balance']) }} RWF credit</span>@endif</span>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    @endif

                    @if($creditWarningVisible)
                    <div class="upos-credit-warning" style="margin-top:8px">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        {{ $creditWarningMessage }}
                    </div>
                    @endif
                </div>

                {{-- Notes --}}
                <div class="upos-field">
                    <label class="upos-label">Sale Notes (optional)</label>
                    <input class="upos-input" type="text" wire:model="notes" placeholder="e.g. delivery instructions…" style="padding:7px 10px;font-size:13px">
                </div>

                {{-- Fulfillment (warehouse items only) --}}
                @if($hasWarehouseItems)
                <div class="upos-fulfillment-block">
                    <div class="upos-fulfillment-title">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        Warehouse Dispatch
                    </div>
                    <div class="upos-fulfillment-toggle">
                        <button type="button" class="upos-fulfillment-btn {{ $fulfillmentMethod === 'transporter' ? 'active' : '' }}" wire:click="$set('fulfillmentMethod','transporter')">Transporter</button>
                        <button type="button" class="upos-fulfillment-btn {{ $fulfillmentMethod === 'pickup' ? 'active' : '' }}" wire:click="$set('fulfillmentMethod','pickup')">Customer Pickup</button>
                    </div>
                    @if($fulfillmentMethod === 'transporter')
                    <div class="upos-field" style="margin-bottom:8px">
                        <label class="upos-label">Select Transporter</label>
                        <select class="upos-select" wire:model="fulfillmentTransporterId">
                            <option value="">— Choose transporter —</option>
                            @foreach($transporters as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}@if($t->company) ({{ $t->company }})@endif</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="upos-field">
                        <label class="upos-label">Dispatch Notes</label>
                        <input class="upos-input" type="text" wire:model="fulfillmentNotes" placeholder="Optional notes for warehouse team…">
                    </div>
                    <div class="upos-fulfillment-note">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        Warehouse items will be dispatched to the customer after payment is collected.
                    </div>
                </div>
                @endif

            </div>{{-- end .upos-co-left --}}

            {{-- Right column --}}
            <div class="upos-co-right">

                {{-- Balance strip --}}
                <div class="upos-bal-strip">
                    <div class="upos-bal-row">
                        <span class="upos-bal-label">Total</span>
                        <span class="upos-bal-val" style="font-family:var(--mono)">{{ number_format($cartTotal) }} RWF</span>
                    </div>
                    <div class="upos-bal-progress">
                        <div class="upos-bal-progress-fill" :style="`width:${pct}%`"></div>
                    </div>
                    <div class="upos-bal-remain" :class="remain > 0 ? 'red' : 'green'">
                        <span x-text="remain > 0 ? number_format_js(remain) + ' RWF remaining' : remain < 0 ? number_format_js(Math.abs(remain)) + ' RWF overpaid' : 'Fully paid ✓'"></span>
                    </div>
                </div>

                {{-- Payment channels --}}
                <div class="upos-co-section-title">Payment</div>

                {{-- MoMo --}}
                <div class="upos-pay-row">
                    <div class="upos-pay-label-row">
                        <label class="upos-pay-label">Mobile Money</label>
                    </div>
                    <input class="upos-pay-input" type="number" x-model="momo" min="0" placeholder="0">
                </div>

                @if($settingAllowCardPayment)
                {{-- Card --}}
                <div class="upos-pay-row">
                    <div class="upos-pay-label-row">
                        <label class="upos-pay-label">Card</label>
                    </div>
                    <input class="upos-pay-input" type="number" x-model="card" min="0" placeholder="0">
                    <div class="upos-pay-ref">
                        <input class="upos-pay-input" type="text" wire:model="payRef_card" placeholder="Card reference" style="font-size:11px">
                    </div>
                </div>
                @endif

                @if($settingAllowBankTransfer)
                {{-- Bank --}}
                <div class="upos-pay-row">
                    <div class="upos-pay-label-row">
                        <label class="upos-pay-label">Bank Transfer</label>
                    </div>
                    <input class="upos-pay-input" type="number" x-model="bank" min="0" placeholder="0">
                    <div class="upos-pay-ref">
                        <input class="upos-pay-input" type="text" wire:model="payRef_bank_transfer" placeholder="Transfer reference" style="font-size:11px">
                    </div>
                </div>
                @endif

                @if($settingAllowCreditSales)
                {{-- Credit --}}
                <div class="upos-pay-row">
                    <div class="upos-pay-label-row">
                        <label class="upos-pay-label">Credit</label>
                        @if($settingCreditRequiresCustomer && !$selectedCustomerId)
                        <span style="font-size:10px;color:var(--amber)">Select customer first</span>
                        @endif
                    </div>
                    <input class="upos-pay-input" type="number" x-model="credit" min="0" placeholder="0"
                        @blur="$wire.set('payAmt_credit', parseInt(credit)||0)"
                        @if($settingCreditRequiresCustomer && !$selectedCustomerId) disabled @endif>
                </div>
                @endif

                {{-- Cash (auto) --}}
                <div style="background:var(--bg);border-radius:var(--rsm);padding:8px 12px">
                    <div class="upos-pay-label-row" style="margin-bottom:4px">
                        <label class="upos-pay-label">Cash</label>
                        <span class="upos-pay-auto-badge">auto</span>
                    </div>
                    <div class="upos-cash-display" x-text="number_format_js(cash) + ' RWF'"></div>
                </div>

                {{-- Complete button --}}
                <button class="upos-co-complete" @click="complete()" :disabled="remain !== 0 || cash < 0">
                    Complete Sale
                </button>

                @php $hasPriceOverride = collect($cart)->contains(fn($i) => !empty($i['price_modified'])); @endphp
                @if($hasPriceOverride)
                <button class="upos-cart-action-btn" style="width:100%;margin-top:2px;background:var(--amber-dim);border-color:var(--amber);color:var(--amber)" wire:click="holdSale">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;margin-right:5px;vertical-align:middle"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Submit for Owner Approval
                </button>
                @else
                <button class="upos-btn-ghost" style="width:100%;font-size:12px;padding:8px;margin-top:2px" wire:click="closeCheckoutModal">
                    Cancel — back to cart
                </button>
                @endif

            </div>{{-- end .upos-co-right --}}
        </div>{{-- end .upos-co-body --}}
    </div>
</div>
@endif

{{-- ── Receipt Modal ───────────────────────────────────────────────────────── --}}
@if($showReceiptModal && $completedSale)
<div class="upos-overlay">
    <div class="upos-modal-card upos-rc-card" @click.stop>
        {{-- Green banner --}}
        <div class="upos-rc-banner">
            <div class="upos-rc-banner-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
                <h3 class="upos-rc-banner-title">Sale Complete</h3>
                <p class="upos-rc-banner-sub">{{ $completedSale->sale_number }} · {{ $completedSale->sale_date->format('d M Y H:i') }}</p>
            </div>
        </div>

        <div class="upos-rc-body">
            {{-- Customer --}}
            @if($completedSale->customer_name)
            <div style="font-size:13px;color:var(--text-sub)">
                <strong>Customer:</strong> {{ $completedSale->customer_name }}
                @if($completedSale->customer_phone) · {{ $completedSale->customer_phone }}@endif
            </div>
            @endif

            {{-- Items --}}
            <div>
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-dim);margin-bottom:6px">Items</div>
                <div class="upos-rc-items">
                    @php
                        $groupedItems = collect($completedSale->items)
                            ->groupBy(fn($i) => $i->product_id ?? 'unknown')
                            ->map(fn($g) => (object)[
                                'name'       => $g->first()->product?->name ?? '—',
                                'box_count'  => $g->count(),
                                'line_total' => $g->sum('line_total'),
                                'has_wh'     => $g->contains(fn($i) => $i->box && $i->box->location_type?->value === 'warehouse'),
                            ]);
                    @endphp
                    @foreach($groupedItems as $grp)
                    <div class="upos-rc-item">
                        <div class="upos-rc-item-name">
                            {{ $grp->name }}
                            @if($grp->box_count > 1)
                                <span style="color:var(--text-dim);font-size:11px;font-weight:600;margin-left:3px">×{{ $grp->box_count }}</span>
                            @endif
                            @if($grp->has_wh)
                                <span class="upos-badge warehouse" style="margin-left:4px;vertical-align:middle">WH</span>
                            @endif
                        </div>
                        <span class="upos-rc-item-total">{{ number_format($grp->line_total) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Warehouse dispatch note --}}
            @if($completedSale->fulfillment_type === 'warehouse_direct' && $completedSale->fulfillment_status === 'pending')
            <div class="upos-rc-wh-note">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                <div>
                    <strong>Warehouse items pending dispatch.</strong>
                    The warehouse team has been notified and will dispatch items via
                    {{ $completedSale->fulfillment_method === 'transporter' && $completedSale->fulfillmentTransporter
                        ? $completedSale->fulfillmentTransporter->name
                        : ($completedSale->fulfillment_method === 'pickup' ? 'customer pickup' : $completedSale->fulfillment_method) }}.
                </div>
            </div>
            @endif

            {{-- Payments --}}
            <div>
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-dim);margin-bottom:6px">Payments</div>
                <div class="upos-rc-payments">
                    @foreach($completedSale->payments as $payment)
                    <div class="upos-rc-pay-row">
                        <span style="color:var(--text-sub)">{{ ucfirst(str_replace('_',' ',$payment->payment_method->value ?? $payment->payment_method)) }}</span>
                        <span style="font-weight:700;font-family:var(--mono)">{{ number_format($payment->amount) }}</span>
                    </div>
                    @endforeach
                    <div class="upos-rc-total-row">
                        <span class="upos-rc-total-label">Total</span>
                        <span class="upos-rc-total-val">{{ number_format($completedSale->total) }} RWF</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="upos-rc-foot">
            <button class="upos-btn-ghost" wire:click="closeReceipt">New Sale</button>
            <a href="{{ route('shop.receipts') }}" target="_blank"
               style="display:inline-flex;align-items:center;gap:5px;padding:10px 14px;border-radius:var(--rsm);border:1.5px solid var(--border);background:transparent;color:var(--text-dim);font-size:13px;font-weight:600;text-decoration:none;transition:all var(--tr)"
               onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'"
               onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-dim)'">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                History
            </a>
            <button class="upos-btn-primary" wire:click="printReceipt">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;margin-right:5px;vertical-align:middle"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print
            </button>
        </div>
    </div>
</div>
@endif

<script>
function number_format_js(n) {
    return Math.abs(parseInt(n||0)).toLocaleString();
}
document.addEventListener('livewire:initialized', function () {
    Livewire.on('open-print-window', function (params) {
        var url = Array.isArray(params) ? params[0]?.url : params?.url;
        if (url) window.open(url, '_blank');
    });
});
</script>
</div>
@endif
</div>
