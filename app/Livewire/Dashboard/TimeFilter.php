<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class TimeFilter extends Component
{
    public string $preset   = 'today';
    public string $dateFrom = '';
    public string $dateTo   = '';
    public string $currency = 'RWF';

    public function mount(): void
    {
        $this->resolveDates();
    }

    public function setPreset(string $preset): void
    {
        $this->preset = $preset;
        $this->resolveDates();
        $this->dispatchFilter();
    }

    // Livewire lifecycle hook — fires when dateFrom is changed via wire:model.live
    public function updatedDateFrom(): void
    {
        $this->preset = 'custom';
        $this->dispatchFilter();
    }

    // Livewire lifecycle hook — fires when dateTo is changed via wire:model.live
    public function updatedDateTo(): void
    {
        $this->preset = 'custom';
        $this->dispatchFilter();
    }

    private function resolveDates(): void
    {
        $today = business_today();

        match ($this->preset) {
            'today'      => [$this->dateFrom, $this->dateTo] = [$today->toDateString(), $today->toDateString()],
            'yesterday'  => [$this->dateFrom, $this->dateTo] = [$today->copy()->subDay()->toDateString(), $today->copy()->subDay()->toDateString()],
            'week'       => [$this->dateFrom, $this->dateTo] = [$today->copy()->startOfWeek()->toDateString(), $today->toDateString()],
            'month'      => [$this->dateFrom, $this->dateTo] = [$today->copy()->startOfMonth()->toDateString(), $today->toDateString()],
            'last_month' => [$this->dateFrom, $this->dateTo] = [
                $today->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(),
                $today->copy()->subMonthNoOverflow()->endOfMonth()->toDateString(),
            ],
            default      => [$this->dateFrom, $this->dateTo] = [$today->copy()->subDays(29)->toDateString(), $today->toDateString()],
        };
    }

    private function dispatchFilter(): void
    {
        // Livewire 3 named parameters — listeners: refresh(string $period, ?string $from, ?string $to)
        $this->dispatch('time-filter-changed',
            period: $this->preset,
            from:   $this->dateFrom,
            to:     $this->dateTo,
        );
    }

    public function render()
    {
        return view('livewire.dashboard.time-filter');
    }
}