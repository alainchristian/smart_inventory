<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\DailySession;
use Livewire\Component;

class SessionActivityPanel extends Component
{
    public int    $shopId           = 0;
    public ?int   $viewingSessionId = null;
    public string $search           = '';

    public function mount(int $shopId): void
    {
        $this->shopId = $shopId;

        $latest = DailySession::where('shop_id', $shopId)
            ->orderByDesc('session_date')
            ->orderByDesc('opened_at')
            ->first();

        $this->viewingSessionId = $latest?->id;
    }

    public function selectSession(int $id): void
    {
        $this->viewingSessionId = $id;
        $this->dispatch('session-selected', sessionId: $id);
    }

    public function render()
    {
        $query = DailySession::where('shop_id', $this->shopId)
            ->orderByDesc('session_date')
            ->orderByDesc('opened_at');

        if ($this->search !== '') {
            $query->whereRaw("TO_CHAR(session_date, 'DD Mon YYYY Mon') ILIKE ?", ['%' . $this->search . '%']);
        }

        $sessions         = $query->limit(60)->get();
        $selectedSession  = $this->viewingSessionId
            ? DailySession::find($this->viewingSessionId)
            : null;

        return view('livewire.shop.day-close.session-activity-panel', compact('sessions', 'selectedSession'));
    }
}
