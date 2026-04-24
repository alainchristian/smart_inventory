<?php

namespace App\Livewire\Dashboard;

use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\Customer;
use App\Models\DamagedGood;
use App\Models\HeldSale;
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

        // ── 6. Pending price override approvals (was 5) ──────────────────────
        $pendingOverrides = DB::table('sales')
            ->join('users as seller', 'sales.sold_by', '=', 'seller.id')
            ->join('shops', 'sales.shop_id', '=', 'shops.id')
            ->whereNull('sales.voided_at')
            ->whereNull('sales.deleted_at')
            ->where('sales.has_price_override', true)
            ->whereNull('sales.price_override_approved_at')
            ->whereRaw("seller.role::text != 'owner'")
            ->orderByDesc('sales.sale_date')
            ->limit(5)
            ->select('sales.id', 'sales.sale_number', 'sales.total', 'sales.sale_date', 'shops.name as shop_name', 'seller.name as seller_name')
            ->get();

        if ($pendingOverrides->isNotEmpty()) {
            $sections[] = [
                'type'  => 'price_overrides',
                'label' => 'Price Override Approvals',
                'icon'  => 'tag',
                'color' => 'var(--amber)',
                'bg'    => 'var(--amber-dim)',
                'count' => $pendingOverrides->count(),
                'items' => $pendingOverrides->map(fn($s) => [
                    'id'          => $s->id,
                    'title'       => $s->sale_number,
                    'subtitle'    => $s->shop_name . ' · sold by ' . $s->seller_name,
                    'value'       => number_format($s->total) . ' RWF',
                    'value_color' => 'var(--amber)',
                    'age'         => \Carbon\Carbon::parse($s->sale_date)->diffForHumans(),
                    'link'        => route('owner.reports.sales') . '?activeTab=audit',
                ])->toArray(),
            ];
        }

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
                    'link'        => null,
                    'cart_data'   => $h->cart_data,
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

    public function approveHeldSale(int $id): void
    {
        $user = auth()->user();
        if (! $user->isOwner()) return;

        $held = HeldSale::find($id);
        if (! $held || $held->override_approved_at) return;

        $held->update([
            'override_approved_at' => now(),
            'override_approved_by' => $user->id,
        ]);

        // Resolve the associated alert
        Alert::where('entity_type', 'HeldSale')
            ->where('entity_id', $held->id)
            ->where('is_resolved', false)
            ->each(fn($a) => $a->markAsResolved($user->id));

        ActivityLog::create([
            'user_id'           => $user->id,
            'user_name'         => $user->name,
            'action'            => 'held_sale_approved',
            'entity_type'       => 'HeldSale',
            'entity_id'         => $held->id,
            'entity_identifier' => $held->hold_reference,
            'details'           => ['seller' => $held->seller->name, 'cart_total' => $held->cart_total],
            'ip_address'        => request()->ip(),
        ]);

        $this->loadActions();
        $this->dispatch('notification', ['type' => 'success',
            'message' => "{$held->hold_reference} approved."]);
    }

    public function rejectHeldSale(int $id, string $reason = ''): void
    {
        $user = auth()->user();
        if (! $user->isOwner()) return;

        $held = HeldSale::find($id);
        if (! $held || $held->override_rejected_at) return;

        $held->update([
            'override_rejected_at' => now(),
            'override_rejected_by' => $user->id,
            'rejected_reason'      => $reason ?: 'Rejected by owner',
        ]);

        // Resolve the associated alert
        Alert::where('entity_type', 'HeldSale')
            ->where('entity_id', $held->id)
            ->where('is_resolved', false)
            ->each(fn($a) => $a->markAsResolved($user->id));

        ActivityLog::create([
            'user_id'           => $user->id,
            'user_name'         => $user->name,
            'action'            => 'held_sale_rejected',
            'entity_type'       => 'HeldSale',
            'entity_id'         => $held->id,
            'entity_identifier' => $held->hold_reference,
            'details'           => ['seller' => $held->seller->name, 'reason' => $reason],
            'ip_address'        => request()->ip(),
        ]);

        $this->loadActions();
        $this->dispatch('notification', ['type' => 'warning',
            'message' => "{$held->hold_reference} rejected."]);
    }

    public function render()
    {
        return view('livewire.dashboard.owner-actions');
    }
}
