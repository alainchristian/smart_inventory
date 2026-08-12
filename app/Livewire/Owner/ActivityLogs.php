<?php

namespace App\Livewire\Owner;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogs extends Component
{
    use WithPagination;

    public string $search         = '';
    public string $filterUser     = '';
    public string $filterAction   = '';
    public string $filterEntity   = '';
    public string $filterModule   = '';
    public string $filterSeverity = '';
    public string $filterStatus   = '';
    public string $filterActionType = '';
    public ?string $filterTraceId = null;
    public string $dateFrom       = '';
    public string $dateTo         = '';
    public string $rangePreset    = '';

    public string $sortBy  = 'created_at';
    public string $sortDir = 'desc';

    public int $perPage = 25;

    // Slide-over drawer
    public ?int $selectedLogId = null;

    protected $queryString = [
        'search'           => ['except' => ''],
        'filterUser'       => ['except' => ''],
        'filterAction'     => ['except' => ''],
        'filterEntity'     => ['except' => ''],
        'filterModule'     => ['except' => ''],
        'filterSeverity'   => ['except' => ''],
        'filterStatus'     => ['except' => ''],
        'filterActionType' => ['except' => ''],
        'dateFrom'         => ['except' => ''],
        'dateTo'           => ['except' => ''],
        'sortBy'           => ['except' => 'created_at'],
        'sortDir'          => ['except' => 'desc'],
    ];

    public function updatedSearch(): void         { $this->resetPage(); }
    public function updatedFilterUser(): void     { $this->resetPage(); }
    public function updatedFilterAction(): void   { $this->resetPage(); }
    public function updatedFilterEntity(): void   { $this->resetPage(); }
    public function updatedFilterModule(): void   { $this->resetPage(); }
    public function updatedFilterSeverity(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void   { $this->resetPage(); }
    public function updatedFilterActionType(): void { $this->resetPage(); }
    public function updatedPerPage(): void        { $this->resetPage(); }

    public function updatedDateFrom(): void
    {
        $this->rangePreset = '';
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->rangePreset = '';
        $this->resetPage();
    }

    /**
     * "24h" maps to the current calendar day rather than a rolling 24-hour
     * window — the date pickers this row of buttons sits above are day-only
     * (<input type="date">), so keeping the same granularity keeps the quick
     * buttons and manual pickers visually consistent instead of half-broken.
     */
    public function setRangePreset(string $preset): void
    {
        $this->rangePreset = $preset;

        [$this->dateFrom, $this->dateTo] = match ($preset) {
            '24h' => [today()->toDateString(), today()->toDateString()],
            '7d'  => [today()->subDays(6)->toDateString(), today()->toDateString()],
            '30d' => [today()->subDays(29)->toDateString(), today()->toDateString()],
            default => [$this->dateFrom, $this->dateTo],
        };

        $this->resetPage();
    }

    public function sortByColumn(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }

        $this->resetPage();
    }

    public function openDrawer(int $id): void
    {
        $this->selectedLogId = $id;
    }

    public function closeDrawer(): void
    {
        $this->selectedLogId = null;
    }

    public function viewRelated(string $traceId): void
    {
        $this->filterTraceId = $traceId;
        $this->selectedLogId = null;
        $this->resetPage();
    }

