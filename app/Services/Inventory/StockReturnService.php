<?php

namespace App\Services\Inventory;

use App\Enums\BoxStatus;
use App\Enums\LocationType;
use App\Models\ActivityLog;
use App\Models\Box;
use App\Models\BoxMovement;
use App\Models\Shop;
use App\Models\StockReturn;
use App\Models\StockReturnBox;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Return to warehouse: a shop sends boxes back to its warehouse (e.g. stock
 * outside the categories it sells). send → in_transit → receive | cancel.
 *
 * While in transit a box has status IN_TRANSIT and stays at the shop
 * location, so every stock query (which filters full/partial) ignores it.
 */
class StockReturnService
{
    /**
     * @param array<int,int> $boxesPerProduct product_id => number of boxes to send.
     *                       Opened (partial) boxes go first, then sealed ones.
     */
    public function send(Shop $shop, array $boxesPerProduct, ?string $reason, User $user): StockReturn
    {
        if (! $user->isOwner() && ! ($user->isShopManager() && (int) $user->location_id === $shop->id)) {
            throw new \DomainException('Only this shop or the owner can send stock back.');
        }
        if (! $shop->default_warehouse_id) {
            throw new \DomainException("{$shop->name} has no default warehouse to return stock to.");
        }

        $boxesPerProduct = array_filter(array_map('intval', $boxesPerProduct), fn ($n) => $n > 0);
        if ($boxesPerProduct === []) {
            throw new \DomainException('Choose at least one box to send back.');
        }

        return DB::transaction(function () use ($shop, $boxesPerProduct, $reason, $user) {
            $return = StockReturn::create([
                'return_number' => $this->nextNumber(),
                'shop_id'       => $shop->id,
                'warehouse_id'  => $shop->default_warehouse_id,
                'status'        => StockReturn::IN_TRANSIT,
                'reason'        => $reason ?: null,
                'sent_by'       => $user->id,
                'sent_at'       => now(),
            ]);

            foreach ($boxesPerProduct as $productId => $count) {
                $boxes = Box::where('product_id', $productId)
                    ->where('location_type', LocationType::SHOP)
                    ->where('location_id', $shop->id)
                    ->whereIn('status', [BoxStatus::PARTIAL, BoxStatus::FULL])
                    ->where('items_remaining', '>', 0)
                    ->orderByRaw("CASE WHEN status = 'partial' THEN 0 ELSE 1 END")
                    ->orderBy('received_at')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->limit($count)
                    ->get();

                if ($boxes->count() < $count) {
                    $name = \App\Models\Product::whereKey($productId)->value('name') ?? "product #{$productId}";
                    throw new \DomainException("Only {$boxes->count()} box(es) of {$name} are available to send back.");
                }

                foreach ($boxes as $box) {
                    StockReturnBox::create([
                        'stock_return_id' => $return->id,
                        'box_id'          => $box->id,
                        'items_sent'      => $box->items_remaining,
                        'previous_status' => $box->status->value,
                    ]);

                    BoxMovement::create([
                        'box_id'             => $box->id,
                        'from_location_type' => LocationType::SHOP,
                        'from_location_id'   => $shop->id,
                        'to_location_type'   => LocationType::WAREHOUSE,
                        'to_location_id'     => $shop->default_warehouse_id,
                        'movement_type'      => 'return_sent',
                        'moved_by'           => $user->id,
                        'moved_at'           => now(),
                        'reference_type'     => 'stock_return',
                        'reference_id'       => $return->id,
                        'reason'             => "Return to warehouse sent: {$return->return_number}",
                        'items_moved'        => $box->items_remaining,
                    ]);

                    $box->update(['status' => BoxStatus::IN_TRANSIT]);
                }
            }

            $this->log('stock_return_sent', $return, $user, ['boxes' => $return->boxes()->count()]);

            return $return;
        });
    }

