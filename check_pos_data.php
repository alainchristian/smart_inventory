<?php
// Run this via: php artisan tinker < check_pos_data.php

use App\Models\Shop;
use App\Models\Box;
use App\Models\Product;

echo "\n=== POS Data Diagnostic ===\n\n";

// Check shops
$shops = Shop::all();
echo "Total Shops: " . $shops->count() . "\n";
foreach ($shops as $shop) {
    echo "  - Shop #{$shop->id}: {$shop->name}\n";
}

echo "\n";

// Check boxes per shop
foreach ($shops as $shop) {
    $boxCount = Box::where('location_type', 'shop')
        ->where('location_id', $shop->id)
        ->count();
    
    $availableBoxes = Box::where('location_type', 'shop')
        ->where('location_id', $shop->id)
        ->whereIn('status', ['full', 'partial'])
        ->where('items_remaining', '>', 0)
        ->count();
    
    echo "Shop #{$shop->id} ({$shop->name}):\n";
    echo "  Total boxes: {$boxCount}\n";
    echo "  Available boxes (full/partial with items): {$availableBoxes}\n";
}

echo "\n";

// Check products
$totalProducts = Product::where('is_active', true)->count();
echo "Total Active Products: {$totalProducts}\n";

echo "\n";

// Check products with stock at shops
foreach ($shops as $shop) {
    $productsWithStock = Product::where('is_active', true)
        ->whereHas('boxes', function ($query) use ($shop) {
            $query->where('location_type', 'shop')
                  ->where('location_id', $shop->id)
                  ->whereIn('status', ['full', 'partial'])
                  ->where('items_remaining', '>', 0);
        })
        ->count();
    
    echo "Shop #{$shop->id} ({$shop->name}): {$productsWithStock} products with stock\n";
}

echo "\n=== End Diagnostic ===\n";
