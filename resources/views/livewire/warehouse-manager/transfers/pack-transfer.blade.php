@php use App\Enums\TransferStatus; @endphp
<style>
/* ── Pack Transfer ───────────────────────────────────── */
.pt-wrap { display:flex; flex-direction:column; gap:16px; }

/* Card shell */
.pt-card { background:#fff; border:1px solid var(--border); border-radius:12px; overflow:hidden; margin-bottom:0; }
.pt-card-head {
    display:flex; align-items:center; justify-content:space-between;
    padding:10px 14px; border-bottom:1px solid var(--border); background:var(--surface2);
}
.pt-card-head h3 { font-size:13px; font-weight:700; color:var(--text); margin:0; }
.pt-card-head p  { font-size:11px; color:var(--text-dim); margin:0; }
.pt-card-body    { padding:14px; }

/* Flash messages */
.pt-flash {
    display:flex; align-items:flex-start; gap:10px;
    padding:10px 14px; border-radius:10px; font-size:12px; border:1px solid;
}
.pt-flash.ok  { background:var(--green-dim); border-color:rgba(16,185,129,.25); color:var(--green); }
.pt-flash.err { background:var(--red-dim);   border-color:rgba(225,29,72,.25);  color:var(--red); }

/* Transfer header */
.pt-num   { font-size:16px; font-weight:800; color:var(--text); font-family:var(--mono); }
.pt-badge { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:20px; font-size:10px; font-weight:700; text-transform:uppercase; }
.pt-meta-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-top:12px; }
.pt-meta-label { font-size:10px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:var(--text-dim); }
.pt-meta-value { font-size:12px; font-weight:600; color:var(--text); margin-top:2px; }

