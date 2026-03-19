# Owner Dashboard — Remove Overlaps + Owner Actions Panel
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read DASHBOARD_UPDATE.md and follow every step in order."

---

## Read these files first

```bash
cat resources/views/owner/dashboard.blade.php
cat app/Livewire/Dashboard/RecentMovements.php
cat app/Models/ReturnModel.php | head -40
cat app/Models/DamagedGood.php | head -40
cat app/Models/Transfer.php | grep -n "has_discrepancy\|discrepancy"
grep -n "outstanding_balance\|max_credit" app/Models/Customer.php
```

---

## What this file does

1. Removes the static hardcoded stats section at the top of the dashboard
   (duplicates BusinessKpiRow and OpsKpiRow)
2. Removes the Stock Distribution donut from Row 4
   (duplicates OpsKpiRow box split numerically)
3. Replaces Recent Movements with a new "Requires Your Action" panel
4. Creates the new OwnerActions Livewire component

---

## STEP 1 — Remove the static stats section from the dashboard blade

**File:** `resources/views/owner/dashboard.blade.php`

Find the hardcoded static stats block near the top. It contains cards with:
- Retail value / cost value / potential profit
- Active Boxes count

It is inside a `@if` block that checks `isset($stats)` or similar, and renders
`.bkpi` cards directly in the blade (not via a Livewire tag).

**Delete the entire block** from its opening `@if` to its closing `@endif`
(or from the opening `<div>` if there is no `@if`).

The first visible content after the page header should now be:
```blade
<div class="section-label">Business Overview</div>
<livewire:dashboard.business-kpi-row />
```

---

## STEP 2 — Remove Stock Distribution from Row 4

**File:** `resources/views/owner/dashboard.blade.php`

Find Row 4:
```blade
<div class="row-ops-activity">
    <livewire:dashboard.transfer-status />
    <livewire:dashboard.activity-feed />
    <livewire:dashboard.stock-distribution />
</div>
```

Remove only the stock distribution line:
```blade
<div class="row-ops-activity">
    <livewire:dashboard.transfer-status />
    <livewire:dashboard.activity-feed />
</div>
```

---

## STEP 3 — Replace Recent Movements with Owner Actions in Row 5

**File:** `resources/views/owner/dashboard.blade.php`

Find Row 5:
```blade
<livewire:dashboard.recent-movements />
```

Replace with:
```blade
<livewire:dashboard.owner-actions />
```

The full Row 5 should now read:
```blade
<div class="section-label">Requires Your Attention</div>
<div class="row-trace-alerts">
    <livewire:dashboard.owner-actions />
    <div class="right-stack-panel">
        <livewire:dashboard.alerts-panel />
        <livewire:dashboard.system-status />
    </div>
</div>
```

---

## STEP 4 — Create the OwnerActions Livewire component

**File:** `app/Livewire/Dashboard/OwnerActions.php`

