# SmartInventory — Owner Purchase Price Upload
## Claude Code Instructions

> Drop in project root. Tell Claude Code:
> "Read PURCHASE_PRICE_UPLOAD.md and follow every step in order."

---

## What This Builds

A dedicated owner-only page at `/owner/products/purchase-prices` where the owner can:
- Upload a CSV with two columns: `sku` and `purchase_price`
- Preview the changes before confirming (shows product name, old price, new price)
- Apply prices in bulk with one click
- Download a template pre-filled with all existing SKUs

The warehouse manager never sees this page — it's behind the owner middleware and the `purchase_price` column is already hidden from all other roles.

---

## Step 0 — Read relevant files first

```bash
cat app/Livewire/Warehouse/Inventory/ReceiveBoxes.php | grep -n "processExcelFile\|excelFile\|Excel::\|downloadTemplate" | head -20
cat routes/web.php | grep -n "categories\|products"
grep -n "canViewPurchasePrices\|viewPurchasePrice" app/Models/User.php app/Providers/AuthServiceProvider.php
```

---

## Step 1 — Add the route

**Target:** `routes/web.php`

Find the owner products route group:
```php
Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', ...)->name('index');
    Route::get('/create', ...)->name('create');
    Route::get('/{product}/edit', ...)->name('edit');
});
```

Add one new route **before** `/{product}/edit` (specific routes must come before wildcard):
```php
Route::get('/purchase-prices', function () {
    return view('owner.products.purchase-prices');
})->name('purchase-prices');
```

Result:
```php
Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', function () { return view('owner.products.index'); })->name('index');
    Route::get('/create', function () { return view('owner.products.create'); })->name('create');
    Route::get('/purchase-prices', function () {
        return view('owner.products.purchase-prices');
    })->name('purchase-prices');
    Route::get('/{product}/edit', function (\App\Models\Product $product) {
        return view('owner.products.edit', compact('product'));
    })->name('edit');
});
```

---

## Step 2 — Create the Livewire component

**File:** `app/Livewire/Owner/Products/UploadPurchasePrices.php`

