@php use App\Enums\TransferStatus; @endphp
<div>
@if($sessionBlocked)
    <x-session-gate-blocked
        :reason="$sessionBlockReason"
        :session-date="$blockedSessionDate"
        :session-id="$blockedSessionId"
    />
@else
<style>
/* ── Receive Transfer ───────────────────────────────────── */
.rf-wrap { display:flex; flex-direction:column; gap:16px; }

/* Cards */
.rf-card { background:var(--surface); border:1px solid var(--border); border-radius:12px; overflow:hidden; }
.rf-card-head {
    display:flex; align-items:center; justify-content:space-between; gap:10px;
    padding:10px 14px; border-bottom:1px solid var(--border);
    background:var(--surface2); flex-wrap:wrap;
}
.rf-card-title { font-size:11px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; color:var(--text-dim); }
.rf-card-body  { padding:16px; }

/* Flash */
.rf-flash {
    display:flex; align-items:flex-start; gap:10px;
    padding:10px 14px; border-radius:10px; font-size:12px; border:1px solid; line-height:1.5;
}
.rf-flash.ok   { background:var(--green-dim);  border-color:rgba(16,185,129,.25); color:var(--green); }
.rf-flash.err  { background:var(--red-dim);    border-color:rgba(225,29,72,.25);  color:var(--red); }
.rf-flash.info { background:var(--accent-dim); border-color:rgba(99,102,241,.25); color:var(--accent); }

/* Transfer header */
.rf-num  { font-size:17px; font-weight:800; color:var(--text); font-family:var(--mono); letter-spacing:-.3px; }
.rf-pill {
    display:inline-flex; align-items:center; gap:5px;
    padding:2px 8px; border-radius:999px; font-size:10px; font-weight:700; letter-spacing:.3px;
    background:var(--accent-dim); color:var(--accent); border:1px solid rgba(99,102,241,.2);
}

/* Route strip */
.rf-route {
    display:flex; align-items:center; gap:0;
    background:var(--surface2); border-radius:10px;
    padding:12px 14px; border:1px solid var(--border);
}
.rf-route-node  { flex:1; }
.rf-route-label { font-size:10px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:var(--text-dim); }
.rf-route-name  { font-size:13px; font-weight:700; color:var(--text); margin-top:2px; }
.rf-route-arrow {
    width:28px; height:28px; border-radius:50%;
    background:var(--accent-dim); color:var(--accent);
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}

/* Scan strip */
.rf-scan-strip {
    background:var(--surface2); border:1px solid var(--border);
    border-radius:12px; padding:14px; border-left:3px solid var(--accent);
}
.rf-scan-label { font-size:10px; font-weight:700; letter-spacing:.7px; text-transform:uppercase; color:var(--text-dim); margin-bottom:8px; }
.rf-scan-row   { display:flex; gap:8px; }
.rf-scan-input {
    flex:1; padding:10px 14px; border:1.5px solid var(--border); border-radius:8px;
    font-size:14px; font-weight:700; font-family:var(--mono);
    background:var(--surface); color:var(--text); outline:none; transition:border-color .15s;
}
.rf-scan-input:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(99,102,241,.1); }
.rf-scan-btn {
    padding:10px 20px; background:var(--accent); color:#fff;
    border:none; border-radius:8px; font-size:12px; font-weight:700;
    cursor:pointer; white-space:nowrap; transition:opacity .15s;
}
.rf-scan-btn:hover { opacity:.88; }

/* Quantity panel (modal) */
.rf-qty-overlay {
    position:fixed; inset:0; z-index:9999; background:rgba(10,14,26,.6);
    backdrop-filter:blur(4px); display:flex; align-items:center;
    justify-content:center; padding:20px; animation:rfFadeIn .15s ease;
}
@keyframes rfFadeIn { from{opacity:0} to{opacity:1} }
.rf-qty-modal {
    background:var(--surface); border:1px solid var(--border); border-radius:16px;
    width:100%; max-width:340px; padding:24px;
    box-shadow:0 24px 60px rgba(0,0,0,.15); animation:rfSlideUp .2s ease;
}
@keyframes rfSlideUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }

