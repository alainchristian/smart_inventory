# SmartInventory — Review Transfer Request Redesign
## Claude Code Instructions

> Drop this file in the project root, then tell Claude Code:
> "Read REVIEW_TRANSFER_REDESIGN.md and follow every step in order."

---

## Ground Rules

- **DO NOT touch** `app/Livewire/WarehouseManager/Transfers/ReviewTransfer.php` — preserve ALL logic
- **ONLY rewrite** `resources/views/livewire/warehouse-manager/transfers/review-transfer.blade.php`
- Every `wire:` binding must match the **EXACT** method/property names from the PHP file
- Design system: CSS vars `--accent --surface --surface2 --surface3 --border --border-hi --text --text-sub --text-dim --green --red --amber --violet --success --r --rsm --font --mono --tr`
- Run `php artisan view:clear && php artisan cache:clear` after

---

## Step 0 — Read First (MANDATORY)

```bash
# Read the ENTIRE PHP component before writing anything
cat app/Livewire/WarehouseManager/Transfers/ReviewTransfer.php

# Check what git commit was working before
git log --oneline -20

# View the current blade file
cat resources/views/livewire/warehouse-manager/transfers/review-transfer.blade.php

# Check wrapper layout
cat resources/views/warehouse/transfers/show.blade.php

# Verify TransferStatus enum values
cat app/Enums/TransferStatus.php
```

**Key method/property names (DO NOT change these in wire: bindings):**
- Approve transfer: `wire:click="approve"`
- Open reject modal: `wire:click="openRejectModal"`
- Close reject modal: `wire:click="closeRejectModal"` / `@click="$wire.closeRejectModal()"`
- Reject transfer: `wire:click="reject"`
- Reject reason field: `wire:model="rejectReason"`
- Boxes requested input: `wire:model.live="items.{{ $index }}.boxes_requested"`
- Modal toggle property: `$showRejectModal`
- Stock levels: `$stockLevels` (passed from render)
- Transfer object: `$transfer`
- Items array: `$items`

---

## Step 1 — Full Blade Rewrite

**Target:** `resources/views/livewire/warehouse-manager/transfers/review-transfer.blade.php`

Replace the **entire** file with the content below:

