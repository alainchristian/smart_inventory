<div>
@if($sessionBlocked)
    <x-session-gate-blocked
        :reason="$sessionBlockReason"
        :session-date="$blockedSessionDate"
        :session-id="$blockedSessionId"
    />
@else
<div class="dg-page" style="font-family:var(--font);color:var(--text);padding-bottom:60px">
<style>
/* ── KPI Cards ── */
.dg-kpi-grid  { display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px }
.dg-kpi-card  { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);padding:18px 20px }
.dg-kpi-icon  { width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;margin-bottom:12px }
.dg-kpi-val   { font-size:26px;font-weight:800;font-family:var(--mono);letter-spacing:-1px;line-height:1;margin-bottom:4px }
.dg-kpi-lbl   { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-dim) }
.dg-kpi-sub   { font-size:11px;color:var(--text-dim);margin-top:4px }

/* ── Filter card ── */
.dg-filter    { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);padding:16px 20px;margin-bottom:20px }
.dg-filter-row { display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end }
.dg-f-group   { display:flex;flex-direction:column;gap:5px;min-width:0 }
.dg-f-lbl     { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-dim) }
.dg-inp       { padding:8px 11px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:var(--surface);color:var(--text);outline:none;font-family:var(--font);transition:border-color var(--tr) }
.dg-inp:focus { border-color:var(--accent) }
.dg-inp.ico   { padding-left:34px }
.dg-inp-wrap  { position:relative }
.dg-inp-icon  { position:absolute;left:10px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--text-dim);pointer-events:none }

/* disposition pills */
.dg-pills     { display:flex;gap:6px;flex-wrap:wrap }
.dg-pill      { padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;border:1.5px solid var(--border);background:transparent;color:var(--text-dim);cursor:pointer;font-family:var(--font);transition:all var(--tr);white-space:nowrap }
.dg-pill.on   { border-color:var(--accent);background:var(--accent);color:#fff }
.dg-pill:hover:not(.on) { border-color:var(--text-dim);color:var(--text) }
.dg-pill.amb.on { border-color:var(--amber);background:var(--amber);color:#fff }

.dg-reset-btn { display:inline-flex;align-items:center;gap:5px;padding:8px 13px;border-radius:8px;font-size:12px;font-weight:600;border:1px solid var(--border);background:transparent;color:var(--text-dim);cursor:pointer;font-family:var(--font);transition:all var(--tr);white-space:nowrap }
.dg-reset-btn:hover { color:var(--text);border-color:var(--text-dim) }

/* ── Table ── */
.dg-table-wrap { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);overflow:hidden }
.dg-tbl-head   { padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between }
.dg-tbl-ttl    { font-size:14px;font-weight:700 }
.dg-tbl-count  { font-size:12px;color:var(--text-dim) }
.dg-scroll     { overflow-x:auto }
.dg-tbl        { width:100%;border-collapse:collapse;table-layout:fixed }
.dg-tbl th     { padding:10px 16px;text-align:left;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);border-bottom:1px solid var(--border);white-space:nowrap;background:var(--surface) }
.dg-tbl td     { padding:12px 16px;border-bottom:1px solid var(--border);vertical-align:middle }
.dg-tbl tr:last-child td { border-bottom:none }
.dg-tbl tbody tr { cursor:pointer;transition:background var(--tr) }
.dg-tbl tbody tr:hover { background:var(--surface2) }

/* ── Disposition badge ── */
.dg-badge { display:inline-flex;align-items:center;padding:3px 9px;border-radius:6px;font-size:11px;font-weight:700 }

/* ── Expand panel ── */
.dg-expand    { background:var(--surface) }
.dg-expand td { padding:16px 20px;border-left:3px solid var(--accent) }
.dg-exp-grid  { display:grid;grid-template-columns:1fr 1fr;gap:20px }
.dg-exp-sec   { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-dim);margin-bottom:10px }
.dg-exp-row   { display:flex;justify-content:space-between;align-items:flex-start;padding:6px 0;font-size:12px;border-bottom:1px solid var(--border) }
.dg-exp-row:last-child { border-bottom:none }
.dg-exp-key   { color:var(--text-dim);font-weight:500;flex-shrink:0;margin-right:16px }
.dg-exp-val   { font-weight:600;text-align:right }

