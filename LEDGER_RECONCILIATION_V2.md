# Sales Analytics Ledger — Reconciliation Block (Modern Redesign)
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read LEDGER_RECONCILIATION_V2.md and follow every step in order."

---

## Read first

```bash
cat resources/views/livewire/owner/reports/sales-analytics.blade.php
```

Find the `TAB: SALES LEDGER` section. Confirm the following variables
exist in the `@php` block at the top of that tab:
- `$gp['revenue']`, `$gp['gross_profit']`, `$gp['total_cost']`, `$gp['margin_pct']`
- `$trueOutstanding`, `$totalCreditRepaid`, `$totalCreditGiven`, `$repaymentRate`

If the previous reconciliation block from LEDGER_RECONCILIATION.md was
already inserted, **remove it entirely** before inserting the new one below.

---

## STEP 1 — Insert the reconciliation block

**File:** `resources/views/livewire/owner/reports/sales-analytics.blade.php`

Find the end of the ledger table section — the line just before:

```blade
{{-- ══════════════════ TAB: PRICE AUDIT ══════════════════ --}}
@elseif($activeTab === 'audit')
```

Insert the entire block below at that position:

```blade
{{-- ── Revenue Reconciliation ──────────────────────────────────────── --}}
@php
    $collectedRevenue   = $gp['revenue'] - $trueOutstanding;
    $profitOnPaper      = $gp['gross_profit'];
    $profitInHand       = $collectedRevenue - $gp['total_cost'];
    $profitGap          = $profitOnPaper - $profitInHand;
    $collectedMarginPct = $collectedRevenue > 0
        ? round(($profitInHand / $collectedRevenue) * 100, 1)
        : 0;
    $creditRiskPct = $gp['revenue'] > 0
        ? round(($trueOutstanding / $gp['revenue']) * 100, 1)
        : 0;
    $gapIsMaterial = $profitGap > 0
        && $gp['gross_profit'] > 0
        && ($profitGap / $gp['gross_profit']) > 0.05;
@endphp

<style>
/* ── Reconciliation block ─────────────────────────────────────────── */
.recon-wrap {
    margin-top:24px;
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:var(--r);
    overflow:hidden;
}

/* Header */
.recon-header {
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:16px 22px;
    border-bottom:1px solid var(--border);
    gap:12px;
}
.recon-header-left { min-width:0; }
.recon-title {
    font-size:13px;
    font-weight:700;
    color:var(--text);
    letter-spacing:-.1px;
}
.recon-subtitle {
    font-size:11px;
    color:var(--text-dim);
    font-family:var(--mono);
    margin-top:2px;
}
.recon-risk-pill {
    flex-shrink:0;
    font-size:11px;
    font-weight:700;
    padding:4px 12px;
    border-radius:20px;
    font-family:var(--mono);
    background:var(--amber-dim);
    color:var(--amber);
    border:1px solid rgba(217,119,6,.2);
    white-space:nowrap;
}

/* Waterfall rows */
.recon-rows { padding:8px 0; }
.recon-row {
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:9px 22px;
    gap:12px;
    transition:background var(--tr);
}
.recon-row:hover { background:var(--surface2); }
.recon-row-label {
    display:flex;
    align-items:center;
    gap:10px;
    min-width:0;
}
.recon-dot {
    width:8px;
    height:8px;
    border-radius:50%;
    flex-shrink:0;
}
.recon-label-text {
    font-size:13px;
    color:var(--text-sub);
    white-space:nowrap;
}
.recon-label-sub {
    font-size:10px;
    color:var(--text-dim);
    font-family:var(--mono);
    margin-left:8px;
}
.recon-value {
    font-size:13px;
    font-weight:700;
    font-family:var(--mono);
    white-space:nowrap;
    flex-shrink:0;
}

/* Dividers */
.recon-divider {
    margin:4px 22px;
    border:none;
    border-top:1px solid var(--border);
}
.recon-divider-bold {
    margin:4px 22px;
    border:none;
    border-top:2px solid var(--border);
}

/* Subtotal row (net collected) */
.recon-subtotal {
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:10px 22px;
    gap:12px;
    background:var(--surface2);
}
.recon-subtotal-label {
    font-size:12px;
    font-weight:700;
    color:var(--text);
    text-transform:uppercase;
    letter-spacing:.5px;
}
.recon-subtotal-value {
    font-size:14px;
    font-weight:800;
    font-family:var(--mono);
    color:var(--text);
}

/* Result cards */
.recon-results {
    display:grid;
    grid-template-columns:1fr 1fr;
    border-top:1px solid var(--border);
}
.recon-result-card {
    padding:18px 22px;
}
.recon-result-card:first-child {
    border-right:1px solid var(--border);
}
.recon-result-label {
    font-size:10px;
    font-weight:700;
    letter-spacing:.6px;
    text-transform:uppercase;
    margin-bottom:8px;
}
.recon-result-value {
    font-size:24px;
    font-weight:800;
    font-family:var(--mono);
    letter-spacing:-1px;
    line-height:1;
    margin-bottom:6px;
}
.recon-result-meta {
    font-size:11px;
    color:var(--text-dim);
    font-family:var(--mono);
}
.recon-result-badge {
    display:inline-flex;
    align-items:center;
    gap:4px;
    font-size:10px;
    font-weight:700;
    padding:2px 8px;
    border-radius:20px;
    margin-top:6px;
}

/* Warning banner */
.recon-warning {
    margin:4px 16px 14px;
    padding:11px 14px;
    border-radius:8px;
    background:rgba(217,119,6,.06);
    border:1px solid rgba(217,119,6,.25);
    display:flex;
    align-items:flex-start;
    gap:10px;
}
.recon-warning-icon { font-size:14px; flex-shrink:0; margin-top:1px; }
.recon-warning-text {
    font-size:11px;
    color:var(--text-sub);
    line-height:1.6;
}

/* Mobile */
@media(max-width:600px) {
    .recon-header { padding:12px 16px; flex-wrap:wrap; }
    .recon-row { padding:8px 16px; }
    .recon-subtotal { padding:10px 16px; }
    .recon-results { grid-template-columns:1fr; }
    .recon-result-card:first-child {
        border-right:none;
        border-bottom:1px solid var(--border);
    }
    .recon-result-card { padding:16px; }
    .recon-result-value { font-size:20px; }
    .recon-divider,
    .recon-divider-bold { margin:4px 16px; }
    .recon-warning { margin:4px 12px 12px; }
    .recon-label-sub { display:none; }
    .recon-risk-pill { font-size:10px; padding:3px 9px; }
}
</style>

<div class="recon-wrap">

    {{-- Header --}}
    <div class="recon-header">
        <div class="recon-header-left">
            <div class="recon-title">Revenue Reconciliation</div>
            <div class="recon-subtitle">
                Paper profit vs cash profit · {{ $this->activeDateRangeLabel }}
            </div>
        </div>
        @if($creditRiskPct > 0)
        <div class="recon-risk-pill">⚠ {{ $creditRiskPct }}% on credit</div>
        @endif
    </div>

    {{-- Waterfall --}}
    <div class="recon-rows">

        {{-- Gross Revenue --}}
        <div class="recon-row">
            <div class="recon-row-label">
                <div class="recon-dot" style="background:var(--accent)"></div>
                <span class="recon-label-text">Gross Revenue</span>
                <span class="recon-label-sub">{{ $rev['transactions_count'] }} transactions</span>
            </div>
            <span class="recon-value" style="color:var(--accent)">
                {{ number_format($gp['revenue']) }}
            </span>
        </div>

        {{-- Less outstanding credit --}}
        @if($trueOutstanding > 0)
        <div class="recon-row">
            <div class="recon-row-label">
                <div class="recon-dot" style="background:var(--amber)"></div>
                <span class="recon-label-text">Less: Outstanding Credit</span>
                <span class="recon-label-sub">
                    {{ number_format($totalCreditRepaid) }} repaid · {{ $repaymentRate }}% rate
                </span>
            </div>
            <span class="recon-value" style="color:var(--amber)">
                ({{ number_format($trueOutstanding) }})
            </span>
        </div>
        @endif

        <hr class="recon-divider">

        {{-- Net Collected Revenue --}}
        <div class="recon-subtotal">
            <span class="recon-subtotal-label">Net Collected Revenue</span>
            <span class="recon-subtotal-value">{{ number_format($collectedRevenue) }}</span>
        </div>

        <hr class="recon-divider">

        {{-- Less COGS --}}
        <div class="recon-row">
            <div class="recon-row-label">
                <div class="recon-dot" style="background:var(--text-dim)"></div>
                <span class="recon-label-text">Less: Cost of Goods Sold</span>
            </div>
            <span class="recon-value" style="color:var(--text-sub)">
                ({{ number_format($gp['total_cost']) }})
            </span>
        </div>

        <hr class="recon-divider-bold">

    </div>

    {{-- Result cards --}}
    <div class="recon-results">

        {{-- Profit on paper --}}
        <div class="recon-result-card">
            <div class="recon-result-label" style="color:var(--text-dim)">
                Gross Profit on Paper
            </div>
            <div class="recon-result-value" style="color:var(--green)">
                {{ number_format($profitOnPaper) }}
            </div>
            <div class="recon-result-meta">{{ $gp['margin_pct'] }}% margin</div>
            <div class="recon-result-badge"
                 style="background:var(--green-dim);color:var(--green)">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5">
                    <path d="M13 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V9z"/>
                    <polyline points="13 2 13 9 20 9"/>
                </svg>
                Includes uncollected
            </div>
        </div>

        {{-- Profit in hand --}}
        @php
            $inHandIsGreen = $profitInHand >= $profitOnPaper * 0.90;
            $inHandColor   = $inHandIsGreen ? 'var(--green)' : 'var(--amber)';
            $inHandBg      = $inHandIsGreen ? 'var(--green-dim)' : 'var(--amber-dim)';
        @endphp
        <div class="recon-result-card"
             style="background:{{ $inHandIsGreen ? 'rgba(14,158,134,.03)' : 'rgba(217,119,6,.03)' }}">
            <div class="recon-result-label" style="color:{{ $inHandColor }}">
                Gross Profit in Hand
            </div>
            <div class="recon-result-value" style="color:{{ $inHandColor }}">
                {{ number_format($profitInHand) }}
            </div>
            <div class="recon-result-meta">
                {{ $collectedMarginPct }}% margin
                @if($profitGap > 0)
                    · <span style="color:var(--amber)">
                        {{ number_format($profitGap) }} gap
                    </span>
                @endif
            </div>
            <div class="recon-result-badge"
                 style="background:{{ $inHandBg }};color:{{ $inHandColor }}">
                @if($inHandIsGreen)
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Fully collected
                @else
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    Credit gap
                @endif
            </div>
        </div>

    </div>

    {{-- Material gap warning --}}
    @if($gapIsMaterial)
    <div class="recon-warning">
        <div class="recon-warning-icon">⚠️</div>
        <div class="recon-warning-text">
            <strong style="color:var(--amber)">
                {{ number_format($profitGap) }} RWF
                ({{ round(($profitGap / $gp['gross_profit']) * 100, 1) }}%
                of gross profit)
            </strong>
            is earned but not yet collected —
            tied to {{ number_format($trueOutstanding) }} RWF in outstanding
            customer credit.
            Recovering credit balances will close this gap.
        </div>
    </div>
    @endif

</div>
```

---

## STEP 2 — Clear caches

```bash
php artisan view:clear && php artisan cache:clear
```

---

## Do NOT touch

- Any PHP component or service files
- Any other tabs or sections of the blade
- The existing strip cards or product table

---

## Verification

1. Open Sales Analytics → Ledger tab → scroll below the product table
2. The reconciliation block appears with a clean header, waterfall rows,
   and two result cards side by side
3. On mobile (≤600px): the two result cards stack vertically, padding
   reduces, and sub-labels are hidden to save space
4. When `$trueOutstanding = 0`: the "Less: Outstanding Credit" row is
   hidden and both profit cards show the same value in green
5. When credit gap > 5% of gross profit: amber warning banner appears
6. The "Credit gap" / "Fully collected" badge on the right card switches
   between amber and green automatically
