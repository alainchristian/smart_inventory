{{--
    Shared styles for the transfer module (prefix tf-). Every transfer
    component includes <x-transfers.styles />; @once prints it a single time
    per page. Page-specific CSS stays in each page's own <style> block.
--}}
@once
<style>
/* ── Page header ─────────────────────────────────────────────── */
.tf-head        { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:24px;flex-wrap:wrap }
.tf-head-main   { display:flex;align-items:flex-start;gap:12px;min-width:0 }
.tf-back        { width:34px;height:34px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;
                  border-radius:var(--rsm);background:var(--surface);color:var(--text-sub);text-decoration:none;
                  box-shadow:var(--shadow-card);transition:all var(--tr);margin-top:1px }
.tf-back:hover  { color:var(--accent);box-shadow:var(--shadow-card-hover) }
.tf-title       { font-size:22px;font-weight:800;color:var(--text);margin:0 0 4px;line-height:1.25;letter-spacing:-.2px }
.tf-title-mono  { font-family:var(--mono);letter-spacing:-.5px }
.tf-sub         { font-size:13px;color:var(--text-dim);margin:0;display:flex;align-items:center;gap:8px;flex-wrap:wrap }
.tf-head-actions{ display:flex;gap:8px;align-items:center;flex-wrap:wrap }

/* ── Buttons ─────────────────────────────────────────────────── */
.tf-btn         { padding:9px 16px;border-radius:var(--rsm);font-size:13px;font-weight:600;cursor:pointer;
                  font-family:var(--font);transition:all var(--tr);display:inline-flex;align-items:center;
                  gap:6px;white-space:nowrap;text-decoration:none;line-height:1.2;border:none }
.tf-btn-primary { background:var(--accent);color:#fff;box-shadow:0 3px 10px rgba(59,111,212,.25) }
.tf-btn-primary:hover { opacity:.88 }
.tf-btn-primary:disabled { opacity:.5;cursor:not-allowed }
.tf-btn-ghost   { background:var(--surface);color:var(--text-sub);border:1px solid var(--border) }
.tf-btn-ghost:hover { background:var(--surface2);color:var(--text) }
.tf-btn-danger  { background:var(--red-dim);color:var(--red);border:1px solid var(--red) }
.tf-btn-danger:hover { background:var(--red);color:#fff }
.tf-btn-sm      { padding:5px 11px;font-size:12px }

/* ── Status badge ────────────────────────────────────────────── */
.tf-badge       { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:3px 9px;
                  border-radius:6px;white-space:nowrap;line-height:1.4;
                  background:var(--tf-tone-dim);color:var(--tf-tone) }
.tf-badge-dot   { width:6px;height:6px;border-radius:50%;flex-shrink:0;background:currentColor }
.tf-tone-amber   { --tf-tone:var(--amber);  --tf-tone-dim:var(--amber-dim) }
.tf-tone-accent  { --tf-tone:var(--accent); --tf-tone-dim:var(--accent-dim) }
.tf-tone-red     { --tf-tone:var(--red);    --tf-tone-dim:var(--red-dim) }
.tf-tone-violet  { --tf-tone:var(--violet); --tf-tone-dim:var(--violet-dim) }
.tf-tone-pink    { --tf-tone:var(--pink);   --tf-tone-dim:var(--pink-dim) }
.tf-tone-green   { --tf-tone:var(--green);  --tf-tone-dim:var(--green-dim) }
.tf-tone-text-dim{ --tf-tone:var(--text-dim);--tf-tone-dim:var(--surface2) }

/* ── Route: Warehouse → Shop (never clipped) ─────────────────── */
.tf-route       { display:inline-flex;align-items:center;gap:6px;white-space:nowrap;font-size:13px;color:var(--text-sub) }
.tf-route-node  { display:inline-flex;align-items:center;gap:5px }
.tf-route-node svg { color:var(--text-dim);flex-shrink:0 }
.tf-route-to    { font-weight:600;color:var(--text) }
.tf-route-arrow { color:var(--text-dim);flex-shrink:0 }

/* ── Timeline: Requested → … → Received ──────────────────────── */
.tf-steps       { display:grid;grid-template-columns:repeat(var(--tf-steps,6),minmax(0,1fr));list-style:none;margin:0;padding:0 }
.tf-step        { position:relative;padding:0 8px;text-align:center;min-width:0 }
.tf-step::before{ content:'';position:absolute;top:11px;left:-50%;right:50%;height:2px;background:var(--border) }
.tf-step:first-child::before { display:none }
.tf-step.done::before, .tf-step.current::before { background:var(--green) }
.tf-step.stopped::before { background:var(--red) }
.tf-step-dot    { position:relative;z-index:1;width:24px;height:24px;margin:0 auto 8px;border-radius:50%;
                  display:flex;align-items:center;justify-content:center;
                  background:var(--surface);border:2px solid var(--border);color:#fff }
.tf-step.done .tf-step-dot    { background:var(--green);border-color:var(--green) }
.tf-step.current .tf-step-dot { border-color:var(--accent);box-shadow:0 0 0 4px var(--accent-dim) }
.tf-step.current .tf-step-dot::after { content:'';width:8px;height:8px;border-radius:50%;background:var(--accent) }
.tf-step.stopped .tf-step-dot { background:var(--red);border-color:var(--red) }
.tf-step-label  { font-size:12px;font-weight:700;color:var(--text-dim) }
.tf-step.done .tf-step-label, .tf-step.current .tf-step-label { color:var(--text) }
.tf-step.stopped .tf-step-label { color:var(--red) }
.tf-step-who    { font-size:12px;color:var(--text-sub);margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis }
.tf-step-when   { font-size:11px;color:var(--text-dim);margin-top:1px;font-family:var(--mono);white-space:nowrap }

/* ── Phones ──────────────────────────────────────────────────── */
@media (max-width:640px) {
    .tf-head        { margin-bottom:16px;gap:12px }
    .tf-title       { font-size:var(--m-fs-title) }
    .tf-head-actions{ width:100% }
    .tf-head-actions > * { flex:1;justify-content:center }
    /* Timeline turns vertical: dot on the left, text beside it */
    .tf-steps       { grid-template-columns:1fr }
    .tf-step        { text-align:left;padding:0 0 14px 34px }
    .tf-step:last-child { padding-bottom:0 }
    .tf-step::before{ display:none }
    /* each step draws the line down to the next dot, coloured by the next step */
    .tf-step:not(:last-child)::after { content:'';position:absolute;left:11px;top:26px;bottom:2px;width:2px;background:var(--border) }
    .tf-step.next-done::after    { background:var(--green) }
    .tf-step.next-stopped::after { background:var(--red) }
    .tf-step-dot    { position:absolute;left:0;top:0;margin:0 }
    .tf-step-who    { white-space:normal }
}
</style>
@endonce
