# SmartInventory — Product Create & Edit Pages
## Claude Code Instructions

> Drop in project root, run:
> "Read PRODUCT_FORMS.md and implement every step in order."

---

## Ground Rules
- Read every target file before editing — understand existing structure first
- Prices are stored in **cents** (integer). Display = value / 100. Save = input * 100
- Design system uses CSS variables: `--accent`, `--surface`, `--surface2`, `--border`, `--text`, `--text-sub`, `--text-dim`, `--green`, `--red`, `--amber`, `--violet`, `--pink`, `--r`, `--rx`, `--rsm`, `--font`, `--mono`
- Use `x-app-layout` wrapper (same as products index page)
- No Tailwind utility classes for layout — use inline styles with CSS variables
- Run `php artisan view:clear && php artisan cache:clear` after all changes

---

## Step 0 — Pre-Flight

```bash
# Check existing files
ls app/Livewire/Products/
ls resources/views/owner/products/
ls resources/views/livewire/products/
cat app/Livewire/Products/CreateProduct.php
grep -n "products" routes/web.php
cat app/Policies/ProductPolicy.php
grep -n "Gate::" app/Providers/AuthServiceProvider.php
```

---

## Step 1 — Fix Routes

**Target:** `routes/web.php`

Find the owner products route block and replace it completely:

```php
// Products (owner can manage all)
Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', function () {
        return view('owner.products.index');
    })->name('index');

    Route::get('/create', function () {
        return view('owner.products.create');
    })->name('create');

    Route::get('/{product}/edit', function (\App\Models\Product $product) {
        return view('owner.products.edit', compact('product'));
    })->name('edit');
});
```

---

## Step 2 — Fix ProductPolicy

**Target:** `app/Policies/ProductPolicy.php`

Replace entire file:

```php
<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // all authenticated users can view products
    }

    public function view(User $user, Product $product): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isOwner();
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->isOwner();
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->isOwner();
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return $user->isOwner();
    }

    public function viewPurchasePrice(User $user): bool
    {
        return $user->isOwner();
    }
}
```

---

## Step 3 — Fix / Rewrite CreateProduct Livewire Component

**Target:** `app/Livewire/Products/CreateProduct.php`

Read the existing file. Replace entirely with this clean version that correctly handles cent conversion and SKU auto-generation:

```php
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
```

---

## Step 4 — Create EditProduct Livewire Component

**Target:** `app/Livewire/Products/EditProduct.php` (create new file)

```php
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
```

---

## Step 5 — Create Shared Form Partial

**Target:** `resources/views/livewire/products/_form.blade.php` (create new file)

This partial is included by both create and edit blades. `$mode` is either `'create'` or `'edit'`. `$component` references the Livewire component (`$this`).

