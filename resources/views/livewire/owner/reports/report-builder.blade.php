<div style="font-family:var(--font)">
<style>
/* ── Report Builder (rb-) ─────────────────────────────────────── */

/* Template modal */
.rb-modal-overlay  { position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:900;
                     display:flex;align-items:center;justify-content:center;padding:20px }
.rb-modal          { background:var(--surface);border-radius:16px;
                     box-shadow:0 20px 60px rgba(0,0,0,.25);
                     width:100%;max-width:860px;max-height:90vh;
                     display:flex;flex-direction:column;overflow:hidden }
.rb-modal-header   { display:flex;align-items:center;justify-content:space-between;
                     padding:20px 24px;border-bottom:1px solid var(--border);flex-shrink:0 }
.rb-modal-title    { font-size:18px;font-weight:800;color:var(--text);letter-spacing:-.4px }
.rb-modal-sub      { font-size:12px;color:var(--text-dim);margin-top:3px }
.rb-modal-close    { width:32px;height:32px;border:none;border-radius:8px;
                     background:var(--surface2);color:var(--text-dim);cursor:pointer;
                     font-size:18px;display:flex;align-items:center;justify-content:center;
                     transition:background var(--tr) }
.rb-modal-close:hover { background:var(--border);color:var(--text) }
.rb-modal-body     { overflow-y:auto;padding:20px 24px;flex:1 }
.rb-tmpl-grid      { display:grid;grid-template-columns:repeat(3,1fr);gap:12px }
.rb-tmpl-card      { background:var(--bg);border-radius:var(--r);overflow:hidden;
                     cursor:pointer;border:2px solid transparent;
                     transition:border-color var(--tr),box-shadow var(--tr);
                     display:flex;flex-direction:column }
.rb-tmpl-card:hover { border-color:var(--accent);box-shadow:var(--shadow-card) }
.rb-tmpl-top       { padding:16px 16px 12px;display:flex;align-items:flex-start;gap:10px }
.rb-tmpl-icon      { width:40px;height:40px;border-radius:10px;flex-shrink:0;
                     display:flex;align-items:center;justify-content:center }
.rb-tmpl-name      { font-size:13px;font-weight:700;color:var(--text);line-height:1.3 }
.rb-tmpl-desc      { font-size:11.5px;color:var(--text-dim);margin-top:3px;
                     display:-webkit-box;-webkit-line-clamp:2;
                     -webkit-box-orient:vertical;overflow:hidden }
.rb-tmpl-chips     { display:flex;flex-wrap:wrap;gap:4px;padding:0 16px 12px }
.rb-tmpl-chip      { font-size:10px;font-weight:600;padding:2px 7px;border-radius:5px;
                     background:var(--surface);color:var(--text-dim) }
.rb-tmpl-footer    { margin-top:auto;padding:10px 16px;border-top:1px solid var(--border);
                     font-size:12px;font-weight:700;color:var(--accent);
                     display:flex;align-items:center;gap:4px }
.rb-scratch-card   { background:var(--bg);border-radius:var(--r);border:2px dashed var(--border);
                     cursor:pointer;display:flex;flex-direction:column;
                     align-items:center;justify-content:center;gap:8px;
                     padding:32px 16px;text-align:center;
                     transition:border-color var(--tr) }
.rb-scratch-card:hover { border-color:var(--accent) }
.rb-scratch-icon   { width:44px;height:44px;border-radius:12px;background:var(--surface);
                     display:flex;align-items:center;justify-content:center;
                     color:var(--text-dim);font-size:24px }
.rb-scratch-name   { font-size:13px;font-weight:700;color:var(--text) }
.rb-scratch-desc   { font-size:11.5px;color:var(--text-dim) }

/* Sticky top bar */
.rb-top-bar        { position:sticky;top:0;z-index:100;background:var(--surface);
                     box-shadow:var(--shadow-card);border-radius:var(--r);
                     padding:14px 20px;margin-bottom:20px }
.rb-top-inner      { display:flex;align-items:flex-end;gap:14px;flex-wrap:wrap }
.rb-top-field      { display:flex;flex-direction:column;gap:4px;min-width:0 }
.rb-top-label      { font-size:10.5px;font-weight:700;text-transform:uppercase;
                     letter-spacing:.5px;color:var(--text-dim) }
.rb-top-name       { flex:1;min-width:200px }
.rb-name-input     { width:100%;border:1.5px solid var(--border);border-radius:8px;
                     padding:7px 11px;font-size:14px;font-weight:600;
                     color:var(--text);font-family:var(--font);background:var(--surface);
                     outline:none;transition:border-color var(--tr) }
.rb-name-input:focus { border-color:var(--accent) }
.rb-name-input::placeholder { color:var(--text-dim);font-weight:400 }
.rb-top-select     { border:1.5px solid var(--border);border-radius:8px;padding:7px 11px;
                     font-size:13px;color:var(--text);font-family:var(--font);
                     background:var(--surface);outline:none;cursor:pointer;
                     transition:border-color var(--tr) }
