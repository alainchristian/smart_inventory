<div>
<style>
/* ═══════════════════════════════════════════════════════════
   DCR — Daily Close Report   prefix: dcr-
   ═══════════════════════════════════════════════════════════ */

/* ── Date navigation bar ── */
.dcr-datebar      { display:flex;align-items:center;justify-content:space-between;
                    gap:10px;flex-wrap:wrap;margin-bottom:20px;
                    background:white;border:1px solid var(--border);border-radius:12px;
                    padding:10px 14px;box-shadow:0 1px 4px rgba(0,0,0,0.05); }
.dcr-nav-group    { display:flex;align-items:center;gap:6px; }
.dcr-nav-btn      { display:flex;align-items:center;justify-content:center;
                    width:32px;height:32px;border-radius:8px;
                    border:1px solid var(--border);background:var(--surface2);
                    color:var(--text-dim);cursor:pointer;font-size:16px;
                    font-family:var(--font);transition:all 0.15s;flex-shrink:0; }
.dcr-nav-btn:hover { border-color:var(--accent);color:var(--accent); }
.dcr-nav-btn:disabled { opacity:0.35;cursor:default; }
.dcr-date-input   { padding:6px 10px;border-radius:8px;border:1.5px solid var(--border);
                    background:var(--surface2);color:var(--text);font-size:13px;
                    font-weight:600;font-family:var(--font);cursor:pointer;
                    height:32px;box-sizing:border-box; }
.dcr-date-input:focus { outline:none;border-color:var(--accent); }
.dcr-today-btn    { padding:5px 12px;height:32px;border-radius:8px;
                    border:1px solid var(--border);background:var(--surface2);
                    color:var(--text-dim);font-size:12px;font-weight:600;
                    cursor:pointer;font-family:var(--font);white-space:nowrap;
                    transition:all 0.15s; }
.dcr-today-btn:hover { border-color:var(--accent);color:var(--accent); }
.dcr-day-label    { font-size:13px;font-weight:600;color:var(--text-dim);
                    white-space:nowrap; }

/* ── Flash / Alert banners ── */
.dcr-flash        { display:flex;align-items:flex-start;gap:10px;padding:11px 14px;
                    border-radius:10px;font-size:12px;margin-bottom:10px;
                    border-left:3px solid; }
.dcr-flash-ok     { background:var(--green-dim);border-color:var(--green);color:var(--green); }
.dcr-flash-err    { background:var(--red-dim);border-color:var(--red);color:var(--red); }
.dcr-flash-warn   { background:var(--amber-dim);border-color:var(--amber);color:var(--amber); }
.dcr-flash strong { font-weight:700; }
.dcr-flash-sub    { color:var(--text-dim);font-size:11px;margin-top:2px; }

/* ── KPI strip ── */
.dcr-kpis         { display:grid;grid-template-columns:repeat(4,1fr);
                    gap:0;background:white;border-radius:14px;
                    overflow:hidden;border:1px solid var(--border);
                    box-shadow:0 1px 4px rgba(0,0,0,0.05);margin-bottom:20px; }
.dcr-kpi          { background:white;padding:14px 16px;
                    border-right:1px solid var(--border); }
.dcr-kpi:last-child { border-right:none; }
.dcr-kpi-label    { font-size:10px;font-weight:700;text-transform:uppercase;
                    letter-spacing:0.6px;color:var(--text-dim);margin-bottom:6px; }
.dcr-kpi-val      { font-size:20px;font-weight:800;font-family:var(--mono);
                    line-height:1;letter-spacing:-0.5px; }
.dcr-kpi-sub      { font-size:11px;color:var(--text-dim);margin-top:4px; }

/* ── Balance statement card ── */
.dcr-balance      { background:white;border:1px solid var(--border);
                    border-radius:16px;overflow:hidden;margin-bottom:20px;
                    box-shadow:0 1px 4px rgba(0,0,0,0.05); }
.dcr-balance-head { display:flex;align-items:center;justify-content:space-between;
                    padding:13px 18px;border-bottom:1px solid var(--border);
                    background:var(--surface2); }
.dcr-balance-title{ font-size:13px;font-weight:700;color:var(--text); }
.dcr-badge        { display:inline-flex;align-items:center;gap:5px;padding:3px 10px;
                    border-radius:20px;font-size:11px;font-weight:700; }
.dcr-badge-ok     { background:var(--green-dim);color:var(--green); }
.dcr-badge-err    { background:var(--red-dim);color:var(--red); }
.dcr-badge-open   { background:var(--green-dim);color:var(--green); }
.dcr-badge-closed { background:var(--amber-dim);color:var(--amber); }
.dcr-badge-locked { background:var(--surface);color:var(--text-dim);
                    border:1px solid var(--border); }

.dcr-taccounts    { display:grid;grid-template-columns:1fr 1fr; }
.dcr-tacol        { padding:0; }
.dcr-tacol:first-child { border-right:1px solid var(--border); }
.dcr-tacol-head   { padding:8px 18px;font-size:10px;font-weight:700;
                    text-transform:uppercase;letter-spacing:0.6px;color:var(--text-dim);
                    border-bottom:1px solid var(--border);background:var(--surface2); }
.dcr-tarow        { display:flex;justify-content:space-between;align-items:center;
                    padding:9px 18px;border-bottom:1px solid var(--border);
                    transition:background 0.1s; }
.dcr-tarow:last-child { border-bottom:none; }
.dcr-tarow:hover  { background:var(--surface2); }
.dcr-tarow-lbl    { font-size:12px;color:var(--text-dim); }
.dcr-tarow-hint   { font-size:10px;color:var(--text-dim);margin-top:1px; }
.dcr-tarow-val    { font-size:12px;font-weight:700;font-family:var(--mono);
                    white-space:nowrap; }
.dcr-tatotal      { display:flex;justify-content:space-between;align-items:center;
                    padding:11px 18px;background:var(--surface2);
                    border-top:2px solid var(--border); }
.dcr-tatotal-lbl  { font-size:12px;font-weight:700;color:var(--text); }
.dcr-tatotal-val  { font-size:20px;font-weight:800;font-family:var(--mono);
                    color:var(--accent); }
.dcr-tatotal-unit { font-size:11px;font-weight:400;color:var(--text-dim); }

/* ── Lower 2-col grid ── */
.dcr-lower        { display:grid;grid-template-columns:1fr 1.5fr;gap:14px;margin-bottom:20px; }
.dcr-panel        { background:white;border:1px solid var(--border);
                    border-radius:14px;overflow:hidden;
                    box-shadow:0 1px 4px rgba(0,0,0,0.05); }
.dcr-panel-head   { display:flex;align-items:center;justify-content:space-between;
                    padding:11px 14px;border-bottom:1px solid var(--border);
                    background:var(--surface2); }
.dcr-panel-lbl    { font-size:10px;font-weight:700;text-transform:uppercase;
                    letter-spacing:0.6px;color:var(--text-dim); }
