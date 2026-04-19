@php use App\Enums\TransferStatus; @endphp
@if($sessionBlocked)
    <x-session-gate-blocked
        :reason="$sessionBlockReason"
        :session-date="$blockedSessionDate"
        :session-id="$blockedSessionId"
    />
@else

@push('styles')
<style>
/* ── Receive Transfer — Design System (Matching Pack Transfer) ── */
:root {
    --amber: #d97706;
    --amber-dim: #fef3c7;
    --green-dim: #dcfce7;
}
.pt-wrap { display:flex;flex-direction:column;gap:20px;font-family:var(--font); }

.pt-card {
    background:var(--surface);border:1.5px solid var(--border);
    border-radius:12px;overflow:hidden;
    box-shadow:0 1px 3px rgba(0,0,0,.04);
}
.pt-card-head {
    padding:16px 22px;border-bottom:1px solid var(--border);
    display:flex;align-items:center;justify-content:space-between;gap:12px;
    background:var(--surface);
}
.pt-card-title {
    font-size:11px;font-weight:700;letter-spacing:.6px;
    text-transform:uppercase;color:var(--text-sub);
    display:flex;align-items:center;gap:6px;
}
.pt-card-body  { padding:22px; }

/* Route strip */
.pt-route {
    display:flex;align-items:center;
    background:var(--surface2);border-radius:10px;
    padding:14px 18px;border:1px solid var(--border);gap:0;
}
.pt-route-node  { flex:1; }
.pt-route-label { font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--text-dim); }
.pt-route-name  { font-size:14px;font-weight:700;color:var(--text);margin-top:3px; }
.pt-route-arrow {
    width:34px;height:34px;border-radius:50%;
    background:var(--accent-dim);color:var(--accent);
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}