.rb-top-select:focus { border-color:var(--accent) }
.rb-date-input     { border:1.5px solid var(--border);border-radius:8px;padding:7px 10px;
                     font-size:12px;color:var(--text);font-family:var(--font);
                     background:var(--surface);outline:none;transition:border-color var(--tr) }
.rb-date-input:focus { border-color:var(--accent) }
.rb-share-row      { display:flex;align-items:center;gap:7px;cursor:pointer;
                     padding:7px 0;white-space:nowrap }
.rb-share-check    { width:16px;height:16px;accent-color:var(--accent);cursor:pointer }
.rb-share-label    { font-size:12px;font-weight:600;color:var(--text-sub) }
.rb-top-errs       { flex-basis:100%;margin-top:6px }
.rb-top-err        { font-size:12px;color:var(--red);font-weight:600 }

/* Workspace */
.rb-workspace      { display:flex;gap:16px;align-items:flex-start }

/* Canvas zone */
.rb-canvas-zone    { flex:1;min-width:0 }
.rb-canvas-hdr     { display:flex;align-items:center;justify-content:space-between;
                     margin-bottom:12px }
.rb-canvas-label   { font-size:11px;font-weight:700;text-transform:uppercase;
                     letter-spacing:.6px;color:var(--text-dim) }
.rb-canvas-count   { font-size:11.5px;font-weight:600;color:var(--text-dim);
                     background:var(--surface2);padding:3px 10px;border-radius:20px }

/* Canvas empty state */
.rb-canvas-empty   { background:var(--surface);border-radius:var(--r);
                     box-shadow:var(--shadow-card);padding:56px 24px;text-align:center }
.rb-canvas-empty-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.rb-canvas-empty-sub   { font-size:13px;color:var(--text-dim) }

/* Canvas list (sortable container) */
.rb-canvas-list    { display:flex;flex-direction:column;gap:10px }

/* Block item */
.rb-block-item     { background:var(--surface);border-radius:var(--r);
                     box-shadow:var(--shadow-card);overflow:visible;
                     transition:box-shadow var(--tr) }
.rb-block-item:hover { box-shadow:var(--shadow-card-hover) }
.rb-block-ghost    { opacity:.35;background:var(--accent-dim);border:2px dashed var(--accent);
                     border-radius:var(--r) }
.rb-block-dragging { box-shadow:0 10px 30px rgba(0,0,0,.18);transform:rotate(.8deg);
                     z-index:50 }

/* Block header */
.rb-block-hdr      { display:flex;align-items:center;gap:8px;
                     padding:11px 12px 11px 10px;
                     border-bottom:1px solid var(--border) }
.rb-block-handle   { color:var(--text-dim);cursor:grab;padding:2px 4px;
                     flex-shrink:0;transition:color var(--tr);line-height:1;
                     display:flex;align-items:center }
.rb-block-handle:active { cursor:grabbing }
.rb-block-handle:hover  { color:var(--text-sub) }
.rb-block-title    { flex:1;min-width:0;border:none;background:transparent;
                     font-size:13px;font-weight:600;color:var(--text);
                     font-family:var(--font);outline:none;padding:3px 6px;
                     border-radius:5px;transition:background var(--tr) }
.rb-block-title:hover  { background:var(--surface2) }
.rb-block-title:focus  { background:var(--surface2);outline:1.5px solid var(--accent) }

/* Block controls */
.rb-block-ctls     { display:flex;align-items:center;gap:3px;flex-shrink:0 }
.rb-width-grp      { display:flex;gap:1px;background:var(--surface2);
                     border-radius:6px;padding:2px;overflow:hidden }
.rb-width-btn      { padding:3px 8px;font-size:10.5px;font-weight:700;border:none;
                     border-radius:4px;cursor:pointer;background:transparent;
                     color:var(--text-dim);font-family:var(--font);
                     transition:all var(--tr) }
.rb-width-btn.active { background:var(--surface);color:var(--accent);
                        box-shadow:0 1px 3px rgba(0,0,0,.08) }
.rb-viz-grp        { display:flex;gap:1px;background:var(--surface2);
                     border-radius:6px;padding:2px;overflow:hidden }
.rb-viz-btn        { width:28px;height:26px;border:none;border-radius:4px;
                     background:transparent;color:var(--text-dim);cursor:pointer;
                     display:flex;align-items:center;justify-content:center;
                     transition:all var(--tr) }
.rb-viz-btn.active { background:var(--surface);color:var(--accent) }
.rb-viz-btn:hover  { background:var(--surface) }
.rb-icon-btn       { width:28px;height:28px;border:none;border-radius:7px;
                     background:transparent;color:var(--text-dim);cursor:pointer;
                     display:flex;align-items:center;justify-content:center;
                     transition:background var(--tr) }
.rb-icon-btn:hover { background:var(--surface2) }
.rb-icon-btn.active { background:var(--accent-dim);color:var(--accent) }
.rb-remove-btn     { color:var(--red) }
.rb-remove-btn:hover { background:var(--red-dim) }

