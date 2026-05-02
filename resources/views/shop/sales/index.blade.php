<x-app-layout>

@push('styles')
<style>
/* ═══════════════════════════════════════════════════
   ROOT
═══════════════════════════════════════════════════ */
.sli-root { display:flex; flex-direction:column; gap:22px; padding-bottom:48px; }

/* ═══════════════════════════════════════════════════
   PAGE HEADER
═══════════════════════════════════════════════════ */
.sli-page-head {
    display:flex; align-items:flex-start; justify-content:space-between;
    gap:16px; flex-wrap:wrap;
}
.sli-page-title { font-size:22px; font-weight:800; color:var(--text); letter-spacing:-.4px; margin:0; line-height:1.2; }
.sli-page-sub   { font-size:13px; color:var(--text-dim); margin:4px 0 0; }

.sli-page-actions { display:flex; align-items:center; gap:8px; flex-shrink:0; }

.sli-btn-ghost {
    display:inline-flex; align-items:center; gap:7px;
    padding:9px 16px; border:1.5px solid var(--border); border-radius:10px;
    font-size:13px; font-weight:600; color:var(--text-dim); text-decoration:none;
    background:var(--surface); transition:all .15s; white-space:nowrap;
}
.sli-btn-ghost:hover { color:var(--text); border-color:var(--text-dim); background:var(--surface2); }

.sli-btn-primary {
    display:inline-flex; align-items:center; gap:7px;
    padding:9px 18px; border:none; border-radius:10px;
    font-size:13px; font-weight:600; color:#fff; text-decoration:none;
    background:linear-gradient(135deg,#3b6bd4,#2a55b8);
    box-shadow:0 2px 8px rgba(59,107,212,.25); transition:all .15s; white-space:nowrap;
}
.sli-btn-primary:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(59,107,212,.35); }

/* ═══════════════════════════════════════════════════
   KPI CARDS
═══════════════════════════════════════════════════ */

/* Desktop: 5 equal columns */
.sli-kpi-row {
    display:grid;
    grid-template-columns:repeat(5,1fr);
    gap:14px;
}

/* Tablet landscape → 3 + 2 */
@media(max-width:1200px) {
    .sli-kpi-row { grid-template-columns:repeat(3,1fr); }
}

/* Tablet portrait → horizontal scroll strip */
@media(max-width:768px) {
    .sli-kpi-row {
        display:flex;
        overflow-x:auto;
        gap:12px;
        padding-bottom:6px;          /* room for scroll hint shadow */
        -webkit-overflow-scrolling:touch;
        scrollbar-width:none;
        /* Fade-out right edge hint */
        -webkit-mask-image:linear-gradient(to right,black 92%,transparent 100%);
        mask-image:linear-gradient(to right,black 92%,transparent 100%);
    }
    .sli-kpi-row::-webkit-scrollbar { display:none; }
    .sli-kpi { min-width:180px; flex-shrink:0; }
}

/* Very small phones → 2 columns, no scroll */
@media(max-width:440px) {
    .sli-kpi-row {
        display:grid;
        grid-template-columns:repeat(2,1fr);
        overflow:visible;
        -webkit-mask-image:none; mask-image:none;
    }
    .sli-kpi { min-width:0; }
}

.sli-kpi {
    background:var(--surface); border:1px solid var(--border); border-radius:14px;
    padding:18px 20px 0; overflow:hidden;
    display:flex; flex-direction:column; gap:10px;
    box-shadow:0 1px 3px rgba(0,0,0,.03); transition:box-shadow .15s;
}
.sli-kpi:hover { box-shadow:0 4px 16px rgba(0,0,0,.07); }

.sli-kpi-head {
    display:flex; align-items:flex-start; justify-content:space-between; gap:8px;
}
.sli-kpi-icon {
    width:36px; height:36px; border-radius:10px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
}
.sli-kpi-period {
    font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.05em;
    color:var(--text-dim); background:var(--surface2); border:1px solid var(--border);
    border-radius:20px; padding:2px 7px; white-space:nowrap; line-height:1.6;
    /* Hide period badge when cards are very narrow (≤1200 tablet grid) */
}
@media(max-width:1200px) and (min-width:769px) {
    .sli-kpi-period { display:none; }
}