/* Product rows */
.rf-prod-row { background:var(--surface); border:1px solid var(--border); border-radius:10px; overflow:hidden; }
.rf-prod-row.complete { border-color:var(--green); }
.rf-prod-head {
    display:flex; align-items:center; justify-content:space-between;
    padding:9px 14px; background:var(--surface2); border-bottom:1px solid var(--border); gap:8px; flex-wrap:wrap;
}
.rf-prod-name   { font-size:13px; font-weight:700; color:var(--text); }
.rf-prod-body   { padding:12px 14px; }
.rf-prog-info   { display:flex; align-items:center; justify-content:space-between; margin-bottom:5px; }
.rf-prog-text   { font-size:12px; color:var(--text-dim); }
.rf-prog-nums   { font-size:12px; font-weight:700; color:var(--text); font-family:var(--mono); }
.rf-prog-bar-wrap { height:5px; background:var(--surface2); border-radius:4px; overflow:hidden; }
.rf-prog-bar    { height:100%; border-radius:4px; transition:width .3s; }
.rf-prog-bar.partial  { background:var(--amber); }
.rf-prog-bar.done     { background:var(--green); }
.rf-prog-bar.empty    { background:var(--surface2); }

/* Box rows */
.rf-box-row {
    display:flex; align-items:center; gap:10px;
    padding:9px 14px; border-bottom:1px solid var(--border);
    font-size:12px;
}
.rf-box-row:last-child { border-bottom:none; }
.rf-box-row.scanned { background:var(--green-dim); }
.rf-box-row.damaged { background:var(--red-dim); }
.rf-box-code  { font-family:var(--mono); font-weight:700; font-size:12px; color:var(--accent);
                background:var(--accent-dim); padding:2px 8px; border-radius:5px; white-space:nowrap;
                border:1px solid rgba(99,102,241,.15); }
.rf-box-product { flex:1; color:var(--text); font-weight:600; }
.rf-box-items   { font-family:var(--mono); font-size:11px; color:var(--text-dim); white-space:nowrap; }