/* ── Pending dot ── */
.dg-dot       { width:7px;height:7px;border-radius:50%;display:inline-block;margin-right:5px;flex-shrink:0 }

/* ── Modals (shared) ── */
.dg-modal-bg  { position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:500;display:flex;align-items:center;justify-content:center;padding:20px }
.dg-modal     { background:var(--surface);border-radius:var(--r);box-shadow:0 20px 60px rgba(0,0,0,.18);width:100%;max-width:560px;max-height:90vh;overflow-y:auto }
.dg-modal-hd  { padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between }
.dg-modal-ttl { font-size:15px;font-weight:800 }
.dg-modal-bd  { padding:20px 22px }
.dg-modal-ft  { padding:16px 22px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:flex-end;gap:10px }
.dg-close-btn { width:28px;height:28px;border-radius:7px;border:1px solid var(--border);background:transparent;color:var(--text-dim);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all var(--tr) }
.dg-close-btn:hover { background:var(--surface2);color:var(--text) }

/* Disposition option cards */
.dg-disp-grid { display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:16px }
.dg-disp-opt  { padding:11px 13px;border-radius:9px;border:1.5px solid var(--border);background:var(--surface);cursor:pointer;display:flex;align-items:center;gap:10px;transition:all var(--tr);font-family:var(--font);text-align:left }
.dg-disp-opt:hover { border-color:var(--text-dim) }
.dg-disp-opt.on   { border-color:var(--accent) }
.dg-disp-ico  { width:28px;height:28px;border-radius:7px;background:var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0 }
.dg-disp-opt.on .dg-disp-ico { background:var(--accent-dim) }
.dg-disp-lbl  { font-size:12px;font-weight:700;color:var(--text-dim) }
.dg-disp-opt.on .dg-disp-lbl { color:var(--accent) }
.dg-disp-sub  { font-size:10px;color:var(--text-dim);margin-top:1px }
.dg-disp-opt.on .dg-disp-sub { color:var(--accent) }
.dg-disp-full { grid-column:1/-1 }

/* Product info strip in modal */
.dg-prod-strip { border:1px solid var(--border);border-radius:9px;padding:13px 15px;margin-bottom:18px;display:flex;align-items:center;justify-content:space-between;gap:12px }

/* Form fields */
.dg-form-row  { margin-bottom:16px }
.dg-form-lbl  { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-dim);margin-bottom:6px }
.dg-form-inp  { width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:var(--surface);color:var(--text);outline:none;font-family:var(--font);box-sizing:border-box;transition:border-color var(--tr) }
.dg-form-inp:focus { border-color:var(--accent) }
.dg-form-2col { display:grid;grid-template-columns:1fr 1fr;gap:12px }

/* Product search dropdown */
.dg-dd        { position:absolute;top:calc(100% + 4px);left:0;right:0;background:var(--surface);border:1px solid var(--border);border-radius:9px;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:600;overflow:hidden }
.dg-dd-item   { padding:10px 13px;cursor:pointer;transition:background var(--tr);font-size:13px }
.dg-dd-item:hover { background:var(--surface2) }
.dg-dd-sku    { font-size:10px;color:var(--text-dim);margin-top:1px;font-family:var(--mono) }