.dcr-panel-body   { padding:14px; }

/* Expense bars */
.dcr-exp-row      { margin-bottom:10px; }
.dcr-exp-row:last-child { margin-bottom:0; }
.dcr-exp-meta     { display:flex;justify-content:space-between;
                    font-size:11px;margin-bottom:4px; }
.dcr-exp-cat      { color:var(--text-dim);font-weight:500; }
.dcr-exp-amt      { color:var(--text);font-weight:700;font-family:var(--mono); }
.dcr-bar-track    { height:5px;border-radius:3px;background:var(--border);overflow:hidden; }
.dcr-bar-fill     { height:100%;border-radius:3px;background:var(--red);transition:width 0.4s ease; }

/* Transaction feed */
.dcr-feed         { max-height:340px;overflow-y:auto;
                    scrollbar-width:thin;scrollbar-color:var(--border) transparent; }
.dcr-feed-row     { display:flex;align-items:center;gap:8px;padding:8px 14px;
                    border-bottom:1px solid var(--border);transition:background 0.1s; }
.dcr-feed-row:last-child { border-bottom:none; }
.dcr-feed-row:hover { background:var(--surface2); }
.dcr-feed-dot     { width:7px;height:7px;border-radius:50%;flex-shrink:0; }
.dcr-feed-time    { font-size:10px;color:var(--text-dim);font-family:var(--mono);
                    white-space:nowrap;width:30px;flex-shrink:0; }
.dcr-feed-desc    { font-size:11px;color:var(--text-dim);flex:1;
                    overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0; }
.dcr-feed-mth     { font-size:10px;color:var(--text-dim);flex-shrink:0;
                    white-space:nowrap;display:none; }
.dcr-feed-amt     { font-size:12px;font-weight:700;font-family:var(--mono);
                    flex-shrink:0;white-space:nowrap; }

/* ── Credit panel ── */
.dcr-cust-row     { display:flex;justify-content:space-between;align-items:center;
                    padding:8px 0;border-bottom:1px solid var(--border); }
.dcr-cust-row:last-child { border-bottom:none; }
.dcr-cust-name    { font-size:12px;font-weight:600;color:var(--text); }
.dcr-cust-sub     { font-size:10px;color:var(--text-dim);margin-top:1px; }
.dcr-cust-amt     { font-size:13px;font-weight:700;font-family:var(--mono); }

/* ── Session cards ── */
.dcr-session-label{ font-size:10px;font-weight:700;text-transform:uppercase;
                    letter-spacing:0.6px;color:var(--text-dim);margin-bottom:10px; }
.dcr-session      { background:white;border:1px solid var(--border);
                    border-radius:14px;overflow:hidden;margin-bottom:10px;
                    box-shadow:0 1px 4px rgba(0,0,0,0.05);transition:box-shadow 0.15s; }
.dcr-session:last-child { margin-bottom:0; }
.dcr-session.dcr-session-open { border-color:var(--red); }
.dcr-session-head { display:flex;align-items:center;gap:10px;padding:13px 16px;
                    cursor:pointer;user-select:none;transition:background 0.1s; }
.dcr-session-head:hover { background:var(--surface2); }
.dcr-session-shop { font-size:13px;font-weight:700;color:var(--text);flex:1;
                    overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0; }
.dcr-session-who  { font-size:11px;color:var(--text-dim);white-space:nowrap; }
.dcr-session-actions { display:flex;align-items:center;gap:6px;flex-shrink:0; }
.dcr-lock-btn     { padding:4px 10px;border-radius:7px;font-size:11px;font-weight:700;
                    border:1.5px solid var(--accent);color:var(--accent);
                    background:var(--accent-dim);cursor:pointer;font-family:var(--font);
                    white-space:nowrap;transition:opacity 0.15s; }
.dcr-lock-btn:disabled { opacity:0.4;cursor:not-allowed; }
.dcr-chevron      { width:16px;height:16px;color:var(--text-dim);flex-shrink:0;
                    transition:transform 0.2s; }
.dcr-chevron.dcr-open { transform:rotate(180deg); }

/* Session inline KPI strip */
.dcr-session-kpis { display:flex;border-top:1px solid var(--border); }
.dcr-skpi         { flex:1;padding:9px 12px;text-align:center;
                    border-right:1px solid var(--border); }
.dcr-skpi:last-child { border-right:none; }
.dcr-skpi-lbl     { font-size:9px;font-weight:700;text-transform:uppercase;
                    letter-spacing:0.4px;color:var(--text-dim);margin-bottom:3px; }
.dcr-skpi-val     { font-size:12px;font-weight:700;font-family:var(--mono); }

/* Session expanded detail */
.dcr-detail       { border-top:1px solid var(--border);background:white;
                    padding:0; }
.dcr-detail-grid  { display:grid;grid-template-columns:repeat(3,1fr);gap:0; }
.dcr-detail-grid > div { padding:20px 22px; }
.dcr-detail-grid > div:not(:last-child) { border-right:1px solid var(--border); }
.dcr-detail-col-title { font-size:10px;font-weight:700;text-transform:uppercase;
                         letter-spacing:0.5px;color:var(--text-dim);margin-bottom:10px; }
.dcr-detail-line  { display:flex;justify-content:space-between;align-items:baseline;
                    padding:5px 0;border-bottom:1px solid var(--border);font-size:12px; }
.dcr-detail-line:last-child { border-bottom:none; }
.dcr-detail-lbl   { color:var(--text-dim); }
.dcr-detail-val   { font-weight:600;font-family:var(--mono);color:var(--text);
                    white-space:nowrap; }

/* ── Empty state ── */
.dcr-empty        { text-align:center;padding:52px 24px;border-radius:14px;
                    border:1px solid var(--border);background:white;
                    box-shadow:0 1px 4px rgba(0,0,0,0.05); }
.dcr-empty-icon   { font-size:32px;margin-bottom:12px; }
.dcr-empty-title  { font-size:14px;font-weight:600;color:var(--text-dim); }
.dcr-empty-sub    { font-size:12px;color:var(--text-dim);margin-top:4px; }

/* ══════════════ RESPONSIVE ══════════════ */

@media(max-width:900px) {
    .dcr-lower { grid-template-columns:1fr; }
    .dcr-feed  { max-height:260px; }
    .dcr-feed-mth { display:none; }
    .dcr-detail-grid { grid-template-columns:1fr 1fr; }
    .dcr-detail-grid > div:not(:last-child) { border-right:none;border-bottom:1px solid var(--border); }
    .dcr-detail-grid > div:nth-child(odd):not(:last-child) { border-right:1px solid var(--border); }
}

