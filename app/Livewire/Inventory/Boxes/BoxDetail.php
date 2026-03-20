<?php

namespace App\Livewire\Inventory\Boxes;

use App\Models\Box;
use App\Models\BoxMovement;
use App\Models\Shop;
use App\Models\Transfer;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class BoxDetail extends Component
{
    public ?int $boxId = null;

    #[On('open-box-detail')]
    public function openFor(int $boxId): void
    {
        $this->boxId = $boxId;
    }

    public function close(): void
    {
        $this->boxId = null;
    }

    public function render()
    {
        if (!$this->boxId) {
            return view('livewire.inventory.boxes.box-detail', [
                'box'         => null,
                'movements'   => [],
                'transfers'   => [],
                'ageDays'     => null,
                'costValue'   => 0,
                'retailValue' => 0,
                'warehouses'  => collect(),
                'shops'       => collect(),
                'isOwner'     => false,
            ]);
        }

        $box = Box::with([
            'product.category',
            'location',
            'receivedBy',
        ])->findOrFail($this->boxId);

        $isOwner = auth()->user()->isOwner() || auth()->user()->isAdmin();

        // ── Location name lookups ──────────────────────────────────────────
        $warehouses = Warehouse::pluck('name', 'id');
        $shops      = Shop::pluck('name', 'id');

        // ── Movement timeline ──────────────────────────────────────────────
        $movements = BoxMovement::with('movedBy')
            ->where('box_id', $this->boxId)
            ->orderByDesc('moved_at')
            ->limit(20)
            ->get()
            ->map(fn ($m) => [
                'date'     => $m->moved_at ? $m->moved_at->format('d M Y, H:i') : '—',
                'relative' => $m->moved_at ? $m->moved_at->diffForHumans() : '—',
                'type'     => $m->movement_type,
                'from'     => $this->locationLabel($m->from_location_type?->value, $m->from_location_id, $warehouses, $shops),
                'to'       => $this->locationLabel($m->to_location_type?->value, $m->to_location_id, $warehouses, $shops),
                'items'    => $m->items_moved ?? 0,
                'moved_by' => $m->movedBy?->name ?? '—',
                'reason'   => $m->reason ?? '—',
            ])
            ->toArray();

        // ── Transfer history ───────────────────────────────────────────────
        $transfers = DB::table('transfer_boxes')
            ->join('transfers', 'transfer_boxes.transfer_id', '=', 'transfers.id')
            ->leftJoin('warehouses as wh', 'transfers.from_warehouse_id', '=', 'wh.id')
            ->leftJoin('shops as sh', 'transfers.to_shop_id', '=', 'sh.id')
            ->where('transfer_boxes.box_id', $this->boxId)
            ->selectRaw("
                transfers.id,
                transfers.transfer_number,
                transfers.status,
                transfers.has_discrepancy,
                transfers.shipped_at,
                transfers.received_at,
                transfer_boxes.scanned_out_at,
                transfer_boxes.scanned_in_at,
                transfer_boxes.is_received,
                transfer_boxes.is_damaged,
                wh.name as warehouse_name,
                sh.name as shop_name
            ")
            ->orderByDesc('transfers.created_at')
            ->get()
            ->toArray();

        // ── Financial (owner only) ─────────────────────────────────────────
        $costValue   = 0;
        $retailValue = 0;
        if ($isOwner && $box->product) {
            $costValue   = $box->items_remaining * $box->product->purchase_price;
            $retailValue = $box->items_remaining * $box->product->selling_price;
        }

        // ── Age ────────────────────────────────────────────────────────────
        $ageDays = $box->received_at
            ? (int) $box->received_at->diffInDays(now())
            : null;

        return view('livewire.inventory.boxes.box-detail', [
            'box'         => $box,
            'movements'   => $movements,
            'transfers'   => $transfers,
            'ageDays'     => $ageDays,
            'costValue'   => $costValue,
            'retailValue' => $retailValue,
            'warehouses'  => $warehouses,
            'shops'       => $shops,
            'isOwner'     => $isOwner,
        ]);
    }

    private function locationLabel(?string $type, ?int $id, $warehouses, $shops): string
    {
        if (!$type || !$id) {
            return '—';
        }

        return $type === 'warehouse'
            ? ($warehouses[$id] ?? 'Warehouse #' . $id)
            : ($shops[$id]      ?? 'Shop #'      . $id);
    }
}
