<?php

namespace App\Livewire\Inventory\Boxes;

use App\Enums\BoxStatus;
use App\Enums\LocationType;
use App\Models\ActivityLog;
use App\Models\Box;
use App\Models\BoxMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\Inventory\BarcodeService;
use Livewire\Component;

class ReceiveBoxes extends Component
{
    public ?int $warehouseId = null;
    public ?int $productId = null;
    public int $numberOfBoxes = 1;
    public ?string $batchNumber = null;
    public ?string $expiryDate = null;

    public array $createdBoxes = [];

    protected $rules = [
        'warehouseId' => 'required|exists:warehouses,id',
        'productId' => 'required|exists:products,id',
        'numberOfBoxes' => 'required|integer|min:1|max:100',
        'batchNumber' => 'nullable|string|max:50',
        'expiryDate' => 'nullable|date|after:today',
    ];

    protected $messages = [
        'warehouseId.required' => 'Please select a warehouse.',
        'productId.required' => 'Please select a product.',
        'numberOfBoxes.min' => 'You must create at least 1 box.',
        'numberOfBoxes.max' => 'You can create a maximum of 100 boxes at once.',
        'expiryDate.after' => 'Expiry date must be in the future.',
    ];

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->isWarehouseManager()) {
            $this->warehouseId = $user->location_id;
        }

        if (request()->has('product_id')) {
            $this->productId = (int) request()->get('product_id');
        }
    }

    public function createBoxes()
    {
        $this->authorize('create', Box::class);

        $this->validate();

        $product = Product::findOrFail($this->productId);
        $barcodeService = app(BarcodeService::class);

        $boxes = [];

        for ($i = 0; $i < $this->numberOfBoxes; $i++) {
            $box = Box::create([
                'product_id' => $this->productId,
                'box_code' => $barcodeService->generateBoxCode(),
                'status' => BoxStatus::FULL,
                'items_total' => $product->items_per_box,
                'items_remaining' => $product->items_per_box,
                'location_type' => LocationType::WAREHOUSE,
                'location_id' => $this->warehouseId,
                'received_by' => auth()->id(),
                'received_at' => now(),
                'batch_number' => $this->batchNumber,
                'expiry_date' => $this->expiryDate,
            ]);

            BoxMovement::create([
                'box_id'             => $box->id,
                'from_location_type' => null,
                'from_location_id'   => null,
                'to_location_type'   => 'warehouse',
                'to_location_id'     => $this->warehouseId,
                'movement_type'      => 'received',
                'moved_by'           => auth()->id(),
                'moved_at'           => now(),
                'items_moved'        => $product->items_per_box,
            ]);

            $boxes[] = $box;
        }

        ActivityLog::create([
            'user_id'           => auth()->id(),
            'user_name'         => auth()->user()->name,
            'action'            => 'stock_intake',
            'entity_type'       => 'Box',
            'entity_id'         => $boxes[0]->id ?? null,
            'entity_identifier' => $product->name,
            'details'           => [
                'product_id'    => $product->id,
                'warehouse_id'  => $this->warehouseId,
                'boxes_created' => $this->numberOfBoxes,
                'batch_number'  => $this->batchNumber,
            ],
        ]);

        $this->createdBoxes = $boxes;

        session()->flash('success', "{$this->numberOfBoxes} box(es) of {$product->name} added to warehouse.");

        $this->dispatch('boxes-created', count: $this->numberOfBoxes);
    }

    public function printLabels()
    {
        $barcodeService = app(BarcodeService::class);
        $boxIds = collect($this->createdBoxes)->pluck('id')->toArray();

        $pdf = $barcodeService->bulkPrintLabels($boxIds);

        return response()->streamDownload(
            fn () => print($pdf),
            'box-labels-' . now()->format('Y-m-d-His') . '.pdf'
        );
    }

    public function resetForm(): void
    {
        $this->productId = null;
        $this->numberOfBoxes = 1;
        $this->batchNumber = null;
        $this->expiryDate = null;
        $this->createdBoxes = [];
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.inventory.boxes.receive-boxes', [
            'warehouses' => Warehouse::active()->orderBy('name')->get(),
            'products' => Product::active()->with('category')->orderBy('name')->get(),
        ]);
    }
}