.sli-kpi-body { display:flex; flex-direction:column; gap:3px; }
.sli-kpi-label {
    font-size:11px; font-weight:600; text-transform:uppercase;
    letter-spacing:.05em; color:var(--text-dim);
}
.sli-kpi-value {
    font-size:22px; font-weight:800; color:var(--text); line-height:1.15;
}
/* Scale value down on narrower grid cells */
@media(max-width:1200px) { .sli-kpi-value { font-size:19px; } }
@media(max-width:440px)  { .sli-kpi-value { font-size:17px; } }

.sli-kpi-value--warn { color:#f59e0b; }
.sli-kpi-unit  { font-size:11px; font-weight:600; color:var(--text-dim); margin-left:3px; }

.sli-kpi-bar  { height:3px; margin-top:auto; border-radius:0 0 14px 14px; background:var(--border); }
.sli-kpi-bar-fill { height:100%; border-radius:3px; transition:width .4s ease; }

/* ═══════════════════════════════════════════════════
   FILTER PANEL
═══════════════════════════════════════════════════ */
.sli-filter-panel {
    background:var(--surface); border:1px solid var(--border); border-radius:14px;
    padding:16px 20px; display:flex; flex-direction:column; gap:0;
    box-shadow:0 1px 3px rgba(0,0,0,.03);
}

/* ── Search row (always visible) ── */
.sli-search-row  { display:flex; align-items:center; gap:10px; }
.sli-search-wrap { position:relative; flex:1; min-width:0; }
.sli-search-icon {
    position:absolute; left:13px; top:50%; transform:translateY(-50%);
    width:16px; height:16px; color:var(--text-dim); pointer-events:none;
}
.sli-search-input {
    width:100%; padding:10px 38px 10px 38px;
    border:1.5px solid var(--border); border-radius:10px;
    background:var(--surface); color:var(--text); font-size:13px;
    outline:none; transition:border-color .15s; box-sizing:border-box;
}
.sli-search-input::placeholder { color:var(--text-dim); }
.sli-search-input:focus { border-color:#3b6bd4; }
.sli-search-clear {
    position:absolute; right:11px; top:50%; transform:translateY(-50%);
    width:20px; height:20px; border:none; background:none; cursor:pointer;
    color:var(--text-dim); display:flex; align-items:center; justify-content:center;
    border-radius:4px; transition:color .12s;
}
.sli-search-clear svg { width:13px; height:13px; }
.sli-search-clear:hover { color:var(--text); }

.sli-result-count {
    font-size:12px; font-weight:600; color:var(--text-dim);
    white-space:nowrap; flex-shrink:0;
}
.sli-loading { opacity:.5; }

/* ── Mobile bar: active chips + toggle button ── */
.sli-mobile-bar {
    display:none; /* hidden on desktop */
    align-items:center; justify-content:space-between; gap:8px;
    margin-top:10px; flex-wrap:wrap;
}
.sli-active-chips { display:flex; align-items:center; gap:5px; flex-wrap:wrap; flex:1; min-width:0; }
.sli-ac {
    display:inline-flex; align-items:center; gap:4px;
    padding:4px 10px; border-radius:20px;
    font-size:11px; font-weight:700; white-space:nowrap;
}
.sli-ac--blue { background:rgba(59,107,212,.12); color:#2a55b8; }
.sli-ac--dark { background:rgba(0,0,0,.07); color:var(--text); }
.sli-ac--grey { background:var(--surface2); color:var(--text-dim); border:1px solid var(--border); }

.sli-filter-toggle {
    display:inline-flex; align-items:center; gap:6px;
    padding:6px 13px; border:1.5px solid var(--border); border-radius:9px;
    background:var(--surface); font-size:12px; font-weight:600; color:var(--text-dim);
    cursor:pointer; transition:all .14s; white-space:nowrap; flex-shrink:0;
}
.sli-filter-toggle:hover, .sli-filter-toggle.is-open { border-color:#3b6bd4; color:#3b6bd4; }
.sli-chevron { transition:transform .2s; }
.sli-chevron--up { transform:rotate(180deg); }

/* ── Filter rows block ── */
/* Desktop: always visible */
.sli-filter-rows { display:block; }

/* Mobile: hidden unless .is-open */
@media(max-width:660px) {
    .sli-mobile-bar { display:flex; }
    .sli-result-count { display:none; } /* replaced by chips on mobile */

    .sli-filter-rows {
        display:none;
        overflow:hidden;
        transition:opacity .2s;
    }
    .sli-filter-rows.is-open { display:block; animation:sli-slide-down .18s ease; }

    @keyframes sli-slide-down {
        from { opacity:0; transform:translateY(-4px); }
        to   { opacity:1; transform:translateY(0); }
    }
}

/* ── Divider ── */
.sli-filter-divider { height:1px; background:var(--border); margin:14px 0; }

/* ── Filter rows layout ── */
.sli-filter-row { display:flex; align-items:flex-start; gap:12px; }
.sli-filter-row + .sli-filter-row { margin-top:10px; }

.sli-filter-label {
    display:flex; align-items:center; gap:5px; flex-shrink:0;
    font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em;
    color:var(--text-dim); white-space:nowrap; width:68px;
    /* vertically center with the pill container's own padding */
    margin-top:1px;
}

/* ── Shared segmented-control container ── */
.sli-pills--scroll,
.sli-pills--wrap {
    display:flex; align-items:center; gap:2px;
    background:var(--surface2,#f5f4f1); border:1px solid var(--border);
    border-radius:9px; padding:3px;
}

/* Scroll variant: overflows horizontally, fade-out edge hint */
.sli-pills--scroll {
    flex:1; min-width:0; overflow-x:auto;
    -webkit-overflow-scrolling:touch; scrollbar-width:none;
    -webkit-mask-image:linear-gradient(to right,black 92%,transparent 100%);
    mask-image:linear-gradient(to right,black 92%,transparent 100%);
}
.sli-pills--scroll::-webkit-scrollbar { display:none; }

/* Wrap variant: wraps on desktop, scrolls on small screens */
.sli-pills--wrap { flex-wrap:wrap; }
@media(max-width:660px){
    .sli-pills--wrap {
        flex-wrap:nowrap; overflow-x:auto;
        -webkit-overflow-scrolling:touch; scrollbar-width:none;
        -webkit-mask-image:linear-gradient(to right,black 92%,transparent 100%);
        mask-image:linear-gradient(to right,black 92%,transparent 100%);
    }
    .sli-pills--wrap::-webkit-scrollbar { display:none; }
}

/* ── Individual pill — shared base ── */
.sli-pill--date,
.sli-pill--pay {
    flex-shrink:0; border:none; border-radius:6px;
    background:transparent; cursor:pointer; transition:all .13s;
    white-space:nowrap; line-height:1.4;
}
.sli-pill--date { padding:5px 14px; font-size:13px; font-weight:500; color:var(--text-dim); }
.sli-pill--pay  { padding:4px 12px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:var(--text-dim); }

.sli-pill--date:hover,
.sli-pill--pay:hover  { color:var(--text); background:rgba(0,0,0,.04); }

/* Active: solid filled block */
.sli-pill--date.active {
    background:#3b6bd4; color:#fff; font-weight:700;
    box-shadow:0 1px 5px rgba(59,107,212,.35);
}
.sli-pill--pay.active {
    background:var(--pill-active,#1f1f1f); color:#fff;
    box-shadow:0 1px 4px rgba(0,0,0,.2);
}

/* ═══════════════════════════════════════════════════
   TABLE CARD
═══════════════════════════════════════════════════ */
.sli-table-card {
    background:var(--surface); border:1px solid var(--border); border-radius:14px;
    overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.03);
}
.sli-table-wrap { overflow-x:auto; }
.sli-table { width:100%; border-collapse:collapse; min-width:700px; }

.sli-th {
    padding:12px 16px; font-size:11px; font-weight:700; text-transform:uppercase;
    letter-spacing:.07em; color:var(--text-dim); background:var(--surface);
    border-bottom:1.5px solid var(--border); text-align:left; white-space:nowrap;
}
.sli-th--sortable { cursor:pointer; user-select:none; }
.sli-th--sortable:hover { color:var(--text); }
.sli-th--center { text-align:center; }
.sli-th--right  { text-align:right; }

/* Sort arrows via ::after */
.sli-sort { display:inline-block; width:14px; text-align:center; color:var(--text-dim); font-size:11px; }
.sli-sort.asc::after  { content:'↑'; color:#3b6bd4; }
.sli-sort.desc::after { content:'↓'; color:#3b6bd4; }

/* Rows */
.sli-row { border-bottom:0.5px solid var(--border); transition:background .1s; }
.sli-row:last-child { border-bottom:none; }
.sli-row:hover { background:rgba(59,107,212,.025); }
.sli-row--voided { opacity:.5; }
.sli-row--open { background:rgba(59,107,212,.04); }
.sli-row--open:hover { background:rgba(59,107,212,.06); }

.sli-td { padding:14px 16px; font-size:13px; color:var(--text); vertical-align:middle; }
.sli-td--center { text-align:center; }
.sli-td--right  { text-align:right; }
.sli-td--mono   { font-family:var(--mono, 'SF Mono', monospace); }
.sli-td--num    { font-size:12px; color:var(--text-dim); font-weight:600; }
.sli-td--amount { font-weight:800; font-size:14px; }

.sli-date-d { display:block; font-weight:500; }
.sli-date-t { display:block; font-size:11px; color:var(--text-dim); margin-top:2px; }

.sli-cust-name  { display:block; font-weight:500; }
.sli-cust-phone { display:block; font-size:11px; color:var(--text-dim); margin-top:2px; }
.sli-walkin     { color:var(--text-dim); font-style:italic; font-size:12px; }

.sli-qty-chip {
    display:inline-flex; align-items:center; justify-content:center;
    min-width:26px; height:26px; padding:0 6px; border-radius:8px;
    background:var(--surface2); border:1px solid var(--border);
    font-size:12px; font-weight:700; color:var(--text);
}

.sli-pm-chip {
    display:inline-block; padding:4px 10px; border-radius:20px;
    font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em;
}

.sli-rwf { font-size:11px; font-weight:500; color:var(--text-dim); margin-left:4px; }

.sli-status {
    display:inline-block; padding:3px 10px; border-radius:20px;
    font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em;
}
.sli-status--ok     { background:rgba(16,185,129,.1); color:#047857; }
.sli-status--voided { background:rgba(226,75,74,.1);  color:#a32d2d; }

.sli-expand-btn {
    width:30px; height:30px; border-radius:8px;
    border:1.5px solid var(--border); background:var(--surface);
    cursor:pointer; display:inline-flex; align-items:center; justify-content:center;
    color:var(--text-dim); transition:all .15s;
}
.sli-expand-btn svg { width:14px; height:14px; transition:transform .2s; }
.sli-expand-btn.open { background:#3b6bd4; border-color:#3b6bd4; color:#fff; }
.sli-expand-btn.open svg { transform:rotate(180deg); }
.sli-expand-btn:not(.open):hover { border-color:#3b6bd4; color:#3b6bd4; }

/* ── Expanded detail ── */
.sli-detail-td { padding:0; background:rgba(59,107,212,.025); border-bottom:0.5px solid var(--border); }
.sli-detail-body { padding:24px; border-top:2px solid #3b6bd4; }

.sli-detail-grid { display:grid; grid-template-columns:1.8fr 1fr 1fr; gap:24px; }
@media(max-width:1000px){ .sli-detail-grid{ grid-template-columns:1fr 1fr; } }
@media(max-width:640px) { .sli-detail-grid{ grid-template-columns:1fr; } }

.sli-detail-sec-head {
    display:flex; align-items:center; gap:7px;
    font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.07em;
    color:var(--text-dim); margin-bottom:12px; padding-bottom:8px;
    border-bottom:1px solid var(--border);
}

.sli-inner-tbl { width:100%; border-collapse:collapse; font-size:12px; }
.sli-inner-tbl th {
    padding:6px 10px; color:var(--text-dim); font-weight:700; text-transform:uppercase;
    font-size:10px; letter-spacing:.05em; border-bottom:1px solid var(--border); text-align:left;
}
.sli-inner-tbl th.r, .sli-inner-tbl td.r { text-align:right; }
.sli-inner-tbl td { padding:8px 10px; color:var(--text); border-bottom:0.5px solid var(--border); }
.sli-inner-tbl tr:last-child td { border-bottom:none; }
.sli-inner-tbl .mono { font-family:var(--mono, monospace); }
.sli-inner-tbl .bold { font-weight:700; }
.sli-inner-tbl tfoot td { font-size:11px; padding-top:10px; }

.sli-pmt-line {
    display:flex; justify-content:space-between; align-items:center;
    padding:8px 0; border-bottom:0.5px solid var(--border); font-size:13px;
}
.sli-pmt-line:last-of-type { border-bottom:none; }
.sli-pmt-line--discount { margin-top:4px; padding-top:10px; border-top:1px dashed var(--border); border-bottom:none; }
.sli-pmt-name { color:var(--text-dim); font-weight:500; }
.sli-pmt-val  { font-weight:700; font-family:var(--mono, monospace); color:var(--text); }

.sli-credit-alert {
    display:flex; align-items:center; gap:6px;
    font-size:11px; font-weight:600; color:#b45309;
    background:rgba(245,158,11,.08); border:1px solid rgba(245,158,11,.3);
    border-radius:8px; padding:8px 12px; margin-top:10px;
}

.sli-meta { display:flex; flex-direction:column; gap:0; }
.sli-meta-row {
    display:grid; grid-template-columns:90px 1fr; gap:8px;
    padding:7px 0; border-bottom:0.5px solid var(--border); font-size:12px;
}
.sli-meta-row:last-child { border-bottom:none; }
.sli-meta-k { color:var(--text-dim); font-weight:600; }
.sli-meta-v { color:var(--text); }

.sli-print-btn {
    display:inline-flex; align-items:center; gap:7px; margin-top:14px;
    padding:8px 16px; border:1.5px solid var(--border); border-radius:9px;
    font-size:12px; font-weight:600; color:var(--text-dim); text-decoration:none;
    background:var(--surface); transition:all .15s;
}
.sli-print-btn:hover { border-color:#3b6bd4; color:#3b6bd4; background:rgba(59,107,212,.04); }

/* Empty state */
.sli-empty-state { padding:0; }
.sli-empty-inner {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    padding:64px 24px; gap:10px;
}
.sli-empty-icon {
    width:64px; height:64px; border-radius:18px;
    background:var(--surface2); border:1px solid var(--border);
    display:flex; align-items:center; justify-content:center; color:var(--text-dim);
}
.sli-empty-title { font-size:15px; font-weight:700; color:var(--text); }
.sli-empty-sub   { font-size:13px; color:var(--text-dim); }

/* Infinite scroll feedback */
.sli-loading-more {
    display:flex; align-items:center; justify-content:center; gap:8px;
    padding:18px; font-size:13px; color:var(--text-dim); font-weight:500;
    border-top:1px solid var(--border);
}
.sli-all-loaded {
    padding:14px 20px; text-align:center;
    font-size:12px; color:var(--text-faint,var(--text-dim)); font-weight:500;
    border-top:1px solid var(--border);
    letter-spacing:.2px;
}
@keyframes sli-spin { to { transform:rotate(360deg); } }
.sli-spin { animation:sli-spin .8s linear infinite; }
</style>
@endpush

<div style="display:flex;flex-direction:column;gap:22px;padding-bottom:48px;">

    {{-- Header --}}
    <div class="sli-page-head">
        <div>
            <h1 class="sli-page-title">Sales History</h1>
            <p class="sli-page-sub">Browse, search and inspect all transactions for this shop</p>
        </div>
        <div class="sli-page-actions">
            <a href="{{ route('shop.dashboard') }}" class="sli-btn-ghost">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Dashboard
            </a>
            <a href="{{ route('shop.pos') }}" class="sli-btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Sale
            </a>
        </div>
    </div>

    <livewire:shop.sales.sales-index />

</div>

</x-app-layout>
