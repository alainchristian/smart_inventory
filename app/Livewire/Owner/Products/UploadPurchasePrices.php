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
    public array $parseErrors = []; // [{row, message}]
    public bool  $showPreview = false;
    public bool  $applied   = false;
    public int   $updatedCount = 0;
    public ?string $fileError = null;

    // ── Parse the uploaded CSV and show preview ──────────────
    public function processFile(): void
    {
        if (! auth()->user()->isOwner()) {
            abort(403);
        }

        $this->fileError = null;

        if (!$this->csvFile) {
            $this->fileError = 'Please select a CSV file.';
            return;
        }

        $this->preview     = [];
        $this->parseErrors = [];

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
            $skuCol          = array_search('sku', $header ?? []);
            $purchasePriceCol = array_search('purchase_price', $header ?? []);
            $sellingPriceCol  = array_search('selling_price', $header ?? []);

            if ($skuCol === false || $purchasePriceCol === false) {
                $this->parseErrors[] = [
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

                $sku               = trim($row[$skuCol] ?? '');
                $rawPurchasePrice  = trim($row[$purchasePriceCol] ?? '');
                $rawSellingPrice   = $sellingPriceCol !== false ? trim($row[$sellingPriceCol] ?? '') : '';

                // Validate SKU
                if (empty($sku)) {
                    $this->parseErrors[] = ['row' => $rowNumber, 'message' => 'Missing SKU'];
                    $rowNumber++;
                    continue;
                }

                // Validate purchase price
                if (! is_numeric($rawPurchasePrice) || (float)$rawPurchasePrice < 0) {
                    $this->parseErrors[] = [
                        'row'     => $rowNumber,
                        'message' => "SKU {$sku}: invalid purchase price '{$rawPurchasePrice}'"
                    ];
                    $rowNumber++;
                    continue;
                }

                // Validate selling price (optional)
                $newSellingPrice = null;
                if (!empty($rawSellingPrice)) {
                    if (! is_numeric($rawSellingPrice) || (float)$rawSellingPrice < 0) {
                        $this->parseErrors[] = [
                            'row'     => $rowNumber,
                            'message' => "SKU {$sku}: invalid selling price '{$rawSellingPrice}'"
                        ];
                        $rowNumber++;
                        continue;
                    }
                    $newSellingPrice = (int) $rawSellingPrice;
                }

                $newPurchasePrice = (int) $rawPurchasePrice;

                // Find product
                $product = Product::withTrashed()
                    ->where('sku', $sku)
                    ->first();

                if (! $product) {
                    $this->parseErrors[] = [
                        'row'     => $rowNumber,
                        'message' => "SKU '{$sku}' not found in database"
                    ];
                    $rowNumber++;
                    continue;
                }

                $oldPurchasePrice = $product->purchase_price ?? 0;
                $oldSellingPrice  = $product->selling_price ?? 0;

                // Determine status
                $purchaseChanged = $oldPurchasePrice !== $newPurchasePrice;
                $sellingChanged  = $newSellingPrice !== null && $oldSellingPrice !== $newSellingPrice;

                if (!$purchaseChanged && !$sellingChanged) {
                    $status = 'unchanged';
                } elseif ($oldPurchasePrice === 0 && $purchaseChanged) {
                    $status = 'new';
                } else {
                    $status = 'updated';
                }

                $this->preview[] = [
                    'sku'                => $sku,
                    'name'               => $product->name,
                    'old_purchase_price' => $oldPurchasePrice,
                    'new_purchase_price' => $newPurchasePrice,
                    'old_selling_price'  => $oldSellingPrice,
                    'new_selling_price'  => $newSellingPrice,
                    'status'             => $status,   // 'new' | 'updated' | 'unchanged'
                ];

                $rowNumber++;
            }

            fclose($handle);
            $this->showPreview = true;

        } catch (\Exception $e) {
            $this->parseErrors[] = ['row' => 0, 'message' => 'File error: ' . $e->getMessage()];
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

            $updateData = ['purchase_price' => $item['new_purchase_price']];

            // Only update selling price if provided in CSV
            if ($item['new_selling_price'] !== null) {
                $updateData['selling_price'] = $item['new_selling_price'];
            }

            Product::withTrashed()
                ->where('sku', $item['sku'])
                ->update($updateData);

            $count++;
        }

        $this->updatedCount = $count;
        $this->applied      = true;
        $this->showPreview  = false;
        $this->preview      = [];
        $this->csvFile      = null;
    }

    // ── Reset to upload another file ─────────────────────────
    public function resetUpload(): void
    {
        $this->preview      = [];
        $this->parseErrors  = [];
        $this->showPreview  = false;
        $this->applied      = false;
        $this->updatedCount = 0;
        $this->csvFile      = null;
        $this->fileError    = null;
    }

    // ── Download a CSV template pre-filled with all SKUs ──────
    public function downloadTemplate(): mixed
    {
        if (! auth()->user()->isOwner()) {
            abort(403);
        }

        $products = Product::orderBy('sku')->get(['sku', 'name', 'purchase_price', 'selling_price', 'items_per_box']);

        $filename = 'product-prices-' . now()->format('Y-m-d') . '.csv';
        $handle   = fopen('php://temp', 'r+');

        fputcsv($handle, ['sku', 'purchase_price', 'computed_box_purchase_price', 'product_name_reference']);

        foreach ($products as $product) {
            fputcsv($handle, [
                $product->sku,
                $product->purchase_price ?? 0,
                $product->purchase_price * $product->items_per_box,  // computed, read-only reference
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
