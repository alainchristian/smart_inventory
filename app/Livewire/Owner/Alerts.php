<?php

namespace App\Livewire\Owner;

use App\Models\Alert;
use Livewire\Component;
use Livewire\WithPagination;

class Alerts extends Component
{
    use WithPagination;

    public string $filterStatus   = 'unresolved';
    public string $filterSeverity = '';
    public string $search         = '';

    protected $queryString = [
        'filterStatus'   => ['except' => 'unresolved'],
        'filterSeverity' => ['except' => ''],
        'search'         => ['except' => ''],
    ];

    public function updatedFilterStatus(): void   { $this->resetPage(); }
    public function updatedFilterSeverity(): void { $this->resetPage(); }
    public function updatedSearch(): void         { $this->resetPage(); }

    public function dismiss(int $alertId): void
    {
        $alert = Alert::find($alertId);
        if ($alert && !$alert->is_resolved) {
            $alert->markAsDismissed();
        }
    }

    public function resolve(int $alertId): void
    {
        $alert = Alert::find($alertId);
        if ($alert) {
            $alert->markAsResolved(auth()->id());
        }
    }

    public function resolveAll(): void
    {
        Alert::unresolved()->notDismissed()->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ]);
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filterStatus   = 'unresolved';
        $this->filterSeverity = '';
        $this->search         = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Alert::query()->orderByRaw(
            "CASE severity WHEN 'critical' THEN 1 WHEN 'warning' THEN 2 ELSE 3 END"
        )->orderByDesc('created_at');

        match ($this->filterStatus) {
            'unresolved' => $query->unresolved()->notDismissed(),
            'resolved'   => $query->resolved(),
            'dismissed'  => $query->where('is_dismissed', true),
            default      => null,
        };

        if ($this->filterSeverity !== '') {
            $query->where('severity', $this->filterSeverity);
        }

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('title', 'ilike', '%' . $this->search . '%')
                  ->orWhere('message', 'ilike', '%' . $this->search . '%')
                  ->orWhere('entity_type', 'ilike', '%' . $this->search . '%');
            });
        }

        $counts = [
            'unresolved' => Alert::unresolved()->notDismissed()->count(),
            'resolved'   => Alert::resolved()->count(),
            'dismissed'  => Alert::where('is_dismissed', true)->count(),
        ];

        return view('livewire.owner.alerts', [
            'alerts' => $query->paginate(25),
            'counts' => $counts,
        ]);
    }
}
