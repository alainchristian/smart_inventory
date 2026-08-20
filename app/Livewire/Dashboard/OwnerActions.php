<?php

namespace App\Livewire\Dashboard;

use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\Customer;
use App\Models\DamagedGood;
use App\Models\DailySession;
use App\Models\HeldSale;
use App\Models\ReturnModel;
use App\Models\Transfer;
use App\Services\SettingsService;
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

        // ── 0. Unclosed sessions from previous days ───────────────────────────
        $unclosedPrevious = DailySession::with('shop')
            ->where('status', 'open')
            ->where('session_date', '<', today()->toDateString())
            ->orderBy('session_date')
            ->get();

        if ($unclosedPrevious->isNotEmpty()) {
            $sections[] = [
                'type'     => 'unclosed_sessions',
                'label'    => 'Sessions Not Closed',
                'icon'     => 'warning',
                'color'    => 'var(--red)',
                'bg'       => 'var(--red-dim)',
                'count'    => $unclosedPrevious->count(),
                'priority' => 0,
                'items'    => $unclosedPrevious->map(fn ($s) => [
                    'id'          => $s->id,
                    'title'       => $s->shop->name ?? '—',
                    'subtitle'    => 'Session for '
                                   . $s->session_date->format('d M Y')
                                   . ' was never closed ('
                                   . $s->session_date->diffInDays(today())
                                   . ' day(s) ago)',
                    'value'       => 'OPEN',
                    'value_color' => 'var(--red)',
                    'age'         => $s->opened_at->diffForHumans(),
                    'link'        => route('owner.finance.daily')
                                   . '?date=' . $s->session_date->toDateString(),
                ])->toArray(),
            ];
        }

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

        // ── 5. Overdue credit customers ───────────────────────────────────────
        $overdueDays = $settings->overdueCreditDays();

        if ($overdueDays > 0) {
            $cutoff = now()->subDays($overdueDays);

            $overdueCustomers = Customer::where('outstanding_balance', '>', 0)
                ->whereNull('deleted_at')
                ->where(function ($q) use ($cutoff) {
                    $q->whereNull('last_repayment_at')
                      ->where('last_credit_at', '<', $cutoff);
                })
                ->orWhere(function ($q) use ($cutoff) {
                    $q->where('outstanding_balance', '>', 0)
                      ->whereNull('deleted_at')
                      ->where('last_repayment_at', '<', $cutoff);
                })
                ->orderByDesc('outstanding_balance')
                ->limit(5)
                ->get();

            if ($overdueCustomers->isNotEmpty()) {
                $sections[] = [
                    'type'  => 'overdue_credit',
                    'label' => 'Overdue Credit — No Recent Repayment',
                    'icon'  => 'clock',
                    'color' => 'var(--red)',
                    'bg'    => 'var(--red-dim)',
                    'count' => $overdueCustomers->count(),
                    'items' => $overdueCustomers->map(fn ($c) => [
                        'id'          => $c->id,
                        'title'       => $c->name,
                        'subtitle'    => $c->phone . ' · '
                                       . ($c->last_repayment_at
                                           ? 'Last paid ' . $c->last_repayment_at->diffForHumans()
                                           : 'Never repaid'),
                        'value'       => number_format($c->outstanding_balance) . ' RWF owed',
                        'value_color' => 'var(--red)',
                        'age'         => $c->last_repayment_at?->diffForHumans() ?? 'Never',
                        'link'        => route('owner.credit.writeoffs'),
                    ])->toArray(),
                ];
            }
        }

        // ── 5b. Pending return approvals (all, not threshold-filtered) ───────
        $allPendingReturns = \App\Models\ReturnModel::with(['shop', 'processedBy'])
            ->whereNull('approved_at')
            ->whereNull('approved_by')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        if ($allPendingReturns->isNotEmpty()) {
            $sections[] = [
                'type'  => 'pending_returns',
                'label' => 'Returns Awaiting Approval',
                'icon'  => 'return',
                'color' => 'var(--amber)',
                'bg'    => 'var(--amber-dim)',
                'count' => \App\Models\ReturnModel::whereNull('approved_at')
                               ->whereNull('approved_by')->count(),
                'items' => $allPendingReturns->map(fn($r) => [
                    'id'          => $r->id,
                    'title'       => $r->return_number,
                    'subtitle'    => ($r->shop->name ?? '—')
                                   . ' · '
                                   . ($r->customer_name ?? 'Walk-in')
                                   . ' · '
                                   . $r->created_at->diffForHumans(),
                    'value'       => $r->is_exchange
                                     ? 'Exchange'
                                     : number_format($r->refund_amount) . ' RWF',
                    'value_color' => $r->is_exchange ? 'var(--accent)' : 'var(--red)',
                    'age'         => $r->created_at->diffForHumans(),
                    'link'        => route('shop.returns.index') . '?statusFilter=pending_approval',
                ])->toArray(),
            ];
        }

        // Note: completed sales with has_price_override are NOT listed as an
        // action here — per the price_override_threshold business setting,
        // a sale can only complete directly (bypassing HeldSale) when it's
        // within policy, so it needs no owner action. Only pending HeldSale
        // rows (below) ever exceeded the threshold and need a real decision.

        // ── 6. Held sales needing price approval ──────────────────────────────
        $pendingHeld = HeldSale::where('needs_price_approval', true)
            ->whereNull('override_approved_at')
            ->whereNull('override_rejected_at')
            ->with(['seller', 'shop'])
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        if ($pendingHeld->isNotEmpty()) {
            $sections[] = [
                'type'  => 'held_approvals',
                'label' => 'Price Approval Needed',
                'icon'  => 'tag',
                'color' => 'var(--amber)',
                'bg'    => 'var(--amber-dim)',
                'count' => $pendingHeld->count(),
                'items' => $pendingHeld->map(fn($h) => [
                    'id'          => $h->id,
                    'title'       => $h->hold_reference,
                    'subtitle'    => ($h->shop?->name ?? '—') . ' · ' . ($h->seller?->name ?? '—')
                                   . ' · ' . $h->item_count . ' item(s)',
                    'value'       => number_format($h->cart_total) . ' RWF',
                    'value_color' => 'var(--amber)',
                    'age'         => $h->created_at->diffForHumans(),
                    'link'        => route('owner.reports.sales') . '?activeTab=audit',
                ])->toArray(),
            ];
        }

        // ── 7. Unresolved critical alerts ─────────────────────────────────────
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

    public function forceCloseSession(int $sessionId): void
    {
        $user = auth()->user();
        if (! $user->isOwner()) return;

        $session = DailySession::with('shop')->find($sessionId);
        if (! $session || $session->status !== 'open') return;

        $session->update([
            'status'    => 'closed',
            'closed_at' => now(),
            'closed_by' => $user->id,
        ]);

        ActivityLog::create([
            'user_id'           => $user->id,
            'user_name'         => $user->name,
            'action'            => 'session_force_closed',
            'entity_type'       => 'DailySession',
            'entity_id'         => $session->id,
            'entity_identifier' => ($session->shop->name ?? "Session #{$session->id}"),
            'details'           => [
                'session_date' => $session->session_date->toDateString(),
                'reason'       => 'Force-closed by owner — stale open session',
            ],
            'ip_address'        => request()->ip(),
        ]);

        $this->loadActions();
        $this->dispatch('notification', ['type' => 'success',
            'message' => 'Session for ' . ($session->shop->name ?? 'shop') . ' has been closed.']);
    }

    // Held-sale price-override approve/reject now lives in the Price Audit
    // module (App\Livewire\Owner\Reports\SalesAnalytics) — this widget only
    // links there (see the 'held_approvals' section above).

    public function render()
    {
        return view('livewire.dashboard.owner-actions');
    }
}
