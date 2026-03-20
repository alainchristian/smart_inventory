<?php

namespace App\Livewire\Inventory\Boxes;

use App\Models\Box;
use App\Models\Shop;
use App\Models\Warehouse;
use Livewire\Component;

class BoxShow extends Component
{
    public int $boxId;
    public Box $box;
    public array $locationNames = [];

    public function mount(int $boxId): void
    {
        if (!auth()->user()->isOwner() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $this->boxId = $boxId;
        $this->box = Box::with([
            'product.category',
            'location',
            'receivedBy',
            'movements.movedBy',
        ])->findOrFail($boxId);

        // Pre-resolve unique location names for movements
        $warehouseIds = [];
        $shopIds = [];

        foreach ($this->box->movements as $m) {
            if ($m->from_location_type?->value === 'warehouse' && $m->from_location_id) {
                $warehouseIds[] = $m->from_location_id;
            }
            if ($m->to_location_type?->value === 'warehouse' && $m->to_location_id) {
                $warehouseIds[] = $m->to_location_id;
            }
            if ($m->from_location_type?->value === 'shop' && $m->from_location_id) {
                $shopIds[] = $m->from_location_id;
            }
            if ($m->to_location_type?->value === 'shop' && $m->to_location_id) {
                $shopIds[] = $m->to_location_id;
            }
        }

        $warehouses = Warehouse::whereIn('id', array_unique($warehouseIds))->pluck('name', 'id');
        $shops = Shop::whereIn('id', array_unique($shopIds))->pluck('name', 'id');

        foreach ($warehouses as $id => $name) {
            $this->locationNames["warehouse_{$id}"] = $name;
        }
        foreach ($shops as $id => $name) {
            $this->locationNames["shop_{$id}"] = $name;
        }
    }

    public function render()
    {
        return view('livewire.inventory.boxes.box-show');
    }
}
