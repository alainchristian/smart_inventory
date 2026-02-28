<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class TimeFilter extends Component
{
    public string  $activePeriod = 'today';
    public string  $currency     = 'RWF';
    public ?string $customFrom   = null;
    public ?string $customTo     = null;
    public bool    $showCustom   = false;

    public function setPeriod(string $period): void
    {
        $this->activePeriod = $period;
        $this->showCustom   = false;
        $this->dispatchFilter();
    }

    public function applyCustomRange(): void
    {
        if ($this->customFrom && $this->customTo) {
            $this->activePeriod = 'custom';
            $this->showCustom   = false;
            $this->dispatchFilter();
        }
    }

    private function dispatchFilter(): void
    {
        $this->dispatch('time-filter-changed', [
            'period' => $this->activePeriod,
            'from'   => $this->customFrom,
            'to'     => $this->customTo,
        ]);
    }

    public function render()
    {
        return view('livewire.dashboard.time-filter');
    }
}
