<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function print(Sale $sale, Request $request)
    {
        $user = auth()->user();

        $isWarehouseUser = $user->isWarehouseManager() && $sale->source_warehouse_id === $user->location_id;

        if (! $user->isOwner() && $user->location_id !== $sale->shop_id && ! $isWarehouseUser) {
            abort(403);
        }

        $sale->load(['items.product', 'items.box', 'payments', 'soldBy', 'shop', 'customer']);

        $groupedItems = $sale->groupedItems();

        // The picking slip (warehouse fulfillment queue) and the document that
        // physically travels to the warehouse with a transporter must not
        // disclose the sale amount — only the customer's own pickup receipt
        // (or an explicit ?full=1 reprint, e.g. from Sales History / Reprint
        // Search) shows pricing.
        $isPickingSlip = $request->routeIs('warehouse.sales.fulfillment.picking-slip');
        $hideAmounts = ($isPickingSlip
                || ($sale->fulfillment_method === 'transporter' && $sale->fulfillment_pickup_code))
            && ! $request->boolean('full');

        return view('receipt.print', compact('sale', 'groupedItems', 'hideAmounts'));
    }
}
