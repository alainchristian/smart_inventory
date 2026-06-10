<div class="settings-root">
<style>
/* ══════════════════════════════════════════
   BUSINESS SETTINGS — Design System Aligned
   Zero hardcoded hex. All from CSS vars.
══════════════════════════════════════════ */

.settings-root {
    font-family: var(--font);
}

/* ── Page header ─────────────────────────── */
.settings-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 24px;
}
.settings-title {
    font-size: 1.625rem;
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.4px;
    margin: 0 0 4px;
}
.settings-subtitle {
    font-size: 0.875rem;
    color: var(--text-dim);
    font-family: var(--mono);
    margin: 0;
}

/* ── Status KPI strip ───────────────────── */
.settings-kpi-strip {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 10px;
    margin-bottom: 28px;
}
.settings-kpi {
    background: var(--surface);
    border: none;
    border-radius: var(--radius-sm, 10px);
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    box-shadow: var(--shadow-card);
}
.settings-kpi-label {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-dim);
    line-height: 1.2;
}
.settings-kpi-val {
    font-size: 0.8125rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 5px;
}
.settings-kpi-val.on  { color: var(--green); }
.settings-kpi-val.off { color: var(--red);   }
.settings-kpi-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}
.settings-kpi-dot.on  { background: var(--green); }
.settings-kpi-dot.off { background: var(--red);   }

/* ── Section label (from design system) ─── */
.page-section-label {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--text-dim);
    margin: 1.5rem 0 0.625rem;
}
.page-section-label:first-of-type { margin-top: 0; }

/* ── Section card (from design system) ───── */
.section-card {
    background: var(--surface);
    border: none;
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 12px;
    box-shadow: var(--shadow-card);
}
.section-card-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 22px;
    border-bottom: 1px solid var(--border);
}
.section-card-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.section-card-title {
    font-size: 0.9375rem;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}
.section-card-sub {
    font-size: 0.75rem;
    color: var(--text-dim);
    margin: 2px 0 0;
}

/* ── Settings row ───────────────────────── */
.st-row {
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: start;
    gap: 20px;
    padding: 15px 22px;
    border-bottom: 1px solid var(--border);
    transition: background var(--tr, .18s ease);
}
.st-row:last-child { border-bottom: none; }
.st-row:hover      { background: var(--surface2, var(--surface)); }
.st-row.full-width { grid-template-columns: 1fr; }
.st-row.disabled   { opacity: .45; pointer-events: none; }

.st-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 3px;
    line-height: 1.3;
}
.st-hint {
    font-size: 0.75rem;
    color: var(--text-dim);
    line-height: 1.55;
    max-width: 520px;
}

/* ── Toggle switch ──────────────────────── */
.st-toggle {
    position: relative;
    width: 42px;
    height: 23px;
    flex-shrink: 0;
    cursor: pointer;
    margin-top: 2px;
}
.st-toggle input { position: absolute; opacity: 0; width: 0; height: 0; }
.st-toggle-track {
    position: absolute;
    inset: 0;
    border-radius: 23px;
    background: var(--border);
    border: 1.5px solid var(--border);
    transition: background var(--tr, .18s), border-color var(--tr, .18s);
}
.st-toggle input:checked ~ .st-toggle-track {
    background: var(--accent);
    border-color: var(--accent);
}
.st-toggle-knob {
    position: absolute;
    top: 2.5px;
    left: 2.5px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,.18);
    transition: transform var(--tr, .18s);
    pointer-events: none;
}
.st-toggle input:checked ~ .st-toggle-track .st-toggle-knob {
    transform: translateX(19px);
}

