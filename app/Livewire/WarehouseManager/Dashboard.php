<?php

namespace App\Livewire\WarehouseManager;

use App\Models\Box;
use App\Models\Product;
use App\Models\Transfer;
use App\Models\Warehouse;
use App\Enums\TransferStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public int    $warehouseId;
    public string $period           = 'today';
    public string $customFrom       = '';
    public string $customTo         = '';
    public bool   $showCustomPicker = false;
    public string $periodLabel      = '';

    public function mount(int $warehouseId): void
    {
        $user = auth()->user();
        $this->warehouseId = $user->isWarehouseManager() ? $user->location_id : $warehouseId;
        $this->updatePeriodLabel();
    }

    public function setPeriod(string $period): void
    {
        if ($period === 'custom') {
            $this->showCustomPicker = true;
            if (!$this->customFrom) $this->customFrom = now()->subDays(6)->format('Y-m-d');
            if (!$this->customTo)   $this->customTo   = now()->format('Y-m-d');
            return;
        }
        $this->period           = $period;
        $this->showCustomPicker = false;
        $this->updatePeriodLabel();
    }

    public function applyCustomRange(): void
    {
        if ($this->customFrom && $this->customTo) {
            $this->period           = 'custom';
            $this->showCustomPicker = false;
            $this->updatePeriodLabel();
        }
    }

    public function cancelCustomPicker(): void
    {
        $this->showCustomPicker = false;
    }

    protected function getDateRange(): array
    {
        return match ($this->period) {
            'today'      => [today()->startOfDay(), today()->endOfDay()],
            'yesterday'  => [today()->subDay()->startOfDay(), today()->subDay()->endOfDay()],
            'this_week'  => [now()->startOfWeek(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth(), now()->endOfDay()],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'last_30'    => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            'custom'     => [Carbon::parse($this->customFrom)->startOfDay(), Carbon::parse($this->customTo)->endOfDay()],
            default      => [today()->startOfDay(), today()->endOfDay()],
        };
    }

    protected function getPrevDateRange(): array
    {
        [$from, $to] = $this->getDateRange();
        $days = (int) $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1;
        return [$from->copy()->subDays($days), $to->copy()->subDays($days)];
    }

    protected function updatePeriodLabel(): void
    {
        [$from, $to] = $this->getDateRange();
        $this->periodLabel = $from->isSameDay($to)
            ? $from->format('M j, Y')
            : $from->format('M j') . ' – ' . $to->format('M j, Y');
    }

    public function render()
    {
        $wId = $this->warehouseId;
        [$from, $to]         = $this->getDateRange();
        [$prevFrom, $prevTo] = $this->getPrevDateRange();
        $prevPeriodLabel     = $prevFrom->format('M j') . ' – ' . $prevTo->format('M j, Y');

        $warehouse = Warehouse::find($wId);

        // ── Current stock ────────────────────────────────────────────────
        $totalItems   = (int) Box::where('location_type','warehouse')->where('location_id',$wId)
            ->whereIn('status',['full','partial'])->sum('items_remaining');
        $totalBoxes   = (int) Box::where('location_type','warehouse')->where('location_id',$wId)
            ->whereIn('status',['full','partial'])->count();
        $fullBoxes    = (int) Box::where('location_type','warehouse')->where('location_id',$wId)
            ->where('status','full')->count();
        $partialBoxes = (int) Box::where('location_type','warehouse')->where('location_id',$wId)
            ->where('status','partial')->count();
        $damagedBoxes = (int) Box::where('location_type','warehouse')->where('location_id',$wId)
            ->where('status','damaged')->count();

        // ── Period KPIs ──────────────────────────────────────────────────
        $inboundBoxes      = (int) Box::where('location_type','warehouse')->where('location_id',$wId)
            ->whereBetween('created_at',[$from,$to])->count();
        $inboundItems      = (int) Box::where('location_type','warehouse')->where('location_id',$wId)
            ->whereBetween('created_at',[$from,$to])->sum('items_remaining');
        $outboundTransfers = (int) Transfer::where('from_warehouse_id',$wId)
            ->whereBetween('shipped_at',[$from,$to])->count();

        // ── Prev period KPIs ─────────────────────────────────────────────
        $prevInboundBoxes      = (int) Box::where('location_type','warehouse')->where('location_id',$wId)
            ->whereBetween('created_at',[$prevFrom,$prevTo])->count();
        $prevInboundItems      = (int) Box::where('location_type','warehouse')->where('location_id',$wId)
            ->whereBetween('created_at',[$prevFrom,$prevTo])->sum('items_remaining');
        $prevOutboundTransfers = (int) Transfer::where('from_warehouse_id',$wId)
            ->whereBetween('shipped_at',[$prevFrom,$prevTo])->count();

        // ── % Changes ────────────────────────────────────────────────────
        $itemsChange = $prevInboundItems > 0
            ? round(($inboundItems - $prevInboundItems) / $prevInboundItems * 100, 1)
            : ($inboundItems > 0 ? 100 : 0);
        $inboundChange = $prevInboundBoxes > 0
            ? round(($inboundBoxes - $prevInboundBoxes) / $prevInboundBoxes * 100, 1)
            : ($inboundBoxes > 0 ? 100 : 0);
        $outboundChange = $prevOutboundTransfers > 0
            ? round(($outboundTransfers - $prevOutboundTransfers) / $prevOutboundTransfers * 100, 1)
            : ($outboundTransfers > 0 ? 100 : 0);

        // ── Outbound boxes (quantity_requested stores boxes directly) ──────
        $outboundBoxes = (int) DB::table('transfer_items')
            ->join('transfers', 'transfer_items.transfer_id', '=', 'transfers.id')
            ->where('transfers.from_warehouse_id', $wId)
            ->whereBetween('transfers.shipped_at', [$from, $to])
            ->whereNull('transfers.deleted_at')
            ->sum('transfer_items.quantity_requested');

        $outboundItems = (int) DB::table('transfer_items')
            ->join('transfers', 'transfer_items.transfer_id', '=', 'transfers.id')
            ->join('products',  'transfer_items.product_id',  '=', 'products.id')
            ->where('transfers.from_warehouse_id', $wId)
            ->whereBetween('transfers.shipped_at', [$from, $to])
            ->whereNull('transfers.deleted_at')
            ->selectRaw('COALESCE(SUM(transfer_items.quantity_requested::numeric * products.items_per_box::numeric), 0) as total')
            ->value('total') ?? 0;

        $prevOutboundBoxes = (int) DB::table('transfer_items')
            ->join('transfers', 'transfer_items.transfer_id', '=', 'transfers.id')
            ->where('transfers.from_warehouse_id', $wId)
            ->whereBetween('transfers.shipped_at', [$prevFrom, $prevTo])
            ->whereNull('transfers.deleted_at')
            ->sum('transfer_items.quantity_requested');
        $outboundBoxesChange = $prevOutboundBoxes > 0
            ? round(($outboundBoxes - $prevOutboundBoxes) / $prevOutboundBoxes * 100, 1)
            : ($outboundBoxes > 0 ? 100 : 0);

        $outboundShopsCount = (int) Transfer::where('from_warehouse_id',$wId)
            ->whereBetween('shipped_at',[$from,$to])
            ->distinct('to_shop_id')->count('to_shop_id');

        $netStockChange = $inboundBoxes - $outboundBoxes;

        // ── Low stock ────────────────────────────────────────────────────
        $lowStockProducts = Product::active()
            ->with(['boxes' => fn($q) => $q->where('location_type','warehouse')
                ->where('location_id',$wId)->whereIn('status',['full','partial'])])
            ->get()
            ->filter(fn($p) => $p->isLowStock('warehouse',$wId))
            ->map(function($p) use ($wId) {
                $p->current_stock = $p->getCurrentStock('warehouse',$wId)['total_items'];
                return $p;
            })
            ->sortBy('current_stock')->take(5);
        $lowStockCount = $lowStockProducts->count();

        // ── Transfer pipeline ────────────────────────────────────────────
        $pendingTransfers = Transfer::where('from_warehouse_id',$wId)
            ->where('status', TransferStatus::PENDING)
            ->with(['toShop'])->orderBy('requested_at','asc')->get();
        $awaitingShipment = Transfer::where('from_warehouse_id',$wId)
            ->where('status', TransferStatus::APPROVED)
            ->with(['toShop'])->orderBy('reviewed_at','desc')->limit(6)->get();
        $inTransit = Transfer::where('from_warehouse_id',$wId)
            ->where('status', TransferStatus::IN_TRANSIT)
            ->with(['toShop'])->orderBy('shipped_at','desc')->limit(6)->get();

        // ── Activity feed ─────────────────────────────────────────────────
        $recentBoxes = Box::where('location_type','warehouse')->where('location_id',$wId)
            ->with(['product','receivedBy'])->orderBy('created_at','desc')->limit(5)->get()
            ->map(fn($b) => [
                'type'  => 'receipt',
                'time'  => $b->created_at,
                'title' => 'Shipment Received',
                'sub'   => ($b->product->name ?? '—') . ' – ' . number_format($b->items_remaining) . ' units',
                'color' => 'green',
            ]);

        $recentShipments = Transfer::where('from_warehouse_id',$wId)
            ->whereNotNull('shipped_at')
            ->with(['toShop'])->orderBy('shipped_at','desc')->limit(5)->get()
            ->map(fn($t) => [
                'type'  => 'transfer',
                'time'  => $t->shipped_at,
                'title' => 'Stock Sent to Shop',
                'sub'   => 'Shop: ' . ($t->toShop->name ?? '—') . ' – ' . $t->transfer_number,
                'color' => 'accent',
            ]);

        $recentApprovals = Transfer::where('from_warehouse_id',$wId)
            ->whereNotNull('reviewed_at')
            ->with(['toShop'])->orderBy('reviewed_at','desc')->limit(3)->get()
            ->map(fn($t) => [
                'type'  => 'approval',
                'time'  => $t->reviewed_at,
                'title' => 'Transfer Approved',
                'sub'   => $t->transfer_number . ' → ' . ($t->toShop->name ?? '—'),
                'color' => 'purple',
            ]);

        $activityFeed = $recentBoxes->concat($recentShipments)->concat($recentApprovals)
            ->sortByDesc('time')->take(8)->values();

        // ── Category breakdown ────────────────────────────────────────────
        $categoryBreakdown = DB::table('boxes')
            ->join('products','boxes.product_id','=','products.id')
            ->join('categories','products.category_id','=','categories.id')
            ->where('boxes.location_type','warehouse')->where('boxes.location_id',$wId)
            ->whereIn('boxes.status',['full','partial','damaged'])->whereNull('products.deleted_at')
            ->select('categories.name', DB::raw('COUNT(boxes.id) as total_items'))
            ->groupBy('categories.name')->orderByDesc('total_items')->limit(6)->get();

        $categoryTotal = (int) $categoryBreakdown->sum('total_items');

        // ── Inventory value ───────────────────────────────────────────────
        $inventoryValue = (int) DB::table('boxes')
            ->join('products','boxes.product_id','=','products.id')
            ->where('boxes.location_type','warehouse')->where('boxes.location_id',$wId)
            ->whereIn('boxes.status',['full','partial'])->whereNull('products.deleted_at')
            ->selectRaw('COALESCE(SUM(boxes.items_remaining * products.purchase_price), 0) as val')
            ->value('val') ?? 0;

        // ── Sparklines (7 data points) ────────────────────────────────────
        $diffDays = (int) $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay());
        $sparkInbound = $sparkOutbound = [];

        if ($diffDays === 0) {
            $hourSlots = [[0,3],[4,7],[8,10],[11,13],[14,16],[17,19],[20,23]];
            foreach ($hourSlots as [$sH,$eH]) {
                $s = $from->copy()->setHour($sH)->setMinute(0)->setSecond(0);
                $e = $from->copy()->setHour($eH)->setMinute(59)->setSecond(59);
                $sparkInbound[]  = (int) Box::where('location_type','warehouse')->where('location_id',$wId)->whereBetween('created_at',[$s,$e])->count();
                $sparkOutbound[] = (int) Transfer::where('from_warehouse_id',$wId)->whereBetween('shipped_at',[$s,$e])->count();
            }
        } elseif ($diffDays < 7) {
            for ($i = 0; $i <= $diffDays; $i++) {
                $d = $from->copy()->addDays($i)->format('Y-m-d');
                $sparkInbound[]  = (int) Box::where('location_type','warehouse')->where('location_id',$wId)->whereDate('created_at',$d)->count();
                $sparkOutbound[] = (int) Transfer::where('from_warehouse_id',$wId)->whereDate('shipped_at',$d)->count();
            }
        } else {
            $step = max(1,(int) round($diffDays / 6));
            for ($i = 0; $i <= 6; $i++) {
                $day = $from->copy()->addDays($i * $step);
                if ($day->gt($to)) break;
                $d = $day->format('Y-m-d');
                $sparkInbound[]  = (int) Box::where('location_type','warehouse')->where('location_id',$wId)->whereDate('created_at',$d)->count();
                $sparkOutbound[] = (int) Transfer::where('from_warehouse_id',$wId)->whereDate('shipped_at',$d)->count();
            }
        }

        // ── Trend chart (This Period vs Previous Period) ──────────────────
        $trendLabels = $trendCurrent = $trendPrev = [];

        if ($diffDays === 0) {
            $hourSlots = [[0,3],[4,7],[8,10],[11,13],[14,16],[17,19],[20,23]];
            foreach ($hourSlots as $idx => [$sH,$eH]) {
                $s  = $from->copy()->setHour($sH)->setMinute(0)->setSecond(0);
                $e  = $from->copy()->setHour($eH)->setMinute(59)->setSecond(59);
                $ps = $prevFrom->copy()->setHour($sH)->setMinute(0)->setSecond(0);
                $pe = $prevFrom->copy()->setHour($eH)->setMinute(59)->setSecond(59);
                $label = $sH === 0 ? '12AM' : ($sH < 12 ? "{$sH}AM" : ($sH === 12 ? '12PM' : ($sH-12).'PM'));
                $trendLabels[]  = $label;
                $trendCurrent[] = (int) Box::where('location_type','warehouse')->where('location_id',$wId)->whereBetween('created_at',[$s,$e])->count();
                $trendPrev[]    = (int) Box::where('location_type','warehouse')->where('location_id',$wId)->whereBetween('created_at',[$ps,$pe])->count();
            }
        } elseif ($diffDays < 14) {
            for ($i = 0; $i <= $diffDays; $i++) {
                $day     = $from->copy()->addDays($i);
                $prevDay = $prevFrom->copy()->addDays($i);
                $trendLabels[]  = $day->format('M j');
                $trendCurrent[] = (int) Box::where('location_type','warehouse')->where('location_id',$wId)->whereDate('created_at',$day->format('Y-m-d'))->count();
                $trendPrev[]    = (int) Box::where('location_type','warehouse')->where('location_id',$wId)->whereDate('created_at',$prevDay->format('Y-m-d'))->count();
            }
        } else {
            $step = max(1,(int) round($diffDays / 13));
            for ($i = 0, $day = $from->copy(); $day->lte($to); $day->addDays($step), $i++) {
                $prevDay = $prevFrom->copy()->addDays($i * $step);
                $trendLabels[]  = $day->format('M j');
                $trendCurrent[] = (int) Box::where('location_type','warehouse')->where('location_id',$wId)->whereDate('created_at',$day->format('Y-m-d'))->count();
                $trendPrev[]    = (int) Box::where('location_type','warehouse')->where('location_id',$wId)->whereDate('created_at',$prevDay->format('Y-m-d'))->count();
            }
        }

        // ── Dynamic insights ──────────────────────────────────────────────
        $insights = [];
        if ($inboundBoxes > 0 || $prevInboundBoxes > 0) {
            $insights[] = $inboundChange >= 0
                ? "Inbound boxes are up <strong>{$inboundChange}%</strong> compared to the previous period."
                : "Inbound boxes decreased by <strong>" . abs($inboundChange) . "%</strong> compared to the previous period.";
        } else {
            $insights[] = "No inbound boxes recorded for this period. Consider scheduling new deliveries.";
        }
        $insights[] = $outboundBoxesChange < 0
            ? "Outbound boxes decreased by <strong>" . abs($outboundBoxesChange) . "%</strong>. Ensure timely supplies to avoid stockouts."
            : "Outbound boxes " . ($outboundBoxesChange > 0 ? "increased by <strong>{$outboundBoxesChange}%</strong>" : "unchanged") . " compared to the previous period.";
        $insights[] = $lowStockCount > 0
            ? "<strong>{$lowStockCount} product" . ($lowStockCount > 1 ? 's are' : ' is') . " low in stock</strong> and need" . ($lowStockCount > 1 ? '' : 's') . " attention."
            : "All products are <strong>well-stocked</strong>. No immediate restocking needed.";

        return view('livewire.warehouse-manager.dashboard', compact(
            'warehouse',
            'totalItems', 'totalBoxes', 'fullBoxes', 'partialBoxes', 'damagedBoxes',
            'inboundBoxes', 'inboundItems', 'outboundTransfers', 'outboundBoxes', 'outboundBoxesChange',
            'itemsChange', 'inboundChange', 'outboundChange',
            'outboundItems', 'outboundShopsCount', 'netStockChange',
            'lowStockCount', 'lowStockProducts',
            'pendingTransfers', 'awaitingShipment', 'inTransit',
            'activityFeed', 'categoryBreakdown', 'categoryTotal', 'inventoryValue',
            'sparkInbound', 'sparkOutbound',
            'trendLabels', 'trendCurrent', 'trendPrev',
            'prevPeriodLabel', 'insights',
            'from', 'to'
        ));
    }
}