@media(max-width:640px) {
    .dcr-datebar    { gap:8px; }
    .dcr-day-label  { display:none; }
    .dcr-kpis       { grid-template-columns:repeat(2,1fr); }
    .dcr-kpi:nth-child(even) { border-right:none; }
    .dcr-kpi:nth-child(1),.dcr-kpi:nth-child(2) { border-bottom:1px solid var(--border); }
    .dcr-kpi-val    { font-size:17px; }
    .dcr-taccounts  { grid-template-columns:1fr; }
    .dcr-tacol:first-child { border-right:none;border-bottom:1px solid var(--border); }
    .dcr-tatotal-val { font-size:17px; }
    .dcr-session-kpis { flex-wrap:wrap; }
    .dcr-skpi         { min-width:50%;flex:none; }
    .dcr-skpi:nth-child(2n) { border-right:none; }
    .dcr-skpi:nth-last-child(-n+2) { border-top:1px solid var(--border); }
    .dcr-detail-grid { grid-template-columns:1fr; }
    .dcr-session-who { display:none; }
}

@media(max-width:400px) {
    .dcr-kpi-val { font-size:15px; }
    .dcr-skpi    { min-width:50%; }
}

/* ── Session detail modal ── */
.dcr-modal-wrap {
    position:fixed;inset:0;z-index:100;
    display:flex;align-items:center;justify-content:center;padding:20px;
    background:rgba(10,15,30,0.5);backdrop-filter:blur(3px);
}
.dcr-modal {
    background:white;border-radius:16px;
    box-shadow:0 24px 80px rgba(0,0,0,0.25);
    width:100%;max-width:760px;max-height:88vh;
    display:flex;flex-direction:column;overflow:hidden;
}
.dcr-modal-head {
    display:flex;align-items:center;justify-content:space-between;gap:12px;
    padding:14px 20px;border-bottom:1px solid var(--border);
    background:var(--surface2);flex-shrink:0;
}
.dcr-modal-body { overflow-y:auto;flex:1;overscroll-behavior:contain;background:white; }
.dcr-modal-close {
    display:flex;align-items:center;justify-content:center;
    width:28px;height:28px;border-radius:7px;border:1px solid var(--border);
    background:var(--surface2);color:var(--text-dim);font-size:18px;
    cursor:pointer;font-family:var(--font);line-height:1;flex-shrink:0;transition:all 0.15s;
}
.dcr-modal-close:hover { border-color:var(--red);color:var(--red); }
/* Verdict banner */
.dcr-verdict {
    display:flex;align-items:center;gap:10px;padding:10px 20px;
    font-size:12px;font-weight:600;border-bottom:1px solid var(--border);flex-shrink:0;
}
.dcr-verdict-ok   { background:var(--green-dim);color:var(--green); }
.dcr-verdict-err  { background:var(--red-dim);color:var(--red); }
.dcr-verdict-warn { background:var(--amber-dim);color:var(--amber); }
.dcr-verdict-live { background:var(--accent-dim);color:var(--accent); }
.dcr-verdict-seal { background:var(--surface2);color:var(--text-dim); }
/* Reconciliation formula strip */
.dcr-recon-strip {
    padding:10px 20px 12px;background:var(--surface2);
    border-bottom:1px solid var(--border);flex-shrink:0;
    overflow-x:auto;-webkit-overflow-scrolling:touch;
}
.dcr-recon-strip::-webkit-scrollbar { height:3px; }
.dcr-recon-strip::-webkit-scrollbar-thumb { background:var(--border);border-radius:2px; }
.dcr-recon-eq {
    display:flex;align-items:flex-end;gap:6px;
    min-width:max-content;
}
.dcr-recon-item {
    display:flex;flex-direction:column;align-items:center;gap:2px;
}
.dcr-recon-label {
    font-size:9px;font-weight:700;text-transform:uppercase;
    letter-spacing:0.4px;color:var(--text-dim);font-family:var(--font);
}
.dcr-recon-val {
    font-size:12px;font-weight:700;font-family:var(--mono);color:var(--text);
}
.dcr-recon-op {
    font-size:14px;font-weight:600;color:var(--text-dim);
    padding-bottom:2px;flex-shrink:0;
}
.dcr-recon-eq-sign {
    font-size:16px;font-weight:700;padding-bottom:2px;flex-shrink:0;
}
/* Action footer */
.dcr-modal-footer {
    display:flex;align-items:center;justify-content:space-between;gap:10px;
    padding:12px 18px;border-top:1px solid var(--border);
    background:var(--surface2);flex-shrink:0;
}
.dcr-footer-note { font-size:11px;color:var(--text-dim);flex:1; }
.dcr-btn-lock {
    padding:7px 18px;border-radius:8px;font-size:12px;font-weight:700;
    border:none;cursor:pointer;font-family:var(--font);
    background:var(--accent);color:white;transition:opacity 0.15s;white-space:nowrap;
}
.dcr-btn-lock:hover { opacity:0.85; }
.dcr-btn-reopen {
    padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;
    border:1.5px solid var(--border);background:var(--surface2);
    color:var(--text-dim);cursor:pointer;font-family:var(--font);
    transition:all 0.15s;white-space:nowrap;
}
.dcr-btn-reopen:hover { border-color:var(--amber);color:var(--amber); }
/* Variance alert row */
.dcr-variance-alert {
    display:flex;justify-content:space-between;align-items:baseline;
    padding:7px 10px;margin:3px -10px;border-radius:6px;font-size:12px;
}
@media(max-width:640px) {
    .dcr-modal-wrap { padding:0;align-items:flex-end; }
    .dcr-modal { border-radius:16px 16px 0 0;max-height:90vh;max-width:100%; }
    .dcr-modal-head { padding:12px 14px; }
    .dcr-detail-grid { grid-template-columns:1fr !important; }
    .dcr-detail-grid > div { padding:14px 16px; }
    .dcr-detail-grid > div:not(:last-child) { border-right:none !important;border-bottom:1px solid var(--border); }
    .dcr-modal-footer { flex-wrap:wrap; }
}
</style>