/* Block meta bar */
.rb-block-meta     { display:flex;align-items:center;gap:6px;
                     padding:7px 12px 7px 14px;background:var(--bg) }
.rb-domain-dot     { width:6px;height:6px;border-radius:50%;flex-shrink:0 }
.rb-block-meta-txt { font-size:11px;color:var(--text-dim);flex:1;min-width:0;
                     white-space:nowrap;overflow:hidden;text-overflow:ellipsis }

/* Inline settings panel */
.rb-settings-panel { background:var(--bg);border-top:1px solid var(--border);padding:16px }
.rb-settings-grid  { display:grid;grid-template-columns:1fr 1fr;gap:12px }
.rb-settings-grid.full { grid-template-columns:1fr }
.rb-sf             { display:flex;flex-direction:column;gap:5px }
.rb-sf label       { font-size:10.5px;font-weight:700;text-transform:uppercase;
                     letter-spacing:.4px;color:var(--text-dim) }
.rb-sf-select,.rb-sf-input { width:100%;border:1.5px solid var(--border);border-radius:7px;
                              padding:6px 10px;font-size:12px;color:var(--text);
                              background:var(--surface);font-family:var(--font);
                              outline:none;transition:border-color var(--tr) }
.rb-sf-select:focus,.rb-sf-input:focus { border-color:var(--accent) }
.rb-sf-toggle      { display:flex;align-items:center;gap:7px;cursor:pointer;margin-top:2px }
.rb-sf-toggle input { accent-color:var(--accent);cursor:pointer }
.rb-sf-toggle span { font-size:12px;font-weight:600;color:var(--text-sub) }
.rb-threshold-row  { display:grid;grid-template-columns:1fr 1fr;gap:8px }

/* Text block editor */
.rb-text-editor    { width:100%;min-height:80px;resize:vertical;padding:10px 12px;
                     border:none;background:var(--bg);font-size:13px;
                     color:var(--text);font-family:var(--font);outline:none;
                     border-top:1px solid var(--border) }
.rb-text-editor:focus { background:var(--surface) }

/* Metric palette sidebar */
.rb-palette        { width:300px;flex-shrink:0;position:sticky;top:16px;
                     max-height:calc(100vh - 80px);display:flex;flex-direction:column;
                     background:var(--surface);border-radius:var(--r);
                     box-shadow:var(--shadow-card);overflow:hidden }
.rb-pal-hdr        { padding:14px 16px 10px;flex-shrink:0;
                     border-bottom:1px solid var(--border) }
.rb-pal-title      { font-size:13px;font-weight:700;color:var(--text);margin-bottom:8px }
.rb-pal-search-wrap { position:relative;display:flex;align-items:center }
.rb-pal-search-ico  { position:absolute;left:9px;color:var(--text-dim);pointer-events:none }
.rb-pal-search     { width:100%;border:1.5px solid var(--border);border-radius:8px;
                     padding:6px 10px 6px 30px;font-size:12.5px;color:var(--text);
                     background:var(--bg);font-family:var(--font);outline:none;
                     transition:border-color var(--tr) }
.rb-pal-search:focus { border-color:var(--accent);background:var(--surface) }
.rb-pal-tabs       { display:flex;gap:0;overflow-x:auto;scrollbar-width:none;
                     border-bottom:1px solid var(--border);flex-shrink:0 }
.rb-pal-tabs::-webkit-scrollbar { display:none }
.rb-pal-tab        { flex-shrink:0;padding:8px 12px;border:none;border-bottom:2px solid transparent;
                     background:transparent;color:var(--text-dim);font-size:11px;font-weight:600;
                     font-family:var(--font);cursor:pointer;transition:all var(--tr);
                     display:flex;align-items:center;gap:5px;white-space:nowrap }
.rb-pal-tab.active { color:var(--accent);border-bottom-color:var(--accent) }
.rb-pal-tab:hover:not(.active) { color:var(--text);background:var(--surface2) }
.rb-pal-body       { overflow-y:auto;flex:1;padding:8px }
.rb-pal-group-lbl  { font-size:10px;font-weight:700;text-transform:uppercase;
                     letter-spacing:.6px;color:var(--text-dim);
                     padding:10px 8px 4px;display:flex;align-items:center;gap:6px }
.rb-pal-dot        { width:5px;height:5px;border-radius:50%;flex-shrink:0 }
.rb-metric-row     { display:flex;align-items:center;gap:8px;padding:8px;
                     border-radius:8px;transition:background var(--tr);cursor:default }
.rb-metric-row:hover { background:var(--surface2) }
.rb-metric-info    { flex:1;min-width:0 }
.rb-metric-name    { font-size:12.5px;font-weight:600;color:var(--text);line-height:1.2 }
.rb-metric-desc    { font-size:11px;color:var(--text-dim);margin-top:2px;
                     white-space:nowrap;overflow:hidden;text-overflow:ellipsis }