```blade
@php use App\Enums\TransferStatus; @endphp

<style>
/* ── Review Transfer — Design System Aligned ── */
.rt-wrap { display:flex; flex-direction:column; gap:20px; font-family:var(--font); }

/* ── Alert banners ── */
.rt-alert {
    display:flex; align-items:flex-start; gap:12px;
    padding:14px 18px; border-radius:10px;
    border:1px solid; font-size:14px; line-height:1.5;
}
.rt-alert.success { background:var(--success-dim); border-color:rgba(22,163,74,.25); color:#14532d; }
.rt-alert.error   { background:var(--red-dim);     border-color:rgba(225,29,72,.25);  color:#7f1d1d; }
.rt-alert.info    { background:var(--accent-dim);  border-color:rgba(59,111,212,.25); color:#1e3a8a; }

/* ── Card ── */
.rt-card {
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:12px;
    overflow:hidden;
}
.rt-card-head {
    padding:18px 22px;
    border-bottom:1px solid var(--border);
    display:flex; align-items:center; justify-content:space-between; gap:12px;
    background:var(--surface);
}
.rt-card-title {
    font-size:13px; font-weight:700; letter-spacing:.5px;
    text-transform:uppercase; color:var(--text-sub);
}
.rt-card-body { padding:22px; }

/* ── Transfer header meta grid ── */
.rt-meta-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));
    gap:16px;
}
.rt-meta-item { display:flex; flex-direction:column; gap:4px; }
.rt-meta-label {
    font-size:10.5px; font-weight:700; letter-spacing:.6px;
    text-transform:uppercase; color:var(--text-dim);
}
.rt-meta-value { font-size:14px; font-weight:600; color:var(--text); }

/* ── Status pill ── */
.rt-pill {
    display:inline-flex; align-items:center; gap:6px;
    padding:4px 12px; border-radius:999px;
    font-size:11.5px; font-weight:700; letter-spacing:.3px;
}
.rt-pill.pending  { background:var(--amber-dim); color:var(--amber); border:1px solid rgba(217,119,6,.25); }
.rt-pill.approved { background:var(--green-dim);  color:var(--green); border:1px solid rgba(14,158,134,.25); }
.rt-pill.rejected { background:var(--red-dim);    color:var(--red);   border:1px solid rgba(225,29,72,.25); }

/* ── Route strip ── */
.rt-route {
    display:flex; align-items:center; gap:0;
    background:var(--surface2); border-radius:10px;
    padding:14px 18px; border:1px solid var(--border);
}
.rt-route-node { flex:1; }
.rt-route-label { font-size:10px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:var(--text-dim); }
.rt-route-name  { font-size:14px; font-weight:700; color:var(--text); margin-top:3px; }
.rt-route-arrow {
    display:flex; align-items:center; justify-content:center;
    width:36px; height:36px; border-radius:50%;
    background:var(--accent-dim); color:var(--accent);
    flex-shrink:0;
}

/* ── Product rows ── */
.rt-product-row {
    border:1.5px solid var(--border);
    border-radius:10px; overflow:hidden;
    transition:border-color var(--tr);
}
.rt-product-row.has-warning { border-color:rgba(225,29,72,.4); background:rgba(225,29,72,.02); }
.rt-product-head {
    display:flex; align-items:center; justify-content:space-between;
    padding:14px 18px; background:var(--surface2);
    border-bottom:1px solid var(--border);
}
.rt-product-name { font-size:15px; font-weight:700; color:var(--text); }
.rt-product-body {
    padding:16px 18px;
    display:grid;
    grid-template-columns:1fr 1fr 1fr;
    gap:16px;
    align-items:start;
}
@media(max-width:640px) {
    .rt-product-body { grid-template-columns:1fr; }
}

/* ── Stat box inside product row ── */
.rt-stat { display:flex; flex-direction:column; gap:4px; }
.rt-stat-label { font-size:10.5px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; color:var(--text-dim); }
.rt-stat-value { font-size:22px; font-weight:800; color:var(--text); font-family:var(--mono); line-height:1; }
.rt-stat-sub   { font-size:11px; color:var(--text-dim); margin-top:2px; }
.rt-stat-value.ok  { color:var(--green); }
.rt-stat-value.bad { color:var(--red); }

/* ── Input field ── */
.rt-input {
    width:100%; padding:10px 14px;
    background:var(--surface); color:var(--text);
    border:1.5px solid var(--border-hi);
    border-radius:8px; font-size:15px; font-weight:700;
    font-family:var(--mono);
    transition:border-color var(--tr), box-shadow var(--tr);
    outline:none;
}
.rt-input:focus {
    border-color:var(--accent);
    box-shadow:0 0 0 3px var(--accent-glow);
}
.rt-input.rt-input-error {
    border-color:var(--red);
    box-shadow:0 0 0 3px var(--red-glow);
}

/* ── Stock availability bar ── */
.rt-stock-bar-wrap {
    height:6px; border-radius:999px;
    background:var(--surface3); overflow:hidden; margin-top:8px;
}
.rt-stock-bar { height:100%; border-radius:999px; transition:width .4s var(--ease); }
.rt-stock-bar.ok  { background:var(--green); }
.rt-stock-bar.bad { background:var(--red); }

/* ── Warning chip ── */
.rt-warn {
    display:inline-flex; align-items:center; gap:6px;
    padding:6px 12px; border-radius:8px; margin-top:12px;
    background:var(--red-dim); border:1px solid rgba(225,29,72,.25);
    color:var(--red); font-size:12px; font-weight:600;
}

/* ── Action footer ── */
.rt-action-bar {
    display:flex; align-items:center; justify-content:flex-end;
    gap:12px; padding:18px 22px;
    background:var(--surface2); border-top:1px solid var(--border);
    flex-wrap:wrap;
}

/* ── Buttons ── */
.rt-btn {
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    padding:10px 22px; border-radius:9px;
    font-size:14px; font-weight:700; font-family:var(--font);
    border:none; cursor:pointer;
    transition:background var(--tr), transform var(--tr), box-shadow var(--tr), opacity var(--tr);
    white-space:nowrap;
}
.rt-btn:active { transform:scale(.97); }
.rt-btn:disabled { opacity:.45; cursor:not-allowed; transform:none; }

.rt-btn-approve {
    background:var(--accent); color:#fff;
    box-shadow:0 2px 8px var(--accent-glow);
}
.rt-btn-approve:hover:not(:disabled) {
    background:#2d5dbf;
    box-shadow:0 4px 14px var(--accent-glow);
}
.rt-btn-reject {
    background:var(--red-dim); color:var(--red);
    border:1.5px solid rgba(225,29,72,.3);
}
.rt-btn-reject:hover:not(:disabled) {
    background:rgba(225,29,72,.15);
}
.rt-btn-secondary {
    background:var(--surface3); color:var(--text-sub);
    border:1.5px solid var(--border);
}
.rt-btn-secondary:hover { background:var(--border); }
.rt-btn-danger {
    background:var(--red); color:#fff;
    box-shadow:0 2px 8px var(--red-glow);
}
.rt-btn-danger:hover:not(:disabled) {
    background:#be1039;
    box-shadow:0 4px 14px var(--red-glow);
}

/* ── Status banner (approved/rejected state) ── */
.rt-status-banner {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    gap:12px; padding:32px; text-align:center; border-radius:12px;
    border:1px solid;
}
.rt-status-banner.approved { background:var(--green-dim); border-color:rgba(14,158,134,.3); color:var(--green); }
.rt-status-banner.rejected { background:var(--red-dim);   border-color:rgba(225,29,72,.3);  color:var(--red); }
.rt-status-banner-icon { width:52px; height:52px; }
.rt-status-banner-title { font-size:18px; font-weight:800; }
.rt-status-banner-sub   { font-size:14px; opacity:.8; }

/* ── Modal overlay ── */
.rt-modal-overlay {
    position:fixed; inset:0; z-index:50;
    background:rgba(10,14,26,.6);
    backdrop-filter:blur(4px);
    display:flex; align-items:center; justify-content:center;
    padding:20px;
    animation:rtFadeIn .15s ease;
}
@keyframes rtFadeIn { from { opacity:0 } to { opacity:1 } }

.rt-modal {
    background:var(--surface); border:1px solid var(--border);
    border-radius:14px; width:100%; max-width:500px;
    box-shadow:0 24px 60px rgba(0,0,0,.18);
    animation:rtSlideUp .2s var(--ease);
}
@keyframes rtSlideUp { from { opacity:0; transform:translateY(16px) } to { opacity:1; transform:translateY(0) } }

.rt-modal-head {
    display:flex; align-items:center; justify-content:space-between;
    padding:18px 22px; border-bottom:1px solid var(--border);
}
.rt-modal-title { font-size:16px; font-weight:800; color:var(--text); }
.rt-modal-close {
    width:32px; height:32px; border-radius:8px;
    background:var(--surface2); border:1px solid var(--border);
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; color:var(--text-sub); transition:background var(--tr);
}
.rt-modal-close:hover { background:var(--surface3); }
.rt-modal-body { padding:22px; display:flex; flex-direction:column; gap:16px; }
.rt-modal-foot {
    display:flex; align-items:center; justify-content:flex-end; gap:10px;
    padding:16px 22px; border-top:1px solid var(--border);
}

/* ── Textarea ── */
.rt-textarea {
    width:100%; padding:12px 14px;
    background:var(--surface2); color:var(--text);
    border:1.5px solid var(--border-hi);
    border-radius:8px; font-size:14px; font-family:var(--font);
    resize:vertical; min-height:110px; outline:none;
    transition:border-color var(--tr), box-shadow var(--tr);
}
.rt-textarea:focus {
    border-color:var(--red);
    box-shadow:0 0 0 3px var(--red-glow);
    background:var(--surface);
}

/* ── Field label ── */
.rt-field-label {
    font-size:11px; font-weight:700; letter-spacing:.5px;
    text-transform:uppercase; color:var(--text-sub); margin-bottom:6px;
    display:block;
}
.rt-field-error { font-size:12px; color:var(--red); margin-top:5px; font-weight:600; }

/* ── Notes box ── */
.rt-notes {
    padding:14px 16px; background:var(--surface2);
    border-radius:8px; border:1px solid var(--border);
    font-size:14px; color:var(--text-sub); line-height:1.6;
}

/* ── Pack CTA ── */
.rt-pack-cta {
    display:inline-flex; align-items:center; gap:8px;
    padding:10px 22px; border-radius:9px;
    background:var(--accent); color:#fff;
    font-size:14px; font-weight:700; text-decoration:none;
    box-shadow:0 2px 8px var(--accent-glow);
    transition:background var(--tr), box-shadow var(--tr);
}
.rt-pack-cta:hover { background:#2d5dbf; box-shadow:0 4px 14px var(--accent-glow); }

/* ── Divider ── */
.rt-divider { border:none; border-top:1px solid var(--border); margin:0; }

@media(max-width:640px) {
    .rt-action-bar { justify-content:stretch; }
    .rt-btn { flex:1; }
    .rt-meta-grid { grid-template-columns:1fr 1fr; }
}
</style>

<div class="rt-wrap">

    {{-- ── Flash Messages ── --}}
    @if(session()->has('success'))
        <div class="rt-alert success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="flex-shrink:0;margin-top:1px">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session()->has('error'))
        <div class="rt-alert error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="flex-shrink:0;margin-top:1px">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- ── Transfer Header Card ── --}}
    <div class="rt-card">
        <div class="rt-card-head">
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                <span style="font-size:20px;font-weight:800;color:var(--text);letter-spacing:-.3px">
                    {{ $transfer->transfer_number }}
                </span>
                @php
                    $statusClass = match($transfer->status) {
                        TransferStatus::PENDING  => 'pending',
                        TransferStatus::APPROVED => 'approved',
                        TransferStatus::REJECTED => 'rejected',
                        default                  => 'pending',
                    };
                @endphp
                <span class="rt-pill {{ $statusClass }}">
                    <span style="width:6px;height:6px;border-radius:50%;background:currentColor"></span>
                    {{ $transfer->status->label() }}
                </span>
            </div>
            <div style="font-size:12px;color:var(--text-dim)">
                {{ $transfer->requested_at?->format('M d, Y · H:i') }}
            </div>
        </div>

        <div class="rt-card-body" style="display:flex;flex-direction:column;gap:18px">

            {{-- Route strip --}}
            <div class="rt-route">
                <div class="rt-route-node">
                    <div class="rt-route-label">From Warehouse</div>
                    <div class="rt-route-name">{{ $transfer->fromWarehouse->name }}</div>
                </div>
                <div class="rt-route-arrow">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </div>
                <div class="rt-route-node" style="text-align:right">
                    <div class="rt-route-label">To Shop</div>
                    <div class="rt-route-name">{{ $transfer->toShop->name }}</div>
                </div>
            </div>

            {{-- Meta grid --}}
            <div class="rt-meta-grid">
                <div class="rt-meta-item">
                    <span class="rt-meta-label">Requested By</span>
                    <span class="rt-meta-value">{{ $transfer->requestedBy->name }}</span>
                </div>
                <div class="rt-meta-item">
                    <span class="rt-meta-label">Products</span>
                    <span class="rt-meta-value">{{ count($items) }} {{ count($items) === 1 ? 'product' : 'products' }}</span>
                </div>
                @if($transfer->transporter)
                    <div class="rt-meta-item">
                        <span class="rt-meta-label">Transporter</span>
                        <span class="rt-meta-value">{{ $transfer->transporter->name }}</span>
                        @if($transfer->transporter->vehicle_number)
                            <span style="font-size:12px;color:var(--text-dim)">{{ $transfer->transporter->vehicle_number }}</span>
                        @endif
                    </div>
                @endif
                @if($transfer->reviewed_by)
                    <div class="rt-meta-item">
                        <span class="rt-meta-label">Reviewed By</span>
                        <span class="rt-meta-value">{{ $transfer->reviewedBy?->name ?? '—' }}</span>
                    </div>
                @endif
            </div>

            {{-- Notes --}}
            @if($transfer->notes && $transfer->status === TransferStatus::PENDING)
                <div>
                    <div class="rt-meta-label" style="margin-bottom:6px">Shop Notes</div>
                    <div class="rt-notes">{{ $transfer->notes }}</div>
                </div>
            @endif

        </div>
    </div>

    {{-- ── Requested Products Card ── --}}
    <div class="rt-card">
        <div class="rt-card-head">
            <span class="rt-card-title">Requested Products</span>
            @if($transfer->status === TransferStatus::PENDING)
                <span style="font-size:12px;color:var(--text-dim)">
                    You may adjust quantities before approving
                </span>
            @endif
        </div>

        <div class="rt-card-body" style="display:flex;flex-direction:column;gap:14px">
            @foreach($items as $index => $item)
                @php
                    $stock          = $stockLevels[$item['product_id']] ?? null;
                    $availableBoxes = $stock ? $stock['total_boxes'] : 0;
                    $requestedBoxes = (int) ($item['boxes_requested'] ?? 0);
                    $exceedsStock   = $requestedBoxes > $availableBoxes;
                    $totalItems     = $requestedBoxes * $item['items_per_box'];
                    $stockPct       = $availableBoxes > 0 ? min(100, round(($requestedBoxes / $availableBoxes) * 100)) : 100;
                @endphp

                <div class="rt-product-row {{ $exceedsStock ? 'has-warning' : '' }}">

                    {{-- Product header --}}
                    <div class="rt-product-head">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:32px;height:32px;border-radius:8px;background:var(--accent-dim);
                                        display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <svg width="16" height="16" fill="none" stroke="var(--accent)" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <span class="rt-product-name">{{ $item['product_name'] }}</span>
                        </div>
                        <span style="font-size:11px;color:var(--text-dim);background:var(--surface3);
                                     padding:3px 10px;border-radius:6px;font-weight:600">
                            {{ $item['items_per_box'] }} items / box
                        </span>
                    </div>

                    {{-- Product body --}}
                    <div class="rt-product-body">

                        {{-- Boxes Requested --}}
                        <div class="rt-stat">
                            <label class="rt-stat-label">Boxes Requested</label>
                            @if($transfer->status === TransferStatus::PENDING)
                                <input type="number"
                                       wire:model.live="items.{{ $index }}.boxes_requested"
                                       min="0"
                                       class="rt-input {{ $exceedsStock ? 'rt-input-error' : '' }}"
                                       placeholder="0">
                                @error("items.{$index}.boxes_requested")
                                    <span class="rt-field-error">{{ $message }}</span>
                                @enderror
                            @else
                                <span class="rt-stat-value">{{ number_format($requestedBoxes) }}</span>
                                <span class="rt-stat-sub">boxes</span>
                            @endif
                        </div>

                        {{-- Available in Warehouse --}}
                        <div class="rt-stat">
                            <span class="rt-stat-label">Available in Warehouse</span>
                            <span class="rt-stat-value {{ $exceedsStock ? 'bad' : 'ok' }}">
                                {{ number_format($availableBoxes) }}
                            </span>
                            <span class="rt-stat-sub">
                                @if($stock)
                                    {{ $stock['full_boxes'] }} full · {{ $stock['partial_boxes'] }} partial
                                @else
                                    boxes available
                                @endif
                            </span>
                            @if($availableBoxes > 0)
                                <div class="rt-stock-bar-wrap">
                                    <div class="rt-stock-bar {{ $exceedsStock ? 'bad' : 'ok' }}"
                                         style="width:{{ min(100, $stockPct) }}%"></div>
                                </div>
                            @endif
                        </div>

                        {{-- Total Items --}}
                        <div class="rt-stat">
                            <span class="rt-stat-label">Total Items</span>
                            <span class="rt-stat-value">{{ number_format($totalItems) }}</span>
                            <span class="rt-stat-sub">items total</span>
                        </div>

                    </div>

                    {{-- Stock warning --}}
                    @if($exceedsStock && $requestedBoxes > 0)
                        <div style="padding:0 18px 14px">
                            <div class="rt-warn">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                Requested {{ $requestedBoxes }} boxes but only {{ $availableBoxes }} available. Reduce quantity to approve.
                            </div>
                        </div>
                    @endif

                </div>
            @endforeach
        </div>

        {{-- ── Action Bar (Pending state only) ── --}}
        @if($transfer->status === TransferStatus::PENDING)
            <hr class="rt-divider">
            <div class="rt-action-bar">
                <button type="button"
                        wire:click="openRejectModal"
                        class="rt-btn rt-btn-reject"
                        wire:loading.attr="disabled">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Reject Request
                </button>

                <button type="button"
                        wire:click="approve"
                        class="rt-btn rt-btn-approve"
                        wire:loading.attr="disabled">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" wire:loading.remove wire:target="approve">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" wire:loading wire:target="approve"
                         style="animation:spin 1s linear infinite">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="31.4" stroke-dashoffset="10" stroke-linecap="round"/>
                    </svg>
                    <span wire:loading.remove wire:target="approve">Approve Transfer</span>
                    <span wire:loading wire:target="approve">Processing…</span>
                </button>
            </div>

        {{-- ── Approved state ── --}}
        @elseif($transfer->status === TransferStatus::APPROVED)
            <div style="padding:22px">
                <div class="rt-status-banner approved">
                    <svg class="rt-status-banner-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="rt-status-banner-title">Transfer Approved</div>
                    <div class="rt-status-banner-sub">This transfer has been approved and is ready for packing.</div>
                    <a href="{{ route('warehouse.transfers.pack', $transfer) }}" class="rt-pack-cta">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                        Pack Transfer
                    </a>
                </div>
            </div>

        {{-- ── Rejected state ── --}}
        @elseif($transfer->status === TransferStatus::REJECTED)
            <div style="padding:22px">
                <div class="rt-status-banner rejected">
                    <svg class="rt-status-banner-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="rt-status-banner-title">Transfer Rejected</div>
                    @if($transfer->notes)
                        <div class="rt-status-banner-sub">Reason: {{ $transfer->notes }}</div>
                    @endif
                </div>
            </div>
        @endif

    </div>

    {{-- ── Reject Modal ── --}}
    @if($showRejectModal)
        <div class="rt-modal-overlay" x-data="{ show: @entangle('showRejectModal') }">
            <div class="rt-modal" @click.stop>

                <div class="rt-modal-head">
                    <span class="rt-modal-title">Reject Transfer Request</span>
                    <button type="button" class="rt-modal-close" @click="$wire.closeRejectModal()">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="rt-modal-body">
                    <div style="display:flex;align-items:flex-start;gap:12px;padding:14px;
                                background:var(--red-dim);border-radius:10px;border:1px solid rgba(225,29,72,.2)">
                        <svg width="18" height="18" fill="none" stroke="var(--red)" viewBox="0 0 24 24" stroke-width="2.5" style="flex-shrink:0;margin-top:1px">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span style="font-size:13px;color:var(--red);line-height:1.5">
                            This action will reject the transfer request. The shop will be notified and will need to submit a new request.
                        </span>
                    </div>

                    <div>
                        <label class="rt-field-label">
                            Reason for Rejection <span style="color:var(--red)">*</span>
                        </label>
                        <textarea wire:model="rejectReason"
                                  class="rt-textarea"
                                  placeholder="Explain why this transfer cannot be fulfilled…"></textarea>
                        @error('rejectReason')
                            <span class="rt-field-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="rt-modal-foot">
                    <button type="button"
                            class="rt-btn rt-btn-secondary"
                            @click="$wire.closeRejectModal()">
                        Cancel
                    </button>
                    <button type="button"
                            wire:click="reject"
                            class="rt-btn rt-btn-danger"
                            wire:loading.attr="disabled"
                            wire:target="reject">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" wire:loading.remove wire:target="reject">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span wire:loading.remove wire:target="reject">Reject Transfer</span>
                        <span wire:loading wire:target="reject">Rejecting…</span>
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>

@push('scripts')
<style>
@keyframes spin { to { transform:rotate(360deg) } }
</style>
@endpush
```