/* Phone scanner states */
.pt-scanner-row { display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; }
.pt-scanner-title { font-size:13px; font-weight:700; color:var(--text); margin:0; }
.pt-scanner-sub   { font-size:11px; color:var(--text-dim); margin:2px 0 0; }
.pt-connected-pill {
    display:inline-flex; align-items:center; gap:6px;
    padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;
    background:var(--green-dim); color:var(--green);
}
.pt-connected-dot { width:7px; height:7px; border-radius:50%; background:var(--green); flex-shrink:0; }
.pt-expired-pill  {
    display:inline-flex; align-items:center; gap:6px;
    padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;
    background:var(--amber-dim); color:var(--amber);
}
.pt-scanner-btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:6px 14px; border-radius:8px; font-size:12px; font-weight:700;
    cursor:pointer; border:1px solid var(--border); text-decoration:none;
    background:#fff; color:var(--text); transition:all .15s; white-space:nowrap;
}
.pt-scanner-btn:hover   { border-color:var(--accent); color:var(--accent); }
.pt-scanner-btn.primary { background:var(--accent); color:#fff; border-color:var(--accent); }
.pt-scanner-btn.primary:hover { opacity:.88; }
.pt-scanner-btn.danger  { background:var(--red-dim); color:var(--red); border-color:rgba(225,29,72,.3); }
.pt-scanner-btn.danger:hover  { background:var(--red); color:#fff; }

/* QR section */
.pt-qr-wrap {
    display:grid; grid-template-columns:auto 1fr; gap:20px; align-items:start;
    margin-top:14px; padding-top:14px; border-top:1px solid var(--border);
}
.pt-qr-box { background:var(--surface2); padding:14px; border-radius:10px; border:1px solid var(--border); display:inline-block; }
.pt-manual-code {
    margin-top:8px; padding:10px 12px; background:var(--surface2);
    border-radius:8px; border:1px solid var(--border);
    font-size:18px; font-weight:800; font-family:var(--mono);
    letter-spacing:4px; text-align:center; color:var(--text);
}

/* Scan strip */
.pt-scan-strip {
    background:var(--surface); border:1px solid var(--border);
    border-radius:12px; padding:14px;
    border-left:3px solid var(--accent);
}
.pt-scan-label { font-size:10px; font-weight:700; letter-spacing:.7px; text-transform:uppercase; color:var(--text-dim); margin-bottom:8px; }
.pt-scan-row   { display:flex; gap:8px; }
.pt-scan-input {
    flex:1; padding:10px 14px; border:1.5px solid var(--border); border-radius:8px;
    font-size:14px; font-weight:700; font-family:var(--mono);
    background:#fff; color:var(--text); outline:none; transition:border-color .15s;
}
.pt-scan-input:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(99,102,241,.1); }
.pt-scan-btn {
    padding:10px 20px; background:var(--accent); color:#fff;
    border:none; border-radius:8px; font-size:12px; font-weight:700;
    cursor:pointer; white-space:nowrap; transition:opacity .15s;
}
.pt-scan-btn:hover { opacity:.88; }

/* Quantity panel */
.pt-qty-panel {
    background:var(--accent-dim); border:1.5px solid var(--accent);
    border-radius:12px; padding:16px;
}
.pt-qty-title { font-size:14px; font-weight:800; color:var(--accent); }
.pt-qty-sub   { font-size:11px; color:var(--text-dim); margin-top:2px; }
.pt-qty-input {
    width:100%; padding:10px 14px; border:2px solid var(--accent); border-radius:9px;
    font-size:28px; font-weight:800; text-align:center;
    background:#fff; color:var(--text); font-family:var(--mono);
    outline:none; box-sizing:border-box; margin-top:10px;
}
.pt-qty-hint  { font-size:11px; color:var(--text-dim); text-align:center; margin-top:5px; }

/* Product rows */
.pt-prod-row { background:#fff; border:1px solid var(--border); border-radius:10px; overflow:hidden; }
.pt-prod-row.complete { border-color:var(--green); }
.pt-prod-head {
    display:flex; align-items:center; justify-content:space-between;
    padding:9px 14px; background:var(--surface2); border-bottom:1px solid var(--border);
    gap:8px; flex-wrap:wrap;
}
.pt-prod-name   { font-size:13px; font-weight:700; color:var(--text); }
.pt-complete-badge {
    padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700;
    background:var(--green-dim); color:var(--green);
}
.pt-prod-body   { padding:12px 14px; }
.pt-prog-info   { display:flex; align-items:center; justify-content:space-between; margin-bottom:5px; }
.pt-prog-text   { font-size:12px; color:var(--text-dim); }
.pt-prog-nums   { font-size:12px; font-weight:700; color:var(--text); font-family:var(--mono); }
.pt-prog-bar-wrap { height:5px; background:var(--surface2); border-radius:4px; overflow:hidden; margin-bottom:12px; }
.pt-prog-bar    { height:100%; border-radius:4px; background:var(--accent); transition:width .3s; }
.pt-prog-bar.done { background:var(--green); }
.pt-boxes-label { font-size:10px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; color:var(--text-dim); margin-bottom:7px; }
.pt-boxes-grid  { display:grid; grid-template-columns:repeat(auto-fill, minmax(110px,1fr)); gap:6px; }
.pt-box-chip {
    padding:7px 9px; border:1px solid var(--border); border-radius:8px;
    cursor:pointer; transition:all .15s; background:#fff; text-align:left;
}
.pt-box-chip:hover { border-color:var(--accent); background:var(--accent-dim); }
.pt-box-code  { font-size:11px; font-weight:700; font-family:var(--mono); color:var(--text); }
.pt-box-meta  { font-size:10px; color:var(--text-dim); margin-top:1px; }
.pt-add-btn {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 12px; border-radius:7px; font-size:11px; font-weight:700;
    background:var(--accent); color:#fff; border:none; cursor:pointer;
    transition:opacity .15s; white-space:nowrap;
}
.pt-add-btn:hover { opacity:.88; }
.pt-no-boxes { font-size:11px; color:var(--text-dim); text-align:center; padding:10px 0; }

/* Assigned boxes table */
.pt-table { width:100%; border-collapse:collapse; min-width:420px; }
.pt-table thead th {
    padding:7px 12px; font-size:10px; font-weight:700; letter-spacing:.6px;
    text-transform:uppercase; color:var(--text-dim); border-bottom:1px solid var(--border); text-align:left;
}
.pt-table tbody tr { border-bottom:1px solid var(--border); }
.pt-table tbody tr:last-child { border-bottom:none; }
.pt-table tbody tr:hover { background:var(--surface2); }
.pt-table tbody td { padding:8px 12px; font-size:12px; color:var(--text); vertical-align:middle; }
.pt-box-code-cell { font-family:var(--mono); font-weight:700; font-size:12px; }
.pt-status-chip {
    display:inline-flex; padding:2px 7px; border-radius:5px;
    font-size:10px; font-weight:700; text-transform:uppercase;
    background:var(--green-dim); color:var(--green);
}
.pt-remove-btn {
    padding:3px 9px; border-radius:5px; font-size:11px; font-weight:600;
    background:var(--red-dim); color:var(--red); border:none; cursor:pointer; transition:all .15s;
}
.pt-remove-btn:hover { background:var(--red); color:#fff; }

/* Summary */
.pt-summary { display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:14px; }
.pt-sum-box  { text-align:center; padding:12px; background:var(--surface2); border-radius:8px; }
.pt-sum-v    { font-size:20px; font-weight:800; color:var(--text); font-family:var(--mono); line-height:1.1; }
.pt-sum-l    { font-size:10px; font-weight:600; letter-spacing:.6px; text-transform:uppercase; color:var(--text-dim); margin-top:2px; }

/* Ship button */
.pt-ship-btn {
    width:100%; padding:11px; background:var(--accent); color:#fff;
    border:none; border-radius:9px; font-size:13px; font-weight:700;
    cursor:pointer; display:flex; align-items:center; justify-content:center;
    gap:8px; transition:opacity .15s;
}
.pt-ship-btn:hover:not(:disabled) { opacity:.88; }
.pt-ship-btn:disabled { opacity:.4; cursor:not-allowed; }

/* Modal */
.pt-modal-overlay {
    position:fixed; inset:0; z-index:50; background:rgba(10,14,26,.6);
    backdrop-filter:blur(4px); display:flex; align-items:center;
    justify-content:center; padding:20px;
}
.pt-modal {
    background:#fff; border:1px solid var(--border); border-radius:14px;
    width:100%; max-width:480px; box-shadow:0 24px 60px rgba(0,0,0,.15);
}
.pt-modal-head { display:flex; align-items:center; justify-content:space-between; padding:14px 18px; border-bottom:1px solid var(--border); }
.pt-modal-head h3 { font-size:14px; font-weight:700; color:var(--text); margin:0; }
.pt-modal-close {
    width:28px; height:28px; border-radius:7px; background:var(--surface2);
    border:1px solid var(--border); display:flex; align-items:center; justify-content:center;
    cursor:pointer; color:var(--text-dim);
}
.pt-modal-close:hover { background:var(--border); }
.pt-modal-body { padding:18px; display:flex; flex-direction:column; gap:14px; }
.pt-modal-foot { display:flex; align-items:center; justify-content:flex-end; gap:8px; padding:14px 18px; border-top:1px solid var(--border); }
.pt-field-label { font-size:11px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; color:var(--text-dim); margin-bottom:5px; display:block; }
.pt-select {
    width:100%; padding:9px 12px; border:1.5px solid var(--border); border-radius:8px;
    font-size:13px; background:#fff; color:var(--text); outline:none;
}
.pt-select:focus { border-color:var(--accent); }
.pt-btn {
    display:inline-flex; align-items:center; justify-content:center; gap:6px;
    padding:8px 16px; border-radius:8px; font-size:12px; font-weight:700;
    cursor:pointer; border:1px solid var(--border); transition:all .15s;
}
.pt-btn.primary { background:var(--accent); color:#fff; border-color:var(--accent); }
.pt-btn.primary:hover { opacity:.88; }
.pt-btn.outline { background:#fff; color:var(--text); }
.pt-btn.outline:hover { border-color:var(--accent); color:var(--accent); }

/* Responsive */
@media(max-width:640px) {
    .pt-meta-grid { grid-template-columns:1fr 1fr; }
    .pt-summary   { grid-template-columns:1fr; }
    .pt-qr-wrap   { grid-template-columns:1fr; }
}
@media(max-width:480px) {
    .pt-scan-row  { flex-direction:column; }
    .pt-qty-input { font-size:22px; }
}
</style>

<div class="pt-wrap">

    {{-- Flash messages --}}
    @if(session()->has('success'))
    <div class="pt-flash ok">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session()->has('error'))
    <div class="pt-flash err">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Transfer header --}}
    <div class="pt-card">
        <div class="pt-card-head">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                <span class="pt-num">{{ $transfer->transfer_number }}</span>
                @php
                    $sc = match($transfer->status->value) {
                        'approved'   => ['bg'=>'rgba(99,102,241,.1)',  'c'=>'var(--accent)'],
                        'in_transit' => ['bg'=>'rgba(139,92,246,.1)',  'c'=>'#8b5cf6'],
                        default      => ['bg'=>'rgba(99,102,241,.1)',  'c'=>'var(--accent)'],
                    };
                @endphp
                <span class="pt-badge" style="background:{{ $sc['bg'] }};color:{{ $sc['c'] }}">
                    <span style="width:5px;height:5px;border-radius:50%;background:currentColor;"></span>
                    {{ $transfer->status->label() }}
                </span>
            </div>
            <span style="font-size:11px;color:var(--text-dim);">{{ $transfer->requested_at?->format('d M Y') }}</span>
        </div>
        <div class="pt-card-body">
            <div class="pt-meta-grid">
                <div>
                    <div class="pt-meta-label">Destination</div>
                    <div class="pt-meta-value">{{ $transfer->toShop->name }}</div>
                </div>
                <div>
                    <div class="pt-meta-label">Requested By</div>
                    <div class="pt-meta-value">{{ $transfer->requestedBy->name }}</div>
                </div>
                <div>
                    <div class="pt-meta-label">Approved</div>
                    <div class="pt-meta-value">{{ $transfer->reviewed_at?->format('d M Y, H:i') ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Phone scanner --}}
    <div class="pt-card">
        <div class="pt-card-head">
            <div>
                <h3>Phone Scanner</h3>
                <p>Use your phone as a dedicated barcode scanner</p>
            </div>
        </div>
        <div class="pt-card-body">
            @if(!$showScannerQR)
                {{-- Disabled state --}}
                <div class="pt-scanner-row">
                    <div>
                        <p class="pt-scanner-title">Not connected</p>
                        <p class="pt-scanner-sub">Enable phone scanning for hands-free box processing</p>
                    </div>
                    <button type="button" wire:click="generateScannerSession" class="pt-scanner-btn primary">
                        <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12" y2="18"/></svg>
                        Enable Phone Scanner
                    </button>
                </div>

            @elseif($phoneConnected)
                {{-- Connected state --}}
                <div class="pt-scanner-row">
                    <div style="display:flex;align-items:center;gap:8px">
                        <span class="pt-connected-pill">
                            <span class="pt-connected-dot"></span>
                            Phone Connected
                        </span>
                        <span style="font-size:11px;color:var(--text-dim)">Point camera at barcodes to scan</span>
                    </div>
                    <div style="display:flex;gap:6px">
                        <button type="button" wire:click="$toggle('showScannerQR')" class="pt-scanner-btn">Show QR</button>
                        <button type="button" wire:click="closeScannerSession" class="pt-scanner-btn danger">Disconnect</button>
                    </div>
                </div>
                <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border);
                            display:flex;justify-content:space-between;font-size:11px;color:var(--text-dim)">
                    <span>Code: <strong style="font-family:var(--mono);color:var(--text)">{{ $scannerSession->session_code }}</strong></span>
                    <span>Expires {{ $scannerSession->expires_at->diffForHumans() }}</span>
                </div>
                <div wire:poll.2s="checkForScans"></div>

            @elseif($scannerSession && $scannerSession->expires_at->isPast())
                {{-- Expired state --}}
                <div class="pt-scanner-row">
                    <div style="display:flex;align-items:center;gap:8px">
                        <span class="pt-expired-pill">Session Expired</span>
                        <span style="font-size:11px;color:var(--text-dim)">Your phone scanner session has expired</span>
                    </div>
                    <button type="button" wire:click="generateScannerSession" class="pt-scanner-btn primary">Reconnect Phone</button>
                </div>

            @elseif($showScannerQR && $scannerSession)
                {{-- QR shown state --}}
                @php $scannerUrl = config('app.url') . '/scanner?code=' . $scannerSession->session_code; @endphp
                <div class="pt-scanner-row">
                    <div>
                        <p class="pt-scanner-title">Waiting for phone connection</p>
                        <p class="pt-scanner-sub">Scan the QR code with your phone camera</p>
                    </div>
                    <button type="button" wire:click="closeScannerSession" class="pt-scanner-btn danger">Cancel</button>
                </div>
                <div class="pt-qr-wrap">
                    <div class="pt-qr-box">
                        {!! QrCode::size(140)->generate($scannerUrl) !!}
                    </div>
                    <div>
                        <p style="font-size:11px;font-weight:700;color:var(--text);margin:0 0 8px;">Setup steps:</p>
                        <ol style="font-size:12px;color:var(--text-dim);margin:0;padding-left:16px;line-height:1.8">
                            <li>Open your phone camera app</li>
                            <li>Point at the QR code</li>
                            <li>Tap the notification to open the scanner</li>
                            <li>Start scanning box barcodes</li>
                        </ol>
                        <p style="font-size:11px;color:var(--text-dim);margin:10px 0 4px;">Or go to <strong style="color:var(--text)">{{ url('/scanner') }}</strong> and enter:</p>
                        <div class="pt-manual-code">{{ $scannerSession->session_code }}</div>
                        <p style="font-size:11px;color:var(--text-dim);margin:8px 0 0;">Expires {{ $scannerSession->expires_at->diffForHumans() }}</p>
                    </div>
                </div>
                <div wire:poll.2s="checkForScans"></div>
            @endif
        </div>
    </div>

    {{-- Scan input --}}
    <div class="pt-scan-strip">
        <div class="pt-scan-label">Scan or enter box barcode</div>
        <div class="pt-scan-row">
            <input type="text"
                   wire:model="scanInput"
                   wire:keydown.enter="scanBox"
                   placeholder="Box code (e.g. BOX-001)"
                   class="pt-scan-input"
                   autofocus>
            <button type="button" wire:click="scanBox" class="pt-scan-btn">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:4px"><path d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                Scan
            </button>
        </div>
        @error('scanInput')
            <p style="font-size:11px;color:var(--red);margin-top:5px">{{ $message }}</p>
        @enderror
    </div>

    {{-- Quantity panel --}}
    @if($showQuantityPanel)
    <div class="pt-qty-panel">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px">
            <div>
                <div class="pt-qty-title">{{ $pendingProductName }}</div>
                <div class="pt-qty-sub">
                    {{ $pendingAlreadyAssigned }} already assigned &nbsp;·&nbsp;
                    <strong style="color:var(--text)">{{ $pendingMaxQty }} box{{ $pendingMaxQty === 1 ? '' : 'es' }} still needed</strong>
                </div>
            </div>
            <button wire:click="closeQuantityPanel" style="background:none;border:none;font-size:18px;color:var(--text-dim);cursor:pointer;line-height:1;padding:2px 4px">×</button>
        </div>
        <label style="display:block;font-size:10px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.6px;margin-bottom:5px">
            How many boxes to add now?
        </label>
        <input wire:model.live="pendingQty"
               wire:keydown.enter="confirmScannedQuantity"
               type="number" min="1" max="{{ $pendingMaxQty }}"
               class="pt-qty-input">
        @error('pendingQty')
            <div style="font-size:11px;color:var(--red);margin-top:4px">{{ $message }}</div>
        @enderror
        @php $afterAdd = max(0, $pendingMaxQty - (int) $pendingQty); @endphp
        <div class="pt-qty-hint">After adding: <strong>{{ $afterAdd }}</strong> box{{ $afterAdd === 1 ? '' : 'es' }} still needed</div>
        <div style="display:flex;gap:8px;margin-top:12px">
            <button wire:click="closeQuantityPanel"
                    style="flex:1;padding:9px;border-radius:9px;border:1px solid var(--border);background:#fff;font-size:12px;font-weight:700;cursor:pointer;color:var(--text)">
                Continue Scanning
            </button>
            <button wire:click="confirmScannedQuantity"
                    wire:loading.attr="disabled" wire:target="confirmScannedQuantity"
                    style="flex:2;padding:9px;border-radius:9px;border:none;background:var(--accent);color:#fff;font-size:12px;font-weight:700;cursor:pointer">
                <span wire:loading.remove wire:target="confirmScannedQuantity">Add {{ $pendingQty }} Box{{ (int) $pendingQty === 1 ? '' : 'es' }}</span>
                <span wire:loading wire:target="confirmScannedQuantity">Adding…</span>
            </button>
        </div>
    </div>
    @endif

    {{-- Products to pack --}}
    <div class="pt-card">
        <div class="pt-card-head">
            <h3>Products to Pack</h3>
        </div>
        <div class="pt-card-body" style="display:flex;flex-direction:column;gap:10px">
            @foreach($items as $item)
                @php
                    $boxesRequested = $item['boxes_requested'];
                    $boxesAssigned  = $item['boxes_assigned'];
                    $progress       = $boxesRequested > 0 ? ($boxesAssigned / $boxesRequested) * 100 : 0;
                    $isComplete     = $boxesAssigned >= $boxesRequested;
                    $availForProd   = $availableBoxes[$item['product_id']] ?? collect();
                @endphp
                <div class="pt-prod-row {{ $isComplete ? 'complete' : '' }}">
                    <div class="pt-prod-head">
                        <div style="display:flex;align-items:center;gap:7px">
                            <span class="pt-prod-name">{{ $item['product_name'] }}</span>
                            @if($isComplete)
                                <span class="pt-complete-badge">Complete</span>
                            @endif
                        </div>
                        @if(!$isComplete && $availForProd->isNotEmpty())
                        <button type="button" wire:click="addBoxToProduct({{ $item['product_id'] }})" class="pt-add-btn">
                            <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Quick Add
                        </button>
                        @endif
                    </div>
                    <div class="pt-prod-body">
                        <div class="pt-prog-info">
                            <span class="pt-prog-text">Progress</span>
                            <span class="pt-prog-nums {{ $isComplete ? 'ok' : '' }}" style="{{ $isComplete ? 'color:var(--green)' : '' }}">
                                {{ $boxesAssigned }} / {{ $boxesRequested }} boxes
                            </span>
                        </div>
                        <div class="pt-prog-bar-wrap">
                            <div class="pt-prog-bar {{ $isComplete ? 'done' : '' }}" style="width:{{ min($progress, 100) }}%"></div>
                        </div>

                        @if($availForProd->isNotEmpty())
                        <div class="pt-boxes-label">Available boxes (first 5)</div>
                        <div class="pt-boxes-grid">
                            @foreach($availForProd as $box)
                            <button type="button"
                                    wire:click="scanInput = '{{ $box->box_code }}'; scanBox();"
                                    class="pt-box-chip">
                                <div class="pt-box-code">{{ $box->box_code }}</div>
                                <div class="pt-box-meta">{{ $box->status->label() }} · {{ $box->items_remaining }} items</div>
                            </button>
                            @endforeach
                        </div>
                        @else
                        <div class="pt-no-boxes">No more available boxes for this product</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Assigned boxes --}}
    @if(!empty($assignedBoxes))
    <div class="pt-card">
        <div class="pt-card-head">
            <h3>Assigned Boxes</h3>
            <p>{{ count($assignedBoxes) }} box{{ count($assignedBoxes) === 1 ? '' : 'es' }} assigned</p>
        </div>
        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
            <table class="pt-table">
                <thead>
                    <tr>
                        <th>Box Code</th>
                        <th>Product</th>
                        <th>Status</th>
                        <th>Items</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignedBoxes as $box)
                    <tr>
                        <td><span class="pt-box-code-cell">{{ $box['box_code'] }}</span></td>
                        <td>{{ $box['product_name'] }}</td>
                        <td><span class="pt-status-chip">{{ $box['status'] }}</span></td>
                        <td>{{ number_format($box['items_remaining']) }}</td>
                        <td style="text-align:right">
                            <button type="button" wire:click="removeBox({{ $box['id'] }})" class="pt-remove-btn">Remove</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Summary + Ship --}}
    <div class="pt-card">
        <div class="pt-card-body">
            @php
                $totalItemsAssigned = array_sum(array_column($items, 'quantity_assigned'));
                $productsComplete   = count(array_filter($items, fn($i) => $i['boxes_assigned'] >= $i['boxes_requested']));
            @endphp
            <div class="pt-summary">
                <div class="pt-sum-box">
                    <div class="pt-sum-v">{{ count($assignedBoxes) }}</div>
                    <div class="pt-sum-l">Boxes Assigned</div>
                </div>
                <div class="pt-sum-box">
                    <div class="pt-sum-v">{{ number_format($totalItemsAssigned) }}</div>
                    <div class="pt-sum-l">Total Items</div>
                </div>
                <div class="pt-sum-box">
                    <div class="pt-sum-v" style="{{ $productsComplete === count($items) ? 'color:var(--green)' : '' }}">
                        {{ $productsComplete }}/{{ count($items) }}
                    </div>
                    <div class="pt-sum-l">Products Done</div>
                </div>
            </div>
            <button type="button" wire:click="openShipModal"
                    @if(empty($assignedBoxes)) disabled @endif
                    class="pt-ship-btn">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                Ship Transfer
            </button>
        </div>
    </div>

    {{-- Ship modal --}}
    @if($showShipModal)
    <div class="pt-modal-overlay" wire:click.self="closeShipModal">
        <div class="pt-modal">
            <div class="pt-modal-head">
                <h3>Ship Transfer</h3>
                <button type="button" class="pt-modal-close" wire:click="closeShipModal">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="pt-modal-body">
                <p style="font-size:12px;color:var(--text-dim);margin:0">
                    You are about to ship <strong style="color:var(--text)">{{ count($assignedBoxes) }} boxes</strong>
                    to <strong style="color:var(--text)">{{ $transfer->toShop->name }}</strong>.
                </p>
                <div>
                    <label class="pt-field-label">Select Transporter <span style="color:var(--text-dim);font-weight:400;text-transform:none;letter-spacing:0">(optional)</span></label>
                    <select wire:model="transporterId" class="pt-select">
                        <option value="">No Transporter</option>
                        @foreach($transporters as $transporter)
                        <option value="{{ $transporter->id }}">
                            {{ $transporter->name }}{{ $transporter->vehicle_number ? ' — '.$transporter->vehicle_number : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="pt-modal-foot">
                <button type="button" wire:click="closeShipModal" class="pt-btn outline">Cancel</button>
                <button type="button" wire:click="ship" class="pt-btn primary">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    Confirm &amp; Ship
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
