<?php

namespace App\Livewire\Products\Concerns;

use App\Models\Product;
use App\Models\ProductSellUnit;
use App\Services\SettingsService;

/**
 * "Selling loose" section of the product form (CreateProduct / EditProduct):
 * the single-piece price, the single-piece switch and the packs.
 * Rows are ['id' => ?int, 'name' => string, 'size' => int, 'price' => int].
 */
trait ManagesSellUnits
{
    public array  $sellUnits        = [];
    public bool   $sellSinglePieces = true;
    /** Single-piece price; blank = the box rate (box price ÷ items per box). */
    public string $singlePiecePrice = '';

    protected function loadSellUnits(Product $product): void
    {
        $this->sellSinglePieces = (bool) $product->sell_single_pieces;
        $this->sellUnits = $product->sellUnits()->get()
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'size' => $u->size, 'price' => $u->price])
            ->all();

        // Only show a piece price the owner actually set above the box rate
        $boxRate = $product->items_per_box > 0 && $product->box_selling_price
            ? (int) round($product->box_selling_price / $product->items_per_box)
            : 0;
        $this->singlePiecePrice = $product->selling_price > 0 && (int) $product->selling_price !== $boxRate
            ? (string) $product->selling_price
            : '';
    }

    /** Box price per piece, as entered in the form. */
    public function getBoxRateProperty(): int
    {
        return $this->itemsPerBox > 0 ? (int) round((float) $this->boxSellingPrice / $this->itemsPerBox) : 0;
    }

    /** Single-piece selling price: the owner's figure, or the box rate when left blank. */
    public function getPiecePriceProperty(): int
    {
        return (int) $this->singlePiecePrice > 0 ? (int) $this->singlePiecePrice : $this->boxRate;
    }

    /** What products.selling_price is saved as. */
    protected function sellingPriceToSave(): int
    {
        return $this->piecePrice;
    }

    /** Presets that still make sense: they fit in the box and aren't already added. key => [name, size]. */
    public function getSellUnitPresetsProperty(): array
    {
        $taken = array_map('intval', array_column($this->sellUnits, 'size'));
        $out   = [];
        foreach (ProductSellUnit::PRESETS as $key => [$name]) {
            $size = ProductSellUnit::presetSize($key, (int) $this->itemsPerBox);
            if ($size !== null && ! in_array($size, $taken, true) && ! in_array($size, array_column($out, 1), true)) {
                $out[$key] = [$name, $size];
            }
        }

        return $out;
    }

    /** Is the chosen category allowed to sell loose at all (owner Settings → Sales)? */
    public function getCategorySellsLooseProperty(): bool
    {
        return $this->categoryId
            && app(SettingsService::class)->categoryAllowsIndividualSales((int) $this->categoryId);
    }

    public function addSellUnit(string $preset): void
    {
        $size = ProductSellUnit::presetSize($preset, (int) $this->itemsPerBox);
        $name = $size !== null ? ProductSellUnit::PRESETS[$preset][0] : 'Pack';
        $size ??= min(10, max(2, $this->itemsPerBox - 1));

        $this->sellUnits[] = [
            'id'    => null,
            'name'  => $name,
            'size'  => $size,
            // size × piece price, but never under the box rate: 80,000 / 60 = 1,333.33
            // a piece rounds to 1,333, and 6 × 1,333 = 7,998 is below the 8,000 minimum
            'price' => max(
                $this->piecePrice * $size,
                ProductSellUnit::minPrice($size, (int) $this->boxSellingPrice, (int) $this->itemsPerBox)
            ),
        ];
    }

    public function removeSellUnit(int $index): void
    {
        unset($this->sellUnits[$index]);
        $this->sellUnits = array_values($this->sellUnits);
        $this->resetValidation();
    }

    protected function sellUnitRules(): array
    {
        return [
            'singlePiecePrice'   => ['nullable', 'integer', 'min:0', function ($attr, $value, $fail) {
                if ((int) $value > 0 && (int) $value < (int) floor(ProductSellUnit::boxRate((int) $this->boxSellingPrice, (int) $this->itemsPerBox))) {
                    $fail('A single piece can\'t cost less than its share of the box (' . number_format($this->boxRate) . ' RWF).');
                }
            }],
            'sellSinglePieces'   => 'boolean',
            'sellUnits'          => 'array|max:8',
            'sellUnits.*.name'   => 'required|string|max:40|distinct:ignore_case',
            'sellUnits.*.size'   => ['required', 'integer', 'min:2', 'distinct', function ($attr, $value, $fail) {
                if ((int) $value >= (int) $this->itemsPerBox) {
                    $fail("A pack must hold fewer pieces than a box ({$this->itemsPerBox}).");
                }
            }],
            'sellUnits.*.price'  => ['required', 'integer', 'min:1', function ($attr, $value, $fail) {
                $row = $this->sellUnits[(int) explode('.', $attr)[1]] ?? null;
                if (! $row || (int) ($row['size'] ?? 0) < 2 || (int) $row['size'] >= (int) $this->itemsPerBox) {
                    return;   // the size rule reports that one
                }
                $problem = ProductSellUnit::problemFor(
                    (string) ($row['name'] ?? ''), (int) $row['size'], (int) $value,
                    $this->piecePrice, (int) $this->boxSellingPrice, (int) $this->itemsPerBox,
                );
                if ($problem) {
                    $fail($problem);
                }
            }],
        ];
    }

    protected function sellUnitMessages(): array
    {
        return [
            'sellUnits.*.name.required'  => 'Give the pack a name.',
            'sellUnits.*.name.distinct'  => 'Two packs have the same name.',
            'sellUnits.*.size.required'  => 'How many pieces?',
            'sellUnits.*.size.min'       => 'At least 2 pieces.',
            'sellUnits.*.size.distinct'  => 'Two packs have the same size.',
            'sellUnits.*.price.required' => 'Set a price.',
            'sellUnits.*.price.min'      => 'Set a price.',
            'singlePiecePrice.integer'   => 'Enter a whole amount.',
        ];
    }

    /**
     * Replace the product's packs with the form rows. Rewritten wholesale (not
     * updated in place) so swapping two packs' sizes can't trip the unique
     * index; nothing references unit ids — sales and carts use product + size.
     */
    protected function saveSellUnits(Product $product): void
    {
        $product->update(['sell_single_pieces' => $this->sellSinglePieces]);

        $product->sellUnits()->delete();
        foreach ($this->sellUnits as $row) {
            $product->sellUnits()->create([
                'name'  => trim($row['name']),
                'size'  => (int) $row['size'],
                'price' => (int) $row['price'],
            ]);
        }
    }
}