```php
<?php

namespace App\Livewire\Owner\Products;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class UploadPurchasePrices extends Component
{
    use WithFileUploads;

    public $csvFile;
    public array $preview   = [];   // [{sku, name, old_price, new_price, status}]
    public array $errors    = [];   // [{row, message}]
    public bool  $showPreview = false;
    public bool  $applied   = false;
    public int   $updatedCount = 0;

    protected $rules = [
        'csvFile' => 'required|mimes:csv,txt|max:5120',
    ];

    // ── Parse the uploaded CSV and show preview ──────────────
    public function processFile(): void
    {
        if (! auth()->user()->isOwner()) {
            abort(403);
        }

        $this->validate();

        $this->preview  = [];
        $this->errors   = [];

        try {
            $path = $this->csvFile->getRealPath();
            $handle = fopen($path, 'r');

            if (! $handle) {
                throw new \Exception('Could not read file.');
            }

            $header = fgetcsv($handle); // skip header row

            // Normalise header: lowercase, trim
            if ($header) {
                $header = array_map(fn($h) => strtolower(trim($h)), $header);
            }

            // Find column positions (flexible — order doesn't matter)
            $skuCol   = array_search('sku', $header ?? []);
            $priceCol = array_search('purchase_price', $header ?? []);

            if ($skuCol === false || $priceCol === false) {
                $this->errors[] = [
                    'row' => 1,
                    'message' => 'CSV must have columns: sku and purchase_price'
                ];
                fclose($handle);
                $this->showPreview = true;
                return;
            }

            $rowNumber = 2;
            while (($row = fgetcsv($handle)) !== false) {
                if (empty(array_filter($row))) {
                    $rowNumber++;
                    continue; // skip blank rows
                }

                $sku      = trim($row[$skuCol] ?? '');
                $rawPrice = trim($row[$priceCol] ?? '');

                // Validate SKU
                if (empty($sku)) {
                    $this->errors[] = ['row' => $rowNumber, 'message' => 'Missing SKU'];
                    $rowNumber++;
                    continue;
                }

                // Validate price
                if (! is_numeric($rawPrice) || (float)$rawPrice < 0) {
                    $this->errors[] = [
                        'row'     => $rowNumber,
                        'message' => "SKU {$sku}: invalid price '{$rawPrice}'"
                    ];
                    $rowNumber++;
                    continue;
                }

                $newPrice = (int) $rawPrice;

                // Find product
                $product = Product::withTrashed()
                    ->where('sku', $sku)
                    ->first();

                if (! $product) {
                    $this->errors[] = [
                        'row'     => $rowNumber,
                        'message' => "SKU '{$sku}' not found in database"
                    ];
                    $rowNumber++;
                    continue;
                }

                $oldPrice = $product->purchase_price ?? 0;
                $status   = $oldPrice === $newPrice ? 'unchanged' : ($oldPrice === 0 ? 'new' : 'updated');

                $this->preview[] = [
                    'sku'       => $sku,
                    'name'      => $product->name,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'status'    => $status,   // 'new' | 'updated' | 'unchanged'
                ];

                $rowNumber++;
            }

            fclose($handle);
            $this->showPreview = true;

        } catch (\Exception $e) {
            $this->errors[] = ['row' => 0, 'message' => 'File error: ' . $e->getMessage()];
            $this->showPreview = true;
        }
    }

    // ── Apply the previewed prices ────────────────────────────
    public function applyPrices(): void
    {
        if (! auth()->user()->isOwner()) {
            abort(403);
        }

        if (empty($this->preview)) {
            return;
        }

        $count = 0;

        foreach ($this->preview as $item) {
            if ($item['status'] === 'unchanged') {
                continue;
            }

            Product::withTrashed()
                ->where('sku', $item['sku'])
                ->update(['purchase_price' => $item['new_price']]);

            $count++;
        }

        $this->updatedCount = $count;
        $this->applied      = true;
        $this->showPreview  = false;
        $this->preview      = [];
        $this->csvFile      = null;
    }

    // ── Reset to upload another file ─────────────────────────
    public function reset(): void
    {
        $this->preview      = [];
        $this->errors       = [];
        $this->showPreview  = false;
        $this->applied      = false;
        $this->updatedCount = 0;
        $this->csvFile      = null;
    }

    // ── Download a CSV template pre-filled with all SKUs ──────
    public function downloadTemplate(): mixed
    {
        if (! auth()->user()->isOwner()) {
            abort(403);
        }

        $products = Product::orderBy('sku')->get(['sku', 'name', 'purchase_price']);

        $filename = 'purchase-prices-' . now()->format('Y-m-d') . '.csv';
        $handle   = fopen('php://temp', 'r+');

        fputcsv($handle, ['sku', 'purchase_price', 'product_name_reference']);

        foreach ($products as $product) {
            fputcsv($handle, [
                $product->sku,
                $product->purchase_price ?? 0,
                $product->name,   // reference only — this column is ignored on upload
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    // ── Computed helpers ──────────────────────────────────────
    public function getNewCountProperty(): int
    {
        return count(array_filter($this->preview, fn($r) => $r['status'] === 'new'));
    }

    public function getUpdatedCountPreviewProperty(): int
    {
        return count(array_filter($this->preview, fn($r) => $r['status'] === 'updated'));
    }

    public function getUnchangedCountProperty(): int
    {
        return count(array_filter($this->preview, fn($r) => $r['status'] === 'unchanged'));
    }

    public function render()
    {
        return view('livewire.owner.products.upload-purchase-prices');
    }
}
```

---

## Step 3 — Create the Blade view

**File:** `resources/views/livewire/owner/products/upload-purchase-prices.blade.php`

