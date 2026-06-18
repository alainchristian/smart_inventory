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

        // Group items by product + unit price + sale type so qty is aggregated
        $groupedItems = $sale->items
            ->groupBy(fn ($i) => $i->product_id . '_' . $i->actual_unit_price . '_' . ($i->is_full_box ? 'b' : 'i'))
            ->map(function ($grp) {
                $first       = $grp->first();
                $isBox       = $first->is_full_box;
                $ipb         = max(1, $first->product->items_per_box ?? 1);
                $totalItems  = $grp->sum('quantity_sold');

                return [
                    'product_name'    => $first->product->name ?? '—',
                    'quantity'        => $isBox ? (int) round($totalItems / $ipb) : $totalItems,
                    'unit_price'      => $isBox ? $first->actual_unit_price * $ipb : $first->actual_unit_price,
                    'line_total'      => $grp->sum('line_total'),
                    'is_full_box'     => $isBox,
                    'price_modified'  => $grp->contains('price_was_modified', true),
                    'original_price'  => $isBox ? $first->original_unit_price * $ipb : $first->original_unit_price,
                    'source'          => $first->box?->location_type?->value ?? 'shop',
                ];
            })
            ->values();

        return view('receipt.print', compact('sale', 'groupedItems'));
    }
}