---

## Step 2 — Clear Caches

```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
```

---

## Step 3 — Verify Buttons Work

```bash
# Check the PHP methods exist and match wire: targets
grep -n "public function approve\|public function reject\|public function openRejectModal\|public function closeRejectModal" app/Livewire/WarehouseManager/Transfers/ReviewTransfer.php

# Confirm route for pack link exists
grep -n "warehouse.transfers.pack" routes/web.php

# Check TransferStatus enum has PENDING / APPROVED / REJECTED
cat app/Enums/TransferStatus.php
```

If the `warehouse.transfers.pack` route does not exist, replace:
```blade
<a href="{{ route('warehouse.transfers.pack', $transfer) }}" class="rt-pack-cta">
```
with:
```blade
<a href="{{ route('warehouse.transfers.index') }}" class="rt-pack-cta">
```

---

## Step 4 — Check Git History for Working Version (if buttons still broken)

```bash
# See recent commits
git log --oneline -15

# Find the last commit where ReviewTransfer.php was changed
git log --oneline -- app/Livewire/WarehouseManager/Transfers/ReviewTransfer.php

# Restore the PHP file from a known-good commit if needed (replace COMMIT_HASH)
# git checkout COMMIT_HASH -- app/Livewire/WarehouseManager/Transfers/ReviewTransfer.php
```

---

## Step 5 — Smoke Test Checklist

- [ ] Page loads without errors at `/warehouse/transfers/{id}`
- [ ] Transfer number, status badge, warehouse→shop route strip displays correctly
- [ ] Product rows show boxes requested (editable if pending), available stock, total items
- [ ] Stock availability bar fills correctly (green = ok, red = exceeds stock)
- [ ] Warning chip appears when requested > available
- [ ] **Approve button** fires `wire:click="approve"` and shows loading spinner
- [ ] **Reject button** opens the reject modal via `wire:click="openRejectModal"`
- [ ] Reject modal: textarea binds to `wire:model="rejectReason"`, confirm fires `wire:click="reject"`
- [ ] Modal backdrop click and ✕ button both close modal via `$wire.closeRejectModal()`
- [ ] Approved state shows green banner with Pack Transfer link
- [ ] Rejected state shows red banner with rejection reason
- [ ] Flash success/error messages render at the top
