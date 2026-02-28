<?php

namespace App\Livewire\Dashboard;

use App\Models\Alert;
use Livewire\Component;

class AlertsPanel extends Component
{
    /**
     * Dismiss an alert
     */
    public function dismissAlert(int $alertId): void
    {
        $alert = Alert::find($alertId);

        if ($alert) {
            $alert->markAsDismissed();
            $this->dispatch('alert-dismissed');
        }
    }

    /**
     * Get severity color configuration
     */
    public function getSeverityColors(string $severity): array
    {
        return match($severity) {
            'critical' => [
                'bg' => 'bg-[var(--danger-glow)]',
                'border' => 'border-[var(--danger)]',
                'text' => 'text-[var(--danger)]',
                'icon' => 'text-[var(--danger)]'
            ],
            'warning' => [
                'bg' => 'bg-[var(--warn-glow)]',
                'border' => 'border-[var(--warn)]',
                'text' => 'text-[var(--warn)]',
                'icon' => 'text-[var(--warn)]'
            ],
            'info' => [
                'bg' => 'bg-[var(--accent-glow)]',
                'border' => 'border-[var(--accent)]',
                'text' => 'text-[var(--accent)]',
                'icon' => 'text-[var(--accent)]'
            ],
            default => [
                'bg' => 'bg-gray-800',
                'border' => 'border-gray-600',
                'text' => 'text-gray-400',
                'icon' => 'text-gray-400'
            ]
        };
    }

    /**
     * Get icon for alert entity type
     */
    public function getAlertIcon(?string $entityType): string
    {
        if (!$entityType) {
            return 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
        }

        $type = strtolower($entityType);

        return match(true) {
            str_contains($type, 'product') || str_contains($type, 'stock') => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
            str_contains($type, 'box') => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            str_contains($type, 'transfer') => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
            str_contains($type, 'expir') => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
        };
    }

    public function render()
    {
        $alerts = Alert::unresolved()
            ->notDismissed()
            ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'warning' THEN 2 ELSE 3 END")
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        return view('livewire.dashboard.alerts-panel', [
            'alerts' => $alerts,
        ]);
    }
}