```php
<?php

namespace App\Livewire\Dashboard;

use App\Models\Alert;
use App\Models\Customer;
use App\Models\DamagedGood;
use App\Models\ReturnModel;
use App\Models\Transfer;
use App\Services\SettingsService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class OwnerActions extends Component
{
    public array $sections = [];
    public int   $totalActions = 0;

    public function mount(): void
    {
        $this->loadActions();
    }

    public function loadActions(): void
    {
        $settings = app(SettingsService::class);
        $sections = [];

        // ── 1. Return approvals pending ───────────────────────────────────────
        $returnThreshold = $settings->returnApprovalThreshold();
        $pendingReturns  = ReturnModel::whereNull('approved_at')
            ->whereNull('deleted_at')
            ->when($returnThreshold > 0, fn($q) =>
                $q->where('refund_amount', '>=', $returnThreshold)
            )
            ->with(['processedBy', 'shop'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        if ($pendingReturns->isNotEmpty()) {
            $sections[] = [
                'type'  => 'returns',
                'label' => 'Return Approvals',
                'icon'  => 'rotate',
                'color' => 'var(--violet)',
                'bg'    => 'var(--violet-dim)',
                'count' => $pendingReturns->count(),
                'items' => $pendingReturns->map(fn($r) => [
                    'id'          => $r->id,
                    'title'       => $r->return_number,
                    'subtitle'    => ($r->shop?->name ?? 'Unknown shop')
                                   . ' · processed by ' . ($r->processedBy?->name ?? '—'),
                    'value'       => number_format($r->refund_amount) . ' RWF',
                    'value_color' => 'var(--violet)',
                    'age'         => $r->created_at->diffForHumans(),
                    'link'        => route('owner.returns.index'),
                ])->toArray(),
            ];
        }

        // ── 2. Transfer discrepancies ─────────────────────────────────────────
        $discrepancies = Transfer::where('has_discrepancy', true)
            ->whereNull('deleted_at')
            ->with(['fromWarehouse', 'toShop'])
            ->orderByDesc('received_at')
            ->limit(5)
            ->get();

        if ($discrepancies->isNotEmpty()) {
            $sections[] = [
                'type'  => 'discrepancies',
                'label' => 'Transfer Discrepancies',
                'icon'  => 'alert-triangle',
                'color' => 'var(--amber)',
                'bg'    => 'var(--amber-dim)',
                'count' => $discrepancies->count(),
                'items' => $discrepancies->map(fn($t) => [
                    'id'          => $t->id,
                    'title'       => $t->transfer_number ?? "Transfer #{$t->id}",
                    'subtitle'    => ($t->fromWarehouse?->name ?? '—')
                                   . ' → ' . ($t->toShop?->name ?? '—'),
                    'value'       => 'Received ' . $t->received_at?->diffForHumans(),
                    'value_color' => 'var(--amber)',
                    'age'         => $t->received_at?->diffForHumans() ?? '—',
                    'link'        => route('owner.transfers.show', $t->id),
                ])->toArray(),
            ];
        }

        // ── 3. Damaged goods pending disposition ──────────────────────────────
        $pendingDamaged = DamagedGood::where('disposition', 'pending')
            ->whereNull('deleted_at')
            ->where('recorded_at', '<=', now()->subDays(3))
            ->with('product')
            ->orderBy('recorded_at')
            ->limit(5)
            ->get();

        if ($pendingDamaged->isNotEmpty()) {
            $sections[] = [
                'type'  => 'damaged',
                'label' => 'Damaged Goods — No Decision',
                'icon'  => 'package-x',
                'color' => 'var(--red)',
                'bg'    => 'var(--red-dim)',
                'count' => $pendingDamaged->count(),
                'items' => $pendingDamaged->map(fn($d) => [
                    'id'          => $d->id,
                    'title'       => $d->product?->name ?? 'Unknown product',
                    'subtitle'    => $d->quantity_damaged . ' units · ' . $d->damage_reference,
                    'value'       => number_format($d->estimated_loss) . ' RWF loss',
                    'value_color' => 'var(--red)',
                    'age'         => $d->recorded_at->diffForHumans(),
                    'link'        => route('owner.damaged-goods.index'),
                ])->toArray(),
            ];
        }

        // ── 4. Customers over credit limit ────────────────────────────────────
        $maxCredit = $settings->maxCreditPerCustomer();
        if ($maxCredit > 0) {
            $overLimit = Customer::where('outstanding_balance', '>', $maxCredit * 0.9)
                ->where('outstanding_balance', '>', 0)
                ->whereNull('deleted_at')
                ->orderByDesc('outstanding_balance')
                ->limit(5)
                ->get();

            if ($overLimit->isNotEmpty()) {
                $sections[] = [
                    'type'  => 'credit',
                    'label' => 'Credit Limit Warnings',
                    'icon'  => 'credit-card',
                    'color' => 'var(--amber)',
                    'bg'    => 'var(--amber-dim)',
                    'count' => $overLimit->count(),
                    'items' => $overLimit->map(fn($c) => [
                        'id'          => $c->id,
                        'title'       => $c->name,
                        'subtitle'    => $c->phone
                                        . ' · ' . round(($c->outstanding_balance / $maxCredit) * 100)
                                        . '% of limit used',
                        'value'       => number_format($c->outstanding_balance) . ' RWF',
                        'value_color' => 'var(--amber)',
                        'age'         => $c->last_credit_at?->diffForHumans() ?? '—',
                        'link'        => route('owner.reports.customer-credit'),
                    ])->toArray(),
                ];
            }
        }

        // ── 5. Unresolved critical alerts ─────────────────────────────────────
        $criticalAlerts = Alert::where('severity', 'critical')
            ->where('is_resolved', false)
            ->where('is_dismissed', false)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        if ($criticalAlerts->isNotEmpty()) {
            $sections[] = [
                'type'  => 'alerts',
                'label' => 'Critical Alerts',
                'icon'  => 'bell',
                'color' => 'var(--red)',
                'bg'    => 'var(--red-dim)',
                'count' => $criticalAlerts->count(),
                'items' => $criticalAlerts->map(fn($a) => [
                    'id'          => $a->id,
                    'title'       => $a->title,
                    'subtitle'    => $a->message,
                    'value'       => $a->created_at->diffForHumans(),
                    'value_color' => 'var(--text-dim)',
                    'age'         => $a->created_at->diffForHumans(),
                    'link'        => route('owner.alerts.index'),
                ])->toArray(),
            ];
        }

        $this->sections     = $sections;
        $this->totalActions = collect($sections)->sum('count');
    }

    public function render()
    {
        return view('livewire.dashboard.owner-actions');
    }
}
```

