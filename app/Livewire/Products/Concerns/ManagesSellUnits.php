<?php

namespace App\Livewire\Products\Concerns;

use App\Models\Product;
use App\Models\ProductSellUnit;
use App\Services\SettingsService;

/**
 * "Selling in packs" section of the product form (CreateProduct / EditProduct).
 * Rows are ['id' => ?int, 'name' => string, 'size' => int, 'price' => int].
 */
trait ManagesSellUnits
{
    public array $sellUnits        = [];
    public bool  $sellSinglePieces = true;

    protected function loadSellUnits(Product $product): void
    {
        $this->sellSinglePieces = (bool) $product->sell_single_pieces;
        $this->sellUnits = $product->sellUnits()->get()
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'size' => $u->size, 'price' => $u->price])
            ->all();
    }

    /** Single-piece selling price implied by the box price entered in the form. */
    public function getPiecePriceProperty(): int
    {
        return $this->itemsPerBox > 0 ? (int) round((float) $this->boxSellingPrice / $this->itemsPerBox) : 0;
    }

    /** Presets that still make sense: not already added, and smaller than a box. */
    public function getSellUnitPresetsProperty(): array
    {
        $taken = array_map('intval', array_column($this->sellUnits, 'size'));

        return array_filter(
            ProductSellUnit::PRESETS,
            fn ($p) => $p[1] < $this->itemsPerBox && ! in_array($p[1], $taken, true)
        );
    }

    /** Is the chosen category allowed to sell loose at all (owner Settings → Sales)? */
    public function getCategorySellsLooseProperty(): bool
    {
        return $this->categoryId
            && app(SettingsService::class)->categoryAllowsIndividualSales((int) $this->categoryId);
    }

    public function addSellUnit(string $preset): void
    {
        [$name, $size] = ProductSellUnit::PRESETS[$preset] ?? ['Pack', null];
        $size ??= min(10, max(2, $this->itemsPerBox - 1));

        $this->sellUnits[] = [
            'id'    => null,
            'name'  => $name,
            'size'  => $size,
            'price' => $this->piecePrice * $size,
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
            'sellSinglePieces'   => 'boolean',
            'sellUnits'          => 'array|max:8',
            'sellUnits.*.name'   => 'required|string|max:40|distinct:ignore_case',
            'sellUnits.*.size'   => ['required', 'integer', 'min:2', 'distinct', function ($attr, $value, $fail) {
                if ((int) $value >= (int) $this->itemsPerBox) {
                    $fail("A pack must hold fewer pieces than a box ({$this->itemsPerBox}).");
                }
            }],
            'sellUnits.*.price'  => 'required|integer|min:1',
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
