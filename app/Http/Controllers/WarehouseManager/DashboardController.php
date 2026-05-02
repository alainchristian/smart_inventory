<?php

namespace App\Http\Controllers\WarehouseManager;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isOwner()) {
            $warehouseId = $request->query('warehouse_id');
            if (!$warehouseId) {
                $warehouse = Warehouse::orderBy('name')->first();
                if (!$warehouse) {
                    abort(404, 'No warehouses found in the system.');
                }
                $warehouseId = $warehouse->id;
            }
        } else {
            if (!$user->location_id) {
                abort(403, 'No warehouse assigned to your account.');
            }
            $warehouseId = $user->location_id;
        }

        return view('warehouse.dashboard', compact('warehouseId'));
    }
}