/* ── Number input ───────────────────────── */
.st-input-wrap { display: flex; align-items: center; gap: 8px; }
.st-input {
    width: 130px;
    padding: 7px 12px;
    border: 1.5px solid var(--border);
    border-radius: 9px;
    font-size: 0.875rem;
    font-weight: 700;
    font-family: var(--mono);
    background: var(--surface);
    color: var(--text);
    outline: none;
    text-align: right;
    transition: border-color var(--tr, .18s), box-shadow var(--tr, .18s);
    box-sizing: border-box;
}
.st-input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-dim);
}
.st-input:disabled { background: var(--surface2, var(--surface)); cursor: not-allowed; }
.st-input-unit { font-size: 0.8125rem; color: var(--text-dim); white-space: nowrap; }
.st-input-error { font-size: 0.6875rem; color: var(--red); margin-top: 4px; text-align: right; }

/* ── Category chips ─────────────────────── */
.st-chips-wrap {
    padding: 12px 22px 16px;
    border-top: 1px solid var(--border);
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}
.st-chips-label {
    width: 100%;
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--text-dim);
    margin-bottom: 4px;
}
.st-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 20px;
    cursor: pointer;
    border: 1.5px solid var(--border);
    background: var(--surface2, var(--surface));
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--text-dim);
    transition: all var(--tr, .18s);
    user-select: none;
}
.st-chip:hover {
    border-color: var(--accent);
    color: var(--accent);
    background: var(--accent-dim);
}
.st-chip.on {
    border-color: var(--accent);
    background: var(--accent-dim);
    color: var(--accent);
}
.st-chip-box {
    width: 14px;
    height: 14px;
    border-radius: 4px;
    border: 1.5px solid currentColor;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    transition: background var(--tr, .18s);
}
.st-chip.on .st-chip-box { background: var(--accent); border-color: var(--accent); }
.st-chip-parent { font-size: 0.625rem; opacity: .55; margin-left: 2px; }

/* ── Sticky save bar ────────────────────── */
.settings-save-bar {
    position: sticky;
    bottom: 0;
    z-index: 20;
    background: var(--surface);
    border: none;
    border-radius: 14px;
    box-shadow: var(--shadow-card-hover);
    padding: 14px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-top: 16px;
    box-shadow: 0 -4px 20px rgba(26,31,54,.07);
}
.settings-save-hint {
    font-size: 0.8125rem;
    color: var(--text-dim);
    line-height: 1.45;
}
.settings-save-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
    flex-shrink: 0;
}

/* Saved toast (inline) */
.settings-saved-toast {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--green);
    padding: 7px 14px;
    background: var(--green-dim);
    border: 1px solid rgba(14,158,134,.25);
    border-radius: 8px;
    white-space: nowrap;
}

/* Save button (filter-action-btn pattern) */
.settings-save-btn {
    padding: 9px 26px;
    background: var(--accent);
    color: #fff;
    border: none;
    border-radius: 9px;
    font-size: 0.875rem;
    font-weight: 700;
    cursor: pointer;
    font-family: var(--font);
    display: flex;
    align-items: center;
    gap: 7px;
    box-shadow: 0 4px 14px rgba(59,111,212,.22);
    transition: opacity var(--tr, .18s), transform var(--tr, .18s), box-shadow var(--tr, .18s);
}
.settings-save-btn:hover   { opacity: .88; transform: translateY(-1px); }
.settings-save-btn:active  { transform: translateY(0); }
.settings-save-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }

/* Spinner */
.st-spin {
    width: 14px; height: 14px;
    border: 2px solid rgba(255,255,255,.35);
    border-top-color: #fff;
    border-radius: 50%;
    animation: st-spin .65s linear infinite;
    flex-shrink: 0;
}
@keyframes st-spin { to { transform: rotate(360deg); } }