```blade
<div>
  {{-- ══ UPLOAD CARD ══ --}}
  @if(!$showPreview && !$applied)
  <div style="max-width:680px">

    {{-- Instructions card --}}
    <div style="background:var(--surface);border:1px solid var(--border);
                border-radius:var(--r);padding:22px 24px;margin-bottom:16px">
      <div style="display:flex;align-items:flex-start;gap:14px">
        <div style="width:40px;height:40px;border-radius:10px;background:var(--accent-dim);
                    display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
               style="color:var(--accent)">
            <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
          </svg>
        </div>
        <div>
          <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px">
            Upload Purchase Prices
          </div>
          <div style="font-size:13px;color:var(--text-sub);line-height:1.6">
            Upload a CSV with two columns: <code style="background:var(--surface2);padding:1px 6px;
            border-radius:4px;font-size:12px">sku</code> and
            <code style="background:var(--surface2);padding:1px 6px;
            border-radius:4px;font-size:12px">purchase_price</code>.
            The third column <code style="background:var(--surface2);padding:1px 6px;
            border-radius:4px;font-size:12px">product_name_reference</code> is optional
            and ignored — it's there to help you identify products while filling in the spreadsheet.
            Prices must be in whole RWF (e.g. <strong>33000</strong> for RWF 33,000).
          </div>
        </div>
      </div>
    </div>

    {{-- Download template --}}
    <div style="background:var(--surface);border:1px solid var(--border);
                border-radius:var(--r);padding:18px 24px;margin-bottom:16px;
                display:flex;align-items:center;justify-content:space-between">
      <div>
        <div style="font-size:13px;font-weight:600;color:var(--text)">Download current price sheet</div>
        <div style="font-size:12px;color:var(--text-sub);margin-top:2px">
          Pre-filled with all {{ \App\Models\Product::count() }} SKUs and their current purchase prices
        </div>
      </div>
      <button wire:click="downloadTemplate"
              style="padding:8px 16px;background:var(--surface2);border:1px solid var(--border);
                     border-radius:var(--rx);font-size:13px;font-weight:600;color:var(--text);
                     cursor:pointer;display:flex;align-items:center;gap:6px">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/>
        </svg>
        Download CSV
      </button>
    </div>

    {{-- Upload area --}}
    <div style="background:var(--surface);border:1px solid var(--border);
                border-radius:var(--r);padding:22px 24px">
      <div style="font-size:12px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;
                  color:var(--text-sub);margin-bottom:14px">Upload CSV</div>

      <label style="display:block;border:2px dashed var(--border);border-radius:var(--r);
                    padding:32px;text-align:center;cursor:pointer;transition:.15s"
             onmouseover="this.style.borderColor='var(--accent)'"
             onmouseout="this.style.borderColor='var(--border)'">
        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5"
             viewBox="0 0 24 24" style="color:var(--text-dim);margin:0 auto 10px">
          <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
        </svg>
        @if($csvFile)
          <div style="font-size:14px;font-weight:600;color:var(--accent)">
            {{ $csvFile->getClientOriginalName() }}
          </div>
          <div style="font-size:12px;color:var(--text-sub);margin-top:4px">
            {{ number_format($csvFile->getSize() / 1024, 1) }} KB — click to change
          </div>
        @else
          <div style="font-size:14px;font-weight:600;color:var(--text)">
            Choose CSV file
          </div>
          <div style="font-size:12px;color:var(--text-sub);margin-top:4px">
            .csv files only, max 5 MB
          </div>
        @endif
        <input type="file" wire:model="csvFile" accept=".csv,text/csv" style="display:none">
      </label>

      @error('csvFile')
        <div style="margin-top:8px;font-size:12px;color:var(--red)">{{ $message }}</div>
      @enderror

      @if($csvFile)
        <div style="margin-top:14px;display:flex;justify-content:flex-end">
          <button wire:click="processFile"
                  wire:loading.attr="disabled"
                  style="padding:9px 20px;background:var(--accent);color:#fff;border:none;
                         border-radius:var(--rx);font-size:13px;font-weight:700;cursor:pointer">
            <span wire:loading wire:target="processFile">Parsing…</span>
            <span wire:loading.remove wire:target="processFile">Preview Changes →</span>
          </button>
        </div>
      @endif
    </div>
  </div>
  @endif

  {{-- ══ PREVIEW CARD ══ --}}
  @if($showPreview)
  <div>

    {{-- Summary bar --}}
    <div style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap">
      <div style="padding:10px 18px;background:var(--green-dim);border-radius:var(--r);
                  display:flex;align-items:center;gap:8px">
        <span style="font-size:20px;font-weight:800;color:var(--green)">{{ $this->newCount }}</span>
        <span style="font-size:12px;color:var(--green);font-weight:600">New prices</span>
      </div>
      <div style="padding:10px 18px;background:var(--accent-dim);border-radius:var(--r);
                  display:flex;align-items:center;gap:8px">
        <span style="font-size:20px;font-weight:800;color:var(--accent)">{{ $this->updatedCountPreview }}</span>
        <span style="font-size:12px;color:var(--accent);font-weight:600">Updates</span>
      </div>
      <div style="padding:10px 18px;background:var(--surface2);border-radius:var(--r);
                  display:flex;align-items:center;gap:8px">
        <span style="font-size:20px;font-weight:800;color:var(--text-sub)">{{ $this->unchangedCount }}</span>
        <span style="font-size:12px;color:var(--text-sub);font-weight:600">Unchanged</span>
      </div>
      @if(count($errors) > 0)
      <div style="padding:10px 18px;background:var(--red-dim);border-radius:var(--r);
                  display:flex;align-items:center;gap:8px">
        <span style="font-size:20px;font-weight:800;color:var(--red)">{{ count($errors) }}</span>
        <span style="font-size:12px;color:var(--red);font-weight:600">Errors</span>
      </div>
      @endif
    </div>

    {{-- Errors --}}
    @if(count($errors) > 0)
    <div style="background:var(--red-dim);border:1px solid var(--red);border-radius:var(--r);
                padding:14px 18px;margin-bottom:16px">
      <div style="font-size:12px;font-weight:700;color:var(--red);margin-bottom:8px">
        ⚠ Rows with errors (skipped)
      </div>
      @foreach($errors as $err)
        <div style="font-size:12px;color:var(--red);font-family:var(--mono);padding:2px 0">
          Row {{ $err['row'] }}: {{ $err['message'] }}
        </div>
      @endforeach
    </div>
    @endif

    {{-- Preview table --}}
    @if(count($preview) > 0)
    <div style="background:var(--surface);border:1px solid var(--border);
                border-radius:var(--r);overflow:hidden;margin-bottom:16px">
      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr style="background:var(--surface2);border-bottom:1px solid var(--border)">
            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;
                       letter-spacing:.5px;text-transform:uppercase;color:var(--text-sub)">Product</th>
            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;
                       letter-spacing:.5px;text-transform:uppercase;color:var(--text-sub)">SKU</th>
            <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;
                       letter-spacing:.5px;text-transform:uppercase;color:var(--text-sub)">Old Price</th>
            <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;
                       letter-spacing:.5px;text-transform:uppercase;color:var(--text-sub)">New Price</th>
            <th style="padding:10px 14px;text-align:center;font-size:10px;font-weight:700;
                       letter-spacing:.5px;text-transform:uppercase;color:var(--text-sub)">Change</th>
          </tr>
        </thead>
        <tbody>
          @foreach($preview as $row)
          @php
            $margin = $row['new_price'] > 0
              ? round((($row['new_price']) / max($row['new_price'], 1)) * 100, 0)
              : 0;
            $diff = $row['new_price'] - $row['old_price'];
          @endphp
          <tr style="border-bottom:1px solid var(--border);
                     {{ $row['status'] === 'unchanged' ? 'opacity:.45' : '' }}">
            <td style="padding:9px 14px;font-size:13px;color:var(--text);font-weight:500">
              {{ $row['name'] }}
            </td>
            <td style="padding:9px 14px;font-size:12px;color:var(--text-sub);font-family:var(--mono)">
              {{ $row['sku'] }}
            </td>
            <td style="padding:9px 14px;text-align:right;font-size:13px;color:var(--text-sub);font-family:var(--mono)">
              @if($row['old_price'] > 0)
                {{ number_format($row['old_price']) }}
              @else
                <span style="color:var(--text-dim);font-style:italic">—</span>
              @endif
            </td>
            <td style="padding:9px 14px;text-align:right;font-size:13px;font-weight:700;
                       font-family:var(--mono);color:var(--text)">
              {{ number_format($row['new_price']) }}
            </td>
            <td style="padding:9px 14px;text-align:center">
              @if($row['status'] === 'new')
                <span style="font-size:11px;font-weight:700;padding:3px 8px;border-radius:20px;
                             background:var(--green-dim);color:var(--green)">NEW</span>
              @elseif($row['status'] === 'updated')
                <span style="font-size:11px;font-weight:700;padding:3px 8px;border-radius:20px;
                             background:var(--accent-dim);color:var(--accent)">
                  {{ $diff > 0 ? '+' : '' }}{{ number_format($diff) }}
                </span>
              @else
                <span style="font-size:11px;color:var(--text-dim)">—</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif

    {{-- Actions --}}
    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button wire:click="reset"
              style="padding:9px 18px;background:var(--surface2);border:1px solid var(--border);
                     border-radius:var(--rx);font-size:13px;font-weight:600;color:var(--text-sub);
                     cursor:pointer">
        ← Upload different file
      </button>
      @if($this->newCount + $this->updatedCountPreview > 0)
      <button wire:click="applyPrices"
              wire:loading.attr="disabled"
              wire:confirm="Apply {{ $this->newCount + $this->updatedCountPreview }} price updates?"
              style="padding:9px 20px;background:var(--accent);color:#fff;border:none;
                     border-radius:var(--rx);font-size:13px;font-weight:700;cursor:pointer">
        <span wire:loading wire:target="applyPrices">Saving…</span>
        <span wire:loading.remove wire:target="applyPrices">
          ✓ Apply {{ $this->newCount + $this->updatedCountPreview }} Updates
        </span>
      </button>
      @endif
    </div>
  </div>
  @endif

  {{-- ══ SUCCESS STATE ══ --}}
  @if($applied)
  <div style="max-width:480px;text-align:center;padding:48px 24px">
    <div style="width:56px;height:56px;background:var(--green-dim);border-radius:50%;
                display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
      <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5"
           viewBox="0 0 24 24" style="color:var(--green)">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
    </div>
    <div style="font-size:20px;font-weight:800;color:var(--text);margin-bottom:8px">
      {{ number_format($updatedCount) }} prices updated
    </div>
    <div style="font-size:14px;color:var(--text-sub);margin-bottom:24px">
      Purchase prices have been saved. They are only visible to you.
    </div>
    <button wire:click="reset"
            style="padding:9px 20px;background:var(--accent);color:#fff;border:none;
                   border-radius:var(--rx);font-size:13px;font-weight:700;cursor:pointer">
      Upload another file
    </button>
  </div>
  @endif
</div>
```

