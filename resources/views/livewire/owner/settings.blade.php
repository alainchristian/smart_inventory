<div style="font-family:var(--font);max-width:900px;margin:0 auto">
<style>
/* ══════════════════════════════════════════════
   SETTINGS PAGE — Design System Aligned
══════════════════════════════════════════════ */

/* Page header */
.st-page-title {
    font-size:26px;font-weight:800;color:var(--text);
    letter-spacing:-.4px;margin-bottom:4px;
}
.st-page-sub {
    font-size:14px;color:var(--text-dim);font-family:var(--mono);
}

/* Section card */
.st-card {
    background:var(--surface);border:1px solid var(--border);
    border-radius:var(--r);margin-bottom:16px;overflow:hidden;
}

/* Section header */
.st-card-head {
    display:flex;align-items:center;gap:14px;
    padding:18px 24px 16px;
    border-bottom:1px solid var(--border);
}
.st-card-icon {
    width:38px;height:38px;border-radius:10px;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;
}
.st-card-title { font-size:15px;font-weight:700;color:var(--text);line-height:1.2 }
.st-card-desc  { font-size:12px;color:var(--text-dim);margin-top:2px;line-height:1.4 }

/* Settings row */
.st-row {
    display:grid;
    grid-template-columns:1fr auto;
    align-items:start;
    gap:20px;
    padding:16px 24px;
    border-bottom:1px solid var(--border);
    transition:background var(--tr);
}
.st-row:last-child { border-bottom:none }
.st-row:hover      { background:var(--surface2) }
.st-row.full-width {
    grid-template-columns:1fr;
}

.st-label {
    font-size:14px;font-weight:600;color:var(--text);
    margin-bottom:3px;line-height:1.3;
}
.st-hint {
    font-size:12px;color:var(--text-dim);line-height:1.55;
    max-width:540px;
}

/* Toggle */
.st-toggle { position:relative;width:42px;height:23px;flex-shrink:0;cursor:pointer }
.st-toggle input { position:absolute;opacity:0;width:0;height:0 }
.st-toggle-track {
    position:absolute;inset:0;border-radius:23px;
    background:var(--surface3);border:1.5px solid var(--border);
    transition:background var(--tr),border-color var(--tr);
}
.st-toggle input:checked ~ .st-toggle-track {
    background:var(--accent);border-color:var(--accent);
}
.st-toggle-knob {
    position:absolute;top:2.5px;left:2.5px;
    width:18px;height:18px;border-radius:50%;
    background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.18);
    transition:transform var(--tr);pointer-events:none;
}
.st-toggle input:checked ~ .st-toggle-track .st-toggle-knob {
    transform:translateX(19px);
}

/* Number input */
.st-input-wrap { display:flex;align-items:center;gap:8px }
.st-input {
    width:130px;padding:7px 12px;
    border:1.5px solid var(--border);border-radius:9px;
    font-size:14px;font-weight:700;font-family:var(--mono);
    background:var(--surface);color:var(--text);outline:none;
    text-align:right;transition:border-color var(--tr);
    box-sizing:border-box;
}
.st-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.st-input-unit { font-size:13px;color:var(--text-dim);white-space:nowrap }
.st-input-error { font-size:10px;color:var(--red);margin-top:4px;text-align:right }

/* Category chips */
.st-chips-wrap {
    padding:14px 24px 18px;
    border-top:1px solid var(--border);
    display:flex;flex-wrap:wrap;gap:7px;
}
.st-chips-label {
    width:100%;font-size:10px;font-weight:700;letter-spacing:.5px;
    text-transform:uppercase;color:var(--text-dim);margin-bottom:4px;
}
.st-chip {
    display:inline-flex;align-items:center;gap:6px;
    padding:5px 12px;border-radius:20px;cursor:pointer;
    border:1.5px solid var(--border);background:var(--surface2);
    font-size:13px;font-weight:600;color:var(--text-sub);
    transition:all var(--tr);user-select:none;
}
.st-chip:hover { border-color:var(--accent);color:var(--accent);background:var(--accent-dim) }
.st-chip.on {
    border-color:var(--accent);background:var(--accent-dim);color:var(--accent);
}
.st-chip-box {
    width:14px;height:14px;border-radius:4px;border:1.5px solid currentColor;
    display:grid;place-items:center;flex-shrink:0;transition:background var(--tr);
}
.st-chip.on .st-chip-box { background:var(--accent);border-color:var(--accent) }
.st-chip-parent {
    font-size:10px;opacity:.55;margin-left:2px;
}

/* Disabled overlay row */
.st-row.disabled { opacity:.45;pointer-events:none }