/* ── Mobile ─────────────────────────────── */
@media (max-width: 768px) {
    .settings-kpi-strip {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (max-width: 600px) {
    .settings-header { flex-direction: column; }
    .settings-kpi-strip { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .section-card-header { padding: 13px 16px; }
    .st-row { padding: 13px 16px; gap: 12px; }
    .st-input { width: 100%; }
    .st-input-wrap { flex-direction: column; align-items: flex-end; }
    .st-chips-wrap { padding: 11px 16px 14px; }
    .settings-save-bar { padding: 12px 16px; flex-direction: column; align-items: stretch; }
    .settings-save-btn { width: 100%; justify-content: center; }
    .settings-save-hint { font-size: 0.75rem; }
}
@media (max-width: 480px) {
    .settings-kpi-strip { grid-template-columns: repeat(2, 1fr); }
}
</style>

{{-- ── Page header ──────────────────────────────────────────── --}}
<div class="settings-header">
    <div>
        <h1 class="settings-title">Business Settings</h1>
        <p class="settings-subtitle">Operational rules &middot; changes take effect immediately after saving</p>
    </div>
</div>

{{-- ── Status KPI strip ─────────────────────────────────────── --}}
<div class="settings-kpi-strip">
    <div class="settings-kpi">
        <div class="settings-kpi-label">Indiv. Sales</div>
        <div class="settings-kpi-val {{ $allowIndividualItemSales ? 'on' : 'off' }}">
            <span class="settings-kpi-dot {{ $allowIndividualItemSales ? 'on' : 'off' }}"></span>
            {{ $allowIndividualItemSales ? 'Enabled' : 'Disabled' }}
        </div>
    </div>
    <div class="settings-kpi">
        <div class="settings-kpi-label">Returns</div>
        <div class="settings-kpi-val {{ $allowSellerReturns ? 'on' : 'off' }}">
            <span class="settings-kpi-dot {{ $allowSellerReturns ? 'on' : 'off' }}"></span>
            {{ $allowSellerReturns ? 'Allowed' : 'Blocked' }}
        </div>
    </div>
    <div class="settings-kpi">
        <div class="settings-kpi-label">Credit Sales</div>
        <div class="settings-kpi-val {{ $allowCreditSales ? 'on' : 'off' }}">
            <span class="settings-kpi-dot {{ $allowCreditSales ? 'on' : 'off' }}"></span>
            {{ $allowCreditSales ? 'Enabled' : 'Disabled' }}
        </div>
    </div>
    <div class="settings-kpi">
        <div class="settings-kpi-label">Price Override</div>
        <div class="settings-kpi-val {{ $allowPriceOverride ? 'on' : 'off' }}">
            <span class="settings-kpi-dot {{ $allowPriceOverride ? 'on' : 'off' }}"></span>
            {{ $allowPriceOverride ? 'Allowed' : 'Locked' }}
        </div>
    </div>
    <div class="settings-kpi">
        <div class="settings-kpi-label">Card Payments</div>
        <div class="settings-kpi-val {{ $allowCardPayment ? 'on' : 'off' }}">
            <span class="settings-kpi-dot {{ $allowCardPayment ? 'on' : 'off' }}"></span>
            {{ $allowCardPayment ? 'On' : 'Off' }}
        </div>
    </div>
    <div class="settings-kpi">
        <div class="settings-kpi-label">Bank Transfer</div>
        <div class="settings-kpi-val {{ $allowBankTransferPayment ? 'on' : 'off' }}">
            <span class="settings-kpi-dot {{ $allowBankTransferPayment ? 'on' : 'off' }}"></span>
            {{ $allowBankTransferPayment ? 'On' : 'Off' }}
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     SECTION 1: Sales Rules
══════════════════════════════════════════ --}}
<div class="page-section-label">Sales</div>
<div class="section-card">
    <div class="section-card-header">
        <div class="section-card-icon" style="background:var(--accent-dim)">
            <svg width="18" height="18" fill="none" stroke="var(--accent)"
                 stroke-width="2" viewBox="0 0 24 24">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 01-8 0"/>
            </svg>
        </div>
        <div>
            <h3 class="section-card-title">Sales Rules</h3>
            <p class="section-card-sub">Control how products can be sold at the shop level</p>
        </div>
    </div>

    {{-- Individual item sales --}}
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

    {{-- Category filter (shown only when individual sales on) --}}
    @if($allowIndividualItemSales)
    <div class="st-row full-width" style="padding-bottom:4px">
        <div>
            <div class="st-label">Restrict individual sales to specific categories</div>
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
        <div wire:click="toggleCategory({{ $cat->id }})" class="st-chip {{ $on ? 'on' : '' }}">
            <div class="st-chip-box">
                @if($on)
                <svg width="9" height="9" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="3.5">
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

{{-- ══════════════════════════════════════════
     SECTION 2: Returns Policy
══════════════════════════════════════════ --}}
<div class="page-section-label">Returns</div>
<div class="section-card">
    <div class="section-card-header">
        <div class="section-card-icon" style="background:var(--violet-dim)">
            <svg width="18" height="18" fill="none" stroke="var(--violet)"
                 stroke-width="2" viewBox="0 0 24 24">
                <polyline points="1 4 1 10 7 10"/>
                <path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
            </svg>
        </div>
        <div>
            <h3 class="section-card-title">Returns Policy</h3>
            <p class="section-card-sub">Control who can process returns and under what conditions</p>
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
                Set to&nbsp;0 to require approval on all returns regardless of amount.
            </div>
        </div>
        <div>
            <div class="st-input-wrap">
                <input wire:model="returnApprovalThreshold"
                       type="number" min="0" class="st-input" placeholder="100000">
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
                Set to&nbsp;0 to allow returns at any time with no day limit.
            </div>
        </div>
        <div>
            <div class="st-input-wrap">
                <input wire:model="maxReturnDays"
                       type="number" min="0" class="st-input" placeholder="30">
                <span class="st-input-unit">days</span>
            </div>
            @error('maxReturnDays')
                <div class="st-input-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     SECTION 3: Credit Policy
══════════════════════════════════════════ --}}
<div class="page-section-label">Credit</div>
<div class="section-card">
    <div class="section-card-header">
        <div class="section-card-icon" style="background:var(--amber-dim)">
            <svg width="18" height="18" fill="none" stroke="var(--amber)"
                 stroke-width="2" viewBox="0 0 24 24">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
        </div>
        <div>
            <h3 class="section-card-title">Credit Policy</h3>
            <p class="section-card-sub">Control how credit sales work at the point of sale</p>
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
                When enabled, credit payment is blocked until a customer is selected
                from the registry. Strongly recommended to maintain accurate credit tracking.
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
                New credit sales are blocked if the customer already owes this amount
                or more. Set to&nbsp;0 for no limit.
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

    <div class="st-row {{ !$allowCreditSales ? 'disabled' : '' }}">
        <div>
            <div class="st-label">Overdue credit threshold</div>
            <div class="st-hint">
                Flag a customer as overdue if they have an outstanding balance with no
                repayment in this many days. Set to&nbsp;0 to disable overdue alerts entirely.
            </div>
        </div>
        <div>
            <div class="st-input-wrap">
                <input wire:model="overdueCreditDays"
                       type="number" min="0" max="365" class="st-input"
                       placeholder="14"
                       {{ !$allowCreditSales ? 'disabled' : '' }}>
                <span class="st-input-unit">days</span>
            </div>
            @error('overdueCreditDays')
                <div class="st-input-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     SECTION 4: Price Override
