<?php

namespace App\Livewire\Transfers\Concerns;

use App\Enums\TransferStatus;
use App\Models\Shop;
use App\Models\Transfer;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

/**
 * One transfer list for owner, shop and warehouse
 * (view: livewire/transfers/transfers-list). Each component supplies
 * role() and scope(); the trait owns filters, tab counts and summaries.
 *
 * Summary cards always cover all of the role's transfers (never the
 * filtered rows); tab counts follow the search / period / shop filters.
 */
trait ListsTransfers
{
    use WithPagination;

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'period', except: 'any')]
    public string $period = 'any';

    #[Url(as: 'shop', except: '')]
    public string $shopFilter = '';

    /** owner | shop | warehouse */
    abstract protected function role(): string;

    /** Restrict to the transfers this user may see. */
    abstract protected function scope(Builder $query): Builder;

    public const PERIODS = [
        'any'        => 'Any time',
        'today'      => 'Today',
        'last_7'     => 'Last 7 days',
        'last_30'    => 'Last 30 days',
        'month'      => 'This month',
        'last_month' => 'Last month',
    ];

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingPeriod(): void { $this->resetPage(); }
    public function updatingShopFilter(): void { $this->resetPage(); }

    public function setStatus(string $status): void
    {
        $this->statusFilter = $this->statusOptions()->has($status) ? $status : 'all';
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'period', 'shopFilter', 'statusFilter');
        $this->resetPage();
    }

    /** Tabs, in order, for this role. */
    protected function statusOptions()
    {
        // In the order a transfer moves through; the dead ends last.
        $tabs = collect(['all' => 'All'])->merge(collect([
            TransferStatus::PENDING, TransferStatus::APPROVED, TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED,
            TransferStatus::RECEIVED, TransferStatus::REJECTED, TransferStatus::CANCELLED,
        ])->mapWithKeys(fn ($s) => [$s->value => $s->label()]));

        return $this->role() === 'shop' ? $tabs : $tabs->put('discrepancy', 'Discrepancies');
    }

    protected function baseQuery(): Builder
    {
        return $this->scope(Transfer::query());
    }

    /** @return array{0: Carbon, 1: Carbon}|null  UTC bounds of the period on requested_at */
    protected function periodBounds(): ?array
    {
        $today = business_today();

        [$from, $to] = match ($this->period) {
            'today'      => [$today->copy(), $today->copy()],
            'last_7'     => [$today->copy()->subDays(6), $today->copy()],
            'last_30'    => [$today->copy()->subDays(29), $today->copy()],
            'month'      => [$today->copy()->startOfMonth(), $today->copy()],
            'last_month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
            default      => [null, null],
        };

        return $from ? [$from->startOfDay()->utc(), $to->endOfDay()->utc()] : null;
    }

    /** Search / period / shop filters (not status). */
    protected function filteredQuery(): Builder
    {
        $query = $this->baseQuery();

        $term = trim($this->search);
        if ($term !== '') {
            $like = '%' . addcslashes($term, '\\%_') . '%';
            $query->where(function (Builder $q) use ($like) {
                $q->where('transfer_number', 'ilike', $like)
                  ->orWhereHas('toShop', fn ($s) => $s->where('name', 'ilike', $like))
                  ->orWhereHas('items.product', fn ($p) => $p->where('name', 'ilike', $like)
                      ->orWhere('sku', 'ilike', $like)->orWhere('barcode', 'ilike', $like));
            });
        }

        if ($bounds = $this->periodBounds()) {
            $query->whereBetween('requested_at', $bounds);
        }

        if ($this->shopFilter !== '' && $this->role() !== 'shop') {
            $query->where('to_shop_id', (int) $this->shopFilter);
        }

        return $query;
    }

    protected function applyStatus(Builder $query, string $status): Builder
    {
        return match ($status) {
            'all'         => $query,
            'discrepancy' => $query->where('has_discrepancy', true),
            default       => $query->where('status', $status),
        };
    }

    /** Count per tab in one query. */
    protected function tabCounts(): array
    {
        $row = $this->filteredQuery()->toBase()->selectRaw(
            'count(*) as "all", count(*) filter (where has_discrepancy) as discrepancy, '
            . collect(TransferStatus::cases())
                ->map(fn ($s) => "count(*) filter (where status = '{$s->value}') as \"{$s->value}\"")
                ->implode(', ')
        )->first();

        return array_map('intval', (array) $row);
    }

    protected function rowsQuery(): Builder
    {
        return $this->filteredQuery()
            ->with(['toShop:id,name', 'fromWarehouse:id,name', 'requestedBy:id,name'])
            ->withCount('items')
            ->withSum('items as boxes_requested', 'quantity_requested');
    }

    /** Shops the filter offers (owner: all; warehouse: shops it has sent to). */
    protected function shopOptions()
    {
        if ($this->role() === 'shop') {
            return collect();
        }

        return Shop::whereIn('id', $this->baseQuery()->select('to_shop_id')->distinct())
            ->orderBy('name')->pluck('name', 'id');
    }

    /** Transfers the warehouse must act on: pending (approve) and approved (pack), oldest first. */
    protected function needsAction()
    {
        return $this->baseQuery()
            ->whereIn('status', [TransferStatus::PENDING, TransferStatus::APPROVED])
            ->with(['toShop:id,name', 'requestedBy:id,name'])
            ->withCount('items')
            ->withSum('items as boxes_requested', 'quantity_requested')
            ->orderBy('requested_at')
            ->get();
    }

    // ── Summary figures (always over all of the role's transfers) ─────────

    protected function monthStartUtc(): Carbon
    {
        return business_today()->startOfMonth()->utc();
    }

    /** Waiting time of the oldest transfer in a status, e.g. "2 d" / "5 h". */
    protected function oldestWaiting(TransferStatus $status, string $column): ?string
    {
        $oldest = $this->baseQuery()->where('status', $status)->min($column);
        if (! $oldest) {
            return null;
        }
        $hours = (int) Carbon::parse($oldest)->diffInHours(now());

        return $hours >= 48 ? intdiv($hours, 24) . ' d' : max(1, $hours) . ' h';
    }

    /** Boxes requested across transfers in the given statuses. */
    protected function boxesIn(array $statuses): int
    {
        return (int) DB::table('transfer_items')
            ->whereIn('transfer_id', $this->baseQuery()->whereIn('status', $statuses)->select('id'))
            ->sum('quantity_requested');
    }

    /** Received this month: count, boxes received, damaged boxes, average days from request to receipt. */
    protected function receivedThisMonth(): array
    {
        $ids = $this->baseQuery()->where('status', TransferStatus::RECEIVED)->where('received_at', '>=', $this->monthStartUtc())->select('id');
        $boxes = DB::table('transfer_boxes')->whereIn('transfer_id', $ids)
            ->selectRaw('count(*) filter (where is_received) as received, count(*) filter (where is_damaged) as damaged, count(*) filter (where not is_received) as missing')
            ->first();
        $days = $this->baseQuery()->where('status', TransferStatus::RECEIVED)->where('received_at', '>=', $this->monthStartUtc())
            ->toBase()->selectRaw('avg(extract(epoch from (received_at - requested_at)) / 86400) as d')->value('d');

        return [
            'count'    => $this->baseQuery()->where('status', TransferStatus::RECEIVED)->where('received_at', '>=', $this->monthStartUtc())->count(),
            'boxes'    => (int) ($boxes->received ?? 0),
            'damaged'  => (int) ($boxes->damaged ?? 0),
            'missing'  => (int) ($boxes->missing ?? 0),
            // "2.5 d", or hours under a day
            'lead'     => $days === null ? null : ((float) $days >= 1 ? round((float) $days, 1) . ' d' : max(1, (int) round((float) $days * 24)) . ' h'),
        ];
    }

    protected function summary(): array
    {
        $count = fn (array $statuses) => $this->baseQuery()->whereIn('status', $statuses)->count();
        $road  = [TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED];

        return [
            'pending'         => $count([TransferStatus::PENDING]),
            'pending_boxes'   => $this->boxesIn([TransferStatus::PENDING]),
            'pending_oldest'  => $this->oldestWaiting(TransferStatus::PENDING, 'requested_at'),
            'pending_shops'   => $this->baseQuery()->where('status', TransferStatus::PENDING)->distinct()->count('to_shop_id'),
            'approved'        => $count([TransferStatus::APPROVED]),
            'approved_boxes'  => $this->boxesIn([TransferStatus::APPROVED]),
            'approved_oldest' => $this->oldestWaiting(TransferStatus::APPROVED, 'reviewed_at'),
            'packing_started' => $this->baseQuery()->where('status', TransferStatus::APPROVED)->whereNotNull('packed_at')->count(),
            'in_transit'      => $count([TransferStatus::IN_TRANSIT]),
            'delivered'       => $count([TransferStatus::DELIVERED]),
            'road_boxes'      => (int) DB::table('transfer_boxes')->whereIn('transfer_id', $this->baseQuery()->whereIn('status', $road)->select('id'))->count(),
            'shipped_today'   => $this->baseQuery()->where('shipped_at', '>=', business_today()->startOfDay()->utc())->count(),
            'received'        => $this->receivedThisMonth(),
            'discrepancies'   => $this->baseQuery()->where('has_discrepancy', true)->where('received_at', '>=', $this->monthStartUtc())->count(),
            'discrepancies_all' => $this->baseQuery()->where('has_discrepancy', true)->count(),
        ];
    }

    public function render()
    {
        $showNeeds = $this->role() === 'warehouse' && $this->statusFilter === 'all'
            && trim($this->search) === '' && $this->period === 'any' && $this->shopFilter === '';

        $rows = $this->applyStatus($this->rowsQuery(), $this->statusFilter);
        if ($showNeeds) {
            // "Needs you" lists these above; the table holds the rest.
            $rows->whereNotIn('status', [TransferStatus::PENDING, TransferStatus::APPROVED]);
        }

        return view('livewire.transfers.transfers-list', [
            'role'        => $this->role(),
            'transfers'   => $rows->orderByDesc('requested_at')->orderByDesc('id')->paginate(20),
            'tabs'        => $this->statusOptions(),
            'counts'      => $this->tabCounts(),
            'summary'     => $this->summary(),
            'shops'       => $this->shopOptions(),
            'needs'       => $showNeeds ? $this->needsAction() : collect(),
            'showNeeds'   => $showNeeds,
            'periods'     => self::PERIODS,
            // Filters in the phone sheet (period, shop) and all active filters.
            'sheetFilters'=> ($this->period !== 'any' ? 1 : 0) + ($this->shopFilter !== '' ? 1 : 0),
            'filtersOn'   => (trim($this->search) !== '' ? 1 : 0) + ($this->period !== 'any' ? 1 : 0) + ($this->shopFilter !== '' ? 1 : 0),
        ]);
    }
}