    public function clearTraceFilter(): void
    {
        $this->filterTraceId = null;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search           = '';
        $this->filterUser       = '';
        $this->filterAction     = '';
        $this->filterEntity     = '';
        $this->filterModule     = '';
        $this->filterSeverity   = '';
        $this->filterStatus     = '';
        $this->filterActionType = '';
        $this->filterTraceId    = null;
        $this->dateFrom         = '';
        $this->dateTo           = '';
        $this->rangePreset      = '';
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
        if ($action === 'stock_received') {
            return ['label' => 'Stock received', 'icon' => 'box', 'color' => 'blue'];
        }
        if ($action === 'stock_imported') {
            return ['label' => 'Stock imported', 'icon' => 'box', 'color' => 'blue'];
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
        if (str_contains($action, 'user') && str_contains($action, 'created')) {
            return ['label' => 'User created', 'icon' => 'user', 'color' => 'blue'];
        }
        if (str_contains($action, 'user')) {
            return ['label' => 'User updated', 'icon' => 'user', 'color' => 'default'];
        }
        if ($action === 'login' || $action === 'signed_in') {
            return ['label' => 'Signed in', 'icon' => 'user', 'color' => 'default'];
        }
        if ($action === 'logout' || $action === 'signed_out') {
            return ['label' => 'Signed out', 'icon' => 'user', 'color' => 'default'];
        }
        if ($action === 'login_failed' || $action === 'failed_login') {
            return ['label' => 'Failed sign-in attempt', 'icon' => 'warning', 'color' => 'red'];
        }
        if ($action === 'lockout') {
            return ['label' => 'Account locked out', 'icon' => 'warning', 'color' => 'red'];
        }
        if ($action === 'permission_denied') {
            return ['label' => 'Permission denied', 'icon' => 'warning', 'color' => 'red'];
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

    /**
     * Coarse action-type bucket. Only the actions written by the new
     * AuditLogger/Auditable path map cleanly onto create/update/delete/auth/
     * access — the 19 files with pre-existing curated business actions
     * (transfer_approved, sale_voided, etc.) don't fit that vocabulary, so
     * they fall into a catch-all "action" bucket rather than being
     * mislabeled as a generic CRUD verb they aren't.
     */
    public function actionTypeOf(string $action): array
    {
        $action = strtolower($action);

        return match (true) {
            in_array($action, ['signed_in', 'signed_out', 'login', 'logout', 'failed_login', 'login_failed', 'lockout']) => ['value' => 'auth', 'label' => 'Auth'],
            $action === 'permission_denied' => ['value' => 'access', 'label' => 'Access'],
            $action === 'created' => ['value' => 'create', 'label' => 'Create'],
            $action === 'updated' => ['value' => 'update', 'label' => 'Update'],
            $action === 'deleted' => ['value' => 'delete', 'label' => 'Delete'],
            default => ['value' => 'action', 'label' => 'Business Action'],
        };
    }

    public static function actionTypeOptions(): array
    {
        return [
            ['value' => 'create', 'label' => 'Create'],
            ['value' => 'update', 'label' => 'Update'],
            ['value' => 'delete', 'label' => 'Delete'],
            ['value' => 'auth', 'label' => 'Auth'],
            ['value' => 'access', 'label' => 'Access'],
            ['value' => 'action', 'label' => 'Business Action'],
        ];
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

    public function getDistinctModulesProperty(): array
    {
        return ActivityLog::selectRaw('COALESCE(module, entity_type) as module_val')
            ->whereRaw('COALESCE(module, entity_type) IS NOT NULL')
            ->distinct()
            ->orderBy('module_val')
            ->pluck('module_val')
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
            || $this->filterModule !== ''
            || $this->filterSeverity !== ''
            || $this->filterStatus !== ''
            || $this->filterActionType !== ''
            || $this->filterTraceId !== null
            || $this->dateFrom !== ''
            || $this->dateTo !== '';
    }

    public function getSelectedLogProperty(): ?ActivityLog
    {
        return $this->selectedLogId ? ActivityLog::find($this->selectedLogId) : null;
    }

    public function getRelatedLogsProperty(): \Illuminate\Support\Collection
    {
        if (!$this->selectedLog?->trace_id) {
            return collect();
        }

        return ActivityLog::where('trace_id', $this->selectedLog->trace_id)
            ->where('id', '!=', $this->selectedLog->id)
            ->orderByDesc('created_at')
            ->get();
    }

    // ── Query building (shared by render() and exportCsv()) ──────────────────

    private function buildQuery(): Builder
    {
        $query = ActivityLog::query();

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
        if ($this->filterModule !== '') {
            $query->whereRaw('COALESCE(module, entity_type) = ?', [$this->filterModule]);
        }
        if ($this->filterSeverity !== '') {
            $query->where('severity', $this->filterSeverity);
        }
        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }
        if ($this->filterActionType !== '') {
            $actions = collect($this->getDistinctActionsProperty())
                ->pluck('value')
                ->filter(fn ($a) => $this->actionTypeOf($a)['value'] === $this->filterActionType)
                ->values()
                ->all();
            $query->whereIn('action', $actions);
        }
        if ($this->filterTraceId !== null) {
            $query->where('trace_id', $this->filterTraceId);
        }
        if ($this->dateFrom !== '') {
            $query->where('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo !== '') {
            $query->where('created_at', '<=', $this->dateTo . ' 23:59:59');
        }

        return $query->orderBy($this->sortBy, $this->sortDir);
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $rows = $this->buildQuery()->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Time', 'User', 'Action', 'Module', 'Entity Type', 'Reference', 'Status', 'Severity', 'IP Address']);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->created_at->format('Y-m-d H:i:s'),
                    csv_safe($row->user_name) ?? 'System',
                    $row->action,
                    $row->module ?? $row->entity_type,
                    $row->entity_type,
                    csv_safe($row->entity_identifier),
                    $row->status,
                    $row->severity,
                    $row->ip_address,
                ]);
            }

            fclose($out);
        }, 'activity-log-' . now()->format('Ymd-His') . '.csv');
    }

    public function render()
    {
        $logs = $this->buildQuery()->paginate($this->perPage);

        return view('livewire.owner.activity-logs', ['logs' => $logs]);
    }
}
