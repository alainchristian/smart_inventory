<div style="font-family:var(--font)">
<style>
/* ── page prefix: rb- (receive-boxes) ─────── */

/* Mode tabs */
.rb-tabs { display:flex;gap:4px;margin-bottom:20px;align-items:center }
.rb-tab  { padding:9px 20px;border-radius:9px;border:1.5px solid var(--border);
           font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font);
           background:var(--surface);color:var(--text-dim);transition:all var(--tr);
           display:flex;align-items:center;gap:7px;white-space:nowrap }
.rb-tab:hover  { border-color:var(--accent);color:var(--accent) }
.rb-tab.active { background:var(--accent);border-color:var(--accent);color:#fff }
.rb-dl-btn { padding:8px 16px;background:var(--surface2);color:var(--text-sub);
             border:1.5px solid var(--border);border-radius:var(--rsm);font-size:12px;
             font-weight:600;cursor:pointer;font-family:var(--font);
             transition:all var(--tr);display:inline-flex;align-items:center;gap:6px }
.rb-dl-btn:hover { border-color:var(--accent);color:var(--accent) }

/* Flash */
.rb-flash { padding:12px 16px;border-radius:var(--rsm);font-size:13px;font-weight:500;
            margin-bottom:16px;display:flex;align-items:center;gap:10px }
.rb-flash.green { background:var(--green-dim);color:var(--green);border:1px solid var(--green) }
.rb-flash.red   { background:var(--red-dim);color:var(--red);border:1px solid var(--red) }
.rb-flash.amber { background:var(--amber-dim);color:var(--amber);border:1px solid var(--amber) }

/* Cards */
.rb-card { background:var(--surface);border:none;box-shadow:var(--shadow-card);
           border-radius:var(--r);padding:22px 24px }

/* Scan strip */
.rb-scan { background:var(--surface);border:none;box-shadow:var(--shadow-card);
           border-radius:var(--r);padding:22px 24px;margin-bottom:16px }
.rb-scan-lbl { font-size:10px;font-weight:700;letter-spacing:.9px;text-transform:uppercase;
               color:var(--text-dim);margin-bottom:10px }
.rb-scan-row { display:flex;gap:10px;align-items:center }
.rb-scan-input { flex:1;padding:13px 16px;background:var(--surface);
                 border:1.5px solid var(--border);border-radius:var(--rsm);
                 font-family:var(--mono);font-size:15px;font-weight:600;color:var(--text);
                 outline:none;box-sizing:border-box;transition:border-color var(--tr) }
.rb-scan-input:focus  { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.rb-scan-input::placeholder { color:var(--text-dim);font-weight:400;font-size:13px;font-family:var(--font) }
.rb-scan-input:disabled { background:var(--surface2);color:var(--text-dim);cursor:not-allowed }
.rb-scan-btn  { padding:13px 22px;background:var(--accent);color:#fff;border:none;
                border-radius:var(--rsm);font-size:13px;font-weight:700;cursor:pointer;
                font-family:var(--font);white-space:nowrap;
                box-shadow:0 3px 10px var(--accent-glow);transition:opacity var(--tr) }
.rb-scan-btn:hover { opacity:.88 }
.rb-scan-btn:disabled { opacity:.38;cursor:not-allowed }
.rb-scan-clear { padding:13px 16px;background:var(--surface2);color:var(--text-sub);
                 border:1.5px solid var(--border);border-radius:var(--rsm);
                 font-size:13px;cursor:pointer;font-family:var(--font);transition:all var(--tr) }
.rb-scan-clear:hover { border-color:var(--border-hi);color:var(--text) }
.rb-scan-hint { font-size:11px;color:var(--text-dim);margin-top:8px }
.rb-scan-hint.warn { color:var(--amber) }

/* OR divider */
.rb-or { display:flex;align-items:center;gap:12px;margin:16px 0;
         color:var(--text-dim);font-size:12px;font-weight:600;letter-spacing:.3px }
.rb-or::before,.rb-or::after { content:'';flex:1;height:1px;background:var(--border) }

/* Browse button */
.rb-browse-btn { width:100%;padding:12px 20px;background:var(--accent-dim);color:var(--accent);
                 border:1.5px solid var(--accent);border-radius:var(--rsm);font-size:13px;
                 font-weight:700;cursor:pointer;font-family:var(--font);
                 display:flex;align-items:center;justify-content:center;gap:8px;
                 transition:all var(--tr) }
.rb-browse-btn:hover    { background:var(--accent);color:#fff }
.rb-browse-btn:disabled { opacity:.42;cursor:not-allowed;border-color:var(--border);
                          background:var(--surface2);color:var(--text-dim) }

/* Form fields */
.rb-field  { margin-bottom:16px }
.rb-grid-2 { display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px }
.rb-label  { display:block;font-size:12px;font-weight:700;color:var(--text-sub);
             margin-bottom:6px;letter-spacing:.3px }
.rb-label span { color:var(--red) }
.rb-input  { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:var(--rsm);
             font-size:14px;background:var(--surface);color:var(--text);outline:none;
             box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.rb-input:focus    { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.rb-input:disabled { background:var(--surface2);color:var(--text-dim);cursor:not-allowed }
.rb-input.mono     { font-family:var(--mono) }
.rb-select { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:var(--rsm);
             font-size:14px;background:var(--surface);color:var(--text);outline:none;
             box-sizing:border-box;font-family:var(--font);cursor:pointer;
             transition:border-color var(--tr) }
.rb-select:focus    { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.rb-select:disabled { background:var(--surface2);color:var(--text-dim);cursor:not-allowed }
.rb-error  { font-size:11px;color:var(--red);margin-top:4px }
.rb-hint   { font-size:11px;color:var(--text-dim);margin-top:4px;line-height:1.5 }
.rb-divider { height:1px;background:var(--border);margin:18px 0 }
.rb-section-head { font-size:13px;font-weight:700;color:var(--text);margin-bottom:14px }

/* Alert boxes */
.rb-alert       { padding:14px 16px;border-radius:var(--rsm);margin-bottom:14px }
.rb-alert.green { background:var(--green-dim);border:1px solid var(--green) }
.rb-alert.amber { background:var(--amber-dim);border:1px solid var(--amber) }
.rb-alert.red   { background:var(--red-dim);border:1px solid var(--red) }
.rb-alert.blue  { background:var(--accent-dim);border:1px solid var(--accent) }
.rb-alert-head  { display:flex;align-items:center;gap:8px;margin-bottom:6px }
.rb-alert-title { font-size:13px;font-weight:700 }
.rb-alert.green .rb-alert-title { color:var(--green) }
.rb-alert.amber .rb-alert-title { color:var(--amber) }
.rb-alert.red   .rb-alert-title { color:var(--red) }
.rb-alert.blue  .rb-alert-title { color:var(--accent) }
.rb-alert-body  { font-size:12px;line-height:1.7 }
.rb-alert.green .rb-alert-body  { color:var(--green) }
.rb-alert.amber .rb-alert-body  { color:var(--amber) }
.rb-alert.red   .rb-alert-body  { color:var(--red) }
.rb-alert.blue  .rb-alert-body  { color:var(--accent) }

/* Product info grid inside modal */
.rb-pinfo     { display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px }
.rb-pinfo-row { display:flex;flex-direction:column;gap:2px }
.rb-pinfo-key { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--text-dim) }
.rb-pinfo-val { font-size:13px;font-weight:600;color:var(--text) }

/* Search results */
.rb-results { border:1px solid var(--border);border-radius:var(--rsm);
              background:var(--surface);box-shadow:var(--shadow-card);
              max-height:260px;overflow-y:auto;margin-top:6px }
.rb-result  { padding:11px 14px;cursor:pointer;border-bottom:1px solid var(--border);
              transition:background var(--tr);display:flex;align-items:center;
              justify-content:space-between;gap:12px }
.rb-result:last-child  { border-bottom:none }
.rb-result:hover,.rb-result.sel { background:var(--accent-dim) }
.rb-result-name { font-size:13px;font-weight:600;color:var(--text) }
.rb-result-meta { font-size:11px;color:var(--text-dim);margin-top:1px }

/* Checkbox row */
.rb-chk-row  { display:flex;align-items:flex-start;gap:10px;padding:12px 14px;
               background:var(--accent-dim);border-radius:var(--rsm);
               border:1px solid var(--accent);cursor:pointer }
.rb-chk-row input { margin-top:3px;accent-color:var(--accent);flex-shrink:0 }
.rb-chk-text { font-size:13px;font-weight:600;color:var(--text) }
.rb-chk-sub  { font-size:11px;color:var(--text-dim);margin-top:2px;line-height:1.5 }

/* Excel row cards */
.rb-xs { border-radius:var(--r);margin-bottom:16px;box-shadow:var(--shadow-card);overflow:hidden }
.rb-xs-head { padding:14px 18px;display:flex;align-items:center;gap:10px }
.rb-xs-head.green { background:var(--green-dim);border-bottom:1px solid var(--green) }
.rb-xs-head.amber { background:var(--amber-dim);border-bottom:1px solid var(--amber) }
.rb-xs-head.red   { background:var(--red-dim);border-bottom:1px solid var(--red) }
.rb-xs-title { font-size:13px;font-weight:700 }
.rb-xs-head.green .rb-xs-title { color:var(--green) }
.rb-xs-head.amber .rb-xs-title { color:var(--amber) }
.rb-xs-head.red   .rb-xs-title { color:var(--red) }
.rb-xs-body { background:var(--surface);padding:16px }
.rb-row-card { border:1px solid var(--border);border-radius:var(--rsm);padding:16px;margin-bottom:12px }
.rb-row-card:last-child { margin-bottom:0 }
.rb-row-badge { display:inline-flex;align-items:center;font-size:10px;font-weight:700;
                padding:2px 8px;border-radius:20px;white-space:nowrap }
.rb-row-fields { background:var(--bg);border-radius:var(--rsm);padding:14px;margin-top:12px }
.rb-chg-alert  { background:var(--amber-dim);border:1px solid var(--amber);border-radius:var(--rsm);
                 padding:12px 14px;margin-bottom:12px }
.rb-chg-title  { font-size:12px;font-weight:700;color:var(--amber);margin-bottom:6px;
                 display:flex;align-items:center;gap:6px }
.rb-chg-list   { margin:0 0 0 14px;font-size:11px;color:var(--amber);line-height:1.9;padding:0 }

/* Import summary */
.rb-summary    { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px }
.rb-stat       { background:var(--bg);border-radius:var(--rsm);padding:14px 16px;text-align:center }
.rb-stat-lbl   { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;
                 color:var(--text-dim);margin-bottom:6px }
.rb-stat-val   { font-size:22px;font-weight:800;font-family:var(--mono);line-height:1 }

/* Session bar */
.rb-session { background:var(--accent-dim);border:1px solid var(--accent);
              border-radius:var(--rsm);padding:10px 18px;margin-bottom:16px;
              display:flex;align-items:center;gap:10px }

/* Recent table */
.rb-tbl-wrap { background:var(--surface);border:none;box-shadow:var(--shadow-card);
               border-radius:var(--r);overflow:hidden;margin-top:20px }
.rb-tbl-head { display:flex;align-items:center;justify-content:space-between;
               padding:14px 18px;border-bottom:1px solid var(--border) }
.rb-tbl      { width:100%;border-collapse:collapse;font-size:13px }
.rb-tbl thead tr { background:var(--bg);border-bottom:1px solid var(--border) }
.rb-tbl thead th { padding:9px 14px;text-align:left;font-size:11px;font-weight:700;
                   letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.rb-tbl tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.rb-tbl tbody tr:last-child { border-bottom:none }
.rb-tbl tbody tr:hover { background:var(--surface2) }
.rb-tbl td { padding:11px 14px;vertical-align:middle }
.rb-new-btn { padding:6px 14px;background:transparent;border:1.5px solid var(--border);
              border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;
              font-family:var(--font);color:var(--text-sub);transition:all var(--tr) }
.rb-new-btn:hover { border-color:var(--accent);color:var(--accent) }

/* Modal */
.rb-overlay { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);
              backdrop-filter:blur(2px);display:flex;align-items:center;
              justify-content:center;padding:20px }
.rb-modal   { background:var(--surface);border-radius:var(--r);
              box-shadow:0 20px 60px rgba(26,31,54,.25);
              width:100%;max-width:520px;max-height:88vh;display:flex;flex-direction:column }
.rb-modal-head  { display:flex;align-items:center;justify-content:space-between;
                  padding:18px 22px;border-bottom:1px solid var(--border);flex-shrink:0 }
.rb-modal-title { font-size:16px;font-weight:800;color:var(--text) }
.rb-modal-sub   { font-size:12px;color:var(--text-dim);margin-top:2px }
.rb-modal-close { width:32px;height:32px;border-radius:8px;border:none;
                  background:var(--surface2);color:var(--text-sub);cursor:pointer;
                  display:flex;align-items:center;justify-content:center;transition:background var(--tr) }
.rb-modal-close:hover { background:var(--surface3) }
.rb-modal-body  { flex:1;overflow-y:auto;padding:22px }
.rb-modal-foot  { padding:14px 22px;border-top:1px solid var(--border);
                  display:flex;gap:10px;justify-content:flex-end;flex-shrink:0 }
.rb-btn-confirm { padding:11px 24px;background:var(--green);color:#fff;border:none;
                  border-radius:var(--rsm);font-size:14px;font-weight:700;cursor:pointer;
                  font-family:var(--font);box-shadow:0 3px 10px rgba(14,158,134,.25);
                  transition:opacity var(--tr) }
.rb-btn-confirm:hover    { opacity:.88 }
.rb-btn-confirm:disabled { opacity:.42;cursor:not-allowed }
.rb-btn-cancel  { padding:11px 20px;background:transparent;border:1.5px solid var(--border);
                  color:var(--text-sub);border-radius:var(--rsm);font-size:14px;font-weight:600;
                  cursor:pointer;font-family:var(--font);transition:all var(--tr) }
.rb-btn-cancel:hover { border-color:var(--border-hi);color:var(--text) }

/* File upload zone */
.rb-upload-zone { border:2px dashed var(--border);border-radius:var(--r);
                  padding:32px 20px;text-align:center;transition:border-color var(--tr);cursor:pointer }
.rb-upload-zone:hover { border-color:var(--accent) }
.rb-upload-choose { display:inline-flex;align-items:center;gap:8px;padding:9px 20px;
                    background:var(--accent);color:#fff;border:none;border-radius:var(--rsm);
                    font-size:13px;font-weight:700;cursor:pointer;font-family:var(--font) }

/* Instructions */
.rb-instr      { background:var(--bg);border-radius:var(--rsm);padding:14px 16px;margin-bottom:16px }
.rb-instr-title { font-size:12px;font-weight:700;color:var(--text);margin-bottom:8px }
.rb-instr ol   { margin:0 0 0 18px;padding:0;font-size:12px;color:var(--text-sub);line-height:2 }

/* Responsive */
@media(max-width:768px) {
    .rb-scan-row { flex-direction:column }
    .rb-scan-input,.rb-scan-btn,.rb-scan-clear { width:100%;box-sizing:border-box }
    .rb-grid-2 { grid-template-columns:1fr }
    .rb-summary { grid-template-columns:1fr 1fr }
    .rb-modal { align-self:flex-end;max-width:100vw;border-radius:var(--r) var(--r) 0 0;max-height:92vh }
    .rb-modal-foot { flex-direction:column }
    .rb-btn-confirm,.rb-btn-cancel { width:100%;text-align:center }
}
@media(max-width:480px) {
    .rb-tabs { overflow-x:auto;flex-wrap:nowrap;-webkit-overflow-scrolling:touch }
    .rb-tab  { flex-shrink:0 }
    .rb-summary { grid-template-columns:1fr 1fr }
}
</style>

{{-- ── Mode tabs ────────────────────────────────────────────────── --}}
<div class="rb-tabs">
    <button wire:click="switchMode('manual')" type="button"
            class="rb-tab {{ $manualMode ? 'active' : '' }}">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
        </svg>
        Manual Entry
    </button>
    <button wire:click="switchMode('excel')" type="button"
            class="rb-tab {{ !$manualMode ? 'active' : '' }}">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
        </svg>
        Excel / CSV Import
    </button>
    @if(!$manualMode)
    <button wire:click="downloadTemplate" type="button" class="rb-dl-btn" style="margin-left:auto">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        Download Template
    </button>
    @endif
</div>

{{-- ── Flash messages ───────────────────────────────────────────── --}}
@if(session()->has('success'))
<div class="rb-flash green">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0">
        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
    </svg>
    {{ session('success') }}
</div>
@endif
@if(session()->has('error'))
<div class="rb-flash red">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0">
        <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
    </svg>
    {{ session('error') }}
</div>
@endif
@if(session()->has('warning'))
<div class="rb-flash amber">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0">
        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
    </svg>
    {{ session('warning') }}
</div>
@endif

@if($manualMode)
{{-- ══════════════════════════ MANUAL MODE ══════════════════════════ --}}

    {{-- Warehouse selector --}}
    <div class="rb-card" style="margin-bottom:16px">
        <label class="rb-label">Warehouse <span>*</span></label>
        <select wire:model.live="warehouseId" class="rb-select"
                {{ auth()->user()->isWarehouseManager() ? 'disabled' : '' }}>
            <option value="">Select warehouse…</option>
            @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
            @endforeach
        </select>
        @error('warehouseId') <p class="rb-error">{{ $message }}</p> @enderror
    </div>

    {{-- Scan strip --}}
    <div class="rb-scan">
        <div class="rb-scan-lbl">Scan product barcode</div>
        <div class="rb-scan-row">
            <input wire:model.live="productBarcode"
                   type="text"
                   class="rb-scan-input"
                   placeholder="Scan or type barcode from box…"
                   {{ !$warehouseId ? 'disabled' : '' }}
                   autofocus>
            @if($productBarcode)
            <button wire:click="clearProduct" type="button" class="rb-scan-clear">Clear</button>
            @endif
        </div>
        @if(!$warehouseId)
        <p class="rb-scan-hint warn">Select a warehouse above before scanning</p>
        @else
        <p class="rb-scan-hint">Receive form opens automatically when a barcode is detected</p>
        @endif
        @error('productBarcode') <p style="font-size:11px;color:rgba(255,100,100,.85);margin-top:8px">{{ $message }}</p> @enderror
    </div>

    {{-- OR + Browse --}}
    <div class="rb-card">
        <div class="rb-or">OR</div>
        <button wire:click="openProductDropdown" type="button"
                {{ !$warehouseId ? 'disabled' : '' }}
                class="rb-browse-btn">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            Browse &amp; Select Product
        </button>
        <p class="rb-hint" style="text-align:center;margin-top:10px">
            @if(!$warehouseId)
                Select a warehouse to enable product browsing
            @else
                Choose from existing products or create a new one
            @endif
        </p>
    </div>

@else
{{-- ══════════════════════════ EXCEL MODE ═══════════════════════════ --}}

    @if(!$showExcelPreview)
    {{-- Upload card --}}
    <div class="rb-card">

        <div class="rb-field">
            <label class="rb-label">Warehouse <span>*</span></label>
            <select wire:model.live="warehouseId" class="rb-select"
                    {{ auth()->user()->isWarehouseManager() ? 'disabled' : '' }}>
                <option value="">Select warehouse…</option>
                @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="rb-instr">
            <div class="rb-instr-title">Import Instructions</div>
            <ol>
                <li>Download the CSV/Excel template</li>
                <li>Fill in: <strong>barcode, product_name, category, items_per_box, box_purchase_price, box_selling_price, boxes</strong></li>
                <li><strong>Optional:</strong> sku, batch_number, and expiry_date. (The system will automatically generate an SKU and a Batch Number for you if left blank)</li>
                <li>Upload the file — the system auto-matches existing products by barcode, then by name</li>
                <li>Review matches, correct any unknown products, then confirm the import</li>
            </ol>
        </div>

        <label class="rb-upload-zone">
            <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                 style="color:var(--text-dim);margin-bottom:12px">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            <div style="font-size:14px;font-weight:600;color:var(--text-sub);margin-bottom:14px">
                Drop file here or click to browse
            </div>
            <span class="rb-upload-choose">Choose CSV / Excel File</span>
            <input type="file" wire:model="excelFile" accept=".xlsx,.xls,.csv" style="display:none">
            @if($excelFile)
            <div style="margin-top:12px;font-size:12px;color:var(--accent);font-weight:600;
                        display:flex;align-items:center;gap:6px;justify-content:center">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                {{ $excelFile->getClientOriginalName() }}
            </div>
            @endif
            @error('excelFile') <p class="rb-error" style="margin-top:8px">{{ $message }}</p> @enderror
        </label>

        @if($excelFile && !$showExcelPreview)
        <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-top:16px">
            @if(!$warehouseId)
            <span style="font-size:12px;color:var(--red)">Select a warehouse first</span>
            @endif
            <button wire:click="processExcelFile" type="button"
                    {{ !$warehouseId ? 'disabled' : '' }}
                    style="padding:11px 22px;background:{{ $warehouseId ? 'var(--accent)' : 'var(--surface2)' }};
                           color:{{ $warehouseId ? '#fff' : 'var(--text-dim)' }};border:none;
                           border-radius:var(--rsm);font-size:14px;font-weight:700;
                           cursor:{{ $warehouseId ? 'pointer' : 'not-allowed' }};font-family:var(--font);
                           display:inline-flex;align-items:center;gap:8px;
                           {{ $warehouseId ? 'box-shadow:0 3px 10px rgba(59,111,212,.25)' : '' }}">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                </svg>
                Preview Import
            </button>
        </div>
        @endif
    </div>

    @else
    {{-- ───────── EXCEL PREVIEW ───────── --}}

        {{-- RECOGNIZED --}}
        @if(!empty($excelRecognized))
        <div class="rb-xs">
            <div class="rb-xs-head green">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color:var(--green);flex-shrink:0">
                    <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <span class="rb-xs-title">
                    Products Found — {{ count($excelRecognized) }} rows · {{ array_sum(array_column($excelRecognized, 'boxes')) }} boxes
                </span>
            </div>
            <div class="rb-xs-body">
                @foreach($excelRecognized as $item)
                @php $rowNum = $item['row_number']; @endphp
                <div class="rb-row-card">
                    <div style="display:flex;align-items:center;gap:7px;margin-bottom:10px;flex-wrap:wrap">
                        <span class="rb-row-badge" style="background:var(--green-dim);color:var(--green)">Row {{ $rowNum }}</span>
                        <span class="rb-row-badge" style="background:var(--green-dim);color:var(--green)">Matched by {{ $item['match_method'] }}</span>
                        @if($item['new_barcode'] ?? false)
                        <span class="rb-row-badge" style="background:var(--accent-dim);color:var(--accent)">New Barcode</span>
                        @endif
                        <span style="font-size:12px;color:var(--text-dim);margin-left:auto">
                            {{ $item['boxes'] }} boxes{{ !empty($item['batch_number']) ? ' · Batch: ' . $item['batch_number'] : '' }}
                        </span>
                    </div>

                    {{-- Change detection --}}
                    @if(!empty($excelHasDifferentValues[$rowNum]) && in_array(true, $excelHasDifferentValues[$rowNum]))
                    <div class="rb-chg-alert">
                        <div class="rb-chg-title">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                            Excel values differ from database:
                        </div>
                        <ul class="rb-chg-list">
                            @if($excelHasDifferentValues[$rowNum]['name'] ?? false)          <li>Product name differs</li>@endif
                            @if($excelHasDifferentValues[$rowNum]['sku'] ?? false)           <li>SKU differs</li>@endif
                            @if($excelHasDifferentValues[$rowNum]['items_per_box'] ?? false) <li>Items per box differs</li>@endif
                            @if($excelHasDifferentValues[$rowNum]['box_purchase_price'] ?? false) <li>Box purchase price differs</li>@endif
                            @if($excelHasDifferentValues[$rowNum]['box_selling_price'] ?? false)  <li>Box selling price differs</li>@endif
                        </ul>
                        <label style="display:flex;align-items:center;gap:8px;margin-top:10px;cursor:pointer;font-size:12px;font-weight:600;color:var(--amber)">
                            <input type="checkbox" wire:model="shouldUpdateProduct.{{ $rowNum }}" style="accent-color:var(--amber)">
                            Update product with Excel values
                        </label>
                    </div>
                    @endif

                    {{-- Editable fields --}}
                    <div class="rb-row-fields">
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-dim);margin-bottom:10px">
                            Product Information (editable)
                        </div>
                        <div class="rb-field">
                            <label class="rb-label">Product Name</label>
                            <input type="text" wire:model="editableProductNames.{{ $rowNum }}" class="rb-input">
                        </div>
                        <div class="rb-grid-2">
                            <div>
                                <label class="rb-label">Barcode</label>
                                <input type="text" wire:model="editableProductBarcodes.{{ $rowNum }}" class="rb-input mono">
                            </div>
                            <div>
                                <label class="rb-label">SKU</label>
                                <input type="text" wire:model="editableProductSkus.{{ $rowNum }}" class="rb-input mono">
                            </div>
                        </div>
                        <div class="rb-grid-2">
                            <div>
                                <label class="rb-label">Category</label>
                                <select wire:model="editableProductCategories.{{ $rowNum }}" class="rb-select">
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="rb-label">Items / Box</label>
                                <input type="number" wire:model="editableProductItemsPerBox.{{ $rowNum }}" min="1" class="rb-input">
                            </div>
                        </div>
                        <div class="rb-grid-2">
                            <div>
                                <label class="rb-label">Box Purchase Price (RWF)</label>
                                <input type="number" wire:model="editableProductBoxPurchasePrices.{{ $rowNum }}" min="0" class="rb-input">
                            </div>
                            <div>
                                <label class="rb-label">Box Selling Price (RWF)</label>
                                <input type="number" wire:model="editableProductBoxSellingPrices.{{ $rowNum }}" min="0" class="rb-input">
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- UNKNOWN / NEW --}}
        @if(!empty($excelUnknown))
        <div class="rb-xs">
            <div class="rb-xs-head amber">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color:var(--amber);flex-shrink:0">
                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <span class="rb-xs-title">Verify Products — {{ count($excelUnknown) }} rows</span>
            </div>
            <div class="rb-xs-body">
                @foreach($excelUnknown as $item)
                @php $rowNum = $item['row_number']; @endphp
                <div class="rb-row-card" style="{{ $item['status'] === 'matched' ? 'border-color:var(--green)' : '' }}">
                    <div style="display:flex;align-items:center;gap:7px;margin-bottom:10px;flex-wrap:wrap">
                        <span class="rb-row-badge" style="background:var(--amber-dim);color:var(--amber)">Row {{ $rowNum }}</span>
                        @if($item['status'] === 'matched')
                        <span class="rb-row-badge" style="background:var(--green-dim);color:var(--green)">Matched</span>
                        @else
                        <span class="rb-row-badge" style="background:var(--red-dim);color:var(--red)">New Product</span>
                        @endif
                        <span style="font-size:12px;color:var(--text-dim);margin-left:auto">
                            {{ $item['boxes'] }} boxes{{ !empty($item['batch_number']) ? ' · Batch: ' . $item['batch_number'] : '' }}
                        </span>
                    </div>

                    @if($item['status'] === 'matched')
                    <div class="rb-alert green" style="margin-bottom:0">
                        <div style="font-size:13px;font-weight:700;color:var(--green)">{{ $item['matched_product_name'] }}</div>
                        <div style="font-size:11px;color:var(--green);margin-top:3px">
                            SKU: {{ $item['product_sku'] }} · {{ $item['matched_items_per_box'] }} items/box
                        </div>
                    </div>
                    @else
                    <div class="rb-row-fields">
                        {{-- Live search --}}
                        <div style="position:relative;margin-bottom:12px">
                            <label class="rb-label">
                                Product Name
                                <span style="color:var(--text-dim);font-weight:400">(search existing or keep as new)</span>
                            </label>
                            <div style="padding:8px 12px;background:var(--accent-dim);border-radius:var(--rsm);
                                        margin-bottom:8px;font-size:12px;color:var(--accent);font-weight:600">
                                From Excel: "{{ $editableProductNames[$rowNum] }}"
                            </div>
                            <input type="text"
                                   wire:model.live.debounce.300ms="editableProductSearchQuery.{{ $rowNum }}"
                                   placeholder="Type to search existing products…"
                                   class="rb-input"
                                   autocomplete="off">
                            @if(!empty($liveSearchResults[$rowNum]))
                            <div style="position:absolute;left:0;right:0;z-index:20;top:100%;margin-top:2px">
                                <div class="rb-results">
                                    @foreach($liveSearchResults[$rowNum] as $result)
                                    <div class="rb-result"
                                         wire:click="selectProductFromSearch({{ $rowNum }}, {{ $result['id'] }})">
                                        <div>
                                            <div class="rb-result-name">{{ $result['name'] }}</div>
                                            <div class="rb-result-meta">
                                                SKU: {{ $result['sku'] }} · {{ $result['items_per_box'] }} items/box · {{ number_format($result['selling_price']) }} RWF
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            <p class="rb-hint">Type to search — or leave as is to create a new product</p>
                        </div>

                        <div class="rb-grid-2">
                            <div>
                                <label class="rb-label">Barcode</label>
                                <input type="text" value="{{ $editableProductBarcodes[$rowNum] }}" disabled class="rb-input mono">
                            </div>
                            <div>
                                <label class="rb-label">SKU</label>
                                <input type="text" wire:model="editableProductSkus.{{ $rowNum }}" class="rb-input mono">
                            </div>
                        </div>
                        <div class="rb-grid-2">
                            <div>
                                <label class="rb-label">Category <span>*</span></label>
                                @if($editableProductCategories[$rowNum])
                                    <input type="text" value="{{ $item['category_name'] }}" disabled
                                           class="rb-input"
                                           style="background:var(--green-dim);color:var(--green);border-color:var(--green)">
                                @else
                                    <select wire:model="editableProductCategories.{{ $rowNum }}"
                                            class="rb-select"
                                            style="border-color:var(--red);background:var(--red-dim);color:var(--red)">
                                        <option value="">"{{ $item['category_name'] }}" not found — select:</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                            <div>
                                <label class="rb-label">Items / Box</label>
                                <input type="number" wire:model="editableProductItemsPerBox.{{ $rowNum }}" class="rb-input">
                            </div>
                        </div>
                        <div class="rb-grid-2">
                            <div>
                                <label class="rb-label">Box Purchase Price (RWF)</label>
                                <input type="number" wire:model="editableProductBoxPurchasePrices.{{ $rowNum }}" min="0" class="rb-input">
                            </div>
                            <div>
                                <label class="rb-label">Box Selling Price (RWF)</label>
                                <input type="number" wire:model="editableProductBoxSellingPrices.{{ $rowNum }}" min="0" class="rb-input">
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ERRORS --}}
        @if(!empty($excelErrors))
        <div class="rb-xs">
            <div class="rb-xs-head red">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color:var(--red);flex-shrink:0">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                <span class="rb-xs-title">Import Errors — {{ count($excelErrors) }} rows</span>
            </div>
            <div class="rb-xs-body">
                @foreach($excelErrors as $error)
                <div style="padding:8px 2px;font-size:13px;color:var(--red);border-bottom:1px solid var(--border)">
                    <strong>Row {{ $error['row'] }}:</strong> {{ $error['error'] }}
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- IMPORT SUMMARY --}}
        <div class="rb-card">
            <div class="rb-section-head">Import Summary</div>
            <div class="rb-summary">
                <div class="rb-stat">
                    <div class="rb-stat-lbl">Ready</div>
                    <div class="rb-stat-val" style="color:var(--green)">{{ array_sum(array_column($excelRecognized, 'boxes')) }}</div>
                    <div style="font-size:11px;color:var(--text-dim);margin-top:4px">boxes</div>
                </div>
                <div class="rb-stat">
                    <div class="rb-stat-lbl">New Products</div>
                    <div class="rb-stat-val" style="color:var(--amber)">{{ count(array_filter($excelUnknown, fn($i) => $i['status'] !== 'matched')) }}</div>
                    <div style="font-size:11px;color:var(--text-dim);margin-top:4px">to create</div>
                </div>
                <div class="rb-stat">
                    <div class="rb-stat-lbl">Errors</div>
                    <div class="rb-stat-val" style="color:var(--red)">{{ count($excelErrors) }}</div>
                    <div style="font-size:11px;color:var(--text-dim);margin-top:4px">rows</div>
                </div>
                <div class="rb-stat">
                    <div class="rb-stat-lbl">Total</div>
                    <div class="rb-stat-val" style="color:var(--accent)">{{ array_sum(array_column($excelRecognized, 'boxes')) + array_sum(array_column($excelUnknown, 'boxes')) }}</div>
                    <div style="font-size:11px;color:var(--text-dim);margin-top:4px">boxes</div>
                </div>
            </div>

            @if(!$this->canImport && !empty($warehouseId))
            <div class="rb-alert amber" style="margin-bottom:16px">
                <div class="rb-alert-body">Select categories for all new products before importing</div>
            </div>
            @endif

            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button wire:click="cancelExcelImport" type="button" class="rb-btn-cancel">Cancel</button>
                <button wire:click="confirmExcelImport" type="button"
                        @if(!$this->canImport) disabled @endif
                        class="rb-btn-confirm">
                    Import All ({{ array_sum(array_column($excelRecognized, 'boxes')) + array_sum(array_column($excelUnknown, 'boxes')) }} boxes)
                </button>
            </div>
        </div>

    @endif
