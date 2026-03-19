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
