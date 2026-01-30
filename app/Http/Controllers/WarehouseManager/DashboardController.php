<?php

namespace App\Http\Controllers\WarehouseManager;

use App\Enums\AlertSeverity;
use App\Enums\TransferStatus;
use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Box;
use App\Models\Product;
use App\Models\Transfer;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $warehouseId = $user->location_id;
        $warehouse = Warehouse::findOrFail($warehouseId);

        // Warehouse stock statistics
        $stockStats = [
            'total_boxes' => Box::where('location_type', 'warehouse')
                ->where('location_id', $warehouseId)
                ->count(),
            'full_boxes' => Box::where('location_type', 'warehouse')
                ->where('location_id', $warehouseId)
                ->where('status', 'full')
                ->count(),
            'partial_boxes' => Box::where('location_type', 'warehouse')
                ->where('location_id', $warehouseId)
                ->where('status', 'partial')
                ->count(),
            'empty_boxes' => Box::where('location_type', 'warehouse')
                ->where('location_id', $warehouseId)
                ->where('status', 'empty')
                ->count(),
            'damaged_boxes' => Box::where('location_type', 'warehouse')
                ->where('location_id', $warehouseId)
                ->where('status', 'damaged')
                ->count(),
            'total_items' => Box::where('location_type', 'warehouse')
                ->where('location_id', $warehouseId)
                ->sum('items_remaining'),
        ];

        // Inventory value for this warehouse
        $inventoryValue = Box::where('location_type', 'warehouse')
            ->where('location_id', $warehouseId)
            ->whereIn('status', ['full', 'partial'])
            ->with('product')
            ->get()
            ->sum(function ($box) {
                return $box->items_remaining * $box->product->purchase_price;
            });
        $stockStats['inventory_value'] = $inventoryValue / 100;

        // Pending transfer requests
        $pendingTransfers = Transfer::where('from_warehouse_id', $warehouseId)
            ->where('status', TransferStatus::PENDING)
            ->with(['toShop', 'requestedBy', 'items.product'])
            ->orderBy('requested_at', 'asc')
            ->get();

        // Recent approved transfers awaiting shipment
        $awaitingShipment = Transfer::where('from_warehouse_id', $warehouseId)
            ->where('status', TransferStatus::APPROVED)
            ->with(['toShop', 'requestedBy'])
            ->orderBy('reviewed_at', 'desc')
            ->limit(5)
            ->get();

        // Transfers in transit
        $inTransit = Transfer::where('from_warehouse_id', $warehouseId)
            ->where('status', TransferStatus::IN_TRANSIT)
            ->with(['toShop', 'transporter'])
            ->orderBy('shipped_at', 'desc')
            ->limit(5)
            ->get();

        // Low stock alerts for this warehouse
        $lowStockProducts = Product::active()
            ->with(['boxes' => function ($query) use ($warehouseId) {
                $query->where('location_type', 'warehouse')
                    ->where('location_id', $warehouseId)
                    ->whereIn('status', ['full', 'partial']);
            }])
            ->get()
            ->filter(function ($product) use ($warehouseId) {
                return $product->isLowStock('warehouse', $warehouseId);
            })
            ->map(function ($product) use ($warehouseId) {
                $stock = $product->getCurrentStock('warehouse', $warehouseId);
                $product->current_stock = $stock['total_items'];
                return $product;
            })
            ->sortBy('current_stock')
            ->take(10);

        // Expiring boxes (within 30 days)
        $expiringBoxes = Box::where('location_type', 'warehouse')
            ->where('location_id', $warehouseId)
            ->expiringSoon(30)
            ->with('product')
            ->orderBy('expiry_date', 'asc')
            ->limit(10)
            ->get();

        // Recent alerts for warehouse
        $alerts = Alert::where(function ($query) use ($warehouseId) {
            $query->whereNull('user_id')
                ->orWhere('user_id', auth()->id());
        })
            ->where(function ($query) use ($warehouseId) {
                $query->where('entity_type', 'warehouse')
                    ->where('entity_id', $warehouseId)
                    ->orWhereNull('entity_type');
            })
            ->unresolved()
            ->notDismissed()
            ->orderBy('severity', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Recent box receipts
        $recentBoxes = Box::where('location_type', 'warehouse')
            ->where('location_id', $warehouseId)
            ->with(['product', 'receivedBy'])
            ->orderBy('received_at', 'desc')
            ->limit(10)
            ->get();

        return view('warehouse.dashboard', compact(
            'warehouse',
            'stockStats',
            'pendingTransfers',
            'awaitingShipment',
            'inTransit',
            'lowStockProducts',
            'expiringBoxes',
            'alerts',
            'recentBoxes'
        ));
    }
}
