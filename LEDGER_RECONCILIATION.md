# Sales Analytics Ledger — Revenue Reconciliation Block
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read LEDGER_RECONCILIATION.md and follow every step in order."

---

## Read this file first

```bash
cat resources/views/livewire/owner/reports/sales-analytics.blade.php
```

Focus on the `TAB: SALES LEDGER` section. Note:
- `$gp['revenue']`       = gross revenue for the period
- `$gp['total_cost']`    = cost of goods sold
- `$gp['gross_profit']`  = gross profit on paper
- `$gp['margin_pct']`    = gross margin %
- `$trueOutstanding`     = credit still uncollected (from customers table)
- `$totalCreditRepaid`   = total repaid all time
- `$repaymentRate`       = repayment rate %

All variables are already computed in the existing `@php` block.
No new queries are needed.

---

## What we are adding

A compact reconciliation block immediately after the closing `</div>` of
the product ledger table (after `@endif` of the tfoot block), and before
the `@elseif($activeTab === 'audit')` line.

It shows the owner the difference between profit on paper vs profit in hand.

---

## STEP 1 — Insert the reconciliation block

**File:** `resources/views/livewire/owner/reports/sales-analytics.blade.php`

Find the closing of the ledger table section. It looks like:

```blade
        </div>
    </div>

{{-- ══════════════════ TAB: PRICE AUDIT ══════════════════ --}}
@elseif($activeTab === 'audit')
```

Insert this block between the closing `</div>` of the ledger table
and the `@elseif($activeTab === 'audit')` line:

