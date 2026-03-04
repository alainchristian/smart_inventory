<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;

class CreateProduct extends Component
{
    // Identity
    public string  $name        = '';
    public string  $sku         = '';
    public string  $barcode     = '';
    public ?int    $categoryId  = null;
    public string  $description = '';

    // Packaging & pricing (user enters RWF, stored in cents)
    public int     $itemsPerBox       = 1;
    public string  $purchasePrice     = '';   // user input string, convert on save
    public string  $sellingPrice      = '';
    public string  $boxSellingPrice   = '';

    // Operational
    public int     $lowStockThreshold = 10;
    public int     $reorderPoint      = 20;
    public string  $unitOfMeasure     = 'piece';
    public string  $supplier          = '';
    public bool    $isActive          = true;

    protected function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'sku'              => 'required|string|max:100|unique:products,sku',
            'barcode'          => 'nullable|string|max:100|unique:products,barcode',
            'categoryId'       => 'required|exists:categories,id',
            'description'      => 'nullable|string|max:1000',
            'itemsPerBox'      => 'required|integer|min:1|max:10000',
            'purchasePrice'    => 'required|numeric|min:0',
            'sellingPrice'     => 'required|numeric|min:0',
            'boxSellingPrice'  => 'nullable|numeric|min:0',
            'lowStockThreshold'=> 'required|integer|min:0',
            'reorderPoint'     => 'required|integer|min:0',
            'unitOfMeasure'    => 'required|string|max:50',
            'supplier'         => 'nullable|string|max:255',
            'isActive'         => 'boolean',
        ];
    }

    protected $messages = [
        'name.required'      => 'Product name is required.',
        'sku.required'       => 'SKU is required.',
        'sku.unique'         => 'This SKU is already taken.',
        'barcode.unique'     => 'This barcode is already registered.',
        'categoryId.required'=> 'Please select a category.',
        'itemsPerBox.min'    => 'Must be at least 1 item per box.',
        'purchasePrice.required' => 'Purchase price is required.',
        'sellingPrice.required'  => 'Selling price is required.',
    ];

    public function mount(): void
    {
        if (request()->has('barcode')) {
            $this->barcode = request()->get('barcode');
        }
        $this->generateSku();
    }

    public function generateSku(): void
    {
        $count = str_pad(Product::count() + 1, 3, '0', STR_PAD_LEFT);
        $this->sku = 'PROD-' . strtoupper(substr(uniqid(), -4)) . '-' . $count;
    }

    public function updatedName(): void
    {
        // Auto-suggest SKU from name only if still on generated value
        if (str_starts_with($this->sku, 'PROD-')) {
            $words = array_slice(explode(' ', strtoupper($this->name)), 0, 3);
            $prefix = implode('-', array_map(fn($w) => substr($w, 0, 3), $words));
            $count  = str_pad(Product::count() + 1, 3, '0', STR_PAD_LEFT);
            $this->sku = $prefix . '-' . $count;
        }
    }

    // Live margin preview
    public function getMarginProperty(): ?float
    {
        $buy  = (float) $this->purchasePrice;
        $sell = (float) $this->sellingPrice;
        if ($sell <= 0 || $buy <= 0) return null;
        return round(($sell - $buy) / $sell * 100, 1);
    }

    // Live box price suggestion
    public function getBoxPriceSuggestionProperty(): string
    {
        $sell = (float) $this->sellingPrice;
        $ipb  = (int)   $this->itemsPerBox;
        if ($sell <= 0 || $ipb <= 0) return '';
        return number_format($sell * $ipb, 0, '.', ',');
    }

    public function save(): void
    {
        if (! auth()->user()->isOwner()) {
            session()->flash('error', 'Only owners can create products.');
            return;
        }

        $this->validate();

        $product = Product::create([
            'category_id'        => $this->categoryId,
            'name'               => $this->name,
            'sku'                => strtoupper(trim($this->sku)),
            'barcode'            => $this->barcode ?: null,
            'description'        => $this->description ?: null,
            'items_per_box'      => $this->itemsPerBox,
            'purchase_price'     => (int) $this->purchasePrice,
            'selling_price'      => (int) $this->sellingPrice,
            'box_selling_price'  => $this->boxSellingPrice !== ''
                                    ? (int) $this->boxSellingPrice
                                    : null,
            'low_stock_threshold'=> $this->lowStockThreshold,
            'reorder_point'      => $this->reorderPoint,
            'unit_of_measure'    => $this->unitOfMeasure,
            'supplier'           => $this->supplier ?: null,
            'is_active'          => $this->isActive,
        ]);

        session()->flash('success', "Product \"{$product->name}\" created successfully.");
        $this->redirect(route('owner.products.index'), navigate: true);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.products.create-product', [
            'categories' => Category::active()->orderBy('name')->get(),
        ]);
    }
}