/* Damage controls */
.rf-damage-btn {
    padding:3px 9px; border-radius:5px; font-size:11px; font-weight:600;
    border:none; cursor:pointer; transition:all .15s; white-space:nowrap;
    background:var(--surface2); color:var(--text-dim); border:1px solid var(--border);
}
.rf-damage-btn.active { background:var(--red-dim); color:var(--red); border-color:rgba(225,29,72,.3); }
.rf-damage-btn.active:hover { background:var(--red); color:#fff; }
.rf-damage-input {
    flex:1; padding:5px 9px; border:1.5px solid rgba(225,29,72,.4); border-radius:6px;
    font-size:11px; outline:none; background:var(--surface); color:var(--text);
    min-width:0;
}
.rf-damage-input:focus { border-color:var(--red); }
.rf-remove-btn {
    padding:3px 9px; border-radius:5px; font-size:11px; font-weight:600;
    background:none; color:var(--red); border:none; cursor:pointer; transition:color .15s;
}
.rf-remove-btn:hover { color:var(--red); opacity:.7; }

/* Summary strip */
.rf-summary { display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:14px; }
.rf-sum-box  { text-align:center; padding:12px; background:var(--surface2); border-radius:8px; border:1px solid var(--border); }
.rf-sum-v    { font-size:20px; font-weight:800; color:var(--text); font-family:var(--mono); line-height:1.1; }
.rf-sum-l    { font-size:10px; font-weight:600; letter-spacing:.6px; text-transform:uppercase; color:var(--text-dim); margin-top:2px; }

/* Complete button */
.rf-complete-btn {
    width:100%; padding:11px; background:var(--green); color:#fff;
    border:none; border-radius:9px; font-size:13px; font-weight:700;
    cursor:pointer; display:flex; align-items:center; justify-content:center;
    gap:8px; transition:opacity .15s;
}
.rf-complete-btn:hover:not(:disabled) { opacity:.88; }
.rf-complete-btn:disabled { opacity:.4; cursor:not-allowed; }

/* Divider between unscanned / scanned sections */
.rf-section-label {
    font-size:10px; font-weight:700; letter-spacing:.6px; text-transform:uppercase;
    color:var(--text-dim); padding:6px 14px;
    background:var(--surface2); border-bottom:1px solid var(--border);
}

/* Responsive */
@media(max-width:640px) {
    .rf-summary { grid-template-columns:1fr; }
    .rf-route   { flex-direction:column; gap:10px; }
    .rf-route-node:last-child { text-align:left; }
    .rf-route-arrow { transform:rotate(90deg); }
}
</style>

<div class="rf-wrap">

    {{-- Flash messages --}}
    @if(session()->has('success'))
    <div class="rf-flash ok">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @foreach(['scan_success','scan_error','error','info'] as $fk)
        @if(session()->has($fk))
        <div class="rf-flash {{ str_contains($fk,'error') ? 'err' : (str_contains($fk,'info') ? 'info' : 'ok') }}">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="flex-shrink:0;margin-top:1px">
                @if(str_contains($fk,'error'))
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                @elseif(str_contains($fk,'info'))
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                @endif
            </svg>
            <span>{{ session($fk) }}</span>
        </div>
        @endif
    @endforeach

    {{-- Transfer header --}}
    <div class="rf-card">
        <div class="rf-card-head">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <span class="rf-num">{{ $transfer->transfer_number }}</span>
                <span class="rf-pill">
                    <span style="width:5px;height:5px;border-radius:50%;background:currentColor"></span>
                    {{ $transfer->status->label() }}
                </span>
            </div>
            <span style="font-size:11px;color:var(--text-dim)">{{ $transfer->shipped_at?->format('d M Y') }}</span>
        </div>
        <div class="rf-card-body">
            <div class="rf-route">
                <div class="rf-route-node">
                    <div class="rf-route-label">From Warehouse</div>
                    <div class="rf-route-name">{{ $transfer->fromWarehouse->name }}</div>
                </div>
                <div class="rf-route-arrow">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </div>
                <div class="rf-route-node" style="text-align:right">
                    <div class="rf-route-label">To Shop</div>
                    <div class="rf-route-name">{{ $transfer->toShop->name }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scan strip --}}
    <div class="rf-scan-strip">
        <div class="rf-scan-label">Scan box code or product barcode</div>
        <div class="rf-scan-row">
            <input type="text"
                   wire:model="scanInput"
                   wire:keydown.enter="scanBox"
                   placeholder="Box code or product barcode — press Enter"
                   class="rf-scan-input"
                   autofocus>
            <button type="button" @click="$wire.scanBox()" class="rf-scan-btn">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:4px"><path d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                Receive
            </button>
        </div>
    </div>

    {{-- Quantity panel --}}
    @if($showQuantityPanel)
    <div class="rf-qty-overlay"
         x-data
         x-on:keydown.escape.window="$wire.closeQuantityPanel()">
        <div class="rf-qty-modal" @click.stop>
            <div style="font-size:15px;font-weight:800;color:var(--text);text-align:center;margin-bottom:3px">
                {{ $pendingProductName }}
            </div>
            <div style="font-size:12px;color:var(--text-dim);text-align:center;margin-bottom:16px">
                {{ $pendingAlreadyScanned }} already scanned &nbsp;·&nbsp;
                <strong style="color:var(--text)">{{ $pendingMaxQty }} box{{ $pendingMaxQty === 1 ? '' : 'es' }} remaining</strong>
            </div>
            <input wire:model.live="pendingQty"
                   wire:keydown.enter="confirmScannedQuantity"
                   x-on:keydown.escape.stop="$wire.closeQuantityPanel()"
                   type="number" min="1" max="{{ $pendingMaxQty }}"
                   x-init="$nextTick(() => $el.select())"
                   style="width:100%;padding:12px;border:2px solid var(--accent);
                          border-radius:10px;font-size:34px;font-weight:800;text-align:center;
                          background:var(--surface);color:var(--text);font-family:var(--mono);
                          outline:none;box-sizing:border-box;display:block">
            @error('pendingQty')
                <div style="font-size:11px;color:var(--red);margin-top:6px;text-align:center">{{ $message }}</div>
            @enderror
            @php $afterAdd = max(0, $pendingMaxQty - (int) $pendingQty); @endphp
            <div style="font-size:11px;color:var(--text-dim);text-align:center;margin-top:8px">
                After confirming: <strong style="color:{{ $afterAdd === 0 ? 'var(--green)' : 'var(--text)' }}">{{ $afterAdd }} box{{ $afterAdd === 1 ? '' : 'es' }} still needed</strong>
            </div>
            <div style="font-size:11px;color:var(--text-dim);text-align:center;margin-top:12px;padding-top:12px;border-top:1px solid var(--border)">
                Press <kbd style="background:var(--surface2);border:1px solid var(--border);border-radius:4px;padding:1px 5px;font-size:11px">Enter</kbd>
                to confirm &nbsp;·&nbsp;
                <kbd style="background:var(--surface2);border:1px solid var(--border);border-radius:4px;padding:1px 5px;font-size:11px">Esc</kbd>
                to cancel
            </div>
            <div style="display:flex;gap:8px;margin-top:14px">
                <button @click="$wire.closeQuantityPanel()"
                        style="flex:1;padding:9px;border-radius:9px;border:1px solid var(--border);
                               background:var(--surface);font-size:12px;font-weight:700;cursor:pointer;color:var(--text)">
                    Cancel
                </button>
                <button @click="$wire.confirmScannedQuantity()"
                        wire:loading.attr="disabled" wire:target="confirmScannedQuantity"
                        style="flex:2;padding:9px;border-radius:9px;border:none;
                               background:var(--accent);color:#fff;font-size:12px;font-weight:700;cursor:pointer">
                    <span wire:loading.remove wire:target="confirmScannedQuantity">Confirm {{ $pendingQty }} Box{{ (int) $pendingQty === 1 ? '' : 'es' }}</span>
                    <span wire:loading wire:target="confirmScannedQuantity" style="display:none">Confirming…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Receiving progress per product --}}
    @if(isset($receivingSummary) && count($receivingSummary) > 0)
    <div class="rf-card">
        <div class="rf-card-head">
            <span class="rf-card-title">Receiving Progress</span>
            @php
                $totalReceived  = collect($receivingSummary)->sum('boxes_received');
                $totalShipped   = collect($receivingSummary)->sum('boxes_shipped');
                $totalRemaining = $totalShipped - $totalReceived;
            @endphp
            <span style="font-size:11px;font-weight:700;font-family:var(--mono);
                         padding:3px 10px;border-radius:6px;border:1px solid;
                         {{ $totalRemaining > 0
                             ? 'background:var(--amber-dim);color:var(--amber);border-color:rgba(217,119,6,.2)'
                             : 'background:var(--green-dim);color:var(--green);border-color:rgba(16,185,129,.2)' }}">
                {{ $totalRemaining > 0 ? $totalRemaining . ' remaining' : '✓ Complete' }}
            </span>
        </div>
        <div class="rf-card-body" style="display:flex;flex-direction:column;gap:10px">
            @foreach($receivingSummary as $summary)
                @php
                    $pct       = $summary['boxes_shipped'] > 0 ? min(100, round($summary['boxes_received'] / $summary['boxes_shipped'] * 100)) : 0;
                    $remaining = $summary['boxes_shipped'] - $summary['boxes_received'];
                    $barClass  = $summary['complete'] ? 'done' : ($summary['boxes_received'] > 0 ? 'partial' : 'empty');
                @endphp
                <div class="rf-prod-row {{ $summary['complete'] ? 'complete' : '' }}">
                    <div class="rf-prod-head">
                        <div style="display:flex;align-items:center;gap:8px">
                            <span class="rf-prod-name">{{ $summary['product_name'] }}</span>
                            @if($summary['complete'])
                                <span style="padding:2px 8px;border-radius:10px;font-size:10px;font-weight:700;background:var(--green-dim);color:var(--green)">Complete</span>
                            @else
                                <span style="padding:2px 7px;border-radius:5px;font-size:10px;font-weight:700;background:var(--amber-dim);color:var(--amber)">{{ $remaining }} left</span>
                            @endif
                        </div>
                        <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim);background:var(--surface2);padding:2px 8px;border-radius:5px">
                            {{ $summary['barcode'] }}
                        </span>
                    </div>
                    <div class="rf-prod-body">
                        <div class="rf-prog-info">
                            <span class="rf-prog-text">Progress</span>
                            <span class="rf-prog-nums" style="{{ $summary['complete'] ? 'color:var(--green)' : '' }}">
                                {{ $summary['boxes_received'] }} / {{ $summary['boxes_shipped'] }} boxes
                            </span>
                        </div>
                        <div class="rf-prog-bar-wrap">
                            <div class="rf-prog-bar {{ $barClass }}" style="width:{{ $pct }}%"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Boxes: pending on top, scanned this session in middle, previously received at bottom --}}
    @if((isset($pendingBoxes) && count($pendingBoxes) > 0) || (isset($sessionBoxes) && count($sessionBoxes) > 0) || (isset($receivedBoxes) && count($receivedBoxes) > 0))
    <div class="rf-card">
        <div class="rf-card-head">
            <span class="rf-card-title">Boxes</span>
            <div style="display:flex;gap:6px;align-items:center">
                @if(isset($pendingBoxes) && count($pendingBoxes) > 0)
                <span style="font-size:11px;font-weight:700;font-family:var(--mono);
                             background:var(--amber-dim);color:var(--amber);
                             padding:3px 8px;border-radius:6px">
                    {{ count($pendingBoxes) }} pending
                </span>
                @endif
                @php $doneCount = (isset($sessionBoxes) ? count($sessionBoxes) : 0) + (isset($receivedBoxes) ? count($receivedBoxes) : 0); @endphp
                @if($doneCount > 0)
                <span style="font-size:11px;font-weight:700;font-family:var(--mono);
                             background:var(--green-dim);color:var(--green);
                             padding:3px 8px;border-radius:6px">
                    {{ $doneCount }} received
                </span>
                @endif
            </div>
        </div>

        {{-- 1. Pending (unscanned) — top --}}
        @if(isset($pendingBoxes) && count($pendingBoxes) > 0)
        <div class="rf-section-label">Pending — {{ count($pendingBoxes) }} box{{ count($pendingBoxes) === 1 ? '' : 'es' }}</div>
        @foreach($pendingBoxes as $box)
        <div class="rf-box-row">
            <span class="rf-box-code">{{ $box['box_code'] }}</span>
            <span class="rf-box-product">{{ $box['product_name'] }}</span>
            <span class="rf-box-items">{{ $box['items'] }} items</span>
        </div>
        @endforeach
        @endif

        {{-- 2. Scanned this session — middle, with damage controls --}}
        @if(isset($sessionBoxes) && count($sessionBoxes) > 0)
        <div class="rf-section-label" style="background:var(--green-dim);color:var(--green)">
            Scanned this session — {{ count($sessionBoxes) }} box{{ count($sessionBoxes) === 1 ? '' : 'es' }}
        </div>
        @foreach($sessionBoxes as $box)
        <div class="rf-box-row scanned {{ $box['is_damaged'] ? 'damaged' : '' }}" style="flex-wrap:wrap">
            <span class="rf-box-code">{{ $box['box_code'] }}</span>
            <span class="rf-box-product">{{ $box['product_name'] }}</span>
            <span class="rf-box-items">{{ $box['items'] }} items</span>
            <div style="display:flex;align-items:center;gap:6px;width:100%;margin-top:5px;padding-top:5px;border-top:1px solid rgba(0,0,0,.06)">
                <button type="button"
                        @click="$wire.markAsDamaged({{ $box['box_id'] }}, {{ $box['is_damaged'] ? 'false' : 'true' }})"
                        class="rf-damage-btn {{ $box['is_damaged'] ? 'active' : '' }}">
                    {{ $box['is_damaged'] ? '✓ Damaged' : 'Mark Damaged' }}
                </button>
                @if($box['is_damaged'])
                <input type="text"
                       @change="$wire.updateDamageNotes({{ $box['box_id'] }}, $event.target.value)"
                       value="{{ $box['damage_notes'] }}"
                       placeholder="Describe the damage…"
                       class="rf-damage-input">
                @endif
                <button type="button"
                        @click="$wire.removeScannedBox({{ $box['box_id'] }})"
                        class="rf-remove-btn" style="margin-left:auto">
                    Remove
                </button>
            </div>
        </div>
        @endforeach
        @endif

        {{-- 3. Previously received (committed to DB) — bottom --}}
        @if(isset($receivedBoxes) && count($receivedBoxes) > 0)
        <div class="rf-section-label" style="background:var(--surface2);color:var(--text-dim)">
            Previously received — {{ count($receivedBoxes) }} box{{ count($receivedBoxes) === 1 ? '' : 'es' }}
        </div>
        @foreach($receivedBoxes as $box)
        <div class="rf-box-row" style="opacity:.7">
            <span class="rf-box-code">{{ $box['box_code'] }}</span>
            <span class="rf-box-product">{{ $box['product_name'] }}</span>
            <span class="rf-box-items">{{ $box['items'] }} items</span>
            @if($box['is_damaged'])
                <span style="padding:2px 7px;border-radius:5px;font-size:10px;font-weight:700;background:var(--red-dim);color:var(--red)">Damaged</span>
            @else
                <svg width="13" height="13" fill="none" stroke="var(--green)" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            @endif
        </div>
        @endforeach
        @endif

    </div>
    @endif

    {{-- Summary + Complete --}}
    <div class="rf-card">
        <div class="rf-card-head">
            <span class="rf-card-title">Complete Receiving</span>
        </div>
        <div class="rf-card-body" style="display:flex;flex-direction:column;gap:14px">
            <div class="rf-summary">
                <div class="rf-sum-box">
                    <div class="rf-sum-v">{{ $expectedBoxes }}</div>
                    <div class="rf-sum-l">Expected</div>
                </div>
                <div class="rf-sum-box">
                    <div class="rf-sum-v" style="{{ $scannedCount > 0 ? 'color:var(--green)' : '' }}">{{ $scannedCount }}</div>
                    <div class="rf-sum-l">Scanned</div>
                </div>
                <div class="rf-sum-box">
                    <div class="rf-sum-v" style="{{ $remainingCount > 0 ? 'color:var(--amber)' : 'color:var(--green)' }}">{{ $remainingCount }}</div>
                    <div class="rf-sum-l">Remaining</div>
                </div>
            </div>

            <button type="button"
                    @click="$wire.completeReceipt()"
                    @if(empty($scannedBoxes)) disabled @endif
                    class="rf-complete-btn"
                    wire:loading.attr="disabled" wire:target="completeReceipt">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"
                     wire:loading.remove wire:target="completeReceipt">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <span wire:loading.remove wire:target="completeReceipt">Complete Receiving</span>
                <span wire:loading wire:target="completeReceipt" style="display:none">Processing…</span>
            </button>
            <p style="font-size:11px;color:var(--text-dim);text-align:center;margin-top:-6px">
                Partial deliveries allowed. Missing or damaged boxes will be flagged.
            </p>
        </div>
    </div>

</div>

<script>
window.addEventListener('quantity-confirmed', () => {
    setTimeout(() => {
        const input = document.querySelector('.rf-scan-input');
        if (input) { input.focus(); input.select(); }
    }, 80);
});
</script>

@endif
</div>