══════════════════════════════════════════ --}}
<div class="page-section-label">Pricing</div>
<div class="section-card">
    <div class="section-card-header">
        <div class="section-card-icon" style="background:var(--green-dim)">
            <svg width="18" height="18" fill="none" stroke="var(--green)"
                 stroke-width="2" viewBox="0 0 24 24">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
            </svg>
        </div>
        <div>
            <h3 class="section-card-title">Price Override</h3>
            <p class="section-card-sub">Control when sellers need approval to change prices</p>
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
                Price changes beyond this percentage from the listed price require owner
                approval before the sale can complete.
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

{{-- ══════════════════════════════════════════
     SECTION 5: Inventory Policy
══════════════════════════════════════════ --}}
<div class="page-section-label">Inventory</div>
<div class="section-card">
    <div class="section-card-header">
        <div class="section-card-icon" style="background:var(--green-dim)">
            <svg width="18" height="18" fill="none" stroke="var(--green)"
                 stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <div>
            <h3 class="section-card-title">Inventory Policy</h3>
            <p class="section-card-sub">Define how many boxes triggers a low-stock flag at each location type</p>
        </div>
    </div>

    <div class="st-row">
        <div>
            <div class="st-label">Low stock threshold &mdash; Shops</div>
            <div class="st-hint">
                A product at a shop with this many boxes or fewer will be flagged as low stock
                on dashboards, stock alerts, and the notification bar.
            </div>
        </div>
        <div>
            <div class="st-input-wrap">
                <input wire:model="lowStockBoxesShop"
                       type="number" min="1" class="st-input" placeholder="2">
                <span class="st-input-unit">boxes</span>
            </div>
            @error('lowStockBoxesShop')
                <div class="st-input-error">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="st-row">
        <div>
            <div class="st-label">Low stock threshold &mdash; Warehouses</div>
            <div class="st-hint">
                A product at a warehouse with this many boxes or fewer will be flagged on
                the warehouse manager dashboard and stock levels page.
            </div>
        </div>
        <div>
            <div class="st-input-wrap">
                <input wire:model="lowStockBoxesWarehouse"
                       type="number" min="1" class="st-input" placeholder="5">
                <span class="st-input-unit">boxes</span>
            </div>
            @error('lowStockBoxesWarehouse')
                <div class="st-input-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     SECTION 6: Payment Methods
