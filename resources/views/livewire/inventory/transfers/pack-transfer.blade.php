@php use App\Enums\TransferStatus; @endphp
<div>
<style>
/* ── Pack Transfer ───────────────────────────────────── */
.pt-wrap { display:flex; flex-direction:column; gap:16px; }

/* Cards */
.pt-card { background:var(--surface); border:1px solid var(--border); border-radius:12px; overflow:hidden; }
.pt-card-head {
    display:flex; align-items:center; justify-content:space-between; gap:10px;
    padding:10px 14px; border-bottom:1px solid var(--border);
    background:var(--surface2); flex-wrap:wrap;
}
.pt-card-title { font-size:11px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; color:var(--text-dim); }
.pt-card-body  { padding:16px; }

/* Flash */
.pt-flash {
    display:flex; align-items:flex-start; gap:10px;
    padding:10px 14px; border-radius:10px; font-size:12px; border:1px solid; line-height:1.5;
}
.pt-flash.ok  { background:var(--green-dim);  border-color:rgba(16,185,129,.25); color:var(--green); }
.pt-flash.err { background:var(--red-dim);    border-color:rgba(225,29,72,.25);  color:var(--red); }

/* Transfer header */
.pt-num  { font-size:17px; font-weight:800; color:var(--text); font-family:var(--mono); letter-spacing:-.3px; }
.pt-pill {
    display:inline-flex; align-items:center; gap:5px;
    padding:2px 8px; border-radius:999px; font-size:10px; font-weight:700; letter-spacing:.3px;
    background:var(--accent-dim); color:var(--accent); border:1px solid rgba(99,102,241,.2);
}

/* Route strip */
.pt-route {
    display:flex; align-items:center; gap:0;
    background:var(--surface2); border-radius:10px;
    padding:12px 14px; border:1px solid var(--border);
}
.pt-route-node  { flex:1; }
.pt-route-label { font-size:10px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:var(--text-dim); }
.pt-route-name  { font-size:13px; font-weight:700; color:var(--text); margin-top:2px; }
.pt-route-arrow {
    width:28px; height:28px; border-radius:50%;
    background:var(--accent-dim); color:var(--accent);
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}

/* Scan strip */
.pt-scan-strip {
    background:var(--surface2); border:1px solid var(--border);
    border-radius:12px; padding:14px; border-left:3px solid var(--accent);
}
.pt-scan-label { font-size:10px; font-weight:700; letter-spacing:.7px; text-transform:uppercase; color:var(--text-dim); margin-bottom:8px; }
.pt-scan-row   { display:flex; gap:8px; }
.pt-scan-input {
    flex:1; padding:10px 14px; border:1.5px solid var(--border); border-radius:8px;
    font-size:14px; font-weight:700; font-family:var(--mono);
    background:var(--surface); color:var(--text); outline:none; transition:border-color .15s;
}
.pt-scan-input:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(99,102,241,.1); }
.pt-scan-btn {
    padding:10px 20px; background:var(--accent); color:#fff;
    border:none; border-radius:8px; font-size:12px; font-weight:700;
    cursor:pointer; white-space:nowrap; transition:opacity .15s;
}
.pt-scan-btn:hover { opacity:.88; }

/* Quantity panel (modal) */
.pt-qty-overlay {
    position:fixed; inset:0; z-index:9999; background:rgba(10,14,26,.6);
    backdrop-filter:blur(4px); display:flex; align-items:center;
    justify-content:center; padding:20px; animation:ptFadeIn .15s ease;
}
@keyframes ptFadeIn { from{opacity:0} to{opacity:1} }
.pt-qty-modal {
    background:var(--surface); border:1px solid var(--border); border-radius:16px;
    width:100%; max-width:340px; padding:24px;
    box-shadow:0 24px 60px rgba(0,0,0,.15); animation:ptSlideUp .2s ease;
}
@keyframes ptSlideUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
.pt-qty-title { font-size:15px; font-weight:800; color:var(--text); text-align:center; margin-bottom:3px; }
.pt-qty-sub   { font-size:12px; color:var(--text-dim); text-align:center; margin-bottom:16px; }
.pt-qty-input {
    width:100%; padding:12px; border:2px solid var(--accent); border-radius:10px;
    font-size:34px; font-weight:800; text-align:center;
    background:var(--surface); color:var(--text); font-family:var(--mono);
    outline:none; box-sizing:border-box; display:block;
}
.pt-qty-hint { font-size:11px; color:var(--text-dim); text-align:center; margin-top:8px; }