/* Buttons */
.dg-btn-pr { display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:700;border:none;background:var(--accent);color:#fff;cursor:pointer;font-family:var(--font);transition:all var(--tr) }
.dg-btn-pr:hover { opacity:.88 }
.dg-btn-pr:disabled { opacity:.45;cursor:not-allowed }
.dg-btn-gh { display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;border:1px solid var(--border);background:transparent;color:var(--text-dim);cursor:pointer;font-family:var(--font);transition:all var(--tr) }
.dg-btn-gh:hover { color:var(--text);border-color:var(--text-dim) }
.dg-btn-rec { display:inline-flex;align-items:center;gap:6px;padding:8px 15px;border-radius:8px;font-size:13px;font-weight:700;border:none;background:var(--accent);color:#fff;cursor:pointer;font-family:var(--font);transition:all var(--tr) }
.dg-btn-rec:hover { opacity:.88 }

/* Notes textarea */
.dg-textarea  { width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:var(--surface);color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font);resize:vertical;transition:border-color var(--tr) }
.dg-textarea:focus { border-color:var(--accent) }

/* Empty state */
.dg-empty { text-align:center;padding:56px 20px }
.dg-empty-ico { width:48px;height:48px;border-radius:50%;background:var(--border);display:flex;align-items:center;justify-content:center;margin:0 auto 14px }

/* Pagination */
.dg-pager { padding:14px 20px;border-top:1px solid var(--border) }

/* Flash */
.dg-flash { padding:12px 16px;border-radius:9px;font-size:13px;font-weight:600;margin-bottom:20px;display:flex;align-items:center;gap:8px }

/* Validation errors */
.dg-err { font-size:12px;color:var(--red);margin-top:4px }

/* Responsive */
@media(max-width:900px) {
    .dg-kpi-grid { grid-template-columns:1fr 1fr }
    .dg-exp-grid { grid-template-columns:1fr }
    .dg-disp-grid { grid-template-columns:1fr }
    .dg-disp-full { grid-column:auto }
    .dg-form-2col { grid-template-columns:1fr }
}
@media(max-width:600px) {
    .dg-kpi-grid { grid-template-columns:1fr 1fr }
    .dg-filter-row { flex-direction:column;align-items:stretch }
}
[x-cloak] { display:none !important }
</style>

@if(session()->has('success'))
    <div class="dg-flash" style="background:var(--green-dim);border:1px solid var(--green);color:var(--green)">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
@endif
@if(session()->has('error'))
    <div class="dg-flash" style="background:var(--red-dim);border:1px solid var(--red);color:var(--red)">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        {{ session('error') }}
    </div>
@endif

{{-- Header --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;gap:12px;flex-wrap:wrap">
    <div>
        <h1 style="font-size:22px;font-weight:800;letter-spacing:-.4px;margin:0 0 3px">Damaged Goods</h1>
        <p style="font-size:13px;color:var(--text-dim);margin:0">{{ $locationName }}</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        @if($kpiStats['pending_count'] > 0)
            <div style="display:flex;align-items:center;gap:7px;padding:8px 14px;border-radius:9px;background:var(--amber-dim);border:1px solid var(--amber)">
                <span class="dg-dot" style="background:var(--amber)"></span>
                <span style="font-size:12px;font-weight:700;color:var(--amber)">{{ $kpiStats['pending_count'] }} awaiting disposition</span>
            </div>
        @endif
        <button type="button" wire:click="openRecordForm" class="dg-btn-rec">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Record Damaged Good
        </button>
    </div>
</div>

{{-- KPI Cards --}}
<div class="dg-kpi-grid">
    <div class="dg-kpi-card">
        <div class="dg-kpi-icon" style="background:var(--accent-dim)">
            <svg width="16" height="16" fill="none" stroke="var(--accent)" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div class="dg-kpi-val" style="color:var(--text)">{{ number_format($kpiStats['total_damaged']) }}</div>
        <div class="dg-kpi-lbl">Total Records</div>
        <div class="dg-kpi-sub">{{ number_format($kpiStats['total_quantity']) }} items logged</div>
    </div>
    <div class="dg-kpi-card">
        <div class="dg-kpi-icon" style="background:var(--amber-dim)">
            <svg width="16" height="16" fill="none" stroke="var(--amber)" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="dg-kpi-val" style="color:{{ $kpiStats['pending_count'] > 0 ? 'var(--amber)' : 'var(--text-dim)' }}">{{ number_format($kpiStats['pending_count']) }}</div>
        <div class="dg-kpi-lbl">Pending Decision</div>
        <div class="dg-kpi-sub">{{ $kpiStats['pending_count'] > 0 ? 'Requires action' : 'All resolved' }}</div>
    </div>
    <div class="dg-kpi-card">
        <div class="dg-kpi-icon" style="background:var(--red-dim)">
            <svg width="16" height="16" fill="none" stroke="var(--red)" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div class="dg-kpi-val" style="color:var(--red)">{{ number_format($kpiStats['total_quantity']) }}</div>
        <div class="dg-kpi-lbl">Items Damaged</div>
        <div class="dg-kpi-sub">Units lost from stock</div>
    </div>
    <div class="dg-kpi-card">
        <div class="dg-kpi-icon" style="background:var(--red-dim)">
            <svg width="16" height="16" fill="none" stroke="var(--red)" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="dg-kpi-val" style="color:var(--red)">
            @if($kpiStats['total_loss'] >= 1000000)
                {{ number_format($kpiStats['total_loss'] / 1000000, 1) }}M
            @elseif($kpiStats['total_loss'] >= 1000)
                {{ number_format($kpiStats['total_loss'] / 1000, 0) }}K
            @else
                {{ number_format($kpiStats['total_loss']) }}
            @endif
        </div>
        <div class="dg-kpi-lbl">Est. Loss (RWF)</div>
        <div class="dg-kpi-sub">Value lost from damage</div>
    </div>
</div>

{{-- Filters --}}
<div class="dg-filter">
    {{-- Disposition pills --}}
    <div style="margin-bottom:14px">
        <div class="dg-f-lbl" style="margin-bottom:8px">Status</div>
        <div class="dg-pills">
            <button type="button" wire:click="setDispositionFilter('all')"
                    class="dg-pill {{ $dispositionFilter === 'all' ? 'on' : '' }}">All</button>

            <button type="button" wire:click="setDispositionFilter('pending')"
                    class="dg-pill amb {{ $dispositionFilter === 'pending' ? 'on' : '' }}">
                Pending
                @if($kpiStats['pending_count'] > 0)
                    <span style="margin-left:4px;background:{{ $dispositionFilter === 'pending' ? 'rgba(255,255,255,.25)' : 'var(--amber-dim)' }};color:{{ $dispositionFilter === 'pending' ? '#fff' : 'var(--amber)' }};padding:1px 6px;border-radius:10px;font-size:10px">{{ $kpiStats['pending_count'] }}</span>
                @endif
            </button>

            <button type="button" wire:click="setDispositionFilter('return_to_supplier')"
                    class="dg-pill {{ $dispositionFilter === 'return_to_supplier' ? 'on' : '' }}">Return to Supplier</button>

            <button type="button" wire:click="setDispositionFilter('dispose')"
                    class="dg-pill {{ $dispositionFilter === 'dispose' ? 'on' : '' }}">Dispose</button>

            <button type="button" wire:click="setDispositionFilter('discount_sale')"
                    class="dg-pill {{ $dispositionFilter === 'discount_sale' ? 'on' : '' }}">Discount Sale</button>

            <button type="button" wire:click="setDispositionFilter('write_off')"
                    class="dg-pill {{ $dispositionFilter === 'write_off' ? 'on' : '' }}">Write Off</button>

            <button type="button" wire:click="setDispositionFilter('repair')"
                    class="dg-pill {{ $dispositionFilter === 'repair' ? 'on' : '' }}">Repair</button>
        </div>
    </div>

    {{-- Row 2: search + dates + location + reset --}}
    <div class="dg-filter-row">
        <div class="dg-f-group" style="flex:2;min-width:180px">
            <div class="dg-f-lbl">Search</div>
            <div class="dg-inp-wrap">
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Reference, product name…"
                       class="dg-inp ico" style="width:100%;box-sizing:border-box">
                <svg class="dg-inp-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        @if($isOwner && count($locations) > 0)
            <div class="dg-f-group" style="flex:1;min-width:140px">
                <div class="dg-f-lbl">Location</div>
                <select wire:model.live="locationFilter" class="dg-inp" style="width:100%;box-sizing:border-box">
                    <option value="all">All Locations</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc['id'] }}">{{ $loc['name'] }} ({{ $loc['type'] }})</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="dg-f-group">
            <div class="dg-f-lbl">From</div>
            <input type="date" wire:model="dateFrom" class="dg-inp">
        </div>
        <div class="dg-f-group">
            <div class="dg-f-lbl">To</div>
            <input type="date" wire:model="dateTo" class="dg-inp">
        </div>

        <div class="dg-f-group" style="justify-content:flex-end">
            <div class="dg-f-lbl" style="opacity:0">.</div>
            <button type="button" wire:click="resetFilters" class="dg-reset-btn">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Reset
            </button>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="dg-table-wrap" x-data="{ open: null }">
    <div class="dg-tbl-head">
        <div class="dg-tbl-ttl">Damaged Goods Log</div>
        <div class="dg-tbl-count">{{ $damagedGoods->total() }} record{{ $damagedGoods->total() !== 1 ? 's' : '' }}</div>
    </div>
    <div class="dg-scroll">
        <table class="dg-tbl">
            <colgroup>
                <col style="width:160px">
                <col style="width:200px">
                <col style="width:100px">
                <col style="width:80px">
                <col style="width:120px">
                <col style="width:140px">
                <col style="width:100px">
                <col style="width:120px">
            </colgroup>
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Product</th>
                    <th>Source</th>
                    <th>Qty</th>
                    <th>Est. Loss</th>
                    <th>Disposition</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($damagedGoods as $dg)
                    @php
                        $disp = $dg->disposition->value ?? 'pending';
                        $dispColors = [
                            'pending'            => ['bg' => 'var(--amber-dim)',  'color' => 'var(--amber)'],
                            'return_to_supplier' => ['bg' => 'var(--accent-dim)', 'color' => 'var(--accent)'],
                            'dispose'            => ['bg' => 'var(--red-dim)',    'color' => 'var(--red)'],
                            'discount_sale'      => ['bg' => 'var(--green-dim)',  'color' => 'var(--green)'],
                            'write_off'          => ['bg' => 'var(--border)',     'color' => 'var(--text-dim)'],
                            'repair'             => ['bg' => 'var(--accent-dim)', 'color' => 'var(--accent)'],
                        ];
                        $dc = $dispColors[$disp] ?? $dispColors['pending'];
                    @endphp

                    <tr @click="open === {{ $dg->id }} ? open = null : open = {{ $dg->id }}">
                        <td>
                            <span style="font-family:var(--mono);font-size:12px;font-weight:700">{{ $dg->damage_reference }}</span>
                        </td>
                        <td>
                            <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $dg->product->name ?? 'Unknown' }}</div>
                            @if($dg->product->sku ?? null)
                                <div style="font-size:11px;color:var(--text-dim)">{{ $dg->product->sku }}</div>
                            @endif
                        </td>
                        <td>
                            <span style="font-size:11px;font-weight:600;padding:3px 8px;border-radius:5px;background:var(--border);color:var(--text-dim);white-space:nowrap">
                                {{ ucwords(str_replace('_', ' ', $dg->source_type ?? 'unknown')) }}
                            </span>
                        </td>
                        <td>
                            <span style="font-size:14px;font-weight:800;font-family:var(--mono);color:var(--red)">{{ $dg->quantity_damaged }}</span>
                        </td>
                        <td>
                            <span style="font-size:13px;font-weight:700;font-family:var(--mono)">{{ number_format($dg->estimated_loss) }}</span>
                            <span style="font-size:10px;color:var(--text-dim)"> RWF</span>
                        </td>
                        <td>
                            <span class="dg-badge" style="background:{{ $dc['bg'] }};color:{{ $dc['color'] }}">
                                {{ $dg->disposition->label() }}
                            </span>
                        </td>
                        <td style="font-size:12px;color:var(--text-dim)">
                            {{ $dg->recorded_at->format('d M Y') }}
                        </td>
                        <td @click.stop>
                            <div style="display:flex;align-items:center;gap:6px">
                                <button type="button"
                                        @click="open === {{ $dg->id }} ? open = null : open = {{ $dg->id }}"
                                        style="padding:5px 10px;border-radius:7px;font-size:11px;font-weight:600;border:1px solid var(--border);background:transparent;color:var(--text-dim);cursor:pointer;font-family:var(--font);display:flex;align-items:center;gap:4px;transition:all var(--tr)"
                                        :style="open === {{ $dg->id }} ? 'border-color:var(--accent);color:var(--accent)' : ''">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                         :style="open === {{ $dg->id }} ? 'transform:rotate(180deg)' : ''"
                                         style="transition:transform .2s">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                    <span x-text="open === {{ $dg->id }} ? 'Hide' : 'Details'">Details</span>
                                </button>
                                @if($dg->isPending())
                                    <button type="button"
                                            wire:click="openDispositionModal({{ $dg->id }})"
                                            style="padding:5px 10px;border-radius:7px;font-size:11px;font-weight:700;border:none;background:var(--amber);color:#fff;cursor:pointer;font-family:var(--font);white-space:nowrap">
                                        Decide
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Expand panel --}}
                    <tr x-show="open === {{ $dg->id }}" x-cloak class="dg-expand">
                        <td colspan="8">
                            <div class="dg-exp-grid">
                                <div>
                                    <div class="dg-exp-sec">Damage Details</div>
                                    <div class="dg-exp-row">
                                        <span class="dg-exp-key">Description</span>
                                        <span class="dg-exp-val">{{ $dg->damage_description ?? '—' }}</span>
                                    </div>
                                    <div class="dg-exp-row">
                                        <span class="dg-exp-key">Recorded by</span>
                                        <span class="dg-exp-val">{{ $dg->recordedBy->name ?? '—' }}</span>
                                    </div>
                                    <div class="dg-exp-row">
                                        <span class="dg-exp-key">Recorded at</span>
                                        <span class="dg-exp-val">{{ $dg->recorded_at->format('d M Y · g:i A') }}</span>
                                    </div>
                                    @if($dg->disposition_decided_at)
                                        <div class="dg-exp-row">
                                            <span class="dg-exp-key">Decided by</span>
                                            <span class="dg-exp-val">{{ $dg->dispositionDecidedBy->name ?? '—' }}</span>
                                        </div>
                                        <div class="dg-exp-row">
                                            <span class="dg-exp-key">Decided at</span>
                                            <span class="dg-exp-val">{{ $dg->disposition_decided_at->format('d M Y · g:i A') }}</span>
                                        </div>
                                        @if($dg->disposition_notes)
                                            <div style="margin-top:10px;padding:10px 12px;border-radius:8px;border-left:3px solid var(--border);background:var(--surface);font-size:12px;color:var(--text-dim)">
                                                {{ $dg->disposition_notes }}
                                            </div>
                                        @endif
                                    @endif
                                </div>
                                <div>
                                    <div class="dg-exp-sec">Photos</div>
                                    @if($dg->photos && count($dg->photos) > 0)
                                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                                            @foreach($dg->photos as $photo)
                                                <a href="{{ asset('storage/' . $photo) }}" target="_blank" style="display:block;border-radius:8px;overflow:hidden;border:1px solid var(--border)">
                                                    <img src="{{ asset('storage/' . $photo) }}" alt="Damage photo" style="width:100%;height:100px;object-fit:cover;display:block">
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <div style="padding:24px;border:1px solid var(--border);border-radius:9px;text-align:center">
                                            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" style="color:var(--text-dim);margin:0 auto 8px;display:block"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <div style="font-size:12px;color:var(--text-dim)">No photos uploaded</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="8">
                            <div class="dg-empty">
                                <div class="dg-empty-ico">
                                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--text-dim)"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div style="font-size:14px;font-weight:700;margin-bottom:4px">No damaged goods found</div>
                                <div style="font-size:13px;color:var(--text-dim)">No records match your current filters.</div>
                                @if($dispositionFilter === 'all' && !$search)
                                    <div style="margin-top:14px">
                                        <button type="button" wire:click="openRecordForm" class="dg-btn-rec" style="font-size:12px;padding:7px 14px">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                            Record your first damaged good
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($damagedGoods->hasPages())
        <div class="dg-pager">{{ $damagedGoods->links() }}</div>
    @endif