══════════════════════════════════════════ --}}
<div class="page-section-label">Payments</div>
<div class="section-card">
    <div class="section-card-header">
        <div class="section-card-icon" style="background:var(--violet-dim)">
            <svg width="18" height="18" fill="none" stroke="var(--violet)"
                 stroke-width="2" viewBox="0 0 24 24">
                <rect x="1" y="4" width="22" height="16" rx="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
        </div>
        <div>
            <h3 class="section-card-title">Payment Methods</h3>
            <p class="section-card-sub">Cash and Mobile Money are always available. Toggle optional channels below.</p>
        </div>
    </div>

    {{-- Always-on info row --}}
    <div class="st-row" style="background:var(--surface2,var(--surface))">
        <div>
            <div class="st-label" style="color:var(--text-dim);font-weight:500">
                Cash &amp; Mobile Money
            </div>
            <div class="st-hint">These channels are always enabled and cannot be disabled.</div>
        </div>
        <span style="font-size:.75rem;font-weight:700;color:var(--green);
                     background:var(--green-dim);padding:3px 10px;border-radius:20px;
                     white-space:nowrap;align-self:center">
            Always on
        </span>
    </div>

    <div class="st-row">
        <div>
            <div class="st-label">Allow card payment</div>
            <div class="st-hint">
                When enabled, sellers can accept debit/credit card payments at checkout.
                Turn on only if a card terminal is in use.
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
                Turn on only if your shop accepts direct transfers.
            </div>
        </div>
        <label class="st-toggle">
            <input type="checkbox" wire:model.live="allowBankTransferPayment">
            <div class="st-toggle-track"><div class="st-toggle-knob"></div></div>
        </label>
    </div>
</div>

{{-- ── Sticky save bar ──────────────────────────────────────── --}}
<div class="settings-save-bar">
    <p class="settings-save-hint">
        Changes are applied system-wide immediately after saving.<br>
        Sellers will see the new rules on their next action.
    </p>
    <div class="settings-save-actions">
        {{-- Inline saved confirmation --}}
        <div x-data="{ show: false }"
             x-on:settings-saved.window="show = true; setTimeout(() => show = false, 3200)"
             x-show="show"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-end="opacity-0"
             x-cloak
             class="settings-saved-toast">
            <svg width="14" height="14" fill="none" stroke="currentColor"
                 stroke-width="2.5" viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            Settings saved
        </div>

        <button wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
                class="settings-save-btn">
            <span wire:loading.remove wire:target="save">Save Settings</span>
            <span wire:loading wire:target="save" style="display:none;align-items:center;gap:7px">
                <span class="st-spin"></span>
                Saving&hellip;
            </span>
        </button>
    </div>
</div>

</div>