```blade
{{-- ── Revenue Reconciliation ──────────────────────────────────────── --}}
@php
    $collectedRevenue   = $gp['revenue'] - $trueOutstanding;
    $costOfGoods        = $gp['total_cost'];
    $profitOnPaper      = $gp['gross_profit'];
    $profitInHand       = $collectedRevenue - $costOfGoods;
    $profitGap          = $profitOnPaper - $profitInHand;
    $collectedMarginPct = $collectedRevenue > 0
        ? round(($profitInHand / $collectedRevenue) * 100, 1)
        : 0;
    $creditRiskPct      = $gp['revenue'] > 0
        ? round(($trueOutstanding / $gp['revenue']) * 100, 1)
        : 0;
@endphp

<div style="margin-top:20px;background:var(--surface);border:1px solid var(--border);
            border-radius:var(--r);overflow:hidden">

    {{-- Header --}}
    <div style="padding:14px 20px;border-bottom:1px solid var(--border);
                display:flex;align-items:center;justify-content:space-between">
        <div>
            <div class="sa-section-title"
                 style="font-size:13px;font-weight:700;color:var(--text)">
                Revenue Reconciliation
            </div>
            <div class="sa-section-subtitle"
                 style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:2px">
                Profit on paper vs profit in hand · {{ $this->activeDateRangeLabel }}
            </div>
        </div>
        @if($creditRiskPct > 0)
        <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;
                     background:rgba(217,119,6,.1);color:#d97706;font-family:var(--mono)">
            {{ $creditRiskPct }}% on credit
        </span>
        @endif
    </div>

    {{-- Reconciliation rows --}}
    <div style="padding:6px 0">

        {{-- Gross Revenue --}}
        <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:10px 20px">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:3px;height:20px;border-radius:2px;
                            background:var(--accent)"></div>
                <span style="font-size:13px;color:var(--text)">Gross Revenue</span>
            </div>
            <span style="font-size:14px;font-weight:700;font-family:var(--mono);
                         color:var(--accent)">
                {{ number_format($gp['revenue']) }}
            </span>
        </div>

        {{-- Less outstanding credit --}}
        @if($trueOutstanding > 0)
        <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:10px 20px;background:rgba(217,119,6,.04)">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:3px;height:20px;border-radius:2px;
                            background:#d97706"></div>
                <div>
                    <span style="font-size:13px;color:var(--text-sub)">
                        Less: Outstanding Credit
                    </span>
                    <span style="font-size:11px;color:var(--text-dim);margin-left:8px">
                        {{ $repaymentRate }}% repayment rate
                        · {{ number_format($totalCreditRepaid) }} RWF repaid
                    </span>
                </div>
            </div>
            <span style="font-size:14px;font-weight:700;font-family:var(--mono);
                         color:#d97706">
                ({{ number_format($trueOutstanding) }})
            </span>
        </div>
        @endif

        {{-- Net Collected Revenue --}}
        <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:10px 20px;border-top:1px solid var(--border);
                    border-bottom:1px solid var(--border)">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:3px;height:20px;border-radius:2px;
                            background:var(--text-sub)"></div>
                <span style="font-size:13px;font-weight:600;color:var(--text)">
                    Net Collected Revenue
                </span>
            </div>
            <span style="font-size:14px;font-weight:700;font-family:var(--mono);
                         color:var(--text)">
                {{ number_format($collectedRevenue) }}
            </span>
        </div>

        {{-- Less cost of goods --}}
        <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:10px 20px">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:3px;height:20px;border-radius:2px;
                            background:var(--text-dim)"></div>
                <span style="font-size:13px;color:var(--text-sub)">
                    Less: Cost of Goods
                </span>
            </div>
            <span style="font-size:14px;font-weight:700;font-family:var(--mono);
                         color:var(--text-sub)">
                ({{ number_format($costOfGoods) }})
            </span>
        </div>

        {{-- Divider --}}
        <div style="margin:0 20px;border-top:2px solid var(--border)"></div>

        {{-- Two-column result: profit on paper vs profit in hand --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;padding:4px 0">

            {{-- Gross Profit on Paper --}}
            <div style="padding:14px 20px;border-right:1px solid var(--border)">
                <div style="font-size:10px;font-weight:700;letter-spacing:.5px;
                            text-transform:uppercase;color:var(--text-dim);margin-bottom:6px">
                    Gross Profit on Paper
                </div>
                <div style="font-size:22px;font-weight:800;font-family:var(--mono);
                            color:var(--green)">
                    {{ number_format($profitOnPaper) }}
                </div>
                <div style="font-size:11px;color:var(--text-dim);margin-top:3px">
                    {{ $gp['margin_pct'] }}% margin · includes uncollected
                </div>
            </div>

            {{-- Gross Profit in Hand --}}
            <div style="padding:14px 20px;
                        background:{{ $profitInHand >= $profitOnPaper * 0.9
                            ? 'rgba(22,163,74,.04)'
                            : 'rgba(217,119,6,.04)' }}">
                <div style="font-size:10px;font-weight:700;letter-spacing:.5px;
                            text-transform:uppercase;
                            color:{{ $profitInHand >= $profitOnPaper * 0.9
                                ? 'var(--green)'
                                : '#d97706' }};
                            margin-bottom:6px">
                    Gross Profit in Hand
                </div>
                <div style="font-size:22px;font-weight:800;font-family:var(--mono);
                            color:{{ $profitInHand >= $profitOnPaper * 0.9
                                ? 'var(--green)'
                                : '#d97706' }}">
                    {{ number_format($profitInHand) }}
                </div>
                <div style="font-size:11px;color:var(--text-dim);margin-top:3px">
                    {{ $collectedMarginPct }}% margin
                    @if($profitGap > 0)
                        · {{ number_format($profitGap) }} RWF gap
                    @endif
                </div>
            </div>

        </div>

        {{-- Warning if gap is significant (>5% of gross profit) --}}
        @if($profitGap > 0 && $gp['gross_profit'] > 0
            && ($profitGap / $gp['gross_profit']) > 0.05)
        <div style="margin:0 16px 14px;padding:10px 14px;
                    background:rgba(217,119,6,.06);
                    border:1px solid rgba(217,119,6,.3);
                    border-radius:8px;
                    display:flex;align-items:flex-start;gap:8px">
            <span style="font-size:14px;flex-shrink:0;margin-top:1px">⚠️</span>
            <div style="font-size:11px;color:var(--text-sub);line-height:1.6">
                <strong style="color:#d97706">
                    {{ number_format($profitGap) }} RWF
                    ({{ round(($profitGap / $gp['gross_profit']) * 100, 1) }}%
                    of gross profit)
                </strong>
                is profit recorded but not yet collected.
                This is tied to outstanding credit balances.
                Recovering credit will improve the profit-in-hand figure.
            </div>
        </div>
        @endif

    </div>
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
- Any other tabs
- The existing strip cards or product table
- Any migrations

---

## Verification

1. Open Sales Analytics → Ledger tab
2. Reconciliation block appears below the product table
3. Numbers check: Gross Profit on Paper − Outstanding Credit = Gross Profit in Hand
4. If outstanding credit > 5% of gross profit, the amber warning appears
5. If outstanding credit = 0 (all credit repaid), the "Less: Outstanding Credit"
   row is hidden and both profit figures are equal
6. The "profit in hand" column turns amber when the gap is meaningful,
   green when credit exposure is small (< 10% of gross profit)