---

## STEP 5 — Create the OwnerActions blade view

**File:** `resources/views/livewire/dashboard/owner-actions.blade.php`

```blade
<div style="background:var(--surface);border:1px solid var(--border);
            border-radius:var(--r);overflow:hidden;height:100%">

    {{-- Header --}}
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);
                display:flex;align-items:center;justify-content:space-between">
        <div>
            <div style="font-size:15px;font-weight:700;color:var(--text);
                        display:flex;align-items:center;gap:8px">
                Requires Your Attention
                @if($totalActions > 0)
                <span style="font-size:11px;font-weight:700;padding:2px 8px;
                             border-radius:20px;background:var(--red-dim);
                             color:var(--red);font-family:var(--mono)">
                    {{ $totalActions }}
                </span>
                @endif
            </div>
            <div style="font-size:12px;color:var(--text-dim);margin-top:2px">
                Items blocked waiting for owner action
            </div>
        </div>
        <button wire:click="loadActions"
                wire:loading.class="opacity-50"
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);
                       background:var(--surface2);display:flex;align-items:center;
                       justify-content:center;cursor:pointer;color:var(--text-dim);
                       transition:all var(--tr)"
                title="Refresh">
            <svg width="13" height="13" fill="none" stroke="currentColor"
                 stroke-width="2" viewBox="0 0 24 24">
                <polyline points="1 4 1 10 7 10"/>
                <path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
            </svg>
        </button>
    </div>

    {{-- Content --}}
    <div style="overflow-y:auto;max-height:520px">

        @if(empty($sections))
        {{-- All clear state --}}
        <div style="padding:48px 20px;text-align:center">
            <div style="font-size:36px;margin-bottom:12px">✅</div>
            <div style="font-size:15px;font-weight:700;color:var(--text-sub);
                        margin-bottom:6px">All clear</div>
            <div style="font-size:13px;color:var(--text-dim);line-height:1.5">
                No returns to approve, no discrepancies,
                no pending decisions. The business is running smoothly.
            </div>
        </div>

        @else
        @foreach($sections as $section)

        {{-- Section header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:10px 18px 6px;background:var(--surface2);
                    border-bottom:1px solid var(--border);
                    border-top:{{ !$loop->first ? '1px solid var(--border)' : 'none' }}">
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:24px;height:24px;border-radius:6px;
                            background:{{ $section['bg'] }};
                            display:flex;align-items:center;justify-content:center">
                    @if($section['icon'] === 'rotate')
                    <svg width="12" height="12" fill="none" stroke="{{ $section['color'] }}"
                         stroke-width="2.5" viewBox="0 0 24 24">
                        <polyline points="1 4 1 10 7 10"/>
                        <path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
                    </svg>
                    @elseif($section['icon'] === 'alert-triangle')
                    <svg width="12" height="12" fill="none" stroke="{{ $section['color'] }}"
                         stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    @elseif($section['icon'] === 'package-x')
                    <svg width="12" height="12" fill="none" stroke="{{ $section['color'] }}"
                         stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                        <line x1="9.5" y1="9.5" x2="14.5" y2="14.5"/>
                        <line x1="14.5" y1="9.5" x2="9.5" y2="14.5"/>
                    </svg>
                    @elseif($section['icon'] === 'credit-card')
                    <svg width="12" height="12" fill="none" stroke="{{ $section['color'] }}"
                         stroke-width="2.5" viewBox="0 0 24 24">
                        <rect x="1" y="4" width="22" height="16" rx="2"/>
                        <line x1="1" y1="10" x2="23" y2="10"/>
                    </svg>
                    @else
                    <svg width="12" height="12" fill="none" stroke="{{ $section['color'] }}"
                         stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 01-3.46 0"/>
                    </svg>
                    @endif
                </div>
                <span style="font-size:12px;font-weight:700;color:{{ $section['color'] }};
                             text-transform:uppercase;letter-spacing:.4px">
                    {{ $section['label'] }}
                </span>
            </div>
            <span style="font-size:11px;font-weight:700;font-family:var(--mono);
                         color:{{ $section['color'] }}">
                {{ $section['count'] }}
            </span>
        </div>

        {{-- Items --}}
        @foreach($section['items'] as $item)
        <a href="{{ $item['link'] }}"
           style="display:flex;align-items:flex-start;justify-content:space-between;
                  gap:12px;padding:11px 18px;border-bottom:1px solid var(--border);
                  text-decoration:none;transition:background var(--tr)"
           onmouseover="this.style.background='var(--surface2)'"
           onmouseout="this.style.background='transparent'">
            <div style="min-width:0;flex:1">
                <div style="font-size:13px;font-weight:600;color:var(--text);
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    {{ $item['title'] }}
                </div>
                <div style="font-size:11px;color:var(--text-dim);margin-top:2px;
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    {{ $item['subtitle'] }}
                </div>
            </div>
            <div style="text-align:right;flex-shrink:0">
                <div style="font-size:12px;font-weight:700;font-family:var(--mono);
                            color:{{ $item['value_color'] }};white-space:nowrap">
                    {{ $item['value'] }}
                </div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:2px">
                    {{ $item['age'] }}
                </div>
            </div>
        </a>
        @endforeach

        {{-- View all link --}}
        @if($section['count'] >= 5)
        <a href="{{ $section['items'][0]['link'] }}"
           style="display:block;padding:8px 18px;font-size:12px;font-weight:600;
                  color:{{ $section['color'] }};text-decoration:none;
                  border-bottom:1px solid var(--border);
                  background:{{ $section['bg'] }};opacity:.8">
            View all {{ $section['label'] }} →
        </a>
        @endif

        @endforeach
        @endif

    </div>
</div>
```

---

## STEP 6 — Clear caches

```bash
php artisan livewire:discover
php artisan view:clear && php artisan cache:clear
```

---

## Do NOT touch

- `app/Livewire/Dashboard/RecentMovements.php` — leave the file, just remove
  the tag from the dashboard blade. The component may be used elsewhere.
- `app/Livewire/Dashboard/StockDistribution.php` — same: file stays, tag removed
- Any other dashboard components
- BusinessKpiRow or OpsKpiRow

---

## Verification

1. Open `/owner/dashboard` — the static bkpi cards at the very top are gone
2. First visible section is "Business Overview" with the Livewire KPI row
3. Row 4 now shows Transfer Status and Activity Feed only (no donut chart)
4. Row 5 left panel shows "Requires Your Attention" instead of box movements
5. If no actions pending: green checkmark with "All clear" message
6. If a return awaits approval: it appears under "Return Approvals" as a
   clickable row linking to the returns page
7. If a transfer has a discrepancy: it appears under "Transfer Discrepancies"
   linking to the specific transfer
8. If damaged goods have no decision for 3+ days: they appear under
   "Damaged Goods — No Decision"
9. The total count badge in the panel header sums all pending items
10. Refresh button reloads actions without a full page reload