/* Product rows */
.pt-prod-row { background:var(--surface); border:1px solid var(--border); border-radius:10px; overflow:hidden; }
.pt-prod-row.complete { border-color:var(--green); }
.pt-prod-head {
    display:flex; align-items:center; justify-content:space-between;
    padding:9px 14px; background:var(--surface2); border-bottom:1px solid var(--border); gap:8px; flex-wrap:wrap;
}
.pt-prod-name   { font-size:13px; font-weight:700; color:var(--text); }
.pt-prod-body   { padding:12px 14px; }
.pt-prog-info   { display:flex; align-items:center; justify-content:space-between; margin-bottom:5px; }
.pt-prog-text   { font-size:12px; color:var(--text-dim); }
.pt-prog-nums   { font-size:12px; font-weight:700; color:var(--text); font-family:var(--mono); }
.pt-prog-bar-wrap { height:5px; background:var(--surface2); border-radius:4px; overflow:hidden; }
.pt-prog-bar    { height:100%; border-radius:4px; transition:width .3s; }
.pt-prog-bar.partial  { background:var(--amber); }
.pt-prog-bar.done     { background:var(--green); }
.pt-prog-bar.empty    { background:var(--surface2); }

/* Packed boxes table */
.pt-table { width:100%; border-collapse:collapse; min-width:400px; }
.pt-table thead th {
    padding:7px 12px; font-size:10px; font-weight:700; letter-spacing:.6px;
    text-transform:uppercase; color:var(--text-dim); border-bottom:1px solid var(--border); text-align:left;
}
.pt-table tbody tr { border-bottom:1px solid var(--border); }
.pt-table tbody tr:last-child { border-bottom:none; }
.pt-table tbody tr:hover { background:var(--surface2); }
.pt-table tbody td { padding:8px 12px; font-size:12px; color:var(--text); vertical-align:middle; }
.pt-code-cell { font-family:var(--mono); font-weight:700; font-size:12px; color:var(--accent); }

/* Summary strip */
.pt-summary { display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:14px; }
.pt-sum-box  { text-align:center; padding:12px; background:var(--surface2); border-radius:8px; border:1px solid var(--border); }
.pt-sum-v    { font-size:20px; font-weight:800; color:var(--text); font-family:var(--mono); line-height:1.1; }
.pt-sum-l    { font-size:10px; font-weight:600; letter-spacing:.6px; text-transform:uppercase; color:var(--text-dim); margin-top:2px; }

/* Transporter select */
.pt-select {
    width:100%; padding:9px 12px; border:1.5px solid var(--border); border-radius:8px;
    font-size:13px; background:var(--surface); color:var(--text); outline:none; transition:border-color .15s;
}
.pt-select:focus { border-color:var(--accent); }
.pt-field-label { font-size:11px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; color:var(--text-dim); margin-bottom:5px; display:block; }
.pt-field-error { font-size:11px; color:var(--red); margin-top:4px; font-weight:600; }

/* Ship button */
.pt-ship-btn {
    width:100%; padding:11px; background:var(--accent); color:#fff;
    border:none; border-radius:9px; font-size:13px; font-weight:700;
    cursor:pointer; display:flex; align-items:center; justify-content:center;
    gap:8px; transition:opacity .15s;
}
.pt-ship-btn:hover:not(:disabled) { opacity:.88; }
.pt-ship-btn:disabled { opacity:.4; cursor:not-allowed; }