---

## Step 4 — Create the page layout blade

**File:** `resources/views/owner/products/purchase-prices.blade.php`

```blade
<x-app-layout>

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
        <span style="font-size:12px;color:var(--text-sub)">Purchase Prices</span>
      </div>
      <h1>Purchase Prices</h1>
      <p>Upload purchase prices privately — not visible to warehouse or shop managers</p>
    </div>
  </div>

  <livewire:owner.products.upload-purchase-prices />

</x-app-layout>
```

---

## Step 5 — Add navigation link in sidebar (owner section only)

**Target:** `resources/views/livewire/layout/sidebar.blade.php` (or `resources/views/components/sidebar.blade.php`)

Find the owner Products link in the sidebar — it looks like:
```blade
<a href="{{ route('owner.products.index') }}" wire:navigate ...>
    Products
</a>
```

After the Products link (or inside a submenu if one exists), add:
```blade
<a href="{{ route('owner.products.purchase-prices') }}" wire:navigate
   class="block px-4 py-1.5 text-[13px] rounded-lg transition-colors
          {{ request()->routeIs('owner.products.purchase-prices') ? 'bg-[var(--accent-dim)] text-[var(--accent)]' : 'text-[var(--text-dim)] hover:bg-[var(--surface2)] hover:text-[var(--text)]' }}">
    💰 Purchase Prices
</a>
```

