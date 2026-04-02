<?php

namespace App\Livewire\Dashboard;

use App\Models\Alert;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AlertsAndHealth extends Component
{
    public string  $activeTab      = 'alerts';
    public ?string $filterSeverity = null;

    // System health props (from SystemStatus)
    public bool $dbOk          = true;
    public bool $queueOk       = true;
    public bool $posOk         = true;
    public bool $allOk         = true;
    public int  $criticalCount = 0;

    public function mount(): void
    {
        $this->runHealthChecks();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function setFilterSeverity(?string $severity): void
    {
        $this->filterSeverity = $severity;
    }

    public function getAlertSummary(): array
    {
        return [
            'critical' => Alert::unresolved()->notDismissed()->where('severity', 'critical')->count(),
            'warning'  => Alert::unresolved()->notDismissed()->where('severity', 'warning')->count(),
            'info'     => Alert::unresolved()->notDismissed()->where('severity', 'info')->count(),
        ];
    }

    public function dismissAlert(int $alertId): void
    {
        $alert = Alert::find($alertId);
        if ($alert) {
            $alert->markAsDismissed();
        }
    }

    public function runHealthChecks(): void
    {
        try {
            DB::select('SELECT 1');
            $this->dbOk = true;
        } catch (\Exception $e) {
            $this->dbOk = false;
        }

        $this->queueOk = !DB::table('failed_jobs')
            ->where('failed_at', '>=', now()->subHour())->exists();

        $this->posOk = Sale::where('created_at', '>=', now()->subHours(4))->exists();

        $this->criticalCount = Alert::where('severity', 'critical')
            ->where('is_resolved', false)->count();

        $this->allOk = $this->dbOk && $this->queueOk;
    }

    public function getSeverityColors(string $severity): array
    {
        return match ($severity) {
            'critical' => [
                'bg'     => 'var(--red-dim)',
                'border' => 'var(--red)',
                'text'   => 'var(--red)',
            ],
            'warning' => [
                'bg'     => 'var(--amber-dim)',
                'border' => 'var(--amber)',
                'text'   => 'var(--amber)',
            ],
            'info' => [
                'bg'     => 'var(--accent-dim)',
                'border' => 'var(--accent)',
                'text'   => 'var(--accent)',
            ],
            default => [
                'bg'     => 'var(--surface2)',
                'border' => 'var(--border)',
                'text'   => 'var(--text-dim)',
            ],
        };
    }

    public function getAlertIcon(?string $entityType): string
    {
        if (!$entityType) {
            return 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
        }

        $type = strtolower($entityType);

        return match (true) {
            str_contains($type, 'product') || str_contains($type, 'stock')
                => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
            str_contains($type, 'box')
                => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            str_contains($type, 'transfer')
                => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
            str_contains($type, 'expir')
                => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            default
                => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        };
    }

    public function render()
    {
        $query = Alert::unresolved()
            ->notDismissed()
            ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'warning' THEN 2 ELSE 3 END")
            ->orderBy('created_at', 'desc');

        if ($this->filterSeverity !== null) {
            $query->where('severity', $this->filterSeverity);
        }

        $alerts       = $query->limit(12)->get();
        $alertSummary = $this->getAlertSummary();

        return view('livewire.dashboard.alerts-and-health', [
            'alerts'       => $alerts,
            'alertSummary' => $alertSummary,
        ]);
    }
}