/* Status pills */
.st-pill {
    display:inline-flex;align-items:center;gap:4px;
    font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;
    white-space:nowrap;flex-shrink:0;
}
.st-pill.on  { background:var(--green-dim);color:var(--green) }
.st-pill.off { background:var(--red-dim);color:var(--red) }

/* Sticky save bar */
.st-save-bar {
    position:sticky;bottom:0;z-index:20;
    background:var(--surface);border:1px solid var(--border);
    border-radius:var(--r);
    padding:14px 24px;
    display:flex;align-items:center;justify-content:space-between;
    gap:16px;margin-top:16px;
    box-shadow:0 -4px 20px rgba(26,31,54,.07);
}
.st-save-hint { font-size:13px;color:var(--text-dim) }
.st-save-btn {
    padding:10px 30px;background:var(--accent);color:#fff;
    border:none;border-radius:10px;font-size:14px;font-weight:700;
    cursor:pointer;font-family:var(--font);
    box-shadow:0 4px 14px rgba(59,111,212,.25);
    transition:opacity var(--tr),transform var(--tr);
}
.st-save-btn:hover   { opacity:.88;transform:translateY(-1px) }
.st-save-btn:active  { transform:translateY(0) }
.st-save-btn:disabled { opacity:.5;cursor:not-allowed;transform:none }

/* Mobile */
@media(max-width:600px) {
    .st-card-head { padding:14px 16px 12px }
    .st-row { padding:13px 16px;gap:12px }
    .st-input { width:100% }
    .st-input-wrap { flex-direction:column;align-items:flex-end }
    .st-chips-wrap { padding:12px 16px 14px }
    .st-save-bar { padding:12px 16px;flex-direction:column;align-items:stretch }
    .st-save-btn { width:100%;text-align:center }
}
@keyframes spin { to { transform: rotate(360deg) } }
</style>

{{-- ── Page header ─────────────────────────────────────────────────── --}}
<div style="margin-bottom:24px;display:flex;align-items:flex-start;
            justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div>
        <div class="st-page-title">Business Settings</div>
        <div class="st-page-sub">
            Operational rules · changes take effect immediately after saving
        </div>
    </div>
    {{-- Quick status summary --}}
    <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
        <span class="st-pill {{ $allowCreditSales ? 'on' : 'off' }}">
            {{ $allowCreditSales ? '✓' : '✕' }} Credit Sales
        </span>
        <span class="st-pill {{ $allowSellerReturns ? 'on' : 'off' }}">
            {{ $allowSellerReturns ? '✓' : '✕' }} Seller Returns
        </span>
        <span class="st-pill {{ $allowPriceOverride ? 'on' : 'off' }}">
            {{ $allowPriceOverride ? '✓' : '✕' }} Price Override
        </span>
    </div>
</div>

{{-- ── SECTION 1: Sales Rules ───────────────────────────────────────── --}}
<div class="st-card">
    <div class="st-card-head">
        <div class="st-card-icon"
             style="background:var(--accent-dim)">
            <svg width="18" height="18" fill="none" stroke="var(--accent)"
                 stroke-width="2" viewBox="0 0 24 24">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 01-8 0"/>
            </svg>
        </div>
        <div>
            <div class="st-card-title">Sales Rules</div>
            <div class="st-card-desc">Control how products can be sold at the shop level</div>
        </div>
    </div>

    {{-- Individual item sales toggle --}}
    <div class="st-row">
        <div>
            <div class="st-label">Allow individual item sales</div>
            <div class="st-hint">
                When enabled, sellers can sell loose items from a box.
                Disable to force full-box-only sales across all categories.
            </div>
        </div>
        <label class="st-toggle">
            <input type="checkbox" wire:model.live="allowIndividualItemSales">
            <div class="st-toggle-track"><div class="st-toggle-knob"></div></div>
        </label>
    </div>

    {{-- Category filter --}}
    @if($allowIndividualItemSales)
    <div class="st-row full-width" style="padding-bottom:4px">
        <div>
            <div class="st-label">
                Restrict individual sales to specific categories
            </div>
            <div class="st-hint">
                Leave all unselected to allow individual sales for every category.
                Select specific ones to restrict only to those.
            </div>
        </div>
    </div>
    <div class="st-chips-wrap">
        <div class="st-chips-label">
            @if(empty($individualSaleCategoryIds))
                All categories allowed
            @else
                {{ count($individualSaleCategoryIds) }} categor{{ count($individualSaleCategoryIds) === 1 ? 'y' : 'ies' }} selected
            @endif
        </div>
        @foreach($categories as $cat)
        @php $on = in_array($cat->id, $individualSaleCategoryIds) @endphp
        <div wire:click="toggleCategory({{ $cat->id }})"
             class="st-chip {{ $on ? 'on' : '' }}">
            <div class="st-chip-box">
                @if($on)
                <svg width="9" height="9" viewBox="0 0 24 24" fill="none"
                     stroke="#fff" stroke-width="3.5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                @endif
            </div>
            {{ $cat->name }}
            @if($cat->parent)
                <span class="st-chip-parent">{{ $cat->parent->name }}</span>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- ── SECTION 2: Returns Policy ────────────────────────────────────── --}}