```blade
{{--
  Shared product form partial
  Variables available: $categories, $mode ('create'|'edit')
  Livewire properties accessed via wire:model
--}}

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:flex-start"
     class="product-form-grid">

  {{-- ═══ LEFT: Main fields ═══ --}}
  <div style="display:flex;flex-direction:column;gap:16px">

    {{-- Card: Identity --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:22px 24px">
      <div style="font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
                  color:var(--text-sub);margin-bottom:16px;padding-bottom:12px;
                  border-bottom:1px solid var(--border)">
        Product Identity
      </div>

      {{-- Name --}}
      <div style="margin-bottom:14px">
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
          Product Name <span style="color:var(--red)">*</span>
        </label>
        <input wire:model.live="name" type="text" placeholder="e.g. Coca Cola 500ml"
               style="width:100%;padding:9px 12px;border:1px solid var(--border);
                      border-radius:var(--rx);font-size:14px;background:var(--surface);
                      color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font)"
               onfocus="this.style.borderColor='var(--accent)'"
               onblur="this.style.borderColor='var(--border)'">
        @error('name')
          <div style="color:var(--red);font-size:11px;margin-top:4px">{{ $message }}</div>
        @enderror
      </div>

      {{-- SKU + Barcode row --}}
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            SKU <span style="color:var(--red)">*</span>
          </label>
          <input wire:model="sku" type="text" placeholder="PROD-001"
                 style="width:100%;padding:9px 12px;border:1px solid var(--border);
                        border-radius:var(--rx);font-size:13px;background:var(--surface);
                        color:var(--text);outline:none;box-sizing:border-box;
                        font-family:var(--mono);text-transform:uppercase"
                 onfocus="this.style.borderColor='var(--accent)'"
                 onblur="this.style.borderColor='var(--border)'">
          @error('sku')
            <div style="color:var(--red);font-size:11px;margin-top:4px">{{ $message }}</div>
          @enderror
        </div>
        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Barcode
          </label>
          <input wire:model="barcode" type="text" placeholder="8801234567890"
                 style="width:100%;padding:9px 12px;border:1px solid var(--border);
                        border-radius:var(--rx);font-size:13px;background:var(--surface);
                        color:var(--text);outline:none;box-sizing:border-box;font-family:var(--mono)"
                 onfocus="this.style.borderColor='var(--accent)'"
                 onblur="this.style.borderColor='var(--border)'">
          @error('barcode')
            <div style="color:var(--red);font-size:11px;margin-top:4px">{{ $message }}</div>
          @enderror
        </div>
      </div>

      {{-- Category --}}
      <div style="margin-bottom:14px">
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
          Category <span style="color:var(--red)">*</span>
        </label>
        <select wire:model="categoryId"
                style="width:100%;padding:9px 12px;border:1px solid var(--border);
                       border-radius:var(--rx);font-size:13px;background:var(--surface);
                       color:var(--text);outline:none;cursor:pointer;box-sizing:border-box">
          <option value="">Select a category...</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
          @endforeach
        </select>
        @error('categoryId')
          <div style="color:var(--red);font-size:11px;margin-top:4px">{{ $message }}</div>
        @enderror
      </div>

      {{-- Description --}}
      <div>
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
          Description
        </label>
        <textarea wire:model="description" rows="3"
                  placeholder="Optional product description..."
                  style="width:100%;padding:9px 12px;border:1px solid var(--border);
                         border-radius:var(--rx);font-size:13px;background:var(--surface);
                         color:var(--text);outline:none;resize:vertical;
                         box-sizing:border-box;font-family:var(--font)"
                  onfocus="this.style.borderColor='var(--accent)'"
                  onblur="this.style.borderColor='var(--border)'"></textarea>
      </div>
    </div>

    {{-- Card: Pricing --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:22px 24px">
      <div style="font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
                  color:var(--text-sub);margin-bottom:16px;padding-bottom:12px;
                  border-bottom:1px solid var(--border);display:flex;align-items:center;
                  justify-content:space-between">
        <span>Pricing <span style="color:var(--text-dim);font-size:10px">(in RWF)</span></span>
        {{-- Live margin badge --}}
        @if($this->margin !== null)
          <span style="font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;
                       background:{{ $this->margin >= 20 ? 'var(--green-dim)' : ($this->margin >= 10 ? 'var(--accent-dim)' : 'var(--red-dim)') }};
                       color:{{ $this->margin >= 20 ? 'var(--green)' : ($this->margin >= 10 ? 'var(--accent)' : 'var(--red)') }}">
            {{ $this->margin }}% margin
          </span>
        @endif
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">

        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Purchase Price <span style="color:var(--red)">*</span>
          </label>
          <div style="position:relative">
            <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);
                         font-size:11px;color:var(--text-dim);font-weight:600">RWF</span>
            <input wire:model.live="purchasePrice" type="number" min="0" step="100"
                   placeholder="0"
                   style="width:100%;padding:9px 12px 9px 40px;border:1px solid var(--border);
                          border-radius:var(--rx);font-size:13px;background:var(--surface);
                          color:var(--text);outline:none;box-sizing:border-box;font-family:var(--mono)"
                   onfocus="this.style.borderColor='var(--violet)'"
                   onblur="this.style.borderColor='var(--border)'">
          </div>
          @error('purchasePrice')
            <div style="color:var(--red);font-size:11px;margin-top:4px">{{ $message }}</div>
          @enderror
          <div style="font-size:10px;color:var(--text-dim);margin-top:3px">Owner-only visible</div>
        </div>

        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Selling Price / Item <span style="color:var(--red)">*</span>
          </label>
          <div style="position:relative">
            <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);
                         font-size:11px;color:var(--text-dim);font-weight:600">RWF</span>
            <input wire:model.live="sellingPrice" type="number" min="0" step="100"
                   placeholder="0"
                   style="width:100%;padding:9px 12px 9px 40px;border:1px solid var(--border);
                          border-radius:var(--rx);font-size:13px;background:var(--surface);
                          color:var(--text);outline:none;box-sizing:border-box;font-family:var(--mono)"
                   onfocus="this.style.borderColor='var(--accent)'"
                   onblur="this.style.borderColor='var(--border)'">
          </div>
          @error('sellingPrice')
            <div style="color:var(--red);font-size:11px;margin-top:4px">{{ $message }}</div>
          @enderror
        </div>

        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Box Selling Price
            <span style="font-size:10px;font-weight:400;color:var(--text-dim)">(optional)</span>
          </label>
          <div style="position:relative">
            <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);
                         font-size:11px;color:var(--text-dim);font-weight:600">RWF</span>
            <input wire:model.live="boxSellingPrice" type="number" min="0" step="100"
                   placeholder="{{ $this->boxPriceSuggestion ?: '0' }}"
                   style="width:100%;padding:9px 12px 9px 40px;border:1px solid var(--border);
                          border-radius:var(--rx);font-size:13px;background:var(--surface);
                          color:var(--text);outline:none;box-sizing:border-box;font-family:var(--mono)"
                   onfocus="this.style.borderColor='var(--accent)'"
                   onblur="this.style.borderColor='var(--border)'">
          </div>
          @if($this->boxPriceSuggestion)
            <div style="font-size:10px;color:var(--text-dim);margin-top:3px">
              Suggested: RWF {{ $this->boxPriceSuggestion }}
              ({{ $itemsPerBox }} x {{ $sellingPrice }})
            </div>
          @endif
        </div>

      </div>
    </div>

    {{-- Card: Packaging & Operational --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:22px 24px">
      <div style="font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
                  color:var(--text-sub);margin-bottom:16px;padding-bottom:12px;
                  border-bottom:1px solid var(--border)">
        Packaging &amp; Operations
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px">

        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Items per Box <span style="color:var(--red)">*</span>
          </label>
          <input wire:model.live="itemsPerBox" type="number" min="1"
                 style="width:100%;padding:9px 12px;border:1px solid var(--border);
                        border-radius:var(--rx);font-size:13px;background:var(--surface);
                        color:var(--text);outline:none;box-sizing:border-box;font-family:var(--mono)"
                 onfocus="this.style.borderColor='var(--accent)'"
                 onblur="this.style.borderColor='var(--border)'">
          @error('itemsPerBox')
            <div style="color:var(--red);font-size:11px;margin-top:4px">{{ $message }}</div>
          @enderror
        </div>

        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Low Stock Alert At
          </label>
          <input wire:model="lowStockThreshold" type="number" min="0"
                 style="width:100%;padding:9px 12px;border:1px solid var(--border);
                        border-radius:var(--rx);font-size:13px;background:var(--surface);
                        color:var(--text);outline:none;box-sizing:border-box;font-family:var(--mono)"
                 onfocus="this.style.borderColor='var(--amber)'"
                 onblur="this.style.borderColor='var(--border)'">
          <div style="font-size:10px;color:var(--text-dim);margin-top:3px">items remaining</div>
        </div>

        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Reorder Point
          </label>
          <input wire:model="reorderPoint" type="number" min="0"
                 style="width:100%;padding:9px 12px;border:1px solid var(--border);
                        border-radius:var(--rx);font-size:13px;background:var(--surface);
                        color:var(--text);outline:none;box-sizing:border-box;font-family:var(--mono)"
                 onfocus="this.style.borderColor='var(--accent)'"
                 onblur="this.style.borderColor='var(--border)'">
        </div>

      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">

        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Unit of Measure
          </label>
          <select wire:model="unitOfMeasure"
                  style="width:100%;padding:9px 12px;border:1px solid var(--border);
                         border-radius:var(--rx);font-size:13px;background:var(--surface);
                         color:var(--text);outline:none;cursor:pointer;box-sizing:border-box">
            <option value="piece">Piece</option>
            <option value="pair">Pair</option>
            <option value="kg">Kilogram (kg)</option>
            <option value="g">Gram (g)</option>
            <option value="litre">Litre</option>
            <option value="ml">Millilitre (ml)</option>
            <option value="box">Box</option>
            <option value="carton">Carton</option>
            <option value="set">Set</option>
            <option value="roll">Roll</option>
          </select>
        </div>

        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Supplier
          </label>
          <input wire:model="supplier" type="text" placeholder="e.g. Rwanda Imports Ltd"
                 style="width:100%;padding:9px 12px;border:1px solid var(--border);
                        border-radius:var(--rx);font-size:13px;background:var(--surface);
                        color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font)"
                 onfocus="this.style.borderColor='var(--accent)'"
                 onblur="this.style.borderColor='var(--border)'">
        </div>

      </div>
    </div>

  </div>{{-- /left --}}

  {{-- ═══ RIGHT: Summary sidebar ═══ --}}
  <div style="display:flex;flex-direction:column;gap:16px;position:sticky;top:84px">

    {{-- Status card --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:20px 22px">
      <div style="font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
                  color:var(--text-sub);margin-bottom:14px">Status</div>

      <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
        <div style="position:relative;width:40px;height:22px;flex-shrink:0">
          <input wire:model.live="isActive" type="checkbox" style="opacity:0;position:absolute;inset:0;cursor:pointer;z-index:1">
          <div style="position:absolute;inset:0;border-radius:11px;transition:.2s;
                      background:{{ $isActive ? 'var(--green)' : 'var(--border-hi)' }}"></div>
          <div style="position:absolute;top:3px;left:{{ $isActive ? '21px' : '3px' }};
                      width:16px;height:16px;border-radius:50%;background:#fff;
                      transition:.2s;box-shadow:0 1px 3px rgba(0,0,0,.2)"></div>
        </div>
        <div>
          <div style="font-size:13px;font-weight:600;
                      color:{{ $isActive ? 'var(--green)' : 'var(--text-sub)' }}">
            {{ $isActive ? 'Active' : 'Inactive' }}
          </div>
          <div style="font-size:11px;color:var(--text-dim)">
            {{ $isActive ? 'Visible in POS and transfers' : 'Hidden from operations' }}
          </div>
        </div>
      </label>
    </div>

    {{-- Live preview card --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:20px 22px">
      <div style="font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
                  color:var(--text-sub);margin-bottom:14px">Preview</div>

      <div style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:4px;min-height:22px">
        {{ $name ?: 'Product name' }}
      </div>
      <div style="font-family:var(--mono);font-size:11px;color:var(--text-dim);margin-bottom:12px">
        {{ $sku ?: 'SKU-000' }}
      </div>

      @if($sellingPrice)
        <div style="font-size:18px;font-weight:700;color:var(--accent);font-family:var(--mono);margin-bottom:4px">
          {{ number_format((float)$sellingPrice) }} RWF
        </div>
        <div style="font-size:11px;color:var(--text-dim)">per item</div>
      @endif

      @if($itemsPerBox > 0 && $sellingPrice)
        <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
          <div style="font-size:11px;color:var(--text-sub)">Box of {{ $itemsPerBox }}</div>
          <div style="font-size:14px;font-weight:700;color:var(--text);font-family:var(--mono)">
            @if($boxSellingPrice)
              {{ number_format((float)$boxSellingPrice) }} RWF
            @else
              ~{{ number_format((float)$sellingPrice * $itemsPerBox) }} RWF
            @endif
          </div>
        </div>
      @endif

      @if($this->margin !== null)
        <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border);
                    display:flex;align-items:center;justify-content:space-between">
          <span style="font-size:11px;color:var(--text-sub)">Gross margin</span>
          <span style="font-size:13px;font-weight:700;
                       color:{{ $this->margin >= 20 ? 'var(--green)' : ($this->margin >= 10 ? 'var(--accent)' : 'var(--red)') }}">
            {{ $this->margin }}%
          </span>
        </div>
      @endif
    </div>

    {{-- Action buttons --}}
    <div style="display:flex;flex-direction:column;gap:8px">
      <button
        @if($mode === 'create') wire:click="save" @else wire:click="update" @endif
        wire:loading.attr="disabled"
        style="padding:11px 20px;background:var(--accent);color:#fff;border:none;
               border-radius:var(--rx);font-size:14px;font-weight:700;cursor:pointer;
               width:100%;font-family:var(--font);display:flex;align-items:center;
               justify-content:center;gap:8px">
        <span wire:loading.remove>
          @if($mode === 'create')
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display:inline">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Create Product
          @else
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display:inline">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
              <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
            </svg>
            Save Changes
          @endif
        </span>
        <span wire:loading style="font-size:13px">Saving...</span>
      </button>

      <a href="{{ route('owner.products.index') }}"
         style="padding:10px 20px;background:var(--surface2);color:var(--text-sub);
                border:1px solid var(--border);border-radius:var(--rx);font-size:13px;
                font-weight:600;text-decoration:none;text-align:center;display:block">
        Cancel
      </a>
    </div>

  </div>{{-- /right --}}

</div>

{{-- Responsive: stack on smaller screens --}}
<style>
@media (max-width: 900px) {
  .product-form-grid {
    grid-template-columns: 1fr !important;
  }
}
</style>
```

