<?php

namespace App\Livewire\Inventory\Boxes;

use App\Enums\BoxStatus;
use App\Enums\LocationType;
use App\Models\Box;
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

    public function mount()
    {
        $user = auth()->user();

        if ($user->isWarehouseManager()) {
            $this->warehouseId = $user->location_id;
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

            $boxes[] = $box;
        }

        $this->createdBoxes = $boxes;

        session()->flash('success', "{$this->numberOfBoxes} boxes created successfully.");

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
