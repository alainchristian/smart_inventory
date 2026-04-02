<?php

namespace App\Livewire\Owner;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogs extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $filterUser   = '';
    public string $filterAction = '';
    public string $filterEntity = '';
    public string $dateFrom     = '';
    public string $dateTo       = '';

    // Expand row to show full details
    public ?int $expandedId = null;

    protected $queryString = [
        'search'       => ['except' => ''],
        'filterUser'   => ['except' => ''],
        'filterAction' => ['except' => ''],
        'filterEntity' => ['except' => ''],
        'dateFrom'     => ['except' => ''],
        'dateTo'       => ['except' => ''],
    ];

    public function updatedSearch(): void    { $this->resetPage(); }
    public function updatedFilterUser(): void   { $this->resetPage(); }
    public function updatedFilterAction(): void { $this->resetPage(); }
    public function updatedFilterEntity(): void { $this->resetPage(); }
    public function updatedDateFrom(): void { $this->resetPage(); }
    public function updatedDateTo(): void   { $this->resetPage(); }

    public function toggleExpand(int $id): void
    {
        $this->expandedId = ($this->expandedId === $id) ? null : $id;
    }

    public function clearFilters(): void
    {
        $this->search       = '';
        $this->filterUser   = '';
        $this->filterAction = '';
        $this->filterEntity = '';
        $this->dateFrom     = '';
        $this->dateTo       = '';
        $this->resetPage();
    }

    // ── Action label / icon / color ──────────────────────────────────────────

    public function parseAction(ActivityLog $log): array
    {
        $action = strtolower($log->action ?? '');
        $entity = strtolower($log->entity_type ?? '');

        if (str_contains($action, 'transfer_requested') || ($action === 'created' && $entity === 'transfer')) {
            return ['label' => 'Transfer requested', 'icon' => 'transfer', 'color' => 'blue'];
        }
        if (str_contains($action, 'transfer_approved') || ($action === 'approved' && $entity === 'transfer')) {
            return ['label' => 'Transfer approved', 'icon' => 'check', 'color' => 'green'];
        }
        if (str_contains($action, 'transfer_rejected') || ($action === 'rejected' && $entity === 'transfer')) {
            return ['label' => 'Transfer rejected', 'icon' => 'x', 'color' => 'red'];
        }
        if (str_contains($action, 'transfer_packed') || str_contains($action, 'scan_out')) {
            return ['label' => 'Boxes scanned out', 'icon' => 'transfer', 'color' => 'blue'];
        }
        if (str_contains($action, 'transfer_received') || str_contains($action, 'scan_in')) {
            return ['label' => 'Transfer received', 'icon' => 'check', 'color' => 'green'];
        }
        if (str_contains($action, 'discrepancy')) {
            return ['label' => 'Discrepancy flagged', 'icon' => 'warning', 'color' => 'amber'];
        }
        if (str_contains($action, 'transfer')) {
            return ['label' => 'Transfer updated', 'icon' => 'transfer', 'color' => 'blue'];
        }
        if (str_contains($action, 'held_sale_approved')) {
            return ['label' => 'Price override approved', 'icon' => 'check', 'color' => 'green'];
        }
        if (str_contains($action, 'held_sale_rejected')) {
            return ['label' => 'Price override rejected', 'icon' => 'x', 'color' => 'red'];
        }
        if (str_contains($action, 'sale_held')) {
            return ['label' => 'Sale held for approval', 'icon' => 'warning', 'color' => 'amber'];
        }
        if (str_contains($action, 'sale_voided') || str_contains($action, 'voided')) {
            return ['label' => 'Sale voided', 'icon' => 'x', 'color' => 'red'];
        }
        if (str_contains($action, 'price_modified') || str_contains($action, 'price_override')) {
            return ['label' => 'Price modified', 'icon' => 'warning', 'color' => 'amber'];
        }
        if (str_contains($action, 'sale') || ($action === 'created' && $entity === 'sale')) {
            return ['label' => 'Sale completed', 'icon' => 'sale', 'color' => 'green'];
        }
        if (str_contains($action, 'return') && str_contains($action, 'approved')) {
            return ['label' => 'Return approved', 'icon' => 'check', 'color' => 'green'];
        }
        if (str_contains($action, 'return')) {
            return ['label' => 'Return processed', 'icon' => 'return', 'color' => 'amber'];
        }
        if (str_contains($action, 'box_received') || ($action === 'created' && $entity === 'box')) {
            return ['label' => 'Box received', 'icon' => 'box', 'color' => 'blue'];
        }
        if (str_contains($action, 'box_damaged') || str_contains($action, 'damaged')) {
            return ['label' => 'Box damaged', 'icon' => 'warning', 'color' => 'red'];
        }
        if (str_contains($action, 'box_adjustment') || str_contains($action, 'adjustment')) {
            return ['label' => 'Inventory adjusted', 'icon' => 'box', 'color' => 'amber'];
        }
        if (str_contains($action, 'box')) {
            return ['label' => 'Box updated', 'icon' => 'box', 'color' => 'blue'];
        }
        if (str_contains($action, 'disposition') || str_contains($entity, 'damaged')) {
            return ['label' => 'Damage disposition', 'icon' => 'warning', 'color' => 'red'];
        }
        if (str_contains($action, 'repayment') || str_contains($action, 'credit_repayment')) {
            return ['label' => 'Credit repayment', 'icon' => 'sale', 'color' => 'green'];
        }
        if (str_contains($action, 'product') || $entity === 'product') {
            return ['label' => 'Product updated', 'icon' => 'product', 'color' => 'blue'];
        }
        if (str_contains($action, 'user') && str_contains($action, 'created')) {
            return ['label' => 'User created', 'icon' => 'user', 'color' => 'blue'];
        }
        if (str_contains($action, 'user')) {
            return ['label' => 'User updated', 'icon' => 'user', 'color' => 'default'];
        }
        if (str_contains($action, 'login') || str_contains($action, 'logout')) {
            return ['label' => ucfirst($action), 'icon' => 'user', 'color' => 'default'];
        }
        if ($action === 'created') {
            return ['label' => ucfirst($entity ?: 'Record') . ' created', 'icon' => 'default', 'color' => 'blue'];
        }
        if ($action === 'updated') {
            return ['label' => ucfirst($entity ?: 'Record') . ' updated', 'icon' => 'default', 'color' => 'default'];
        }
        if ($action === 'deleted') {
            return ['label' => ucfirst($entity ?: 'Record') . ' deleted', 'icon' => 'x', 'color' => 'red'];
        }

        return [
            'label' => ucfirst(str_replace(['_', '-'], ' ', $action)),
            'icon'  => 'default',
            'color' => 'default',
        ];
    }

    public function buildContext(ActivityLog $log): string
    {
        $parts   = [];
        $details = $log->details ?? [];

        if ($log->entity_identifier) {
            $parts[] = $log->entity_identifier;
        }
        if (!empty($details['box_count'])) {
            $parts[] = $details['box_count'] . ' box' . ($details['box_count'] > 1 ? 'es' : '');
        }
        if (!empty($details['shop_name'])) {
            $parts[] = $details['shop_name'];
        } elseif (!empty($details['warehouse_name'])) {
            $parts[] = $details['warehouse_name'];
        }
        if (!empty($details['total'])) {
            $parts[] = number_format($details['total']) . ' RWF';
        }
        if (!empty($details['cart_total'])) {
            $parts[] = number_format($details['cart_total']) . ' RWF';
        }
        if (!empty($details['diff_pct'])) {
            $parts[] = ($details['diff_pct'] > 0 ? '+' : '') . $details['diff_pct'] . '%';
        }
        if (!empty($details['quantity'])) {
            $parts[] = $details['quantity'] . ' items';
        }
        if (!empty($details['refund_amount'])) {
            $parts[] = 'Refund: ' . number_format($details['refund_amount']) . ' RWF';
        }
        if (!empty($details['seller'])) {
            $parts[] = 'by ' . $details['seller'];
        }

        return implode(' · ', $parts);
    }

    // ── Distinct filter options ──────────────────────────────────────────────

    public function getDistinctActionsProperty(): array
    {
        return ActivityLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->map(fn($a) => [
                'value' => $a,
                'label' => ucfirst(str_replace(['_', '-'], ' ', $a)),
            ])
            ->toArray();
    }

    public function getDistinctEntitiesProperty(): array
    {
        return ActivityLog::select('entity_type')
            ->whereNotNull('entity_type')
            ->distinct()
            ->orderBy('entity_type')
            ->pluck('entity_type')
            ->toArray();
    }

    public function getDistinctUsersProperty(): array
    {
        return ActivityLog::select('user_id', 'user_name')
            ->whereNotNull('user_id')
            ->distinct()
            ->orderBy('user_name')
            ->get()
            ->unique('user_id')
            ->map(fn($l) => ['id' => $l->user_id, 'name' => $l->user_name])
            ->values()
            ->toArray();
    }

    public function getHasActiveFiltersProperty(): bool
    {
        return $this->search !== ''
            || $this->filterUser !== ''
            || $this->filterAction !== ''
            || $this->filterEntity !== ''
            || $this->dateFrom !== ''
            || $this->dateTo !== '';
    }

    public function render()
    {
        $query = ActivityLog::query()->orderByDesc('created_at');

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('entity_identifier', 'ilike', '%' . $this->search . '%')
                  ->orWhere('user_name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('action', 'ilike', '%' . $this->search . '%');
            });
        }
        if ($this->filterUser !== '') {
            $query->where('user_id', $this->filterUser);
        }
        if ($this->filterAction !== '') {
            $query->where('action', $this->filterAction);
        }
        if ($this->filterEntity !== '') {
            $query->where('entity_type', $this->filterEntity);
        }
        if ($this->dateFrom !== '') {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo !== '') {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $logs = $query->paginate(50);

        return view('livewire.owner.activity-logs', ['logs' => $logs]);
    }
}
