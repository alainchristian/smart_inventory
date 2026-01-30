<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;

class CreateProduct extends Component
{
    public string $name = '';
    public string $sku = '';
    public ?string $barcode = null;
    public ?int $categoryId = null;
    public ?string $description = null;
    public int $itemsPerBox = 1;
    public float $purchasePrice = 0;
    public float $sellingPrice = 0;
    public ?float $boxSellingPrice = null;
    public int $lowStockThreshold = 10;
    public int $reorderPoint = 20;
    public string $unitOfMeasure = 'piece';
    public ?float $weightPerItem = null;
    public ?string $supplier = null;
    public bool $isActive = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'sku' => 'required|string|max:100|unique:products,sku',
        'barcode' => 'nullable|string|max:100|unique:products,barcode',
        'categoryId' => 'required|exists:categories,id',
        'description' => 'nullable|string|max:1000',
        'itemsPerBox' => 'required|integer|min:1',
        'purchasePrice' => 'required|numeric|min:0',
        'sellingPrice' => 'required|numeric|min:0',
        'boxSellingPrice' => 'nullable|numeric|min:0',
        'lowStockThreshold' => 'required|integer|min:0',
        'reorderPoint' => 'required|integer|min:0',
        'unitOfMeasure' => 'required|string|max:50',
        'weightPerItem' => 'nullable|numeric|min:0',
        'supplier' => 'nullable|string|max:255',
        'isActive' => 'boolean',
    ];

    protected $messages = [
        'name.required' => 'Product name is required.',
        'sku.required' => 'SKU is required.',
        'sku.unique' => 'This SKU already exists.',
        'barcode.unique' => 'This barcode already exists.',
        'categoryId.required' => 'Please select a category.',
        'itemsPerBox.min' => 'Items per box must be at least 1.',
        'purchasePrice.required' => 'Purchase price is required.',
        'sellingPrice.required' => 'Selling price is required.',
        'sellingPrice.min' => 'Selling price must be greater than or equal to 0.',
    ];

    public function mount()
    {
        // Generate SKU if empty
        if (empty($this->sku)) {
            $this->generateSku();
        }
    }

    public function generateSku()
    {
        // Generate SKU format: PROD-YYYYMMDD-XXX
        $date = now()->format('Ymd');
        $count = Product::whereDate('created_at', today())->count() + 1;
        $this->sku = "PROD-{$date}-" . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    public function updatedName()
    {
        // Auto-generate SKU when name changes (if SKU is empty)
        if (empty($this->sku)) {
            $this->generateSku();
        }
    }

    public function updatedSellingPrice()
    {
        // Validate selling price is greater than purchase price
        if ($this->purchasePrice > 0 && $this->sellingPrice < $this->purchasePrice) {
            $this->addError('sellingPrice', 'Selling price should be greater than purchase price.');
        }
    }

    public function calculateBoxPrice()
    {
        if ($this->sellingPrice > 0 && $this->itemsPerBox > 0) {
            $this->boxSellingPrice = $this->sellingPrice * $this->itemsPerBox;
        }
    }

    public function save()
    {
        $this->authorize('create', Product::class);

        $this->validate();

        // Additional validation: selling price should be greater than purchase price
        if ($this->sellingPrice < $this->purchasePrice) {
            $this->addError('sellingPrice', 'Selling price should be greater than purchase price.');
            return;
        }

        $product = Product::create([
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'category_id' => $this->categoryId,
            'description' => $this->description,
            'items_per_box' => $this->itemsPerBox,
            'purchase_price' => round($this->purchasePrice * 100),
            'selling_price' => round($this->sellingPrice * 100),
            'box_selling_price' => $this->boxSellingPrice ? round($this->boxSellingPrice * 100) : null,
            'low_stock_threshold' => $this->lowStockThreshold,
            'reorder_point' => $this->reorderPoint,
            'unit_of_measure' => $this->unitOfMeasure,
            'weight_per_item' => $this->weightPerItem,
            'supplier' => $this->supplier,
            'is_active' => $this->isActive,
        ]);

        session()->flash('success', 'Product created successfully.');

        return redirect()->route('products.index');
    }

    public function resetForm()
    {
        $this->reset();
        $this->generateSku();
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.products.create-product', [
            'categories' => Category::active()->orderBy('name')->get(),
            'unitOptions' => [
                'piece' => 'Piece',
                'kg' => 'Kilogram',
                'liter' => 'Liter',
                'meter' => 'Meter',
                'box' => 'Box',
                'pack' => 'Pack',
                'dozen' => 'Dozen',
            ],
        ]);
    }
}