/* Responsive */
@media(max-width:640px) {
    .pt-summary { grid-template-columns:1fr; }
    .pt-route   { flex-direction:column; gap:10px; }
    .pt-route-node:last-child { text-align:left; }
    .pt-route-arrow { transform:rotate(90deg); }
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
    @foreach(['scan_success','scan_error','error'] as $fk)
        @if(session()->has($fk))
        <div class="pt-flash {{ str_contains($fk,'error') ? 'err' : 'ok' }}">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="flex-shrink:0;margin-top:1px">
                @if(str_contains($fk,'error'))
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                @endif
            </svg>
            <span>{{ session($fk) }}</span>
        </div>
        @endif
    @endforeach

    {{-- Transfer header --}}
    <div class="pt-card">
        <div class="pt-card-head">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <span class="pt-num">{{ $transfer->transfer_number }}</span>
                <span class="pt-pill">
                    <span style="width:5px;height:5px;border-radius:50%;background:currentColor"></span>
                    {{ $transfer->status->label() }}
                </span>
            </div>
            <span style="font-size:11px;color:var(--text-dim)">{{ $transfer->requested_at?->format('d M Y') }}</span>
        </div>
        <div class="pt-card-body">
            <div class="pt-route">
                <div class="pt-route-node">
                    <div class="pt-route-label">From Warehouse</div>
                    <div class="pt-route-name">{{ $transfer->fromWarehouse->name }}</div>
                </div>
                <div class="pt-route-arrow">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </div>
                <div class="pt-route-node" style="text-align:right">
                    <div class="pt-route-label">To Shop</div>
                    <div class="pt-route-name">{{ $transfer->toShop->name }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scan strip --}}
    <div class="pt-scan-strip">
        <div class="pt-scan-label">Scan or type product barcode</div>
        <div class="pt-scan-row">
            <input type="text"
                   wire:model="scanInput"
                   wire:keydown.enter="scanProduct"
                   placeholder="Product barcode — press Enter to pack"
                   class="pt-scan-input"
                   autofocus>
            <button type="button" @click="$wire.scanProduct()" class="pt-scan-btn">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:4px"><path d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                Pack
            </button>
        </div>
        @error('scanInput')
            <p style="font-size:11px;color:var(--red);margin-top:5px">{{ $message }}</p>
        @enderror
    </div>

    {{-- Quantity panel --}}
    @if($showQuantityPanel)
    <div class="pt-qty-overlay"
         x-data
         x-on:keydown.escape.window="$wire.closeQuantityPanel()">
        <div class="pt-qty-modal" @click.stop>
            <div class="pt-qty-title">{{ $pendingProductName }}</div>
            <div class="pt-qty-sub">
                {{ $pendingAlreadyAssigned }} already assigned &nbsp;·&nbsp;
                <strong style="color:var(--text)">{{ $pendingMaxQty }} box{{ $pendingMaxQty === 1 ? '' : 'es' }} needed</strong>
            </div>
            <input wire:model.live="pendingQty"
                   wire:keydown.enter="confirmScannedQuantity"
                   x-on:keydown.escape.stop="$wire.closeQuantityPanel()"
                   type="number" min="1" max="{{ $pendingMaxQty }}"
                   x-init="$nextTick(() => $el.select())"
                   class="pt-qty-input">
            @error('pendingQty')
                <div style="font-size:11px;color:var(--red);margin-top:6px;text-align:center">{{ $message }}</div>
            @enderror
            @php $afterAdd = max(0, $pendingMaxQty - (int) $pendingQty); @endphp
            <div class="pt-qty-hint">
                After adding: <strong style="color:{{ $afterAdd === 0 ? 'var(--green)' : 'var(--text)' }}">{{ $afterAdd }} box{{ $afterAdd === 1 ? '' : 'es' }} still needed</strong>
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
                    <span wire:loading.remove wire:target="confirmScannedQuantity">Add {{ $pendingQty }} Box{{ (int) $pendingQty === 1 ? '' : 'es' }}</span>
                    <span wire:loading wire:target="confirmScannedQuantity" style="display:none">Adding…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Packing progress --}}
    @if(isset($packingSummary) && count($packingSummary) > 0)
    <div class="pt-card">
        <div class="pt-card-head">
            <span class="pt-card-title">Packing Progress</span>
            @php
                $totalPacked    = collect($packingSummary)->sum('boxes_packed');
                $totalNeeded    = collect($packingSummary)->sum('boxes_needed');
                $totalRemaining = $totalNeeded - $totalPacked;
            @endphp
            <span style="font-size:11px;font-weight:700;font-family:var(--mono);
                         padding:3px 10px;border-radius:6px;border:1px solid;
                         {{ $totalRemaining > 0
                             ? 'background:var(--amber-dim);color:var(--amber);border-color:rgba(217,119,6,.2)'
                             : 'background:var(--green-dim);color:var(--green);border-color:rgba(16,185,129,.2)' }}">
                {{ $totalRemaining > 0 ? $totalRemaining . ' remaining' : '✓ Complete' }}
            </span>
        </div>
        <div class="pt-card-body" style="display:flex;flex-direction:column;gap:10px">
            @foreach($packingSummary as $summary)
                @php
                    $pct      = $summary['boxes_needed'] > 0 ? min(100, round($summary['boxes_packed'] / $summary['boxes_needed'] * 100)) : 0;
                    $remaining = $summary['boxes_needed'] - $summary['boxes_packed'];
                    $barClass  = $summary['complete'] ? 'done' : ($summary['boxes_packed'] > 0 ? 'partial' : 'empty');
                @endphp
                <div class="pt-prod-row {{ $summary['complete'] ? 'complete' : '' }}">
                    <div class="pt-prod-head">
                        <div style="display:flex;align-items:center;gap:8px">
                            <span class="pt-prod-name">{{ $summary['product_name'] }}</span>
                            @if($summary['complete'])
                                <span style="padding:2px 8px;border-radius:10px;font-size:10px;font-weight:700;
                                             background:var(--green-dim);color:var(--green)">Complete</span>
                            @else
                                <span style="padding:2px 7px;border-radius:5px;font-size:10px;font-weight:700;
                                             background:var(--amber-dim);color:var(--amber)">{{ $remaining }} left</span>
                            @endif
                        </div>
                        <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim);
                                     background:var(--surface2);padding:2px 8px;border-radius:5px">
                            {{ $summary['barcode'] }}
                        </span>
                    </div>
                    <div class="pt-prod-body">
                        <div class="pt-prog-info">
                            <span class="pt-prog-text">Progress</span>
                            <span class="pt-prog-nums" style="{{ $summary['complete'] ? 'color:var(--green)' : '' }}">
                                {{ $summary['boxes_packed'] }} / {{ $summary['boxes_needed'] }} boxes
                            </span>
                        </div>
                        <div class="pt-prog-bar-wrap">
                            <div class="pt-prog-bar {{ $barClass }}" style="width:{{ $pct }}%"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Packed boxes --}}
    @if(isset($packedBoxes) && count($packedBoxes) > 0)
    <div class="pt-card">
        <div class="pt-card-head">
            <span class="pt-card-title">Packed Boxes</span>
            <span style="font-size:12px;font-weight:700;font-family:var(--mono);
                         background:var(--accent-dim);color:var(--accent);padding:3px 10px;border-radius:6px">
                {{ count($packedBoxes) }} box{{ count($packedBoxes) === 1 ? '' : 'es' }}
            </span>
        </div>
        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch">
            <table class="pt-table">
                <thead>
                    <tr>
                        <th>Box Code</th>
                        <th>Product</th>
                        <th>Items</th>
                        <th>Scanned Out</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packedBoxes as $box)
                    <tr>
                        <td><span class="pt-code-cell">{{ $box['box_code'] }}</span></td>
                        <td>{{ $box['product_name'] }}</td>
                        <td style="font-family:var(--mono)">{{ number_format($box['items']) }}</td>
                        <td>
                            @if($box['scanned_out'] ?? false)
                                <svg width="13" height="13" fill="none" stroke="var(--green)" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <span style="font-size:10px;color:var(--text-dim)">Pending</span>
                            @endif
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
        <div class="pt-card-head">
            <span class="pt-card-title">Ship Transfer</span>
        </div>
        <div class="pt-card-body" style="display:flex;flex-direction:column;gap:14px">

            {{-- Summary --}}
            @php
                $totalItemsAssigned = isset($packedBoxes) ? array_sum(array_column($packedBoxes, 'items')) : 0;
                $productsComplete   = isset($packingSummary) ? count(array_filter($packingSummary, fn($s) => $s['complete'])) : 0;
                $totalProducts      = isset($packingSummary) ? count($packingSummary) : 0;
            @endphp
            <div class="pt-summary">
                <div class="pt-sum-box">
                    <div class="pt-sum-v">{{ isset($packedBoxes) ? count($packedBoxes) : 0 }}</div>
                    <div class="pt-sum-l">Boxes Packed</div>
                </div>
                <div class="pt-sum-box">
                    <div class="pt-sum-v">{{ number_format($totalItemsAssigned) }}</div>
                    <div class="pt-sum-l">Total Items</div>
                </div>
                <div class="pt-sum-box">
                    <div class="pt-sum-v" style="{{ $productsComplete === $totalProducts && $totalProducts > 0 ? 'color:var(--green)' : '' }}">
                        {{ $productsComplete }}/{{ $totalProducts }}
                    </div>
                    <div class="pt-sum-l">Products Done</div>
                </div>
            </div>

            {{-- Transporter --}}
            <div>
                <label class="pt-field-label">
                    Transporter <span style="color:var(--red)">*</span>
                    <span style="font-size:10px;color:var(--text-dim);font-weight:400;text-transform:none;letter-spacing:0;margin-left:6px">Select existing or type to add new</span>
                </label>
                <input type="text"
                       wire:model="transporterInput"
                       list="transporters-datalist"
                       placeholder="e.g. John Doe or select from list…"
                       class="pt-select"
                       autocomplete="off">
                <datalist id="transporters-datalist">
                    @foreach($transporters as $t)
                        <option value="{{ $t->name }}">{{ $t->vehicle_number ? $t->vehicle_number : '' }}</option>
                    @endforeach
                </datalist>
                @error('transporterInput')
                    <span class="pt-field-error">{{ $message }}</span>
                @enderror
                <p style="font-size:11px;color:var(--text-dim);margin-top:4px">
                    New names are saved automatically when you ship.
                </p>
            </div>

            {{-- Ship button --}}
            <button type="button"
                    @click="$wire.shipTransfer()"
                    @if(empty($packedBoxes ?? [])) disabled @endif
                    class="pt-ship-btn"
                    wire:loading.attr="disabled" wire:target="shipTransfer">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"
                     wire:loading.remove wire:target="shipTransfer">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
                <span wire:loading.remove wire:target="shipTransfer">Ship Transfer</span>
                <span wire:loading wire:target="shipTransfer" style="display:none">Shipping…</span>
            </button>
            <p style="font-size:11px;color:var(--text-dim);text-align:center;margin-top:-6px">
                Partial shipments are allowed. Pack at least one box before shipping.
            </p>
        </div>
    </div>

</div>

<script>
window.addEventListener('quantity-confirmed', () => {
    setTimeout(() => {
        const input = document.querySelector('.pt-scan-input');
        if (input) { input.focus(); input.select(); }
    }, 80);
});
</script>

</div>