@endif

{{-- ── Session bar ──────────────────────────────────────────────── --}}
@if($sessionTotal > 0)
<div class="rb-session" style="margin-top:16px">
    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color:var(--accent);flex-shrink:0">
        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
    </svg>
    <span style="font-size:13px;color:var(--accent);font-weight:600">
        Session total: <strong style="font-family:var(--mono)">{{ $sessionTotal }}</strong> boxes received
    </span>
</div>
@endif

{{-- ── Recent boxes ─────────────────────────────────────────────── --}}
@if(!empty($recentBoxes))
<div class="rb-tbl-wrap">
    <div class="rb-tbl-head">
        <div>
            <span style="font-size:14px;font-weight:700;color:var(--text)">Recently Received</span>
            <span style="font-size:12px;color:var(--text-dim);margin-left:8px;font-weight:400">{{ count($recentBoxes) }} boxes this session</span>
        </div>
        <button wire:click="startNew" type="button" class="rb-new-btn">Start New</button>
    </div>
    <div style="overflow-x:auto">
    <table class="rb-tbl">
        <thead>
            <tr>
                <th>Box Code</th>
                <th>Product</th>
                <th>Items</th>
                <th>Supplier Barcode</th>
                <th>Batch</th>
                <th>Expiry</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentBoxes as $box)
            <tr>
                <td>
                    <span style="font-family:var(--mono);font-size:12px;font-weight:700;
                                 padding:3px 9px;border-radius:7px;
                                 background:var(--accent-dim);color:var(--accent)">
                        {{ $box->box_code }}
                    </span>
                </td>
                <td style="font-weight:600;color:var(--text)">{{ $box->product->name }}</td>
                <td style="font-family:var(--mono);font-size:12px;font-weight:700;color:var(--text-sub)">
                    {{ $box->items_remaining }}
                </td>
                <td style="font-family:var(--mono);font-size:11px;color:var(--text-dim)">
                    {{ $box->supplier_barcode ?? '—' }}
                </td>
                <td style="font-size:12px;color:var(--text-dim)">{{ $box->batch_number ?? '—' }}</td>
                <td style="font-size:12px;color:var(--text-dim)">
                    {{ $box->expiry_date ? $box->expiry_date->format('d M Y') : '—' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endif

{{-- ══════════════════════════ RECEIVE MODAL ════════════════════════ --}}
@if($showReceiveModal)
<div class="rb-overlay" wire:click="closeReceiveModal">
    <div class="rb-modal" @click.stop>

        {{-- Header --}}
        <div class="rb-modal-head">
            <div>
                <div class="rb-modal-title">
                    @if($isNewProduct)      Create New Product
                    @elseif($barcodeIsKnown) Receive Boxes
                    @elseif($showProductSearch) Link Barcode to Product
                    @elseif($showProductDropdown) Select Product
                    @else Receive Boxes
                    @endif
                </div>
                @if($productBarcode && !$showProductDropdown && !$isNewProduct)
                <div class="rb-modal-sub">
                    Barcode: <span style="font-family:var(--mono);color:var(--accent);font-weight:700">{{ $productBarcode }}</span>
                </div>
                @endif
            </div>
            <button wire:click="closeReceiveModal" type="button" class="rb-modal-close">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="rb-modal-body">

            {{-- Warehouse warning --}}
            @if(!$warehouseId)
            <div class="rb-alert red">
                <div class="rb-alert-head">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <span class="rb-alert-title">Warehouse Not Selected</span>
                </div>
                <div class="rb-alert-body">Close this dialog and select a warehouse on the main screen first.</div>
            </div>
            @endif

            @if($barcodeIsKnown && !$showProductSearch)
            {{-- ── SCENARIO 1: Known barcode ── --}}
            @if($product)
            <div class="rb-alert green">
                <div class="rb-alert-head">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <span class="rb-alert-title">Product Recognized</span>
                </div>
                <div class="rb-pinfo">
                    <div class="rb-pinfo-row">
                        <span class="rb-pinfo-key">Name</span>
                        <span class="rb-pinfo-val">{{ $product->name }}</span>
                    </div>
                    <div class="rb-pinfo-row">
                        <span class="rb-pinfo-key">SKU</span>
                        <span class="rb-pinfo-val" style="font-family:var(--mono)">{{ $product->sku }}</span>
                    </div>
                    <div class="rb-pinfo-row">
                        <span class="rb-pinfo-key">Items / Box</span>
                        <span class="rb-pinfo-val">{{ $product->items_per_box }}</span>
                    </div>
                    <div class="rb-pinfo-row">
                        <span class="rb-pinfo-key">Selling Price</span>
                        <span class="rb-pinfo-val">{{ number_format($product->selling_price) }} RWF</span>
                    </div>
                </div>
            </div>
            @endif

            @elseif($showProductSearch)
            {{-- ── SCENARIO 2: Unknown barcode ── --}}
            <div class="rb-alert amber">
                <div class="rb-alert-head">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    <span class="rb-alert-title">Barcode Not Recognized</span>
                </div>
                <div class="rb-alert-body">
                    Barcode <strong style="font-family:var(--mono)">{{ $productBarcode }}</strong> is not in the system.
                    Search for the product below to link it.
                </div>
            </div>

            <div class="rb-field">
                <label class="rb-label">Search Product</label>
                <input type="text"
                       wire:model.live.debounce.300ms="productSearch"
                       placeholder="Type product name or SKU…"
                       class="rb-input">
                <p class="rb-hint">Start typing to see matching products</p>
            </div>

            @if(!empty($searchResults))
            <div class="rb-results" style="margin-bottom:14px">
                @foreach($searchResults as $result)
                <div wire:click="selectProduct({{ $result['id'] }})"
                     class="rb-result {{ $selectedProductId == $result['id'] ? 'sel' : '' }}">
                    <div>
                        <div class="rb-result-name">{{ $result['name'] }}</div>
                        <div class="rb-result-meta">SKU: {{ $result['sku'] }} · {{ $result['items_per_box'] }} items/box</div>
                    </div>
                    @if($selectedProductId == $result['id'])
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color:var(--accent);flex-shrink:0">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    @endif
                </div>
                @endforeach
            </div>
            @elseif(strlen($productSearch) >= 2)
            <div style="text-align:center;padding:20px 0 14px;color:var(--text-dim);font-size:13px">
                No products found for "{{ $productSearch }}"
            </div>
            @endif

            @if($selectedProductId)
            <div class="rb-chk-row" style="margin-bottom:14px">
                <input type="checkbox" wire:model="rememberBarcode" checked id="rb-remember">
                <label for="rb-remember" style="cursor:pointer;flex:1">
                    <div class="rb-chk-text">Remember this barcode association</div>
                    <div class="rb-chk-sub">
                        Saves <strong style="font-family:var(--mono)">{{ $productBarcode }}</strong> for
                        "{{ $selectedProductName }}" so it's recognized instantly next time
                    </div>
                </label>
            </div>
            @if($rememberBarcode)
            <div class="rb-field">
                <label class="rb-label">
                    Supplier Name
                    <span style="color:var(--text-dim);font-weight:400">(optional)</span>
                </label>
                <input type="text" wire:model="supplierName" placeholder="e.g., Supplier A" class="rb-input">
            </div>
            @endif
            @endif

            @elseif($showProductDropdown)
            {{-- ── SCENARIO 3: Product dropdown ── --}}
            <div class="rb-alert blue">
                <div class="rb-alert-head">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--accent)">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <span class="rb-alert-title">Select a Product</span>
                </div>
                <div class="rb-alert-body">Browse existing products or create a new one if it doesn't exist yet</div>
            </div>

            <div class="rb-field">
                <label class="rb-label">Search Product</label>
                <input type="text"
                       wire:model.live.debounce.300ms="productSearch"
                       placeholder="Type product name or SKU…"
                       class="rb-input"
                       autofocus>
                <p class="rb-hint">Start typing to see matching products</p>
            </div>

            @if(!empty($searchResults))
            <div class="rb-results" style="margin-bottom:14px">
                @foreach($searchResults as $result)
                <div wire:click="selectProductFromDropdown({{ $result['id'] }})"
                     class="rb-result {{ $selectedProductId == $result['id'] ? 'sel' : '' }}">
                    <div>
                        <div class="rb-result-name">{{ $result['name'] }}</div>
                        <div class="rb-result-meta">
                            SKU: {{ $result['sku'] }} · {{ $result['items_per_box'] }} items/box · {{ number_format($result['selling_price']) }} RWF
                        </div>
                    </div>
                    @if($selectedProductId == $result['id'])
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color:var(--accent);flex-shrink:0">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    @endif
                </div>
                @endforeach
            </div>
            @elseif(strlen($productSearch) >= 2)
            <div style="text-align:center;padding:16px 0 14px">
                <p style="font-size:13px;color:var(--text-dim);margin-bottom:14px">
                    No products found for "{{ $productSearch }}"
                </p>
                <button wire:click="createNewProduct" type="button"
                        style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;
                               background:var(--accent);color:#fff;border:none;border-radius:var(--rsm);
                               font-size:13px;font-weight:700;cursor:pointer;font-family:var(--font)">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Create New Product
                </button>
            </div>
            @else
            <div style="text-align:center;padding:12px 0 14px">
                <button wire:click="createNewProduct" type="button"
                        style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;
                               background:var(--accent);color:#fff;border:none;border-radius:var(--rsm);
                               font-size:13px;font-weight:700;cursor:pointer;font-family:var(--font)">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Create New Product
                </button>
            </div>
            @endif

            @elseif($isNewProduct)
            {{-- ── SCENARIO 4: New product form ── --}}
            <div class="rb-alert green">
                <div class="rb-alert-head">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color:var(--green)">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    <span class="rb-alert-title">Create New Product</span>
                </div>
                <div class="rb-alert-body">Fill in the product details — it will be created and boxes received in one step</div>
            </div>

            <div class="rb-field">
                <label class="rb-label">Product Name <span>*</span></label>
                <input type="text" wire:model="newProductName" placeholder="e.g., Coca Cola 500ml" class="rb-input">
                @error('newProductName') <p class="rb-error">{{ $message }}</p> @enderror
            </div>
            <div class="rb-field">
                <label class="rb-label">SKU <span>*</span></label>
                <input type="text" wire:model="newProductSku" placeholder="e.g., COC-500ML" class="rb-input mono">
                @error('newProductSku') <p class="rb-error">{{ $message }}</p> @enderror
            </div>
            <div class="rb-field">
                <label class="rb-label">
                    Barcode
                    <span style="color:var(--text-dim);font-weight:400">(optional)</span>
                </label>
                <input type="text" wire:model="productBarcode" placeholder="Leave empty or enter barcode" class="rb-input mono">
                @error('productBarcode') <p class="rb-error">{{ $message }}</p> @enderror
            </div>
            <div class="rb-field">
                <label class="rb-label">Category <span>*</span></label>
                <select wire:model="newProductCategoryId" class="rb-select">
                    <option value="">Select category…</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('newProductCategoryId') <p class="rb-error">{{ $message }}</p> @enderror
            </div>
            <div class="rb-grid-2">
                <div>
                    <label class="rb-label">Items per Box <span>*</span></label>
                    <input type="number" wire:model="newProductItemsPerBox" min="1" placeholder="e.g., 24" class="rb-input">
                    @error('newProductItemsPerBox') <p class="rb-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="rb-label">Selling Price (RWF) <span>*</span></label>
                    <input type="number" wire:model="newProductSellingPrice" min="0" step="1" placeholder="e.g., 500" class="rb-input">
                    @error('newProductSellingPrice') <p class="rb-error">{{ $message }}</p> @enderror
                </div>
            </div>
            @endif

            {{-- ── BOX DETAILS (shown when a product is selected/confirmed) ── --}}
            @if((!$showProductSearch || $selectedProductId) && !$showProductDropdown || $isNewProduct)
            <div class="rb-divider"></div>
            <div class="rb-section-head">Box Details</div>

            <div class="rb-field">
                <label class="rb-label">Number of Boxes <span>*</span></label>
                <input type="number" wire:model="numberOfBoxes" min="1" max="100" class="rb-input">
                @if($product)
                <p class="rb-hint">≈ {{ $numberOfBoxes * $product->items_per_box }} items total</p>
                @endif
                @error('numberOfBoxes') <p class="rb-error">{{ $message }}</p> @enderror
            </div>
            <div class="rb-grid-2">
                <div>
                    <label class="rb-label">
                        Batch Number
                        <span style="color:var(--text-dim);font-weight:400">(optional)</span>
                    </label>
                    <input type="text" wire:model="batchNumber" placeholder="e.g., BATCH-2024-Q1" class="rb-input">
                    @error('batchNumber') <p class="rb-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="rb-label">
                        Expiry Date
                        <span style="color:var(--text-dim);font-weight:400">(optional)</span>
                    </label>
                    <input type="date" wire:model="expiryDate" class="rb-input">
                    @error('expiryDate') <p class="rb-error">{{ $message }}</p> @enderror
                </div>
            </div>
            @endif

        </div>{{-- /modal-body --}}

        {{-- Footer --}}
        <div class="rb-modal-foot">
            <button wire:click="closeReceiveModal" type="button" class="rb-btn-cancel">Cancel</button>
            @if((!$showProductSearch || $selectedProductId) && !$showProductDropdown)
            <button wire:click="createBoxes" type="button"
                    {{ !$warehouseId ? 'disabled' : '' }}
                    class="rb-btn-confirm">
                @if($isNewProduct)
                    Create &amp; Receive {{ $numberOfBoxes }} Box{{ $numberOfBoxes > 1 ? 'es' : '' }}
                @else
                    Receive {{ $numberOfBoxes }} Box{{ $numberOfBoxes > 1 ? 'es' : '' }}
                @endif
            </button>
            @endif
        </div>

    </div>{{-- /modal --}}
</div>{{-- /overlay --}}
@endif

</div>
