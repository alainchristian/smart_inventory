<?php

namespace App\Livewire\Shop\DayClose;

use App\Models\DailySession;
use App\Models\Shop;
use App\Services\DayClose\DailySessionService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SessionHistory extends Component
{
    use WithPagination;

    public ?int $expandedId = null;

    /** all | open | closed | locked */
    #[Url(except: 'all')]
    public string $status = 'all';

    /** Owner-only shop filter. Kept as a URL-bound property (not request()->query) so it survives Livewire requests. */
    #[Url(as: 'shop_id')]
    public ?int $shopId = null;

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user->isShopManager() && ! $user->isOwner()) {
            abort(403);
        }
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedShopId(): void
    {
        $this->resetPage();
    }

    public function setStatus(string $status): void
    {
        $this->status = in_array($status, ['all', 'open', 'closed', 'locked'], true) ? $status : 'all';
        $this->resetPage();
    }

    public function toggleExpand(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    public function closeDetail(): void
    {
        $this->expandedId = null;
    }

    public function lockSession(int $sessionId): void
    {
        $user    = auth()->user();
        $session = DailySession::findOrFail($sessionId);

        try {
            app(DailySessionService::class)->lockSession($session, $user);
            $this->dispatch('notification', ['type' => 'success', 'message' => 'Session locked.']);
        } catch (\Exception $e) {
            $this->dispatch('notification', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /** Shop scope: shop managers are pinned to their own shop; owners may filter. */
    private function scopedShopId(): ?int
    {
        $user = auth()->user();

        return $user->isOwner() ? $this->shopId : $user->location_id;
    }

    private function baseQuery(): Builder
    {
        $query  = DailySession::query();
        $shopId = $this->scopedShopId();

        if ($shopId !== null) {
            $query->forShop($shopId);
        }

        return $query;
    }

    public function render()
    {
        $base = $this->baseQuery();

        // Aggregates across every session in scope (not just the current page)
        $stats = (clone $base)->selectRaw("
                count(*)                                                   as sessions,
                count(*) filter (where status = 'open')                    as open,
                count(*) filter (where status = 'closed')                  as closed,
                count(*) filter (where status = 'locked')                  as locked,
                coalesce(sum(total_sales), 0)                              as sales,
                coalesce(sum(total_expenses), 0)                           as expenses,
                coalesce(sum(total_withdrawals), 0)                        as withdrawals,
                coalesce(sum(cash_variance) filter (where status <> 'open'), 0) as variance,
                count(*) filter (where status <> 'open' and cash_variance < 0)  as short,
                count(*) filter (where status <> 'open' and cash_variance > 0)  as over,
                count(*) filter (where status <> 'open' and coalesce(cash_variance, 0) = 0) as balanced
            ")->toBase()->first();

        $listQuery = clone $base;
        if ($this->status !== 'all') {
            $listQuery->where('status', $this->status);
        }

        $sessions = $listQuery
            ->with(['openedBy', 'closedBy', 'lockedBy', 'shop'])
            ->orderByDesc('session_date')
            ->paginate(20);

        $expandedSession = null;
        if ($this->expandedId) {
            $expandedSession = (clone $base)->with([
                'openedBy', 'closedBy', 'lockedBy', 'shop',
                'expenses.category', 'ownerWithdrawals',
                'bankDeposits',
            ])->find($this->expandedId);
        }

        $user = auth()->user();

        return view('livewire.shop.day-close.session-history', [
            'sessions'        => $sessions,
            'expandedSession' => $expandedSession,
            'stats'           => $stats,
            'isOwner'         => $user->isOwner(),
            'showShopColumn'  => $user->isOwner() && $this->shopId === null,
            'shops'           => $user->isOwner() ? Shop::orderBy('name')->get(['id', 'name']) : collect(),
            'shopName'        => $this->scopedShopId() ? Shop::find($this->scopedShopId())?->name : null,
        ]);
    }
}