---

## Step 6 — Create Product Create Blade View

**Target:** `resources/views/livewire/products/create-product.blade.php` (create new file)

```blade
<div>
  {{-- Flash --}}
  @if(session('error'))
    <div style="margin-bottom:16px;padding:10px 14px;border-radius:var(--r);
                background:var(--red-dim);color:var(--red);font-size:13px;font-weight:600">
      {{ session('error') }}
    </div>
  @endif

  @include('livewire.products._form', ['mode' => 'create'])
</div>
```

---

## Step 7 — Create Product Edit Blade View

**Target:** `resources/views/livewire/products/edit-product.blade.php` (create new file)

```blade
<div>
  {{-- Flash --}}
  @if(session('error'))
    <div style="margin-bottom:16px;padding:10px 14px;border-radius:var(--r);
                background:var(--red-dim);color:var(--red);font-size:13px;font-weight:600">
      {{ session('error') }}
    </div>
  @endif

  @include('livewire.products._form', ['mode' => 'edit'])
</div>
```

---

## Step 8 — Create the Page Wrapper Blades

**Target A:** `resources/views/owner/products/create.blade.php` (create new file)

```blade
<x-app-layout>

  {{-- Breadcrumb header --}}
  <div class="dashboard-page-header">
    <div>
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
        <a href="{{ route('owner.products.index') }}"
           style="font-size:12px;color:var(--accent);text-decoration:none;font-weight:600">
          Products
        </a>
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"
             viewBox="0 0 24 24" style="color:var(--text-dim)">
          <polyline points="9 18 15 12 9 6"/>
        </svg>
        <span style="font-size:12px;color:var(--text-sub)">New Product</span>
      </div>
      <h1>Add Product</h1>
      <p>Fill in the details to add a new product to your catalog</p>
    </div>
  </div>

  <livewire:products.create-product />

</x-app-layout>
```

