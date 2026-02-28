<?php

namespace App\Livewire\Dashboard;

use App\Models\ActivityLog;
use Livewire\Component;

class ActivityFeed extends Component
{
    /**
     * Get color for activity action type
     */
    public function getActionColor(string $action): string
    {
        return match(true) {
            str_contains($action, 'transfer') => 'accent',
            str_contains($action, 'sale') || str_contains($action, 'receipt') => 'success',
            str_contains($action, 'price') || str_contains($action, 'update') => 'warn',
            str_contains($action, 'damage') || str_contains($action, 'delete') => 'danger',
            default => 'accent2'
        };
    }

    /**
     * Format action description with bold entity references
     */
    public function formatDescription(ActivityLog $log): string
    {
        $description = $log->action;

        // Bold the entity identifier if present
        if ($log->entity_identifier) {
            $description = str_replace(
                $log->entity_identifier,
                "<strong class='text-white font-semibold'>{$log->entity_identifier}</strong>",
                $description
            );
        }

        // Bold any entity type mentions
        $entityTypes = ['box', 'transfer', 'sale', 'product', 'warehouse', 'shop'];
        foreach ($entityTypes as $type) {
            $description = preg_replace(
                "/\b({$type})\b/i",
                "<strong class='text-white font-semibold'>$1</strong>",
                $description
            );
        }

        return $description;
    }

    public function render()
    {
        $activities = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.dashboard.activity-feed', [
            'activities' => $activities,
        ]);
    }
}
