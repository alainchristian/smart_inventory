<?php

namespace App\Livewire\Dashboard;

use App\Enums\BoxStatus;
use App\Models\Box;
use Livewire\Component;
use Livewire\WithPagination;

class RecentMovements extends Component
{
    use WithPagination;

    public $sortField = 'updated_at';
    public $sortDirection = 'desc';

    /**
     * Sort by field
     */
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Get status color
     */
    public function getStatusColor($status): array
    {
        $statusValue = $status instanceof BoxStatus ? $status->value : $status;

        return match($statusValue) {
            'full' => ['bg' => 'bg-[var(--success-glow)]', 'text' => 'text-[var(--success)]'],
            'partial' => ['bg' => 'bg-[var(--warn-glow)]', 'text' => 'text-[var(--warn)]'],
            'damaged' => ['bg' => 'bg-[var(--danger-glow)]', 'text' => 'text-[var(--danger)]'],
            'empty' => ['bg' => 'bg-gray-700', 'text' => 'text-gray-400'],
            default => ['bg' => 'bg-gray-700', 'text' => 'text-gray-400']
        };
    }

    public function render()
    {
        $boxes = Box::with(['product', 'location'])
            ->whereIn('status', [
                BoxStatus::FULL,
                BoxStatus::PARTIAL,
                BoxStatus::DAMAGED
            ])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.dashboard.recent-movements', [
            'boxes' => $boxes,
        ]);
    }
}
