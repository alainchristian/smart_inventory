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

        $sale->load(['items.product', 'payments', 'soldBy', 'shop', 'customer']);

        // Group items by product + unit price + sale type so qty is aggregated
        $groupedItems = $sale->items
            ->groupBy(fn ($i) => $i->product_id . '_' . $i->actual_unit_price . '_' . ($i->is_full_box ? 'b' : 'i'))
            ->map(fn ($grp) => [
                'product_name'    => $grp->first()->product->name ?? '—',
                'quantity'        => $grp->sum('quantity_sold'),
                'unit_price'      => $grp->first()->actual_unit_price,
                'line_total'      => $grp->sum('line_total'),
                'is_full_box'     => $grp->first()->is_full_box,
                'price_modified'  => $grp->contains('price_was_modified', true),
                'original_price'  => $grp->first()->original_unit_price,
            ])
            ->values();

        return view('receipt.print', compact('sale', 'groupedItems'));
    }
}