    /**
     * @param array<int,string> $outcomes box_id => received | damaged | missing
     *                          (boxes not listed count as received).
     */
    public function receive(StockReturn $return, array $outcomes, ?string $notes, User $user): StockReturn
    {
        if (! $user->isOwner() && ! ($user->isWarehouseManager() && (int) $user->location_id === (int) $return->warehouse_id)) {
            throw new \DomainException('Only the receiving warehouse or the owner can receive this return.');
        }
        if (! $return->isInTransit()) {
            throw new \DomainException("{$return->return_number} is already {$return->status}.");
        }

        return DB::transaction(function () use ($return, $outcomes, $notes, $user) {
            $discrepancy = false;

            foreach ($return->boxes()->with('box')->get() as $line) {
                $outcome = $outcomes[$line->box_id] ?? StockReturnBox::RECEIVED;
                if (! in_array($outcome, [StockReturnBox::RECEIVED, StockReturnBox::DAMAGED, StockReturnBox::MISSING], true)) {
                    $outcome = StockReturnBox::RECEIVED;
                }
                $box = $line->box;

                if ($outcome === StockReturnBox::MISSING) {
                    // Never arrived: out of stock everywhere, stays on the shop's record for follow-up
                    $box->update(['status' => BoxStatus::DAMAGED]);
                    $discrepancy = true;
                    $this->movement($box, $return, $user, 'return_missing', 'Missing on return', null);
                } else {
                    $this->movement($box, $return, $user, 'return_to_warehouse',
                        $outcome === StockReturnBox::DAMAGED ? 'Received damaged' : 'Received', $return->warehouse_id);

                    $box->location_type = LocationType::WAREHOUSE;
                    $box->location_id   = $return->warehouse_id;
                    $box->status        = $outcome === StockReturnBox::DAMAGED
                        ? BoxStatus::DAMAGED
                        : ($box->items_remaining >= $box->items_total ? BoxStatus::FULL : BoxStatus::PARTIAL);
                    $box->save();

                    $discrepancy = $discrepancy || $outcome === StockReturnBox::DAMAGED;
                }

                $line->update(['outcome' => $outcome]);
            }

            $return->update([
                'status'          => StockReturn::RECEIVED,
                'received_by'     => $user->id,
                'received_at'     => now(),
                'receipt_notes'   => $notes ?: null,
                'has_discrepancy' => $discrepancy,
            ]);

            $this->log('stock_return_received', $return, $user, ['has_discrepancy' => $discrepancy]);

            return $return;
        });
    }

    /** Called off before it reaches the warehouse: boxes go back on sale at the shop. */
    public function cancel(StockReturn $return, ?string $reason, User $user): StockReturn
    {
        if (! $user->isOwner() && ! ($user->isShopManager() && (int) $user->location_id === (int) $return->shop_id)) {
            throw new \DomainException('Only the sending shop or the owner can cancel this return.');
        }
        if (! $return->isInTransit()) {
            throw new \DomainException("{$return->return_number} is already {$return->status}.");
        }

        return DB::transaction(function () use ($return, $reason, $user) {
            foreach ($return->boxes()->with('box')->get() as $line) {
                $line->box->update(['status' => BoxStatus::from($line->previous_status)]);
                $this->movement($line->box, $return, $user, 'return_cancelled', 'Return cancelled — back on sale', null);
            }

            $return->update([
                'status'        => StockReturn::CANCELLED,
                'cancelled_by'  => $user->id,
                'cancelled_at'  => now(),
                'cancel_reason' => $reason ?: null,
            ]);

            $this->log('stock_return_cancelled', $return, $user, ['reason' => $reason]);

            return $return;
        });
    }

    private function movement(Box $box, StockReturn $return, User $user, string $type, string $what, ?int $toWarehouseId): void
    {
        BoxMovement::create([
            'box_id'             => $box->id,
            'from_location_type' => $box->location_type,
            'from_location_id'   => $box->location_id,
            'to_location_type'   => $toWarehouseId ? LocationType::WAREHOUSE : null,
            'to_location_id'     => $toWarehouseId,
            'movement_type'      => $type,
            'moved_by'           => $user->id,
            'moved_at'           => now(),
            'reference_type'     => 'stock_return',
            'reference_id'       => $return->id,
            'reason'             => "{$what}: {$return->return_number}",
            'items_moved'        => $box->items_remaining,
        ]);
    }

    private function nextNumber(): string
    {
        $last = StockReturn::lockForUpdate()->orderByDesc('id')->value('return_number');
        $n    = $last ? ((int) substr($last, 4)) + 1 : 1;

        return 'RTW-' . str_pad((string) $n, 6, '0', STR_PAD_LEFT);
    }

    private function log(string $action, StockReturn $return, User $user, array $details): void
    {
        ActivityLog::create([
            'user_id'           => $user->id,
            'user_name'         => $user->name,
            'action'            => $action,
            'entity_type'       => 'StockReturn',
            'entity_id'         => $return->id,
            'entity_identifier' => $return->return_number,
            'details'           => $details + [
                'shop_id'      => $return->shop_id,
                'warehouse_id' => $return->warehouse_id,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
    }
}