/* Scan bar */
.pt-scan-bar {
    display:flex;gap:10px;align-items:center;
    padding:18px;background:var(--surface2);border-radius:10px;
    border:2px dashed rgba(59,111,212,.25);
}
.pt-scan-input {
    flex:1;padding:11px 14px;
    background:var(--surface);color:var(--text);
    border:1.5px solid var(--border-hi);border-radius:8px;
    font-size:15px;font-family:var(--mono);font-weight:600;outline:none;
    transition:border-color var(--tr),box-shadow var(--tr);
}
.pt-scan-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow); }
.pt-scan-input::placeholder { color:var(--text-dim);font-weight:500; }
.pt-scan-btn {
    padding:11px 20px;background:var(--accent);color:#fff;
    border:none;border-radius:8px;font-size:14px;font-weight:700;
    font-family:var(--font);cursor:pointer;white-space:nowrap;
    transition:background var(--tr),transform var(--tr);
    box-shadow:0 2px 6px var(--accent-glow);
}
.pt-scan-btn:hover { background:#2d5dbf; }
.pt-scan-btn:active { transform:scale(.98); }

/* Product summary rows */
.pt-product-row {
    border:1.5px solid var(--border);border-radius:10px;overflow:hidden;
    background:var(--surface);
    box-shadow:0 1px 3px rgba(0,0,0,.04);
}
.pt-product-head {
    display:flex;align-items:center;justify-content:space-between;
    padding:12px 16px;background:var(--surface2);border-bottom:1px solid var(--border);
}
.pt-product-name { font-size:14px;font-weight:700;color:var(--text);line-height:1.4; }
.pt-progress-wrap {
    display:flex;align-items:center;gap:10px;
    padding:14px 16px;
}
.pt-progress-bar-bg {
    flex:1;height:8px;border-radius:999px;
    background:var(--surface3);overflow:hidden;
    box-shadow:inset 0 1px 2px rgba(0,0,0,.05);
}
.pt-progress-bar { height:100%;border-radius:999px;transition:width .4s var(--ease); }
.pt-progress-bar.complete { background:linear-gradient(90deg, var(--green) 0%, #0ea97e 100%); }
.pt-progress-bar.partial  { background:linear-gradient(90deg, var(--amber) 0%, #f59e0b 100%); }
.pt-progress-bar.empty    { background:var(--surface3); }
.pt-progress-label {
    font-size:12px;font-weight:700;font-family:var(--mono);
    color:var(--text-sub);white-space:nowrap;
    padding:2px 8px;background:var(--surface2);border-radius:6px;
}

/* Received boxes list */
.pt-box-item {
    display:flex;align-items:center;gap:12px;
    padding:10px 16px;border-bottom:1px solid var(--border);
    transition:background var(--tr);
}
.pt-box-item:last-child { border-bottom:none; }
.pt-box-item:hover { background:var(--surface2); }
.pt-box-code {
    font-size:12px;font-family:var(--mono);font-weight:700;
    color:var(--accent);background:var(--accent-dim);
    padding:4px 10px;border-radius:6px;white-space:nowrap;
    border:1px solid rgba(59,111,212,.15);
}
.pt-box-product { font-size:13px;color:var(--text);font-weight:600;flex:1;line-height:1.4; }
.pt-box-items   { font-size:12px;color:var(--text-dim);font-family:var(--mono);font-weight:600; }
.pt-box-damaged {
    font-size:11px;font-weight:700;
    color:#dc2626;background:#fee2e2;
    padding:3px 8px;border-radius:5px;
}

/* Scanned boxes - special styling for receive */
.pt-scanned-box {
    display:flex;align-items:center;gap:12px;
    padding:12px 16px;border-bottom:1px solid var(--border);
    transition:background var(--tr);
}
.pt-scanned-box:last-child { border-bottom:none; }
.pt-scanned-box.damaged { background:#fef2f2; }
.pt-scanned-box.good { background:#f0fdf4; }
.pt-damage-btn {
    padding:6px 14px;
    border-radius:7px;font-weight:600;font-size:12px;
    border:none;cursor:pointer;transition:all 0.2s;
}
.pt-damage-btn.inactive {
    background:var(--surface3);color:var(--text-sub);
}
.pt-damage-btn.inactive:hover {
    background:#d1d5db;
}
.pt-damage-btn.active {
    background:#dc2626;color:white;
}
.pt-damage-input {
    flex:1;padding:8px 12px;
    border:1.5px solid #fca5a5;border-radius:7px;
    font-size:13px;outline:none;
}
.pt-damage-input:focus {
    border-color:#dc2626;box-shadow:0 0 0 3px rgba(220,38,38,0.1);
}
.pt-remove-btn {
    color:#dc2626;font-weight:600;font-size:12px;
    background:none;border:none;cursor:pointer;padding:4px 8px;
    transition:color 0.2s;
}
.pt-remove-btn:hover { color:#991b1b; }

/* Flash */
.pt-flash {
    display:flex;align-items:flex-start;gap:10px;
    padding:12px 16px;border-radius:10px;border:1px solid;font-size:14px;
    line-height:1.5;
}
.pt-flash svg { flex-shrink:0; }
.pt-flash.success { background:var(--success-dim);border-color:rgba(22,163,74,.25);color:#14532d; }
.pt-flash.error   { background:var(--red-dim);    border-color:rgba(225,29,72,.25); color:#7f1d1d; }
.pt-flash.info    { background:#eff6ff;border-color:rgba(59,130,246,.25);color:#1e3a8a; }

/* Buttons */
.pt-btn {
    display:inline-flex;align-items:center;justify-content:center;gap:8px;
    padding:11px 24px;border-radius:9px;border:none;cursor:pointer;
    font-size:14px;font-weight:700;font-family:var(--font);
    transition:background var(--tr),box-shadow var(--tr),transform var(--tr),opacity var(--tr);
}
.pt-btn:active { transform:scale(.97); }
.pt-btn:disabled { opacity:.4;cursor:not-allowed;transform:none; }
.pt-btn-primary {
    background:var(--accent);color:#fff;
    box-shadow:0 2px 8px var(--accent-glow);width:100%;
    min-height:44px;
}
.pt-btn-primary:hover:not(:disabled) { background:#2d5dbf;box-shadow:0 4px 14px var(--accent-glow); }
.pt-btn-primary:active:not(:disabled) { transform:scale(.98); }

@media(max-width:768px) {
    .pt-card { border-radius:10px; }
    .pt-card-head { padding:16px; flex-wrap:wrap; gap:10px; }
    .pt-card-body { padding:18px; }
    .pt-card-title { font-size:12px; }

    /* Route */
    .pt-route { flex-direction:column; gap:14px; text-align:center; padding:16px; }
    .pt-route-node { text-align:center !important; }
    .pt-route-arrow { transform:rotate(90deg); margin:4px auto; }
    .pt-route-label { font-size:11px; }
    .pt-route-name { font-size:15px; }

    /* Scan section */
    .pt-scan-bar { gap:10px; padding:16px; }
    .pt-scan-input { font-size:16px; padding:12px 14px; }
    .pt-scan-btn { padding:12px 20px; font-size:15px; font-weight:700; }

    /* Progress */
    .pt-product-head { padding:14px 16px; flex-wrap:wrap; gap:10px; }
    .pt-product-name { font-size:15px; }
    .pt-progress-wrap { flex-direction:column; gap:10px; align-items:stretch; }
    .pt-progress-label { text-align:right; font-size:13px; }

    /* Boxes */
    .pt-box-item, .pt-scanned-box { padding:12px 14px; flex-wrap:wrap; gap:10px; }
    .pt-box-code { font-size:12px; padding:4px 10px; }
    .pt-box-product { font-size:14px; }
    .pt-box-items { font-size:13px; }

    /* Buttons */
    .pt-btn-primary { width:100%; font-size:16px; padding:13px 24px; }
}

@media(max-width:640px) {
    .pt-wrap { gap:14px; }
    .pt-card { border-radius:8px; }
    .pt-card-head { padding:14px; gap:8px; }
    .pt-card-body { padding:16px; }

    /* Header */
    .pt-card-head > div > span:first-child { font-size:18px; }
    .pt-card-head > div > span:nth-child(2) { font-size:10px; padding:3px 10px; }
    .pt-card-head > span { font-size:11px; }

    /* Route */
    .pt-route { padding:14px; gap:10px; }
    .pt-route-label { font-size:10px; }
    .pt-route-name { font-size:14px; }

    /* Scan */
    .pt-scan-bar { padding:14px; gap:10px; }
    .pt-scan-input { font-size:15px; padding:11px 13px; }
    .pt-scan-btn { padding:11px 18px; font-size:14px; }

    /* Flash messages */
    .pt-flash { font-size:13px; padding:10px 14px; }

    /* Progress */
    .pt-product-name { font-size:14px; }
    .pt-product-head span { font-size:11px !important; }
    .pt-progress-label { font-size:12px; }

    /* Boxes */
    .pt-box-item, .pt-scanned-box { padding:10px 12px; gap:8px; }
    .pt-box-code { font-size:11px; padding:3px 9px; }
    .pt-box-product { font-size:13px; }
    .pt-box-items { font-size:12px; }

    /* Buttons */
    .pt-btn-primary { font-size:15px; padding:12px 22px; }

    /* Helper text */
    div[style*="font-size:12px"] p { font-size:13px !important; line-height:1.5; }
}

@keyframes spin { to { transform:rotate(360deg) } }
</style>
@endpush

<div class="pt-wrap">

    {{-- Flash Messages --}}
    @if(session()->has('success'))
        <div class="pt-flash success">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="flex-shrink:0;margin-top:2px">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @foreach(['scan_success','scan_error','error','info'] as $flashKey)
        @if(session()->has($flashKey))
            <div class="pt-flash {{ str_contains($flashKey,'error') ? 'error' : (str_contains($flashKey,'info') ? 'info' : 'success') }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="flex-shrink:0;margin-top:2px">
                    @if(str_contains($flashKey,'error'))
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @elseif(str_contains($flashKey,'info'))
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @endif
                </svg>
                {{ session($flashKey) }}
            </div>
        @endif
    @endforeach

    {{-- Transfer Header --}}
    <div class="pt-card">
        <div class="pt-card-head" style="min-height:auto">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;flex:1">
                <span style="font-size:20px;font-weight:800;color:var(--text);line-height:1.2">{{ $transfer->transfer_number }}</span>
                <span style="font-size:11px;font-weight:700;padding:3px 12px;border-radius:999px;
                             background:var(--accent-dim);color:var(--accent);border:1px solid rgba(59,111,212,.2);white-space:nowrap">
                    {{ $transfer->status->label() }}
                </span>
            </div>
            <span style="font-size:12px;color:var(--text-dim);white-space:nowrap">{{ $transfer->shipped_at?->format('M d, Y') }}</span>
        </div>
        <div class="pt-card-body">
            <div class="pt-route">
                <div class="pt-route-node">
                    <div class="pt-route-label">From Warehouse</div>
                    <div class="pt-route-name">{{ $transfer->fromWarehouse->name }}</div>
                </div>
                <div class="pt-route-arrow">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </div>
                <div class="pt-route-node" style="text-align:right">
                    <div class="pt-route-label">To Shop</div>
                    <div class="pt-route-name">{{ $transfer->toShop->name }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scan Section --}}
    <div class="pt-card">
        <div class="pt-card-body">
            <div class="pt-scan-bar">
                <svg width="20" height="20" fill="none" stroke="var(--text-dim)" viewBox="0 0 24 24" stroke-width="2" style="flex-shrink:0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                <input type="text"
                       wire:model="scanInput"
                       wire:keydown.enter="scanBox"
                       class="pt-scan-input"
                       placeholder="Scan or type barcode, then press Enter…"
                       autofocus>
                <button type="button" wire:click="scanBox" class="pt-scan-btn">
                    Receive
                </button>
            </div>
        </div>
    </div>

    {{-- ── Quantity Popup ─────────────────────────────────── --}}
    @if($showQuantityPanel)
    <div
        x-data
        x-on:keydown.escape.window="$wire.closeQuantityPanel()"
        style="position:fixed;inset:0;z-index:900;display:flex;align-items:center;
               justify-content:center;background:rgba(15,18,36,.55);backdrop-filter:blur(3px)"
    >
        <div style="background:var(--surface);border-radius:18px;width:340px;max-width:92vw;
                    padding:28px 28px 24px;box-shadow:0 24px 64px rgba(0,0,0,.26);
                    border:1px solid var(--border)"
             x-on:click.stop>

            {{-- Product name --}}
            <div style="font-size:16px;font-weight:800;color:var(--text);
                        margin-bottom:4px;text-align:center">
                📦 {{ $pendingProductName }}
            </div>

            {{-- Progress subtitle --}}
            <div style="font-size:12px;color:var(--text-sub);text-align:center;margin-bottom:20px">
                {{ $pendingAlreadyScanned }} already scanned
                &nbsp;·&nbsp;
                <strong style="color:var(--text)">
                    {{ $pendingMaxQty }} box{{ $pendingMaxQty === 1 ? '' : 'es' }} remaining
                </strong>
            </div>

            {{-- Number input --}}
            <input
                wire:model.live="pendingQty"
                wire:keydown.enter="confirmScannedQuantity"
                x-on:keydown.escape.stop="$wire.closeQuantityPanel()"
                type="number"
                min="1"
                max="{{ $pendingMaxQty }}"
                x-init="$nextTick(() => $el.select())"
                style="width:100%;padding:14px;border:2px solid var(--accent);
                       border-radius:12px;font-size:32px;font-weight:800;text-align:center;
                       background:var(--surface);color:var(--text);font-family:var(--mono);
                       outline:none;box-sizing:border-box;display:block"
            >

            @error('pendingQty')
                <div style="font-size:11px;color:var(--red);margin-top:6px;text-align:center">
                    {{ $message }}
                </div>
            @enderror

            {{-- Live indicator --}}
            @php $afterAdd = max(0, $pendingMaxQty - (int) $pendingQty); @endphp
            <div style="font-size:11px;color:var(--text-dim);margin-top:10px;text-align:center">
                After confirming:
                <strong style="color:{{ $afterAdd === 0 ? 'var(--green)' : 'var(--text)' }}">
                    {{ $afterAdd }} box{{ $afterAdd === 1 ? '' : 'es' }} still needed
                </strong>
            </div>

            {{-- Hint --}}
            <div style="font-size:10px;color:var(--text-dim);text-align:center;margin-top:14px;
                        padding-top:14px;border-top:1px solid var(--border)">
                Press <kbd style="background:var(--surface2);border:1px solid var(--border);
                                  border-radius:4px;padding:1px 5px;font-size:10px">Enter</kbd>
                to confirm &nbsp;·&nbsp;
                <kbd style="background:var(--surface2);border:1px solid var(--border);
                            border-radius:4px;padding:1px 5px;font-size:10px">Esc</kbd>
                to cancel
            </div>
        </div>
    </div>
    @endif

    {{-- Receiving Progress per Product --}}
    @if(isset($receivingSummary) && count($receivingSummary) > 0)
    <div class="pt-card">
        <div class="pt-card-head">
            <span class="pt-card-title">Receiving Progress</span>
            @php
                $totalReceived = collect($receivingSummary)->sum('boxes_received');
                $totalShipped = collect($receivingSummary)->sum('boxes_shipped');
                $totalRemaining = $totalShipped - $totalReceived;
            @endphp
            <span style="font-size:13px;font-weight:700;font-family:var(--mono);
                         background:{{ $totalRemaining > 0 ? 'var(--amber-dim)' : 'var(--green-dim)' }};
                         color:{{ $totalRemaining > 0 ? 'var(--amber)' : 'var(--green)' }};
                         padding:4px 12px;border-radius:6px;border:1px solid {{ $totalRemaining > 0 ? 'rgba(217,119,6,.2)' : 'rgba(22,163,74,.2)' }}">
                {{ $totalRemaining > 0 ? $totalRemaining . ' remaining' : 'Complete' }}
            </span>
        </div>
        <div class="pt-card-body" style="display:flex;flex-direction:column;gap:12px">
            @foreach($receivingSummary as $summary)
                @php
                    $pct = $summary['boxes_shipped'] > 0
                        ? min(100, round($summary['boxes_received'] / $summary['boxes_shipped'] * 100))
                        : 0;
                    $remaining = $summary['boxes_shipped'] - $summary['boxes_received'];
                    $barClass = $summary['complete'] ? 'complete' : ($summary['boxes_received'] > 0 ? 'partial' : 'empty');
                @endphp
                <div class="pt-product-row">
                    <div class="pt-product-head">
                        <div style="display:flex;align-items:center;gap:10px">
                            <span class="pt-product-name">{{ $summary['product_name'] }}</span>
                            @if(!$summary['complete'])
                                <span style="font-size:12px;font-weight:700;font-family:var(--mono);
                                             color:var(--amber);background:var(--amber-dim);
                                             padding:2px 8px;border-radius:5px">
                                    {{ $remaining }} left
                                </span>
                            @endif
                        </div>
                        <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim);
                                     background:var(--surface3);padding:2px 8px;border-radius:5px">
                            {{ $summary['barcode'] }}
                        </span>
                    </div>
                    <div class="pt-progress-wrap">
                        <div class="pt-progress-bar-bg">
                            <div class="pt-progress-bar {{ $barClass }}" style="width:{{ $pct }}%"></div>
                        </div>
                        <span class="pt-progress-label" style="{{ $summary['complete'] ? 'color:var(--green)' : '' }}">
                            {{ $summary['boxes_received'] }} / {{ $summary['boxes_shipped'] }} boxes
                        </span>
                        @if($summary['complete'])
                            <svg width="16" height="16" fill="none" stroke="var(--green)" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Expected Boxes List --}}
    @if(isset($expectedBoxesList) && count($expectedBoxesList) > 0)
    <div class="pt-card">
        <div class="pt-card-head">
            <span class="pt-card-title">Expected Boxes</span>
            <span style="font-size:12px;font-weight:700;font-family:var(--mono);
                         background:var(--accent-dim);color:var(--accent);padding:3px 10px;border-radius:6px">
                {{ collect($expectedBoxesList)->where('is_received', false)->count() }} / {{ count($expectedBoxesList) }} remaining
            </span>
        </div>
        <div style="divide-y:var(--border)">
            @foreach($expectedBoxesList as $box)
                <div class="pt-box-item">
                    <span class="pt-box-code">{{ $box['box_code'] }}</span>
                    <span class="pt-box-product">{{ $box['product_name'] }}</span>
                    <span class="pt-box-items">{{ $box['items'] }} items</span>
                    @if($box['is_received'])
                        @if($box['is_damaged'])
                            <span class="pt-box-damaged">DAMAGED</span>
                        @else
                            <svg width="14" height="14" fill="none" stroke="var(--green)" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Scanned Boxes (with damage marking) --}}
    @if(!empty($scannedBoxes))
        <div class="pt-card">
            <div class="pt-card-head">
                <span class="pt-card-title">Scanned Boxes</span>
                <span style="font-size:12px;font-weight:700;font-family:var(--mono);
                             background:var(--green-dim);color:var(--green);padding:3px 10px;border-radius:6px">
                    {{ count($scannedBoxes) }} confirmed
                </span>
            </div>
            <div>
                @foreach($scannedBoxes as $boxId => $box)
                    <div class="pt-scanned-box {{ $box['is_damaged'] ? 'damaged' : 'good' }}">
                        <div style="flex:1;display:flex;flex-direction:column;gap:8px">
                            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                                <span class="pt-box-code">{{ $box['box_code'] }}</span>
                                <span class="pt-box-product">{{ $box['product_name'] }}</span>
                            </div>

                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                                <button type="button"
                                        wire:click="markAsDamaged({{ $boxId }}, {{ $box['is_damaged'] ? 'false' : 'true' }})"
                                        class="pt-damage-btn {{ $box['is_damaged'] ? 'active' : 'inactive' }}">
                                    {{ $box['is_damaged'] ? '✓ Damaged' : 'Mark as Damaged' }}
                                </button>

                                @if($box['is_damaged'])
                                    <input type="text"
                                           wire:model.blur="scannedBoxes.{{ $boxId }}.damage_notes"
                                           wire:change="updateDamageNotes({{ $boxId }}, $event.target.value)"
                                           placeholder="Enter damage notes..."
                                           value="{{ $box['damage_notes'] }}"
                                           class="pt-damage-input">
                                @endif
                            </div>
                        </div>

                        <button type="button"
                                wire:click="removeScannedBox({{ $boxId }})"
                                class="pt-remove-btn">
                            Remove
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Complete Receipt Button --}}
    <div class="pt-card">
        <div class="pt-card-head">
            <span class="pt-card-title">Complete Receiving</span>
        </div>
        <div class="pt-card-body" style="display:flex;flex-direction:column;gap:16px">
            <button type="button"
                    wire:click="completeReceipt"
                    @if(empty($scannedBoxes)) disabled @endif
                    class="pt-btn pt-btn-primary"
                    wire:loading.attr="disabled"
                    wire:target="completeReceipt">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"
                     wire:loading.remove wire:target="completeReceipt">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                     wire:loading wire:target="completeReceipt"
                     style="animation:spin 1s linear infinite">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"
                            stroke-dasharray="31.4" stroke-dashoffset="10" stroke-linecap="round"/>
                </svg>
                <span wire:loading.remove wire:target="completeReceipt">Complete Receiving</span>
                <span wire:loading wire:target="completeReceipt">Processing…</span>
            </button>
            <p style="font-size:12px;color:var(--text-dim);text-align:center;margin-top:-8px">
                Partial deliveries allowed. Missing or damaged boxes will be reported.
            </p>
        </div>
    </div>

</div>

@push('scripts')
<script>
window.addEventListener('quantity-confirmed', () => {
    setTimeout(() => {
        const scanInput = document.querySelector('[wire\\:model="scanInput"]');
        if (scanInput) {
            scanInput.focus();
            scanInput.select();
        }
    }, 80);
});
</script>
@endpush
@endif