.rb-add-btn        { flex-shrink:0;padding:4px 10px;border:1.5px solid var(--accent);
                     border-radius:6px;background:transparent;color:var(--accent);
                     font-size:11px;font-weight:700;font-family:var(--font);
                     cursor:pointer;transition:all var(--tr);white-space:nowrap }
.rb-add-btn:hover  { background:var(--accent);color:#fff }
.rb-added-badge    { flex-shrink:0;padding:4px 10px;border-radius:6px;
                     background:var(--green-dim);color:var(--green);
                     font-size:11px;font-weight:700;white-space:nowrap }

/* Footer save bar */
.rb-footer         { display:flex;align-items:center;justify-content:space-between;
                     gap:16px;margin-top:20px;background:var(--surface);
                     border-radius:var(--r);box-shadow:var(--shadow-card);
                     padding:16px 20px;flex-wrap:wrap }
.rb-footer-left    { display:flex;align-items:center;gap:12px;flex-wrap:wrap }
.rb-block-count    { font-size:13px;font-weight:600;color:var(--text-dim) }
.rb-block-count strong { color:var(--text);font-weight:800 }
.rb-schedule-toggle { display:flex;align-items:center;gap:6px;border:none;
                      background:transparent;color:var(--text-dim);font-size:12px;
                      font-weight:600;font-family:var(--font);cursor:pointer;
                      padding:5px 10px;border-radius:7px;transition:background var(--tr) }
.rb-schedule-toggle:hover { background:var(--surface2) }
.rb-schedule-section { flex-basis:100%;padding-top:14px;border-top:1px solid var(--border);
                        margin-top:8px;display:grid;grid-template-columns:1fr 1fr;gap:12px }
.rb-save-btn       { padding:10px 24px;background:var(--accent);color:#fff;
                     border:none;border-radius:9px;font-size:13px;font-weight:700;
                     font-family:var(--font);cursor:pointer;
                     transition:opacity var(--tr);white-space:nowrap;
                     display:flex;align-items:center;gap:6px }
.rb-save-btn:hover { opacity:.88 }
.rb-save-btn:disabled { opacity:.5;cursor:not-allowed }

/* Validation errors */
.rb-errs           { background:var(--red-dim);border:1px solid var(--red);border-radius:9px;
                     padding:12px 16px;margin-bottom:16px }
.rb-errs li        { font-size:13px;color:var(--red);font-weight:600;margin-bottom:3px }
.rb-errs li:last-child { margin-bottom:0 }

/* Responsive */
@media(max-width:900px) {
    .rb-workspace   { flex-direction:column }
    .rb-palette     { width:100%;position:static;max-height:none }
    .rb-pal-body    { max-height:300px }
    .rb-tmpl-grid   { grid-template-columns:1fr 1fr }
    .rb-schedule-section { grid-template-columns:1fr }
}
@media(max-width:640px) {
    .rb-top-inner   { gap:10px }
    .rb-top-name    { flex-basis:100% }
    .rb-tmpl-grid   { grid-template-columns:1fr }
    .rb-block-ctls  { flex-wrap:wrap }
    .rb-settings-grid { grid-template-columns:1fr }
    .rb-footer      { flex-direction:column;align-items:stretch }
    .rb-footer-left { justify-content:space-between }
    .rb-save-btn    { width:100%;justify-content:center }
}
</style>

@php
$domainColors = [
    'sales'         => 'accent',
    'inventory'     => 'green',
    'replenishment' => 'amber',
    'loss'          => 'red',
    'transfers'     => 'violet',
    'operations'    => 'text-sub',
    'finance'       => 'green',
    'content'       => 'text-dim',
];
$domainDomains = [
    'all'           => ['label' => 'All',        'icon' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>'],
    'sales'         => ['label' => 'Sales',       'icon' => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>'],
    'inventory'     => ['label' => 'Inventory',   'icon' => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>'],
    'replenishment' => ['label' => 'Restock',     'icon' => '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>'],
    'loss'          => ['label' => 'Loss',        'icon' => '<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>'],
    'transfers'     => ['label' => 'Transfers',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>'],
    'operations'    => ['label' => 'Ops',         'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>'],
    'finance'       => ['label' => 'Finance',     'icon' => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>'],
    'content'       => ['label' => 'Text',        'icon' => '<path d="M4 6h16M4 12h16M4 18h7"/>'],
];
$vizIcons = [
    'kpi_card'   => '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>',
    'table'      => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>',
    'bar_chart'  => '<rect x="18" y="3" width="4" height="18" rx="1"/><rect x="10" y="8" width="4" height="13" rx="1"/><rect x="2" y="13" width="4" height="8" rx="1"/>',
    'line_chart' => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
    'text'       => '<path d="M4 6h16M4 12h16M4 18h7"/>',
];
$vizLabels = [
    'kpi_card'   => 'KPI',
    'table'      => 'Table',
    'bar_chart'  => 'Bar',
    'line_chart' => 'Line',
    'text'       => 'Text',
];
@endphp

{{-- ═══ TEMPLATE MODAL ══════════════════════════════════════════════════ --}}
@if($showTemplateModal && !$editingReportId)
<div class="rb-modal-overlay" wire:click.self="dismissTemplateModal">
    <div class="rb-modal">
        <div class="rb-modal-header">
            <div>
                <div class="rb-modal-title">Choose a starting point</div>
                <div class="rb-modal-sub">Pick a template or start with a blank canvas</div>
            </div>
            <button class="rb-modal-close" wire:click="dismissTemplateModal">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="rb-modal-body">
            <div class="rb-tmpl-grid">
                @foreach($tmplList as $t)
                <div class="rb-tmpl-card" wire:click="loadTemplate('{{ $t['key'] }}')"
                     @if(count($canvas) > 0) wire:confirm="Load '{{ $t['name'] }}'? Your current blocks will be replaced." @endif>
                    <div class="rb-tmpl-top">
                        <div class="rb-tmpl-icon" style="background:var({{ $t['color'] }}-dim);color:var({{ $t['color'] }})">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                {!! $t['icon'] !!}
                            </svg>
                        </div>
                        <div>
                            <div class="rb-tmpl-name">{{ $t['name'] }}</div>
                            <div class="rb-tmpl-desc">{{ $t['description'] }}</div>
                        </div>
                    </div>
                    <div class="rb-tmpl-chips">
                        @foreach($t['metrics_preview'] as $m)
                            <span class="rb-tmpl-chip">{{ $m }}</span>
                        @endforeach
                        <span class="rb-tmpl-chip" style="background:var({{ $t['color'] }}-dim);color:var({{ $t['color'] }})">{{ $t['block_count'] }} blocks</span>
                    </div>
                    <div class="rb-tmpl-footer">
                        Use Template
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </div>
                </div>
                @endforeach

                {{-- Start from Scratch --}}
                <div class="rb-scratch-card" wire:click="dismissTemplateModal">
                    <div class="rb-scratch-icon">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                    </div>
                    <div class="rb-scratch-name">Start from Scratch</div>
                    <div class="rb-scratch-desc">Build your own report block by block</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ═══ STICKY TOP BAR ═══════════════════════════════════════════════════ --}}
<div class="rb-top-bar">
    <div class="rb-top-inner">
        {{-- Report name --}}
        <div class="rb-top-field rb-top-name">
            <label class="rb-top-label">Report Name</label>
            <input wire:model.blur="reportName"
                   class="rb-name-input"
                   placeholder="Give your report a name…"
                   maxlength="120">
            @error('reportName')
                <span class="rb-top-err">{{ $message }}</span>
            @enderror
        </div>

        {{-- Date range --}}
        <div class="rb-top-field">
            <label class="rb-top-label">Date Range</label>
            <select wire:model.live="dateRange" class="rb-top-select">
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="last_month">Last Month</option>
                <option value="quarter">This Quarter</option>
                <option value="year">This Year</option>
                <option value="custom">Custom Range</option>
            </select>
        </div>

        {{-- Custom dates --}}
        @if($dateRange === 'custom')
        <div class="rb-top-field">
            <label class="rb-top-label">From</label>
            <input type="date" wire:model.live="dateFrom" class="rb-date-input">
        </div>
        <div class="rb-top-field">
            <label class="rb-top-label">To</label>
            <input type="date" wire:model.live="dateTo" class="rb-date-input">
        </div>
        @endif

        {{-- Location filter --}}
        <div class="rb-top-field">
            <label class="rb-top-label">Location</label>
            <select wire:model.live="locationFilter" class="rb-top-select">
                <option value="all">All Locations</option>
                @if($warehouses->count())
                    <optgroup label="Warehouses">
                        @foreach($warehouses as $wh)
                            <option value="warehouse:{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </optgroup>
                @endif
                @if($shops->count())
                    <optgroup label="Shops">
                        @foreach($shops as $sh)
                            <option value="shop:{{ $sh->id }}">{{ $sh->name }}</option>
                        @endforeach
                    </optgroup>
                @endif
            </select>
        </div>

        {{-- Comparison --}}
        <div class="rb-top-field">
            <label class="rb-top-label">Compare With</label>
            <select wire:model.live="comparisonMode" class="rb-top-select">
                <option value="none">No comparison</option>
                <option value="prior_period">Prior period</option>
                <option value="prior_year">Prior year</option>
            </select>
        </div>

        {{-- Share --}}
        <div class="rb-top-field" style="justify-content:flex-end">
            <label class="rb-top-label">&nbsp;</label>
            <label class="rb-share-row">
                <input type="checkbox" wire:model.live="isShared" class="rb-share-check">
                <span class="rb-share-label">Share with all owners</span>
            </label>
        </div>
    </div>
</div>

{{-- ═══ VALIDATION ERRORS ══════════════════════════════════════════════════ --}}
@if($errors->any())
<ul class="rb-errs">
    @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
    @endforeach
</ul>
@endif

{{-- ═══ WORKSPACE ════════════════════════════════════════════════════════ --}}
<div class="rb-workspace">

    {{-- ─── CANVAS ZONE ─── --}}
    <div class="rb-canvas-zone">
        <div class="rb-canvas-hdr">
            <span class="rb-canvas-label">Report Canvas</span>
            <span class="rb-canvas-count">{{ count($canvas) }} {{ Str::plural('block', count($canvas)) }}</span>
        </div>

        @if(empty($canvas))
        <div class="rb-canvas-empty">
            <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.2"
                 viewBox="0 0 24 24" style="color:var(--text-dim);margin:0 auto 12px;display:block">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <line x1="3" y1="9" x2="21" y2="9"/>
                <line x1="9" y1="21" x2="9" y2="9"/>
            </svg>
            <div class="rb-canvas-empty-title">Canvas is empty</div>
            <div class="rb-canvas-empty-sub">Add blocks from the palette →</div>
        </div>
        @else

        {{-- The Sortable container — NO wire:key on this div --}}
        <div id="rb-canvas-blocks" class="rb-canvas-list">
            @foreach($canvas as $block)
            @php
                $meta        = $flatCatalogue[$block['metric_id']] ?? null;
                $blockDomain = $meta['domain'] ?? 'content';
                $blockColor  = $domainColors[$blockDomain] ?? 'text-dim';
                $vizOptions  = $meta['viz_options'] ?? [$block['viz']];
                $isText      = $block['metric_id'] === 'text_block';
                $showThresh  = in_array($block['viz'] ?? '', ['kpi_card', 'scorecard']);
                $showSort    = in_array($block['viz'] ?? '', ['table', 'bar_chart']);
                $blockOpts   = $block['block_options'] ?? [];
            @endphp
            <div wire:key="rb-block-{{ $block['id'] }}"
                 class="rb-block-item"
                 data-block-id="{{ $block['id'] }}"
                 x-data="{ open: false }">

                {{-- Block header --}}
                <div class="rb-block-hdr">
                    {{-- Drag handle --}}
                    <div class="rb-block-handle">
                        <svg width="10" height="18" viewBox="0 0 10 18" fill="currentColor">
                            <circle cx="3" cy="3" r="1.5"/><circle cx="7" cy="3" r="1.5"/>
                            <circle cx="3" cy="9" r="1.5"/><circle cx="7" cy="9" r="1.5"/>
                            <circle cx="3" cy="15" r="1.5"/><circle cx="7" cy="15" r="1.5"/>
                        </svg>
                    </div>

                    {{-- Title --}}
                    <input class="rb-block-title"
                           value="{{ $block['title'] }}"
                           wire:change="updateBlockTitle('{{ $block['id'] }}', $event.target.value)"
                           placeholder="Block title">

                    {{-- Controls --}}
                    <div class="rb-block-ctls">
                        {{-- Width H/F --}}
                        <div class="rb-width-grp" title="Block width">
                            <button class="rb-width-btn {{ ($block['width'] ?? 'half') === 'half' ? 'active' : '' }}"
                                    wire:click="updateBlockWidth('{{ $block['id'] }}', 'half')">½</button>
                            <button class="rb-width-btn {{ ($block['width'] ?? 'half') === 'full' ? 'active' : '' }}"
                                    wire:click="updateBlockWidth('{{ $block['id'] }}', 'full')">Full</button>
                        </div>

                        {{-- Viz selector (only if multiple options) --}}
                        @if(count($vizOptions) > 1)
                        <div class="rb-viz-grp">
                            @foreach($vizOptions as $vizOpt)
                            <button class="rb-viz-btn {{ ($block['viz'] ?? '') === $vizOpt ? 'active' : '' }}"
                                    wire:click="updateBlockViz('{{ $block['id'] }}', '{{ $vizOpt }}')"
                                    title="{{ $vizLabels[$vizOpt] ?? $vizOpt }}">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    {!! $vizIcons[$vizOpt] ?? '' !!}
                                </svg>
                            </button>
                            @endforeach
                        </div>
                        @endif

                        {{-- Settings toggle --}}
                        @if(!$isText)
                        <button class="rb-icon-btn" @click="open = !open" :class="{ active: open }" title="Block settings">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
                            </svg>
                        </button>
                        @endif

                        {{-- Remove --}}
                        <button class="rb-icon-btn rb-remove-btn"
                                wire:click="removeBlock('{{ $block['id'] }}')"
                                wire:confirm="Remove this block?"
                                title="Remove block">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Domain + description meta bar --}}
                <div class="rb-block-meta">
                    <div class="rb-domain-dot" style="background:var(--{{ $blockColor }})"></div>
                    <span class="rb-block-meta-txt">
                        {{ ucfirst($blockDomain) }}
                        @if($meta)
                            &middot; {{ $meta['description'] }}
                        @endif
                    </span>
                </div>

                {{-- Text block editor --}}
                @if($isText)
                <textarea class="rb-text-editor"
                          placeholder="Write notes, context, or instructions for this report section…"
                          wire:change="updateBlockContent('{{ $block['id'] }}', $event.target.value)">{{ $block['content'] ?? '' }}</textarea>
                @endif

                {{-- Inline settings panel (Alpine-controlled, no server round-trip) --}}
                @if(!$isText)
                <div class="rb-settings-panel" x-show="open" x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="rb-settings-grid">
                        {{-- Location override --}}
                        <div class="rb-sf">
                            <label>Location Override</label>
                            <select class="rb-sf-select"
                                    wire:change="updateBlockOverride('{{ $block['id'] }}', 'location_filter_override', $event.target.value)">
                                <option value="">Inherit from report</option>
                                <option value="all" {{ ($block['location_filter_override'] ?? '') === 'all' ? 'selected' : '' }}>All Locations</option>
                                @foreach($warehouses as $wh)
                                    <option value="warehouse:{{ $wh->id }}"
                                            {{ ($block['location_filter_override'] ?? '') === 'warehouse:'.$wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }} (Warehouse)
                                    </option>
                                @endforeach
                                @foreach($shops as $sh)
                                    <option value="shop:{{ $sh->id }}"
                                            {{ ($block['location_filter_override'] ?? '') === 'shop:'.$sh->id ? 'selected' : '' }}>
                                        {{ $sh->name }} (Shop)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Date range override --}}
                        <div class="rb-sf">
                            <label>Date Range Override</label>
                            <select class="rb-sf-select"
                                    wire:change="updateBlockOverride('{{ $block['id'] }}', 'date_range_override', $event.target.value)">
                                @foreach(['' => 'Inherit from report', 'today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'last_month' => 'Last Month', 'last_30' => 'Last 30 Days', 'quarter' => 'This Quarter', 'year' => 'This Year'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ ($block['date_range_override'] ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- KPI thresholds --}}
                        @if($showThresh)
                        <div class="rb-sf">
                            <label>Warning Threshold</label>
                            <input type="number" class="rb-sf-input" placeholder="e.g. 5000"
                                   value="{{ $block['threshold_warning'] ?? '' }}"
                                   wire:change="updateBlockThreshold('{{ $block['id'] }}', 'threshold_warning', $event.target.value)">
                        </div>
                        <div class="rb-sf">
                            <label>Critical Threshold</label>
                            <input type="number" class="rb-sf-input" placeholder="e.g. 10000"
                                   value="{{ $block['threshold_critical'] ?? '' }}"
                                   wire:change="updateBlockThreshold('{{ $block['id'] }}', 'threshold_critical', $event.target.value)">
                        </div>
                        @endif

                        {{-- Sort + limit --}}
                        @if($showSort)
                        <div class="rb-sf">
                            <label>Sort By Column</label>
                            <input type="text" class="rb-sf-input" placeholder="e.g. revenue"
                                   value="{{ $blockOpts['sort_by'] ?? '' }}"
                                   wire:change="updateBlockOption('{{ $block['id'] }}', 'sort_by', $event.target.value)">
                        </div>
                        <div class="rb-sf">
                            <label>Sort Direction</label>
                            <select class="rb-sf-select"
                                    wire:change="updateBlockOption('{{ $block['id'] }}', 'sort_direction', $event.target.value)">
                                <option value="desc" {{ ($blockOpts['sort_direction'] ?? 'desc') === 'desc' ? 'selected' : '' }}>Descending</option>
                                <option value="asc"  {{ ($blockOpts['sort_direction'] ?? 'desc') === 'asc'  ? 'selected' : '' }}>Ascending</option>
                            </select>
                        </div>
                        <div class="rb-sf">
                            <label>Limit Rows</label>
                            <input type="number" min="1" max="100" class="rb-sf-input" placeholder="All rows"
                                   value="{{ $blockOpts['limit'] ?? '' }}"
                                   wire:change="updateBlockOption('{{ $block['id'] }}', 'limit', $event.target.value)">
                        </div>
                        @endif

                        {{-- Hide if zero --}}
                        <div class="rb-sf" style="grid-column:1/-1">
                            <label class="rb-sf-toggle">
                                <input type="checkbox"
                                       {{ ($block['show_if_nonzero'] ?? false) ? 'checked' : '' }}
                                       wire:change="updateBlockShowIfNonzero('{{ $block['id'] }}', $event.target.checked)">
                                <span>Hide this block when data is zero</span>
                            </label>
                        </div>
                    </div>
                </div>
                @endif

            </div>{{-- end rb-block-item --}}
            @endforeach
        </div>{{-- end rb-canvas-blocks --}}

        @endif{{-- end canvas not empty --}}
    </div>{{-- end canvas zone --}}

    {{-- ─── METRIC PALETTE ─── --}}
    <div class="rb-palette">
        <div class="rb-pal-hdr">
            <div class="rb-pal-title">Add Blocks</div>
            <div class="rb-pal-search-wrap">
                <svg class="rb-pal-search-ico" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                </svg>
                <input wire:model.live.debounce.300ms="catalogueSearch"
                       class="rb-pal-search"
                       placeholder="Search metrics…">
            </div>
        </div>

        {{-- Domain tabs --}}
        <div class="rb-pal-tabs">
            @foreach($domainDomains as $domKey => $domMeta)
            <button class="rb-pal-tab {{ $catalogueDomain === $domKey ? 'active' : '' }}"
                    wire:click="$set('catalogueDomain', '{{ $domKey }}')">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    {!! $domMeta['icon'] !!}
                </svg>
                {{ $domMeta['label'] }}
            </button>
            @endforeach
        </div>

        {{-- Metric list --}}
        <div class="rb-pal-body">
            @forelse($catalogue as $domain => $metrics)
                @if($catalogueDomain === 'all')
                <div class="rb-pal-group-lbl">
                    <div class="rb-pal-dot" style="background:var(--{{ $domainColors[$domain] ?? 'text-dim' }})"></div>
                    {{ ucfirst($domain) }}
                </div>
                @endif
                @foreach($metrics as $metric)
                @php $isAdded = in_array($metric['id'], $addedMetricIds) @endphp
                <div class="rb-metric-row">
                    <div class="rb-metric-info">
                        <div class="rb-metric-name">{{ $metric['label'] }}</div>
                        <div class="rb-metric-desc">{{ $metric['description'] }}</div>
                    </div>
                    @if($isAdded)
                        <span class="rb-added-badge">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display:inline;vertical-align:middle">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Added
                        </span>
                    @else
                        <button class="rb-add-btn" wire:click="addBlock('{{ $metric['id'] }}')">+ Add</button>
                    @endif
                </div>
                @endforeach
            @empty
                <div style="padding:24px 12px;text-align:center;font-size:12px;color:var(--text-dim)">
                    No metrics match "{{ $catalogueSearch }}"
                </div>
            @endforelse
        </div>
    </div>

</div>{{-- end workspace --}}

{{-- ═══ FOOTER SAVE BAR ══════════════════════════════════════════════════ --}}
<div class="rb-footer" x-data="{ showSchedule: {{ ($scheduleCron || $scheduleRecipients) ? 'true' : 'false' }} }">
    <div class="rb-footer-left">
        <span class="rb-block-count">
            <strong>{{ count($canvas) }}</strong> {{ Str::plural('block', count($canvas)) }} on canvas
        </span>
        <button class="rb-schedule-toggle" @click="showSchedule = !showSchedule">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
            </svg>
            <span x-text="showSchedule ? 'Hide schedule' : 'Schedule delivery…'"></span>
        </button>
    </div>

    <div class="rb-footer-left" style="margin-left:auto">
        <a href="{{ route('owner.reports.custom.library') }}"
           style="color:var(--text-dim);font-size:13px;font-weight:600;text-decoration:none;
                  padding:9px 14px;border-radius:8px;transition:background var(--tr)"
           onmouseover="this.style.background='var(--surface2)'"
           onmouseout="this.style.background='transparent'">
            Cancel
        </a>
        <button wire:click="save" class="rb-save-btn" wire:loading.attr="disabled">
            <span wire:loading.remove>
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                </svg>
                {{ $editingReportId ? 'Update Report' : 'Save Report' }}
            </span>
            <span wire:loading>Saving…</span>
        </button>
    </div>

    {{-- Schedule section (Alpine-controlled) --}}
    <div class="rb-schedule-section" x-show="showSchedule">
        <div class="rb-sf">
            <label class="rb-top-label">Cron Schedule</label>
            <input type="text"
                   wire:model.blur="scheduleCron"
                   class="rb-sf-input"
                   placeholder="e.g. 0 8 1 * * (8am on 1st of month)">
        </div>
        <div class="rb-sf">
            <label class="rb-top-label">Email Recipients</label>
            <input type="text"
                   wire:model.blur="scheduleRecipients"
                   class="rb-sf-input"
                   placeholder="email1@example.com, email2@example.com">
        </div>
    </div>
</div>

{{-- ═══ Sortable.js drag-and-drop ════════════════════════════════════════ --}}
@script
<script>
(function () {
    function initSortable() {
        var list = document.getElementById('rb-canvas-blocks');
        if (!list || list._sortableInit) return;
        list._sortableInit = true;
        new Sortable(list, {
            handle: '.rb-block-handle',
            animation: 150,
            ghostClass: 'rb-block-ghost',
            dragClass: 'rb-block-dragging',
            onEnd: function () {
                var ids = Array.from(list.children).map(function (el) {
                    return el.dataset.blockId;
                }).filter(Boolean);
                $wire.reorderBlocks(ids);
            }
        });
    }

    // Load Sortable.js from CDN (only on builder page, not globally)
    if (typeof Sortable === 'undefined') {
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js';
        s.onload = initSortable;
        document.head.appendChild(s);
    } else {
        initSortable();
    }

    // Re-init after Livewire re-renders (e.g. first block added)
    Livewire.hook('commit', ({ succeed }) => {
        succeed(() => {
            requestAnimationFrame(function () {
                var list = document.getElementById('rb-canvas-blocks');
                if (list) { list._sortableInit = false; initSortable(); }
            });
        });
    });
})();
</script>
@endscript

</div>