<div class="st-card">
    <div class="st-card-head">
        <div class="st-card-icon"
             style="background:var(--violet-dim)">
            <svg width="18" height="18" fill="none" stroke="var(--violet)"
                 stroke-width="2" viewBox="0 0 24 24">
                <polyline points="1 4 1 10 7 10"/>
                <path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
            </svg>
        </div>
        <div>
            <div class="st-card-title">Returns Policy</div>
            <div class="st-card-desc">Control who can process returns and under what conditions</div>
        </div>
    </div>

    <div class="st-row">
        <div>
            <div class="st-label">Allow shop managers to process returns</div>
            <div class="st-hint">
                When disabled, only the owner can process customer returns.
                Shop managers will see a message directing them to contact the owner.
            </div>
        </div>
        <label class="st-toggle">
            <input type="checkbox" wire:model.live="allowSellerReturns">
            <div class="st-toggle-track"><div class="st-toggle-knob"></div></div>
        </label>
    </div>

    <div class="st-row">
        <div>
            <div class="st-label">Approval threshold</div>
            <div class="st-hint">
                Returns above this refund amount require owner approval before finalising.
                Set to 0 to require approval on all returns regardless of amount.
            </div>
        </div>
        <div>
            <div class="st-input-wrap">
                <input wire:model="returnApprovalThreshold"
                       type="number" min="0" class="st-input"
                       placeholder="100000">
                <span class="st-input-unit">RWF</span>
            </div>
            @error('returnApprovalThreshold')
                <div class="st-input-error">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="st-row">
        <div>
            <div class="st-label">Maximum days after sale</div>
            <div class="st-hint">
                Returns for sales older than this are blocked with an error.
                Set to 0 to allow returns at any time with no day limit.
            </div>
        </div>
        <div>
            <div class="st-input-wrap">
                <input wire:model="maxReturnDays"
                       type="number" min="0" class="st-input"
                       placeholder="30">
                <span class="st-input-unit">days</span>
            </div>
            @error('maxReturnDays')
                <div class="st-input-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- ── SECTION 3: Credit Policy ─────────────────────────────────────── --}}
<div class="st-card">
    <div class="st-card-head">
        <div class="st-card-icon"
             style="background:var(--amber-dim)">
            <svg width="18" height="18" fill="none" stroke="var(--amber)"
                 stroke-width="2" viewBox="0 0 24 24">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
        </div>
        <div>
            <div class="st-card-title">Credit Policy</div>
            <div class="st-card-desc">Control how credit sales work at the point of sale</div>
        </div>
    </div>

    <div class="st-row">
        <div>
            <div class="st-label">Allow credit sales</div>
            <div class="st-hint">
                When disabled, the credit channel is hidden entirely in POS checkout.
                No seller can offer credit under any circumstances.
            </div>
        </div>
        <label class="st-toggle">
            <input type="checkbox" wire:model.live="allowCreditSales">
            <div class="st-toggle-track"><div class="st-toggle-knob"></div></div>
        </label>
    </div>

    <div class="st-row {{ !$allowCreditSales ? 'disabled' : '' }}">
        <div>
            <div class="st-label">Require registered customer for credit</div>
            <div class="st-hint">
                When enabled, credit payment is blocked until a customer is
                selected from the registry. Strongly recommended to maintain
                accurate credit tracking.
            </div>
        </div>
        <label class="st-toggle">
            <input type="checkbox" wire:model="creditRequiresCustomer"
                   {{ !$allowCreditSales ? 'disabled' : '' }}>
            <div class="st-toggle-track"><div class="st-toggle-knob"></div></div>
        </label>
    </div>

    <div class="st-row {{ !$allowCreditSales ? 'disabled' : '' }}">
        <div>
            <div class="st-label">Maximum outstanding credit per customer</div>
            <div class="st-hint">
                New credit sales are blocked if the customer already owes this
                amount or more. Set to 0 for no limit.
            </div>
        </div>
        <div>
            <div class="st-input-wrap">
                <input wire:model="maxCreditPerCustomer"
                       type="number" min="0" class="st-input"
                       placeholder="0 = unlimited"
                       {{ !$allowCreditSales ? 'disabled' : '' }}>
                <span class="st-input-unit">RWF</span>
            </div>
            @error('maxCreditPerCustomer')
                <div class="st-input-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- ── SECTION 4: Price Override ────────────────────────────────────── --}}
