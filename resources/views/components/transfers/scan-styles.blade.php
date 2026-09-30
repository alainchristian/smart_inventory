{{-- Shared styles for the pack and receive scan screens (prefix tfs-), printed once. --}}
@once
<style>
.tfs-page { padding:0 0 24px }
.tfs-card { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);min-width:0;margin-bottom:16px }
.tfs-card-head { padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px }
.tfs-card-title { font-size:13px;font-weight:700;color:var(--text);margin:0 }
.tfs-card-sub { font-size:12px;color:var(--text-dim);margin-top:2px }
.tfs-pill { font:700 11px var(--mono);padding:2px 8px;border-radius:20px;background:var(--surface2);color:var(--text-sub);white-space:nowrap }

/* Scanner */
.tfs-scan { padding:16px 18px;border-left:3px solid var(--accent);border-radius:var(--r) }
.tfs-scan-label { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:8px }
.tfs-scan-row { display:flex;gap:8px }
.tfs-scan-input { flex:1;min-width:0;padding:11px 14px;border:1.5px solid var(--border);border-radius:10px;font:600 16px var(--mono);
                  background:var(--surface);color:var(--text);outline:none;transition:border-color var(--tr) }
.tfs-scan-input::placeholder { font-family:var(--font);font-weight:500;color:var(--text-dim) }
.tfs-scan-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.tfs-feedback { display:flex;align-items:center;gap:7px;margin-top:10px;font-size:13px;font-weight:600 }
.tfs-feedback.ok { color:var(--green) }
.tfs-feedback.bad { color:var(--red) }
.tfs-feedback.info { color:var(--accent) }

/* Tables */
.tfs-table { width:100%;border-collapse:collapse }
.tfs-table thead tr { border-bottom:2px solid var(--border) }
.tfs-table th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.tfs-table td { padding:11px 16px;font-size:13px;color:var(--text-sub);vertical-align:middle;white-space:nowrap }
.tfs-table tbody tr { border-bottom:1px solid var(--border) }
.tfs-table tbody tr:last-child { border-bottom:none }
.tfs-table tr.done td { color:var(--text-dim) }
.tfs-table tr.flag { background:var(--red-dim) }
.tfs-r { text-align:right !important }
.tfs-prod { font-weight:600;color:var(--text) }
.tfs-table tr.done .tfs-prod { color:var(--text-sub) }
.tfs-mono { font-family:var(--mono);font-size:12px;color:var(--text-dim) }
.tfs-code { font:700 12px var(--mono);color:var(--accent) }
.tfs-n { font:700 13px var(--mono);color:var(--text) }
.tfs-bar { height:6px;border-radius:3px;background:var(--surface2);overflow:hidden;min-width:120px }
.tfs-bar > span { display:block;height:100%;border-radius:3px;background:var(--accent);transition:width var(--tr) }
.tfs-bar.full > span { background:var(--green) }
.tfs-state { display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700;padding:3px 8px;border-radius:6px;white-space:nowrap }
.tfs-act { padding:4px 10px;border-radius:7px;border:1.5px solid var(--border);background:var(--surface);font:600 12px var(--font);
           color:var(--text-sub);cursor:pointer;white-space:nowrap;transition:all var(--tr) }
.tfs-act:hover { border-color:var(--accent);color:var(--accent) }
.tfs-act.danger:hover { border-color:var(--red);color:var(--red) }
.tfs-act.on { background:var(--red-dim);border-color:var(--red);color:var(--red) }
.tfs-note-input { width:220px;padding:6px 9px;border:1.5px solid var(--red);border-radius:7px;font:13px var(--font);background:var(--surface);color:var(--text);outline:none }
.tfs-empty { padding:28px 18px;text-align:center;font-size:13px;color:var(--text-dim) }

/* Sticky action bar */
.tfs-actionbar { position:sticky;bottom:0;z-index:20;margin-top:4px;background:var(--surface);border-radius:var(--r) var(--r) 0 0;
                 box-shadow:0 -4px 16px rgba(26,31,54,.08), 0 0 0 1px var(--border);padding:12px 18px;
                 display:flex;align-items:center;gap:16px;flex-wrap:wrap }
.tfs-stats { display:flex;gap:22px;flex-wrap:wrap;flex:1;min-width:0 }
.tfs-stat-v { font:800 18px var(--mono);color:var(--text);line-height:1.1 }
.tfs-stat-l { font-size:11px;color:var(--text-dim);margin-top:2px;white-space:nowrap }
.tfs-actionbar-go { display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap }
.tfs-field-label { display:block;font-size:11px;font-weight:700;color:var(--text-sub);margin-bottom:4px }
.tfs-input { padding:9px 12px;border:1.5px solid var(--border);border-radius:9px;font:14px var(--font);background:var(--surface);color:var(--text);outline:none;width:240px }
.tfs-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.tfs-err { font-size:11px;color:var(--red);margin-top:4px }

/* Quantity / confirm sheets */
.tfs-qty { width:100%;padding:14px;border:1.5px solid var(--border);border-radius:12px;font:800 28px var(--mono);text-align:center;
           background:var(--surface);color:var(--text);outline:none;box-sizing:border-box }
.tfs-qty:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.tfs-sheet-lead { font-size:13px;color:var(--text-sub);margin:0 0 14px;line-height:1.5 }
.tfs-sum { display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px }
.tfs-sum > div { padding:10px;border-radius:10px;box-shadow:0 0 0 1px var(--border);text-align:center }
.tfs-list { margin:0;padding:0;list-style:none;font-size:13px;color:var(--text-sub) }
.tfs-list li { display:flex;justify-content:space-between;gap:12px;padding:7px 0;border-top:1px solid var(--border) }
.tfs-x { width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;border:none;border-radius:8px;background:var(--surface2);color:var(--text-sub);cursor:pointer }
kbd.tfs-kbd { font:600 11px var(--mono);padding:1px 5px;border-radius:4px;box-shadow:0 0 0 1px var(--border);background:var(--surface) }

@media (max-width:640px) {
    .tfs-card-head, .tfs-scan { padding:12px }
    .tfs-actionbar { margin:4px calc(-1 * var(--m-s3)) 0;border-radius:0;padding:10px var(--m-s3) calc(10px + var(--m-safe-bottom));gap:10px }
    .tfs-stats { gap:14px }
    .tfs-stat-v { font-size:16px }
    .tfs-actionbar-go { width:100% }
    .tfs-actionbar-go > * { flex:1 }
    .tfs-actionbar-go .tf-btn { justify-content:center }
    .tfs-input { width:100%;font-size:16px }
    .tfs-note-input { font-size:16px }
}
@keyframes tfs-spin { to { transform:rotate(360deg) } }
</style>
@endonce