<div>

    {{-- ════════════════════════════════════════
         DATE NAVIGATION BAR
         ════════════════════════════════════════ --}}
    <div class="dcr-datebar">
        <div class="dcr-nav-group">
            <button class="dcr-nav-btn" wire:click="previousDay" title="Previous day">‹</button>
            <input type="date"
                   class="dcr-date-input"
                   wire:model.live="reportDate"
                   max="{{ today()->toDateString() }}">
            <button class="dcr-nav-btn"
                    wire:click="nextDay"
                    title="Next day"
                    @if(\Carbon\Carbon::parse($reportDate)->isToday()) disabled @endif>›</button>
            <button class="dcr-today-btn" wire:click="goToToday">Today</button>
            <span class="dcr-day-label">
                {{ \Carbon\Carbon::parse($reportDate)->format('l, d M Y') }}
            </span>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="dcr-flash dcr-flash-ok" style="margin-bottom:14px;">
            <svg style="width:14px;height:14px;flex-shrink:0;margin-top:1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="dcr-flash dcr-flash-err" style="margin-bottom:14px;">
            <svg style="width:14px;height:14px;flex-shrink:0;margin-top:1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- ════════════════════════════════════════
         EMPTY STATE
         ════════════════════════════════════════ --}}
    @if($sessions->isEmpty())
        <div class="dcr-empty">
            <div class="dcr-empty-icon">📋</div>
            <div class="dcr-empty-title">No sessions found</div>
            <div class="dcr-empty-sub">
                {{ \Carbon\Carbon::parse($reportDate)->format('d M Y') }} had no recorded activity.
            </div>
        </div>
    @else

    {{-- All day-level variables ($dayOpening, $allClosed, $inRows, $outRows, $txFeed, etc.)
         are computed in DailyCloseReport::computeSummary() and passed via render(). --}}

    {{-- ════════════════════════════════════════
         ALERT BANNERS
         ════════════════════════════════════════ --}}
    @if(!$allClosed && $totalSessions > 0)
        <div class="dcr-flash dcr-flash-warn">
            <svg style="width:15px;height:15px;flex-shrink:0;margin-top:1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            <div>
                <strong>{{ $totalSessions - $closedSessions }} session{{ ($totalSessions - $closedSessions) !== 1 ? 's' : '' }} not yet closed</strong>
                <div class="dcr-flash-sub">Remind shop managers to close the day.</div>
            </div>
        </div>
    @endif
    @if(abs($dayVariance) > 5000)
        <div class="dcr-flash dcr-flash-err">
            <svg style="width:15px;height:15px;flex-shrink:0;margin-top:1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            <div>
                <strong>Cash variance: {{ $dayVariance >= 0 ? '+' : '' }}{{ number_format($dayVariance) }} RWF</strong>
                <div class="dcr-flash-sub">Review session reconciliations below for discrepancies.</div>
            </div>
        </div>
    @endif
    @if($allClosed && $totalSessions > 0)
        <div class="dcr-flash dcr-flash-ok">
            <svg style="width:15px;height:15px;flex-shrink:0;margin-top:1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            <div>
                <strong>All sessions closed</strong>
                <div class="dcr-flash-sub">Lock them to make today's records immutable.</div>
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════════════
         DAY KPI STRIP
         ════════════════════════════════════════ --}}
    <div class="dcr-kpis">
        <div class="dcr-kpi">
            <div class="dcr-kpi-label">Revenue</div>
            <div class="dcr-kpi-val" style="color:var(--accent);">{{ number_format($dayRevenue) }}</div>
            <div class="dcr-kpi-sub">{{ $saleCount }} sale{{ $saleCount !== 1 ? 's' : '' }} · RWF</div>
        </div>
        <div class="dcr-kpi">
            <div class="dcr-kpi-label">Operating Profit</div>
            <div class="dcr-kpi-val"
                 style="color:{{ $operatingProfit >= 0 ? 'var(--green)' : 'var(--red)' }};">
                {{ number_format($operatingProfit) }}
            </div>
            <div class="dcr-kpi-sub">Revenue − refunds − expenses · RWF</div>
        </div>
        <div class="dcr-kpi">
            <div class="dcr-kpi-label">Sessions</div>
            <div class="dcr-kpi-val"
                 style="color:{{ $allClosed ? 'var(--green)' : 'var(--amber)' }};">
                {{ $closedSessions }}<span style="font-size:13px;font-weight:500;color:var(--text-dim);">/{{ $totalSessions }}</span>
            </div>
            <div class="dcr-kpi-sub">{{ $allClosed ? 'All closed' : ($totalSessions - $closedSessions) . ' still open' }}</div>
        </div>
        <div class="dcr-kpi">
            <div class="dcr-kpi-label">Cash Variance</div>
            <div class="dcr-kpi-val"
                 style="color:{{ $dayVariance < 0 ? 'var(--red)' : ($dayVariance > 0 ? 'var(--amber)' : 'var(--text-dim)') }};">
                {{ $dayVariance >= 0 ? '+' : '' }}{{ number_format($dayVariance) }}
            </div>
            <div class="dcr-kpi-sub">Across all sessions · RWF</div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         DAILY BALANCE STATEMENT
         ════════════════════════════════════════ --}}
    <div class="dcr-balance">
        <div class="dcr-balance-head">
            <div class="dcr-balance-title">Daily Balance Statement</div>
            <span class="dcr-badge {{ $isBalanced ? 'dcr-badge-ok' : 'dcr-badge-err' }}">
                @if($isBalanced)
                    <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Balanced
                @else
                    Off by {{ number_format(abs($balanceDiff)) }} RWF
                @endif
            </span>
        </div>

        <div class="dcr-taccounts">
            {{-- Money In --}}
            <div class="dcr-tacol">
                <div class="dcr-tacol-head">Money In</div>
                @foreach($inRows as [$lbl, $val, $colorHex, $hint])
                    <div class="dcr-tarow">
                        <div>
                            <div class="dcr-tarow-lbl">{{ $lbl }}</div>
                            <div class="dcr-tarow-hint">{{ $hint }}</div>
                        </div>
                        <div class="dcr-tarow-val"
                             style="color:{{ $val > 0 ? 'var(--accent)' : 'var(--text-dim)' }};">
                            {{ number_format($val) }}
                        </div>
                    </div>
                @endforeach
                <div class="dcr-tatotal">
                    <div class="dcr-tatotal-lbl">Total In</div>
                    <div>
                        <span class="dcr-tatotal-val">{{ number_format($totalIn) }}</span>
                        <span class="dcr-tatotal-unit"> RWF</span>
                    </div>
                </div>
            </div>

            {{-- Where It Went --}}
            <div class="dcr-tacol">
                <div class="dcr-tacol-head">Where It Went</div>
                @foreach($outRows as [$lbl, $val, $colorHex, $hint])
                    <div class="dcr-tarow">
                        <div>
                            <div class="dcr-tarow-lbl">{{ $lbl }}</div>
                            <div class="dcr-tarow-hint">{{ $hint }}</div>
                        </div>
                        <div class="dcr-tarow-val" style="color:var(--text);">
                            {{ number_format($val) }}
                        </div>
                    </div>
                @endforeach
                <div class="dcr-tatotal">
                    <div class="dcr-tatotal-lbl">Total Accounted For</div>
                    <div>
                        <span class="dcr-tatotal-val">{{ number_format($totalOut) }}</span>
                        <span class="dcr-tatotal-unit"> RWF</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         LOWER GRID — EXPENSE BREAKDOWN + TX FEED
         ════════════════════════════════════════ --}}
    <div class="dcr-lower">

        {{-- Expense category bars --}}
        <div class="dcr-panel">
            <div class="dcr-panel-head">
                <span class="dcr-panel-lbl">Expenses by Category</span>
                <span style="font-family:var(--mono);font-size:12px;font-weight:700;color:var(--red);">
                    {{ number_format($dayExpenses) }} RWF
                </span>
            </div>
            <div class="dcr-panel-body">
                @forelse($expByCategory as $cat => $amt)
                    <div class="dcr-exp-row">
                        <div class="dcr-exp-meta">
                            <span class="dcr-exp-cat">{{ $cat }}</span>
                            <span class="dcr-exp-amt">{{ number_format($amt) }}</span>
                        </div>
                        <div class="dcr-bar-track">
                            <div class="dcr-bar-fill"
                                 style="width:{{ round(($amt / max($maxExpCat, 1)) * 100) }}%;"></div>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;padding:20px;font-size:12px;color:var(--text-dim);">
                        No expenses recorded today
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Transaction feed — $txFeed, $expCount, $drawCount passed from render() --}}
        <div class="dcr-panel">
            <div class="dcr-panel-head">
                <span class="dcr-panel-lbl">All Transactions</span>
                <span style="font-size:10px;color:var(--text-dim);">
                    {{ $saleCount }}s · {{ $expCount }}e · {{ $drawCount }}w
                </span>
            </div>
            <div class="dcr-feed">
                @forelse($txFeed as $tx)
                    @php
                        $txColorMap = ['sale'=>'var(--green)','expense'=>'var(--red)','withdrawal'=>'var(--amber)','deposit'=>'var(--accent)'];
                        $txSignMap  = ['sale'=>'+','expense'=>'−','withdrawal'=>'−','deposit'=>'−'];
                        $txDot  = $txColorMap[$tx['type']] ?? 'var(--text-dim)';
                        $txAmtC = $txColorMap[$tx['type']] ?? 'var(--text)';
                        $txSign = $txSignMap[$tx['type']] ?? '';
                    @endphp
                    <div class="dcr-feed-row">
                        <div class="dcr-feed-dot" style="background:{{ $txDot }};"></div>
                        <div class="dcr-feed-time">
                            {{ $tx['time'] instanceof \Carbon\Carbon ? $tx['time']->format('H:i') : \Carbon\Carbon::parse($tx['time'])->format('H:i') }}
                        </div>
                        <div class="dcr-feed-desc" title="{{ $tx['desc'] }}">{{ $tx['desc'] }}</div>
                        <div class="dcr-feed-mth">{{ $tx['method'] ?? '' }}</div>
                        <div class="dcr-feed-amt" style="color:{{ $txAmtC }};">
                            {{ $txSign }}{{ number_format($tx['amount']) }}
                        </div>
                    </div>
                @empty
                    <div style="padding:32px;text-align:center;font-size:12px;color:var(--text-dim);">
                        No transactions recorded
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         SALES SNAPSHOT
         ════════════════════════════════════════ --}}
    @if($todaySales->isNotEmpty())
    <div style="background:white;border:1px solid var(--border);
                border-radius:14px;overflow:hidden;margin-bottom:20px;
                box-shadow:0 1px 4px rgba(0,0,0,0.05);">
        <div class="dcr-panel-head">
            <span class="dcr-panel-lbl">Sales Snapshot</span>
            <span style="font-size:10px;color:var(--text-dim);">
                {{ \Carbon\Carbon::parse($reportDate)->format('d M Y') }}
            </span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--border);">
            @foreach([
                ['Total Sales', number_format($dayRevenue), 'RWF', 'var(--accent)'],
                ['Avg. Basket', $avgBasket ? number_format($avgBasket) : '—', 'RWF', 'var(--text)'],
                ['Peak Hour',   $peakHourFmt ?? '—', '', 'var(--green)'],
            ] as [$l, $v, $u, $c])
            <div style="padding:14px 16px;background:var(--surface2);">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;
                            letter-spacing:0.5px;color:var(--text-dim);margin-bottom:5px;">{{ $l }}</div>
                <div style="font-size:18px;font-weight:800;font-family:var(--mono);
                            color:{{ $c }};letter-spacing:-0.3px;">{{ $v }}
                    @if($u)<span style="font-size:10px;font-weight:400;color:var(--text-dim);"> {{ $u }}</span>@endif
                </div>
            </div>
            @endforeach
        </div>
        {{-- Payment channel breakdown --}}
        @if($pCash > 0 || $pMomo > 0 || ($showCard && $pCard > 0) || ($showBank && $pBank > 0) || $pCredit > 0)
        <div style="padding:12px 16px;border-top:1px solid var(--border);display:flex;gap:8px;flex-wrap:wrap;">
            @foreach(array_filter([
                $pCash > 0                   ? ['Cash',         $pCash,   'var(--accent)'] : null,
                $pMomo > 0                   ? ['Mobile Money', $pMomo,   'var(--accent)'] : null,
                $showCard && $pCard > 0      ? ['Card',         $pCard,   'var(--accent)'] : null,
                $showBank && $pBank > 0      ? ['Bank',         $pBank,   'var(--accent)'] : null,
                $pCredit > 0                 ? ['Credit',       $pCredit, 'var(--amber)']  : null,
            ]) as [$ch, $chv, $chc])
                <div style="display:flex;flex-direction:column;gap:2px;min-width:80px;flex:1;">
                    <div style="font-size:10px;color:var(--text-dim);">{{ $ch }}</div>
                    <div style="font-size:12px;font-weight:700;font-family:var(--mono);color:{{ $chc }};">
                        {{ number_format($chv) }}
                    </div>
                    <div style="height:3px;border-radius:2px;background:var(--border);overflow:hidden;">
                        <div style="height:100%;border-radius:2px;background:{{ $chc }};
                                    width:{{ round(($chv / max($dayRevenue, 1)) * 100) }}%;"></div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    {{-- ════════════════════════════════════════
         CREDIT OUTSTANDING
         ════════════════════════════════════════ --}}
    @if($overdueCustomers->isNotEmpty())
    <div style="background:white;border:1px solid var(--amber);
                border-radius:14px;overflow:hidden;margin-bottom:20px;
                box-shadow:0 1px 4px rgba(0,0,0,0.05);">
        <div class="dcr-panel-head" style="background:var(--amber-dim);">
            <span class="dcr-panel-lbl" style="color:var(--amber);">Outstanding Credit</span>
            <span style="font-size:12px;font-weight:700;font-family:var(--mono);color:var(--amber);">
                {{ number_format($creditOutstanding) }} RWF
            </span>
        </div>
        <div class="dcr-panel-body">
            @foreach($overdueCustomers->take(8) as $cust)
                @php
                    $custIsOverdue = $cust->last_repayment_at &&
                                     $cust->last_repayment_at->diffInDays(now()) > 14;
                @endphp
                <div class="dcr-cust-row">
                    <div>
                        <div class="dcr-cust-name">{{ $cust->name }}</div>
                        <div class="dcr-cust-sub"
                             style="color:{{ $custIsOverdue ? 'var(--red)' : 'var(--text-dim)' }};">
                            @if($custIsOverdue)
                                {{ $cust->last_repayment_at->diffInDays(now()) }}d overdue
                            @elseif($cust->last_repayment_at)
                                {{ $cust->last_repayment_at->diffForHumans() }}
                            @else
                                Outstanding
                            @endif
                        </div>
                    </div>
                    <div class="dcr-cust-amt"
                         style="color:{{ $custIsOverdue ? 'var(--red)' : 'var(--amber)' }};">
                        {{ number_format($cust->outstanding_balance) }}
                    </div>
                </div>
            @endforeach
            @if($overdueCustomers->count() > 8)
                <div style="text-align:center;padding:10px 0 2px;font-size:11px;color:var(--text-dim);">
                    +{{ $overdueCustomers->count() - 8 }} more
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════
         SESSION CARDS
         ════════════════════════════════════════ --}}
    <div class="dcr-session-label">
        Sessions — {{ \Carbon\Carbon::parse($reportDate)->format('d M Y') }}
    </div>

    @foreach($sessions as $session)
    @php
        $isExpanded = $expandedSessionId === $session->id;
        $sv = $session->cash_variance ?? 0;
        $sesShowCard = $settingAllowCard || ($session->total_sales_card ?? 0) > 0;
        $sesShowBank = $settingAllowBankTransfer || ($session->total_sales_bank_transfer ?? 0) > 0;
    @endphp
    <div class="dcr-session {{ $session->isOpen() ? 'dcr-session-open' : '' }}">

        {{-- Session header ── clickable ── --}}
        <div class="dcr-session-head"
             wire:click="toggleExpand({{ $session->id }})">
            <div style="flex:1;min-width:0;">
                <div class="dcr-session-shop">{{ $session->shop->name ?? '—' }}</div>
                <div class="dcr-session-who">
                    {{ $session->openedBy->name ?? '—' }}
                    @if($session->closedBy && $session->closedBy->id !== $session->openedBy?->id)
                        → {{ $session->closedBy->name }}
                    @endif
                </div>
            </div>
            <span class="dcr-badge dcr-badge-{{ $session->status }}">
                {{ ucfirst($session->status) }}
            </span>
            <div class="dcr-session-actions">
                @if(!$session->isLocked())
                    <button class="dcr-lock-btn"
                            wire:click.stop="lockSession({{ $session->id }})"
                            wire:confirm="Lock this session? This cannot be undone."
                            @if(!$session->isClosed()) disabled @endif>
                        🔒 Lock
                    </button>
                @else
                    <span style="font-size:10px;color:var(--text-dim);white-space:nowrap;">
                        🔒 {{ $session->locked_at?->format('H:i') }}
                    </span>
                @endif
                <svg class="dcr-chevron {{ $isExpanded ? 'dcr-open' : '' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>

        {{-- Inline KPI strip --}}
        <div class="dcr-session-kpis">
            @foreach([
                ['Revenue',  $session->total_sales ?? 0,         'var(--accent)'],
                ['Expenses', $session->total_expenses ?? 0,       'var(--red)'],
                ['Banked',   $session->total_bank_deposits ?? 0,  'var(--accent)'],
                ['Variance', $sv, $sv < 0 ? 'var(--red)' : ($sv > 0 ? 'var(--amber)' : 'var(--text-dim)')],
            ] as [$kl, $kv, $kc])
            <div class="dcr-skpi">
                <div class="dcr-skpi-lbl">{{ $kl }}</div>
                <div class="dcr-skpi-val" style="color:{{ $kc }};">{{ number_format($kv) }}</div>
            </div>
            @endforeach
        </div>

    </div>
    @endforeach

    {{-- ════════════════════════════════════════
         SESSION DETAIL MODAL
         ════════════════════════════════════════ --}}
    @if($expandedSessionId && ($dcrmSess = $sessions->firstWhere('id', $expandedSessionId)))
    @php
        $dcrmSv       = $dcrmSess->cash_variance ?? 0;
        $dcrmIsOpen   = $dcrmSess->isOpen();
        $dcrmIsClosed = $dcrmSess->isClosed();
        $dcrmIsLocked = $dcrmSess->isLocked();
        $dcrmShowCard = $settingAllowCard || ($dcrmSess->total_sales_card ?? 0) > 0;
        $dcrmShowBank = $settingAllowBankTransfer || ($dcrmSess->total_sales_bank_transfer ?? 0) > 0;
    @endphp
    <div class="dcr-modal-wrap" wire:click="toggleExpand({{ $expandedSessionId }})">
        <div class="dcr-modal" wire:click.stop>

            {{-- ── Header ── --}}
            <div class="dcr-modal-head">
                <div style="min-width:0;">
                    <div style="font-size:15px;font-weight:700;color:var(--text);
                                overflow:hidden;text-overflow:ellipsis;white-space:nowrap;letter-spacing:-0.2px;">
                        {{ $dcrmSess->shop->name ?? '—' }}
                    </div>
                    <div style="font-size:12px;color:var(--text-dim);margin-top:4px;
                                display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <span>{{ \Carbon\Carbon::parse($reportDate)->format('d M Y') }}</span>
                        <span class="dcr-badge dcr-badge-{{ $dcrmSess->status }}">{{ ucfirst($dcrmSess->status) }}</span>
                        @if($dcrmSess->openedBy)
                            <span>{{ $dcrmSess->openedBy->name }}</span>
                        @endif
                    </div>
                </div>
                <button class="dcr-modal-close"
                        wire:click="toggleExpand({{ $expandedSessionId }})">×</button>
            </div>

            {{-- ── Verdict banner ── --}}
            @if($dcrmIsOpen)
                <div class="dcr-verdict dcr-verdict-live">
                    <svg style="width:13px;height:13px;flex-shrink:0;" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="6"/></svg>
                    Live — session is running, figures are not yet reconciled
                </div>
            @elseif($dcrmIsLocked)
                <div class="dcr-verdict dcr-verdict-seal">
                    🔒 Sealed — locked by {{ $dcrmSess->lockedBy->name ?? '—' }}
                    on {{ $dcrmSess->locked_at?->format('d M Y') }} at {{ $dcrmSess->locked_at?->format('H:i') }}
                </div>
            @elseif($dcrmSv === 0)
                <div class="dcr-verdict dcr-verdict-ok">
                    <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Cash balanced — all money is accounted for, ready to lock
                </div>
            @elseif($dcrmSv < 0)
                <div class="dcr-verdict dcr-verdict-err">
                    <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    Cash shortage of {{ number_format(abs($dcrmSv)) }} RWF — investigate before locking
                </div>
            @else
                <div class="dcr-verdict dcr-verdict-warn">
                    <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    Cash surplus of {{ number_format($dcrmSv) }} RWF — review before locking
                </div>
            @endif

            {{-- ── Cash drawer formula strip (closed & locked sessions only) ── --}}
            @if(!$dcrmIsOpen)
            @php
                $dcrmCashSales = $dcrmSess->total_sales_cash       ?? 0;
                $dcrmCashRep   = $dcrmSess->total_repayments_cash   ?? 0;
                $dcrmCashRef   = $dcrmSess->total_refunds_cash      ?? 0;
                $dcrmCashExp   = $dcrmSess->total_expenses_cash     ?? 0;
                $dcrmCashWd    = $dcrmSess->total_withdrawals_cash  ?? 0;
                $dcrmCashDep   = $dcrmSess->cash_deposits           ?? 0;
                $dcrmExpected  = $dcrmSess->expected_cash           ?? 0;
                $dcrmCounted   = $dcrmSess->actual_cash_counted;
                $dcrmOpening   = $dcrmSess->opening_balance         ?? 0;
            @endphp
            <div class="dcr-recon-strip">
                <div style="font-size:9px;font-weight:700;text-transform:uppercase;
                            letter-spacing:0.5px;color:var(--text-dim);margin-bottom:8px;">
                    Cash drawer formula
                </div>
                <div class="dcr-recon-eq">

                    {{-- Opening --}}
                    <div class="dcr-recon-item">
                        <span class="dcr-recon-label">Opening</span>
                        <span class="dcr-recon-val">{{ number_format($dcrmOpening) }}</span>
                    </div>

                    {{-- Cash sales --}}
                    @if($dcrmCashSales > 0)
                    <span class="dcr-recon-op" style="color:var(--accent);">+</span>
                    <div class="dcr-recon-item">
                        <span class="dcr-recon-label">Cash Sales</span>
                        <span class="dcr-recon-val" style="color:var(--accent);">{{ number_format($dcrmCashSales) }}</span>
                    </div>
                    @endif

                    {{-- Cash repayments --}}
                    @if($dcrmCashRep > 0)
                    <span class="dcr-recon-op" style="color:var(--accent);">+</span>
                    <div class="dcr-recon-item">
                        <span class="dcr-recon-label">Repayments</span>
                        <span class="dcr-recon-val" style="color:var(--accent);">{{ number_format($dcrmCashRep) }}</span>
                    </div>
                    @endif

                    {{-- Refunds --}}
                    @if($dcrmCashRef > 0)
                    <span class="dcr-recon-op" style="color:var(--amber);">−</span>
                    <div class="dcr-recon-item">
                        <span class="dcr-recon-label">Refunds</span>
                        <span class="dcr-recon-val" style="color:var(--amber);">{{ number_format($dcrmCashRef) }}</span>
                    </div>
                    @endif

                    {{-- Cash expenses --}}
                    @if($dcrmCashExp > 0)
                    <span class="dcr-recon-op" style="color:var(--red);">−</span>
                    <div class="dcr-recon-item">
                        <span class="dcr-recon-label">Expenses</span>
                        <span class="dcr-recon-val" style="color:var(--red);">{{ number_format($dcrmCashExp) }}</span>
                    </div>
                    @endif

                    {{-- Cash withdrawals --}}
                    @if($dcrmCashWd > 0)
                    <span class="dcr-recon-op" style="color:var(--amber);">−</span>
                    <div class="dcr-recon-item">
                        <span class="dcr-recon-label">Withdrawals</span>
                        <span class="dcr-recon-val" style="color:var(--amber);">{{ number_format($dcrmCashWd) }}</span>
                    </div>
                    @endif

                    {{-- Cash banked --}}
                    @if($dcrmCashDep > 0)
                    <span class="dcr-recon-op" style="color:var(--accent);">−</span>
                    <div class="dcr-recon-item">
                        <span class="dcr-recon-label">Banked</span>
                        <span class="dcr-recon-val" style="color:var(--accent);">{{ number_format($dcrmCashDep) }}</span>
                    </div>
                    @endif

                    {{-- = Expected --}}
                    <span class="dcr-recon-eq-sign" style="color:var(--text-dim);">=</span>
                    <div class="dcr-recon-item">
                        <span class="dcr-recon-label">Expected</span>
                        <span class="dcr-recon-val" style="font-size:13px;">{{ number_format($dcrmExpected) }}</span>
                    </div>

                    {{-- = or ≠ Counted --}}
                    <span class="dcr-recon-eq-sign"
                          style="color:{{ $dcrmSv === 0 ? 'var(--green)' : ($dcrmSv < 0 ? 'var(--red)' : 'var(--amber)') }};">
                        {{ $dcrmSv === 0 ? '=' : '≠' }}
                    </span>
                    <div class="dcr-recon-item">
                        <span class="dcr-recon-label">Counted</span>
                        <span class="dcr-recon-val"
                              style="font-size:13px;color:{{ $dcrmSv === 0 ? 'var(--green)' : ($dcrmSv < 0 ? 'var(--red)' : 'var(--amber)') }};">
                            {{ $dcrmCounted !== null ? number_format($dcrmCounted) : '—' }}
                        </span>
                    </div>

                    {{-- Gap label when unbalanced --}}
                    @if($dcrmSv !== 0)
                    <span class="dcr-recon-eq-sign" style="color:var(--text-dim);">·</span>
                    <div class="dcr-recon-item">
                        <span class="dcr-recon-label">{{ $dcrmSv < 0 ? 'Shortage' : 'Surplus' }}</span>
                        <span class="dcr-recon-val"
                              style="font-size:13px;font-weight:800;
                                     color:{{ $dcrmSv < 0 ? 'var(--red)' : 'var(--amber)' }};">
                            {{ ($dcrmSv > 0 ? '+' : '') . number_format($dcrmSv) }}
                        </span>
                    </div>
                    @endif

                </div>
            </div>
            @endif

            {{-- ── 3-column body ── --}}
            <div class="dcr-modal-body">
                <div class="dcr-detail" style="border-top:none;border-bottom:none;">
                    <div class="dcr-detail-grid">

                        {{-- Column 1: Revenue by channel --}}
                        <div>
                            <div class="dcr-detail-col-title">Revenue by Channel</div>
                            @foreach(array_filter([
                                ['Cash',          $dcrmSess->total_sales_cash ?? 0,          true,           'var(--accent)'],
                                ['Mobile Money',  $dcrmSess->total_sales_momo ?? 0,          true,           'var(--accent)'],
                                ['Card',          $dcrmSess->total_sales_card ?? 0,          $dcrmShowCard,  'var(--accent)'],
                                ['Bank Transfer', $dcrmSess->total_sales_bank_transfer ?? 0, $dcrmShowBank,  'var(--accent)'],
                                ['Credit',        $dcrmSess->total_sales_credit ?? 0,        true,           'var(--amber)'],
                            ], fn($r) => $r[2]) as [$ch, $cv, , $cc])
                                <div class="dcr-detail-line">
                                    <span class="dcr-detail-lbl">{{ $ch }}</span>
                                    <span class="dcr-detail-val"
                                          style="color:{{ $cv > 0 ? $cc : 'var(--text-dim)' }};">
                                        {{ number_format($cv) }}
                                    </span>
                                </div>
                            @endforeach
                            <div class="dcr-detail-line"
                                 style="border-top:1.5px solid var(--border);margin-top:4px;">
                                <span style="font-size:11px;font-weight:700;color:var(--text);">Total</span>
                                <span class="dcr-detail-val" style="color:var(--accent);">
                                    {{ number_format($dcrmSess->total_sales ?? 0) }}
                                </span>
                            </div>
                        </div>

                        {{-- Column 2: Expenses + Withdrawals --}}
                        <div>
                            <div class="dcr-detail-col-title">Expenses</div>
                            @forelse($dcrmSess->expenses->whereNull('deleted_at') as $exp)
                                <div class="dcr-detail-line">
                                    <span class="dcr-detail-lbl">
                                        {{ $exp->category->name ?? '—' }}
                                        @if($exp->description)
                                            <span style="color:var(--text-dim);">
                                                — {{ \Illuminate\Support\Str::limit($exp->description, 22) }}
                                            </span>
                                        @endif
                                    </span>
                                    <span class="dcr-detail-val" style="color:var(--red);">
                                        {{ number_format($exp->amount) }}
                                    </span>
                                </div>
                            @empty
                                <div style="font-size:11px;color:var(--text-dim);padding:4px 0;">None</div>
                            @endforelse
                            @if($dcrmSess->expenses->whereNull('deleted_at')->count())
                                <div class="dcr-detail-line"
                                     style="border-top:1.5px solid var(--border);margin-top:4px;">
                                    <span style="font-size:11px;font-weight:700;color:var(--text);">Total</span>
                                    <span class="dcr-detail-val" style="color:var(--red);">
                                        {{ number_format($dcrmSess->expenses->whereNull('deleted_at')->sum('amount')) }}
                                    </span>
                                </div>
                            @endif

                            <div class="dcr-detail-col-title" style="margin-top:18px;">Owner Withdrawals</div>
                            @forelse($dcrmSess->ownerWithdrawals as $wd)
                                <div class="dcr-detail-line">
                                    <span class="dcr-detail-lbl">
                                        {{ \Illuminate\Support\Str::limit($wd->reason ?? '—', 22) }}
                                        <span style="font-size:10px;color:var(--text-dim);">
                                            ({{ ucfirst($wd->method ?? 'Cash') }})
                                        </span>
                                    </span>
                                    <span class="dcr-detail-val" style="color:var(--amber);">
                                        {{ number_format($wd->amount) }}
                                    </span>
                                </div>
                            @empty
                                <div style="font-size:11px;color:var(--text-dim);padding:4px 0;">None</div>
                            @endforelse
                        </div>

                        {{-- Column 3: Cash Reconciliation --}}
                        <div>
                            <div class="dcr-detail-col-title">Cash Reconciliation</div>
                            @php
                                $dcrmReconRows = [
                                    ['Opening',  $dcrmSess->opening_balance     ?? null, 'var(--text-dim)', false],
                                    ['Expected', $dcrmSess->expected_cash       ?? null, 'var(--text)',     false],
                                    ['Counted',  $dcrmSess->actual_cash_counted ?? null, 'var(--text)',     false],
                                    ['Variance', $dcrmSv, $dcrmSv < 0 ? 'var(--red)' : ($dcrmSv > 0 ? 'var(--amber)' : 'var(--text-dim)'), $dcrmSv !== 0],
                                    ['Banked',   $dcrmSess->total_bank_deposits ?? 0,    'var(--accent)',   false],
                                    ['Retained', $dcrmSess->cash_retained       ?? 0,    'var(--text)',     false],
                                ];
                            @endphp
                            @foreach($dcrmReconRows as [$rl, $rv, $rc, $isAlert])
                                @if($isAlert)
                                    <div class="dcr-variance-alert"
                                         style="background:{{ $dcrmSv < 0 ? 'var(--red-dim)' : 'var(--amber-dim)' }};">
                                        <span style="font-weight:700;color:{{ $rc }};">{{ $rl }}</span>
                                        <span style="font-weight:700;font-family:var(--mono);font-size:13px;color:{{ $rc }};">
                                            {{ $rv !== null ? ($dcrmSv > 0 ? '+' : '') . number_format($rv) : '—' }}
                                            <span style="font-size:10px;font-weight:500;"> RWF</span>
                                        </span>
                                    </div>
                                @else
                                    <div class="dcr-detail-line">
                                        <span class="dcr-detail-lbl">{{ $rl }}</span>
                                        <span class="dcr-detail-val" style="color:{{ $rc }};">
                                            {{ $rv !== null ? number_format($rv) : '—' }}
                                            @if($rv !== null)<span style="font-size:10px;font-weight:400;color:var(--text-dim);"> RWF</span>@endif
                                        </span>
                                    </div>
                                @endif
                            @endforeach

                            @if($dcrmSess->bankDeposits->isNotEmpty())
                                <div class="dcr-detail-col-title" style="margin-top:18px;">Bank Deposits</div>
                                @foreach($dcrmSess->bankDeposits as $dep)
                                    <div class="dcr-detail-line">
                                        <span class="dcr-detail-lbl">
                                            {{ $dep->deposited_at?->format('H:i') ?? '—' }}
                                            @if($dep->bank_reference)
                                                <span style="color:var(--text-dim);"> · {{ $dep->bank_reference }}</span>
                                            @endif
                                        </span>
                                        <span class="dcr-detail-val" style="color:var(--accent);">
                                            {{ number_format($dep->amount) }}
                                            <span style="font-size:10px;font-weight:400;color:var(--text-dim);"> RWF</span>
                                        </span>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                    </div>
                </div>
            </div>

            {{-- ── Action footer ── --}}
            <div class="dcr-modal-footer">
                @if($dcrmIsOpen)
                    <span class="dcr-footer-note">Session is still running — the shop manager must close it before you can lock it.</span>
                @elseif($dcrmIsLocked)
                    <span class="dcr-footer-note">
                        🔒 Permanently sealed · {{ $dcrmSess->lockedBy->name ?? '—' }}
                        · {{ $dcrmSess->locked_at?->format('d M Y H:i') }}
                    </span>
                @else
                    <span class="dcr-footer-note">
                        Closed by {{ $dcrmSess->closedBy->name ?? '—' }}
                        at {{ $dcrmSess->closed_at?->format('H:i') }}
                        @if($dcrmSv !== 0)
                            · <span style="color:{{ $dcrmSv < 0 ? 'var(--red)' : 'var(--amber)' }};font-weight:600;">
                                {{ $dcrmSv < 0 ? 'Shortage' : 'Surplus' }}: {{ number_format(abs($dcrmSv)) }} RWF
                            </span>
                        @endif
                    </span>
                    <div style="display:flex;gap:8px;flex-shrink:0;">
                        <button class="dcr-btn-reopen"
                                wire:click="reopenSession({{ $expandedSessionId }})"
                                wire:confirm="Reopen this session? The shop manager will be able to add or edit records again.">
                            Reopen
                        </button>
                        <button class="dcr-btn-lock"
                                wire:click="lockSession({{ $expandedSessionId }})"
                                wire:confirm="Lock this session permanently? This cannot be undone.">
                            🔒 Lock Session
                        </button>
                    </div>
                @endif
            </div>

        </div>
    </div>
    @endif

    @endif {{-- end sessions empty check --}}

</div>
</div>