</div>

{{-- ── Record Damaged Good Modal ── --}}
@if($showRecordForm)
    <div class="dg-modal-bg" wire:keydown.escape="closeRecordForm">
        <div class="dg-modal" style="max-width:520px" @click.stop>

            <div class="dg-modal-hd">
                <div>
                    <div class="dg-modal-ttl">Record Damaged Good</div>
                    <div style="font-size:12px;color:var(--text-dim);margin-top:2px">Manual entry — outside of returns flow</div>
                </div>
                <button type="button" wire:click="closeRecordForm" class="dg-close-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="dg-modal-bd">

                {{-- Owner: pick a shop --}}
                @if($isOwner)
                    <div class="dg-form-row">
                        <div class="dg-form-lbl">Shop Location <span style="color:var(--red)">*</span></div>
                        <select wire:model="recordLocationId" class="dg-form-inp">
                            <option value="">Select shop…</option>
                            @foreach($shops as $sh)
                                <option value="{{ $sh->id }}">{{ $sh->name }}</option>
                            @endforeach
                        </select>
                        @error('recordLocationId') <div class="dg-err">{{ $message }}</div> @enderror
                    </div>
                @endif

                {{-- Product search --}}
                <div class="dg-form-row" style="position:relative">
                    <div class="dg-form-lbl">Product <span style="color:var(--red)">*</span></div>
                    <input type="text"
                           wire:model.live.debounce.250ms="recordProductSearch"
                           wire:input="searchRecordProduct"
                           placeholder="Type product name or SKU…"
                           class="dg-form-inp"
                           autocomplete="off">
                    @if($showProductDropdown && count($recordProductResults) > 0)
                        <div class="dg-dd">
                            @foreach($recordProductResults as $pr)
                                <div class="dg-dd-item" wire:click="selectRecordProduct({{ $pr['id'] }}, '{{ addslashes($pr['name']) }}')">
                                    <div style="font-weight:600">{{ $pr['name'] }}</div>
                                    @if($pr['sku'])
                                        <div class="dg-dd-sku">{{ $pr['sku'] }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @error('recordProductId') <div class="dg-err">{{ $message }}</div> @enderror
                    @if($recordProductId)
                        <div style="margin-top:5px;font-size:12px;color:var(--green);display:flex;align-items:center;gap:4px">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Product selected
                        </div>
                    @endif
                </div>

                {{-- Quantity + Estimated Loss --}}
                <div class="dg-form-2col">
                    <div class="dg-form-row">
                        <div class="dg-form-lbl">Quantity Damaged <span style="color:var(--red)">*</span></div>
                        <input type="number" wire:model="recordQuantity" min="1" placeholder="0" class="dg-form-inp">
                        @error('recordQuantity') <div class="dg-err">{{ $message }}</div> @enderror
                    </div>
                    <div class="dg-form-row">
                        <div class="dg-form-lbl">Estimated Loss (RWF) <span style="color:var(--red)">*</span></div>
                        <input type="number" wire:model="recordEstimatedLoss" min="0" placeholder="0" class="dg-form-inp">
                        @error('recordEstimatedLoss') <div class="dg-err">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Description --}}
                <div class="dg-form-row">
                    <div class="dg-form-lbl">Description <span style="color:var(--text-dim);font-weight:400;text-transform:none;letter-spacing:0">(optional)</span></div>
                    <textarea wire:model="recordDescription"
                              rows="3"
                              placeholder="Describe how the damage occurred…"
                              class="dg-textarea"></textarea>
                    @error('recordDescription') <div class="dg-err">{{ $message }}</div> @enderror
                </div>

                <div style="font-size:12px;color:var(--text-dim);padding:10px 12px;border-radius:8px;background:var(--border);display:flex;gap:8px;align-items:flex-start">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    The record will be created as <strong>Pending</strong>. Use the Decide button in the list to set the final disposition.
                </div>

            </div>

            <div class="dg-modal-ft">
                <button type="button" wire:click="closeRecordForm" class="dg-btn-gh">Cancel</button>
                <button type="button"
                        wire:click="saveRecord"
                        wire:loading.attr="disabled"
                        class="dg-btn-pr">
                    <span wire:loading.remove wire:target="saveRecord">Save Record</span>
                    <span wire:loading wire:target="saveRecord" style="display:none">Saving…</span>
                </button>
            </div>

        </div>
    </div>
