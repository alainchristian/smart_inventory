<?php

namespace App\Services\Products;

use App\Models\Product;
use App\Models\ProductSellUnit;
use App\Services\AuditLogger;
use App\Services\SettingsService;
use Illuminate\Support\Facades\DB;

/**
 * Products list → "Apply packs": give many products the same packs (Dozen,
 * Half box, Pack of 10…) in one go instead of editing them one by one.
 *
 * Packs come in as ['key' => preset key | 'custom', 'name' => ?string,
 * 'size' => ?int] (presets size themselves per box, e.g. Half box = half of
 * that product's box). Options:
 *   price:       'piece' (size × single-piece price) | 'discount'
 *   discount:    % off size × single-piece price (when price = 'discount')
 *   existing:    'skip' | 'replace' — when the product already has a pack of that size
 *   single:      'keep' | 'on' | 'off' — the product's "sell single pieces" switch
 * Prices are clamped to the same ladder as the product form
 * (ProductSellUnit::problemFor): never cheaper per piece than the box,
 * never dearer than single pieces.
 */
class SellUnitBulkApplier
{
    public const MAX_PACKS = 8;   // same limit as the product form

    /**
     * What would happen, per product: packs to add / replace and packs
     * skipped with the reason. Writes nothing.
     *
     * @return array<int, array{id:int, name:string, items_per_box:int, sells_loose:bool, add:array, skip:array}>
     */
    public function preview(array $productIds, array $packs, array $opts = []): array
    {
        $settings = app(SettingsService::class);
        $products = Product::with('sellUnits')->whereIn('id', $productIds)->orderBy('name')->get();
        $rows     = [];

        foreach ($products as $product) {
            $ipb      = (int) $product->items_per_box;
            $boxPrice = (int) ($product->box_selling_price ?: $product->selling_price * $ipb);
            $piece    = (int) $product->selling_price;
            $existing = $product->sellUnits->keyBy('size');
            $count    = $existing->count();
            $row      = [
                'id'            => $product->id,
                'name'          => $product->name,
                'items_per_box' => $ipb,
                'sells_loose'   => $settings->categoryAllowsIndividualSales($product->category_id),
                'add'           => [],
                'skip'          => [],
            ];
            $sizesThisRun = [];

            foreach ($packs as $pack) {
                $key  = $pack['key'] ?? 'custom';
                $name = $key === 'custom' ? trim((string) ($pack['name'] ?? '')) : ProductSellUnit::PRESETS[$key][0] ?? '';
                $size = $key === 'custom'
                    ? (int) ($pack['size'] ?? 0)
                    : ProductSellUnit::presetSize($key, $ipb);
                $label = $name !== '' ? $name : 'Pack';

                if ($size === null || $size < 2 || $size >= $ipb) {
                    $row['skip'][] = ['name' => $label, 'reason' => "Doesn't fit in a box of {$ipb}"];
                    continue;
                }
                if (in_array($size, $sizesThisRun, true)) {
                    $row['skip'][] = ['name' => $label, 'reason' => "Same size ({$size}) as another pack chosen"];
                    continue;
                }

                $current = $existing->get($size);
                if ($current && ($opts['existing'] ?? 'skip') !== 'replace') {
                    $row['skip'][] = ['name' => $label, 'reason' => "Already sold as {$current->name} ({$size})"];
                    continue;
                }
                $nameClash = $product->sellUnits->first(fn ($u) => strcasecmp($u->name, $label) === 0 && (int) $u->size !== $size);
                if ($nameClash) {
                    $row['skip'][] = ['name' => $label, 'reason' => "Name already used for a pack of {$nameClash->size}"];
                    continue;
                }
                if (! $current && $count >= self::MAX_PACKS) {
                    $row['skip'][] = ['name' => $label, 'reason' => 'Already has ' . self::MAX_PACKS . ' packs'];
                    continue;
                }

                $price   = $this->price($size, $piece, $boxPrice, $ipb, $opts);
                $problem = ProductSellUnit::problemFor($label, $size, $price, $piece, $boxPrice, $ipb);
                if ($problem) {
                    $row['skip'][] = ['name' => $label, 'reason' => $problem];
                    continue;
                }

                $row['add'][]   = ['name' => $label, 'size' => $size, 'price' => $price, 'replaces' => $current?->name];
                $sizesThisRun[] = $size;
                $count += $current ? 0 : 1;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Write the preview. Owner / admin only. One transaction, one audit entry.
     * Returns ['products' => n changed, 'packs' => n written].
     */
    public function apply(array $productIds, array $packs, array $opts = []): array
    {
        $user = auth()->user();
        if (! $user || (! $user->isOwner() && ! $user->isAdmin())) {
            throw new \DomainException('Only the owner can change how products are sold.');
        }

        $rows   = $this->preview($productIds, $packs, $opts);
        $single = $opts['single'] ?? 'keep';
        $done   = ['products' => 0, 'packs' => 0];

        DB::transaction(function () use ($rows, $single, &$done) {
            foreach ($rows as $row) {
                $changed = false;
                foreach ($row['add'] as $pack) {
                    ProductSellUnit::updateOrCreate(
                        ['product_id' => $row['id'], 'size' => $pack['size']],
                        ['name' => $pack['name'], 'price' => $pack['price']],
                    );
                    $done['packs']++;
                    $changed = true;
                }
                if ($single !== 'keep') {
                    $changed = Product::whereKey($row['id'])
                        ->where('sell_single_pieces', '!=', $single === 'on')
                        ->update(['sell_single_pieces' => $single === 'on']) > 0 || $changed;
                }
                $done['products'] += $changed ? 1 : 0;
            }
        });

        AuditLogger::log([
            'action'      => 'sell_units_bulk_applied',
            'module'      => 'products',
            'entity_type' => 'Product',
            'details'     => [
                'product_ids' => array_column($rows, 'id'),
                'packs'       => array_values($packs),
                'options'     => $opts,
                'written'     => $done,
            ],
        ]);

        return $done;
    }

    /** size × single-piece price, less any discount, kept inside the ladder. */
    private function price(int $size, int $piece, int $boxPrice, int $ipb, array $opts): int
    {
        $base  = $piece * $size;
        $price = ($opts['price'] ?? 'piece') === 'discount'
            ? (int) round($base * (1 - max(0, min(100, (float) ($opts['discount'] ?? 0))) / 100))
            : $base;

        $min = ProductSellUnit::minPrice($size, $boxPrice, $ipb);
        $max = max($base, (int) ceil(ProductSellUnit::boxRate($boxPrice, $ipb) * $size));

        return max($min, min($max, $price));
    }
}
