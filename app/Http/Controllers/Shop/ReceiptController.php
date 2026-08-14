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

        if (! $user->isOwner() && $user->location_id !== $sale->shop_id) {
            abort(403);
        }

        $sale->load(['items.product', 'items.box', 'payments', 'soldBy', 'shop', 'customer']);

        $groupedItems = $sale->groupedItems();

        // The document that physically travels to the warehouse with a
        // transporter must not disclose the sale amount — only the customer's
        // own pickup receipt (or an explicit ?full=1 reprint, e.g. from Sales
        // History / Reprint Search) shows pricing for a transporter dispatch.
        $hideAmounts = $sale->fulfillment_method === 'transporter'
            && $sale->fulfillment_pickup_code
            && ! $request->boolean('full');

        return view('receipt.print', compact('sale', 'groupedItems', 'hideAmounts'));
    }
}