This link must be inside the `@if(auth()->user()->isOwner())` block only — never visible to other roles.

---

## Step 6 — Clear caches and verify

```bash
php artisan view:clear
php artisan cache:clear

# Confirm route exists
php artisan route:list | grep purchase-prices

# Confirm component discovered
php artisan livewire:list | grep -i purchase

# No syntax errors
php -l app/Livewire/Owner/Products/UploadPurchasePrices.php
```

---

## Workflow summary

| Step | Who | Action |
|---|---|---|
| 1 | Warehouse Manager | Uploads `kigalifootwear-products.csv` via Receive Boxes — creates products with `purchase_price = 0` |
| 2 | Owner | Goes to Products → Purchase Prices |
| 3 | Owner | Downloads the current price sheet (CSV pre-filled with all SKUs) |
| 4 | Owner | Fills in the `purchase_price` column in private (Excel/Numbers/Google Sheets) |
| 5 | Owner | Uploads the completed CSV |
| 6 | Owner | Reviews the preview table (old vs new, change diff) |
| 7 | Owner | Clicks "Apply Updates" — prices saved |

The warehouse manager and shop managers **never** see the purchase price column anywhere in the UI — it is already guarded by the `viewPurchasePrice` gate in all blades and the `canViewPurchasePrices()` method on the User model.