**Target B:** `resources/views/owner/products/edit.blade.php` (create new file)

```blade
<x-app-layout>

  {{-- Breadcrumb header --}}
  <div class="dashboard-page-header">
    <div>
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
        <a href="{{ route('owner.products.index') }}"
           style="font-size:12px;color:var(--accent);text-decoration:none;font-weight:600">
          Products
        </a>
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"
             viewBox="0 0 24 24" style="color:var(--text-dim)">
          <polyline points="9 18 15 12 9 6"/>
        </svg>
        <span style="font-size:12px;color:var(--text-sub)">Edit</span>
      </div>
      <h1>Edit Product</h1>
      <p>{{ $product->name }} &middot; <span style="font-family:var(--mono);font-size:13px">{{ $product->sku }}</span></p>
    </div>

    {{-- Product status badge --}}
    <span style="font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;align-self:flex-start;
                 background:{{ $product->is_active ? 'var(--green-dim)' : 'var(--surface2)' }};
                 color:{{ $product->is_active ? 'var(--green)' : 'var(--text-dim)' }}">
      {{ $product->is_active ? 'Active' : 'Inactive' }}
    </span>
  </div>

  <livewire:products.edit-product :product="$product" />

</x-app-layout>
```

---

## Step 9 — Register EditProduct Component

