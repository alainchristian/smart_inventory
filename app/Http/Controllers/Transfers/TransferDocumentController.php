<?php

namespace App\Http\Controllers\Transfers;

use App\Http\Controllers\Controller;
use App\Models\Box;
use App\Models\Transfer;
use App\Services\Inventory\TransferService;

/**
 * Printable transfer documents. Only people who take part in the transfer
 * (TransferService::roleFor: owner, its warehouse, its shop) may open them.
 */
class TransferDocumentController extends Controller
{
    public function deliveryNote(Transfer $transfer)
    {
        abort_unless(app(TransferService::class)->roleFor(auth()->user(), $transfer), 403);

        return view('transfers.delivery-note', compact('transfer'));
    }

    /** What to pick: approved boxes per product, oldest boxes first (the order packing takes them). */
    public function pickingList(Transfer $transfer)
    {
        $role = app(TransferService::class)->roleFor(auth()->user(), $transfer);
        abort_unless(in_array($role, ['owner', 'warehouse'], true), 403);

        $transfer->loadMissing(['items.product', 'fromWarehouse', 'toShop', 'reviewedBy', 'boxes.box']);
        $packed = $transfer->boxes->groupBy(fn ($tb) => $tb->box?->product_id);

        $lines = $transfer->items->map(function ($item) use ($transfer, $packed) {
            $done = ($packed[$item->product_id] ?? collect())->count();
            $toPick = max(0, $item->boxesToSend() - $done);
            // Same choice as TransferService::packBoxesByProductBarcode: available boxes, oldest first.
            $suggested = $toPick === 0 ? collect() : Box::where('product_id', $item->product_id)
                ->where('location_type', 'warehouse')->where('location_id', $transfer->from_warehouse_id)
                ->whereIn('status', ['full', 'partial'])->where('items_remaining', '>', 0)
                ->orderBy('id')->limit($toPick)->pluck('box_code');

            return ['item' => $item, 'product' => $item->product, 'approved' => $item->boxesToSend(),
                'packed' => $done, 'to_pick' => $toPick, 'suggested' => $suggested];
        });

        return view('transfers.picking-list', compact('transfer', 'lines'));
    }
}