@endif

{{-- ── Disposition Modal ── --}}
@if($showDispositionModal && $selectedDamagedGood)
    <div class="dg-modal-bg" wire:keydown.escape="closeDispositionModal">
        <div class="dg-modal" @click.stop>

            <div class="dg-modal-hd">
                <div class="dg-modal-ttl">Decide Disposition</div>
                <button type="button" wire:click="closeDispositionModal" class="dg-close-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="dg-modal-bd">

                {{-- Product summary --}}
                <div class="dg-prod-strip">
                    <div>
                        <div style="font-size:14px;font-weight:700">{{ $selectedDamagedGood->product->name }}</div>
                        <div style="font-size:11px;color:var(--text-dim);margin-top:3px;font-family:var(--mono)">{{ $selectedDamagedGood->damage_reference }}</div>
                        @if($selectedDamagedGood->damage_description)
                            <div style="font-size:12px;color:var(--text-dim);margin-top:6px;font-style:italic">{{ $selectedDamagedGood->damage_description }}</div>
                        @endif
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        <div style="font-size:18px;font-weight:800;font-family:var(--mono);color:var(--red)">{{ $selectedDamagedGood->quantity_damaged }}</div>
                        <div style="font-size:10px;color:var(--text-dim);margin-top:2px">items</div>
                        <div style="font-size:12px;font-weight:700;font-family:var(--mono);color:var(--text-dim);margin-top:6px">{{ number_format($selectedDamagedGood->estimated_loss) }} RWF</div>
                        <div style="font-size:10px;color:var(--text-dim)">est. loss</div>
                    </div>
                </div>

                {{-- Disposition options --}}
                <div style="font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);margin-bottom:10px">Select What to Do</div>
                <div class="dg-disp-grid">

                    <button type="button" wire:click="$set('dispositionDecision','return_to_supplier')"
                            class="dg-disp-opt {{ $dispositionDecision === 'return_to_supplier' ? 'on' : '' }}">
                        <div class="dg-disp-ico">
                            <svg width="13" height="13" fill="none" stroke="{{ $dispositionDecision === 'return_to_supplier' ? 'var(--accent)' : 'var(--text-dim)' }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        </div>
                        <div>
                            <div class="dg-disp-lbl">Return to Supplier</div>
                            <div class="dg-disp-sub">Send back for credit</div>
                        </div>
                    </button>

                    <button type="button" wire:click="$set('dispositionDecision','dispose')"
                            class="dg-disp-opt {{ $dispositionDecision === 'dispose' ? 'on' : '' }}">
                        <div class="dg-disp-ico">
                            <svg width="13" height="13" fill="none" stroke="{{ $dispositionDecision === 'dispose' ? 'var(--accent)' : 'var(--text-dim)' }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </div>
                        <div>
                            <div class="dg-disp-lbl">Dispose</div>
                            <div class="dg-disp-sub">Discard permanently</div>
                        </div>
                    </button>

                    <button type="button" wire:click="$set('dispositionDecision','discount_sale')"
                            class="dg-disp-opt {{ $dispositionDecision === 'discount_sale' ? 'on' : '' }}">
                        <div class="dg-disp-ico">
                            <svg width="13" height="13" fill="none" stroke="{{ $dispositionDecision === 'discount_sale' ? 'var(--accent)' : 'var(--text-dim)' }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        </div>
                        <div>
                            <div class="dg-disp-lbl">Discount Sale</div>
                            <div class="dg-disp-sub">Sell at reduced price</div>
                        </div>
                    </button>

                    <button type="button" wire:click="$set('dispositionDecision','write_off')"
                            class="dg-disp-opt {{ $dispositionDecision === 'write_off' ? 'on' : '' }}">
                        <div class="dg-disp-ico">
                            <svg width="13" height="13" fill="none" stroke="{{ $dispositionDecision === 'write_off' ? 'var(--accent)' : 'var(--text-dim)' }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <div>
                            <div class="dg-disp-lbl">Write Off</div>
                            <div class="dg-disp-sub">Accept the loss</div>
                        </div>
                    </button>

                    <button type="button" wire:click="$set('dispositionDecision','repair')"
                            class="dg-disp-opt dg-disp-full {{ $dispositionDecision === 'repair' ? 'on' : '' }}">
                        <div class="dg-disp-ico">
                            <svg width="13" height="13" fill="none" stroke="{{ $dispositionDecision === 'repair' ? 'var(--accent)' : 'var(--text-dim)' }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <div class="dg-disp-lbl">Repair</div>
                            <div class="dg-disp-sub">Attempt to restore the item</div>
                        </div>
                    </button>

                </div>

                @error('dispositionDecision')
                    <div class="dg-err" style="margin-bottom:12px">{{ $message }}</div>
                @enderror

                {{-- Notes --}}
                <div>
                    <div style="font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);margin-bottom:8px">Notes <span style="font-weight:400;text-transform:none;letter-spacing:0">(optional)</span></div>
                    <textarea wire:model="dispositionNotes"
                              rows="3"
                              placeholder="Add any details about this decision…"
                              class="dg-textarea"></textarea>
                    @error('dispositionNotes')
                        <div class="dg-err" style="margin-top:4px">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <div class="dg-modal-ft">
                <button type="button" wire:click="closeDispositionModal" class="dg-btn-gh">Cancel</button>
                <button type="button"
                        wire:click="saveDisposition"
                        wire:loading.attr="disabled"
                        {{ !$dispositionDecision ? 'disabled' : '' }}
                        class="dg-btn-pr">
                    <span wire:loading.remove wire:target="saveDisposition">Save Decision</span>
                    <span wire:loading wire:target="saveDisposition" style="display:none">Saving…</span>
                </button>
            </div>

        </div>
    </div>
@endif

</div>
@endif
</div>