**Target:** `app/Providers/AppServiceProvider.php` OR check if Livewire auto-discovers components.

Run this to verify auto-discovery works (it should in Livewire 3):

```bash
php artisan livewire:list | grep -i product
```

If `Products\EditProduct` is not listed, register it manually in `AppServiceProvider::boot()`:

```php
\Livewire\Livewire::component('products.edit-product', \App\Livewire\Products\EditProduct::class);
```

---

## Step 10 — Clear and Verify

```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear

# Verify routes exist
php artisan route:list | grep "products"

# Verify Livewire components discovered
php artisan livewire:list | grep -i product

# Check no syntax errors
php -l app/Livewire/Products/CreateProduct.php
php -l app/Livewire/Products/EditProduct.php

# Confirm edit route accepts product model binding
php artisan route:list | grep "products.*edit"
```

---

## Summary of Files Created / Modified

| Action | File |
|--------|------|
| Modified | `routes/web.php` — add edit route |
| Modified | `app/Policies/ProductPolicy.php` — all methods return proper booleans |
| Modified | `app/Livewire/Products/CreateProduct.php` — clean rewrite with cent handling |
| Created  | `app/Livewire/Products/EditProduct.php` — full edit component |
| Created  | `resources/views/livewire/products/_form.blade.php` — shared form partial |
| Created  | `resources/views/livewire/products/create-product.blade.php` |
| Created  | `resources/views/livewire/products/edit-product.blade.php` |
| Created  | `resources/views/owner/products/create.blade.php` — page wrapper |
| Created  | `resources/views/owner/products/edit.blade.php` — page wrapper |

## Key Design Decisions

- **Prices in RWF on screen, cents in DB** — inputs accept whole RWF numbers, saved as `* 100`
- **Live margin badge** — updates as user types purchase/selling price
- **Box price suggestion** — auto-calculates `selling × items_per_box` as placeholder hint
- **Live name-to-SKU suggestion** — auto-builds SKU from first 3 words of product name
- **Sticky sidebar** — action buttons and preview always visible while scrolling long forms
- **Unique validation ignores self on edit** — `unique:products,sku,{id}` prevents false conflicts
- **Toggle switch** — custom CSS toggle for `isActive`, no Tailwind dependency
