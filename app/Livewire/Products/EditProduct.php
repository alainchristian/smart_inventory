<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;

class EditProduct extends Component
{
    public Product $product;

    // Identity
    public string  $name        = '';
    public string  $sku         = '';
    public string  $barcode     = '';
    public ?int    $categoryId  = null;
    public string  $description = '';

    // Packaging & pricing
    public int     $itemsPerBox      = 1;
    public string  $purchasePrice    = '';
    public string  $sellingPrice     = '';
    public string  $boxSellingPrice  = '';

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
            'sku'              => 'required|string|max:100|unique:products,sku,' . $this->product->id,
            'barcode'          => 'nullable|string|max:100|unique:products,barcode,' . $this->product->id,
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
        'name.required'       => 'Product name is required.',
        'sku.required'        => 'SKU is required.',
        'sku.unique'          => 'This SKU is already taken by another product.',
        'barcode.unique'      => 'This barcode is already registered to another product.',
        'categoryId.required' => 'Please select a category.',
        'purchasePrice.required' => 'Purchase price is required.',
        'sellingPrice.required'  => 'Selling price is required.',
    ];

    public function mount(Product $product): void
    {
        $this->product = $product;

        // Populate form fields — prices converted from cents to RWF
        $this->name             = $product->name;
        $this->sku              = $product->sku;
        $this->barcode          = $product->barcode ?? '';
        $this->categoryId       = $product->category_id;
        $this->description      = $product->description ?? '';
        $this->itemsPerBox      = $product->items_per_box;
        $this->purchasePrice    = $product->purchase_price > 0
                                    ? (string) ($product->purchase_price / 100)
                                    : '';
        $this->sellingPrice     = $product->selling_price > 0
                                    ? (string) ($product->selling_price / 100)
                                    : '';
        $this->boxSellingPrice  = $product->box_selling_price
                                    ? (string) ($product->box_selling_price / 100)
                                    : '';
        $this->lowStockThreshold = $product->low_stock_threshold;
        $this->reorderPoint      = $product->reorder_point;
        $this->unitOfMeasure     = $product->unit_of_measure ?? 'piece';
        $this->supplier          = $product->supplier ?? '';
        $this->isActive          = $product->is_active;
    }

    public function getMarginProperty(): ?float
    {
        $buy  = (float) $this->purchasePrice;
        $sell = (float) $this->sellingPrice;
        if ($sell <= 0 || $buy <= 0) return null;
        return round(($sell - $buy) / $sell * 100, 1);
    }

    public function getBoxPriceSuggestionProperty(): string
    {
        $sell = (float) $this->sellingPrice;
        $ipb  = (int)   $this->itemsPerBox;
        if ($sell <= 0 || $ipb <= 0) return '';
        return number_format($sell * $ipb, 0, '.', ',');
    }

    public function update(): void
    {
        if (! auth()->user()->isOwner()) {
            session()->flash('error', 'Only owners can edit products.');
            return;
        }

        $this->validate();

        $this->product->update([
            'category_id'        => $this->categoryId,
            'name'               => $this->name,
            'sku'                => strtoupper(trim($this->sku)),
            'barcode'            => $this->barcode ?: null,
            'description'        => $this->description ?: null,
            'items_per_box'      => $this->itemsPerBox,
            'purchase_price'     => (int) round((float) $this->purchasePrice * 100),
            'selling_price'      => (int) round((float) $this->sellingPrice  * 100),
            'box_selling_price'  => $this->boxSellingPrice !== ''
                                    ? (int) round((float) $this->boxSellingPrice * 100)
                                    : null,
            'low_stock_threshold'=> $this->lowStockThreshold,
            'reorder_point'      => $this->reorderPoint,
            'unit_of_measure'    => $this->unitOfMeasure,
            'supplier'           => $this->supplier ?: null,
            'is_active'          => $this->isActive,
        ]);

        session()->flash('success', "Product \"{$this->product->name}\" updated successfully.");
        $this->redirect(route('owner.products.index'), navigate: true);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.products.edit-product', [
            'categories' => Category::active()->orderBy('name')->get(),
        ]);
    }
}
