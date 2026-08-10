<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Sale;

class ReceiptController extends Controller
{
    public function print(Sale $sale)
    {
        $user = auth()->user();

        if (! $user->isOwner() && $user->location_id !== $sale->shop_id) {
            abort(403);
        }

        $sale->load(['items.product', 'items.box', 'payments', 'soldBy', 'shop', 'customer']);

        $groupedItems = $sale->groupedItems();

        return view('receipt.print', compact('sale', 'groupedItems'));
    }
}