<div class="st-card">
    <div class="st-card-head">
        <div class="st-card-icon"
             style="background:var(--green-dim)">
            <svg width="18" height="18" fill="none" stroke="var(--green)"
                 stroke-width="2" viewBox="0 0 24 24">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
            </svg>
        </div>
        <div>
            <div class="st-card-title">Price Override</div>
            <div class="st-card-desc">Control when sellers need approval to change prices</div>
        </div>
    </div>

    <div class="st-row">
        <div>
            <div class="st-label">Allow sellers to modify prices</div>
            <div class="st-hint">
                When disabled, sellers cannot change prices at all.
                The price displayed is always exactly what is set on the product.
            </div>
        </div>
        <label class="st-toggle">
            <input type="checkbox" wire:model.live="allowPriceOverride">
            <div class="st-toggle-track"><div class="st-toggle-knob"></div></div>
        </label>
    </div>

    <div class="st-row {{ !$allowPriceOverride ? 'disabled' : '' }}">
        <div>
            <div class="st-label">Approval threshold</div>
            <div class="st-hint">
                Price changes beyond this percentage from the listed price
                require owner approval before the sale can complete.
            </div>
        </div>
        <div>
            <div class="st-input-wrap">
                <input wire:model="priceOverrideThreshold"
                       type="number" min="1" max="100" class="st-input"
                       placeholder="20"
                       {{ !$allowPriceOverride ? 'disabled' : '' }}>
                <span class="st-input-unit">%</span>
            </div>
            @error('priceOverrideThreshold')
                <div class="st-input-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- ── SECTION 5: Payment Methods ───────────────────────────────────── --}}
<div class="st-card">
    <div class="st-card-head">
        <div class="st-card-icon" style="background:rgba(99,102,241,.1)">
            <svg width="18" height="18" fill="none" stroke="#6366f1" stroke-width="2" viewBox="0 0 24 24">
                <rect x="1" y="4" width="22" height="16" rx="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
        </div>
        <div>
            <div class="st-card-title">Payment Methods</div>
            <div class="st-card-desc">Choose which payment channels are available at point of sale. Cash and Mobile Money are always on.</div>
        </div>
    </div>

    <div class="st-row">
        <div>
            <div class="st-label">Allow card payment</div>
            <div class="st-hint">
                When enabled, sellers can accept debit/credit card payments at checkout.
                Disabled by default — turn on only if a card terminal is in use.
            </div>
        </div>
        <label class="st-toggle">
            <input type="checkbox" wire:model.live="allowCardPayment">
            <div class="st-toggle-track"><div class="st-toggle-knob"></div></div>
        </label>
    </div>

    <div class="st-row">
        <div>
            <div class="st-label">Allow bank transfer payment</div>
            <div class="st-hint">
                When enabled, sellers can record bank transfer payments and enter a reference number.
                Disabled by default — turn on only if your shop accepts direct transfers.
            </div>
        </div>
        <label class="st-toggle">
            <input type="checkbox" wire:model.live="allowBankTransferPayment">
            <div class="st-toggle-track"><div class="st-toggle-knob"></div></div>
        </label>
    </div>
</div>

{{-- ── Save bar ─────────────────────────────────────────────────────── --}}
<div class="st-save-bar">
    <div class="st-save-hint">
        Changes are applied system-wide immediately after saving.
        Sellers will see the new rules on their next action.
    </div>
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;justify-content:flex-end">
        <div x-data="{ show: false }"
             x-on:settings-saved.window="show = true; setTimeout(() => show = false, 3000)"
             x-show="show"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translateY(4px)"
             x-transition:enter-end="opacity-1 translateY(0)"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-1"
             x-transition:leave-end="opacity-0"
             x-cloak
             style="display:flex;align-items:center;gap:6px;font-size:13px;
                    font-weight:600;color:var(--green);padding:7px 14px;
                    background:var(--green-dim);border:1px solid rgba(14,158,134,.25);
                    border-radius:8px;white-space:nowrap">
            <svg width="14" height="14" fill="none" stroke="currentColor"
                 stroke-width="2.5" viewBox="0 0 24 24" style="margin-right:5px">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            Settings saved
        </div>
        <button wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
                class="st-save-btn">
            <span wire:loading.remove wire:target="save">Save Settings</span>
            <span wire:loading wire:target="save"
                  style="display:none;align-items:center;gap:8px">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5"
                     style="animation:spin 1s linear infinite">
                    <path d="M21 12a9 9 0 11-6.219-8.56"/>
                </svg>
                Saving…
            </span>
        </button>
    </div>
</div>

</div>
