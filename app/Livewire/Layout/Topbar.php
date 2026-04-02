<?php

namespace App\Livewire\Layout;

use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\HeldSale;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Topbar extends Component
{
    public $searchQuery = '';
    public $pageTitle;

    public bool  $showApprovalModal = false;
    public array $pendingHeldSales  = [];

    public function mount($pageTitle = 'Dashboard')
    {
        $this->pageTitle = $pageTitle;
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadNotificationsCountProperty(): int
    {
        return $this->totalPendingActions;
    }

    /**
     * Get pending actions for owner
     */
    public function getPendingActionsProperty(): array
    {
        if (!Auth::check() || !Auth::user()->isOwner()) {
            return [];
        }

        return [
            [
                'type' => 'transfer_approval',
                'count' => \App\Models\Transfer::where('status', 'pending')->count(),
                'label' => 'Transfer Approvals',
                'icon' => 'clock',
                'color' => 'amber',
                'route' => 'owner.transfers.index',
            ],
            [
                'type' => 'discrepancy',
                'count' => \App\Models\Transfer::where('has_discrepancy', true)
                    ->where('status', 'received')
                    ->count(),
                'label' => 'Transfer Discrepancies',
                'icon' => 'alert',
                'color' => 'red',
                'route' => 'owner.transfers.index',
            ],
            [
                'type' => 'damaged_goods',
                'count' => \App\Models\DamagedGood::where('disposition', 'pending')->count(),
                'label' => 'Damaged Goods Decisions',
                'icon' => 'box',
                'color' => 'orange',
                'route' => null,
            ],
            [
                'type' => 'critical_alert',
                'count' => Alert::critical()->unresolved()->notDismissed()->count(),
                'label' => 'Critical Alerts',
                'icon' => 'alert-circle',
                'color' => 'red',
                'route' => null,
            ],
            [
                'type'  => 'price_approval',
                'count' => HeldSale::where('needs_price_approval', true)
                    ->whereNull('override_approved_at')
                    ->whereNull('override_rejected_at')
                    ->count(),
                'label' => 'Price Override Approvals',
                'icon'  => 'tag',
                'color' => 'amber',
                'route' => null,
                'modal' => true,
            ],
        ];
    }

    /**
     * Get total pending actions count
     */
    public function getTotalPendingActionsProperty(): int
    {
        return collect($this->pendingActions)->sum('count');
    }

    // ── Approval Modal ────────────────────────────────────────────────────────

    public function openApprovalModal(): void
    {
        $this->pendingHeldSales = HeldSale::where('needs_price_approval', true)
            ->whereNull('override_approved_at')
            ->whereNull('override_rejected_at')
            ->with(['seller', 'shop'])
            ->orderBy('created_at')
            ->get()
            ->map(fn($h) => [
                'id'         => $h->id,
                'reference'  => $h->hold_reference,
                'shop'       => $h->shop?->name ?? '—',
                'seller'     => $h->seller?->name ?? '—',
                'item_count' => $h->item_count,
                'cart_total' => $h->cart_total,
                'age'        => $h->created_at->diffForHumans(),
                'cart_data'  => collect($h->cart_data ?? [])->map(fn($item) => [
                    'product_name'   => $item['product_name'] ?? '—',
                    'quantity'       => $item['quantity'] ?? 1,
                    'is_full_box'    => $item['is_full_box'] ?? false,
                    'price'          => $item['price'] ?? 0,
                    'original_price' => $item['original_price'] ?? $item['price'] ?? 0,
                    'price_modified' => $item['price_modified'] ?? false,
                    'line_total'     => $item['line_total'] ?? 0,
                ])->toArray(),
            ])
            ->toArray();

        $this->showApprovalModal = true;
    }

    public function approveHeldSale(int $id): void
    {
        $user = Auth::user();
        if (! $user || ! $user->isOwner()) return;

        $held = HeldSale::find($id);
        if (! $held || $held->override_approved_at) return;

        $held->update([
            'override_approved_at' => now(),
            'override_approved_by' => $user->id,
        ]);

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
            'details'           => ['cart_total' => $held->cart_total, 'seller' => $held->seller?->name],
            'ip_address'        => request()->ip(),
        ]);

        $this->pendingHeldSales = collect($this->pendingHeldSales)
            ->filter(fn($h) => $h['id'] !== $id)
            ->values()
            ->toArray();

        if (empty($this->pendingHeldSales)) {
            $this->showApprovalModal = false;
            $this->redirect(route('owner.reports.sales') . '?activeTab=audit');
        }
    }

    public function rejectHeldSale(int $id): void
    {
        $user = Auth::user();
        if (! $user || ! $user->isOwner()) return;

        $held = HeldSale::find($id);
        if (! $held || $held->override_rejected_at) return;

        $held->update([
            'override_rejected_at' => now(),
            'override_rejected_by' => $user->id,
            'rejected_reason'      => 'Rejected by owner',
        ]);

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
            'details'           => ['seller' => $held->seller?->name],
            'ip_address'        => request()->ip(),
        ]);

        $this->pendingHeldSales = collect($this->pendingHeldSales)
            ->filter(fn($h) => $h['id'] !== $id)
            ->values()
            ->toArray();

        if (empty($this->pendingHeldSales)) {
            $this->showApprovalModal = false;
        }
    }

    /**
     * Handle search
     */
    public function search()
    {
        // Implement global search logic
        $this->dispatch('global-search', query: $this->searchQuery);
    }

    public function render()
    {
        return view('livewire.layout.topbar', [
            'currentMonth' => now()->format('M Y'),
            'currentDate' => now()->format('l, F j, Y'),
        ]);
    }
}
